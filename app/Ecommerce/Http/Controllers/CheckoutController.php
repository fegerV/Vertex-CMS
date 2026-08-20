<?php

namespace App\Ecommerce\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Ecommerce\Models\Product;
use App\Ecommerce\Services\CartService;
use App\Ecommerce\Services\OrderService;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CheckoutController extends Controller
{
    public function __construct(
        private readonly CartService $cartService,
        private readonly OrderService $orderService,
    ) {}

    /**
     * Display checkout page
     */
    public function index(Request $request)
    {
        $sessionId = session()->getId();
        $cartItems = $this->cartService->getCart($sessionId);

        if ($cartItems->isEmpty()) {
            return redirect()->route('ecommerce.cart')
                ->with('error', 'Your cart is empty.');
        }

        $totals = $this->cartService->getTotals($cartItems);
        $user = auth()->user();

        // Pre-fill customer data if logged in
        $customerData = [];
        if ($user) {
            $customerData = [
                'name' => $user->name,
                'email' => $user->email,
                'shipping_address' => [
                    'first_name' => '',
                    'last_name' => '',
                    'company' => '',
                    'address_line_1' => '',
                    'address_line_2' => '',
                    'city' => '',
                    'state' => '',
                    'postal_code' => '',
                    'country' => config('vertex.ecommerce.default_country', 'US'),
                    'phone' => '',
                ],
                'billing_address' => [],
            ];
        }

        return view('ecommerce.public.checkout.index', [
            'cartItems' => $cartItems,
            'totals' => $totals,
            'customerData' => $customerData,
            'paymentMethods' => config('ecommerce.payment_methods', ['stripe', 'paypal', 'bank_transfer']),
            'shippingMethods' => config('ecommerce.shipping_methods', [
                ['id' => 'standard', 'name' => 'Standard Shipping', 'price' => 5.99],
                ['id' => 'express', 'name' => 'Express Shipping', 'price' => 12.99],
                ['id' => 'free', 'name' => 'Free Shipping', 'price' => 0],
            ]),
        ]);
    }

    /**
     * Process checkout and create order
     */
    public function store(Request $request)
    {
        $sessionId = session()->getId();
        $cartItems = $this->cartService->getCart($sessionId);

        if ($cartItems->isEmpty()) {
            throw ValidationException::withMessages([
                'cart' => 'Your cart is empty.',
            ]);
        }

        $validated = $request->validate([
            // Customer info
            'customer.name' => 'required|string|max:255',
            'customer.email' => 'required|email|max:255',
            'customer.phone' => 'nullable|string|max:20',

            // Shipping address
            'shipping_address.first_name' => 'required|string|max:100',
            'shipping_address.last_name' => 'required|string|max:100',
            'shipping_address.address_line_1' => 'required|string|max:255',
            'shipping_address.address_line_2' => 'nullable|string|max:255',
            'shipping_address.city' => 'required|string|max:100',
            'shipping_address.state' => 'required|string|max:100',
            'shipping_address.postal_code' => 'required|string|max:20',
            'shipping_address.country' => 'required|string|max:2',
            'shipping_address.phone' => 'nullable|string|max:20',

            // Billing address (optional, defaults to shipping)
            'billing_address.same_as_shipping' => 'boolean',
            'billing_address.first_name' => 'nullable|string|max:100',
            'billing_address.last_name' => 'nullable|string|max:100',
            'billing_address.address_line_1' => 'nullable|string|max:255',
            'billing_address.address_line_2' => 'nullable|string|max:255',
            'billing_address.city' => 'nullable|string|max:100',
            'billing_address.state' => 'nullable|string|max:100',
            'billing_address.postal_code' => 'nullable|string|max:20',
            'billing_address.country' => 'nullable|string|max:2',

            // Payment & shipping
            'payment_method' => 'required|in:stripe,paypal,bank_transfer,cod',
            'shipping_method' => 'required|string',
            'notes' => 'nullable|string|max:1000',

            // Terms acceptance
            'terms_accepted' => 'accepted',
        ]);

        // Prepare billing address
        $billingAddress = [];
        if ($request->input('billing_address.same_as_shipping', false)) {
            $billingAddress = $validated['shipping_address'];
        } else {
            $billingAddress = array_filter($validated['billing_address'] ?? []);
            if (empty($billingAddress)) {
                $billingAddress = $validated['shipping_address'];
            }
        }

        $checkoutData = [
            'name' => $validated['customer']['name'],
            'email' => $validated['customer']['email'],
            'phone' => $validated['customer']['phone'] ?? null,
            'shipping_address' => $validated['shipping_address'],
            'billing_address' => $billingAddress,
            'payment_method' => $validated['payment_method'],
            'shipping_method' => $validated['shipping_method'],
            'notes' => $validated['notes'] ?? null,
        ];

        try {
            DB::beginTransaction();

            $user = auth()->user();
            $order = $this->orderService->createFromCart($checkoutData, $user, $sessionId);

            DB::commit();

            // Clear cart after successful order
            $this->cartService->clearCart($sessionId);

            // Redirect based on payment method
            if ($validated['payment_method'] === 'stripe') {
                return redirect()->route('ecommerce.checkout.payment.stripe', $order)
                    ->with('success', 'Order created. Please complete payment.');
            } elseif ($validated['payment_method'] === 'paypal') {
                return redirect()->route('ecommerce.checkout.payment.paypal', $order)
                    ->with('success', 'Order created. Redirecting to PayPal...');
            } else {
                return redirect()->route('ecommerce.checkout.success', $order)
                    ->with('success', 'Order placed successfully!');
            }
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Checkout failed: ' . $e->getMessage(), [
                'exception' => get_class($e),
                'cart_session' => $sessionId,
            ]);

            throw ValidationException::withMessages([
                'general' => 'Checkout failed. Please try again or contact support.',
            ]);
        }
    }

    /**
     * Display order success page
     */
    public function success($orderId)
    {
        $order = $this->orderService->getOrderById($orderId);

        if (!$order || ($order->session_id !== session()->getId() && !auth()->check())) {
            abort(403, 'Unauthorized access to this order.');
        }

        return view('ecommerce.public.checkout.success', [
            'order' => $order,
        ]);
    }

    /**
     * Stripe payment redirect
     */
    public function stripePayment($orderId)
    {
        $order = $this->orderService->getOrderById($orderId);

        if (!$order || $order->payment_method !== 'stripe') {
            abort(404);
        }

        // TODO: Integrate with Stripe Payment Intents API
        // For now, just show a placeholder
        return view('ecommerce.public.checkout.payment-stripe', [
            'order' => $order,
            'clientSecret' => null, // Will be populated after Stripe integration
        ]);
    }

    /**
     * PayPal payment redirect
     */
    public function paypalPayment($orderId)
    {
        $order = $this->orderService->getOrderById($orderId);

        if (!$order || $order->payment_method !== 'paypal') {
            abort(404);
        }

        // TODO: Integrate with PayPal Orders API
        return view('ecommerce.public.checkout.payment-paypal', [
            'order' => $order,
        ]);
    }
}

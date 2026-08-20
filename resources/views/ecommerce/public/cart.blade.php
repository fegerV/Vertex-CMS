@extends('layouts.app')

@section('title', 'Корзина')
@section('description', 'Ваша корзина покупок')

@section('content')
<div class="vc-cart container mx-auto px-4 py-8">
    <!-- Header -->
    <div class="mb-8">
        <h1 class="text-4xl font-bold mb-2">Корзина</h1>
        <p class="text-slate-600">
            @if($cartItems->count() > 0)
                {{ $cartItems->count() }} товаров
            @else
                Корзина пуста
            @endif
        </p>
    </div>

    @if($cartItems->count() > 0)
        <!-- Cart Items -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Cart Items List -->
            <div class="lg:col-span-2 space-y-4">
                @foreach($cartItems as $item)
                    <div class="flex gap-4 p-4 bg-white rounded-xl shadow-sm border border-slate-100" data-cart-item-id="{{ $item->id }}">
                        <!-- Product Image -->
                        <div class="w-24 h-24 flex-shrink-0 rounded-lg overflow-hidden bg-slate-50">
                            @if($item->product?->media->isNotEmpty())
                                <img
                                    src="{{ $item->product->media->first()->url }}"
                                    alt="{{ $item->product->name }}"
                                    class="w-full h-full object-cover"
                                    loading="lazy"
                                >
                            @else
                                <div class="w-full h-full flex items-center justify-center text-slate-300">
                                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                    </svg>
                                </div>
                            @endif
                        </div>

                        <!-- Product Info -->
                        <div class="flex-1">
                            <h3 class="font-semibold text-lg mb-1">
                                <a href="{{ route('ecommerce.product.show', $item->product->slug) }}" class="hover:text-blue-600">
                                    {{ $item->product->name }}
                                </a>
                            </h3>
                            
                            @if($item->product?->sku)
                                <p class="text-sm text-slate-500 mb-2">Артикул: {{ $item->product->sku }}</p>
                            @endif

                            <!-- Price per item -->
                            <p class="text-slate-900 font-medium">${{ number_format($item->product->price, 2) }}</p>
                        </div>

                        <!-- Quantity Controls -->
                        <div class="flex flex-col items-end justify-between">
                            <form action="{{ route('ecommerce.cart.remove', $item->id) }}" method="POST" class="self-end">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-slate-400 hover:text-red-500 transition-colors">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                    </svg>
                                </button>
                            </form>

                            <div class="flex items-center gap-2">
                                <form action="{{ route('ecommerce.cart.update', $item->id) }}" method="POST" class="flex items-center gap-2">
                                    @csrf
                                    @method('PATCH')
                                    <button 
                                        type="button" 
                                        onclick="this.parentElement.querySelector('[name=\'quantity\']').value = Math.max(1, parseInt(this.parentElement.querySelector('[name=\'quantity\']').value) - 1); this.parentElement.submit();"
                                        class="w-8 h-8 rounded-lg border border-slate-200 hover:bg-slate-50 flex items-center justify-center"
                                    >
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"/>
                                        </svg>
                                    </button>
                                    
                                    <input 
                                        type="number" 
                                        name="quantity" 
                                        value="{{ $item->quantity }}" 
                                        min="1"
                                        max="{{ $item->product->track_inventory ? $item->product->quantity : 999 }}"
                                        onchange="this.form.submit()"
                                        class="w-12 text-center border border-slate-200 rounded-lg py-1"
                                    >
                                    
                                    <button 
                                        type="button"
                                        onclick="this.parentElement.querySelector('[name=\'quantity\']').value = parseInt(this.parentElement.querySelector('[name=\'quantity\']').value) + 1; this.parentElement.submit();"
                                        class="w-8 h-8 rounded-lg border border-slate-200 hover:bg-slate-50 flex items-center justify-center"
                                    >
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                        </svg>
                                    </button>
                                </form>
                            </div>
                        </div>

                        <!-- Item Total -->
                        <div class="flex flex-col items-end justify-between">
                            <p class="text-lg font-bold text-slate-900">
                                ${{ number_format($item->product->price * $item->quantity, 2) }}
                            </p>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Cart Summary -->
            <div class="lg:col-span-1">
                <div class="bg-white rounded-xl shadow-sm border border-slate-100 p-6 sticky top-4">
                    <h2 class="text-xl font-bold mb-4">Итого</h2>
                    
                    <div class="space-y-3 mb-6">
                        <div class="flex justify-between text-slate-600">
                            <span>Подытог</span>
                            <span>${{ number_format($totals['subtotal'], 2) }}</span>
                        </div>
                        
                        @if($totals['discount'] > 0)
                            <div class="flex justify-between text-green-600">
                                <span>Скидка</span>
                                <span>-${{ number_format($totals['discount'], 2) }}</span>
                            </div>
                        @endif
                        
                        @if($totals['tax'] > 0)
                            <div class="flex justify-between text-slate-600">
                                <span>Налог</span>
                                <span>${{ number_format($totals['tax'], 2) }}</span>
                            </div>
                        @endif
                        
                        <div class="border-t pt-3 flex justify-between font-bold text-lg">
                            <span>Итого</span>
                            <span>${{ number_format($totals['total'], 2) }}</span>
                        </div>
                    </div>

                    <a 
                        href="{{ route('ecommerce.checkout') }}"
                        class="block w-full bg-slate-900 text-white text-center px-6 py-3 rounded-lg hover:bg-slate-800 transition-colors font-medium"
                    >
                        Оформить заказ
                    </a>

                    <a 
                        href="{{ route('ecommerce.catalog') }}"
                        class="block w-full text-center px-6 py-3 mt-3 text-slate-600 hover:text-slate-900 transition-colors"
                    >
                        Продолжить покупки
                    </a>
                </div>
            </div>
        </div>
    @else
        <!-- Empty Cart State -->
        <div class="text-center py-16">
            <svg class="w-24 h-24 mx-auto text-slate-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
            </svg>
            <h3 class="text-xl font-semibold mb-2">Ваша корзина пуста</h3>
            <p class="text-slate-600 mb-6">Добавьте товары, чтобы начать покупки</p>
            <a 
                href="{{ route('ecommerce.catalog') }}"
                class="inline-block bg-slate-900 text-white px-6 py-3 rounded-lg hover:bg-slate-800 transition-colors"
            >
                Перейти в каталог
            </a>
        </div>
    @endif
</div>
@endsection

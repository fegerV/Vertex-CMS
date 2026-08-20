@extends('layouts.app')

@section('title', 'Каталог товаров')
@section('description', 'Полный каталог наших товаров')

@section('content')
<div class="vc-catalog container mx-auto px-4 py-8">
    <!-- Header -->
    <div class="mb-8">
        <h1 class="text-4xl font-bold mb-2">Каталог товаров</h1>
        <p class="text-slate-600">{{ count($products) }} товаров</p>
    </div>

    <!-- Filters & Sort -->
    <div class="flex flex-wrap gap-4 mb-8 items-center">
        <form method="GET" action="{{ route('ecommerce.catalog') }}" class="flex gap-2">
            <input 
                type="text" 
                name="search" 
                placeholder="Поиск товаров..." 
                value="{{ request('search') }}"
                class="border rounded-lg px-4 py-2 w-64"
            >
            <select name="sort" onchange="this.form.submit()" class="border rounded-lg px-4 py-2">
                <option value="created_at" {{ request('sort') === 'created_at' ? 'selected' : '' }}>Новые</option>
                <option value="price" {{ request('sort') === 'price' ? 'selected' : '' }}>Цена</option>
                <option value="name" {{ request('sort') === 'name' ? 'selected' : '' }}>Название</option>
            </select>
            <select name="order" onchange="this.form.submit()" class="border rounded-lg px-4 py-2">
                <option value="desc" {{ request('order') === 'desc' ? 'selected' : '' }}>По убыванию</option>
                <option value="asc" {{ request('order') === 'asc' ? 'selected' : '' }}>По возрастанию</option>
            </select>
            <button type="submit" class="bg-slate-900 text-white px-6 py-2 rounded-lg hover:bg-slate-800">
                Применить
            </button>
        </form>
        
        @if(isset($filters['price_range']))
            <div class="text-sm text-slate-600">
                Цена: от ${{ number_format($filters['price_range']['min'], 0) }} до ${{ number_format($filters['price_range']['max'], 0) }}
            </div>
        @endif
    </div>

    <!-- Products Grid -->
    @if(count($products) > 0)
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
            @foreach($products as $product)
                <div class="group bg-white rounded-xl shadow-sm hover:shadow-lg transition-all duration-300 overflow-hidden border border-slate-100">
                    <!-- Product Image -->
                    <a href="{{ route('ecommerce.product.show', $product->slug) }}" class="block relative aspect-square overflow-hidden bg-slate-50">
                        @if($product->media->isNotEmpty())
                            <img 
                                src="{{ $product->media->first()->url }}" 
                                alt="{{ $product->name }}"
                                class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
                                loading="lazy"
                            >
                        @else
                            <div class="w-full h-full flex items-center justify-center text-slate-300">
                                <svg class="w-16 h-16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                            </div>
                        @endif
                        
                        @if($product->compare_price && $product->compare_price > $product->price)
                            <span class="absolute top-2 right-2 bg-red-500 text-white text-xs font-bold px-2 py-1 rounded">
                                -{{ round((($product->compare_price - $product->price) / $product->compare_price) * 100) }}%
                            </span>
                        @endif
                    </a>

                    <!-- Product Info -->
                    <div class="p-4">
                        <a href="{{ route('ecommerce.product.show', $product->slug) }}">
                            <h3 class="font-semibold text-lg mb-2 line-clamp-2 hover:text-slate-700 transition-colors">
                                {{ $product->name }}
                            </h3>
                        </a>
                        
                        @if($product->description)
                            <p class="text-sm text-slate-600 mb-3 line-clamp-2">
                                {{ Str::limit($product->description, 80) }}
                            </p>
                        @endif

                        <!-- Price -->
                        <div class="flex items-center gap-2 mb-3">
                            <span class="text-xl font-bold text-slate-900">
                                ${{ number_format($product->price, 2) }}
                            </span>
                            @if($product->compare_price && $product->compare_price > $product->price)
                                <span class="text-sm text-slate-400 line-through">
                                    ${{ number_format($product->compare_price, 2) }}
                                </span>
                            @endif
                        </div>

                        <!-- Add to Cart -->
                        <form action="{{ route('ecommerce.cart.add', $product->id) }}" method="POST" class="mt-2">
                            @csrf
                            <input type="hidden" name="quantity" value="1">
                            <button 
                                type="submit" 
                                class="w-full bg-slate-900 text-white px-4 py-2 rounded-lg hover:bg-slate-800 transition-colors flex items-center justify-center gap-2"
                                {{ $product->quantity <= 0 ? 'disabled' : '' }}
                            >
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
                                </svg>
                                {{ $product->quantity <= 0 ? 'Нет в наличии' : 'В корзину' }}
                            </button>
                        </form>
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <!-- Empty State -->
        <div class="text-center py-16">
            <svg class="w-24 h-24 mx-auto text-slate-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
            </svg>
            <h3 class="text-xl font-semibold mb-2">Товары не найдены</h3>
            <p class="text-slate-600 mb-4">Попробуйте изменить параметры поиска или фильтрации</p>
            <a href="{{ route('ecommerce.catalog') }}" class="text-blue-600 hover:underline">Сбросить фильтры</a>
        </div>
    @endif
</div>
@endsection

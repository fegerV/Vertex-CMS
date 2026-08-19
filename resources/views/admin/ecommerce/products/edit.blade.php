@extends('admin.layouts.app')

@section('title', 'Edit Product - VertexCMS')
@section('page_title', 'Edit Product')
@section('page_subtitle', $product->name ?? 'Product')

@section('content')
    <div class="mx-auto max-w-4xl">
        <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
            <a href="{{ route('admin.ecommerce.products.index') }}" class="text-sm text-slate-600 hover:text-slate-950">Back to Products</a>
            <a href="{{ route('admin.ecommerce.products.show', $product) }}" class="rounded-md border border-slate-300 px-4 py-2 text-sm font-medium hover:bg-white">
                View Product
            </a>
        </div>

        <form method="POST" action="{{ route('admin.ecommerce.products.update', $product) }}" class="space-y-5 rounded-lg border border-slate-200 bg-white p-6">
            @csrf
            @method('PUT')
            @include('admin.ecommerce.products.partials.form')
        </form>
    </div>
@endsection

@extends('admin.layouts.app')

@section('title', 'Create Product - VertexCMS')
@section('page_title', 'Create Product')
@section('page_subtitle', 'Add a new product to your store')

@section('content')
    <div class="mx-auto max-w-4xl">
        <a href="{{ route('admin.ecommerce.products.index') }}" class="mb-4 inline-block text-sm text-slate-600 hover:text-slate-950">Back to Products</a>

        <form method="POST" action="{{ route('admin.ecommerce.products.store') }}" class="space-y-5 rounded-lg border border-slate-200 bg-white p-6">
            @csrf
            @include('admin.ecommerce.products.partials.form')
        </form>
    </div>
@endsection

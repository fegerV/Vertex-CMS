@extends('admin.layouts.app')

@section('title', 'Create Term - VertexCMS')
@section('page_title', 'Create Term')
@section('page_subtitle', $taxonomy->name)

@section('content')
    <div class="mx-auto max-w-3xl">
        <form method="POST" action="{{ route('admin.taxonomies.terms.store', $taxonomy) }}" class="vc-panel space-y-6 p-6">
            @csrf
            @include('admin.taxonomies.terms.partials.form')
        </form>
    </div>
@endsection

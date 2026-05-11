@extends('admin.layouts.app')

@section('title', 'Create Taxonomy - VertexCMS')
@section('page_title', 'Create Taxonomy')
@section('page_subtitle', 'Add a new classification for pages')

@section('content')
    <div class="mx-auto max-w-3xl">
        <form method="POST" action="{{ route('admin.taxonomies.store') }}" class="vc-panel space-y-6 p-6">
            @csrf
            @include('admin.taxonomies.partials.form')
        </form>
    </div>
@endsection

@extends('admin.layouts.app')

@section('title', 'Создание пользователя - VertexCMS')
@section('page_title', 'Создание пользователя')
@section('page_subtitle', 'Аккаунт, статус и роли')

@section('content')
    <div class="mx-auto max-w-3xl">
        <form method="POST" action="{{ route('admin.users.store') }}" class="vc-panel space-y-5 p-6">
            @csrf
            @include('admin.users.partials.form')
        </form>
    </div>
@endsection

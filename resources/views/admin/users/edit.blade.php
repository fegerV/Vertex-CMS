@extends('admin.layouts.app')

@section('title', 'Редактирование пользователя - VertexCMS')
@section('page_title', 'Редактирование пользователя')
@section('page_subtitle', $user->email)

@section('content')
    <div class="mx-auto max-w-3xl">
        <form method="POST" action="{{ route('admin.users.update', $user) }}" class="vc-panel space-y-5 p-6">
            @csrf
            @method('PUT')
            @include('admin.users.partials.form')
        </form>
    </div>
@endsection

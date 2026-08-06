@extends('admin.layouts.app')

@section('title', 'Уведомления магазина - VertexCMS')
@section('page_title', 'Уведомления магазина')
@section('page_subtitle', 'Настройка уведомлений для покупателей и администраторов')

@section('content')
<div class="space-y-6">
    {{-- Уведомления о заказах --}}
    <section class="rounded-lg border border-slate-200 bg-white p-6">
        <h3 class="mb-4 text-lg font-medium text-slate-900">Уведомления о заказах</h3>
        
        <div class="space-y-4">
            {{-- Новый заказ --}}
            <div class="flex items-start justify-between rounded-md border border-slate-100 p-4">
                <div class="flex-1">
                    <h4 class="text-sm font-medium text-slate-900">Новый заказ создан</h4>
                    <p class="mt-1 text-xs text-slate-500">Отправляется покупателю после успешного оформления заказа</p>
                </div>
                <label class="relative inline-flex cursor-pointer items-center">
                    <input type="checkbox" checked class="peer sr-only">
                    <div class="peer h-6 w-11 rounded-full bg-slate-200 after:absolute after:left-[2px] after:top-[2px] after:h-5 after:w-5 after:rounded-full after:border after:border-slate-300 after:bg-white after:transition-all after:content-[''] peer-checked:bg-slate-900 peer-checked:after:translate-x-full peer-checked:after:border-white"></div>
                </label>
            </div>

            {{-- Заказ подтвержден --}}
            <div class="flex items-start justify-between rounded-md border border-slate-100 p-4">
                <div class="flex-1">
                    <h4 class="text-sm font-medium text-slate-900">Заказ подтвержден</h4>
                    <p class="mt-1 text-xs text-slate-500">Отправляется покупателю при подтверждении заказа менеджером</p>
                </div>
                <label class="relative inline-flex cursor-pointer items-center">
                    <input type="checkbox" checked class="peer sr-only">
                    <div class="peer h-6 w-11 rounded-full bg-slate-200 after:absolute after:left-[2px] after:top-[2px] after:h-5 after:w-5 after:rounded-full after:border after:border-slate-300 after:bg-white after:transition-all after:content-[''] peer-checked:bg-slate-900 peer-checked:after:translate-x-full peer-checked:after:border-white"></div>
                </label>
            </div>

            {{-- Заказ отправлен --}}
            <div class="flex items-start justify-between rounded-md border border-slate-100 p-4">
                <div class="flex-1">
                    <h4 class="text-sm font-medium text-slate-900">Заказ отправлен</h4>
                    <p class="mt-1 text-xs text-slate-500">Отправляется покупателю при смене статуса на "Отправлен"</p>
                </div>
                <label class="relative inline-flex cursor-pointer items-center">
                    <input type="checkbox" checked class="peer sr-only">
                    <div class="peer h-6 w-11 rounded-full bg-slate-200 after:absolute after:left-[2px] after:top-[2px] after:h-5 after:w-5 after:rounded-full after:border after:border-slate-300 after:bg-white after:transition-all after:content-[''] peer-checked:bg-slate-900 peer-checked:after:translate-x-full peer-checked:after:border-white"></div>
                </label>
            </div>

            {{-- Заказ доставлен --}}
            <div class="flex items-start justify-between rounded-md border border-slate-100 p-4">
                <div class="flex-1">
                    <h4 class="text-sm font-medium text-slate-900">Заказ доставлен</h4>
                    <p class="mt-1 text-xs text-slate-500">Отправляется покупателю при получении заказа</p>
                </div>
                <label class="relative inline-flex cursor-pointer items-center">
                    <input type="checkbox" checked class="peer sr-only">
                    <div class="peer h-6 w-11 rounded-full bg-slate-200 after:absolute after:left-[2px] after:top-[2px] after:h-5 after:w-5 after:rounded-full after:border after:border-slate-300 after:bg-white after:transition-all after:content-[''] peer-checked:bg-slate-900 peer-checked:after:translate-x-full peer-checked:after:border-white"></div>
                </label>
            </div>

            {{-- Заказ отменен --}}
            <div class="flex items-start justify-between rounded-md border border-slate-100 p-4">
                <div class="flex-1">
                    <h4 class="text-sm font-medium text-slate-900">Заказ отменен</h4>
                    <p class="mt-1 text-xs text-slate-500">Отправляется покупателю при отмене заказа</p>
                </div>
                <label class="relative inline-flex cursor-pointer items-center">
                    <input type="checkbox" checked class="peer sr-only">
                    <div class="peer h-6 w-11 rounded-full bg-slate-200 after:absolute after:left-[2px] after:top-[2px] after:h-5 after:w-5 after:rounded-full after:border after:border-slate-300 after:bg-white after:transition-all after:content-[''] peer-checked:bg-slate-900 peer-checked:after:translate-x-full peer-checked:after:border-white"></div>
                </label>
            </div>
        </div>
    </section>

    {{-- Уведомления для администратора --}}
    <section class="rounded-lg border border-slate-200 bg-white p-6">
        <h3 class="mb-4 text-lg font-medium text-slate-900">Уведомления для администратора</h3>
        
        <div class="space-y-4">
            <div class="flex items-start justify-between rounded-md border border-slate-100 p-4">
                <div class="flex-1">
                    <h4 class="text-sm font-medium text-slate-900">Новый заказ (админ)</h4>
                    <p class="mt-1 text-xs text-slate-500">Email-уведомление администратору о новом заказе</p>
                </div>
                <label class="relative inline-flex cursor-pointer items-center">
                    <input type="checkbox" checked class="peer sr-only">
                    <div class="peer h-6 w-11 rounded-full bg-slate-200 after:absolute after:left-[2px] after:top-[2px] after:h-5 after:w-5 after:rounded-full after:border after:border-slate-300 after:bg-white after:transition-all after:content-[''] peer-checked:bg-slate-900 peer-checked:after:translate-x-full peer-checked:after:border-white"></div>
                </label>
            </div>

            <div class="flex items-start justify-between rounded-md border border-slate-100 p-4">
                <div class="flex-1">
                    <h4 class="text-sm font-medium text-slate-900">Низкий остаток товара</h4>
                    <p class="mt-1 text-xs text-slate-500">Уведомление когда количество товара ниже порога</p>
                </div>
                <label class="relative inline-flex cursor-pointer items-center">
                    <input type="checkbox" checked class="peer sr-only">
                    <div class="peer h-6 w-11 rounded-full bg-slate-200 after:absolute after:left-[2px] after:top-[2px] after:h-5 after:w-5 after:rounded-full after:border after:border-slate-300 after:bg-white after:transition-all after:content-[''] peer-checked:bg-slate-900 peer-checked:after:translate-x-full peer-checked:after:border-white"></div>
                </label>
            </div>

            <div class="flex items-start justify-between rounded-md border border-slate-100 p-4">
                <div class="flex-1">
                    <h4 class="text-sm font-medium text-slate-900">Закончился товар</h4>
                    <p class="mt-1 text-xs text-slate-500">Уведомление когда товар полностью закончился</p>
                </div>
                <label class="relative inline-flex cursor-pointer items-center">
                    <input type="checkbox" checked class="peer sr-only">
                    <div class="peer h-6 w-11 rounded-full bg-slate-200 after:absolute after:left-[2px] after:top-[2px] after:h-5 after:w-5 after:rounded-full after:border after:border-slate-300 after:bg-white after:transition-all after:content-[''] peer-checked:bg-slate-900 peer-checked:after:translate-x-full peer-checked:after:border-white"></div>
                </label>
            </div>
        </div>
    </section>

    {{-- Настройки отправки --}}
    <section class="rounded-lg border border-slate-200 bg-white p-6">
        <h3 class="mb-4 text-lg font-medium text-slate-900">Настройки отправки</h3>
        <div class="grid gap-4 md:grid-cols-2">
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Email для уведомлений администратора</label>
                <input type="email" class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm focus:border-slate-900 focus:outline-none" placeholder="admin@example.com">
                <p class="mt-1 text-xs text-slate-500">На этот email будут приходить уведомления о заказах</p>
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">От кого отправлять письма</label>
                <input type="text" class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm focus:border-slate-900 focus:outline-none" placeholder="Магазин">
                <p class="mt-1 text-xs text-slate-500">Имя отправителя в письмах покупателям</p>
            </div>
        </div>
    </section>

    <div class="flex justify-end gap-3">
        <button class="rounded-md border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">Отмена</button>
        <button class="rounded-md bg-slate-900 px-4 py-2 text-sm font-medium text-white hover:bg-slate-800">Сохранить настройки</button>
    </div>
</div>
@endsection

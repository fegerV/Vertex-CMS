@extends('admin.layouts.app')

@section('title', 'Настройки магазина - VertexCMS')
@section('page_title', 'Настройки магазина')
@section('page_subtitle', 'Конфигурация интернет-магазина')

@section('content')
<div class="space-y-6">
    {{-- Основные настройки --}}
    <section class="rounded-lg border border-slate-200 bg-white p-6">
        <h3 class="mb-4 text-lg font-medium text-slate-900">Основные настройки</h3>
        <div class="grid gap-4 md:grid-cols-2">
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Валюта по умолчанию</label>
                <select class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm focus:border-slate-900 focus:outline-none">
                    <option value="RUB">Российский рубль (₽)</option>
                    <option value="USD">Доллар США ($)</option>
                    <option value="EUR">Евро (€)</option>
                    <option value="KZT">Казахстанский тенге (₸)</option>
                </select>
                <p class="mt-1 text-xs text-slate-500">Основная валюта для отображения цен в магазине</p>
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Статус магазина</label>
                <select class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm focus:border-slate-900 focus:outline-none">
                    <option value="active">Активен</option>
                    <option value="maintenance">Режим обслуживания</option>
                    <option value="closed">Закрыт</option>
                </select>
                <p class="mt-1 text-xs text-slate-500">Управление доступностью магазина для покупателей</p>
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Минимальная сумма заказа</label>
                <input type="number" class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm focus:border-slate-900 focus:outline-none" placeholder="0">
                <p class="mt-1 text-xs text-slate-500">Минимальная сумма для оформления заказа (0 = без ограничений)</p>
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Бесплатная доставка от</label>
                <input type="number" class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm focus:border-slate-900 focus:outline-none" placeholder="0">
                <p class="mt-1 text-xs text-slate-500">Сумма заказа, при которой доставка становится бесплатной</p>
            </div>
        </div>
    </section>

    {{-- Настройки склада --}}
    <section class="rounded-lg border border-slate-200 bg-white p-6">
        <h3 class="mb-4 text-lg font-medium text-slate-900">Склад и остатки</h3>
        <div class="grid gap-4 md:grid-cols-2">
            <div>
                <label class="flex items-center gap-2">
                    <input type="checkbox" class="rounded border-slate-300 text-slate-900 focus:ring-slate-900">
                    <span class="text-sm font-medium text-slate-700">Отслеживать остатки товаров</span>
                </label>
                <p class="mt-1 text-xs text-slate-500 ml-6">Автоматически уменьшать количество товара после покупки</p>
            </div>
            <div>
                <label class="flex items-center gap-2">
                    <input type="checkbox" class="rounded border-slate-300 text-slate-900 focus:ring-slate-900">
                    <span class="text-sm font-medium text-slate-700">Разрешить заказ отсутствующих товаров</span>
                </label>
                <p class="mt-1 text-xs text-slate-500 ml-6">Покупатели смогут заказывать товары с нулевым остатком</p>
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Порог уведомления о низком остатке</label>
                <input type="number" class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm focus:border-slate-900 focus:outline-none" value="5">
                <p class="mt-1 text-xs text-slate-500">При достижении этого количества товар будет помечен как "мало"</p>
            </div>
        </div>
    </section>

    {{-- Настройки корзины --}}
    <section class="rounded-lg border border-slate-200 bg-white p-6">
        <h3 class="mb-4 text-lg font-medium text-slate-900">Корзина и оформление</h3>
        <div class="grid gap-4 md:grid-cols-2">
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Время жизни корзины (часы)</label>
                <input type="number" class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm focus:border-slate-900 focus:outline-none" value="24">
                <p class="mt-1 text-xs text-slate-500">Через сколько часов корзина очищается при неактивности</p>
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Максимальное количество товаров в корзине</label>
                <input type="number" class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm focus:border-slate-900 focus:outline-none" value="100">
                <p class="mt-1 text-xs text-slate-500">Ограничение на количество позиций в одном заказе</p>
            </div>
        </div>
    </section>

    <div class="flex justify-end gap-3">
        <button class="rounded-md border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">Отмена</button>
        <button class="rounded-md bg-slate-900 px-4 py-2 text-sm font-medium text-white hover:bg-slate-800">Сохранить настройки</button>
    </div>
</div>
@endsection

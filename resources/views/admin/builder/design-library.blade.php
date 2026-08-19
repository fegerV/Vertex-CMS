@extends('admin.layouts.app')

@section('title', 'Design Library - VertexCMS')
@section('page_title', 'Design Library')
@section('page_subtitle', 'Шаблоны, стартеры и пресеты для page builder')
@section('page_wrap_class', 'vc-page-wrap-builder')
@section('body_class', 'vc-admin-body-builder')

@section('content')
<div
    id="design-library"
    data-vc-design-library
    data-workspace='@json($workspace)'
    data-api-url="{{ route('admin.pages.builder.design-library.api') }}"
    class="vc-design-library-host"
>
    <div class="vc-panel vc-panel-strong p-6 text-sm text-[var(--vc-text-soft)]">
        Loading design library...
    </div>
</div>
@endsection

@section('styles')
@parent
<style>
    /* Enhanced Design Library Styles */
    .vc-design-library-host {
        min-height: 100vh;
        overflow-y: auto;
        padding: clamp(1rem, 2vw, 2rem);
        background: linear-gradient(135deg, #0f172a 0%, #1e293b 50%, #0f172a 100%);
        position: relative;
    }
    
    .vc-design-library-host::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 1px;
        background: linear-gradient(90deg, transparent, rgba(45, 212, 191, 0.3), transparent);
    }
    
    .vc-design-library-shell {
        max-width: 1680px;
        margin: 0 auto;
    }
    
    /* Enhanced Header Section */
    .vc-design-library-header {
        background: linear-gradient(135deg, rgba(45, 212, 191, 0.08), rgba(56, 189, 248, 0.05));
        border: 1px solid rgba(45, 212, 191, 0.2);
        backdrop-filter: blur(12px);
    }
    
    .vc-design-library-title {
        font-size: clamp(1.5rem, 3vw, 2rem);
        font-weight: 700;
        background: linear-gradient(135deg, #2dd4bf, #38bdf8);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
    }
    
    /* Enhanced Stats Cards */
    .vc-stat-card {
        background: linear-gradient(135deg, rgba(30, 41, 59, 0.8), rgba(15, 23, 42, 0.9));
        border: 1px solid rgba(45, 212, 191, 0.15);
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        position: relative;
        overflow: hidden;
    }
    
    .vc-stat-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 2px;
        background: linear-gradient(90deg, #2dd4bf, #38bdf8);
        opacity: 0;
        transition: opacity 0.3s ease;
    }
    
    .vc-stat-card:hover {
        transform: translateY(-4px);
        border-color: rgba(45, 212, 191, 0.4);
        box-shadow: 0 20px 40px rgba(0, 0, 0, 0.3), 0 0 30px rgba(45, 212, 191, 0.1);
    }
    
    .vc-stat-card:hover::before {
        opacity: 1;
    }
    
    .vc-stat-value {
        font-size: clamp(1.75rem, 4vw, 2.5rem);
        font-weight: 700;
        color: #2dd4bf;
    }
    
    .vc-stat-label {
        font-size: 0.7rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.15em;
        color: rgba(148, 163, 184, 0.8);
    }
    
    /* Enhanced Tab Navigation */
    .vc-builder-tab-row {
        display: flex;
        flex-wrap: wrap;
        gap: 0.5rem;
        padding: 0.5rem;
        background: rgba(15, 23, 42, 0.5);
        border-radius: 1rem;
        border: 1px solid rgba(45, 212, 191, 0.1);
    }
    
    .vc-builder-tab {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
        border: 1px solid transparent;
        border-radius: 0.75rem;
        padding: 0.6rem 1.2rem;
        background: transparent;
        font-size: 0.8rem;
        font-weight: 600;
        color: rgba(148, 163, 184, 0.9);
        transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
        cursor: pointer;
        position: relative;
        overflow: hidden;
    }
    
    .vc-builder-tab::before {
        content: '';
        position: absolute;
        inset: 0;
        background: linear-gradient(135deg, rgba(45, 212, 191, 0.1), rgba(56, 189, 248, 0.1));
        opacity: 0;
        transition: opacity 0.2s ease;
    }
    
    .vc-builder-tab:hover {
        color: #fff;
    }
    
    .vc-builder-tab:hover::before {
        opacity: 1;
    }
    
    .vc-builder-tab-active {
        background: linear-gradient(135deg, rgba(45, 212, 191, 0.2), rgba(56, 189, 248, 0.15));
        border-color: rgba(45, 212, 191, 0.4);
        color: #2dd4bf;
        box-shadow: 0 0 20px rgba(45, 212, 191, 0.2);
    }
    
    .vc-builder-tab-active::before {
        opacity: 1;
    }
    
    /* Enhanced Search & Filter */
    .vc-search-input,
    .vc-category-select {
        background: rgba(15, 23, 42, 0.8);
        border: 1px solid rgba(45, 212, 191, 0.2);
        border-radius: 0.75rem;
        padding: 0.6rem 1rem;
        color: #fff;
        font-size: 0.85rem;
        transition: all 0.2s ease;
    }
    
    .vc-search-input:focus,
    .vc-category-select:focus {
        outline: none;
        border-color: rgba(45, 212, 191, 0.5);
        box-shadow: 0 0 0 3px rgba(45, 212, 191, 0.1);
    }
    
    .vc-search-input::placeholder {
        color: rgba(148, 163, 184, 0.6);
    }
    
    /* Enhanced Item Cards */
    .vc-design-library-card {
        background: linear-gradient(135deg, rgba(30, 41, 59, 0.6), rgba(15, 23, 42, 0.8));
        border: 1px solid rgba(45, 212, 191, 0.1);
        border-radius: 1.25rem;
        overflow: hidden;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        position: relative;
    }
    
    .vc-design-library-card::before {
        content: '';
        position: absolute;
        inset: 0;
        border-radius: 1.25rem;
        padding: 1px;
        background: linear-gradient(135deg, rgba(45, 212, 191, 0.3), transparent, rgba(56, 189, 248, 0.2));
        -webkit-mask: linear-gradient(#fff 0 0) content-box, linear-gradient(#fff 0 0);
        mask: linear-gradient(#fff 0 0) content-box, linear-gradient(#fff 0 0);
        -webkit-mask-composite: xor;
        mask-composite: exclude;
        opacity: 0.5;
        transition: opacity 0.3s ease;
    }
    
    .vc-design-library-card:hover {
        transform: translateY(-6px) scale(1.01);
        border-color: rgba(45, 212, 191, 0.4);
        box-shadow: 0 24px 58px rgba(0, 0, 0, 0.4), 0 0 40px rgba(45, 212, 191, 0.15);
    }
    
    .vc-design-library-card:hover::before {
        opacity: 1;
    }
    
    .vc-design-library-thumb {
        display: block;
        width: 100%;
        overflow: hidden;
        background: linear-gradient(135deg, #0f172a, #1e293b);
        aspect-ratio: 16 / 9;
        position: relative;
    }
    
    .vc-design-library-thumb img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    }
    
    .vc-design-library-card:hover .vc-design-library-thumb img {
        transform: scale(1.08);
        filter: saturate(1.15) brightness(1.05);
    }
    
    .vc-design-library-thumb-fallback,
    .vc-design-library-thumb-preview {
        display: grid;
        min-height: 14rem;
        place-items: center;
        padding: 1.5rem;
        background: linear-gradient(135deg, #0f172a, #0f766e 62%, #22c55e);
        color: #fff;
        font-size: clamp(1.25rem, 3vw, 2.75rem);
        font-weight: 800;
        text-align: center;
    }
    
    /* Enhanced Badges */
    .vc-builder-badge {
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        border-radius: 0.5rem;
        padding: 0.25rem 0.6rem;
        font-size: 0.65rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.08em;
        transition: all 0.2s ease;
    }
    
    .vc-builder-badge {
        background: rgba(148, 163, 184, 0.15);
        color: rgba(148, 163, 184, 0.9);
    }
    
    .vc-builder-badge-quiet {
        background: rgba(148, 163, 184, 0.1);
        color: rgba(148, 163, 184, 0.7);
    }
    
    .vc-builder-badge-active {
        background: linear-gradient(135deg, rgba(45, 212, 191, 0.2), rgba(34, 197, 94, 0.2));
        color: #2dd4bf;
        border: 1px solid rgba(45, 212, 191, 0.3);
    }
    
    /* Enhanced Buttons */
    .vc-button {
        border-radius: 0.5rem;
        padding: 0.5rem 1rem;
        font-size: 0.8rem;
        font-weight: 600;
        transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
        position: relative;
        overflow: hidden;
    }
    
    .vc-button-secondary {
        background: rgba(148, 163, 184, 0.1);
        border: 1px solid rgba(148, 163, 184, 0.2);
        color: rgba(148, 163, 184, 0.9);
    }
    
    .vc-button-secondary:hover {
        background: rgba(148, 163, 184, 0.2);
        border-color: rgba(148, 163, 184, 0.4);
        color: #fff;
        transform: translateY(-1px);
    }
    
    .vc-button-primary {
        background: linear-gradient(135deg, #2dd4bf, #14b8a6);
        border: 1px solid transparent;
        color: #0f172a;
    }
    
    .vc-button-primary:hover {
        background: linear-gradient(135deg, #5eead4, #2dd4bf);
        box-shadow: 0 0 20px rgba(45, 212, 191, 0.4);
        transform: translateY(-1px);
    }
    
    .vc-button-danger {
        background: rgba(244, 63, 94, 0.15);
        border: 1px solid rgba(244, 63, 94, 0.3);
        color: #fb7185;
    }
    
    .vc-button-danger:hover {
        background: rgba(244, 63, 94, 0.25);
        border-color: rgba(244, 63, 94, 0.5);
        box-shadow: 0 0 15px rgba(244, 63, 94, 0.3);
    }
    
    /* Enhanced Modal */
    .vc-builder-modal {
        backdrop-filter: blur(8px);
        background: rgba(0, 0, 0, 0.7);
    }
    
    .vc-builder-modal-card {
        background: linear-gradient(135deg, rgba(30, 41, 59, 0.95), rgba(15, 23, 42, 0.98));
        border: 1px solid rgba(45, 212, 191, 0.2);
        border-radius: 1.5rem;
        box-shadow: 0 40px 80px rgba(0, 0, 0, 0.5), 0 0 60px rgba(45, 212, 191, 0.1);
    }
    
    /* Enhanced Field Inputs */
    .vc-field-label {
        display: block;
        margin-bottom: 0.4rem;
        font-size: 0.8rem;
        font-weight: 600;
        color: rgba(148, 163, 184, 0.9);
        text-transform: uppercase;
        letter-spacing: 0.08em;
    }
    
    .vc-input,
    .vc-select {
        width: 100%;
        background: rgba(15, 23, 42, 0.6);
        border: 1px solid rgba(45, 212, 191, 0.2);
        border-radius: 0.5rem;
        padding: 0.6rem 0.8rem;
        color: #fff;
        font-size: 0.9rem;
        transition: all 0.2s ease;
    }
    
    .vc-input:focus,
    .vc-select:focus {
        outline: none;
        border-color: rgba(45, 212, 191, 0.5);
        box-shadow: 0 0 0 3px rgba(45, 212, 191, 0.1);
    }
    
    /* Empty State */
    .vc-builder-empty {
        background: rgba(15, 23, 42, 0.5);
        border: 1px dashed rgba(45, 212, 191, 0.2);
        border-radius: 1.25rem;
        color: rgba(148, 163, 184, 0.7);
    }
    
    /* Scrollbar Styling */
    .vc-design-library-host::-webkit-scrollbar {
        width: 8px;
    }
    
    .vc-design-library-host::-webkit-scrollbar-track {
        background: rgba(15, 23, 42, 0.5);
    }
    
    .vc-design-library-host::-webkit-scrollbar-thumb {
        background: rgba(45, 212, 191, 0.3);
        border-radius: 4px;
    }
    
    .vc-design-library-host::-webkit-scrollbar-thumb:hover {
        background: rgba(45, 212, 191, 0.5);
    }
    
    /* Animation for cards on load */
    @keyframes fadeInUp {
        from {
            opacity: 0;
            transform: translateY(20px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
    
    .vc-design-library-card {
        animation: fadeInUp 0.4s ease forwards;
    }
    
    .vc-design-library-card:nth-child(1) { animation-delay: 0.05s; }
    .vc-design-library-card:nth-child(2) { animation-delay: 0.1s; }
    .vc-design-library-card:nth-child(3) { animation-delay: 0.15s; }
    .vc-design-library-card:nth-child(4) { animation-delay: 0.2s; }
    .vc-design-library-card:nth-child(5) { animation-delay: 0.25s; }
    .vc-design-library-card:nth-child(6) { animation-delay: 0.3s; }
</style>
@endsection

@extends('layouts.app')

@section('content')
<link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@400;500;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.salla.network/fonts/sallaicons.css"/>

<style>
    :root {
        --primary-teal: #62D0B6;
        --primary-teal-dark: #004d5a;
        --text-dark: #333333;
        --text-medium: #666666;
        --text-light: #999999;
        --border-color: #eeeeee;
        --white: #ffffff;
        --bg-light: #fcfcfc; /* Consistent background for the whole workspace */
    }

    html, body {
        /* Ensures the background covers the full height of the browser */
        height: 100%;
        margin: 0;
        padding: 0;
    }

    body {
        font-family: 'Tajawal', sans-serif;

        background-image: url("{{asset('images/Pattern_transparent.png')}}");
        background-repeat: repeat;
        background-attachment: fixed;
        background-size: 800px;

        min-height: 100vh; /* Ensures the body takes at least the full viewport height */

        background-color: var(--bg-light);
        display: flex;
    }

    #app {
        flex: 1; /* Pushes the content to fill the space */
        display: flex;
        flex-direction: column;
    }
    .s-icon {
        font-family: 'sallaicons' !important;
        font-style: normal;
        font-size: 20px;
        vertical-align: middle;
    }

    .boxes-container {
        max-width: 1200px;
        margin: 24px auto;
        padding: 0 20px;
    }

    /* --- Unified Header and Search Card --- */
    .unified-card {
        background: var(--white);
        border: 1px solid var(--border-color);
        border-radius: 8px;
        overflow: hidden;
        margin-bottom: 24px;
    }

    .header-top {
        padding: 16px 20px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 30px;
        border-bottom: 1px solid var(--border-color);
    }

    .header-title {
        font-size: 20px;
        font-weight: 700;
        color: var(--text-dark);
        display: flex;
        align-items: center;
        gap: 12px;
        margin: 0;
        white-space: nowrap;
    }

    /* --- Elongated Search Bar --- */
    .search-wrapper {
        position: relative;
        flex: 1;
        max-width: 700px;
        min-width: 300px;
        display: flex;
        align-items: center;
    }

    .search-input {
        width: 100%;
        padding: 12px 15px 12px 45px;
        border: 1px solid var(--border-color);
        border-radius: 10px;
        font-family: 'Tajawal';
        font-size: 14px;
        color: var(--text-dark);
        text-align: right;
        background-color: var(--white);
        transition: all 0.3s ease-in-out;
    }

    .search-input:hover,
    .search-input:focus {
        outline: none;
        border-color: var(--primary-teal);
        box-shadow: 0 0 0 4px rgba(98, 208, 182, 0.15); /* Salla Glow Effect */
    }

    .search-icon {
        position: absolute;
        left: 15px;
        top: 50%;
        transform: translateY(-50%);
        font-size: 18px;
        color: var(--text-light);
        pointer-events: none;
    }

    /* --- Add Button Style --- */
    .btn-teal {
        background: var(--primary-teal);
        color: white;
        padding: 10px 24px;
        border-radius: 8px; /* Fixed: Changed from 30px to 8px for the better shape */
        text-decoration: none !important;
        font-weight: 700;
        border: none;
        font-size: 14px;
        transition: background-color 0.2s ease;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        white-space: nowrap;
    }

    .btn-teal:hover {
        background-color: var(--primary-teal-dark);
        color: white;
        text-decoration: none !important;
    }

    /* --- Box Grid Section --- */
    .box-content-area {
        padding: 24px 20px;
        min-height: 400px;
    }

    .boxes-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
        gap: 20px;
    }

    .box-card {
        background: var(--white);
        border: 1px solid var(--border-color);
        border-radius: 12px;
        overflow: hidden;
        transition: transform 0.2s, box-shadow 0.2s;
    }

    .box-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 5px 15px rgba(0,0,0,0.05);
    }

    .box-image { width: 100%;
        height: 180px;
        object-fit: cover;
        background: #f5f5f5;
    }

    .box-info {
        padding: 16px;
    }

    .box-name {
        font-size: 15px;
        font-weight: 700;
        color: var(--text-dark);
        margin-bottom: 8px;
    }

    .box-price {
        color: var(--primary-teal);
        font-weight: 700;
        font-size: 18px;
        margin-bottom: 16px;
    }

    .box-actions {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding-top: 12px;
        border-top: 1px solid var(--border-color);
    }

    /* Add this to your index.blade.php styles */
    .box-actions a i.sicon-pencil:hover {
        color: var(--primary-teal);
        transform: scale(1.2);
        transition: all 0.2s ease;
    }

    /* --- Custom Styled Buttons --- */
    .btn-preview-styled {
        height: 40px;
        padding: 0 20px;
        background-color: white;
        color: #004d5a;
        border: 1px solid var(--border-color);
        border-radius: 30px;
        gap: 8px;
        font-size: 14px;
        display: inline-flex;
        align-items: center;
        text-decoration: none !important;
        transition: all 0.25s ease;
    }

    .btn-preview-styled:hover {
        border-color: var(--primary-teal);
        box-shadow: 0 0 0 4px rgba(98, 208, 182, 0.2);
        color: #004d5a;
    }

    .btn-delete-styled {
        width: 44px;
        height: 40px;
        background-color: #ff5f5f;
        color: white;
        border-radius: 12px;
        border: none;
        cursor: pointer;
        transition: all 0.25s ease;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .btn-delete-styled:hover {
        background-color: #2b2d33;
    }

    /* --- Empty State (Matches Dashboard Style) --- */
    .empty-state {
        text-align: center;
        padding: 80px 0;
    }
    .empty-text {
        color: var(--text-light);
        font-size: 14px;
        margin-top: 20px;
        }
</style>

<div class="boxes-container" dir="rtl">
    <div class="unified-card">
        <div class="header-top">
            <h2 class="header-title">
                <i class="s-icon sicon-box-bankers"></i> باقاتي
            </h2>

            <div class="search-wrapper">
                <input type="text" id="boxSearch" class="search-input" placeholder="ابحث عن باقة..." onkeyup="filterBoxes()">
                <i class="s-icon sicon-search search-icon"></i>
            </div>

            <a href="{{ url('/dashboard') }}" class="btn-teal">
                إضافة باقة
            </a>
        </div>

        <div class="box-content-area">
            @if($boxes->isEmpty())
                <div class="empty-state">
                    <i class="s-icon sicon-inbox" style="font-size: 64px; color: #d1d1d1; opacity: 0.5;"></i>
                    <div class="empty-text">
                        <strong style="display: block; color: #333; margin-bottom: 4px;">لم يتم إضافة أي باقات بعد</strong>
                        ابدأ بإضافة باقة جديدة للباقة
                    </div>
                </div>
            @else
                <div class="boxes-grid" id="boxesGrid">
                    @foreach($boxes as $box)
                        <div class="box-card" data-name="{{ $box->name }}">
                            <img src="{{ $box->image_url ?: 'https://via.placeholder.com/300x180' }}" class="box-image">
                            <div class="box-info">
                                <div class="box-name">{{ $box->name }}</div>
                                <div class="box-price">{{ number_format($box->price, 2) }} ريال</div>

                                <div class="box-actions">
                                    <div style="display: flex; gap: 10px; align-items: center;">
                                        <a href="{{ url('/boxes/'.$box->id.'/edit') }}" style="color: #666; text-decoration: none;">
                                            <i class="s-icon sicon-pencil"></i>
                                        </a>

                                        <a href="{{ url('/boxes/'.$box->id) }}" class="btn-preview-styled">
                                            <span>معاينة</span>
                                            <i class="s-icon sicon-eye"></i>
                                        </a>
                                    </div>

                                    <form action="{{ url('/boxes/'.$box->id) }}" method="POST" onsubmit="return confirm('هل أنت متأكد من الحذف؟')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn-delete-styled">
                                            <i class="s-icon sicon-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</div>

<script>
    function filterBoxes() {
        const query = document.getElementById('boxSearch').value.toLowerCase();
        const cards = document.querySelectorAll('.box-card');
        cards.forEach(card => {
            const name = card.getAttribute('data-name').toLowerCase();
            card.style.display = name.includes(query) ? "block" : "none";
        });
    }
</script>
@endsection

@extends('layouts.app')

@section('heder-overrides')

<link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@400;500;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.salla.network/fonts/sallaicons.css"/><style>
    /* 1. Global Reset for this page */
    * {
        font-family: 'Tajawal', sans-serif !important;
    }
    body {
        font-family: 'Tajawal', sans-serif;

        background-image: url("{{asset('images/Pattern_transparent.png')}}");
        background-repeat: repeat;
        background-attachment: fixed;
        background-size: 800px;

        background-color: var(--bg-light);
    }

    .show-container {
        max-width: 650px;
        margin: 40px auto;
        padding: 0 15px;
    }

    /* 2. Main Card Styling */
    .package-card {
        background: white;
        border-radius: 20px;
        overflow: hidden;
        border: 1px solid #f0f0f0;
        box-shadow: 0 10px 30px rgba(0,0,0,0.05);
    }

    .image-container {
        padding: 20px 20 px 0 20px;
        background: white;
    }
    .package-main-img {
        width: 100%;
        height: 350px;
        object-fit: cover;
        border-radius: 12px;
        border: 1px solid #f0f0f0;
    }

    .package-body {
        padding: 24px;
    }

    .package-header {
        display: flex;
        flex-direction: row-reverse; /* Aligns title to the right and price to the left */
        justify-content: space-between;
        align-items: center;
        margin-bottom: 30px;
        border-bottom: 1px solid #eeeeee;
        padding-bottom: 15px;
    }

    .package-title {
        font-size: 24px;
        font-weight: 700;
        color: #333;
        margin: 0;
        text-align: right;
    }

    .package-price {
        font-size: 22px;
        font-weight: 700;
        color: #62D0B6;
        text-align: left;
    }

    /* 3. Elements and Products */
    .element-section {
        margin-bottom: 25px;
    }

    .element-header {
        display: flex;
        flex-direction: row;
        justify-content: flex-start;
        align-items: center;
        gap: 12px;
        direction: rtl;
    }

    .element-name {
        font-size: 14px;
        font-weight: 700;
        color: #555;
        text-align: right;
    }

    .element-number {
        background: #62D0B6;
        color: #ffffff;
        width: 24px;
        height: 24px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 11px;
    }

    /* Horizontal Scrolling */
    .products-scroll-wrapper {
        display: flex;
        flex-wrap: nowrap;
        overflow-x: auto;
        gap: 15px;
        padding: 10px 5px 15px 5px;
        -webkit-overflow-scrolling: touch;
        direction: rtl;
        scroll-width: thin;
        scrollbar-color: #ccc transparent;

    }

    .products-scroll-wrapper::-webkit-scrollbar {
        height: 4px;
    }

    .products-scroll-wrapper::-webkit-scrollbar-track {
        background: #f0f0f0;
        border-radius: 10px;
    }

    .products-scroll-wrapper::-webkit-scrollbar-thumb {
        background: #ccc;
        border-radius: 10px;
        border: 3px solid transparent;
        background-clip: content-box;
    }

    .products-scroll-wrapper::-webkit-scrollbar-thumb:hover {
        background-color: #62D0B6;
        /* border-radius: 10px; */

    }

    .products-scroll-wrapper::-webkit-scrollbar-thumb:active {
        background: #004d5a;
    }

    .product-option-card {
        flex: 0 0 120px;
        background: #ffffff;
        border: 1px solid #eeeeee;
        border-radius: 12px;
        padding: 10px;
        text-align: center;
        display: flex;
        flex-direction: column;
        align-items: center;
        transition: all 0.3s ease;
        cursor: pointer;
    }

    .product-option-card:hover {
        border-color: #62D0B6;
        background-color: #f6fdfc;
        transform: translateY(-2px);
    }

    /* The Selection Circle from your photo */
    .product-img-box {
        width: 90px;
        height: 90px;
        margin-bottom: 8px;
    }

    .product-img-box img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        border-radius: 8px;
    }

    .product-label {
        font-size: 13px;
        font-weight: 600;
        color: #333;
        margin-bottom: 2px;
        linee-height: 1.2;
    }

    .product-cost {
        font-size: 11px;
        color: #62D0B6;
        font-weight: 700;
    }

    .btn-submit {
        background: #62D0B6;
        color: white;
        border: none;
        flex: 3;
        padding: 14px;
        border-radius: 12px;
        font-size: 16px;
        font-weight: 700;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        font-family: 'Tajawal', sans-serif;
    }

    .action-footer {
        display: flex;
        gap: 12px;
        align-items: center
        margin-top: 25px;
    }

    .btn-back {
        background: #f9f9f9;
        color: #555;
        width: 52px;
        height: 52px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        text-decoration: none !important;
        border: 1px solid #eee;
        transition: all 0.3s ease;
        flex-shrink: 0;
    }

    .btn-back:hover {
        background: #555; /* Changes to teal on hover as requested */
        color: white;
        border-color: #555;
        text-decoration: none !important;
    }

</style>
@endsection

@section('content')
<div class="show-container" dir="rtl">
    <div class="package-card">

        <div class="image-container">
            <img src="{{ $box->image_url }}" class="package-main-img">
        </div>

        <div class="package-body">
            <div class="package-header">
                <div class="package-price">{{ number_format($box->price, 2) }} ﷼</div>
                <h1 class="package-title">{{ $box->name }}</h1>
            </div>

            @foreach($box->elements as $element)
                <div class="element-section">
                    <div class="element-header">
                        <span class="element-number">{{ $loop->iteration }}</span>
                        <span class="element-name">{{ $element->element_name }}</span>
                    </div>

                    <div class="products-scroll-wrapper">
                        @foreach($element->products as $product)
                            <div class="product-option-card">
                                <div class="selection-ring">
                                    <div class="selection-dot"></div>
                                </div>
                                <div class="product-img-box">
                                    <img src="{{ $product->image_url }}">
                                </div>
                                <div class="product-label">{{ $product->name }}</div>
                                <div class="product-cost">{{ number_format($product->price, 2) }} ﷼</div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endforeach

            <div class="action-footer">
                <button class="btn-submit">
                    <i class="s-icon sicon-cart"></i>
                    أضف إلى السلة
                </button>

                <a href="{{ url('/boxes') }}" class="btn-back">
                    <i class="s-icon sicon-arrow-left"></i>
                </a>
            </div>
        </div>
    </div>
</div>
@endsection

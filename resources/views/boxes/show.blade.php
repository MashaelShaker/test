@extends('layouts.app')

@section('content')
<link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@400;500;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.salla.network/fonts/sallaicons.css"/>

<style>
    :root {
        --primary-teal: #62D0B6;
        --text-dark: #2d3748;
        --border-color: #e2e8f0;
    }
    body { background-color: #f7fafc; font-family: 'Tajawal', sans-serif; }
    .show-card {
        max-width: 600px;
        margin: 50px auto;
        background: white;
        border-radius: 20px;
        box-shadow: 0 10px 25px rgba(0,0,0,0.05);
        padding: 40px;
        text-align: center;
    }
    .badge-custom {
        background: var(--primary-teal);
        color: white;
        padding: 6px 16px;
        border-radius: 20px;
        font-size: 13px;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        margin-bottom: 20px;
    }
    .box-title { font-size: 28px; font-weight: 700; color: var(--text-dark); margin-bottom: 10px; }
    .box-price { font-size: 32px; font-weight: 700; color: var(--primary-teal); margin-bottom: 30px; }
    .divider { border: 0; border-top: 1px solid #eee; margin: 30px 0; }

    .element-row { margin-bottom: 24px; text-align: right; }
    .element-header { display: flex; justify-content: flex-end; align-items: center; gap: 10px; margin-bottom: 15px; }
    .element-number {
        background: var(--primary-teal);
        color: white;
        width: 28px;
        height: 28px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 13px;
    }
    .product-option-card {
        border: 1px solid var(--border-color);
        border-radius: 12px;
        padding: 15px;
        display: flex;
        justify-content: flex-end;
        align-items: center;
        gap: 15px;
        margin-bottom: 10px;
    }
    .product-img { width: 60px; height: 60px; border-radius: 8px; object-fit: cover; }
    .radio-circle {
        width: 20px;
        height: 20px;
        border: 2px solid var(--primary-teal);
        border-radius: 50%;
        position: relative;
    }
    .radio-circle::after {
        content: '';
        position: absolute;
        top: 3px; left: 3px;
        width: 10px; height: 10px;
        background: var(--primary-teal);
        border-radius: 50%;
    }
    .btn-add-cart {
        background: var(--primary-teal);
        color: white;
        width: 100%;
        padding: 15px;
        border-radius: 10px;
        border: none;
        font-weight: 700;
        font-size: 16px;
        margin-top: 20px;
        cursor: pointer;
    }
</style>

<div class="show-card" dir="rtl">
    <div class="badge-custom"><i class="s-icon sicon-box-bankers"></i> باقة مخصصة</div>
    <h1 class="box-title">{{ $box->name }}</h1>
    <div class="box-price">{{ number_format($box->price, 2) }} ريال</div>

    <div class="divider"></div>

    @foreach($box->elements as $element)
    <div class="element-row">
        <div class="element-header">
            <span style="font-weight: 700;">{{ $element->element_name }}</span>
            <div class="element-number">{{ $loop->iteration }}</div>
        </div>

        @foreach($element->products as $product)
        <div class="product-option-card">
            <div style="text-align: left; flex: 1;">
                <div style="font-weight: 700; color: #4a5568;">{{ $product->name }}</div>
                <div style="color: var(--primary-teal); font-size: 14px;">{{ number_format($product->price, 2) }} ريال</div>
            </div>
            <img src="{{ $product->image_url }}" class="product-img">
            <div class="radio-circle"></div>
        </div>
        @endforeach
    </div>
    @endforeach

    <button class="btn-add-cart">
        <i class="s-icon sicon-cart"></i> أضف إلى السلة
    </button>
</div>
@endsection

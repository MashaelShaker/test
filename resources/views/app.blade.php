<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Scripts -->
    <script src="{{ asset('js/app.js') }}" defer></script>

    <!-- Fonts -->
    <link rel="dns-prefetch" href="//fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css?family=Nunito" rel="stylesheet">

    <!-- Styles -->
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/daisyui@1.14.5/dist/full.css" rel="stylesheet" type="text/css" />
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2/dist/tailwind.min.css" rel="stylesheet" type="text/css" />
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>إنشاء باقة جديدة - سلة</title>
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.salla.network/fonts/sallaicons.css" />

    <style>
        /* ==================== أيقونات سلة ==================== */
        .s-icon {
            font-family: 'sallaicons' !important;
            font-style: normal;
            vertical-align: middle;
        }
        .upload-area {
            border: 2px dashed var(--border-color);
            border-radius: 8px;
            padding: 40px 20px;
            text-align: center;
            cursor: pointer;
            transition: all 0.2s;
            background: var(--gray-50);
            margin-bottom: 12px;
        }
        .upload-area:hover {
            border-color: var(--primary-teal);
            background: var(--primary-teal-light);
        }
        .upload-area.has-file {
            border-color: var(--primary-teal);
            background: var(--primary-teal-light);
            padding: 0;
            height: 300px;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            position: relative;
        }
        .upload-area.has-file img {
            max-width: 100%;
            max-height: 100%;
            object-fit: contain;
        }
        .upload-area.has-file .upload-content {
            display: none;
        }
        .upload-icon {
            font-size: 48px;
            color: var(--text-light);
            margin-bottom: 12px;
        }
        .upload-area h4 {
            font-size: 14px;
            font-weight: 600;
            color: var(--text-dark);
            margin-bottom: 4px;
        }
        .upload-area p {
            font-size: 12px;
            color: var(--text-light);
            margin: 0;
        }
        .preview-package-image {
            width: 100%;
            height: 200px;
            border-radius: 8px;
            object-fit: cover;
            margin-bottom: 16px;
            background: var(--gray-100);
            display: none;
        }
        .preview-package-image.visible {
            display: block;
        }
        :root {
            --primary-teal: #62D0B6;
            --accent-teal: #00b894;
            --primary-teal-hover: #00363f;
            --primary-teal-light: #e6f7f4;
            --text-dark: #333333;
            --text-medium: #666666;
            --text-light: #999999;
            --gray-50: #fcfcfc;
            --gray-100: #f5f5f5;
            --border-color: #eeeeee;
            --white: #ffffff;
            --red: #ff5f5f;
            --shadow-sm: 0 1px 3px rgba(0, 0, 0, 0.05);
        }
        /* ==================== إعادة تعيين ==================== */
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }
        body {
            font-family: 'Tajawal', sans-serif;

            background-image: url("{{asset('images/Pattern_transparent.png')}}");
            background-repeat: repeat;
            background-attachment: fixed;
            background-size: 800px;

            background-color: var(--gray-50);
            color: var(--text-dark);
            font-size: 14px;
            line-height: 1.6;
            padding-top: 76px;
            padding-bottom: 0 !important;
        }

        main {
            padding-bottom: 100px !important; /* Creates invisible scroll space so footer doesn't hide content */
        }
        .navbar {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            height: 70px;
            z-index: 1100;
            background: #ffffff !important;
            border-bottom: 1px solid var(--border-color);
        }

        /* ==================== التخطيط الأساسي ==================== */
        .container {
            display: flex;
            max-width: 1400px;
            margin: 0 auto;
            gap: 24px;
            padding: 24px;
            align-items: flex-start;
        }
        .main-content {
            flex: 1;
            min-width: 0;
        }
        .sidebar {
            width: 380px;
            flex-shrink: 0;
            position: sticky;
            top: 24px;
        }
        /* ==================== البطاقات ==================== */
        .card {
            background: var(--white);
            border-radius: 8px;
            border: 1px solid var(--border-color);
            margin-bottom: 16px;
            overflow: hidden;
        }
        .card-header {
            padding: 16px 20px;
            border-bottom: 1px solid var(--border-color);
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: var(--white);
        }
        .card-header h3 {
            margin: 0;
            font-size: 15px;
            font-weight: 700;
            color: var(--text-dark);
        }
        .card-body {
            padding: 20px;
        }
        /* ==================== معاينة الباقة ==================== */
        .preview-card {
            background: var(--white);
            border-radius: 8px;
            border: 1px solid var(--border-color);
            padding: 20px;
        }
        .preview-header {
            text-align: center;
            padding-bottom: 16px;
            border-bottom: 1px solid var(--border-color);
            margin-bottom: 16px;
        }
        .preview-label {
            font-size: 12px;
            color: var(--text-light);
            margin-bottom: 4px;
        }
        .preview-title {
            font-size: 18px;
            font-weight: 700;
            color: var(--text-dark);
            margin-bottom: 8px;
            min-height: 27px;
        }
        .preview-price {
            font-size: 20px;
            font-weight: 700;
            color: var(--primary-teal);
            min-height: 30px;
        }
        .preview-elements {
            display: flex;
            flex-direction: column;
            gap: 12px;
        }
        .preview-element {
            background: var(--gray-50);
            border-radius: 6px;
            padding: 12px;
            border: 1px solid var(--border-color);
        }
        .preview-element-header {
            font-size: 13px;
            font-weight: 600;
            color: var(--text-dark);
            margin-bottom: 8px;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .preview-element-number {
            background: var(--primary-teal);
            color: var(--white);
            width: 24px;
            height: 24px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 11px;
            font-weight: 700;
        }
        .preview-products {
            display: flex;
            flex-direction: column;
            gap: 6px;
        }
        .preview-product {
            font-size: 12px;
            color: var(--text-medium);
            padding: 6px 10px;
            background: var(--white);
            border-radius: 4px;
            border: 1px solid var(--border-color);
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .preview-product-image {
            width: 32px;
            height: 32px;
            border-radius: 4px;
            object-fit: cover;
            background: var(--gray-100);
        }
        .preview-product-details {
            flex: 1;
            min-width: 0;
        }
        .preview-product-name {
            font-weight: 500;
            color: var(--text-dark);
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .preview-product-price {
            font-size: 11px;
            color: var(--primary-teal);
        }
        /* ==================== حقول الإدخال ==================== */
        .form-group {
            margin-bottom: 16px;
        }
        .form-label {
            display: block;
            font-size: 13px;
            font-weight: 600;
            color: var(--text-dark);
            margin-bottom: 6px;
        }
        .form-input,
        .form-textarea {
            width: 100%;
            padding: 10px 12px;
            border: 1px solid var(--border-color);
            border-radius: 6px;
            font-family: 'Tajawal', sans-serif;
            font-size: 14px;
            transition: all 0.2s;
            background: var(--white);
        }
        .form-input:focus,
        .form-textarea:focus {
            outline: none;
            border-color: var(--primary-teal);
            box-shadow: 0 0 0 3px var(--primary-teal-light);
        }
        .form-textarea {
            resize: vertical;
            min-height: 80px;
        }

        /* ==================== الأزرار ==================== */
        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 6px;
            font-family: 'Tajawal', sans-serif;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            justify-content: center;
        }
        .btn-primary {
            background: var(--primary-teal);
            color: var(--white);
        }
        .btn-primary:hover {
            background: var(--primary-teal-hover);
        }
        .btn-secondary {
            background: var(--gray-100);
            color: var(--text-dark);
        }
        .btn-secondary:hover {
            background: var(--gray-50);
            border-color: var(--border-color);
        }
        .btn-danger {
            background: var(--red);
            color: var(--white);
        }
        .btn-full {
            width: 100%;
        }
        .btn:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }
        /* ==================== بطاقة المنتج ==================== */
        .element-card {
            background: var(--white);
            border: 1px solid var(--border-color);
            border-radius: 8px;
            margin-bottom: 16px;
            overflow: hidden;
        }
        .element-header {
            background: var(--gray-50);
            padding: 14px 16px;
            display: flex;
            align-items: center;
            gap: 12px;
            cursor: pointer;
            user-select: none;
            border-bottom: 1px solid var(--border-color);
        }
        .element-header:hover {
            background: var(--gray-100);
        }
        .element-number {
            background: var(--primary-teal);
            color: var(--white);
            width: 28px;
            height: 28px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 13px;
            font-weight: 700;
            flex-shrink: 0;
        }
        .element-title {
            flex: 1;
            font-size: 14px;
            font-weight: 600;
            color: var(--text-dark);
        }
        .element-actions {
            display: flex;
            gap: 8px;
            align-items: center;
        }
        .element-collapse-icon {
            font-size: 18px;
            color: var(--text-light);
            transition: transform 0.2s;
        }
        .element-header.collapsed .element-collapse-icon {
            transform: rotate(-90deg);
        }
        .element-body {
            padding: 16px;
            max-height: 1000px;
            overflow: hidden;
            transition: max-height 0.3s ease-out, padding 0.3s ease;
            max-height: 1000px;
        }
        .element-body.collapsed {
            max-height: 0 !important;
            padding-top: 0 !important;
            padding-bottom: 0 !important;
        }
        .element-name-input {
            width: 100%;
            padding: 10px 12px;
            border: 1px solid var(--border-color);
            border-radius: 6px;
            font-family: 'Tajawal', sans-serif;
            font-size: 13px;
            margin-bottom: 16px;
        }
        .element-name-input:focus {
            outline: none;
            border-color: var(--primary-teal);
            box-shadow: 0 0 0 3px var(--primary-teal-light);
        }

        /* This replaces the inline styles you had before */
        .empty-state-wrapper {
            text-align: center;
            color: #999999; /* This is var(--text-light) */
            padding: 40px 20px;
        }

        .empty-state-icon {
            font-size: 48px;
            opacity: 0.3;
            display: block;
            margin-bottom: 12px;
        }
        /* ==================== قائمة المنتجات ==================== */
        .products-list {
            margin-bottom: 16px;
        }
        .product-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px;
            background: var(--gray-50);
            border: 1px solid var(--border-color);
            border-radius: 6px;
            margin-bottom: 8px;
        }
        .product-image {
            width: 48px;
            height: 48px;
            border-radius: 6px;
            object-fit: cover;
            background: var(--gray-100);
            flex-shrink: 0;
        }
        .product-details {
            flex: 1;
            min-width: 0;
        }
        .product-name {
            font-size: 13px;
            font-weight: 600;
            color: var(--text-dark);
            margin-bottom: 2px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
        .product-price {
            font-size: 12px;
            color: var(--primary-teal);
            font-weight: 600;
        }
        .product-remove {
            width: 24px;
            height: 24px;
            border-radius: 50%;
            background: var(--red);
            color: var(--white);
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            font-size: 16px;
            flex-shrink: 0;
            transition: all 0.2s;
        }
        .product-remove:hover {
            transform: scale(1.1);
        }
        /* ==================== محدد المنتج ==================== */
        .product-selector {
            border: 1px solid var(--border-color);
            border-radius: 6px;
            overflow: hidden;
            background: var(--white);
        }
        .selector-header {
            padding: 12px;
            background: var(--gray-50);
            border-bottom: 1px solid var(--border-color);
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .selector-header .s-icon {
            font-size: 16px;
            color: var(--text-light);
        }
        .selector-search {
            flex: 1;
            padding: 8px 12px;
            border: 1px solid var(--border-color);
            border-radius: 4px;
            font-family: 'Tajawal', sans-serif;
            font-size: 13px;
        }
        .selector-search:focus {
            outline: none;
            border-color: var(--primary-teal);
        }
        .selector-list {
            max-height: 300px;
            overflow-y: auto;
        }
        .selector-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 10px 12px;
            cursor: pointer;
            border-bottom: 1px solid var(--border-color);
            transition: background 0.2s;
        }
        .selector-item:hover {
            background: var(--gray-50);
        }
        .selector-item:last-child {
            border-bottom: none;
        }
        .selector-item-image {
            width: 40px;
            height: 40px;
            border-radius: 4px;
            object-fit: cover;
            background: var(--gray-100);
        }
        .selector-item-details {
            flex: 1;
            min-width: 0;
        }
        .selector-item-name {
            font-size: 13px;
            font-weight: 500;
            color: var(--text-dark);
            margin-bottom: 2px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
        .selector-item-price {
            font-size: 12px;
            color: var(--primary-teal);
            font-weight: 600;
        }
        .selector-item-add {
            padding: 6px 12px;
            background: var(--primary-teal);
            color: var(--white);
            border: none;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
        }
        .selector-item-add:hover {
            background: var(--primary-teal-hover);
        }
        /* ==================== التذييل الثابت ==================== */
        .fixed-footer {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            background: var(--white);
            border-top: 1px solid var(--border-color);
            padding: 15px 0;
            display: flex;
            justify-content: center;
            gap: 15px;
            box-shadow: 0 -2px 8px rgba(0, 0, 0, 0.05);
            z-index: 9990;
        }
        .fixed-footer .btn {
            min-width: 160px;
        }
        /* ==================== استجابة الموبايل ==================== */
        @media (max-width: 1024px) {
            .container {
                flex-direction: column;
            }
            .sidebar {
                width: 100%;
                position: static;
                order: -1;
            }
            .fixed-footer {
                flex-direction: column;
                gap: 8px;
            }
            .fixed-footer .btn {
                width: 100%;
            }
        }
        @media (max-width: 640px) {
            .container {
                padding: 16px;
            }
            .card-body {
                padding: 16px;
            }
            .element-header {
                padding: 12px;
            }
            .element-body {
                padding: 12px;
            }
            .preview-card {
                padding: 16px;
            }
        }
    </style>
    @yield('heder-overrides')
</head>
<body>
    <div id="app">
        @include('partials.navbar')

        <main class="py-4">
            @yield('content')
        </main>
    </div>

@yield('script-overrides')

    <script>
        let packageElements = @json($elements ?? []);
        let elementCounter = packageElements.length;
        let packageImageUrl = "{{ $box->image_url ?? '' }}";

        // المتغير الذي سيحمل بيانات المنتجات من قاعدة البيانات
        let availableProducts = [];

        // دالة جلب المنتجات من Laravel API
        // دالة جلب المنتجات من Laravel API
        // هذه الدالة تجلب جميع المنتجات من جميع الصفحات
        async function fetchProducts() {
            try {
                let allProducts = [];
                let currentPage = 1;
                let hasMorePages = true;

                while (hasMorePages) {
                    // Fetch the specific page from Laravel
                    const response = await fetch(`/api/products?page=${currentPage}`, {
                        headers: { 'Accept': 'application/json' }
                    });

                    if (!response.ok) throw new Error('فشل الاتصال بالسيرفر');
                    const result = await response.json();

                    // 1. Access the 'data' array provided by Laravel's paginator
                    const products = result.data.map(product => ({
                        id: product.internal_id,
                        name: product.name,
                        price: parseFloat(product.price),
                        image: product.image_url || 'https://via.placeholder.com/150' // Use image_url
                    }));

                    allProducts = [...allProducts, ...products];

                    // 2. Check Laravel's 'meta' to see if there's a next page
                    if (result.meta && result.current_page < result.last_page) {
                        currentPage++;
                    } else if (result.next_page_url) { // Standard Laravel pagination key
                        currentPage++;
                    } else {
                        hasMorePages = false;
                    }

                    // Safety break
                    if (currentPage > 500) break;
                }

                availableProducts = allProducts;
                console.log(`✅ تم تحميل ${availableProducts.length} منتج من جميع الصفحات`);

                if (packageElements.length > 0) {
                    renderExistingElements();
                }

            } catch (error) {
                console.error("❌ خطأ:", error);
                alert("تنبيه: فشل جلب المنتجات.");
            }
        }

        window.addEventListener('DOMContentLoaded', fetchProducts);

        function handlePackageImageUpload(event) {
            const file = event.target.files[0];
            if (file) {
                if (file.size > 2 * 1024 * 1024) {
                    alert('حجم الملف كبير جداً. الحد الأقصى 2MB');
                    return;
                }

                const reader = new FileReader();
                reader.onload = function(e) {
                    packageImageUrl = e.target.result;
                    const uploadArea = document.getElementById('upload-area');
                    uploadArea.classList.add('has-file');
                    uploadArea.innerHTML = `<img src="${packageImageUrl}" alt="صورة الباقة">`;
                    updatePreview();
                };
                reader.readAsDataURL(file);
            }
        }

        function addNewElement() {
            // Collapse all existing elements first
            document.querySelectorAll('.element-header').forEach(header => {
                const body = header.nextElementSibling;
                header.classList.add('collapsed');
                body.classList.add('collapsed');
            });

            const elementId = `element-${++elementCounter}`;
            const elementIndex = packageElements.length;

            packageElements.push({
                id: elementId,
                name: '',
                products: []
            });

            const container = document.getElementById('elements-container');

            const emptyState = container.querySelector('.empty-state-wrapper');
                if (emptyState) {
                    emptyState.remove();
                }
            /* if (container.querySelector('div[style*="text-align: center"]')) {
                container.innerHTML = '';
            } */

            const elementHTML = `
                <div class="element-card" id="${elementId}" data-element-index="${elementIndex}">
                    <div class="element-header" onclick="toggleCollapsible(this)">
                        <div class="element-number">${elementIndex + 1}</div>
                        <div class="element-title">المنتج ${elementIndex + 1}</div>
                        <div class="element-actions">
                            <button class="btn btn-danger" style="padding: 6px 12px; font-size: 12px;" onclick="event.stopPropagation(); removeElement('${elementId}', ${elementIndex})">
                                <i class="s-icon sicon-trash"></i>
                            </button>
                            <i class="s-icon sicon-keyboard_arrow_down element-collapse-icon"></i>
                        </div>
                    </div>
                    <div class="element-body">
                        <div class="form-group">
                            <label class="form-label">اسم المنتج</label>
                            <input type="text" class="element-name-input" placeholder="مثال: اختر نوع الشامبو" oninput="updateElementName(${elementIndex}, this.value)">
                        </div>

                        <div class="products-list" id="products-list-${elementId}">
                            <div style="text-align: center; color: var(--text-light); padding: 20px; font-size: 13px;">
                                لم يتم إضافة منتجات بعد
                            </div>
                        </div>

                        <div class="product-selector" id="selector-${elementId}">
                            <div class="selector-header">
                                <i class="s-icon sicon-search"></i>
                                <input type="text" class="selector-search" placeholder="ابحث عن منتج..." oninput="filterProducts('${elementId}', this.value)">
                            </div>
                            <div class="selector-list" id="available-products-${elementId}">
                                ${renderAvailableProducts(elementId, elementIndex)}
                            </div>
                        </div>
                    </div>
                </div>
            `;

            container.insertAdjacentHTML('beforeend', elementHTML);
            updatePreview();
        }

        // دالة عرض المنتجات في القائمة المنسدلة
        function renderAvailableProducts(elementId, elementIndex, filterText = '') {
            // تصفية المنتجات حسب نص البحث
            let productsToShow = availableProducts;
            if (filterText) {
                productsToShow = availableProducts.filter(p =>
                    p.name.includes(filterText)
                );
            }

            if (productsToShow.length === 0) {
                if (availableProducts.length === 0) {
                    return '<div style="padding:10px; text-align:center; color: var(--text-light);">جاري تحميل المنتجات...</div>';
                }
                return '<div style="padding:10px; text-align:center; color: var(--text-light);">لا توجد منتجات تطابق بحثك</div>';
            }

            return productsToShow.map(product => {
                const alreadyAdded = packageElements[elementIndex] &&
                    packageElements[elementIndex].products.some(p => p.id === product.id);
                return `
                <div class="selector-item" id="selector-item-${elementId}-${product.id}"
                    onclick="addProductToElement(${elementIndex}, ${product.id})"
                    style="${alreadyAdded ? 'display:none;' : ''}">
                    <img src="${product.image}" alt="${product.name}" class="selector-item-image" onerror="this.src='https://via.placeholder.com/150'">
                    <div class="selector-item-details">
                        <div class="selector-item-name">${product.name}</div>
                        <div class="selector-item-price">﷼ ${product.price.toFixed(2)}</div>
                    </div>
                    <button class="selector-item-add">
                        <i class="s-icon sicon-plus"></i> إضافة
                    </button>
                </div>`;
            }).join('');
        }

        function filterProducts(elementId, searchText) {
            const index = document.getElementById(elementId).getAttribute('data-element-index');
            const container = document.getElementById(`available-products-${elementId}`);
            container.innerHTML = renderAvailableProducts(elementId, index, searchText);
        }

        function updatePreview() {
            const packageName = document.getElementById('package-name').value || 'اسم الباقة';
            const packagePrice = document.getElementById('package-price').value || '0.00';

            document.getElementById('preview-title').textContent = packageName;
            document.getElementById('preview-price').textContent = `﷼ ${parseFloat(packagePrice).toFixed(2)}`;

            const previewImage = document.getElementById('preview-package-image');
            if (packageImageUrl) {
                previewImage.src = packageImageUrl;
                previewImage.classList.add('visible');
            } else {
                previewImage.classList.remove('visible');
            }

            const previewElements = document.getElementById('preview-elements');

            if (packageElements.length === 0) {
                previewElements.innerHTML = `
                    <div style="text-align: center; color: var(--text-light); padding: 20px; font-size: 12px;">
                        قم بإضافة منتجات للباقة لمشاهدة المعاينة
                    </div>
                `;
                return;
            }

            previewElements.innerHTML = packageElements.map((element, index) => {
                if (element.products.length === 0) return '';

                return `
                    <div class="preview-element">
                        <div class="preview-element-header">
                            <div class="preview-element-number">${index + 1}</div>
                            <span>${element.name || `المنتج ${index + 1}`}</span>
                        </div>
                        <div class="preview-products">
                            ${element.products.map(product => `
                                <div class="preview-product">
                                    <img src="${product.image}" alt="${product.name}" class="preview-product-image">
                                    <div class="preview-product-details">
                                        <div class="preview-product-name">${product.name}</div>
                                        <div class="preview-product-price">﷼ ${product.price.toFixed(2)}</div>
                                    </div>
                                </div>
                            `).join('')}
                        </div>
                    </div>
                `;
            }).join('');
        }

        function addProductToElement(elementIndex, productId) {
            const product = availableProducts.find(p => p.id === productId);
            if (!product) return;

            const exists = packageElements[elementIndex].products.some(p => p.id === productId);
            if (exists) {
                alert('هذا المنتج مضاف بالفعل لهذا المنتج');
                return;
            }

            packageElements[elementIndex].products.push(product);

            const element = packageElements[elementIndex];
            const elementId = element.id;

            updateProductsList(elementId, elementIndex);

            const availableContainer = document.getElementById(`available-products-${elementId}`);
            availableContainer.innerHTML = renderAvailableProducts(elementId, elementIndex);
            updatePreview();
        }

        function updateProductsList(elementId, elementIndex) {
            const element = packageElements[elementIndex];
            const container = document.getElementById(`products-list-${elementId}`);

            if (element.products.length === 0) {
                container.innerHTML = `
                    <div style="text-align: center; color: var(--text-light); padding: 20px; font-size: 13px;">
                        لم يتم إضافة منتجات بعد
                    </div>
                `;
                return;
            }

            //const fullProduct = availableProducts.find(ap => ap.id == product.id) || product;
            container.innerHTML = element.products.map((product, pIndex) => `
                <div class="product-item">
                    <img src="${product.image}" alt="${product.name}" class="product-image">
                    <div class="product-details">
                        <div class="product-name">${product.name}</div>
                        <div class="product-price">﷼ ${product.price.toFixed(2)}</div>
                    </div>
                    <div class="product-remove" onclick="removeProductFromElement(${elementIndex}, ${pIndex})">
                        <i class="s-icon sicon-cancel"></i>
                    </div>
                </div>
            `).join('');
        }

        function removeProductFromElement(elementIndex, productIndex) {
            const element = packageElements[elementIndex];
            const elementId = element.id;
            packageElements[elementIndex].products.splice(productIndex, 1);
            updateProductsList(element.id, elementIndex);

            const availableContainer = document.getElementById(`available-products-${elementId}`);
            if (availableContainer) {
                availableContainer.innerHTML = renderAvailableProducts(elementId, elementIndex);
            }

            updatePreview();
        }

        function updateElementName(elementIndex, name) {
            packageElements[elementIndex].name = name;
            updatePreview();
        }

        function removeElement(elementId, elementIndex) {
            if (confirm('هل أنت متأكد من حذف هذا المنتج وجميع منتجاته؟')) {
                packageElements.splice(elementIndex, 1);
                document.getElementById(elementId).remove();
                renumberElements();
                updatePreview();
            }
        }

        function renumberElements() {
            const elements = document.querySelectorAll('.element-card');
            elements.forEach((el, index) => {
                const number = el.querySelector('.element-number');
                if (number) number.textContent = index + 1;
                el.setAttribute('data-element-index', index);
            });
        }

        function toggleCollapsible(header) {
            const body = header.nextElementSibling;
            const isAlreadyCollapsed = header.classList.contains('collapsed');

            // If the one you clicked is currently closed, we need to open it
            if (isAlreadyCollapsed) {
                // 1. This is the "Auto-Close" part:
                // It finds all other headers and closes them.
                document.querySelectorAll('.element-header').forEach(otherHeader => {
                    otherHeader.classList.add('collapsed');
                    otherHeader.nextElementSibling.classList.add('collapsed');
                });

                // 2. Open the specific one you clicked
                header.classList.remove('collapsed');
                body.classList.remove('collapsed');
            } else {
                // If you clicked on an already open one, just close it
                header.classList.add('collapsed');
                body.classList.add('collapsed');
            }
        }
        async function savePackage() {
            const packageName = document.getElementById('package-name').value;
            const packagePrice = document.getElementById('package-price').value;
            const salla_url = document.getElementById('salla-url').value;

            if (!packageName) { alert('يرجى إدخال اسم الباقة'); return; }
            if (!packagePrice || parseFloat(packagePrice) <= 0) { alert('يرجى إدخال سعر صحيح'); return; }
            if (packageElements.length === 0) { alert('يرجى إضافة منتج واحد على الأقل'); return; }
            if (packageElements.some(e => e.products.length === 0)) { alert('يوجد منتجات بدون منتجات'); return; }

            // We check if the $box variable was passed from the controller
            const isEdit = "{{ isset($box) ? 'true' : 'false' }}" === 'true';
            const boxId = "{{ $box->id ?? '' }}";

            // If editing, use PUT and the specific ID. If creating, use POST.
            const url = isEdit ? `http://127.0.0.1:8000/api/boxes/${boxId}` : 'http://127.0.0.1:8000/api/boxes';
            const method = isEdit ? 'PUT' : 'POST';

            const payload = {
                name: packageName,
                price: parseFloat(packagePrice),
                description: document.getElementById('package-description').value,
                image: packageImageUrl, 
                salla_url: salla_url,
                elements: packageElements.map((el, index) => ({
                    name: el.name || `المنتج ${index + 1}`,
                    products: el.products.map(p => ({ id: p.id }))
                }))
            };

            try {
                const btn = event.target;
                btn.disabled = true;
                btn.innerHTML = '<i class="s-icon sicon-loading sicon-is-spinning"></i> جاري الحفظ...';

                //const response = await fetch('http://127.0.0.1:8000/api/boxes', {
                    const response = await fetch(url, {
                    method: method,
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content },
                    body: JSON.stringify(payload)
                });

                const result = await response.json();
                if (!response.ok) throw new Error(result.message || 'فشل الحفظ');

                btn.innerHTML = '<i class="s-icon sicon-check-circle"></i> تم الحفظ بنجاح';
                setTimeout(() => {
                    window.location.href = '/boxes';
                }, 500);

            } catch (error) {
                alert(`خطأ: ${error.message}`);
                event.target.innerHTML = '<i class="s-icon sicon-save"></i> حفظ الباقة';
                event.target.disabled = false;
            }
        }

        function previewPackage() {
            const packageName = document.getElementById('package-name').value || 'اسم الباقة';
            const packagePrice = document.getElementById('package-price').value || '0.00';

            let previewHTML = `
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>معاينة الباقة - ${packageName}</title>
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.salla.network/fonts/sallaicons.css"/>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Tajawal', sans-serif; background: #f9fafb; padding: 40px 20px; }
        .s-icon { font-family: 'sallaicons' !important; font-style: normal; vertical-align: middle; }
        .preview-container { max-width: 600px; margin: 0 auto; background: white; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
        .package-image { width: 100%; height: 300px; object-fit: cover; background: #e5e7eb; }
        .package-header { padding: 30px; text-align: center; border-bottom: 2px solid #e5e7eb; }
        .package-badge { background: #62D0B6; color: white; padding: 6px 16px; border-radius: 20px; font-size: 13px; display: inline-block; margin-bottom: 12px; font-weight: 600; }
        .package-title { font-size: 28px; font-weight: 700; color: #2d3748; margin-bottom: 12px; }
        .package-price { font-size: 36px; font-weight: 700; color: #62D0B6; }
        .package-elements { padding: 30px; }
        .element-section { margin-bottom: 30px; }
        .element-title { font-size: 18px; font-weight: 700; color: #2d3748; margin-bottom: 16px; display: flex; align-items: center; gap: 10px; }
        .element-number { background: #62D0B6; color: white; width: 32px; height: 32px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 14px; }
        .products-grid { display: grid; gap: 12px; }
        .product-option { display: flex; align-items: center; gap: 12px; padding: 16px; border: 2px solid #e5e7eb; border-radius: 8px; cursor: pointer; transition: all 0.2s; }
        .product-option:hover { border-color: #62D0B6; background: #e8f7f3; }
        .product-radio { width: 20px; height: 20px; accent-color: #62D0B6; }
        .product-image-small { width: 60px; height: 60px; border-radius: 8px; object-fit: cover; background: #e5e7eb; }
        .product-info { flex: 1; }
        .product-name { font-size: 15px; font-weight: 600; color: #2d3748; margin-bottom: 4px; }
        .product-price-tag { font-size: 14px; color: #62D0B6; font-weight: 600; }
        .add-to-cart { background: #62D0B6; color: white; border: none; padding: 16px 32px; border-radius: 8px; font-size: 16px; font-weight: 700; cursor: pointer; width: 100%; margin-top: 30px; font-family: 'Tajawal', sans-serif; display: flex; align-items: center; justify-content: center; gap: 8px; }
        .add-to-cart:hover { background: #4fc1a6; }
    </style>
</head>
<body>
    <div class="preview-container">
        ${packageImageUrl ? `<img src="${packageImageUrl}" alt="${packageName}" class="package-image">` : ''}
        <div class="package-header">
            <div class="package-badge"><i class="s-icon sicon-box-bankers"></i> باقة مخصصة</div>
            <h1 class="package-title">${packageName}</h1>
            <div class="package-price">﷼ ${parseFloat(packagePrice).toFixed(2)}</div>
        </div>
        <div class="package-elements">
            ${packageElements.map((el, i) => `
                <div class="element-section">
                    <div class="element-title">
                        <div class="element-number">${i+1}</div>
                        <span>${el.name || `المنتج ${i+1}`}</span>
                    </div>
                    <div class="products-grid">
                        ${el.products.map((p, pi) => `
                            <label class="product-option">
                                <input type="radio" name="el-${i}" class="product-radio" ${pi===0?'checked':''}>
                                <img src="${p.image}" class="product-image-small">
                                <div class="product-info">
                                    <div class="product-name">${p.name}</div>
                                    <div class="product-price-tag">﷼ ${p.price.toFixed(2)}</div>
                                </div>
                            </label>
                        `).join('')}
                    </div>
                </div>
            `).join('')}
            <button class="add-to-cart">
                <i class="s-icon sicon-cart"></i>
                أضف إلى السلة
            </button>
        </div>
    </div>
</body>
</html>`;
            const previewWindow = window.open('', '_blank', 'width=700,height=900');
            previewWindow.document.write(previewHTML);
            previewWindow.document.close();
        }

        window.addEventListener('load', () => {});

        function renderExistingElements() {
            const container = document.getElementById('elements-container');
            if (packageElements.length > 0) {
                container.innerHTML = ''; // Clear the "No elements" message

                packageElements.forEach((element, index) => {
                    const elementId = element.id;
                    const elementHTML = `
                    <div class="element-card" id="${elementId}" data-element-index="${index}">
                    <div class="element-header collapsed" onclick="toggleCollapsible(this)">
                        <div class="element-number">${index + 1}</div>
                        <div class="element-title">${element.name || `المنتج ${index + 1}`}</div>
                        <div class="element-actions">
                            <button class="btn btn-danger" style="padding: 6px 12px; font-size: 12px;" onclick="event.stopPropagation(); removeElement('${elementId}', ${index})">
                                <i class="s-icon sicon-trash"></i>
                            </button>
                            <i class="s-icon sicon-keyboard_arrow_down element-collapse-icon"></i>
                        </div>
                    </div>
                    <div class="element-body collapsed">
                        <div class="form-group">
                            <label class="form-label" style="text-align: right;">اسم المنتج</label>
                            <input type="text" class="element-name-input" value="${element.name || ''}" oninput="updateElementName(${index}, this.value)">
                        </div>
                        <div class="products-list" id="products-list-${elementId}"></div>
                        <div class="product-selector" id="selector-${elementId}">
                            <div class="selector-header">
                                <i class="s-icon sicon-search"></i>
                                <input type="text" class="selector-search" placeholder="ابحث عن منتج..." oninput="filterProducts('${elementId}', this.value)">
                            </div>
                            <div class="selector-list" id="available-products-${elementId}">
                                ${renderAvailableProducts(elementId, index)}
                            </div>
                        </div>
                    </div>
                </div>`;
                    container.insertAdjacentHTML('beforeend', elementHTML);
                    updateProductsList(elementId, index); // Fill the products for this card
                });
                updatePreview(); // Update the sidebar
            }
        }

        // 5. Update Initialization
    window.addEventListener('DOMContentLoaded', () => {
        // Show current image in upload area if editing
        if (packageImageUrl) {
            const uploadArea = document.getElementById('upload-area');
            uploadArea.classList.add('has-file');
            uploadArea.innerHTML = `<img src="${packageImageUrl}" alt="صورة الباقة">`;
        }
        fetchProducts();
    });

    </script>
    @yield('script-overrides')
</body>
</html>

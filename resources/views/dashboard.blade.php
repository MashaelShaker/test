@extends('app')
@section('content')
    <div class="container"  dir="rtl">
        <!-- المحتوى الرئيسي -->
        <div class="main-content">
            <!-- معلومات الباقة الأساسية -->
            <div class="card">
                <div class="card-header">
                    <h3><i class="s-icon sicon-box-bankers" dir="rtl"></i> معلومات الباقة</h3>
                </div>
                <div class="card-body">
                    <!-- رفع صورة الباقة -->
                    <div class="form-group" dir="rtl" style="text-align: right;">
                        <label class="form-label">صورة الباقة</label>
                        <div class="upload-area" id="upload-area" onclick="document.getElementById('package-image-input').click()">
                            <div class="upload-content">
                                <div class="upload-icon">
                                    <i class="s-icon sicon-image" style="font-size: 48px; color: var(--text-light);"></i>
                                </div>
                                <h4>اضغط لرفع صورة الباقة</h4>
                                <p>PNG, JPG أو GIF (الحد الأقصى 2MB)</p>
                            </div>
                        </div>
                        <input type="file" id="package-image-input" accept="image/*" style="display: none;" onchange="handlePackageImageUpload(event)">
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="package-name" style="text-align: right;">اسم الباقة</label>
                        <input type="text" id="package-name" class="form-input" style="text-align: right;" placeholder="مثال: باقة العناية الشاملة" value="{{ $box->name ?? '' }}" oninput="updatePreview()">
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="package-price" style="text-align: right;">سعر الباقة (﷼)</label>
                        <input type="number" id="package-price" class="form-input" style="text-align: right;" placeholder="0.00" step="0.01" min="0" value="{{ $box->price ?? '' }}" oninput="updatePreview()">
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="package-description" dir="rtl" style="text-align: right;">وصف الباقة (اختياري)</label>
                        <textarea id="package-description" class="form-textarea" placeholder="أضف وصفاً للباقة..." dir="rtl"style="text-align: right;">{{ $box->description ?? '' }}</textarea>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="salla-url" style="text-align: right;"></label>
                        <input
                            type="hidden"
                            id="salla-url"
                            class="form-input"
                            style="text-align: right;"
                            placeholder="https://demostore.salla.sa/dev-gvzumy3zn3luyfpd/box-test/p"
                        >
                    </div>
                </div>
            </div>


            <!-- منتجات الباقة -->
            <div class="card">
                <div class="card-header">
                    <h3><i class="s-icon sicon-list"></i> منتجات الباقة</h3>
                    <button class="btn btn-primary" onclick="addNewElement()">
                        <i class="s-icon sicon-plus"></i>
                        إضافة منتج
                    </button>
                </div>
                <div class="card-body" id="elements-container">
                    <div class="empty-state-wrapper">
                        <i class="s-icon sicon-inbox" class="empty-state-icon"></i>
                        <p>لم يتم إضافة أي منتجات بعد</p>
                        <p style="font-size: 12px; margin-top: 4px;">ابدأ بإضافة منتج جديد للباقة</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- الشريط الجانبي -->
        <div class="sidebar">
            <div class="preview-card">
                <div class="preview-header">
                    <div class="preview-label"><i class="s-icon sicon-eye"></i> معاينة الباقة</div>
                    <img id="preview-package-image" class="preview-package-image" alt="صورة الباقة">
                    <div class="preview-title" id="preview-title">اسم الباقة</div>
                    <div class="preview-price" id="preview-price">﷼ 0.00</div>
                </div>
                <div class="preview-elements" id="preview-elements">
                    <div style="text-align: center; color: var(--text-light); padding: 20px; font-size: 12px;">
                        قم بإضافة منتجات للباقة لمشاهدة المعاينة
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- التذييل الثابت -->
    <div class="fixed-footer">
        <button class="btn btn-secondary" onclick="previewPackage()">
            <i class="s-icon sicon-eye"></i>
            معاينة كاملة
        </button>
        <button class="btn btn-primary" onclick="savePackage()">
            <i class="s-icon sicon-save"></i>
            حفظ الباقة
        </button>
    </div>


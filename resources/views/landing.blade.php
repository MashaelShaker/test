<!doctype html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>باقات سلة — Boxy</title>
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@400;700;800;900&display=swap" rel="stylesheet">
    <style>
        *{margin:0;padding:0;box-sizing:border-box;}
        :root{
            --teal:var(--primary-teal,#1D9E75);
            --teal-light:#5DCAA5;
            --teal-bg:#D4E8E4;
            --ink:#111827;
            --ink-light:#6B7280;
            --bg:#FFFFFF;
            --sand:#A68D78;
        }
        body{
            font-family: 'Tajawal', sans-serif;
            background-color: var(--bg);
            background-image: url("{{ asset('images/Pattern_transparent.png') }}");
            background-size: 545px;
            color: var(--ink);
            line-height: 1.6;
        }
        nav{
            display:flex;
            align-items:center;
            justify-content:space-between;
            padding: 16px 8%;
            background: rgba(255,255,255,0.9);
            backdrop-filter: blur(10px);
        }
        .logo{display:flex;align-items:center;gap:8px;font-weight:800;font-size:1.2rem;color:var(--ink);}
        .logo img{height:32px;}
        .btn-sm,
        .btn-main,
        .btn-white{
            text-decoration:none;
            font-weight:700;
            border-radius:8px;
            display:inline-block;
        }
        .btn-sm{
            padding:8px 18px;
            background:var(--teal-light);
            color:#fff;
            font-size:13px;
        }
        .hero{
            display:flex;
            align-items:center;
            justify-content:space-between;
            padding:60px 8% 70px;
            gap:40px;
            background:linear-gradient(to bottom,#FFFFFF 0%,#F0F8F7 100%);
        }
        .hero-content{flex:1;}
        .hero-image{
            flex:1;
            text-align:left;
            position:relative;
            display:flex;
            justify-content:center;
            align-items:center;
        }
        .hero-image::before{
            content:"";
            position:absolute;
            top:-120px;
            left:-84px;
            width:420px;
            height:420px;
            background-image:url("{{ asset('images/Light.png') }}");
            background-size:contain;
            background-repeat:no-repeat;
            background-position:center;
            pointer-events:none;
            z-index:0;
            opacity:.9;
        }
        .hero-image img{
            width:100%;
            max-width:430px;
            position:relative;
            z-index:1;
        }
        .hero-content{position:relative;z-index:1;}
        .hero-image{z-index:1;}
        h1{font-size:3.5rem;font-weight:900;line-height:1.1;margin-bottom:25px;}
        h1 span{color:var(--teal-light);}
        .hero-desc{font-size:1.1rem;color:var(--ink-light);margin-bottom:30px;max-width:540px;}
        .btn-main{
            padding:12px 35px;
            background:var(--teal-light);
            color:#fff;
            box-shadow:0 4px 15px rgba(93,202,165,.3);
        }
        .steps-section{
            padding:56px 8% 56px;
            margin-top:32px;
            text-align:center;
            border-top:1px solid rgba(0,0,0,.03);
            background-color:transparent;
            background-image:url("{{ asset('images/Pattern_transparent.png') }}");
            background-size:260px;
            position:relative;
            overflow:hidden;
        }
        .steps-section::before{
            content:"";
            position:absolute;
            top:0;
            left:50%;
            transform:translateX(-50%);
            width:min(1720px, calc(100% - 64px));
            height:560px;
            background:#FAFDFD;
            border-radius:48px;
            z-index:0;
        }
        .steps-section > *{
            position:relative;
            z-index:1;
        }
        .section-title{font-size:1.2rem;color:var(--sand);margin-bottom:10px;font-weight:700;}
        .section-heading{font-size:2.2rem;font-weight:900;margin-bottom:60px;}
        .steps-grid{
            display:grid;
            grid-template-columns:repeat(3,1fr);
            gap:30px;
        }
        .step-card{
            position:relative;
            text-align:center;
            padding-top:24px;
        }
        .step-number{
            position:absolute;
            top:-2px;
            left:50%;
            transform:translateX(-50%);
            font-size:6rem;
            line-height:1;
            font-weight:800;
            color:rgba(93,202,165,.35);
            z-index:0;
            pointer-events:none;
        }
        .step-card img{height:146px;margin-bottom:14px;object-fit:contain;position:relative;z-index:1;}
        .step-card h3{font-size:1.3rem;font-weight:800;margin-bottom:10px;}
        .step-card p{color:var(--ink-light);font-size:.95rem;padding:0 10px;}
        .footer-banner{
            width:min(1720px, calc(100% - 64px));
            margin:32px auto 80px;
            background:#62D0B6;
            border-radius:20px;
            padding:40px 60px;
            display:flex;
            justify-content:space-between;
            align-items:center;
            color:#fff;
            position:relative;
            overflow:hidden;
        }
        .footer-banner h2{font-size:1.8rem;font-weight:800;margin-bottom:10px;}
        .footer-banner p{font-size:.9rem;opacity:.9;max-width:500px;}
        .btn-white{
            background:#fff;
            color:var(--teal-light);
            padding:12px 30px;
            font-weight:800;
            z-index:2;
            white-space:nowrap;
        }
        .step-card{
            opacity:0;
            transform:translateY(30px);
            transition:all 0.6s ease-out;
        }
        .step-card.is-visible{
            opacity:1;
            transform:translateY(0);
        }
        @media (max-width: 768px){
            .hero{flex-direction:column;text-align:center;}
            .hero-image{order:-1;text-align:center;}
            .hero-image::before{
                top:-60px;
                left:50%;
                transform:translateX(-50%);
                width:320px;
                height:320px;
            }
            .steps-section{margin-top:20px;}
            .steps-section::before{width:calc(100% - 20px);height:100%;border-radius:32px;}
            .steps-grid{grid-template-columns:1fr;gap:50px;}
            .footer-banner{flex-direction:column;text-align:center;padding:40px 20px;gap:24px;}
            h1{font-size:2.2rem;}
        }
    </style>
</head>
<body>

<section class="hero">
    <div class="hero-content">
        <h1>بع أكثر <br> <span>بدون جهد</span> إضافي</h1>
        <p class="hero-desc">حول منتجاتك الفردية إلى باقات جذابة تزيد قيمة كل طلب، وأدرها كلها من مكان واحد مع مزامنة تلقائية مع سلة.</p>
        <a href="{{ route('oauth.redirect') }}" class="btn-main">جرب الآن</a>
    </div>
    <div class="hero-image">
        <img src="{{ asset('images/Boxy_logo.png') }}" alt="Boxy Box">
    </div>
</section>

<section class="steps-section">
    <div class="section-title">كيف يعمل؟</div>
    <div class="section-heading">ثلاث خطوات والباقي علينا</div>

    <div class="steps-grid">
        <div class="step-card">
            <span class="step-number">01</span>
            <img src="{{ asset('images/Choose your products.png') }}" alt="Step 1">
            <h3>اختر المنتجات</h3>
            <p>تصفح قائمة منتجات متجرك في سلة وحدد العناصر التي ترغب في دمجها داخل باقة واحدة جذابة.</p>
        </div>
        <div class="step-card">
            <span class="step-number">02</span>
            <img src="{{ asset('images/customize the options.png') }}" alt="Step 2">
            <h3>خصص الخيارات</h3>
            <p>حدد السعر الجديد للباقة، وقم بتنسيق الألوان والأحجام المتوفرة لكل منتج داخل هذه المجموعة.</p>
        </div>
        <div class="step-card">
            <span class="step-number">03</span>
            <img src="{{ asset('images/publish and sell.png') }}" alt="Step 3">
            <h3>انشر وبع</h3>
            <p>بضغطة زر، يتم نشر الباقة في متجرك مع مزامنة لحظية للمخزون لضمان دقة العمليات.</p>
        </div>
    </div>
</section>

<div class="footer-banner">
    <div class="banner-text">
        <h2>متكامل بشكل كامل مع منصة سلة</h2>
        <p>ابدأ بناء أول باقة لك اليوم - سهل الإعداد، لا يحتاج خبرة تقنية، ويعمل مباشرة مع متجرك الحالي.</p>
    </div>
    <a href="{{ route('oauth.redirect') }}" class="btn-white">ابدأ مجاناً</a>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const cards = document.querySelectorAll('.step-card');
        const observerOptions = {
            root: null,
            threshold: 0.2
        };
        const observer = new IntersectionObserver((entries, observer) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const index = Array.from(cards).indexOf(entry.target);
                    setTimeout(() => {
                        entry.target.classList.add('is-visible');
                    }, index * 200);
                    observer.unobserve(entry.target);
                }
            });
        }, observerOptions);
        cards.forEach(card => {
            observer.observe(card);
        });
    });
</script>
</body>
</html>

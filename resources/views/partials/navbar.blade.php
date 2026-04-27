<style>
    .navbar {
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        height: 80px;
        /* Increased height to accommodate a larger logo */
        z-index: 1100;
        background: #ffffff !important;
        border-bottom: 1px solid #eeeeee;
        display: flex;
        align-items: center;
    }

    .navbar-container {
        max-width: 1200px;
        margin: 0 auto;
        width: 100%;
        display: flex;
        flex-direction: row;
        /* Logo Left (LTR logic for the bar itself) */
        align-items: center;
        justify-content: space-between;
        padding: 0 24px;
    }

    .navbar-logo {
        height: 65px;
        width: auto;
        object-fit: contain;
        display: block;
    }

    .nav-actions-wrapper {
        display: flex;
        align-items: center;
        gap: 8px;
        flex-direction: row-reverse;
    }

    .nav-divider {
        width: 1px;
        height: 24px;
        background-color: #cbd5e1;
        margin: 0 10px;
        display: inline-block;
    }

    .nav-link-item {
        color: #333;
        font-weight: 700;
        text-decoration: none;
        font-size: 15px;
        transition: color 0.2s;
        font-family: 'Tajawal', sans-serif;
    }

    .nav-link-item:hover {
        color: #62D0B6;
    }

    .navbar-logout-btn {
        color: #dc2626;
        font-weight: 700;
        padding: 0.5rem 1rem;
        border-radius: 8px;
        transition: all 0.2s;
        border: none;
        background: transparent;
        cursor: pointer;
        font-family: 'Tajawal', sans-serif;
        font-size: 15px;
    }

    .navbar-logout-btn:hover {
        background: rgba(220, 38, 38, 0.08);
    }

    .welcome-text {
        color: #999;
        font-size: 13px;
        font-family: 'Tajawal', sans-serif;
        margin-right: 10px; /* Space for the greeting */
        transition: opacity 0.5s ease;
    }

</style>

<nav class="navbar">
    <div class="navbar-container">
        <a class="navbar-brand" href="{{ url('/boxes') }}">
            <img class="navbar-logo" src="{{ asset('images/Boxy_logo.png') }}" alt="Boxy">
        </a>

        <div class="nav-actions-wrapper">
            @auth
                <form method="POST" action="{{ route('logout') }}" class="m-0">
                    @csrf
                    <button type="submit" class="navbar-logout-btn">تسجيل الخروج</button>
                </form>

                <div class="nav-divider"></div>

                <a href="{{ url('/boxes') }}" class="nav-link-item">باقاتي</a>

                <span id="nav-greeting" class="welcome-text">
                    مرحباً، {{ Auth::user()->name }}
                </span>
            @endauth
        </div>
    </div>
</nav>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const greeting = document.getElementById('nav-greeting');
        if (greeting) {
            setTimeout(() => {
                greeting.style.opacity = '0';
                setTimeout(() => {
                    greeting.textContent = "{{ Auth::user()->name }}";
                    greeting.style.opacity = '1';
                }, 500);
            }, 4000);
        }
    });
</script>

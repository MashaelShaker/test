<style>
    .navbar-logo-link {
        display: inline-flex;
        align-items: center;
        gap: 0.75rem;
    }

    .navbar-logo {
        height: 64px;
        max-height: 64px;
        object-fit: contain;
    }



    .navbar-logout-link {
        border: none;
        background: transparent;
        color: #dc2626;
        padding: 0.4rem 0.8rem;
        border-radius: 9999px;
        transition: background 0.2s ease;
        cursor: pointer;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
    }

    .navbar-logout-link:hover {
        background: rgba(220, 38, 38, 0.08);
        text-decoration: none;
    }

    @media (max-width: 768px) {
        .navbar-logo {
            height: 52px;
            max-height: 52px;
        }
    }
</style>

<nav class="navbar navbar-expand-md navbar-light bg-white shadow-sm">
    <div class="container">
        <a class="navbar-brand navbar-logo-link" href="{{ url('/boxes') }}" aria-label="باقاتي">
            <img class="navbar-logo" src="{{ asset('images/Boxy_logo.png') }}" alt="Boxy">
        </a>

        <button class="navbar-toggler" type="button" data-toggle="collapse"
                data-target="#navbarSupportedContent"
                aria-controls="navbarSupportedContent"
                aria-expanded="false"
                aria-label="{{ __('Toggle navigation') }}">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarSupportedContent">
            <ul class="navbar-nav mr-auto"></ul>

            <ul class="navbar-nav ml-auto align-items-center">
                @guest
                    @if (Route::has('login'))
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('login') }}">{{ __('Login') }}</a>
                        </li>
                    @endif
                    @if (Route::has('register'))
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('register') }}">{{ __('Register') }}</a>
                        </li>
                    @endif
                @else
                    <li class="nav-item d-flex align-items-center gap-2">
                        <span class="nav-link text-muted">{{ Auth::user()->name }}</span>
                        <form method="POST" action="{{ route('logout') }}" class="m-0">
                            @csrf
                            <button type="submit" class="navbar-logout-link">
                                Logout
                            </button>
                        </form>
                    </li>
                @endguest
            </ul>
        </div>
    </div>
</nav>

@if (!Request::is('login'))
<!-- Topbar header -->
<header class="topbar" data-navbarbg="skin6">
    <nav class="navbar top-navbar navbar-expand-md navbar-light">
        <div class="navbar-header" data-logobg="skin6">
            <!-- Logo -->
            <a class="navbar-brand" href="#">
                <!-- Logo icon -->
                <b class="logo-icon">
                    <img src="{{ asset('assets/images/logo-icon.png') }}" alt="homepage" class="dark-logo" />
                    <img src="{{ asset('assets/images/logo-light-icon.png') }}" alt="homepage" class="light-logo" />
                </b>
                <!-- Logo text -->
                <span class="logo-text">
                    <img src="{{ asset('assets/images/logo-text.png') }}" alt="homepage" class="dark-logo" />
                    <img src="{{ asset('assets/images/logo-light-text.png') }}" class="light-logo" alt="homepage" />
                </span>
            </a>
            <!-- Sidebar toggle (mobile) -->
            <a class="nav-toggler waves-effect waves-light d-block d-md-none" href="javascript:void(0)">
                <i class="mdi mdi-menu"></i>
            </a>
        </div>

        <div class="navbar-collapse collapse" id="navbarSupportedContent" data-navbarbg="skin5">
            <!-- Left items -->
            <ul class="navbar-nav float-start me-auto">
                <li class="nav-item search-box">
                    <a class="nav-link waves-effect waves-dark" href="javascript:void(0)">
                        <i class="mdi mdi-magnify me-1"></i>
                        <span class="font-16">Search</span>
                    </a>
                    <form class="app-search position-absolute">
                        <input type="text" class="form-control" placeholder="Search &amp; enter">
                        <a class="srh-btn"><i class="mdi mdi-window-close"></i></a>
                    </form>
                </li>
            </ul>

            <!-- Right items (User Dropdown) -->
            <ul class="navbar-nav float-end">
                @if(Auth::check())
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle text-muted waves-effect waves-dark pro-pic"
                        href="#" id="userDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fa fa-user"></i>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end user-dd animated" aria-labelledby="userDropdown">
                        <li>
                            <a class="dropdown-item" href="javascript:void(0)">
                                <i class="ti-wallet m-r-5 m-l-5"></i>
                                {{ Auth::user()->name }} ({{ Auth::user()->role }})
                            </a>
                        </li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <a class="dropdown-item" href="{{ route('logout') }}"
                                onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                                <i class="ti-power-off m-r-5 m-l-5"></i> Logout
                            </a>
                            <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                                @csrf
                            </form>
                        </li>
                    </ul>
                </li>
                @else
                <li>
                    <a class="nav-link text-muted" href="{{ route('login') }}">
                        <i class="fa fa-sign-in-alt"></i> Login
                    </a>
                </li>
                @endif
            </ul>
        </div>
    </nav>
</header>
<!-- End Topbar header -->
@endif

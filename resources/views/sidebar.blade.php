<div class="col-md-2 d-none d-md-block bg-dark sidebar min-vh-100 px-3 py-4">
    <style>
        .sidebar .nav-link {
            transition: background-color 0.18s ease, transform 0.12s ease;
            cursor: pointer;
        }

        .sidebar .nav-link:hover {
            border-bottom: 2px solid #8A244B;
            background-color: rgba(138, 36, 75, 0.12);
            border-radius: 6px;
        }

        .sidebar .nav-link:hover i {
            transform: translateX(-3px);
            transition: transform 0.12s ease;
        }

        .sidebar .nav-link.active {
            background-color: #8A244B;
        }

        /* ensure offcanvas (mobile) links get same hover behaviour */
        .offcanvas .nav-link {
            transition: background-color 0.18s ease, transform 0.12s ease;
            cursor: pointer;
        }

        .offcanvas .nav-link:hover {
            background-color: rgba(138, 36, 75, 0.12);
            border-radius: 6px;
        }

        .offcanvas .nav-link:hover i {
            transform: translateX(-3px);
            transition: transform 0.12s ease;
        }

        .offcanvas .nav-link.active {
            background-color: #8A244B;
        }
    </style>
    <div class="position-sticky" style="top: 56px;">
        <div class="text-center mb-4">
            <img src="/Image/logo.jpg" alt="Logo" class="rounded-circle mb-2" style="width: 60px; height: 60px;">
            <h5 class="mb-0 text-white">DBADS</h5>
        </div>
        <ul class="nav flex-column gap-1">
            <li class="nav-item">
                <a class="nav-link text-white @if (request()->routeIs('dashboard.main')) active @endif" href="/"
                    onclick="window.location.href=this.href">
                    <i class="bi bi-bar-chart me-2"></i>Statistics
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link text-white @if (request()->is('calculate')) active @endif" href="/calculate"
                    onclick="window.location.href=this.href">
                    <i class="bi bi-calculator me-2"></i>Calculate
                </a>
            </li>
        </ul>
        @include('calculator')
    </div>
</div>

<!-- Mobile offcanvas sidebar (visible on small screens) -->
<div class="offcanvas offcanvas-start d-md-none" tabindex="-1" id="mobileSidebar" aria-labelledby="mobileSidebarLabel">
    <div class="offcanvas-header bg-dark">
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas"
            aria-label="Close"></button>
    </div>
    <div class="offcanvas-body bg-dark text-white">
        <div class="text-center mb-4">
            <img src="/Image/logo.jpg" alt="Logo" class="rounded-circle mb-2" style="width: 60px; height: 60px;">
            <h5 class="mb-0 text-white">DBADS</h5>
        </div>
        <ul class="nav flex-column gap-1">
            <li class="nav-item">
                <a class="nav-link text-white @if (request()->routeIs('dashboard.main')) active @endif" href="/"
                    data-bs-dismiss="offcanvas" onclick="window.location.href=this.href">
                    <i class="bi bi-bar-chart me-2"></i>Statistics
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link text-white @if (request()->is('calculate')) active @endif" href="/calculate"
                    data-bs-dismiss="offcanvas" onclick="window.location.href=this.href">
                    <i class="bi bi-calculator me-2"></i>Calculate
                </a>
            </li>
        </ul>
        @include('calculator')
    </div>
</div>

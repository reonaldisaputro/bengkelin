<!-- Main Sidebar Container -->
<aside class="main-sidebar sidebar-dark-primary elevation-4">
    <!-- Brand Logo -->
    <a href="{{ route('adminindex') }}"class="brand-link">
        <img src="{{ asset('css/logo.png') }}" alt="Logo RXS" class="brand-image img-circle elevation-3"
            style="opacity: .8">
        <span class="brand-text font-weight-light">CARS</span>
    </a>

    <!-- Sidebar -->
    <div class="sidebar">
        <!-- Sidebar user (optional) -->
        <div class="user-panel mt-3 pb-3 mb-3">
            <div class="info">
                <h5 class="text-white" style="margin: 0;"> {{ Auth::user()->name }}</h5>
            </div>
        </div>


        <!-- Sidebar Menu -->
        <nav class="mt-2">
            <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu"
                data-accordion="false">
                <!-- Add icons to the links using the .nav-icon class
               with font-awesome or any other icon font library -->
                <li class="nav-item">
                    <a href="{{ route('adminindex') }}" class="nav-link">
                        <i class="nav-icon fas fa-tachometer-alt"></i>
                        <p>
                            Dashboard
                            <i class="right fas fa-angle-left"></i>
                        </p>
                    </a>
                </li>

                <li class="nav-item">
                    <a href="{{ route('showlistuser') }}" class="nav-link">
                        <i class="nav-icon fas fa-users"></i>
                        <p>
                            List User
                            <i class="right fas fa-angle-left"></i>
                        </p>
                    </a>
                </li>

                <li class="nav-item">
                    <a href="{{ route('showlistowner') }}" class="nav-link">
                        <i class="nav-icon fas fa-users"></i>
                        <p>
                            List Owner
                            <i class="right fas fa-angle-left"></i>
                        </p>
                    </a>
                </li>

                <li class="nav-item">
                    <a href="{{ route('showlistbengkel') }}" class="nav-link">
                        <i class="nav-icon fas fa-users"></i>
                        <p>
                            List Bengkel
                            <i class="right fas fa-angle-left"></i>
                        </p>
                    </a>
                </li>

                <li class="nav-item">
                    <a href="{{ route('showlistbooking') }}" class="nav-link">
                        <i class="nav-icon fas fa-users"></i>
                        <p>
                            List Booking
                            <i class="right fas fa-angle-left"></i>
                        </p>
                    </a>
                </li>

                <li class="nav-item">
                    <a href="{{ route('showlisttransaction') }}" class="nav-link">
                        <i class="nav-icon fas fa-users"></i>
                        <p>
                            List Transaction
                            <i class="right fas fa-angle-left"></i>
                        </p>
                    </a>
                </li>

                <li class="nav-header">MASTER DATA</li>

                <li class="nav-item">
                    <a href="{{ route('admin.category.index') }}" class="nav-link">
                        <i class="nav-icon fas fa-tags"></i>
                        <p>
                            Kategori Produk
                            <i class="right fas fa-angle-left"></i>
                        </p>
                    </a>
                </li>

                <li class="nav-item">
                    <a href="{{ route('admin.pencairan') }}" class="nav-link">
                        <i class="nav-icon fas fa-users"></i>
                        <p>
                            Laporan Pencairan
                            <i class="right fas fa-angle-left"></i>
                        </p>
                    </a>
                </li>
            </ul>
        </nav>
        <!-- /.sidebar-menu -->
    </div>
    <!-- /.sidebar -->
</aside>

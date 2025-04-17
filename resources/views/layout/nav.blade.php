<!-- Left Sidebar -->
<aside class="left-sidebar" data-sidebarbg="skin6">
    <div class="scroll-sidebar">
        <nav class="sidebar-nav">
            <ul id="sidebarnav">
                <li class="sidebar-item {{ Request::is('dashboard') ? 'active' : '' }}">
                    <a class="sidebar-link waves-effect waves-dark sidebar-link" href="#" aria-expanded="false">
                        <i class="mdi mdi-view-dashboard"></i>
                        <span class="hide-menu">Dashboard</span>
                    </a>
                </li>
                <li class="sidebar-item {{ Request::is('product') ? 'active' : '' }}">
                    <a class="sidebar-link waves-effect waves-dark sidebar-link" href="{{ url('/product') }}" aria-expanded="false">
                        <i class="mdi mdi-package-variant-closed"></i>
                        <span class="hide-menu">Product</span>
                    </a>
                </li>


                <li class="sidebar-item {{ Request::is('pembelian') ? 'active' : '' }}">
                    <a class="sidebar-link waves-effect waves-dark sidebar-link" href="{{ url('/sales') }}" aria-expanded="false">
                        <i class="mdi mdi-cart"></i>
                        <span class="hide-menu">Pembelian</span>
                    </a>
                </li>
                <li class="sidebar-item {{ Request::is('user') ? 'active' : '' }}">
                    <a class="sidebar-link waves-effect waves-dark sidebar-link" href="#" aria-expanded="false">
                        <i class="mdi mdi-account-network"></i>
                        <span class="hide-menu">User</span>
                    </a>
                </li>


            </ul>
        </nav>
    </div>
</aside>
<!-- End Left Sidebar -->

<!-- ========== App Menu ========== -->
<div class="app-menu navbar-menu">
    <!-- LOGO -->
    <div class="navbar-brand-box">
        <!-- Dark Logo-->
        <a href="{{ route('root') }}" class="logo logo-dark">
            <span class="logo-sm">
                <img src="{{ URL::asset('build/images/logo.png') }}" alt="Oracle Machine Tech" height="28">
            </span>
            <span class="logo-lg">
                <img src="{{ URL::asset('build/images/oracallogo.png') }}" alt="Oracle Machine Tech" height="26">
            </span>
        </a>
        <!-- Light Logo-->
        <a href="{{ route('root') }}" class="logo logo-light">
            <span class="logo-sm">
                <img src="{{ URL::asset('build/images/logo.png') }}" alt="Oracle Machine Tech" height="28">
            </span>
            <span class="logo-lg">
                <img src="{{ URL::asset('build/images/logo2.png') }}" alt="Oracle Machine Tech" height="26">
            </span>
        </a>
        <button type="button" class="btn btn-sm p-0 fs-20 header-item float-end btn-vertical-sm-hover" id="vertical-hover" title="Pin sidebar" aria-label="Pin sidebar">
            <i class="ri-record-circle-line"></i>
        </button>
    </div>

    <div id="scrollbar">
        <div class="container-fluid">

            <div id="two-column-menu">
            </div>
            <ul class="navbar-nav" id="navbar-nav">
                <li class="menu-title"><span>@lang('translation.menu')</span></li>
                @can('dashboard.view')
                <li class="nav-item">
                    <a class="nav-link menu-link" href="{{ route('root') }}">
                        <i class="ri-dashboard-3-line"></i> <span>@lang('translation.dashboards')</span>
                    </a>
                </li> <!-- end Dashboard Menu -->
                @endcan

                @can('reports.view')
                <li class="nav-item">
                    <a class="nav-link menu-link" href="{{ route('reports.index') }}">
                        <i class="ri-bar-chart-box-line"></i> <span>Reports</span>
                    </a>
                </li>
                @endcan

                @can('accounting.view')
                <li class="nav-item">
                    <a class="nav-link menu-link" href="{{ route('accounting.chart') }}">
                        <i class="ri-book-3-line"></i> <span>Accounting</span>
                    </a>
                </li>
                @endcan

                @can('expenses.view')
                <li class="nav-item">
                    <a class="nav-link menu-link" href="{{ route('expenses.index') }}">
                        <i class="ri-exchange-dollar-line"></i> <span>Daily Expenses</span>
                    </a>
                </li>
                @endcan

                @canany(['products.view', 'products.manage-config', 'client-machines.manage', 'client-machines.view', 'ticket-problem-types.manage', 'vendors.view', 'checklist-templates.manage'])
                <li class="menu-title"><span>Masters</span></li>
                @can('products.view')
                <li class="nav-item">
                    <a href="{{route('product')}}" class="nav-link"><i class="ri-price-tag-3-line"></i><span>@lang('Product')</span></a>
                </li>
                @endcan
                {{-- Fiber Machine Config is per-product (software/laser/power/etc.
                     are all scoped to a single product_id) - there is no single
                     correct destination for a sidebar link, so it's reached via
                     the config icons on each product's own row on the Product
                     list instead (@can('products.manage-config') there). --}}
                @can('client-machines.manage')
                <li class="nav-item">
                    <a href="{{route('admin.client-accounts.index')}}" class="nav-link"><i class="ri-contacts-line"></i><span>@lang('Client Accounts')</span></a>
                </li>
                @endcan
                @can('client-machines.view')
                <li class="nav-item">
                    <a href="{{route('admin.client-machines.index')}}" class="nav-link"><i class="ri-cpu-line"></i><span>@lang('Client Machines')</span></a>
                </li>
                @endcan
                @can('ticket-problem-types.manage')
                <li class="nav-item">
                    <a href="{{route('admin.ticket-problem-types.index')}}" class="nav-link"><i class="ri-error-warning-line"></i><span>@lang('Problem Types')</span></a>
                </li>
                @endcan
                @can('vendors.view')
                <li class="nav-item">
                    <a href="{{route('admin.vendors.index')}}" class="nav-link"><i class="ri-truck-line"></i><span>@lang('Vendors')</span></a>
                </li>
                @endcan
                @can('checklist-templates.manage')
                <li class="nav-item">
                    <a href="{{route('admin.checklist-templates.index')}}" class="nav-link"><i class="ri-list-check-2"></i><span>@lang('Checklist Templates')</span></a>
                </li>
                @endcan
                @endcanany

                @canany(['tickets.view', 'spare-part-requests.view'])
                <li class="menu-title"><span>Support Tickets</span></li>
                @can('tickets.view')
                <li class="nav-item">
                    <a href="{{route('admin.tickets.index')}}" class="nav-link"><i class="ri-customer-service-2-line"></i><span>@lang('Tickets')</span></a>
                </li>
                @endcan
                @can('spare-part-requests.view')
                <li class="nav-item">
                    <a href="{{route('admin.spare-part-requests.index')}}" class="nav-link"><i class="ri-tools-fill"></i><span>@lang('Spare Part Requests')</span></a>
                </li>
                @endcan
                @endcanany

                @can('quotations.view')
                <li class="menu-title"><span>Quotations</span></li>
                <li class="nav-item">
                    <a href="{{route('listqutation')}}" class="nav-link"><i class="ri-file-list-3-line"></i><span>@lang('Quotations')</span></a>
                </li>
                @endcan

                @canany(['invoices.view', 'payment-history.view'])
                <li class="menu-title"><span>Sales</span></li>
                @can('invoices.view')
                <li class="nav-item">
                    <a href="{{route('invoice')}}" class="nav-link"><i class="ri-bill-line"></i><span>@lang('Invoice')</span></a>
                </li>
                @endcan
                @can('payment-history.view')
                <li class="nav-item">
                    <a href="{{route('invoice.histry')}}" class="nav-link"><i class="ri-wallet-2-line"></i><span>@lang('Payment history')</span></a>
                </li>
                @endcan
                @endcanany

                @canany(['jobs.view-own', 'jobs.approve', 'jobs.manage-machines', 'employees.view', 'attendance.manage'])
                <li class="menu-title"><span>Workforce</span></li>
                @can('jobs.view-own')
                <li class="nav-item">
                    <a class="nav-link menu-link" href="{{ route('jobs.index') }}">
                        <i class="ri-briefcase-4-line"></i> <span>Jobs</span>
                    </a>
                </li>
                @endcan
                @can('jobs.approve')
                <li class="nav-item">
                    <a class="nav-link menu-link" href="{{ route('jobs.pending-approval') }}">
                        <i class="ri-inbox-line"></i> <span>Pending Approval</span>
                    </a>
                </li>
                @endcan
                @can('jobs.manage-machines')
                <li class="nav-item">
                    <a class="nav-link menu-link" href="{{ route('machines.index') }}">
                        <i class="ri-tools-line"></i> <span>Machines</span>
                    </a>
                </li>
                @endcan
                @can('employees.view')
                <li class="nav-item">
                    <a class="nav-link menu-link" href="{{ route('employees.index') }}">
                        <i class="ri-team-line"></i> <span>Employees</span>
                    </a>
                </li>
                @endcan
                @can('attendance.manage')
                <li class="nav-item">
                    <a class="nav-link menu-link" href="{{ route('attendance.mark') }}">
                        <i class="ri-calendar-check-line"></i> <span>Attendance</span>
                    </a>
                </li>
                @endcan
                @endcanany

                @if(auth()->user()->canAny(['admin.manage-roles', 'admin.manage-users']) || app(\App\Services\Auditing\AuditLogService::class)->hasAnyAuditAccess(auth()->user()))
                <li class="menu-title"><span>Admin</span></li>
                @can('admin.manage-roles')
                <li class="nav-item">
                    <a href="{{ route('admin.roles.index') }}" class="nav-link"><i class="ri-shield-user-line"></i><span>Roles & Permissions</span></a>
                </li>
                @endcan
                @can('admin.manage-users')
                <li class="nav-item">
                    <a href="{{ route('admin.users.index') }}" class="nav-link"><i class="ri-user-settings-line"></i><span>Users</span></a>
                </li>
                @endcan
                @if(app(\App\Services\Auditing\AuditLogService::class)->hasAnyAuditAccess(auth()->user()))
                <li class="nav-item">
                    <a href="{{ route('admin.audit-logs.index') }}" class="nav-link"><i class="ri-history-line"></i><span>Audit Log</span></a>
                </li>
                @endif
                @endif

            </ul>
        </div>
        <!-- Sidebar -->
    </div>
    <div class="sidebar-background"></div>
</div>
<!-- Left Sidebar End -->
<!-- Vertical Overlay-->
<div class="vertical-overlay"></div>
<script>
    // These menu-expand links carry role="button" for assistive tech, which per
    // WAI-ARIA authoring practices means they should also respond to the Space
    // key. Native <a> elements already respond to Enter on their own (browser
    // default), so only Space needs a synthetic click here - handling Enter
    // too would double-fire the click. Purely additive - does not change
    // existing click/collapse behavior.
    document.addEventListener('keydown', function (event) {
        if (event.key !== ' ') {
            return;
        }
        var target = event.target.closest('.nav-link[role="button"]');
        if (!target) {
            return;
        }
        event.preventDefault();
        target.click();
    });
</script>

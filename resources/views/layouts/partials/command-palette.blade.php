{{-- ⌘K / Ctrl+K command palette. Commands mirror the sidebar and are
     permission-gated, so each role only sees what it can actually open. --}}
<style>
    .cmdk-overlay {
        position: fixed; inset: 0; z-index: 1090;
        background: rgba(13, 27, 72, .45);
        display: none; align-items: flex-start; justify-content: center;
        padding-top: 12vh;
    }
    .cmdk-overlay.open { display: flex; }
    .cmdk-panel {
        width: 100%; max-width: 600px; margin: 0 16px;
        background: var(--vz-secondary-bg, #fff); color: var(--vz-body-color, #212529);
        border: 1px solid var(--vz-border-color, #e9ebec);
        border-radius: 12px; box-shadow: 0 20px 60px rgba(13, 27, 72, .35);
        overflow: hidden; display: flex; flex-direction: column; max-height: 70vh;
    }
    .cmdk-search { display: flex; align-items: center; gap: 10px; padding: 14px 16px; border-bottom: 1px solid var(--vz-border-color, #e9ebec); }
    .cmdk-search i { font-size: 20px; color: var(--vz-secondary-color, #878a99); }
    .cmdk-search input { flex: 1; border: 0; outline: 0; background: transparent; font-size: 15px; color: inherit; }
    .cmdk-esc { font-size: 11px; color: var(--vz-secondary-color, #878a99); border: 1px solid var(--vz-border-color, #e9ebec); border-radius: 5px; padding: 2px 6px; }
    .cmdk-list { overflow-y: auto; padding: 8px; }
    .cmdk-item { display: flex; align-items: center; gap: 12px; padding: 10px 12px; border-radius: 8px; color: inherit; text-decoration: none; cursor: pointer; }
    .cmdk-item i { font-size: 18px; color: var(--vz-secondary-color, #878a99); width: 20px; text-align: center; }
    .cmdk-item .cmdk-group { margin-left: auto; font-size: 11px; text-transform: uppercase; letter-spacing: .04em; color: var(--vz-secondary-color, #878a99); }
    .cmdk-item.active, .cmdk-item:hover { background: var(--vz-primary, #2954a6); color: #fff; }
    .cmdk-item.active i, .cmdk-item:hover i, .cmdk-item.active .cmdk-group, .cmdk-item:hover .cmdk-group { color: rgba(255, 255, 255, .85); }
    .cmdk-empty { padding: 24px; text-align: center; color: var(--vz-secondary-color, #878a99); }
    .cmdk-hint { padding: 8px 14px; border-top: 1px solid var(--vz-border-color, #e9ebec); font-size: 11px; color: var(--vz-secondary-color, #878a99); display: flex; gap: 14px; }
</style>

{{-- role="dialog" kept deliberately instead of a native <dialog> element:
     tried the conversion, but this app's bundled Bootstrap CSS styles the
     bare `dialog` tag selector as part of its own Modal component
     (position/width/margin/pointer-events), which fights this component's
     custom centering/backdrop and visibly breaks the layout. The ARIA
     pattern below is a standard, accessible substitute. --}}
<div id="commandPalette" class="cmdk-overlay" role="dialog" aria-modal="true" aria-label="Command palette">
    <div class="cmdk-panel">
        <div class="cmdk-search">
            <i class="ri-search-line"></i>
            <input id="cmdkInput" type="text" placeholder="Search pages and actions…" autocomplete="off" aria-label="Search pages and actions">
            <span class="cmdk-esc">ESC</span>
        </div>
        <div class="cmdk-list" id="cmdkList">
            @php
                // [route, label, icon, group, keywords, permission(null = always)]
                $commands = [
                    ['root', 'Dashboard', 'ri-dashboard-3-line', 'Menu', 'home overview', 'dashboard.view'],
                    ['reports.index', 'Reports', 'ri-bar-chart-box-line', 'Menu', 'analytics', 'reports.view'],
                    ['accounting.chart', 'Chart of Accounts', 'ri-book-3-line', 'Accounting', 'ledger accounts', 'accounting.view'],
                    ['accounting.profit-loss', 'Profit & Loss', 'ri-line-chart-line', 'Accounting', 'p&l pnl income expense', 'accounting.view'],
                    ['accounting.trial-balance', 'Trial Balance', 'ri-scales-3-line', 'Accounting', 'debit credit', 'accounting.view'],
                    ['expenses.index', 'Daily Expenses', 'ri-exchange-dollar-line', 'Menu', 'cash transactions', 'expenses.view'],
                    ['expenses.cashbook', 'Cash Book', 'ri-book-2-line', 'Accounting', 'ledger cash', 'expenses.view'],
                    ['product', 'Products', 'ri-price-tag-3-line', 'Masters', 'items catalogue spare parts', 'products.view'],
                    ['admin.client-accounts.index', 'Client Accounts', 'ri-contacts-line', 'Masters', 'customers portal', 'client-machines.manage'],
                    ['admin.client-machines.index', 'Client Machines', 'ri-cpu-line', 'Masters', 'installed', 'client-machines.view'],
                    ['admin.ticket-problem-types.index', 'Problem Types', 'ri-error-warning-line', 'Masters', 'ticket categories', 'ticket-problem-types.manage'],
                    ['admin.vendors.index', 'Vendors', 'ri-truck-line', 'Masters', 'suppliers', 'vendors.view'],
                    ['admin.tickets.index', 'Tickets', 'ri-customer-service-2-line', 'Support', 'support issues', 'tickets.view'],
                    ['admin.spare-part-requests.index', 'Spare Part Requests', 'ri-tools-fill', 'Support', 'parts', 'spare-part-requests.view'],
                    ['listqutation', 'Quotations', 'ri-file-list-3-line', 'Sales', 'quotes estimate', 'quotations.view'],
                    ['invoice', 'Invoices', 'ri-bill-line', 'Sales', 'billing', 'invoices.view'],
                    ['invoice.create', 'Create Invoice', 'ri-add-circle-line', 'Actions', 'new invoice bill', 'invoices.create'],
                    ['invoice.histry', 'Payment History', 'ri-wallet-2-line', 'Sales', 'payments received', 'payment-history.view'],
                    ['jobs.index', 'Jobs', 'ri-briefcase-4-line', 'Workforce', 'tasks work', 'jobs.view-own'],
                    ['jobs.pending-approval', 'Pending Approval', 'ri-inbox-line', 'Workforce', 'approve jobs', 'jobs.approve'],
                    ['machines.index', 'Machines', 'ri-tools-line', 'Workforce', 'equipment', 'jobs.manage-machines'],
                    ['employees.index', 'Employees', 'ri-team-line', 'Workforce', 'staff workers', 'employees.view'],
                    ['attendance.mark', 'Attendance', 'ri-calendar-check-line', 'Workforce', 'present absent', 'attendance.manage'],
                    ['admin.roles.index', 'Roles & Permissions', 'ri-shield-user-line', 'Admin', 'access', 'admin.manage-roles'],
                    ['admin.users.index', 'Users', 'ri-user-settings-line', 'Admin', 'accounts staff', 'admin.manage-users'],
                    ['profile', 'My Profile', 'ri-account-circle-line', 'Account', 'settings password avatar', null],
                ];
            @endphp
            @foreach($commands as [$route, $label, $icon, $group, $keywords, $permission])
                @if($permission === null || auth()->user()->can($permission))
                    @if(\Illuminate\Support\Facades\Route::has($route))
                        <a href="{{ route($route) }}" class="cmdk-item" data-search="{{ strtolower($label . ' ' . $keywords . ' ' . $group) }}">
                            <i class="{{ $icon }}"></i>
                            <span>{{ $label }}</span>
                            <span class="cmdk-group">{{ $group }}</span>
                        </a>
                    @endif
                @endif
            @endforeach
            <div class="cmdk-empty" id="cmdkEmpty" style="display:none">No matching pages or actions.</div>
        </div>
        <div class="cmdk-hint">
            <span>↑ ↓ to navigate</span><span>↵ to open</span><span>esc to close</span>
        </div>
    </div>
</div>

<script>
    (function () {
        var overlay = document.getElementById('commandPalette');
        if (!overlay) return;
        var input = document.getElementById('cmdkInput');
        var list = document.getElementById('cmdkList');
        var empty = document.getElementById('cmdkEmpty');
        var items = Array.prototype.slice.call(list.querySelectorAll('.cmdk-item'));
        var activeIndex = -1;

        function visibleItems() { return items.filter(function (el) { return el.style.display !== 'none'; }); }

        function setActive(idx) {
            var vis = visibleItems();
            items.forEach(function (el) { el.classList.remove('active'); });
            if (vis.length === 0) { activeIndex = -1; return; }
            activeIndex = (idx + vis.length) % vis.length;
            vis[activeIndex].classList.add('active');
            vis[activeIndex].scrollIntoView({ block: 'nearest' });
        }

        function filter() {
            var q = input.value.trim().toLowerCase();
            var anyVisible = false;
            items.forEach(function (el) {
                var match = q === '' || el.getAttribute('data-search').indexOf(q) !== -1;
                el.style.display = match ? 'flex' : 'none';
                if (match) anyVisible = true;
            });
            empty.style.display = anyVisible ? 'none' : 'block';
            setActive(0);
        }

        function open() {
            overlay.classList.add('open');
            input.value = '';
            filter();
            setTimeout(function () { input.focus(); }, 0);
        }
        function close() { overlay.classList.remove('open'); }

        // Cmd/Ctrl+K toggles; Esc closes.
        document.addEventListener('keydown', function (e) {
            if ((e.metaKey || e.ctrlKey) && e.key.toLowerCase() === 'k') {
                e.preventDefault();
                overlay.classList.contains('open') ? close() : open();
            } else if (e.key === 'Escape' && overlay.classList.contains('open')) {
                close();
            }
        });

        input.addEventListener('input', filter);
        input.addEventListener('keydown', function (e) {
            if (e.key === 'ArrowDown') { e.preventDefault(); setActive(activeIndex + 1); }
            else if (e.key === 'ArrowUp') { e.preventDefault(); setActive(activeIndex - 1); }
            else if (e.key === 'Enter') {
                e.preventDefault();
                var vis = visibleItems();
                if (vis[activeIndex]) window.location.href = vis[activeIndex].href;
            }
        });

        // Click outside the panel closes it.
        overlay.addEventListener('click', function (e) { if (e.target === overlay) close(); });

        // Topbar trigger(s).
        document.querySelectorAll('[data-cmdk-open]').forEach(function (btn) {
            btn.addEventListener('click', function (e) { e.preventDefault(); open(); });
        });
    })();
</script>

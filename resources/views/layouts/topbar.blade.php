@auth
<div id="push-subscribe-banner" data-vapid-key="{{ config('webpush.vapid.public_key') }}" class="alert alert-info d-flex justify-content-between align-items-center m-0 rounded-0" style="display:none">
    <span><i class="ri-notification-3-line"></i> Enable notifications for job updates?</span>
    <span>
        <x-ui.button variant="primary" size="sm" data-push-enable type="button">Enable</x-ui.button>
        <x-ui.button variant="secondary" size="sm" data-push-dismiss type="button" icon="ri-close-line" ariaLabel="Dismiss">Not now</x-ui.button>
    </span>
</div>
<script>
    if (typeof Notification !== 'undefined' && Notification.permission === 'default') {
        var banner = document.getElementById('push-subscribe-banner');
        if (banner) { banner.style.display = 'flex'; }
    }
</script>
<script src="{{ asset('build/js/push-subscribe.js') }}"></script>
@endauth
<header id="page-topbar">
    <div class="layout-width">
        <div class="navbar-header">
            <div class="d-flex">
                <!-- LOGO -->
                <div class="navbar-brand-box horizontal-logo">
                    <a href="index" class="logo logo-dark">
                        <span class="logo-sm">
                            <img src="{{ URL::asset('build/images/logo-sm.png') }}" alt="" height="22">
                        </span>
                        <span class="logo-lg">
                            <img src="{{ URL::asset('build/images/logo-dark.png') }}" alt="" height="17">
                        </span>
                    </a>

                    <a href="index" class="logo logo-light">
                        <span class="logo-sm">
                            <img src="{{ URL::asset('build/images/logo-sm.png') }}" alt="" height="22">
                        </span>
                        <span class="logo-lg">
                            <img src="{{ URL::asset('build/images/logo-light.png') }}" alt="" height="17">
                        </span>
                    </a>
                </div>

                <button type="button" class="btn btn-icon btn-topbar btn-ghost-secondary rounded-circle header-item vertical-menu-btn topnav-hamburger" id="topnav-hamburger-icon" title="Toggle sidebar" aria-label="Toggle sidebar">
                    <span class="hamburger-icon">
                        <span></span>
                        <span></span>
                        <span></span>
                    </span>
                </button>
            </div>

            <div class="d-flex align-items-center">

                <div class="dropdown ms-1 topbar-head-dropdown header-item">
                    <button type="button" class="btn btn-icon btn-topbar btn-ghost-secondary rounded-circle" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <img src="{{ URL::asset('build/images/flags/in.svg') }}" class="rounded" alt="Header Language" height="20">
                    </button>
                    <div class="dropdown-menu dropdown-menu-end">

                        <!-- item-->
                        <a href="{{ url('index/en') }}" class="dropdown-item notify-item language py-2" data-lang="en" title="English">
                            <img src="{{ URL::asset('build/images/flags/in.svg') }}" alt="India flag" class="me-2 rounded" height="20">
                            <span class="align-middle">English</span>
                        </a>
                    </div>
                </div>

                <div class="ms-1 header-item d-none d-sm-flex">
                    <button type="button" class="btn btn-icon btn-topbar btn-ghost-secondary rounded-circle" data-toggle="fullscreen" title="Toggle fullscreen" aria-label="Toggle fullscreen">
                        <i class='ri-fullscreen-line fs-22'></i>
                    </button>
                </div>

                <div class="ms-1 header-item d-none d-sm-flex">
                    <button type="button" class="btn btn-icon btn-topbar btn-ghost-secondary rounded-circle light-dark-mode" title="Toggle dark mode" aria-label="Toggle dark mode">
                        <i class='ri-moon-line fs-22'></i>
                    </button>
                </div>

                <div class="dropdown topbar-head-dropdown ms-1 header-item" id="notificationDropdown">
                    <button type="button" class="btn btn-icon btn-topbar btn-ghost-secondary rounded-circle" id="page-header-notifications-dropdown" data-bs-toggle="dropdown" data-bs-auto-close="outside" aria-haspopup="true" aria-expanded="false">
                        <i class='ri-notification-3-line fs-22'></i>
                        <span class="position-absolute topbar-badge fs-10 translate-middle badge rounded-pill bg-danger">{{ $unreadJobNotificationCount }}<span class="visually-hidden">unread messages</span></span>
                    </button>
                    <div class="dropdown-menu dropdown-menu-lg dropdown-menu-end p-0" aria-labelledby="page-header-notifications-dropdown">

                        <div class="dropdown-head bg-primary bg-pattern rounded-top">
                            <div class="p-3">
                                <div class="row align-items-center">
                                    <div class="col">
                                        <h6 class="m-0 fs-16 fw-semibold text-white"> Notifications </h6>
                                    </div>
                                    <div class="col-auto">
                                        <span class="badge bg-light-subtle text-body fs-13"> {{ $unreadJobNotificationCount }} New</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="tab-content position-relative" id="notificationItemsTabContent">
                            <div class="tab-pane fade show active py-2 ps-2" id="all-noti-tab" role="tabpanel">
                                <div data-simplebar style="max-height: 300px;" class="pe-2">
                                    @forelse ($jobNotifications as $notification)
                                        <div class="text-reset notification-item d-block dropdown-item position-relative">
                                            <div class="d-flex">
                                                @php
                                                    // Each notification class stores a different data shape, so
                                                    // derive the icon/text/link per type rather than assuming a
                                                    // job-decision payload. Missing keys fall back safely.
                                                    $d = $notification->data;
                                                    [$notifVariant, $notifIcon, $notifTitle, $notifText, $notifLink] = match (class_basename($notification->type)) {
                                                        'JobDecisionNotification' => [
                                                            ($d['decision'] ?? '') === 'approved' ? 'success' : 'danger',
                                                            ($d['decision'] ?? '') === 'approved' ? 'ri-checkbox-circle-line' : 'ri-close-circle-line',
                                                            $d['job_title'] ?? 'Job',
                                                            (($d['decision'] ?? '') === 'approved' ? 'Job request approved.' : 'Job request rejected.')
                                                                . ((($d['decision'] ?? '') === 'rejected' && ! empty($d['reason'])) ? ' Reason: ' . $d['reason'] : ''),
                                                            route('jobs.show', $d['job_id'] ?? 0),
                                                        ],
                                                        'JobOverdueNotification' => [
                                                            'danger', 'ri-alarm-warning-line', $d['job_title'] ?? 'Job',
                                                            'Job is overdue' . (! empty($d['due_date']) ? ' (due ' . \Illuminate\Support\Carbon::parse($d['due_date'])->format('d M Y') . ')' : '') . '.',
                                                            route('jobs.show', $d['job_id'] ?? 0),
                                                        ],
                                                        'LowStockNotification' => [
                                                            'warning', 'ri-stack-line', $d['product_name'] ?? 'Inventory item',
                                                            'Low stock — only ' . ($d['quantity'] ?? 0) . ' left.',
                                                            route('invoice.inventrylist'),
                                                        ],
                                                        'MachineDownNotification' => [
                                                            'danger', 'ri-error-warning-line', 'Machine reported down',
                                                            'Reported by ' . ($d['reported_by'] ?? 'a worker') . '.',
                                                            route('jobs.show', $d['job_id'] ?? 0),
                                                        ],
                                                        'TicketStatusChangedNotification' => [
                                                            'info', 'ri-customer-service-2-line', 'Support ticket #' . ($d['ticket_id'] ?? ''),
                                                            'Status changed to ' . ucfirst(str_replace('_', ' ', $d['status'] ?? 'updated')) . '.',
                                                            route('admin.tickets.show', $d['ticket_id'] ?? 0),
                                                        ],
                                                        // JobAssignedNotification and any unknown type.
                                                        default => [
                                                            'info', 'ri-user-shared-line', $d['job_title'] ?? 'Notification',
                                                            ($d['decision'] ?? '') === 'reassigned' ? 'Job reassigned to you.' : 'Job assigned to you.',
                                                            isset($d['job_id']) ? route('jobs.show', $d['job_id']) : '#',
                                                        ],
                                                    };
                                                @endphp
                                                <div class="avatar-xs me-3 flex-shrink-0">
                                                    <span class="avatar-title bg-{{ $notifVariant }}-subtle text-{{ $notifVariant }} rounded-circle fs-16">
                                                        <i class="{{ $notifIcon }}"></i>
                                                    </span>
                                                </div>
                                                <div class="flex-grow-1">
                                                    <a href="{{ $notifLink }}" class="stretched-link">
                                                        <h6 class="mt-0 mb-1 fs-13 fw-semibold">{{ $notifTitle }}</h6>
                                                    </a>
                                                    <div class="fs-13 text-muted">
                                                        <p class="mb-1">{{ $notifText }}</p>
                                                    </div>
                                                    <p class="mb-0 fs-11 fw-medium text-uppercase text-muted">
                                                        <span><i class="ri-time-line"></i> {{ $notification->created_at->diffForHumans() }}</span>
                                                    </p>
                                                </div>
                                            </div>
                                        </div>
                                    @empty
                                        <x-ui.empty-state icon="ri-notification-off-line" message="No notifications yet." />
                                    @endforelse

                                </div>

                            </div>
                        </div>
                    </div>
                </div>

                <div class="dropdown ms-sm-3 header-item topbar-user">
                    <button type="button" class="btn" id="page-header-user-dropdown" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <span class="d-flex align-items-center">
                            <img class="rounded-circle header-profile-user" src="@if (Auth::user()->avatar != ''){{ URL::asset('images/' . Auth::user()->avatar) }}@else{{ URL::asset('build/images/users/avatar-1.jpg') }}@endif" alt="Header Avatar">
                            <span class="text-start ms-xl-2">
                                <span class="d-none d-xl-inline-block ms-1 fw-medium user-name-text">{{Auth::user()->name}}</span>
                                <span class="d-none d-xl-block ms-1 fs-12 user-name-sub-text">{{ Auth::user()->getRoleNames()->first() ?? '' }}</span>
                            </span>
                        </span>
                    </button>
                    <div class="dropdown-menu dropdown-menu-end">
                        <!-- item-->
                        <h6 class="dropdown-header">Welcome, {{ Auth::user()->name }}!</h6>
                        <a class="dropdown-item" href="{{ route('profile') }}"><i class="ri-account-circle-line font-size-16 align-middle me-1"></i> <span>My Profile</span></a>
                        <a class="dropdown-item" href="{{ route('profile') }}#change-password"><i class="ri-lock-2-line font-size-16 align-middle me-1"></i> <span>Change Password</span></a>
                        <div class="dropdown-divider"></div>
                        <button type="button" class="dropdown-item" onclick="document.getElementById('logout-form').submit();"><i class="ri-logout-box-line font-size-16 align-middle me-1"></i> <span key="t-logout">@lang('translation.logout')</span></button>
                        <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                            @csrf
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</header>

{{-- Kept as an inert, empty shell: public/build/js/app.js unconditionally calls
     document.getElementById('removeNotificationModal').addEventListener(...) on every
     page load with no null-check, so removing this element entirely throws a JS error
     that halts subsequent script execution app-wide. Nothing links to it anymore. --}}
<div id="removeNotificationModal" class="modal fade zoomIn" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" id="NotificationModalbtn-close"></button>
            </div>
            <div class="modal-body">
                <div class="d-flex gap-2 justify-content-center mt-4 mb-2">
                    <button type="button" class="btn w-sm btn-light" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn w-sm btn-danger" id="delete-notification">Yes, Delete It!</button>
                </div>
            </div>
        </div>
    </div>
</div>
</div><!-- /.modal -->


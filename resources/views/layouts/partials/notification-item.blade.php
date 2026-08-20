{{-- Shared by the topbar dropdown and the "view all" notifications page - expects $notification. --}}
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
            <a href="{{ $notifLink }}" class="stretched-link js-notif-link" data-notif-id="{{ $notification->id }}">
                <h6 class="mt-0 mb-1 fs-13 fw-semibold">{{ $notifTitle }} @unless($notification->read_at)<span class="badge bg-primary-subtle text-primary ms-1">new</span>@endunless</h6>
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

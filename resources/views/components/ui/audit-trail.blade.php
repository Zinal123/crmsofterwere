{{-- resources/views/components/ui/audit-trail.blade.php --}}
@props(['logs'])
<div class="table-responsive">
    <table class="table table-sm table-bordered align-middle mb-0">
        <thead>
            <tr>
                <th>Date/Time</th>
                <th>User</th>
                <th>Action</th>
                <th>Field</th>
                <th>Old Value</th>
                <th>New Value</th>
            </tr>
        </thead>
        <tbody>
            @forelse($logs as $log)
                <tr>
                    <td>{{ $log->created_at->format('d M Y, H:i') }}</td>
                    <td>{{ $log->user->name ?? 'System' }}</td>
                    <td>{{ $log->action }}</td>
                    <td>{{ $log->field_name ? str_replace('_', ' ', $log->field_name) : '—' }}</td>
                    <td>{{ $log->old_value ?? '—' }}</td>
                    <td>{{ $log->new_value ?? '—' }}</td>
                </tr>
            @empty
                <tr><td colspan="6"><x-ui.empty-state icon="ri-history-line" message="No history recorded yet." /></td></tr>
            @endforelse
        </tbody>
    </table>
</div>

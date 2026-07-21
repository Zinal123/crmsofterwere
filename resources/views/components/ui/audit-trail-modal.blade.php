{{-- resources/views/components/ui/audit-trail-modal.blade.php --}}
@props(['type'])
<div class="modal fade" id="auditTrailModal-{{ $type }}" tabindex="-1" aria-labelledby="auditTrailModalLabel-{{ $type }}" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="auditTrailModalLabel-{{ $type }}">History</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="auditTrailBody-{{ $type }}">
                <p class="text-muted mb-0">Loading…</p>
            </div>
        </div>
    </div>
</div>
<script>
document.getElementById('auditTrailModal-{{ $type }}').addEventListener('show.bs.modal', function (event) {
    var recordId = event.relatedTarget.getAttribute('data-audit-id');
    var body = document.getElementById('auditTrailBody-{{ $type }}');
    body.innerHTML = '<p class="text-muted mb-0">Loading…</p>';

    fetch('{{ url('audit-logs/' . $type) }}/' + recordId)
        .then(function (response) {
            if (!response.ok) { throw new Error('HTTP ' + response.status); }
            return response.text();
        })
        .then(function (html) { body.innerHTML = html; })
        .catch(function () { body.innerHTML = '<p class="text-danger mb-0">Failed to load history.</p>'; });
});
</script>

@props(['recordType' => 'record'])
<div class="modal fade flip" id="deleteOrder" tabindex="-1" aria-labelledby="deleteOrderLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-body p-5 text-center">
                <lord-icon src="https://cdn.lordicon.com/gsqxdxog.json" trigger="loop" colors="primary:#4361ee,secondary:#f7941d" style="width:90px;height:90px">
                </lord-icon>
                <div class="mt-4 text-center">
                    <h4>You are about to delete this {{ $recordType }}?</h4>
                    <p class="text-muted fs-15 mb-4">Deleting this {{ $recordType }} will remove
                        all of
                        its information from our database.</p>
                    <div class="hstack gap-2 justify-content-center remove">
                        <button class="btn btn-link link-success fw-medium text-decoration-none" data-bs-dismiss="modal" id="deleteRecord-close"><i class="ri-close-line me-1 align-middle"></i>
                            Close</button>
                        <button class="btn btn-danger" id="delete-record">Yes,
                            Delete It</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
document.addEventListener('DOMContentLoaded', function () {
    var modalEl = document.getElementById('deleteOrder');
    if (!modalEl) {
        return;
    }
    var modal = new bootstrap.Modal(modalEl);
    var confirmBtn = document.getElementById('delete-record');
    var pendingForm = null;
    var pendingHref = null;

    document.addEventListener('click', function (event) {
        var trigger = event.target.closest('[data-confirm-delete]');
        if (!trigger) {
            return;
        }
        event.preventDefault();
        pendingForm = trigger.closest('form');
        pendingHref = pendingForm ? null : trigger.getAttribute('href');
        modal.show();
    });

    confirmBtn.addEventListener('click', function () {
        modal.hide();
        if (pendingForm) {
            pendingForm.submit();
        } else if (pendingHref) {
            window.location.href = pendingHref;
        }
    });
});
</script>

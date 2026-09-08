<div class="modal fade" id="deleteConfirmModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirm deletion</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p class="mb-0">
                    Are you sure you want to delete
                    <strong id="deleteConfirmItemName">this item</strong>?
                    This can't be undone.
                </p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                <form id="deleteConfirmForm" method="POST" action="">
                    <input type="hidden" name="id" id="deleteConfirmId" value="">
                    <button type="submit" class="btn btn-danger">Delete</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    function openDeleteConfirm(event, id, itemName, actionUrl) {
        event.preventDefault();

        document.getElementById('deleteConfirmId').value = id;
        document.getElementById('deleteConfirmItemName').textContent = itemName;
        document.getElementById('deleteConfirmForm').action = actionUrl;

        var modal = new bootstrap.Modal(document.getElementById('deleteConfirmModal'));
        modal.show();
    }
</script>
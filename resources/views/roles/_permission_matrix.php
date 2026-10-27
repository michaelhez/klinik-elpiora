<?php
/** @var array $catalogue */
/** @var array $assignedIds */
?>
<div class="permission-matrix">
    <?php foreach ($catalogue as $module => $permissions): ?>
    <div class="border rounded-3 p-3 mb-3">
        <div class="d-flex justify-content-between align-items-center mb-2">
            <h3 class="card-title-sm mb-0 text-capitalize"><?= e(str_replace('_', ' ', $module)) ?></h3>
            <div class="form-check form-switch mb-0">
                <input type="checkbox" class="form-check-input module-toggle" data-module="<?= e($module) ?>">
                <label class="form-check-label small">Pilih Semua</label>
            </div>
        </div>
        <div class="row g-2">
            <?php foreach ($permissions as $permission): ?>
            <div class="col-6 col-md-4 col-lg-3">
                <div class="form-check">
                    <input type="checkbox" class="form-check-input perm-checkbox" data-module="<?= e($module) ?>"
                           id="perm_<?= e($permission['id']) ?>" name="permission_ids[]" value="<?= e($permission['id']) ?>"
                           <?= in_array($permission['id'], $assignedIds, false) ? 'checked' : '' ?>>
                    <label class="form-check-label small" for="perm_<?= e($permission['id']) ?>"><?= e($permission['action']) ?></label>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<script>
document.querySelectorAll('.module-toggle').forEach((toggle) => {
    const module = toggle.dataset.module;
    const checkboxes = document.querySelectorAll(`.perm-checkbox[data-module="${module}"]`);
    toggle.checked = Array.from(checkboxes).every((cb) => cb.checked);

    toggle.addEventListener('change', () => {
        checkboxes.forEach((cb) => { cb.checked = toggle.checked; });
    });

    checkboxes.forEach((cb) => {
        cb.addEventListener('change', () => {
            toggle.checked = Array.from(checkboxes).every((c) => c.checked);
        });
    });
});
</script>

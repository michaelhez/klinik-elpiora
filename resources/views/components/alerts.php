<?php
/** @var string|null $flashSuccess */
/** @var string|null $flashError */
$flashSuccess = get_flash('success');
$flashError = get_flash('error');
?>
<div id="flashData"
     data-success="<?= e($flashSuccess ?? '') ?>"
     data-error="<?= e($flashError ?? '') ?>"
     hidden></div>

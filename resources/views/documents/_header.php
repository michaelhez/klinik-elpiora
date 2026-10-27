<?php
/** @var array $clinic */
?>
<style>
    body { font-family: 'DejaVu Sans', sans-serif; font-size: 12px; color: #1a1a1a; }
    .header { border-bottom: 2px solid #1a1a1a; padding-bottom: 10px; margin-bottom: 20px; overflow: hidden; }
    .header h1 { font-size: 18px; margin: 0 0 4px 0; }
    .header p { margin: 0; font-size: 11px; color: #444; }
    .doc-title { text-align: center; margin: 20px 0; }
    .doc-title h2 { font-size: 15px; text-decoration: underline; margin: 0 0 2px 0; }
    .doc-title p { margin: 0; font-size: 11px; }
    table.info-table { width: 100%; margin-bottom: 14px; }
    table.info-table td { padding: 2px 0; vertical-align: top; }
    table.info-table td.label { width: 160px; }
    table.info-table td.sep { width: 10px; }
    .content { margin: 16px 0; text-align: justify; line-height: 1.6; }
    .signature { margin-top: 50px; width: 220px; float: right; text-align: center; }
    .signature .space { height: 60px; }
</style>
<div class="header">
    <h1><?= e($clinic['name'] ?? 'Klinik') ?></h1>
    <p><?= e($clinic['address'] ?? '-') ?></p>
    <p><?= $clinic['phone'] ? 'Telp: ' . e($clinic['phone']) : '' ?><?= $clinic['email'] ? ' &bull; Email: ' . e($clinic['email']) : '' ?></p>
</div>

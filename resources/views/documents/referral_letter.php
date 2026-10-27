<?php
/** @var array $document */
/** @var array $clinic */
$content = $document['content'];
?>
<!DOCTYPE html>
<html>
<head><meta charset="utf-8"><title><?= e($document['document_number']) ?></title></head>
<body>
<?= \App\Core\View::include('documents._header', ['clinic' => $clinic]) ?>

<div class="doc-title">
    <h2>SURAT RUJUKAN</h2>
    <p>No: <?= e($document['document_number']) ?></p>
</div>

<div class="content">
    Kepada Yth. Sejawat di <strong><?= e($content['destination_facility']) ?></strong>,<br>
    Mohon pemeriksaan/penanganan lebih lanjut terhadap pasien berikut:
</div>

<table class="info-table">
    <tr><td class="label">Nama</td><td class="sep">:</td><td><?= e($document['patient_name']) ?></td></tr>
    <tr><td class="label">No. Rekam Medis</td><td class="sep">:</td><td><?= e($document['medical_record_number']) ?></td></tr>
    <tr><td class="label">Jenis Kelamin</td><td class="sep">:</td><td><?= $document['gender'] === 'MALE' ? 'Laki-laki' : 'Perempuan' ?></td></tr>
    <tr><td class="label">Tanggal Lahir</td><td class="sep">:</td><td><?= $document['birth_date'] ? format_date($document['birth_date']) : '-' ?></td></tr>
    <tr><td class="label">Alamat</td><td class="sep">:</td><td><?= e($document['address'] ?? '-') ?></td></tr>
</table>

<div class="content">
    <strong>Alasan Rujukan:</strong><br>
    <?= nl2br(e($content['reason'])) ?>
</div>

<div class="content">
    Atas perhatian dan kerja samanya, kami ucapkan terima kasih.
</div>

<div class="signature">
    <p><?= format_date($document['issued_at'], 'd F Y') ?></p>
    <p>Dokter Pengirim,</p>
    <div class="space"></div>
    <p><strong><?= e($document['doctor_name']) ?></strong></p>
</div>
</body>
</html>

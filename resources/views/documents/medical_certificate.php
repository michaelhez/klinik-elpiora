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
    <h2>SURAT KETERANGAN SEHAT</h2>
    <p>No: <?= e($document['document_number']) ?></p>
</div>

<div class="content">
    Yang bertanda tangan di bawah ini, dokter yang bertugas di <?= e($clinic['name'] ?? 'klinik ini') ?>, menerangkan bahwa setelah dilakukan pemeriksaan pada tanggal <?= format_date($document['issued_at']) ?>, pasien berikut dalam keadaan sehat:
</div>

<table class="info-table">
    <tr><td class="label">Nama</td><td class="sep">:</td><td><?= e($document['patient_name']) ?></td></tr>
    <tr><td class="label">No. Rekam Medis</td><td class="sep">:</td><td><?= e($document['medical_record_number']) ?></td></tr>
    <tr><td class="label">NIK</td><td class="sep">:</td><td><?= e($document['nik'] ?? '-') ?></td></tr>
    <tr><td class="label">Jenis Kelamin</td><td class="sep">:</td><td><?= $document['gender'] === 'MALE' ? 'Laki-laki' : 'Perempuan' ?></td></tr>
    <tr><td class="label">Tanggal Lahir</td><td class="sep">:</td><td><?= $document['birth_date'] ? format_date($document['birth_date']) : '-' ?></td></tr>
    <tr><td class="label">Alamat</td><td class="sep">:</td><td><?= e($document['address'] ?? '-') ?></td></tr>
</table>

<div class="content">
    Surat keterangan ini dibuat untuk keperluan: <strong><?= e($content['purpose']) ?></strong>.
    <?php if (!empty($content['notes'])): ?>
    <br><br>Catatan: <?= e($content['notes']) ?>
    <?php endif; ?>
</div>

<div class="content">
    Demikian surat keterangan ini dibuat dengan sebenarnya untuk dapat dipergunakan sebagaimana mestinya.
</div>

<div class="signature">
    <p><?= format_date($document['issued_at'], 'd F Y') ?></p>
    <p>Dokter Pemeriksa,</p>
    <div class="space"></div>
    <p><strong><?= e($document['doctor_name']) ?></strong></p>
</div>
</body>
</html>

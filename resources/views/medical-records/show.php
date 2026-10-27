<?php
\App\Core\View::extend('layouts.app');
$isLocked = in_array($record['status'], ['FINALIZED', 'AMENDED'], true);
$age = $record['birth_date'] ? (new DateTime($record['birth_date']))->diff(new DateTime())->y : null;
$statusLabels = ['DRAFT' => 'Draf', 'IN_PROGRESS' => 'Berlangsung', 'FINALIZED' => 'Selesai', 'AMENDED' => 'Diamendemen'];
$statusBadge = match ($record['status']) {
    'FINALIZED' => 'badge-success', 'AMENDED' => 'badge-warning', 'IN_PROGRESS' => 'badge-info', default => 'badge-muted',
};
$disabledAttr = $isLocked ? 'disabled' : '';
?>

<div class="breadcrumb-custom"><a href="/dashboard">Dashboard</a> / <a href="/medical-records">Rekam Medis</a> / <?= e($record['record_number']) ?></div>

<div class="surface-card p-4 mb-4">
    <div class="d-flex flex-column flex-md-row justify-content-between gap-3">
        <div>
            <h1 class="page-title mb-1"><?= e($record['patient_name']) ?></h1>
            <div class="text-muted-custom">
                <?= e($record['medical_record_number']) ?> &bull;
                <?= $record['gender'] === 'MALE' ? 'Laki-laki' : 'Perempuan' ?><?= $age !== null ? " &bull; {$age} Tahun" : '' ?> &bull;
                Gol. Darah <?= $record['blood_type'] !== 'UNKNOWN' ? e($record['blood_type']) : '-' ?>
            </div>
            <div class="text-caption mt-1">Dokter: <?= e($record['doctor_name']) ?> &bull; No. Registrasi: <?= e($record['registration_number']) ?></div>
        </div>
        <div class="text-md-end">
            <span class="badge-status <?= $statusBadge ?> mb-2 d-inline-block"><?= $statusLabels[$record['status']] ?? $record['status'] ?></span>
            <div class="text-caption"><?= e($record['record_number']) ?></div>
        </div>
    </div>
</div>

<?php if ($isLocked): ?>
<div class="alert alert-warning d-flex align-items-center gap-2 mb-4">
    <i class="bi bi-lock-fill"></i>
    <div>Rekam medis ini sudah difinalisasi. Perubahan pada Anamnesis atau Pemeriksaan Fisik hanya dapat dilakukan melalui <strong>Amendemen</strong>.</div>
</div>
<?php endif; ?>

<div class="accordion" id="examAccordion">

    <!-- Vital Signs -->
    <div class="accordion-item">
        <h2 class="accordion-header">
            <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#accVitals">
                <i class="bi bi-heart-pulse me-2"></i>Tanda Vital
            </button>
        </h2>
        <div id="accVitals" class="accordion-collapse collapse show" data-bs-parent="#examAccordion">
            <div class="accordion-body">
                <form id="vitalsForm" data-guard>
                    <div class="row g-3">
                        <div class="col-6 col-md-3">
                            <label class="form-label">Sistolik</label>
                            <input type="number" name="systolic" class="form-control" value="<?= e($vitals['systolic'] ?? '') ?>" <?= $disabledAttr ?>>
                        </div>
                        <div class="col-6 col-md-3">
                            <label class="form-label">Diastolik</label>
                            <input type="number" name="diastolic" class="form-control" value="<?= e($vitals['diastolic'] ?? '') ?>" <?= $disabledAttr ?>>
                        </div>
                        <div class="col-6 col-md-3">
                            <label class="form-label">Nadi (bpm)</label>
                            <input type="number" name="heart_rate" class="form-control" value="<?= e($vitals['heart_rate'] ?? '') ?>" <?= $disabledAttr ?>>
                        </div>
                        <div class="col-6 col-md-3">
                            <label class="form-label">Suhu (&deg;C)</label>
                            <input type="number" step="0.1" name="temperature" class="form-control" value="<?= e($vitals['temperature'] ?? '') ?>" <?= $disabledAttr ?>>
                        </div>
                        <div class="col-6 col-md-3">
                            <label class="form-label">Napas (/menit)</label>
                            <input type="number" name="respiratory_rate" class="form-control" value="<?= e($vitals['respiratory_rate'] ?? '') ?>" <?= $disabledAttr ?>>
                        </div>
                        <div class="col-6 col-md-3">
                            <label class="form-label">SpO2 (%)</label>
                            <input type="number" name="oxygen_saturation" class="form-control" value="<?= e($vitals['oxygen_saturation'] ?? '') ?>" <?= $disabledAttr ?>>
                        </div>
                        <div class="col-6 col-md-3">
                            <label class="form-label">Berat (kg)</label>
                            <input type="number" step="0.1" name="weight" class="form-control" value="<?= e($vitals['weight'] ?? '') ?>" <?= $disabledAttr ?>>
                        </div>
                        <div class="col-6 col-md-3">
                            <label class="form-label">Tinggi (cm)</label>
                            <input type="number" step="0.1" name="height" class="form-control" value="<?= e($vitals['height'] ?? '') ?>" <?= $disabledAttr ?>>
                        </div>
                    </div>
                    <?php if ($vitals && $vitals['bmi']): ?>
                    <div class="form-helper mt-2">BMI saat ini: <strong><?= e($vitals['bmi']) ?></strong></div>
                    <?php endif; ?>
                    <?php if (!$isLocked): ?>
                    <button type="submit" class="btn btn-primary btn-sm mt-3" data-loading-text="Menyimpan...">Simpan Tanda Vital</button>
                    <?php endif; ?>
                </form>
            </div>
        </div>
    </div>

    <!-- Anamnesis -->
    <div class="accordion-item">
        <h2 class="accordion-header">
            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#accAnamnesis">
                <i class="bi bi-chat-left-text me-2"></i>Anamnesis
            </button>
        </h2>
        <div id="accAnamnesis" class="accordion-collapse collapse" data-bs-parent="#examAccordion">
            <div class="accordion-body">
                <form id="anamnesisForm" data-guard>
                    <div class="mb-3">
                        <label class="form-label form-label-required">Keluhan Utama</label>
                        <input type="text" name="chief_complaint" class="form-control" value="<?= e($anamnesis['chief_complaint'] ?? '') ?>" <?= $disabledAttr ?> required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Riwayat Penyakit Sekarang</label>
                        <textarea name="present_illness_history" class="form-control" rows="3" <?= $disabledAttr ?>><?= e($anamnesis['present_illness_history'] ?? '') ?></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Riwayat Penyakit Dahulu</label>
                        <textarea name="past_medical_history" class="form-control" rows="2" <?= $disabledAttr ?>><?= e($anamnesis['past_medical_history'] ?? '') ?></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Catatan Tambahan</label>
                        <textarea name="notes" class="form-control" rows="2" <?= $disabledAttr ?>><?= e($anamnesis['notes'] ?? '') ?></textarea>
                    </div>
                    <?php if (!$isLocked): ?>
                    <button type="submit" class="btn btn-primary btn-sm" data-loading-text="Menyimpan...">Simpan Anamnesis</button>
                    <?php else: ?>
                    <button type="button" class="btn btn-outline-warning btn-sm" onclick="Exam.openAmend('anamnesis')"><i class="bi bi-pencil-square me-1"></i>Ajukan Amendemen</button>
                    <?php endif; ?>
                </form>
            </div>
        </div>
    </div>

    <!-- Physical Examination -->
    <div class="accordion-item">
        <h2 class="accordion-header">
            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#accExam">
                <i class="bi bi-clipboard2-pulse me-2"></i>Pemeriksaan Fisik
            </button>
        </h2>
        <div id="accExam" class="accordion-collapse collapse" data-bs-parent="#examAccordion">
            <div class="accordion-body">
                <form id="physicalExamForm" data-guard>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Keadaan Umum</label>
                            <input type="text" name="general_condition" class="form-control" value="<?= e($physicalExam['general_condition'] ?? '') ?>" <?= $disabledAttr ?>>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Kesadaran</label>
                            <input type="text" name="consciousness" class="form-control" value="<?= e($physicalExam['consciousness'] ?? '') ?>" placeholder="Contoh: Compos Mentis" <?= $disabledAttr ?>>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Kepala &amp; Leher</label>
                            <textarea name="head_neck" class="form-control" rows="2" <?= $disabledAttr ?>><?= e($physicalExam['head_neck'] ?? '') ?></textarea>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Dada</label>
                            <textarea name="chest" class="form-control" rows="2" <?= $disabledAttr ?>><?= e($physicalExam['chest'] ?? '') ?></textarea>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Abdomen</label>
                            <textarea name="abdomen" class="form-control" rows="2" <?= $disabledAttr ?>><?= e($physicalExam['abdomen'] ?? '') ?></textarea>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Ekstremitas</label>
                            <textarea name="extremities" class="form-control" rows="2" <?= $disabledAttr ?>><?= e($physicalExam['extremities'] ?? '') ?></textarea>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Catatan</label>
                            <textarea name="notes" class="form-control" rows="2" <?= $disabledAttr ?>><?= e($physicalExam['notes'] ?? '') ?></textarea>
                        </div>
                    </div>
                    <?php if (!$isLocked): ?>
                    <button type="submit" class="btn btn-primary btn-sm mt-3" data-loading-text="Menyimpan...">Simpan Pemeriksaan</button>
                    <?php else: ?>
                    <button type="button" class="btn btn-outline-warning btn-sm mt-3" onclick="Exam.openAmend('physical_examination')"><i class="bi bi-pencil-square me-1"></i>Ajukan Amendemen</button>
                    <?php endif; ?>
                </form>
            </div>
        </div>
    </div>

    <!-- Diagnosis -->
    <div class="accordion-item">
        <h2 class="accordion-header">
            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#accDiagnosis">
                <i class="bi bi-clipboard2-check me-2"></i>Diagnosis (ICD-10)
            </button>
        </h2>
        <div id="accDiagnosis" class="accordion-collapse collapse" data-bs-parent="#examAccordion">
            <div class="accordion-body">
                <?php if ($diagnoses === []): ?>
                    <div class="empty-state py-3"><i class="bi bi-clipboard2-x d-block"></i><h6>Belum ada diagnosis</h6></div>
                <?php else: ?>
                <div class="table-responsive mb-3">
                    <table class="table align-middle">
                        <thead><tr><th>Kode</th><th>Deskripsi</th><th>Tipe</th><th class="text-end">Aksi</th></tr></thead>
                        <tbody>
                        <?php foreach ($diagnoses as $d): ?>
                            <tr>
                                <td class="fw-semibold"><?= e($d['icd10_code']) ?></td>
                                <td><?= e($d['icd10_description']) ?></td>
                                <td><span class="badge-status <?= $d['type'] === 'PRIMARY' ? 'badge-info' : 'badge-muted' ?>"><?= $d['type'] === 'PRIMARY' ? 'Utama' : 'Sekunder' ?></span></td>
                                <td class="text-end">
                                    <?php if (!$isLocked): ?>
                                    <button class="btn btn-sm btn-outline-danger" onclick="Exam.removeDiagnosis(<?= e($d['id']) ?>)"><i class="bi bi-trash"></i></button>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php endif; ?>

                <?php if (!$isLocked): ?>
                <form id="diagnosisForm" data-guard class="row g-2 align-items-end">
                    <div class="col-12 col-md-6 position-relative">
                        <label class="form-label form-label-required">Cari Kode ICD-10</label>
                        <input type="text" id="icd10Search" class="form-control" placeholder="Ketik kode atau deskripsi" autocomplete="off" required>
                        <input type="hidden" name="icd10_code_id" id="icd10CodeId">
                        <div id="icd10Results" class="list-group position-absolute w-100 shadow-sm" style="z-index:1050;display:none;max-height:220px;overflow-y:auto;"></div>
                    </div>
                    <div class="col-6 col-md-3">
                        <label class="form-label">Tipe</label>
                        <select name="type" class="form-select">
                            <option value="PRIMARY">Utama</option>
                            <option value="SECONDARY">Sekunder</option>
                        </select>
                    </div>
                    <div class="col-6 col-md-3">
                        <button type="submit" class="btn btn-primary w-100" data-loading-text="Menambah...">Tambah</button>
                    </div>
                </form>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Medical Actions -->
    <div class="accordion-item">
        <h2 class="accordion-header">
            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#accActions">
                <i class="bi bi-bandaid me-2"></i>Tindakan Medis
            </button>
        </h2>
        <div id="accActions" class="accordion-collapse collapse" data-bs-parent="#examAccordion">
            <div class="accordion-body">
                <?php if ($actions === []): ?>
                    <div class="empty-state py-3"><i class="bi bi-bandaid d-block"></i><h6>Belum ada tindakan</h6></div>
                <?php else: ?>
                <div class="table-responsive mb-3">
                    <table class="table align-middle">
                        <thead><tr><th>Kode</th><th>Tindakan</th><th>Qty</th><th>Harga</th><th class="text-end">Aksi</th></tr></thead>
                        <tbody>
                        <?php foreach ($actions as $a): ?>
                            <tr>
                                <td><?= e($a['action_code']) ?></td>
                                <td><?= e($a['action_name']) ?></td>
                                <td><?= e($a['quantity']) ?></td>
                                <td><?= format_currency($a['price'] * $a['quantity']) ?></td>
                                <td class="text-end">
                                    <?php if (!$isLocked): ?>
                                    <button class="btn btn-sm btn-outline-danger" onclick="Exam.removeAction(<?= e($a['id']) ?>)"><i class="bi bi-trash"></i></button>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php endif; ?>

                <?php if (!$isLocked): ?>
                <form id="actionForm" data-guard class="row g-2 align-items-end">
                    <div class="col-12 col-md-6 position-relative">
                        <label class="form-label form-label-required">Cari Tindakan</label>
                        <input type="text" id="actionSearch" class="form-control" placeholder="Ketik kode atau nama tindakan" autocomplete="off" required>
                        <input type="hidden" name="medical_action_id" id="medicalActionId">
                        <div id="actionResults" class="list-group position-absolute w-100 shadow-sm" style="z-index:1050;display:none;max-height:220px;overflow-y:auto;"></div>
                    </div>
                    <div class="col-6 col-md-3">
                        <label class="form-label">Jumlah</label>
                        <input type="number" name="quantity" class="form-control" value="1" min="1">
                    </div>
                    <div class="col-6 col-md-3">
                        <button type="submit" class="btn btn-primary w-100" data-loading-text="Menambah...">Tambah</button>
                    </div>
                </form>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Laboratory -->
    <div class="accordion-item">
        <h2 class="accordion-header">
            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#accLab">
                <i class="bi bi-flask me-2"></i>Laboratorium
            </button>
        </h2>
        <div id="accLab" class="accordion-collapse collapse" data-bs-parent="#examAccordion">
            <div class="accordion-body">
                <?php if ($labRequests === []): ?>
                    <div class="empty-state py-3"><i class="bi bi-flask d-block"></i><h6>Belum ada permintaan lab</h6></div>
                <?php else: ?>
                <div class="table-responsive mb-3">
                    <table class="table align-middle">
                        <thead><tr><th>No. Permintaan</th><th>Pemeriksaan</th><th>Status</th><th class="text-end">Aksi</th></tr></thead>
                        <tbody>
                        <?php foreach ($labRequests as $lr): ?>
                            <?php
                            $lrLabels = ['REQUESTED' => 'Diminta', 'SAMPLE_COLLECTED' => 'Sampel Diambil', 'PROCESSING' => 'Diproses', 'COMPLETED' => 'Selesai', 'VALIDATED' => 'Tervalidasi', 'CANCELLED' => 'Dibatalkan'];
                            $lrBadge = match ($lr['status']) {
                                'VALIDATED' => 'badge-success', 'COMPLETED' => 'badge-info', 'CANCELLED' => 'badge-danger', default => 'badge-warning',
                            };
                            ?>
                            <tr>
                                <td><?= e($lr['request_number']) ?></td>
                                <td><?= e(implode(', ', array_column($lr['items'], 'test_name'))) ?></td>
                                <td><span class="badge-status <?= $lrBadge ?>"><?= $lrLabels[$lr['status']] ?? $lr['status'] ?></span></td>
                                <td class="text-end"><a href="/lab-requests/<?= e($lr['id']) ?>" class="btn btn-sm btn-outline-secondary"><i class="bi bi-eye"></i></a></td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php endif; ?>

                <?php if (!$isLocked && can('laboratory.request')): ?>
                <form id="labRequestForm" data-guard>
                    <label class="form-label form-label-required">Pilih Jenis Pemeriksaan</label>
                    <div class="position-relative mb-2">
                        <input type="text" id="labTestSearch" class="form-control" placeholder="Ketik nama pemeriksaan lab" autocomplete="off">
                        <div id="labTestResults" class="list-group position-absolute w-100 shadow-sm" style="z-index:1050;display:none;max-height:220px;overflow-y:auto;"></div>
                    </div>
                    <div id="selectedLabTests" class="d-flex flex-wrap gap-2 mb-2"></div>
                    <button type="submit" class="btn btn-primary btn-sm" data-loading-text="Mengirim...">Buat Permintaan Lab</button>
                </form>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Prescription -->
    <div class="accordion-item">
        <h2 class="accordion-header">
            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#accRx">
                <i class="bi bi-capsule me-2"></i>Resep
            </button>
        </h2>
        <div id="accRx" class="accordion-collapse collapse" data-bs-parent="#examAccordion">
            <div class="accordion-body">
                <?php if ($prescriptions === []): ?>
                    <div class="empty-state py-3"><i class="bi bi-capsule d-block"></i><h6>Belum ada resep</h6></div>
                <?php else: ?>
                <div class="table-responsive mb-3">
                    <table class="table align-middle">
                        <thead><tr><th>No. Resep</th><th>Status</th><th class="text-end">Aksi</th></tr></thead>
                        <tbody>
                        <?php
                        $rxLabels = ['DRAFT' => 'Draf', 'WAITING_PHARMACY' => 'Menunggu Farmasi', 'PROCESSING' => 'Diproses', 'READY' => 'Siap', 'DISPENSED' => 'Diserahkan', 'CANCELLED' => 'Dibatalkan'];
                        $rxBadge = ['DISPENSED' => 'badge-success', 'READY' => 'badge-info', 'CANCELLED' => 'badge-danger'];
                        ?>
                        <?php foreach ($prescriptions as $rx): ?>
                            <tr>
                                <td><?= e($rx['prescription_number']) ?></td>
                                <td><span class="badge-status <?= $rxBadge[$rx['status']] ?? 'badge-warning' ?>"><?= $rxLabels[$rx['status']] ?? $rx['status'] ?></span></td>
                                <td class="text-end"><a href="/prescriptions/<?= e($rx['id']) ?>" class="btn btn-sm btn-outline-secondary"><i class="bi bi-eye"></i></a></td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php endif; ?>

                <?php if (!$isLocked && can('prescription.create')): ?>
                <hr>
                <h3 class="card-title-sm mb-3">Buat Resep Baru</h3>

                <div class="mb-4">
                    <label class="form-label">Obat (Non-Racikan)</label>
                    <div class="position-relative mb-2">
                        <input type="text" id="rxMedicineSearch" class="form-control" placeholder="Cari nama obat" autocomplete="off">
                        <div id="rxMedicineResults" class="list-group position-absolute w-100 shadow-sm" style="z-index:1050;display:none;max-height:220px;overflow-y:auto;"></div>
                    </div>
                    <div class="row g-2 mb-2">
                        <div class="col-4"><input type="text" id="rxDosage" class="form-control form-control-sm" placeholder="Dosis (3x1)"></div>
                        <div class="col-3"><input type="number" id="rxQuantity" class="form-control form-control-sm" placeholder="Jumlah" min="1" value="1"></div>
                        <div class="col-5"><input type="text" id="rxInstructions" class="form-control form-control-sm" placeholder="Aturan pakai"></div>
                    </div>
                    <button type="button" class="btn btn-outline-primary btn-sm" id="rxAddItemBtn">Tambah Obat</button>
                    <div id="rxItemList" class="mt-2"></div>
                </div>

                <div class="mb-4">
                    <label class="form-label">Obat Racikan</label>
                    <div class="row g-2 mb-2">
                        <div class="col-6"><input type="text" id="rxCompoundName" class="form-control form-control-sm" placeholder="Nama racikan (Puyer Batuk)"></div>
                        <div class="col-3"><input type="text" id="rxCompoundDosage" class="form-control form-control-sm" placeholder="Aturan pakai"></div>
                        <div class="col-3"><input type="number" id="rxCompoundQuantity" class="form-control form-control-sm" placeholder="Jml bungkus" min="1" value="1"></div>
                    </div>
                    <div class="position-relative mb-2">
                        <input type="text" id="rxCompoundMedicineSearch" class="form-control form-control-sm" placeholder="Cari obat komposisi racikan" autocomplete="off">
                        <div id="rxCompoundMedicineResults" class="list-group position-absolute w-100 shadow-sm" style="z-index:1050;display:none;max-height:220px;overflow-y:auto;"></div>
                    </div>
                    <div class="row g-2 mb-2">
                        <div class="col-6"><span class="form-control form-control-sm bg-light" id="rxCompoundSelectedMedicine">Belum memilih obat</span></div>
                        <div class="col-3"><input type="number" step="0.01" id="rxCompoundQtyPerDose" class="form-control form-control-sm" placeholder="Qty/bungkus" min="0.01"></div>
                        <div class="col-3"><button type="button" class="btn btn-outline-secondary btn-sm w-100" id="rxAddCompoundItemBtn">+ Komposisi</button></div>
                    </div>
                    <div id="rxCompoundItemList" class="mb-2"></div>
                    <button type="button" class="btn btn-outline-primary btn-sm" id="rxAddCompoundBtn">Tambah Racikan ke Resep</button>
                    <div id="rxCompoundList" class="mt-2"></div>
                </div>

                <button type="button" class="btn btn-primary" id="rxSubmitBtn">Kirim Resep ke Farmasi</button>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Notes / Timeline -->
    <div class="accordion-item">
        <h2 class="accordion-header">
            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#accNotes">
                <i class="bi bi-journal-text me-2"></i>Catatan &amp; Riwayat
            </button>
        </h2>
        <div id="accNotes" class="accordion-collapse collapse" data-bs-parent="#examAccordion">
            <div class="accordion-body">
                <?php if (!$isLocked): ?>
                <form id="noteForm" data-guard class="mb-3">
                    <div class="mb-2">
                        <textarea name="note" class="form-control" rows="2" placeholder="Tambahkan catatan klinis..." required></textarea>
                    </div>
                    <button type="submit" class="btn btn-outline-primary btn-sm" data-loading-text="Menyimpan...">Tambah Catatan</button>
                </form>
                <?php endif; ?>

                <div class="timeline">
                    <?php foreach ($notes as $n): ?>
                        <div class="timeline-item">
                            <span class="timeline-dot"></span>
                            <div class="text-caption"><?= format_date($n['created_at'], 'd M Y H:i') ?> &bull; <?= e($n['author_name'] ?? 'Sistem') ?></div>
                            <div class="card-title-sm"><?= nl2br(e($n['note'])) ?></div>
                        </div>
                    <?php endforeach; ?>
                    <?php foreach ($histories as $h): ?>
                        <div class="timeline-item">
                            <span class="timeline-dot" style="background:var(--color-warning);box-shadow:0 0 0 2px var(--color-warning);"></span>
                            <div class="text-caption"><?= format_date($h['created_at'], 'd M Y H:i') ?> &bull; <?= e($h['changed_by_name'] ?? 'Sistem') ?></div>
                            <div class="card-title-sm">
                                <?= $h['action_type'] === 'FINALIZE' ? 'Rekam medis difinalisasi' : 'Amendemen: ' . e($h['section']) ?>
                            </div>
                            <?php if ($h['reason']): ?><div class="text-muted-custom small">Alasan: <?= e($h['reason']) ?></div><?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                    <?php if ($notes === [] && $histories === []): ?>
                        <div class="empty-state py-3"><i class="bi bi-journal d-block"></i><h6>Belum ada catatan</h6></div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php if (!$isLocked && can('medical_record.finalize')): ?>
<div class="surface-card p-4 mt-4 d-flex justify-content-between align-items-center flex-wrap gap-2">
    <div>
        <h2 class="section-title mb-1">Finalisasi Rekam Medis</h2>
        <p class="text-muted-custom mb-0 small">Setelah difinalisasi, data anamnesis dan pemeriksaan fisik hanya dapat diubah melalui amendemen.</p>
    </div>
    <button type="button" class="btn btn-success" onclick="Exam.finalize()"><i class="bi bi-check-circle me-1"></i>Finalisasi</button>
</div>
<?php endif; ?>

<?php if ($isLocked): ?>
<div class="surface-card p-4 mt-4">
    <h2 class="section-title mb-3">Tagihan & Dokumen</h2>

    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3 pb-3 border-bottom">
        <div>
            <div class="fw-semibold">Tagihan Kunjungan</div>
            <?php if ($invoice): ?>
                <div class="text-caption">No. Tagihan: <?= e($invoice['invoice_number']) ?> &bull; <?= format_currency($invoice['total']) ?></div>
            <?php else: ?>
                <div class="text-caption">Tagihan belum dibuat untuk kunjungan ini.</div>
            <?php endif; ?>
        </div>
        <?php if ($invoice): ?>
            <a href="/billing/<?= e($invoice['id']) ?>" class="btn btn-outline-primary btn-sm"><i class="bi bi-receipt me-1"></i>Lihat Tagihan</a>
        <?php elseif (can('billing.create')): ?>
            <form action="/billing/generate" method="POST" onsubmit="return confirm('Buat tagihan untuk kunjungan ini?');">
                <?= csrf_field() ?>
                <input type="hidden" name="medical_record_id" value="<?= (int) $record['id'] ?>">
                <button type="submit" class="btn btn-primary btn-sm"><i class="bi bi-receipt me-1"></i>Buat Tagihan</button>
            </form>
        <?php endif; ?>
    </div>

    <?php if (can('medical_document.create')): ?>
    <div class="d-flex flex-wrap gap-2 mb-3">
        <button type="button" class="btn btn-outline-secondary btn-sm" onclick="Doc.openModal('SICK_NOTE')"><i class="bi bi-file-earmark-medical me-1"></i>Surat Keterangan Sakit</button>
        <button type="button" class="btn btn-outline-secondary btn-sm" onclick="Doc.openModal('REFERRAL_LETTER')"><i class="bi bi-file-earmark-arrow-up me-1"></i>Surat Rujukan</button>
        <button type="button" class="btn btn-outline-secondary btn-sm" onclick="Doc.openModal('MEDICAL_CERTIFICATE')"><i class="bi bi-file-earmark-check me-1"></i>Surat Keterangan Sehat</button>
    </div>
    <?php endif; ?>

    <?php if ($documents !== []): ?>
    <div class="table-responsive">
        <table class="table table-sm align-middle mb-0">
            <thead><tr><th>No. Dokumen</th><th>Jenis</th><th>Tanggal Terbit</th><th class="text-end">Aksi</th></tr></thead>
            <tbody>
            <?php $docLabels = ['SICK_NOTE' => 'Surat Keterangan Sakit', 'REFERRAL_LETTER' => 'Surat Rujukan', 'MEDICAL_CERTIFICATE' => 'Surat Keterangan Sehat']; ?>
            <?php foreach ($documents as $doc): ?>
                <tr>
                    <td><?= e($doc['document_number']) ?></td>
                    <td><?= $docLabels[$doc['type']] ?? $doc['type'] ?></td>
                    <td><?= format_date($doc['issued_at'], 'd M Y H:i') ?></td>
                    <td class="text-end"><a href="/medical-documents/<?= e($doc['id']) ?>/pdf" target="_blank" class="btn btn-sm btn-outline-secondary"><i class="bi bi-file-pdf"></i></a></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php else: ?>
        <p class="text-muted-custom small mb-0">Belum ada dokumen yang diterbitkan.</p>
    <?php endif; ?>
</div>
<?php endif; ?>

<div class="modal fade" id="docModal" tabindex="-1">
    <div class="modal-dialog"><div class="modal-content">
        <form id="docForm" data-guard>
            <input type="hidden" name="type" id="docType">
            <div class="modal-header"><h5 class="modal-title" id="docModalTitle">Terbitkan Dokumen</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body" id="docModalBody"></div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-primary" data-loading-text="Menerbitkan...">Terbitkan</button>
            </div>
        </form>
    </div></div>
</div>

<div class="modal fade" id="amendModal" tabindex="-1">
    <div class="modal-dialog modal-lg"><div class="modal-content">
        <form id="amendForm" data-guard>
            <input type="hidden" name="section" id="amendSection">
            <div class="modal-header"><h5 class="modal-title" id="amendModalTitle">Ajukan Amendemen</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body" id="amendModalBody"></div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-warning" data-loading-text="Menyimpan...">Ajukan Amendemen</button>
            </div>
        </form>
    </div></div>
</div>

<script>
window.MEDICAL_RECORD_ID = <?= (int) $record['id'] ?>;
window.CURRENT_ANAMNESIS = <?= json_encode($anamnesis) ?>;
window.CURRENT_PHYSICAL_EXAM = <?= json_encode($physicalExam) ?>;
</script>
<script src="<?= asset('js/medical-records.js') ?>"></script>
<script src="<?= asset('js/medical-documents.js') ?>"></script>
<script src="<?= asset('js/prescription-builder.js') ?>"></script>

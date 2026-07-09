<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h5 class="card-title"><i class="bi bi-speedometer2 me-2"></i>Dashboard IKU-1</h5>
                    <p class="card-text text-muted">Pantau capaian Indikator Kinerja Utama program studi Anda</p>
                    
                    <?php if (empty($kohorts)): ?>
                        <div class="alert alert-warning">
                            <i class="bi bi-exclamation-triangle me-2"></i>
                            Belum ada kohort aktif untuk program studi ini.
                        </div>
                    <?php else: ?>
                        <!-- Kohort Selector -->
                        <div class="mb-3">
                            <form method="get" class="d-flex align-items-center gap-2">
                                <label for="kohort_id" class="form-label mb-0">Pilih Kohort:</label>
                                <select name="kohort_id" id="kohort_id" class="form-select form-select-sm w-auto" onchange="this.form.submit()">
                                    <?php foreach ($kohorts as $k): ?>
                                        <option value="<?= $k['id'] ?>" <?= $selected_kohort_id == $k['id'] ? 'selected' : '' ?>>
                                            <?= $k['nama_kohort'] ?> (Lulus <?= $k['tahun_lulus'] ?>)
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </form>
                        </div>
                        
                        <?php if ($current_kohort): ?>
                            <!-- IKU-1 Score Card -->
                            <div class="row mb-4">
                                <div class="col-md-4">
                                    <div class="card bg-primary text-white shadow-sm">
                                        <div class="card-body text-center">
                                            <h6 class="card-title">Skor IKU-1</h6>
                                            <h2 class="display-4 fw-bold"><?= number_format($iku_1_score, 1) ?>%</h2>
                                            <small>Kohort: <?= $current_kohort['nama_kohort'] ?></small>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="card bg-success text-white shadow-sm">
                                        <div class="card-body text-center">
                                            <h6 class="card-title">Total Responden</h6>
                                            <h2 class="display-4 fw-bold"><?= $current_kohort['jumlah_mahasiswa'] ?></h2>
                                            <small>Mahasiswa di kohort ini</small>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="card bg-info text-white shadow-sm">
                                        <div class="card-body text-center">
                                            <h6 class="card-title">Tahun Lulus</h6>
                                            <h2 class="display-4 fw-bold"><?= $current_kohort['tahun_lulus'] ?></h2>
                                            <small>Tahun kelulusan</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- IKU Details Table -->
                            <div class="card shadow-sm">
                                <div class="card-header bg-white">
                                    <h6 class="mb-0"><i class="bi bi-table me-2"></i>Rincian Indikator IKU</h6>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-hover align-middle">
                                            <thead class="table-light">
                                                <tr>
                                                    <th>No</th>
                                                    <th>Indikator</th>
                                                    <th>Numerator</th>
                                                    <th>Denominator</th>
                                                    <th>Persentase</th>
                                                    <th>Status</th>
                                                    <th>Aksi</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php 
                                                $iku_labels = [
                                                    1 => 'Lulusan Mendapat Pekerjaan (< 6 bulan)',
                                                    2 => 'Lulusan Mendapat Pekerjaan (> 6 bulan)',
                                                    3 => 'Lulusan Melanjutkan Studi',
                                                    4 => 'Lulusan Wirausaha',
                                                    5 => 'Lulusan Bekerja Sesuai Bidang',
                                                    6 => 'Kepuasan Pengguna Lulusan',
                                                    7 => 'Pengakuan Internasional',
                                                    8 => 'Produktivitas Publikasi'
                                                ];
                                                ?>
                                                <?php foreach ($iku_data as $row): ?>
                                                <tr>
                                                    <td><?= $row['iku_number'] ?></td>
                                                    <td><?= $iku_labels[$row['iku_number']] ?? 'IKU-' . $row['iku_number'] ?></td>
                                                    <td class="text-center"><?= $row['numerator'] ?></td>
                                                    <td class="text-center"><?= $row['denominator'] ?></td>
                                                    <td class="text-center">
                                                        <span class="badge <?= $row['percentage'] >= 70 ? 'bg-success' : ($row['percentage'] >= 50 ? 'bg-warning' : 'bg-danger') ?>">
                                                            <?= number_format($row['percentage'], 2) ?>%
                                                        </span>
                                                    </td>
                                                    <td class="text-center">
                                                        <span class="badge <?= $row['status_capaian'] == 'Melampaui' ? 'bg-success' : ($row['status_capaian'] == 'Tercapai' ? 'bg-info' : 'bg-secondary') ?>">
                                                            <?= $row['status_capaian'] ?>
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <a href="<?= site_url('prodi/iku/detail/' . $row['iku_number'] . '?kohort_id=' . $row['kohort_id']) ?>" 
                                                           class="btn btn-sm btn-outline-primary">
                                                            <i class="bi bi-eye"></i> Detail
                                                        </a>
                                                    </td>
                                                </tr>
                                                <?php endforeach; ?>
                                                <?php if (empty($iku_data)): ?>
                                                <tr>
                                                    <td colspan="7" class="text-center py-4">
                                                        <i class="bi bi-inbox display-4 text-muted"></i>
                                                        <p class="text-muted mt-2">Belum ada data IKU untuk kohort ini</p>
                                                    </td>
                                                </tr>
                                                <?php endif; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Add tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl)
    })
});
</script>

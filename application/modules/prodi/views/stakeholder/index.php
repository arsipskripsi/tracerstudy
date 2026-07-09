<div class="container-fluid">
    <?php if ($this->session->flashdata('success')): ?>
        <div class="alert alert-success alert-dismissible fade show">
            <i class="bi bi-check-circle me-2"></i><?= $this->session->flashdata('success') ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>
    
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <h4><i class="bi bi-people me-2"></i>Survey Pengguna Lulusan</h4>
                <div>
                    <a href="<?= site_url('prodi/stakeholder/add') ?>" class="btn btn-outline-primary">
                        <i class="bi bi-person-plus me-2"></i>Tambah Stakeholder
                    </a>
                    <a href="<?= site_url('prodi/stakeholder/survey_create') ?>" class="btn btn-primary ms-2">
                        <i class="bi bi-file-earmark-plus me-2"></i>Buat Survey
                    </a>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Stakeholders List -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-white">
                    <h6 class="mb-0"><i class="bi bi-building me-2"></i>Daftar Stakeholder</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>No</th>
                                    <th>Nama Instansi</th>
                                    <th>Jenis</th>
                                    <th>PIC</th>
                                    <th>Total Survey</th>
                                    <th>Rating Rata-rata</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $no = 1; foreach ($stakeholders as $s): ?>
                                <tr>
                                    <td><?= $no++ ?></td>
                                    <td><strong><?= $s['nama_instansi'] ?></strong></td>
                                    <td><span class="badge bg-info"><?= $s['jenis_instansi'] ?></span></td>
                                    <td><?= $s['namakontak_pic'] ?: '-' ?></td>
                                    <td><?= $s['total_survey'] ?></td>
                                    <td>
                                        <?php if ($s['avg_rating']): ?>
                                            <span class="text-warning">
                                                <?php for($i=1; $i<=5; $i++): ?>
                                                    <i class="bi bi-star<?= $i <= round($s['avg_rating']) ? '-fill' : '' ?>"></i>
                                                <?php endfor; ?>
                                                (<?= number_format($s['avg_rating'], 1) ?>)
                                            </span>
                                        <?php else: ?>
                                            -
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                                <?php if (empty($stakeholders)): ?>
                                <tr>
                                    <td colspan="6" class="text-center py-4">
                                        <i class="bi bi-inbox display-4 text-muted"></i>
                                        <p class="text-muted mt-2">Belum ada stakeholder</p>
                                    </td>
                                </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Surveys List -->
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-white">
                    <h6 class="mb-0"><i class="bi bi-file-earmark-text me-2"></i>Survey Stakeholder</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>No</th>
                                    <th>Judul Survey</th>
                                    <th>Periode</th>
                                    <th>Status</th>
                                    <th>Respons</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $no = 1; foreach ($surveys as $s): ?>
                                <tr>
                                    <td><?= $no++ ?></td>
                                    <td><strong><?= $s['title'] ?></strong></td>
                                    <td><?= date('d M Y', strtotime($s['start_date'])) ?> - <?= date('d M Y', strtotime($s['end_date'])) ?></td>
                                    <td>
                                        <?php if ($s['is_active']): ?>
                                            <span class="badge bg-success">Aktif</span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary">Tidak Aktif</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><span class="badge bg-primary"><?= $s['response_count'] ?> respons</span></td>
                                </tr>
                                <?php endforeach; ?>
                                <?php if (empty($surveys)): ?>
                                <tr>
                                    <td colspan="5" class="text-center py-4">
                                        <i class="bi bi-inbox display-4 text-muted"></i>
                                        <p class="text-muted mt-2">Belum ada survey stakeholder</p>
                                    </td>
                                </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

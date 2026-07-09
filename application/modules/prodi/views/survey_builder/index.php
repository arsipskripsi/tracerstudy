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
                <h4><i class="bi bi-journal-text me-2"></i>Survey Builder</h4>
                <a href="<?= site_url('prodi/survey_builder/create') ?>" class="btn btn-primary">
                    <i class="bi bi-plus-lg me-2"></i>Buat Survey Baru
                </a>
            </div>
        </div>
    </div>
    
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>No</th>
                                    <th>Judul Survey</th>
                                    <th>Tipe</th>
                                    <th>Pertanyaan</th>
                                    <th>Respons</th>
                                    <th>Periode</th>
                                    <th>Status</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $no = 1; foreach ($surveys as $s): ?>
                                <tr>
                                    <td><?= $no++ ?></td>
                                    <td><strong><?= $s['title'] ?></strong></td>
                                    <td>
                                        <span class="badge <?= $s['type'] == 'tracer_study' ? 'bg-primary' : 'bg-info' ?>">
                                            <?= $s['type'] ?>
                                        </span>
                                    </td>
                                    <td><span class="badge bg-secondary"><?= $s['question_count'] ?></span></td>
                                    <td><span class="badge bg-success"><?= $s['response_count'] ?></span></td>
                                    <td><?= date('d M Y', strtotime($s['start_date'])) ?><br><small>s/d <?= date('d M Y', strtotime($s['end_date'])) ?></small></td>
                                    <td>
                                        <?php if ($s['is_active']): ?>
                                            <span class="badge bg-success">Aktif</span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary">Nonaktif</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <a href="<?= site_url('prodi/survey_builder/questions/' . $s['id']) ?>" class="btn btn-outline-primary" title="Kelola Pertanyaan">
                                                <i class="bi bi-list-task"></i>
                                            </a>
                                            <a href="<?= site_url('prodi/survey_builder/edit/' . $s['id']) ?>" class="btn btn-outline-warning" title="Edit">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                            <a href="<?= site_url('prodi/survey_builder/toggle_status/' . $s['id']) ?>" class="btn btn-outline-info" title="Toggle Status">
                                                <i class="bi bi-toggle-on"></i>
                                            </a>
                                            <a href="<?= site_url('prodi/survey_builder/delete/' . $s['id']) ?>" 
                                               class="btn btn-outline-danger" 
                                               onclick="return confirm('Yakin ingin menghapus survey ini?')"
                                               title="Hapus">
                                                <i class="bi bi-trash"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                                <?php if (empty($surveys)): ?>
                                <tr>
                                    <td colspan="8" class="text-center py-4">
                                        <i class="bi bi-inbox display-4 text-muted"></i>
                                        <p class="text-muted mt-2">Belum ada survey. Silakan buat survey baru.</p>
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

<div class="container-fluid py-4">
    <div class="card shadow-sm border-0">
        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center border-bottom">
            <div>
                <h5 class="mb-0 text-primary"><i class="bi bi-card-checklist me-2"></i>Manajemen Survei</h5>
                <small class="text-muted">Kelola survei dan kuesioner</small>
            </div>
            <div>
                <a href="<?= base_url('admin/surveys/create') ?>" class="btn btn-primary btn-sm">
                    <i class="bi bi-plus-circle me-1"></i> Buat Survei Baru
                </a>
            </div>
        </div>
        <div class="card-body">
            <?php if ($this->session->flashdata('success')): ?>
                <div class="alert alert-success alert-dismissible fade show">
                    <?= $this->session->flashdata('success') ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>
            <?php if ($this->session->flashdata('error')): ?>
                <div class="alert alert-danger alert-dismissible fade show">
                    <?= $this->session->flashdata('error') ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>
            
            <?php if (!empty($surveys)): ?>
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead class="table-dark">
                        <tr>
                            <th>No</th>
                            <th>Judul Survei</th>
                            <th>Dibuat Oleh</th>
                            <th>Status</th>
                            <th>Tanggal Dibuat</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $no = 1;
                        foreach ($surveys as $survey): ?>
                        <tr>
                            <td><?= $no++ ?></td>
                            <td><?= htmlspecialchars($survey['title'] ?? '-') ?></td>
                            <td><?= htmlspecialchars($survey['creator'] ?? 'System') ?></td>
                            <td>
                                <?php if ($survey['status'] === 'published'): ?>
                                <span class="badge bg-success">Aktif</span>
                                <?php elseif ($survey['status'] === 'draft'): ?>
                                <span class="badge bg-warning text-dark">Draft</span>
                                <?php else: ?>
                                <span class="badge bg-secondary"><?= ucfirst($survey['status']) ?></span>
                                <?php endif; ?>
                            </td>
                            <td><?= date('d M Y', strtotime($survey['created_at'])) ?></td>
                            <td>
                                <div class="btn-group btn-group-sm" role="group">
                                    <a href="<?= base_url('admin/surveys/edit/' . $survey['id']) ?>" class="btn btn-outline-primary">
                                        <i class="bi bi-pencil"></i> Edit
                                    </a>
                                    <?php if ($survey['status'] !== 'published'): ?>
                                    <button onclick="deleteSurvey(<?= $survey['id'] ?>, '<?= htmlspecialchars($survey['title']) ?>')" class="btn btn-outline-danger">
                                        <i class="bi bi-trash"></i> Hapus
                                    </button>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php else: ?>
            <div class="text-center py-5">
                <i class="bi bi-inbox text-muted fs-1"></i>
                <p class="text-muted mt-2">Belum ada survei</p>
                <a href="<?= base_url('admin/surveys/create') ?>" class="btn btn-primary mt-2">
                    <i class="bi bi-plus-circle me-1"></i> Buat Survei Pertama
                </a>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
function deleteSurvey(id, title) {
    if (!confirm('Apakah Anda yakin ingin menghapus survei "' + title + '"?\n\nPeringatan: Tindakan ini tidak dapat dibatalkan!')) return;

    window.location = '<?= base_url('admin/surveys/delete/') ?>' + id;
}
</script>

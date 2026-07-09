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
                <h4><i class="bi bi-book me-2"></i>Manajemen Kurikulum</h4>
                <a href="<?= site_url('prodi/kurikulum/create') ?>" class="btn btn-primary">
                    <i class="bi bi-plus-lg me-2"></i>Tambah Kurikulum
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
                                    <th>Nama Kurikulum</th>
                                    <th>Tahun Mulai</th>
                                    <th>Total SKS</th>
                                    <th>Jumlah CPL</th>
                                    <th>Status</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $no = 1; foreach ($kurikulum_list as $k): ?>
                                <tr>
                                    <td><?= $no++ ?></td>
                                    <td><strong><?= $k['nama_kurikulum'] ?></strong></td>
                                    <td><?= $k['tahun_mulai'] ?></td>
                                    <td><?= $k['total_sks'] ?> SKS</td>
                                    <td><span class="badge bg-info"><?= $k['jumlah_cpl'] ?> CPL</span></td>
                                    <td>
                                        <?php if ($k['is_active']): ?>
                                            <span class="badge bg-success">Aktif</span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary">Tidak Aktif</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <a href="<?= site_url('prodi/kurikulum/cpl/' . $k['id']) ?>" class="btn btn-outline-primary" title="Kelola CPL">
                                                <i class="bi bi-list-task"></i>
                                            </a>
                                            <a href="<?= site_url('prodi/kurikulum/edit/' . $k['id']) ?>" class="btn btn-outline-warning" title="Edit">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                            <a href="<?= site_url('prodi/kurikulum/delete/' . $k['id']) ?>" 
                                               class="btn btn-outline-danger" 
                                               onclick="return confirm('Yakin ingin menghapus kurikulum ini?')"
                                               title="Hapus">
                                                <i class="bi bi-trash"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                                <?php if (empty($kurikulum_list)): ?>
                                <tr>
                                    <td colspan="7" class="text-center py-4">
                                        <i class="bi bi-inbox display-4 text-muted"></i>
                                        <p class="text-muted mt-2">Belum ada kurikulum. Silakan tambah kurikulum baru.</p>
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

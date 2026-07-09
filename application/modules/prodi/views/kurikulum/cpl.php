<div class="container-fluid">
    <?php if ($this->session->flashdata('success')): ?>
        <div class="alert alert-success alert-dismissible fade show">
            <i class="bi bi-check-circle me-2"></i><?= $this->session->flashdata('success') ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>
    
    <div class="row mb-4">
        <div class="col-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="<?= site_url('prodi/kurikulum') ?>">Kurikulum</a></li>
                    <li class="breadcrumb-item active">CPL - <?= $kurikulum['nama_kurikulum'] ?></li>
                </ol>
            </nav>
            
            <div class="d-flex justify-content-between align-items-center">
                <h4><i class="bi bi-list-task me-2"></i>CPL - <?= $kurikulum['nama_kurikulum'] ?></h4>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addCplModal">
                    <i class="bi bi-plus-lg me-2"></i>Tambah CPL
                </button>
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
                                    <th>Kode</th>
                                    <th>Jenis</th>
                                    <th>Deskripsi</th>
                                    <th>Level</th>
                                    <th>Target Industri</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $no = 1; foreach ($cpl_list as $c): ?>
                                <tr>
                                    <td><?= $no++ ?></td>
                                    <td><strong><?= $c['kode_cpl'] ?></strong></td>
                                    <td>
                                        <span class="badge 
                                            <?= $c['jenis'] == 'Sikap' ? 'bg-primary' : 
                                               ($c['jenis'] == 'Pengetahuan' ? 'bg-info' : 
                                               ($c['jenis'] == 'Keterampilan_Umum' ? 'bg-warning' : 'bg-success')) ?>">
                                            <?= str_replace('_', ' ', $c['jenis']) ?>
                                        </span>
                                    </td>
                                    <td><?= substr($c['deskripsi'], 0, 80) ?>...</td>
                                    <td><?= $c['level'] ?: '-' ?></td>
                                    <td><?= $c['target_industri'] ?></td>
                                    <td>
                                        <a href="<?= site_url('prodi/kurikulum/delete_cpl/' . $c['id']) ?>" 
                                           class="btn btn-sm btn-outline-danger"
                                           onclick="return confirm('Yakin ingin menghapus CPL ini?')">
                                            <i class="bi bi-trash"></i>
                                        </a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                                <?php if (empty($cpl_list)): ?>
                                <tr>
                                    <td colspan="7" class="text-center py-4">
                                        <i class="bi bi-inbox display-4 text-muted"></i>
                                        <p class="text-muted mt-2">Belum ada CPL. Silakan tambah CPL baru.</p>
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

<!-- Add CPL Modal -->
<div class="modal fade" id="addCplModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form action="<?= site_url('prodi/kurikulum/add_cpl/' . $kurikulum['id']) ?>" method="post">
                <div class="modal-header">
                    <h5 class="modal-title">Tambah CPL Baru</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Kode CPL</label>
                        <input type="text" name="kode_cpl" class="form-control" required placeholder="Contoh: CPL-1">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Jenis CPL</label>
                        <select name="jenis" class="form-select" required>
                            <option value="">Pilih Jenis</option>
                            <option value="Sikap">Sikap</option>
                            <option value="Pengetahuan">Pengetahuan</option>
                            <option value="Keterampilan_Umum">Keterampilan Umum</option>
                            <option value="Keterampilan_Khusus">Keterampilan Khusus</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Deskripsi</label>
                        <textarea name="deskripsi" class="form-control" rows="4" required></textarea>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Level KKNI (Opsional)</label>
                            <select name="level" class="form-select">
                                <option value="">-</option>
                                <option value="1">Level 1</option>
                                <option value="2">Level 2</option>
                                <option value="3">Level 3</option>
                                <option value="4">Level 4</option>
                                <option value="5">Level 5</option>
                                <option value="6">Level 6</option>
                                <option value="7">Level 7</option>
                                <option value="8">Level 8</option>
                                <option value="9">Level 9</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Target Industri</label>
                            <input type="number" name="target_industri" class="form-control" step="0.01" value="4.00">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

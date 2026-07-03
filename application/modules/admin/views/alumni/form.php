<div class="container-fluid py-4">
    <div class="card shadow-sm border-0">
        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center border-bottom">
            <div>
                <h5 class="mb-0 text-primary"><i class="bi bi-person-plus me-2"></i><?= isset($page_title) ? $page_title : 'Form Alumni' ?></h5>
                <small class="text-muted"><?= isset($page_subtitle) ? $page_subtitle : '' ?></small>
            </div>
            <div>
                <a href="<?= base_url('admin/alumni') ?>" class="btn btn-outline-secondary btn-sm">
                    <i class="bi bi-arrow-left me-1"></i> Kembali
                </a>
            </div>
        </div>
        <div class="card-body">
            <?php if (validation_errors()): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="bi bi-exclamation-triangle me-2"></i>
                <?= validation_errors() ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            <?php endif; ?>
            
            <?php if ($this->session->flashdata('message')): ?>
            <div class="alert alert-<?= $this->session->flashdata('message_type') ?? 'success' ?> alert-dismissible fade show" role="alert">
                <i class="bi bi-check-circle me-2"></i>
                <?= $this->session->flashdata('message') ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            <?php endif; ?>
            
            <?= form_open() ?>
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label fw-bold">NIM <span class="text-danger">*</span></label>
                        <input type="text" name="nim" class="form-control" value="<?= isset($alumni['nim']) ? htmlspecialchars($alumni['nim']) : set_value('nim') ?>" required>
                        <small class="text-muted">Nomor Induk Mahasiswa</small>
                    </div>
                    
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Nama Lengkap <span class="text-danger">*</span></label>
                        <input type="text" name="nama_lengkap" class="form-control" value="<?= isset($alumni['nama_lengkap']) ? htmlspecialchars($alumni['nama_lengkap']) : set_value('nama_lengkap') ?>" required>
                        <small class="text-muted">Nama lengkap alumni</small>
                    </div>
                    
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Program Studi <span class="text-danger">*</span></label>
                        <select name="prodi_id" class="form-select" required>
                            <option value="">Pilih Program Studi</option>
                            <?php foreach ($program_studi as $ps): ?>
                            <option value="<?= $ps['id'] ?>" 
                                <?= (isset($alumni['prodi_id']) && $alumni['prodi_id'] == $ps['id']) || set_value('prodi_id') == $ps['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($ps['nama']) ?> (<?= htmlspecialchars($ps['kode']) ?>)
                            </option>
                            <?php endforeach; ?>
                        </select>
                        <small class="text-muted">Program studi alumni</small>
                    </div>
                    
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Tanggal Lulus <span class="text-danger">*</span></label>
                        <input type="date" name="tanggal_lulus" class="form-control" value="<?= isset($alumni['tanggal_lulus']) ? $alumni['tanggal_lulus'] : set_value('tanggal_lulus') ?>" required>
                        <small class="text-muted">Tanggal kelulusan</small>
                    </div>
                    
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Tempat Lahir</label>
                        <input type="text" name="tempat_lahir" class="form-control" value="<?= isset($alumni['tempat_lahir']) ? htmlspecialchars($alumni['tempat_lahir']) : set_value('tempat_lahir') ?>">
                        <small class="text-muted">Kota/kabupaten tempat lahir</small>
                    </div>
                    
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Tanggal Lahir</label>
                        <input type="date" name="tanggal_lahir" class="form-control" value="<?= isset($alumni['tanggal_lahir']) ? $alumni['tanggal_lahir'] : set_value('tanggal_lahir') ?>">
                        <small class="text-muted">Tanggal kelahiran</small>
                    </div>
                    
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Jenis Kelamin</label>
                        <select name="jenis_kelamin" class="form-select">
                            <option value="">Pilih Jenis Kelamin</option>
                            <option value="L" <?= (isset($alumni['jenis_kelamin']) && $alumni['jenis_kelamin'] == 'L') || set_value('jenis_kelamin') == 'L' ? 'selected' : '' ?>>Laki-laki</option>
                            <option value="P" <?= (isset($alumni['jenis_kelamin']) && $alumni['jenis_kelamin'] == 'P') || set_value('jenis_kelamin') == 'P' ? 'selected' : '' ?>>Perempuan</option>
                        </select>
                        <small class="text-muted">Jenis kelamin</small>
                    </div>
                    
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Email Pribadi</label>
                        <input type="email" name="email_pribadi" class="form-control" value="<?= isset($alumni['email_pribadi']) ? htmlspecialchars($alumni['email_pribadi']) : set_value('email_pribadi') ?>">
                        <small class="text-muted">Email pribadi alumni</small>
                    </div>
                    
                    <div class="col-md-6">
                        <label class="form-label fw-bold">No. HP</label>
                        <input type="text" name="no_hp" class="form-control" value="<?= isset($alumni['no_hp']) ? htmlspecialchars($alumni['no_hp']) : set_value('no_hp') ?>">
                        <small class="text-muted">Nomor handphone/WhatsApp</small>
                    </div>
                    
                    <div class="col-md-12">
                        <label class="form-label fw-bold">Alamat Domisili</label>
                        <textarea name="alamat_domisili" class="form-control" rows="2"><?= isset($alumni['alamat_domisili']) ? htmlspecialchars($alumni['alamat_domisili']) : set_value('alamat_domisili') ?></textarea>
                        <small class="text-muted">Alamat tempat tinggal saat ini</small>
                    </div>
                    
                    <div class="col-md-6">
                        <label class="form-label fw-bold">IPK</label>
                        <input type="text" name="ipk" class="form-control" value="<?= isset($alumni['ipk']) ? htmlspecialchars($alumni['ipk']) : set_value('ipk') ?>" step="0.01" min="0" max="4">
                        <small class="text-muted">Indeks Prestasi Kumulatif (0.00 - 4.00)</small>
                    </div>
                </div>
                
                <hr class="my-4">
                
                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-save me-2"></i> Simpan
                    </button>
                    <a href="<?= base_url('admin/alumni') ?>" class="btn btn-outline-secondary">
                        <i class="bi bi-x-circle me-2"></i> Batal
                    </a>
                </div>
            <?= form_close() ?>
        </div>
    </div>
</div>

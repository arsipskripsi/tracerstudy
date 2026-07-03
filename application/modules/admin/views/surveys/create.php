<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-md-12">
            <a href="<?= site_url('admin/surveys') ?>" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i> Kembali
            </a>
            <h3 class="mt-3"><i class="bi bi-plus-circle"></i> Buat Survei Baru</h3>
        </div>
    </div>

    <?php if (validation_errors()): ?>
        <div class="alert alert-danger">
            <?= validation_errors() ?>
        </div>
    <?php endif; ?>

    <div class="row">
        <div class="col-md-8">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0 text-primary"><i class="bi bi-card-checklist me-2"></i>Informasi Survei</h5>
                </div>
                <div class="card-body">
                    <?= form_open('admin/surveys/store') ?>
                        <div class="form-group mb-3">
                            <label for="title">Judul Survei <span class="text-danger">*</span></label>
                            <input type="text" name="title" id="title" class="form-control" 
                                   value="<?= set_value('title') ?>" required maxlength="255"
                                   placeholder="Contoh: Survey Kepuasan Alumni 2024">
                            <small class="form-text text-muted">Masukkan judul yang jelas dan deskriptif</small>
                        </div>

                        <div class="form-group mb-3">
                            <label for="description">Deskripsi</label>
                            <textarea name="description" id="description" class="form-control" rows="4"
                                      placeholder="Deskripsikan tujuan dan scope survey ini..."><?= set_value('description') ?></textarea>
                            <small class="form-text text-muted">Deskripsi akan ditampilkan kepada responden sebelum memulai survey</small>
                        </div>

                        <hr>

                        <div class="form-group mb-3">
                            <div class="form-check">
                                <input type="checkbox" name="add_core" id="add_core" class="form-check-input" value="1" checked>
                                <label class="form-check-label" for="add_core">
                                    <strong>Tambahkan 20 Pertanyaan Inti Belmawa</strong>
                                </label>
                            </div>
                            <small class="form-text text-muted">
                                Pertanyaan inti adalah standar dari Belmawa yang wajib ada untuk publish survey.
                                Minimal 20 pertanyaan inti diperlukan sebelum survey dapat dipublikasikan.
                            </small>
                        </div>

                        <div class="form-group mt-4">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-save"></i> Buat Survei
                            </button>
                            <a href="<?= site_url('admin/surveys') ?>" class="btn btn-secondary">Batal</a>
                        </div>
                    <?= form_close() ?>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card shadow-sm border-0 bg-light">
                <div class="card-header bg-white">
                    <h6><i class="bi bi-info-circle"></i> Tentang Pertanyaan Inti</h6>
                </div>
                <div class="card-body">
                    <div class="alert alert-info">
                        <p class="mb-2"><strong>BR-SUR-002:</strong> Minimal 20 pertanyaan inti diperlukan untuk publish survey.</p>
                        <p class="mb-0"><strong>BR-SUR-001:</strong> Pertanyaan inti tidak dapat dihapus atau diubah setelah dibuat.</p>
                    </div>

                    <h6>Daftar 20 Pertanyaan Inti:</h6>
                    <small class="text-muted">Preview pertanyaan yang akan ditambahkan:</small>
                    
                    <div style="max-height: 400px; overflow-y: auto;" class="mt-2">
                        <?php foreach ($core_questions as $index => $q): ?>
                            <div class="p-2 mb-2 border-start border-3 border-danger bg-white">
                                <small class="text-muted">#<?= $index + 1 ?> [<?= ucfirst($q['type']) ?>]</small>
                                <p class="mb-0 small"><?= htmlspecialchars($q['question_text']) ?></p>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-flex align-items-center justify-content-between">
                <h4 class="mb-0"><?= $title; ?></h4>
            </div>
        </div>
    </div>

    <?php if ($this->session->flashdata('error')): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle"></i> <?= $this->session->flashdata('error'); ?>
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    <?php endif; ?>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <form action="<?= $action; ?>" method="post">
                        <div class="form-group">
                            <label for="name">Nama Kohort <span class="text-danger">*</span></label>
                            <input type="text" 
                                   class="form-control <?= form_error('name') ? 'is-invalid' : ''; ?>" 
                                   id="name" 
                                   name="name" 
                                   placeholder="Contoh: Kohort 2024"
                                   value="<?= set_value('name', isset($kohort) ? $kohort->nama : ''); ?>"
                                   required>
                            <?php if (form_error('name')): ?>
                                <div class="invalid-feedback"><?= form_error('name'); ?></div>
                            <?php endif; ?>
                            <small class="form-text text-muted">
                                Nama kohort biasanya mengikuti format "Kohort [Tahun]"
                            </small>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="tahun_mulai">Tahun Mulai <span class="text-danger">*</span></label>
                                    <input type="number" 
                                           class="form-control <?= form_error('tahun_mulai') ? 'is-invalid' : ''; ?>" 
                                           id="tahun_mulai" 
                                           name="tahun_mulai" 
                                           min="1950" 
                                           max="<?= date('Y'); ?>"
                                           value="<?= set_value('tahun_mulai', isset($kohort) ? $kohort->tahun_mulai : date('Y') - 3); ?>"
                                           required>
                                    <?php if (form_error('tahun_mulai')): ?>
                                        <div class="invalid-feedback"><?= form_error('tahun_mulai'); ?></div>
                                    <?php endif; ?>
                                    <small class="form-text text-muted">
                                        Tahun mulai studi (biasanya 3-4 tahun sebelum tahun selesai)
                                    </small>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="tahun_selesai">Tahun Selesai <span class="text-danger">*</span></label>
                                    <input type="number" 
                                           class="form-control <?= form_error('tahun_selesai') ? 'is-invalid' : ''; ?>" 
                                           id="tahun_selesai" 
                                           name="tahun_selesai" 
                                           min="1950" 
                                           max="<?= date('Y') + 5; ?>"
                                           value="<?= set_value('tahun_selesai', isset($kohort) ? $kohort->tahun_selesai : date('Y')); ?>"
                                           required>
                                    <?php if (form_error('tahun_selesai')): ?>
                                        <div class="invalid-feedback"><?= form_error('tahun_selesai'); ?></div>
                                    <?php endif; ?>
                                    <small class="form-text text-muted">
                                        Tahun kelulusan alumni. Digunakan untuk matching dengan tanggal_lulus di tabel alumni.
                                    </small>
                                </div>
                            </div>
                        </div>

                        <?php if (isset($kohort)): ?>
                        <div class="form-group">
                            <label>Status: </label>
                            <span class="badge badge-<?= $kohort->status == 'aktif' ? 'success' : 'secondary'; ?>">
                                <?= ucfirst($kohort->status); ?>
                            </span>
                            <small class="form-text text-muted d-block mt-1">
                                Gunakan tombol toggle di halaman list untuk mengubah status.
                            </small>
                        </div>
                        <?php endif; ?>

                        <hr>
                        
                        <div class="form-group mb-0">
                            <a href="<?= site_url('kohort'); ?>" class="btn btn-secondary">
                                <i class="fas fa-arrow-left"></i> Batal
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> <?= isset($kohort) ? 'Update' : 'Simpan'; ?>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // Auto-generate name based on tahun_selesai
    $('#tahun_selesai').on('input', function() {
        var year = $(this).val();
        if (year && year.length === 4) {
            var currentName = $('#name').val();
            // Only auto-update if name follows pattern or is empty
            if (currentName === '' || currentName.match(/^Kohort \d{4}$/)) {
                $('#name').val('Kohort ' + year);
            }
        }
    });
    
    // Auto-calculate tahun_mulai when tahun_selesai changes (assuming 4-year program)
    $('#tahun_selesai').on('change', function() {
        var tahun_selesai = $(this).val();
        if (tahun_selesai && !$('#tahun_mulai').val()) {
            $('#tahun_mulai').val(parseInt(tahun_selesai) - 3);
        }
    });
});
</script>

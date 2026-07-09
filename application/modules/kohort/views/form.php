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
                                   value="<?= set_value('name', isset($kohort) ? $kohort->name : ''); ?>"
                                   required>
                            <?php if (form_error('name')): ?>
                                <div class="invalid-feedback"><?= form_error('name'); ?></div>
                            <?php endif; ?>
                            <small class="form-text text-muted">
                                Nama kohort biasanya mengikuti format "Kohort [Tahun]"
                            </small>
                        </div>

                        <div class="form-group">
                            <label for="graduation_year">Tahun Lulus <span class="text-danger">*</span></label>
                            <input type="number" 
                                   class="form-control <?= form_error('graduation_year') ? 'is-invalid' : ''; ?>" 
                                   id="graduation_year" 
                                   name="graduation_year" 
                                   min="1950" 
                                   max="<?= date('Y') + 5; ?>"
                                   value="<?= set_value('graduation_year', isset($kohort) ? $kohort->graduation_year : date('Y')); ?>"
                                   required>
                            <?php if (form_error('graduation_year')): ?>
                                <div class="invalid-feedback"><?= form_error('graduation_year'); ?></div>
                            <?php endif; ?>
                            <small class="form-text text-muted">
                                Tahun kelulusan alumni yang akan dimasukkan ke kohort ini
                            </small>
                        </div>

                        <div class="form-group">
                            <div class="custom-control custom-switch">
                                <input type="checkbox" 
                                       class="custom-control-input" 
                                       id="is_active" 
                                       name="is_active" 
                                       value="1"
                                       <?= (!isset($kohort) || (isset($kohort) && $kohort->is_active)) ? 'checked' : ''; ?>>
                                <label class="custom-control-label" for="is_active">Aktif</label>
                            </div>
                            <small class="form-text text-muted">
                                Kohort yang tidak aktif tidak akan ditampilkan dalam pilihan default
                            </small>
                        </div>

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
    // Auto-generate name based on year
    $('#graduation_year').on('input', function() {
        var year = $(this).val();
        if (year && year.length === 4) {
            var currentName = $('#name').val();
            // Only auto-update if name follows pattern or is empty
            if (currentName === '' || currentName.match(/^Kohort \d{4}$/)) {
                $('#name').val('Kohort ' + year);
            }
        }
    });
});
</script>

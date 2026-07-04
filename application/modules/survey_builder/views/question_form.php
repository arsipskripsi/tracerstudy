<?php
/**
 * View: Question Form
 * Layout: Admin Template
 */
$this->load->view('admin/templates/header', [
    'page_title' => isset($survey) ? 'Tambah Pertanyaan - ' . $survey->title : 'Tambah Pertanyaan',
    'page_subtitle' => 'Survey: ' . (isset($survey) ? htmlspecialchars($survey->title) : '')
]);
?>

<div class="row mb-4">
    <div class="col-md-12">
        <a href="<?= site_url('admin/surveys/edit/' . $survey->id) ?>" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Kembali
        </a>
        <h3 class="mt-3"><i class="bi bi-plus-circle"></i> Tambah Pertanyaan Baru</h3>
    </div>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0"><i class="bi bi-edit"></i> Form Pertanyaan</h5>
            </div>
            <div class="card-body">
                <?= form_open('admin/surveys/question/store/' . $survey->id, ['id' => 'questionForm']) ?>
                    <div class="mb-3">
                        <label for="question_text" class="form-label">Teks Pertanyaan *</label>
                        <textarea name="question_text" id="question_text" class="form-control" rows="3" required placeholder="Masukkan teks pertanyaan"></textarea>
                    </div>

                    <div class="mb-3">
                        <label for="question_type" class="form-label">Tipe Pertanyaan *</label>
                        <select name="question_type" id="question_type" class="form-select" required>
                            <option value="">-- Pilih Tipe --</option>
                            <?php foreach ($question_types as $value => $label): ?>
                                <option value="<?= $value ?>"><?= $label ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-3" id="options_container" style="display: none;">
                        <label for="options" class="form-label">Opsi Jawaban (satu per baris) *</label>
                        <textarea name="options" id="options" class="form-control" rows="5" placeholder="Contoh:&#10;Sangat Puas&#10;Puas&#10;Cukup Puas&#10;Kurang Puas&#10;Sangat Kurang Puas"></textarea>
                        <div class="form-text">Masukkan setiap opsi pada baris terpisah</div>
                    </div>

                    <div class="mb-3">
                        <label for="help_text" class="form-label">Teks Bantuan (Opsional)</label>
                        <input type="text" name="help_text" id="help_text" class="form-control" placeholder="Teks bantuan untuk membantu responden">
                    </div>

                    <div class="mb-3">
                        <label for="placeholder" class="form-label">Placeholder (Opsional)</label>
                        <input type="text" name="placeholder" id="placeholder" class="form-control" placeholder="Teks placeholder untuk input">
                    </div>

                    <div class="mb-3 form-check">
                        <input type="checkbox" name="is_required" id="is_required" class="form-check-input" value="1">
                        <label class="form-check-label" for="is_required">Wajib Diisi</label>
                    </div>

                    <hr>

                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-save"></i> Simpan Pertanyaan
                    </button>
                    <a href="<?= site_url('admin/surveys/edit/' . $survey->id) ?>" class="btn btn-secondary">
                        <i class="bi bi-x-circle"></i> Batal
                    </a>
                <?= form_close() ?>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-info text-white">
                <h5 class="mb-0"><i class="bi bi-info-circle"></i> Info Tipe Pertanyaan</h5>
            </div>
            <div class="card-body">
                <ul class="list-unstyled">
                    <li class="mb-2"><strong>Text:</strong> Jawaban singkat satu baris</li>
                    <li class="mb-2"><strong>Textarea:</strong> Jawaban panjang beberapa baris</li>
                    <li class="mb-2"><strong>Number:</strong> Input angka</li>
                    <li class="mb-2"><strong>Date:</strong> Pilihan tanggal</li>
                    <li class="mb-2"><strong>Radio:</strong> Pilihan ganda (satu jawaban)</li>
                    <li class="mb-2"><strong>Checkbox:</strong> Checkbox (banyak jawaban)</li>
                    <li class="mb-2"><strong>Dropdown:</strong> Dropdown menu (satu jawaban)</li>
                    <li class="mb-2"><strong>Matrix:</strong> Pertanyaan matriks</li>
                    <li class="mb-2"><strong>File:</strong> Upload file</li>
                    <li class="mb-2"><strong>Scale Likert:</strong> Skala likert 1-5</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // Show/hide options field based on question type
    $('#question_type').on('change', function() {
        var selectedType = $(this).val();
        if (['radio', 'checkbox', 'dropdown'].includes(selectedType)) {
            $('#options_container').slideDown();
            $('#options').attr('required', 'required');
        } else {
            $('#options_container').slideUp();
            $('#options').removeAttr('required');
        }
    });

    // Form submission
    $('#questionForm').on('submit', function(e) {
        e.preventDefault();
        
        $.ajax({
            url: this.action,
            type: 'POST',
            data: $(this).serialize(),
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil!',
                        text: response.message
                    }).then(() => {
                        window.location.href = '<?= site_url('admin/surveys/edit/' . $survey->id) ?>';
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: response.message
                    });
                }
            },
            error: function(xhr) {
                const msg = xhr.responseJSON ? xhr.responseJSON.message : 'Terjadi kesalahan';
                Swal.fire({
                    icon: 'error',
                    title: 'Error!',
                    text: msg
                });
            }
        });
    });
});
</script>

<?php $this->load->view('admin/templates/footer'); ?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Pertanyaan - <?= htmlspecialchars($survey->title) ?></title>
    <link rel="stylesheet" href="<?= base_url('assets/css/bootstrap.min.css') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/css/font-awesome.min.css') ?>">
</head>
<body>
    <div class="container-fluid py-4">
        <div class="row mb-4">
            <div class="col-md-12">
                <a href="<?= site_url('survey_builder/questions/' . $survey->id) ?>" class="btn btn-secondary">
                    <i class="fa fa-arrow-left"></i> Kembali
                </a>
                <h3 class="mt-3"><i class="fa fa-plus-circle"></i> Tambah Pertanyaan Baru</h3>
                <p class="text-muted">Survey: <strong><?= htmlspecialchars($survey->title) ?></strong></p>
            </div>
        </div>

        <div class="row">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0"><i class="fa fa-edit"></i> Form Pertanyaan</h5>
                    </div>
                    <div class="card-body">
                        <?= form_open('survey/question/store/' . $survey->id, ['id' => 'questionForm']) ?>
                            <div class="form-group">
                                <label for="question_text">Teks Pertanyaan *</label>
                                <textarea name="question_text" id="question_text" class="form-control" rows="3" required placeholder="Masukkan teks pertanyaan"></textarea>
                            </div>

                            <div class="form-group">
                                <label for="question_type">Tipe Pertanyaan *</label>
                                <select name="question_type" id="question_type" class="form-control" required>
                                    <option value="">-- Pilih Tipe --</option>
                                    <?php foreach ($question_types as $value => $label): ?>
                                        <option value="<?= $value ?>"><?= $label ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="form-group" id="options_container" style="display: none;">
                                <label for="options">Opsi Jawaban (satu per baris) *</label>
                                <textarea name="options" id="options" class="form-control" rows="5" placeholder="Contoh:&#10;Sangat Puas&#10;Puas&#10;Cukup Puas&#10;Kurang Puas&#10;Sangat Kurang Puas"></textarea>
                                <small class="form-text text-muted">Masukkan setiap opsi pada baris terpisah</small>
                            </div>

                            <div class="form-group">
                                <label for="help_text">Teks Bantuan (Opsional)</label>
                                <input type="text" name="help_text" id="help_text" class="form-control" placeholder="Teks bantuan untuk membantu responden">
                            </div>

                            <div class="form-group">
                                <label for="placeholder">Placeholder (Opsional)</label>
                                <input type="text" name="placeholder" id="placeholder" class="form-control" placeholder="Teks placeholder untuk input">
                            </div>

                            <div class="form-group form-check">
                                <input type="checkbox" name="is_required" id="is_required" class="form-check-input" value="1">
                                <label class="form-check-label" for="is_required">Wajib Diisi</label>
                            </div>

                            <hr>

                            <button type="submit" class="btn btn-primary">
                                <i class="fa fa-save"></i> Simpan Pertanyaan
                            </button>
                            <a href="<?= site_url('survey_builder/questions/' . $survey->id) ?>" class="btn btn-secondary">
                                <i class="fa fa-times"></i> Batal
                            </a>
                        <?= form_close() ?>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card">
                    <div class="card-header bg-info text-white">
                        <h5 class="mb-0"><i class="fa fa-info-circle"></i> Info Tipe Pertanyaan</h5>
                    </div>
                    <div class="card-body">
                        <ul class="list-unstyled">
                            <li><strong>Text:</strong> Jawaban singkat satu baris</li>
                            <li><strong>Textarea:</strong> Jawaban panjang beberapa baris</li>
                            <li><strong>Number:</strong> Input angka</li>
                            <li><strong>Date:</strong> Pilihan tanggal</li>
                            <li><strong>Radio:</strong> Pilihan ganda (satu jawaban)</li>
                            <li><strong>Checkbox:</strong> Checkbox (banyak jawaban)</li>
                            <li><strong>Dropdown:</strong> Dropdown menu (satu jawaban)</li>
                            <li><strong>Matrix:</strong> Pertanyaan matriks</li>
                            <li><strong>File:</strong> Upload file</li>
                            <li><strong>Scale Likert:</strong> Skala likert 1-5</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="<?= base_url('assets/js/jquery.min.js') ?>"></script>
    <script src="<?= base_url('assets/js/bootstrap.bundle.min.js') ?>"></script>
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
                            alert(response.message);
                            window.location.href = '<?= site_url('survey_builder/questions/' . $survey->id) ?>';
                        } else {
                            alert('Error: ' + response.message);
                        }
                    },
                    error: function(xhr) {
                        const msg = xhr.responseJSON ? xhr.responseJSON.message : 'Terjadi kesalahan';
                        alert('Error: ' + msg);
                    }
                });
            });
        });
    </script>
</body>
</html>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Survey - <?= htmlspecialchars($survey->title) ?></title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/jquery-ui@1.12.1/themes/smoothness/jquery-ui.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        .question-card { border: 1px solid #ddd; border-radius: 8px; padding: 15px; margin-bottom: 10px; background: #fff; cursor: move; }
        .question-card:hover { box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
        .question-card.core { border-left: 4px solid #dc3545; }
        .question-handle { color: #6c757d; margin-right: 10px; }
        .tab-content { padding: 20px 0; }
        .nav-tabs .nav-link.active { font-weight: bold; }
        .question-type-badge { background: #e9ecef; padding: 3px 8px; border-radius: 4px; font-size: 11px; }
        .core-badge { background: #dc3545; color: white; padding: 3px 8px; border-radius: 4px; font-size: 11px; }
        .ui-sortable-helper { box-shadow: 0 5px 15px rgba(0,0,0,0.2); }
        .ui-sortable-placeholder { visibility: visible !important; background: #f0f0f0; border: 2px dashed #ccc; }
        .form-select { display: block; width: 100%; padding: 0.375rem 0.75rem; font-size: 1rem; font-weight: 400; line-height: 1.5; color: #495057; background-color: #fff; background-clip: padding-box; border: 1px solid #ced4da; border-radius: 0.25rem; transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out; }
        .form-label { display: inline-block; margin-bottom: 0.5rem; font-weight: 500; }
        .form-check-input { width: 1em; height: 1em; margin-top: 0.25em; vertical-align: top; background-color: #fff; background-repeat: no-repeat; background-position: center; background-size: contain; border: 1px solid rgba(0,0,0,0.25); appearance: none; -webkit-appearance: none; -moz-appearance: none; }
        .form-check-label { margin-left: 0.5em; }
    </style>
</head>
<body>
    <div class="container-fluid py-4">
        <div class="row mb-4">
            <div class="col-md-12">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <a href="<?= site_url('survey_builder/index') ?>" class="btn btn-secondary btn-sm">
                            <i class="fa fa-arrow-left"></i> Kembali
                        </a>
                        <h3 class="mt-2 d-inline"><i class="fa fa-edit"></i> Edit Survey</h3>
                        <span class="badge badge-warning ml-2">DRAFT</span>
                    </div>
                    <div>
                        <button onclick="publishSurvey()" class="btn btn-success">
                            <i class="fa fa-check"></i> Publish Survey
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <?php if ($this->session->flashdata('success')): ?>
            <div class="alert alert-success"><?= $this->session->flashdata('success') ?></div>
        <?php endif; ?>
        <?php if ($this->session->flashdata('error')): ?>
            <div class="alert alert-danger"><?= $this->session->flashdata('error') ?></div>
        <?php endif; ?>

        <ul class="nav nav-tabs" id="surveyTabs" role="tablist">
            <li class="nav-item">
                <a class="nav-link active" id="info-tab" data-bs-toggle="tab" href="#info" role="tab">
                    <i class="fa fa-info-circle"></i> Informasi Survey
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" id="questions-tab" data-bs-toggle="tab" href="#questions" role="tab">
                    <i class="fa fa-question-circle"></i> Pertanyaan 
                    <span class="badge badge-primary"><?= count($questions) ?></span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" id="logic-tab" data-bs-toggle="tab" href="#logic" role="tab">
                    <i class="fa fa-random"></i> Logic Jump
                </a>
            </li>
        </ul>

        <div class="tab-content" id="surveyTabContent">
            <!-- Tab Informasi -->
            <div class="tab-pane fade show active" id="info" role="tabpanel">
                <?= form_open('survey_builder/update/' . $survey->id) ?>
                    <div class="form-group">
                        <label for="title">Judul Survey</label>
                        <input type="text" name="title" class="form-control" value="<?= htmlspecialchars($survey->title) ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="description">Deskripsi</label>
                        <textarea name="description" class="form-control" rows="4"><?= htmlspecialchars($survey->description ?? '') ?></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary"><i class="fa fa-save"></i> Simpan Perubahan</button>
                <?= form_close() ?>
            </div>

            <!-- Tab Pertanyaan -->
            <div class="tab-pane fade" id="questions" role="tabpanel">
                <div class="d-flex justify-content-between mb-3">
                    <h5>Daftar Pertanyaan</h5>
                    <?php if ($survey->status === 'draft'): ?>
                        <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#questionModal" onclick="openAddQuestionModal()">
                            <i class="fa fa-plus"></i> Tambah Pertanyaan
                        </button>
                    <?php endif; ?>
                </div>

                <div class="alert alert-info">
                    <i class="fa fa-info-circle"></i> <strong>Tips:</strong> Drag & drop pertanyaan untuk mengubah urutan. 
                    Pertanyaan inti (merah) tidak dapat dihapus atau diubah.
                </div>

                <div id="questions-list">
                    <?php if (empty($questions)): ?>
                        <p class="text-muted text-center py-4">Belum ada pertanyaan. Tambahkan pertanyaan pertama Anda!</p>
                    <?php else: ?>
                        <?php foreach ($questions as $q): ?>
                            <div class="question-card <?= $q->is_belma_inti ? 'core' : '' ?>" data-id="<?= $q->id ?>">
                                <div class="d-flex align-items-start">
                                    <span class="question-handle"><i class="fa fa-arrows-alt"></i></span>
                                    <div class="flex-grow-1">
                                        <div class="mb-2">
                                            <span class="question-type-badge"><?= strtoupper($q->question_type) ?></span>
                                            <?php if ($q->is_belma_inti): ?>
                                                <span class="core-badge"><i class="fa fa-lock"></i> CORE</span>
                                            <?php endif; ?>
                                            <span class="badge badge-secondary">Order: <?= $q->order ?></span>
                                        </div>
                                        <p class="mb-2"><strong><?= htmlspecialchars($q->question_text) ?></strong></p>
                                        <?php if ($q->options): ?>
                                            <small class="text-muted">Opsi: <?= str_replace('|', ', ', htmlspecialchars($q->options)) ?></small>
                                        <?php endif; ?>
                                    </div>
                                    <div class="ml-3">
                                        <?php if (!$q->is_belma_inti): ?>
                                            <button type="button" onclick="openEditQuestionModal(<?= $q->id ?>)" class="btn btn-sm btn-outline-primary" title="Edit">
                                                <i class="fa fa-edit"></i>
                                            </button>
                                            <button onclick="deleteQuestion(<?= $q->id ?>)" class="btn btn-sm btn-outline-danger" title="Hapus">
                                                <i class="fa fa-trash"></i>
                                            </button>
                                        <?php else: ?>
                                            <button class="btn btn-sm btn-light" disabled title="Pertanyaan inti tidak dapat diubah">
                                                <i class="fa fa-lock"></i>
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Tab Logic -->
            <div class="tab-pane fade" id="logic" role="tabpanel">
                <div class="d-flex justify-content-between mb-3">
                    <h5>Logic Jump / Conditional Branching</h5>
                    <a href="<?= site_url('survey_logic/create/' . $survey->id) ?>" class="btn btn-primary btn-sm">
                        <i class="fa fa-plus"></i> Tambah Logic
                    </a>
                </div>
                <div class="alert alert-warning">
                    <i class="fa fa-exclamation-triangle"></i> <strong>BR-SUR-003:</strong> Logic jump tidak boleh membuat circular reference.
                    Sistem akan otomatis mendeteksi dan menolak logic yang menyebabkan siklus.
                </div>
                <div id="logic-list">
                    <p class="text-muted text-center py-4">
                        Logic jump akan ditampilkan di sini setelah ditambahkan.
                        <br><small>Klik "Tambah Logic" untuk membuat aturan conditional branching.</small>
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Tambah/Edit Pertanyaan -->
    <div class="modal fade" id="questionModal" tabindex="-1" role="dialog" aria-labelledby="questionModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="questionModalLabel"><i class="fa fa-question-circle"></i> <span id="modalTitle">Tambah Pertanyaan</span></h5>
                    <button type="button" class="close text-white" data-bs-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form id="questionForm">
                        <input type="hidden" id="questionId" name="question_id" value="">
                        <input type="hidden" id="surveyId" name="survey_id" value="<?= $survey->id ?>">
                        
                        <div class="mb-3">
                            <label for="question_text" class="form-label">Teks Pertanyaan *</label>
                            <textarea name="question_text" id="question_text" class="form-control" rows="3" required placeholder="Masukkan teks pertanyaan"></textarea>
                        </div>

                        <div class="mb-3">
                            <label for="question_type" class="form-label">Tipe Pertanyaan *</label>
                            <select name="question_type" id="question_type" class="form-select" required>
                                <option value="">-- Pilih Tipe --</option>
                                <option value="text">Jawaban Singkat (Text)</option>
                                <option value="textarea">Jawaban Panjang (Textarea)</option>
                                <option value="number">Angka</option>
                                <option value="date">Tanggal</option>
                                <option value="radio">Pilihan Ganda (Radio)</option>
                                <option value="checkbox">Checkbox</option>
                                <option value="dropdown">Dropdown</option>
                                <option value="matrix">Matriks</option>
                                <option value="file">Upload File</option>
                                <option value="scale_likert">Skala Likert</option>
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
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fa fa-times"></i> Batal
                    </button>
                    <button type="button" class="btn btn-primary" onclick="saveQuestion()">
                        <i class="fa fa-save"></i> <span id="saveButtonText">Simpan Pertanyaan</span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        $(document).ready(function() {
            // Enable drag-drop reordering
            $('#questions-list').sortable({
                handle: '.question-handle',
                placeholder: 'ui-sortable-placeholder',
                update: function(event, ui) {
                    var orders = {};
                    $('.question-card').each(function(index) {
                        var qId = $(this).data('id');
                        orders[qId] = index + 1;
                        $(this).find('.badge-secondary').text('Order: ' + (index + 1));
                    });

                    $.ajax({
                        url: '<?= site_url('survey_question/reorder') ?>',
                        type: 'POST',
                        data: { survey_id: <?= $survey->id ?>, orders: orders },
                        dataType: 'json',
                        success: function(response) {
                            if (response.success) {
                                // Show temporary success message
                                $('#questions-list').prepend(
                                    '<div class="alert alert-success alert-dismissible fade show">' +
                                    response.message + 
                                    '<button type="button" class="close" data-bs-dismiss="alert">&times;</button>' +
                                    '</div>'
                                );
                            }
                        }
                    });
                }
            });

            // Load logics on logic tab click
            $('#logic-tab').on('shown.bs.tab', function() {
                loadLogics();
            });
        });

        function deleteQuestion(id) {
            if (!confirm('Apakah Anda yakin ingin menghapus pertanyaan ini?')) return;

            $.ajax({
                url: '<?= site_url('survey_question/delete/' . $survey->id) ?>/' + id,
                type: 'DELETE',
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        location.reload();
                    } else {
                        alert('Error: ' + response.message);
                    }
                },
                error: function(xhr) {
                    const msg = xhr.responseJSON ? xhr.responseJSON.message : 'Terjadi kesalahan';
                    alert('Error: ' + msg);
                }
            });
        }

        function publishSurvey() {
            if (!confirm('Yakin ingin mempublikasikan survey ini? Pastikan sudah memiliki minimal 20 pertanyaan inti.')) {
                return;
            }

            $.ajax({
                url: '<?= site_url('survey_builder/publish/' . $survey->id) ?>',
                type: 'POST',
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        alert(response.message);
                        window.location = '<?= site_url('survey_builder/preview/' . $survey->id) ?>';
                    } else {
                        alert('Error: ' + response.message);
                    }
                },
                error: function(xhr) {
                    const msg = xhr.responseJSON ? xhr.responseJSON.message : 'Terjadi kesalahan';
                    alert('Error: ' + msg);
                }
            });
        }

        function loadLogics() {
            $.ajax({
                url: '<?= site_url('survey_logic/get_logics/' . $survey->id) ?>',
                type: 'GET',
                dataType: 'json',
                success: function(response) {
                    if (response.logics.length === 0) {
                        $('#logic-list').html('<p class="text-muted text-center py-4">Belum ada logic jump.</p>');
                        return;
                    }

                    let html = '<div class="table-responsive"><table class="table table-bordered">';
                    html += '<thead class="thead-light"><tr><th>No</th><th>Pertanyaan Sumber</th><th>Kondisi</th><th>Lompat ke</th><th>Aksi</th></tr></thead><tbody>';
                    
                    response.logics.forEach((logic, idx) => {
                        html += `<tr>
                            <td>${idx + 1}</td>
                            <td>${logic.question_text}</td>
                            <td><code>${logic.condition_value}</code></td>
                            <td>${logic.target_text} (Order: ${logic.target_order})</td>
                            <td>
                                <button onclick="deleteLogic(${logic.id})" class="btn btn-sm btn-danger">
                                    <i class="fa fa-trash"></i> Hapus
                                </button>
                            </td>
                        </tr>`;
                    });
                    
                    html += '</tbody></table></div>';
                    $('#logic-list').html(html);
                }
            });
        }

        function deleteLogic(id) {
            if (!confirm('Hapus logic jump ini?')) return;

            $.ajax({
                url: '<?= site_url('survey_logic/delete/' . $survey->id) ?>/' + id,
                type: 'DELETE',
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        loadLogics();
                    } else {
                        alert('Error: ' + response.message);
                    }
                }
            });
        }

        // Modal functions for question management
        var currentMode = 'add'; // 'add' or 'edit'

        function openAddQuestionModal() {
            currentMode = 'add';
            $('#questionId').val('');
            $('#question_text').val('');
            $('#question_type').val('');
            $('#options').val('');
            $('#help_text').val('');
            $('#placeholder').val('');
            $('#is_required').prop('checked', false);
            $('#options_container').hide();
            $('#modalTitle').text('Tambah Pertanyaan');
            $('#saveButtonText').text('Simpan Pertanyaan');
        }

        function openEditQuestionModal(questionId) {
            currentMode = 'edit';
            $.ajax({
                url: '<?= site_url('survey_question/get_question/') ?>' + questionId,
                type: 'GET',
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        var q = response.question;
                        $('#questionId').val(q.id);
                        $('#question_text').val(q.question_text);
                        $('#question_type').val(q.question_type);
                        
                        // Parse options if exists
                        if (q.options) {
                            try {
                                var opts = JSON.parse(q.options);
                                $('#options').val(opts.join('\n'));
                            } catch(e) {
                                $('#options').val(q.options);
                            }
                            if (['radio', 'checkbox', 'dropdown'].includes(q.question_type)) {
                                $('#options_container').show();
                            }
                        } else {
                            $('#options').val('');
                        }
                        
                        $('#help_text').val(q.help_text || '');
                        $('#placeholder').val(q.placeholder || '');
                        $('#is_required').prop('checked', q.is_required == 1);
                        
                        $('#modalTitle').text('Edit Pertanyaan');
                        $('#saveButtonText').text('Update Pertanyaan');
                        
                        $('#questionModal').modal('show');
                    } else {
                        alert('Error: ' + response.message);
                    }
                },
                error: function(xhr) {
                    const msg = xhr.responseJSON ? xhr.responseJSON.message : 'Terjadi kesalahan';
                    alert('Error: ' + msg);
                }
            });
        }

        function saveQuestion() {
            var questionId = $('#questionId').val();
            var surveyId = $('#surveyId').val();
            var formData = $('#questionForm').serialize();
            
            // Validation
            var questionText = $('#question_text').val().trim();
            var questionType = $('#question_type').val();
            
            if (!questionText) {
                alert('Teks pertanyaan harus diisi!');
                return;
            }
            
            if (!questionType) {
                alert('Tipe pertanyaan harus dipilih!');
                return;
            }
            
            if (['radio', 'checkbox', 'dropdown'].includes(questionType)) {
                var options = $('#options').val().trim();
                if (!options) {
                    alert('Opsi jawaban harus diisi untuk tipe pertanyaan ini!');
                    return;
                }
            }
            
            var url = currentMode === 'add' 
                ? '<?= site_url('survey_question/store/') ?>' + surveyId
                : '<?= site_url('survey_question/update/') ?>' + surveyId + '/' + questionId;
            
            $.ajax({
                url: url,
                type: 'POST',
                data: formData,
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        $('#questionModal').modal('hide');
                        location.reload();
                    } else {
                        alert('Error: ' + response.message);
                    }
                },
                error: function(xhr) {
                    const msg = xhr.responseJSON ? xhr.responseJSON.message : 'Terjadi kesalahan';
                    alert('Error: ' + msg);
                }
            });
        }

        // Show/hide options field based on question type in modal
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
    </script>
</body>
</html>

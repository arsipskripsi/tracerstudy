<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <a href="<?= site_url('admin/surveys') ?>" class="btn btn-secondary btn-sm">
                        <i class="bi bi-arrow-left"></i> Kembali
                    </a>
                    <h3 class="mt-2 d-inline"><i class="bi bi-pencil-square"></i> Edit Survei</h3>
                    <span class="badge bg-warning text-dark ms-2">DRAFT</span>
                </div>
                <div>
                    <button onclick="publishSurvey(<?= $survey->id ?>)" class="btn btn-success">
                        <i class="bi bi-check-circle"></i> Publish Survey
                    </button>
                </div>
            </div>
        </div>
    </div>

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

    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <ul class="nav nav-tabs" id="surveyTabs" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" id="info-tab" data-bs-toggle="tab" data-bs-target="#info" type="button" role="tab">
                <i class="bi bi-info-circle"></i> Informasi Survei
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="questions-tab" data-bs-toggle="tab" data-bs-target="#questions" type="button" role="tab">
                <i class="bi bi-question-circle"></i> Pertanyaan 
                <span class="badge bg-primary"><?= count($questions) ?></span>
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="logic-tab" data-bs-toggle="tab" data-bs-target="#logic" type="button" role="tab">
                <i class="bi bi-random"></i> Logic Jump
            </button>
        </li>
    </ul>

    <div class="tab-content mt-3" id="surveyTabContent">
        <!-- Tab Informasi -->
        <div class="tab-pane fade show active" id="info" role="tabpanel">
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <?= form_open('admin/surveys/update/' . $survey->id) ?>
                        <div class="form-group mb-3">
                            <label for="title">Judul Survei</label>
                            <input type="text" name="title" class="form-control" value="<?= htmlspecialchars($survey->title) ?>" required>
                        </div>
                        <div class="form-group mb-3">
                            <label for="description">Deskripsi</label>
                            <textarea name="description" class="form-control" rows="4"><?= htmlspecialchars($survey->description ?? '') ?></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-save"></i> Simpan Perubahan
                        </button>
                    <?= form_close() ?>
                </div>
            </div>
        </div>

        <!-- Tab Pertanyaan -->
        <div class="tab-pane fade" id="questions" role="tabpanel">
            <div class="d-flex justify-content-between mb-3">
                <h5>Daftar Pertanyaan</h5>
                <button type="button" id="btnAddQuestion" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#questionModal" onclick="openAddQuestionModal()">
                    <i class="bi bi-plus"></i> Tambah Pertanyaan
                </button>
            </div>

            <div class="alert alert-info">
                <i class="bi bi-info-circle"></i> <strong>Tips:</strong> Drag & drop pertanyaan untuk mengubah urutan. 
                Pertanyaan inti (merah) tidak dapat dihapus atau diubah.
            </div>

            <div id="questions-list">
                <?php if (empty($questions)): ?>
                    <p class="text-muted text-center py-4">Belum ada pertanyaan. Tambahkan pertanyaan pertama Anda!</p>
                <?php else: ?>
                    <?php foreach ($questions as $q): ?>
                        <?php 
                        // Pastikan $q adalah array dan memiliki key yang diperlukan
                        if (!is_array($q)) continue;
                        $question_id = $q['id'] ?? null;
                        $question_type = $q['question_type'] ?? 'text';
                        $question_text = $q['question_text'] ?? '';
                        $is_belma_inti = $q['is_belma_inti'] ?? 0;
                        $question_order = $q['order'] ?? 0;
                        $options = $q['options'] ?? null;
                        
                        if (!$question_id) continue;
                        ?>
                        <div class="question-card card mb-2 <?= $is_belma_inti ? 'border-danger' : '' ?>" data-id="<?= $question_id ?>">
                            <div class="card-body p-3">
                                <div class="d-flex align-items-start">
                                    <span class="me-3 text-muted"><i class="bi bi-arrows-move"></i></span>
                                    <div class="flex-grow-1">
                                        <div class="mb-2">
                                            <span class="badge bg-secondary"><?= strtoupper($question_type) ?></span>
                                            <?php if ($is_belma_inti): ?>
                                                <span class="badge bg-danger"><i class="bi bi-lock"></i> CORE</span>
                                            <?php endif; ?>
                                            <span class="badge bg-light text-dark">Order: <?= $question_order ?></span>
                                        </div>
                                        <p class="mb-2"><strong><?= htmlspecialchars($question_text) ?></strong></p>
                                        <?php if ($options): ?>
                                            <small class="text-muted">Opsi: <?= implode(', ', json_decode($options, true) ?? []) ?></small>
                                        <?php endif; ?>
                                    </div>
                                    <div class="ms-3">
                                        <?php if (!$is_belma_inti): ?>
                                            <button onclick="openEditQuestionModal(<?= $question_id ?>)" class="btn btn-sm btn-outline-primary">
                                                <i class="bi bi-pencil"></i>
                                            </button>
                                            <button onclick="deleteQuestion(<?= $question_id ?>)" class="btn btn-sm btn-outline-danger">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        <?php else: ?>
                                            <button class="btn btn-sm btn-light" disabled title="Pertanyaan inti tidak dapat diubah">
                                                <i class="bi bi-lock"></i>
                                            </button>
                                        <?php endif; ?>
                                    </div>
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
                <a href="<?= site_url('admin/surveys/logic/create/' . $survey->id) ?>" class="btn btn-primary btn-sm">
                    <i class="bi bi-plus"></i> Tambah Logic
                </a>
            </div>
            <div class="alert alert-warning">
                <i class="bi bi-exclamation-triangle"></i> <strong>BR-SUR-003:</strong> Logic jump tidak boleh membuat circular reference.
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

<style>
    .question-card { cursor: move; }
    .question-card:hover { box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
    .question-card.border-danger { border-left-width: 4px !important; }
</style>

<script>
$(document).ready(function() {
    // Enable drag-drop reordering
    $('#questions-list').sortable({
        handle: '.bi-arrows-move',
        placeholder: 'card bg-light border-dashed',
        update: function(event, ui) {
            var orders = {};
            $('.question-card').each(function(index) {
                var qId = $(this).data('id');
                orders[qId] = index + 1;
                $(this).find('.badge.bg-light').text('Order: ' + (index + 1));
            });

            $.ajax({
                url: '<?= site_url('admin/surveys/question/reorder') ?>',
                type: 'POST',
                data: { survey_id: <?= $survey->id ?>, orders: orders },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        $('#questions-list').prepend(
                            '<div class="alert alert-success alert-dismissible fade show">' +
                            response.message + 
                            '<button type="button" class="btn-close" data-bs-dismiss="alert"></button>' +
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
    // Refresh CSRF token before deleting
    loadCsrfToken();
    
    Swal.fire({
        title: 'Apakah Anda yakin?',
        text: "Pertanyaan ini akan dihapus secara permanen!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Ya, hapus!',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: '<?= site_url('admin/surveys/question/delete/' . $survey->id) ?>/' + id,
                type: 'POST',
                dataType: 'json',
                data: {
                    [csrfTokenName]: csrfHash
                },
                success: function(response) {
                    // Update CSRF token from response for next request
                    if (response.csrf_token_name && response.csrf_hash) {
                        csrfTokenName = response.csrf_token_name;
                        csrfHash = response.csrf_hash;
                    }
                    
                    if (response.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Terhapus!',
                            text: 'Pertanyaan berhasil dihapus.',
                            timer: 1500,
                            showConfirmButton: false
                        }).then(() => {
                            location.reload();
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal!',
                            text: response.message || 'Gagal menghapus pertanyaan.'
                        });
                    }
                },
                error: function(xhr) {
                    const msg = xhr.responseJSON ? xhr.responseJSON.message : 'Terjadi kesalahan saat menghubungi server';
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: msg
                    });
                }
            });
        }
    });
}

function publishSurvey(id) {
    if (!confirm('Yakin ingin mempublikasikan survey ini? Pastikan sudah memiliki minimal 20 pertanyaan inti.')) {
        return;
    }

    $.ajax({
        url: '<?= site_url('admin/surveys/publish/') ?>' + id,
        type: 'POST',
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                alert(response.message);
                window.location = '<?= site_url('admin/surveys') ?>';
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
        url: '<?= site_url('admin/surveys/logic/get_logics/' . $survey->id) ?>',
        type: 'GET',
        dataType: 'json',
        success: function(response) {
            if (!response.logics || response.logics.length === 0) {
                $('#logic-list').html('<p class="text-muted text-center py-4">Belum ada logic jump.</p>');
                return;
            }

            let html = '<div class="table-responsive"><table class="table table-bordered">';
            html += '<thead class="table-light"><tr><th>No</th><th>Pertanyaan Sumber</th><th>Kondisi</th><th>Lompat ke</th><th>Aksi</th></tr></thead><tbody>';
            
            response.logics.forEach((logic, idx) => {
                html += `<tr>
                    <td>${idx + 1}</td>
                    <td>${logic.question_text}</td>
                    <td><code>${logic.condition_value}</code></td>
                    <td>${logic.target_text} (Order: ${logic.target_order})</td>
                    <td>
                        <button onclick="deleteLogic(${logic.id})" class="btn btn-sm btn-danger">
                            <i class="bi bi-trash"></i> Hapus
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
    // Refresh CSRF token before deleting
    loadCsrfToken();
    
    Swal.fire({
        title: 'Apakah Anda yakin?',
        text: "Logic jump ini akan dihapus!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Ya, hapus!',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: '<?= site_url('admin/surveys/logic/delete/' . $survey->id) ?>/' + id,
                type: 'POST',
                dataType: 'json',
                data: {
                    [csrfTokenName]: csrfHash
                },
                success: function(response) {
                    // Update CSRF token from response for next request
                    if (response.csrf_token_name && response.csrf_hash) {
                        csrfTokenName = response.csrf_token_name;
                        csrfHash = response.csrf_hash;
                    }
                    
                    if (response.success) {
                        loadLogics();
                        Swal.fire({
                            icon: 'success',
                            title: 'Terhapus!',
                            text: 'Logic jump berhasil dihapus.',
                            timer: 1500,
                            showConfirmButton: false
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal!',
                            text: response.message || 'Gagal menghapus logic.'
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
        }
    });
}

// Modal functions for question management
var currentMode = 'add'; // 'add' or 'edit'
var csrfTokenName = '';
var csrfHash = '';

// Setup AJAX to automatically update CSRF token from response body
$.ajaxSetup({
    dataFilter: function(data, type) {
        if (type === 'json') {
            try {
                var response = JSON.parse(data);
                if (response.csrf_token_name && response.csrf_hash) {
                    csrfTokenName = response.csrf_token_name;
                    csrfHash = response.csrf_hash;
                }
            } catch(e) {
                // Ignore parsing errors for non-JSON responses
            }
        }
        return data;
    }
});

// Load CSRF token on page load
function loadCsrfToken() {
    $.ajax({
        url: '<?= site_url('admin/surveys/get_csrf_token') ?>',
        type: 'GET',
        dataType: 'json',
        async: false,
        success: function(response) {
            if (response.csrf_token_name && response.csrf_hash) {
                csrfTokenName = response.csrf_token_name;
                csrfHash = response.csrf_hash;
            }
        }
    });
}

// Load initial CSRF token
loadCsrfToken();

function openAddQuestionModal() {
    currentMode = 'add';
    $('#questionId').val('');
    $('#question_text').val('');
    $('#question_type').val('').trigger('change');
    $('#options').val('');
    $('#help_text').val('');
    $('#placeholder').val('');
    $('#is_required').prop('checked', false);
    $('#options_container').hide();
    $('#options').removeAttr('required');
    $('#modalTitle').text('Tambah Pertanyaan');
    $('#saveButtonText').text('Simpan Pertanyaan');
    
    // Refresh CSRF token when opening modal
    loadCsrfToken();
}

function openEditQuestionModal(questionId) {
    currentMode = 'edit';
    
    // Show loading state
    $('#questionModalLabel').html('<i class="bi bi-hourglass-split"></i> Memuat data...');
    
    // First ensure we have a fresh CSRF token
    $.ajax({
        url: '<?= site_url('admin/surveys/get_csrf_token') ?>',
        type: 'GET',
        dataType: 'json',
        success: function(tokenResponse) {
            if (tokenResponse.csrf_token_name && tokenResponse.csrf_hash) {
                csrfTokenName = tokenResponse.csrf_token_name;
                csrfHash = tokenResponse.csrf_hash;
                
                // Now fetch the question data
                $.ajax({
                    url: '<?= site_url('admin/surveys/get_question/') ?>' + questionId,
                    type: 'GET',
                    dataType: 'json',
                    success: function(response) {
                        if (response.success && response.question) {
                            var q = response.question;
                            
                            // Reset form first
                            $('#questionForm')[0].reset();
                            
                            // Populate form fields
                            $('#questionId').val(q.id);
                            $('#question_text').val(q.question_text);
                            $('#question_type').val(q.question_type).trigger('change');
                            
                            // Handle options
                            $('#options_container').hide();
                            $('#options').removeAttr('required');
                            
                            if (q.options) {
                                try {
                                    var opts = JSON.parse(q.options);
                                    if (Array.isArray(opts)) {
                                        $('#options').val(opts.join('\n'));
                                    } else {
                                        $('#options').val(q.options);
                                    }
                                } catch(e) {
                                    $('#options').val(q.options);
                                }
                            }
                            
                            // Show options field for applicable question types
                            if (['radio', 'checkbox', 'dropdown'].includes(q.question_type)) {
                                $('#options_container').show();
                                $('#options').attr('required', 'required');
                            }
                            
                            $('#help_text').val(q.help_text || '');
                            $('#placeholder').val(q.placeholder || '');
                            $('#is_required').prop('checked', q.is_required == 1);
                            
                            // Update modal title and button
                            $('#modalTitle').text('Edit Pertanyaan');
                            $('#saveButtonText').text('Update Pertanyaan');
                            $('#questionModalLabel').html('<i class="bi bi-question-circle"></i> <span id="modalTitle">Edit Pertanyaan</span>');
                            
                            // Show modal
                            var modalEl = document.getElementById('questionModal');
                            var modal = new bootstrap.Modal(modalEl);
                            modal.show();
                        } else {
                            $('#questionModalLabel').html('<i class="bi bi-question-circle"></i> <span id="modalTitle">Tambah Pertanyaan</span>');
                            Swal.fire('Error', response.message || 'Gagal memuat data pertanyaan', 'error');
                        }
                    },
                    error: function(xhr) {
                        $('#questionModalLabel').html('<i class="bi bi-question-circle"></i> <span id="modalTitle">Tambah Pertanyaan</span>');
                        const msg = xhr.responseJSON ? xhr.responseJSON.message : 'Terjadi kesalahan saat menghubungi server';
                        Swal.fire('Error', msg, 'error');
                    }
                });
            }
        },
        error: function() {
            $('#questionModalLabel').html('<i class="bi bi-question-circle"></i> <span id="modalTitle">Tambah Pertanyaan</span>');
            Swal.fire('Error', 'Gagal mendapatkan token keamanan', 'error');
        }
    });
}

function saveQuestion() {
    var questionId = $('#questionId').val();
    var surveyId = <?= $survey->id ?>;

    // Validation
    var questionText = $('#question_text').val().trim();
    var questionType = $('#question_type').val();

    if (!questionText) {
        Swal.fire('Peringatan', 'Teks pertanyaan harus diisi!', 'warning');
        return;
    }

    if (!questionType) {
        Swal.fire('Peringatan', 'Tipe pertanyaan harus dipilih!', 'warning');
        return;
    }

    if (['radio', 'checkbox', 'dropdown'].includes(questionType)) {
        var options = $('#options').val().trim();
        if (!options) {
            Swal.fire('Peringatan', 'Opsi jawaban harus diisi untuk tipe pertanyaan ini!', 'warning');
            return;
        }
    }

    // Build form data from form fields
    var formData = {
        question_text: questionText,
        question_type: questionType,
        options: $('#options').val(),
        help_text: $('#help_text').val(),
        placeholder: $('#placeholder').val(),
        is_required: $('#is_required').is(':checked') ? 1 : 0
    };

    // Add CSRF token from global variable
    formData[csrfTokenName] = csrfHash;

    var url = currentMode === 'add'
        ? '<?= site_url('admin/surveys/question/store/') ?>' + surveyId
        : '<?= site_url('admin/surveys/question/update/') ?>' + surveyId + '/' + questionId;

    $.ajax({
        url: url,
        type: 'POST',
        data: formData,
        dataType: 'json',
        success: function(response) {
            // Update CSRF token from response for next request
            if (response.csrf_token_name && response.csrf_hash) {
                csrfTokenName = response.csrf_token_name;
                csrfHash = response.csrf_hash;
            }
            
            if (response.success) {
                var modalEl = document.getElementById('questionModal');
                var modal = bootstrap.Modal.getInstance(modalEl);
                if (modal) modal.hide();
                
                // Show success notification with SweetAlert2
                if (currentMode === 'add') {
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil!',
                        text: 'Pertanyaan berhasil ditambahkan!',
                        timer: 2000,
                        showConfirmButton: false
                    }).then(() => {
                        location.reload();
                    });
                } else {
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil!',
                        text: 'Pertanyaan berhasil diperbarui!',
                        timer: 2000,
                        showConfirmButton: false
                    }).then(() => {
                        location.reload();
                    });
                }
            } else {
                Swal.fire('Error', response.message, 'error');
            }
        },
        error: function(xhr) {
            const msg = xhr.responseJSON ? xhr.responseJSON.message : 'Terjadi kesalahan';
            Swal.fire('Error', msg, 'error');
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
}).trigger('change'); // Trigger change event on page load to set initial state
</script>

<!-- Modal Tambah/Edit Pertanyaan -->
<div class="modal fade" id="questionModal" tabindex="-1" aria-labelledby="questionModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="questionModalLabel"><i class="bi bi-question-circle"></i> <span id="modalTitle">Tambah Pertanyaan</span></h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="questionForm">
                    <?= form_hidden($this->security->get_csrf_token_name(), $this->security->get_csrf_hash()) ?>
                    <input type="hidden" id="questionId" name="question_id" value="">

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
                        <small class="form-text text-muted">Masukkan setiap opsi pada baris terpisah</small>
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
                    <i class="bi bi-x-circle"></i> Batal
                </button>
                <button type="button" class="btn btn-primary" onclick="saveQuestion()">
                    <i class="bi bi-save"></i> <span id="saveButtonText">Simpan Pertanyaan</span>
                </button>
            </div>
        </div>
    </div>
</div>

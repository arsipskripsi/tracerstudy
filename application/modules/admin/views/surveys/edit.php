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
                <a href="<?= site_url('admin/surveys/question/create/' . $survey->id) ?>" class="btn btn-primary btn-sm">
                    <i class="bi bi-plus"></i> Tambah Pertanyaan
                </a>
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
                                            <a href="<?= site_url('admin/surveys/question/edit/' . $survey->id . '/' . $question_id) ?>" 
                                               class="btn btn-sm btn-outline-primary">
                                                <i class="bi bi-pencil"></i>
                                            </a>
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
    if (!confirm('Apakah Anda yakin ingin menghapus pertanyaan ini?')) return;

    $.ajax({
        url: '<?= site_url('admin/surveys/question/delete/' . $survey->id) ?>/' + id,
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
    if (!confirm('Hapus logic jump ini?')) return;

    $.ajax({
        url: '<?= site_url('admin/surveys/logic/delete/' . $survey->id) ?>/' + id,
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
</script>

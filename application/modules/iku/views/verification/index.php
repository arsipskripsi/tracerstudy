<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-flex align-items-center justify-content-between">
                <h4 class="mb-0"><?= $page_title; ?></h4>
                <div class="page-title-right">
                    <button type="button" class="btn btn-success" onclick="bulkAction('approve')" id="btnBulkApprove" disabled>
                        <i class="fas fa-check-circle"></i> Approve Selected
                    </button>
                    <button type="button" class="btn btn-danger ml-2" onclick="bulkAction('reject')" id="btnBulkReject" disabled>
                        <i class="fas fa-times-circle"></i> Reject Selected
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row">
        <div class="col-md-4">
            <div class="card card-body bg-light">
                <div class="d-flex justify-content-between">
                    <div>
                        <h5 class="mb-0 text-warning"><?= $pending_count; ?></h5>
                        <small class="text-muted">Pending Verification</small>
                    </div>
                    <i class="fas fa-clock fa-2x text-warning"></i>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card card-body bg-light">
                <div class="d-flex justify-content-between">
                    <div>
                        <h5 class="mb-0 text-success"><?= $approved_count; ?></h5>
                        <small class="text-muted">Approved</small>
                    </div>
                    <i class="fas fa-check-circle fa-2x text-success"></i>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card card-body bg-light">
                <div class="d-flex justify-content-between">
                    <div>
                        <h5 class="mb-0 text-danger"><?= $rejected_count; ?></h5>
                        <small class="text-muted">Rejected</small>
                    </div>
                    <i class="fas fa-times-circle fa-2x text-danger"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter Form -->
    <div class="row mt-3">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <form method="get" class="form-inline">
                        <div class="form-group mr-3">
                            <label class="mr-2">Status:</label>
                            <select name="status" class="form-control form-control-sm" onchange="this.form.submit()">
                                <option value="pending" <?= $status == 'pending' ? 'selected' : ''; ?>>Pending</option>
                                <option value="approved" <?= $status == 'approved' ? 'selected' : ''; ?>>Approved</option>
                                <option value="rejected" <?= $status == 'rejected' ? 'selected' : ''; ?>>Rejected</option>
                            </select>
                        </div>
                        <div class="form-group mr-3">
                            <label class="mr-2">Tahun Lulus:</label>
                            <select name="kohort_id" class="form-control form-control-sm" onchange="this.form.submit()">
                                <option value="">Semua Tahun</option>
                                <?php foreach ($kohorts as $k): ?>
                                    <option value="<?= $k['tahun']; ?>" <?= $this->input->get('kohort_id') == $k['tahun'] ? 'selected' : ''; ?>>
                                        <?= $k['tahun']; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group mr-3">
                            <label class="mr-2">Program Studi:</label>
                            <select name="prodi_id" class="form-control form-control-sm" onchange="this.form.submit()">
                                <option value="">Semua Prodi</option>
                                <?php foreach ($prodis as $p): ?>
                                    <option value="<?= $p['id']; ?>" <?= $this->input->get('prodi_id') == $p['id'] ? 'selected' : ''; ?>>
                                        <?= htmlspecialchars($p['nama']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Verification Table -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover datatable">
                            <thead class="bg-light">
                                <tr>
                                    <th style="width: 3%;">
                                        <input type="checkbox" id="selectAll" onclick="toggleAll(this)">
                                    </th>
                                    <th style="width: 5%;">No</th>
                                    <th style="width: 12%;">NIM</th>
                                    <th style="width: 20%;">Nama Alumni</th>
                                    <th style="width: 15%;">Program Studi</th>
                                    <th style="width: 15%;">Jenis Verifikasi</th>
                                    <th style="width: 10%;">Tanggal</th>
                                    <th style="width: 10%;">Status</th>
                                    <th style="width: 10%;" class="text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $no = 1;
                                foreach ($verifications as $v): 
                                ?>
                                <tr id="row-<?= $v['id']; ?>">
                                    <td>
                                        <?php if ($v['status'] == 'pending'): ?>
                                            <input type="checkbox" class="verification-checkbox" value="<?= $v['id']; ?>" onchange="updateBulkButtons()">
                                        <?php endif; ?>
                                    </td>
                                    <td><?= $no++; ?></td>
                                    <td><strong><?= htmlspecialchars($v['nim']); ?></strong></td>
                                    <td><?= htmlspecialchars($v['nama_lengkap']); ?></td>
                                    <td><?= htmlspecialchars($v['prodi_nama'] ?? '-'); ?></td>
                                    <td>
                                        <span class="badge badge-info"><?= htmlspecialchars(ucfirst($v['jenis_verifikasi'])); ?></span>
                                    </td>
                                    <td><?= date('d M Y H:i', strtotime($v['created_at'])); ?></td>
                                    <td>
                                        <?php if ($v['status'] == 'pending'): ?>
                                            <span class="badge badge-warning">Pending</span>
                                        <?php elseif ($v['status'] == 'approved'): ?>
                                            <span class="badge badge-success">Approved</span>
                                            <br><small class="text-muted">by <?= htmlspecialchars($v['verifikator_nama'] ?? 'System'); ?></small>
                                        <?php else: ?>
                                            <span class="badge badge-danger">Rejected</span>
                                            <br><small class="text-muted">by <?= htmlspecialchars($v['verifikator_nama'] ?? 'System'); ?></small>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center">
                                        <?php if ($v['status'] == 'pending'): ?>
                                            <button class="btn btn-sm btn-success" onclick="singleAction(<?= $v['id']; ?>, 'approve')" title="Approve">
                                                <i class="fas fa-check"></i>
                                            </button>
                                            <button class="btn btn-sm btn-danger" onclick="singleAction(<?= $v['id']; ?>, 'reject')" title="Reject">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        <?php else: ?>
                                            <span class="text-muted">-</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                                
                                <?php if (empty($verifications)): ?>
                                <tr>
                                    <td colspan="9" class="text-center">
                                        <i class="fas fa-inbox fa-3x text-muted mb-3 d-block"></i>
                                        Tidak ada data verifikasi untuk ditampilkan.
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

<!-- Modal for Catatan -->
<div class="modal fade" id="catatanModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Catatan Verifikasi</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="modalAction">
                <input type="hidden" id="modalIds">
                <div class="form-group">
                    <label>Catatan (opsional):</label>
                    <textarea class="form-control" id="modalCatatan" rows="3" placeholder="Masukkan catatan jika diperlukan..."></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-primary" onclick="submitVerification()">Proses</button>
            </div>
        </div>
    </div>
</div>

<script>
let selectedIds = [];

function toggleAll(source) {
    checkboxes = document.getElementsByClassName('verification-checkbox');
    for (var i = 0; i < checkboxes.length; i++) {
        checkboxes[i].checked = source.checked;
    }
    updateBulkButtons();
}

function updateBulkButtons() {
    selectedIds = [];
    document.querySelectorAll('.verification-checkbox:checked').forEach(cb => {
        selectedIds.push(cb.value);
    });
    
    document.getElementById('btnBulkApprove').disabled = selectedIds.length === 0;
    document.getElementById('btnBulkReject').disabled = selectedIds.length === 0;
}

function singleAction(id, action) {
    document.getElementById('modalAction').value = action;
    document.getElementById('modalIds').value = id;
    document.getElementById('catatanModal').modal('show');
}

function bulkAction(action) {
    if (selectedIds.length === 0) {
        alert('Pilih minimal satu data untuk diproses.');
        return;
    }
    
    document.getElementById('modalAction').value = action;
    document.getElementById('modalIds').value = selectedIds.join(',');
    document.getElementById('catatanModal').modal('show');
}

function submitVerification() {
    const action = document.getElementById('modalAction').value;
    const ids = document.getElementById('modalIds').value.split(',');
    const catatan = document.getElementById('modalCatatan').value;
    
    $.ajax({
        url: '<?= site_url('iku/bulkVerification'); ?>',
        type: 'POST',
        data: {
            ids: ids,
            action: action,
            catatan: catatan
        },
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                alert(response.message);
                location.reload();
            } else {
                alert('Error: ' + response.message);
            }
        },
        error: function(xhr, status, error) {
            alert('Terjadi kesalahan: ' + error);
        }
    });
    
    $('#catatanModal').modal('hide');
}

$(document).ready(function() {
    $('.datatable').DataTable({
        responsive: true,
        language: {
            url: '//cdn.datatables.net/plug-ins/1.10.21/i18n/Indonesian.json'
        }
    });
});
</script>

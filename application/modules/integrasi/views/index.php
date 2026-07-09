<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-flex align-items-center justify-content-between">
                <h4 class="mb-0"><?= $page_title; ?></h4>
                <button type="button" class="btn btn-primary" onclick="syncStatus()">
                    <i class="fas fa-sync"></i> Sync Status Nasional
                </button>
            </div>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row">
        <div class="col-md-4">
            <div class="card card-body bg-light">
                <div class="d-flex justify-content-between">
                    <div>
                        <h5 class="mb-0 text-primary"><?= $total_kohort; ?></h5>
                        <small class="text-muted">Total Kohort</small>
                    </div>
                    <i class="fas fa-users fa-2x text-primary"></i>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card card-body bg-light">
                <div class="d-flex justify-content-between">
                    <div>
                        <h5 class="mb-0 text-success"><?= $total_sent; ?></h5>
                        <small class="text-muted">Terkirim ke Belmawa</small>
                    </div>
                    <i class="fas fa-paper-plane fa-2x text-success"></i>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card card-body bg-light">
                <div class="d-flex justify-content-between">
                    <div>
                        <h5 class="mb-0 text-warning"><?= $total_pending; ?></h5>
                        <small class="text-muted">Belum Terkirim</small>
                    </div>
                    <i class="fas fa-clock fa-2x text-warning"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Pending Send Section -->
    <?php if (!empty($pending_send)): ?>
    <div class="row mt-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-warning">
                    <h5 class="mb-0"><i class="fas fa-exclamation-triangle"></i> Siap Dikirim ke Belmawa</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Kohort</th>
                                    <th>Program Studi</th>
                                    <th>Tahun IKU</th>
                                    <th>Final Score</th>
                                    <th>Verified At</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $no = 1;
                                foreach ($pending_send as $ps): 
                                ?>
                                <tr>
                                    <td><?= $no++; ?></td>
                                    <td><?= htmlspecialchars($ps['kohort_nama']); ?></td>
                                    <td><?= htmlspecialchars($ps['prodi_nama'] ?? 'All Prodi'); ?></td>
                                    <td><?= $ps['tahun_iku']; ?></td>
                                    <td>
                                        <span class="badge badge-<?= $ps['final_score'] >= 80 ? 'success' : 'warning'; ?>">
                                            <?= number_format($ps['final_score'], 2); ?>%
                                        </span>
                                    </td>
                                    <td><?= date('d M Y H:i', strtotime($ps['verified_at'])); ?></td>
                                    <td>
                                        <button class="btn btn-sm btn-success" onclick="sendToBelmawa(<?= $ps['id']; ?>)">
                                            <i class="fas fa-paper-plane"></i> Kirim
                                        </button>
                                        <a href="<?= site_url('integrasi/downloadExport/' . $ps['id'] . '/json'); ?>" 
                                           class="btn btn-sm btn-info" target="_blank">
                                            <i class="fas fa-download"></i> JSON
                                        </a>
                                        <a href="<?= site_url('integrasi/downloadExport/' . $ps['id'] . '/xml'); ?>" 
                                           class="btn btn-sm btn-info" target="_blank">
                                            <i class="fas fa-file-code"></i> XML
                                        </a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Recent Sent History -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-success">
                    <h5 class="mb-0"><i class="fas fa-history"></i> Riwayat Pengiriman ke Belmawa</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Kohort</th>
                                    <th>Program Studi</th>
                                    <th>Tahun IKU</th>
                                    <th>Final Score</th>
                                    <th>Dikirim Oleh</th>
                                    <th>Tanggal Kirim</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $no = 1;
                                foreach ($recent_sent as $rs): 
                                ?>
                                <tr>
                                    <td><?= $no++; ?></td>
                                    <td><?= htmlspecialchars($rs['kohort_nama']); ?></td>
                                    <td><?= htmlspecialchars($rs['prodi_nama'] ?? 'All Prodi'); ?></td>
                                    <td><?= $rs['tahun_iku']; ?></td>
                                    <td>
                                        <span class="badge badge-success">
                                            <?= number_format($rs['final_score'], 2); ?>%
                                        </span>
                                    </td>
                                    <td><?= htmlspecialchars($rs['sent_by_name'] ?? 'System'); ?></td>
                                    <td><?= date('d M Y H:i', strtotime($rs['sent_to_belmawa_at'])); ?></td>
                                    <td>
                                        <span class="badge badge-success">
                                            <i class="fas fa-check-circle"></i> Sent
                                        </span>
                                        <br>
                                        <small class="text-muted">Immutable</small>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                                
                                <?php if (empty($recent_sent)): ?>
                                <tr>
                                    <td colspan="8" class="text-center">
                                        <i class="fas fa-inbox fa-3x text-muted mb-3 d-block"></i>
                                        Belum ada data yang dikirim ke Belmawa.
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

<script>
function sendToBelmawa(calculationId) {
    if (!confirm('Apakah Anda yakin ingin mengirim data ini ke Belmawa? Data tidak dapat diubah setelah dikirim.')) {
        return;
    }

    $.ajax({
        url: '<?= site_url('integrasi/sendToBelmawa'); ?>/' + calculationId,
        type: 'POST',
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
}

function syncStatus() {
    $.ajax({
        url: '<?= site_url('integrasi/syncStatus'); ?>',
        type: 'POST',
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
}
</script>

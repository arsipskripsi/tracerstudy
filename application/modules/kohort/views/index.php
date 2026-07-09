<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-flex align-items-center justify-content-between">
                <h4 class="mb-0"><?= $title; ?></h4>
                <div class="page-title-right">
                    <a href="<?= site_url('kohort/create'); ?>" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Tambah Kohort Baru
                    </a>
                    <a href="<?= site_url('kohort/auto_generate'); ?>" class="btn btn-info ml-2">
                        <i class="fas fa-magic"></i> Auto Generate Kohort Tahun Ini
                    </a>
                </div>
            </div>
        </div>
    </div>

    <?php if ($this->session->flashdata('success')): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle"></i> <?= $this->session->flashdata('success'); ?>
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    <?php endif; ?>

    <?php if ($this->session->flashdata('error')): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle"></i> <?= $this->session->flashdata('error'); ?>
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    <?php endif; ?>

    <?php if ($this->session->flashdata('warning')): ?>
        <div class="alert alert-warning alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-triangle"></i> <?= $this->session->flashdata('warning'); ?>
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    <?php endif; ?>

    <?php if ($this->session->flashdata('info')): ?>
        <div class="alert alert-info alert-dismissible fade show" role="alert">
            <i class="fas fa-info-circle"></i> <?= $this->session->flashdata('info'); ?>
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    <?php endif; ?>

    <!-- Statistics Cards -->
    <div class="row mb-3">
        <div class="col-md-4">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <h5 class="card-title">Kohort Aktif</h5>
                    <h2 class="mb-0"><?= isset($active_count) ? $active_count : 0; ?></h2>
                    <small>Kohort dengan status = aktif</small>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card bg-warning text-dark">
                <div class="card-body">
                    <h5 class="card-title">Alumni Tanpa Kohort</h5>
                    <h2 class="mb-0"><?= isset($unassigned_count) ? $unassigned_count : 0; ?></h2>
                    <small>Tahun lulus tidak ada di kohort</small>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <h5 class="card-title">Total Kohort</h5>
                    <h2 class="mb-0"><?= count($kohorts); ?></h2>
                    <small>Semua kohort terdaftar</small>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover datatable">
                            <thead class="bg-light">
                                <tr>
                                    <th style="width: 5%;">No</th>
                                    <th style="width: 25%;">Nama Kohort</th>
                                    <th style="width: 15%;">Tahun Mulai</th>
                                    <th style="width: 15%;">Tahun Selesai</th>
                                    <th style="width: 10%;">Status</th>
                                    <th style="width: 15%;">Jumlah Alumni</th>
                                    <th style="width: 10%;" class="text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $no = 1;
                                foreach ($kohorts as $k): 
                                    $stats = $this->kohort_model->get_statistics($k->id);
                                ?>
                                <tr>
                                    <td><?= $no++; ?></td>
                                    <td><strong><?= htmlspecialchars($k->nama); ?></strong></td>
                                    <td><?= $k->tahun_mulai; ?></td>
                                    <td><?= $k->tahun_selesai; ?></td>
                                    <td>
                                        <?php if ($k->status == 'aktif'): ?>
                                            <span class="badge badge-success">Aktif</span>
                                        <?php else: ?>
                                            <span class="badge badge-secondary">Tidak Aktif</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($stats): ?>
                                            <?= $stats['total_alumni']; ?> alumni
                                            <br>
                                            <small class="text-muted">
                                                Responded: <?= $stats['total_responded']; ?> 
                                                (<?= $stats['response_rate']; ?>%)
                                            </small>
                                        <?php else: ?>
                                            0 alumni
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center">
                                        <a href="<?= site_url('kohort/edit/' . $k->id); ?>" 
                                           class="btn btn-sm btn-info" 
                                           title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <a href="<?= site_url('kohort/toggle_status/' . $k->id); ?>" 
                                           class="btn btn-sm btn-<?= $k->status == 'aktif' ? 'warning' : 'success'; ?>" 
                                           title="<?= $k->status == 'aktif' ? 'Nonaktifkan' : 'Aktifkan'; ?>"
                                           onclick="return confirm('Ubah status kohort ini?')">
                                            <i class="fas fa-<?= $k->status == 'aktif' ? 'pause' : 'play'; ?>"></i>
                                        </a>
                                        <a href="<?= site_url('kohort/delete/' . $k->id); ?>" 
                                           class="btn btn-sm btn-danger" 
                                           title="Hapus"
                                           onclick="return confirm('Apakah Anda yakin ingin menghapus kohort ini? Tindakan ini tidak dapat dibatalkan.')">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                                
                                <?php if (empty($kohorts)): ?>
                                <tr>
                                    <td colspan="7" class="text-center">
                                        <i class="fas fa-inbox fa-3x text-muted mb-3 d-block"></i>
                                        Belum ada kohort. Silakan tambahkan kohort baru.
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
$(document).ready(function() {
    $('.datatable').DataTable({
        responsive: true,
        language: {
            url: '//cdn.datatables.net/plug-ins/1.10.21/i18n/Indonesian.json'
        }
    });
});
</script>

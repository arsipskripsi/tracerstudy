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
                    <small>Kohort dengan tahun lulus >= <?= date('Y'); ?></small>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card bg-warning text-dark">
                <div class="card-body">
                    <h5 class="card-title">Alumni Tanpa Kohort</h5>
                    <h2 class="mb-0"><?= isset($unassigned_count) ? $unassigned_count : 0; ?></h2>
                    <small>Perlu dibuatkan kohort baru</small>
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
                                    <th style="width: 15%;">Tahun Lulus</th>
                                    <th style="width: 15%;">Status</th>
                                    <th style="width: 15%;">Jumlah Alumni</th>
                                    <th style="width: 15%;">Tanggal Dibuat</th>
                                    <th style="width: 10%;" class="text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $no = 1;
                                $current_year = date('Y');
                                foreach ($kohorts as $k): 
                                    $is_active = (isset($k->graduation_year) && $k->graduation_year >= $current_year);
                                    $stats = $this->kohort_model->get_statistics($k->id);
                                ?>
                                <tr>
                                    <td><?= $no++; ?></td>
                                    <td><strong><?= htmlspecialchars($k->name); ?></strong></td>
                                    <td><?= $k->graduation_year; ?></td>
                                    <td>
                                        <?php if ($is_active): ?>
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
                                    <td><?= isset($k->created_at) ? date('d M Y', strtotime($k->created_at)) : '-'; ?></td>
                                    <td class="text-center">
                                        <a href="<?= site_url('kohort/edit/' . $k->id); ?>" 
                                           class="btn btn-sm btn-info" 
                                           title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <?php if ($is_active): ?>
                                        <span class="badge badge-success ml-1">Aktif</span>
                                        <?php else: ?>
                                        <span class="badge badge-secondary ml-1">Tidak Aktif</span>
                                        <?php endif; ?>
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

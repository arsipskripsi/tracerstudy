<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="<?= site_url('prodi/iku') ?>">IKU Dashboard</a></li>
                    <li class="breadcrumb-item active">Detail IKU-<?= $iku_detail['iku_number'] ?></li>
                </ol>
            </nav>
            
            <div class="card shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="bi bi-bar-chart-line me-2"></i>Detail IKU-<?= $iku_detail['iku_number'] ?></h5>
                </div>
                <div class="card-body">
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <th width="200">Kohort</th>
                                    <td>: <?= $iku_detail['nama_kohort'] ?></td>
                                </tr>
                                <tr>
                                    <th>Tahun Lulus</th>
                                    <td>: <?= $iku_detail['tahun_lulus'] ?></td>
                                </tr>
                                <tr>
                                    <th>Indikator</th>
                                    <td>: IKU-<?= $iku_detail['iku_number'] ?></td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6 text-end">
                            <div class="display-4 fw-bold text-primary"><?= number_format($iku_detail['percentage'], 2) ?>%</div>
                            <small class="text-muted">Capaian saat ini</small>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-4">
                            <div class="card bg-light">
                                <div class="card-body text-center">
                                    <h6>Numerator</h6>
                                    <h3 class="text-success"><?= $iku_detail['numerator'] ?></h3>
                                    <small class="text-muted">Jumlah yang memenuhi kriteria</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card bg-light">
                                <div class="card-body text-center">
                                    <h6>Denominator</h6>
                                    <h3 class="text-info"><?= $iku_detail['denominator'] ?></h3>
                                    <small class="text-muted">Total target populasi</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card bg-light">
                                <div class="card-body text-center">
                                    <h6>Status</h6>
                                    <h3><span class="badge <?= $iku_detail['status_capaian'] == 'Melampaui' ? 'bg-success' : ($iku_detail['status_capaian'] == 'Tercapai' ? 'bg-info' : 'bg-secondary') ?>"><?= $iku_detail['status_capaian'] ?></span></h3>
                                    <small class="text-muted">Target: <?= number_format($iku_detail['target_percentage'], 2) ?>%</small>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <?php if ($iku_detail['mapping_data']): ?>
                    <div class="mt-4">
                        <h6>Data Mapping</h6>
                        <pre class="bg-light p-3 rounded"><?= json_encode(json_decode($iku_detail['mapping_data']), JSON_PRETTY_PRINT) ?></pre>
                    </div>
                    <?php endif; ?>
                    
                    <div class="mt-4">
                        <a href="<?= site_url('prodi/iku') ?>" class="btn btn-outline-secondary">
                            <i class="bi bi-arrow-left me-2"></i>Kembali
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

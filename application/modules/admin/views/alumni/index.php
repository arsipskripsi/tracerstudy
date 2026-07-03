<div class="container-fluid py-4">
    <!-- Flash Messages -->
    <?php if ($this->session->flashdata('message')) : ?>
        <div class="alert alert-<?= $this->session->flashdata('message_type') ?> alert-dismissible fade show" role="alert">
            <i class="bi bi-<?= $this->session->flashdata('message_type') == 'success' ? 'check-circle' : 'exclamation-circle' ?> me-2"></i>
            <?= $this->session->flashdata('message') ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>
    
    <div class="card shadow-sm border-0">
        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center border-bottom">
            <div>
                <h5 class="mb-0 text-primary"><i class="bi bi-person-badge me-2"></i>Data Alumni</h5>
                <small class="text-muted">Kelola data alumni</small>
            </div>
            <div>
                <a href="<?= base_url('admin/alumni/add') ?>" class="btn btn-primary btn-sm">
                    <i class="bi bi-plus-circle me-1"></i> Tambah Alumni
                </a>
            </div>
        </div>
        <div class="card-body">
            <?php if (!empty($alumni)): ?>
            <div class="table-responsive">
                <table class="table table-striped table-hover" id="alumniTable">
                    <thead class="table-dark">
                        <tr>
                            <th width="5%">No</th>
                            <th>NIM</th>
                            <th>Nama Lengkap</th>
                            <th>Program Studi</th>
                            <th>Tanggal Lulus</th>
                            <th>Email</th>
                            <th>Status</th>
                            <th width="10%" class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $no = 1; foreach ($alumni as $item): ?>
                        <tr>
                            <td class="text-center"><?= $no++ ?></td>
                            <td><?= htmlspecialchars($item['nim'] ?? '-') ?></td>
                            <td><?= htmlspecialchars($item['nama_lengkap'] ?? '-') ?></td>
                            <td><?= htmlspecialchars($item['prodi_nama'] ?? '-') ?></td>
                            <td><?= date('d M Y', strtotime($item['tanggal_lulus'] ?? 'now')) ?></td>
                            <td><?= htmlspecialchars($item['email'] ?? $item['email_pribadi'] ?? '-') ?></td>
                            <td>
                                <?php if ($item['user_id']): ?>
                                <span class="badge bg-success">Terdaftar</span>
                                <?php else: ?>
                                <span class="badge bg-warning">Belum Terdaftar</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-center">
                                <div class="dropdown">
                                    <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                        <i class="bi bi-three-dots-vertical"></i>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end">
                                        <li>
                                            <a class="dropdown-item" href="<?= base_url('admin/alumni/edit/' . $item['id']) ?>">
                                                <i class="bi bi-pencil me-2"></i>Edit
                                            </a>
                                        </li>
                                        <li>
                                            <hr class="dropdown-divider">
                                        </li>
                                        <li>
                                            <a class="dropdown-item text-danger" href="#" onclick="deleteAlumni(<?= $item['id'] ?>); return false;">
                                                <i class="bi bi-trash me-2"></i>Hapus
                                            </a>
                                        </li>
                                    </ul>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php else: ?>
            <div class="text-center py-5">
                <i class="bi bi-inbox text-muted fs-1"></i>
                <p class="text-muted mt-2">Belum ada data alumni</p>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- DataTables CSS -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css">

<!-- DataTables JS -->
<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>

<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    $(document).ready(function() {
        // Initialize alumni table with numbering
        if ($('#alumniTable').length) {
            $('#alumniTable').DataTable({
                order: [],
                columnDefs: [{
                    orderable: false,
                    targets: [0]
                }]
            });
        }
    });
    
    // Delete alumni function with SweetAlert
    function deleteAlumni(id) {
        Swal.fire({
            title: 'Apakah Anda yakin?',
            text: "Data alumni yang dihapus tidak dapat dikembalikan!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Ya, hapus!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: '<?php echo site_url("admin/alumni/delete"); ?>/' + id,
                    type: 'POST',
                    data: {
                        '<?php echo $this->security->get_csrf_token_name(); ?>': '<?php echo $this->security->get_csrf_hash(); ?>'
                    },
                    dataType: 'json',
                    success: function(res) {
                        if (res.success) {
                            Swal.fire(
                                'Terhapus!',
                                res.message,
                                'success'
                            ).then(() => {
                                location.reload();
                            });
                        } else {
                            Swal.fire(
                                'Gagal!',
                                res.message,
                                'error'
                            );
                        }
                    },
                    error: function() {
                        Swal.fire(
                            'Error!',
                            'Terjadi kesalahan saat menghapus alumni',
                            'error'
                        );
                    }
                });
            }
        });
    }
</script>

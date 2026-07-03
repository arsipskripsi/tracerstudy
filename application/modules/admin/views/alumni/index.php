<div class="container-fluid py-4">
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
                <table class="table table-striped table-hover">
                    <thead class="table-dark">
                        <tr>
                            <th>NIM</th>
                            <th>Nama Lengkap</th>
                            <th>Program Studi</th>
                            <th>Tanggal Lulus</th>
                            <th>Email</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($alumni as $item): ?>
                        <tr>
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
                            <td>
                                <a href="<?= base_url('admin/alumni/edit/' . $item['id']) ?>" class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <button class="btn btn-sm btn-outline-danger" onclick="deleteAlumni(<?= $item['id'] ?>)">
                                    <i class="bi bi-trash"></i>
                                </button>
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

<link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css">
<script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
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

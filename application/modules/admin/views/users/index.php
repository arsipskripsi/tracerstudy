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
                <h5 class="mb-0 text-primary"><i class="bi bi-people me-2"></i>Manajemen User</h5>
                <small class="text-muted">Kelola pengguna sistem</small>
            </div>
            <div>
                <a href="<?= base_url('admin/users/add') ?>" class="btn btn-primary btn-sm">
                    <i class="bi bi-plus-circle me-1"></i> Tambah User
                </a>
            </div>
        </div>
        <div class="card-body">
            <!-- Table -->
            <div class="table-responsive">
                <table id="users_table" class="table table-striped table-hover table-bordered" style="width:100%">
                    <thead class="table-dark">
                        <tr>
                            <th width="5%" class="text-center">No</th>
                            <th width="20%"><i class="bi bi-person me-1"></i> Username</th>
                            <th width="25%"><i class="bi bi-envelope me-1"></i> Email</th>
                            <th width="15%"><i class="bi bi-shield me-1"></i> Role</th>
                            <th width="15%"><i class="bi bi-calendar me-1"></i> Created</th>
                            <th width="20%" class="text-center"><i class="bi bi-gear me-1"></i> Actions</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">

<script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
$(document).ready(function() {
    // Init DataTables with Server-Side Processing
    var table = $('#users_table').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '<?php echo site_url("admin/users/get_data"); ?>',
            type: 'GET'
        },
        columns: [
            { data: null, orderable: false, className: 'text-center', render: function(data, type, row, meta) {
                return meta.row + meta.settings._iDisplayStart + 1;
            }},
            { data: 0, orderable: true },
            { data: 1, orderable: true },
            { data: 2, orderable: false },
            { data: 3, orderable: true },
            { data: 4, orderable: false, className: 'text-center' }
        ],
        order: [[3, 'desc']],
        pageLength: 25,
        lengthMenu: [10, 25, 50, 100],
        language: {
            processing: '<div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div>',
            zeroRecords: 'Tidak ada data user ditemukan',
            emptyTable: 'Belum ada user'
        }
    });
});

// Delete user function with SweetAlert
function deleteUser(id) {
    Swal.fire({
        title: 'Apakah Anda yakin?',
        text: "User yang dihapus tidak dapat dikembalikan!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Ya, hapus!',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: '<?php echo site_url("admin/users/delete"); ?>/' + id,
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
                        'Terjadi kesalahan saat menghapus user',
                        'error'
                    );
                }
            });
        }
    });
}
</script>

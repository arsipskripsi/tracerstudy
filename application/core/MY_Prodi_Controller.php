<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Prodi Controller - Base Controller untuk Admin Prodi/Fakultas/Dosen
 *
 * Base controller untuk modul prodi dengan akses terbatas berdasarkan role
 */
class MY_Prodi_Controller extends MY_Controller {

    protected $prodi_id = null;

    public function __construct() {
        parent::__construct();

        // Redirect ke login jika belum login
        if (!$this->is_logged_in) {
            redirect('auth/login');
            exit;
        }

        // Cek role yang diizinkan akses modul prodi
        $allowed_roles = array('admin_prodi', 'admin_fakultas', 'dosen');
        $user_role = $this->session->userdata('role');

        if (!in_array($user_role, $allowed_roles)) {
            show_error('Akses ditolak. Anda tidak memiliki hak akses ke halaman ini.', 403);
            exit;
        }

        // Get prodi_id dari session atau profile_id
        $this->prodi_id = $this->session->userdata('profile_id');
		

        if (!$this->prodi_id && $user_role === 'admin_prodi') {
            // Admin prodi wajib punya profile_id
           // Cek apakah user ini adalah user testing/development
            $username = $this->session->userdata('username');
			$user_id = $this->session->userdata('user_id');

            // Untuk development, izinkan akses dengan warning jika username mengandung 'test' atau 'demo'
            if (strpos(strtolower($username), 'test') !== false || strpos(strtolower($username), 'demo') !== false) {
                // Set prodi_id default untuk testing (ambil prodi pertama dari database)
                $this->db->select('id')->from('prodi')->limit(1);
                $default_prodi = $this->db->get()->row();
                if ($default_prodi) {
                    $this->prodi_id = $default_prodi->id;
                    $this->session->set_userdata('profile_id', $this->prodi_id);
                } else {
                    show_error('Tidak ada data program studi di database. Silakan tambahkan data prodi terlebih dahulu.', 403);
                    exit;
                }
            } else {
                // Untuk user admin_prodi non-test, coba ambil prodi_id dari database jika belum ada di session
                // Ini menangani kasus dimana user sudah memiliki relasi dengan prodi tapi session belum ter-set

                // Jika user_id tidak ada di session, ambil dari database berdasarkan username
                if (!$user_id && $username) {
                    $this->db->select('id')->from('users')->where('username', $username);
                    $user_record = $this->db->get()->row();
                    if ($user_record) {
                        $user_id = $user_record->id;
                    }
                }

                if ($user_id) {
                    $this->db->select('prodi_id')->from('users')->where('id', $user_id);
                    $user_data = $this->db->get()->row();

                    if ($user_data && $user_data->prodi_id) {
                        // User memiliki prodi_id di database, set ke session
                        $this->prodi_id = $user_data->prodi_id;
                        $this->session->set_userdata('profile_id', $this->prodi_id);
                    } else {
                        show_error('Konfigurasi akun tidak lengkap. User admin_prodi harus memiliki prodi_id. Hubungi administrator untuk mengaitkan akun Anda dengan program studi.', 403);
                        exit;
                    }
                } else {
                    show_error('Konfigurasi akun tidak lengkap. User admin_prodi harus memiliki prodi_id. Hubungi administrator untuk mengaitkan akun Anda dengan program studi.', 403);
                    exit;
                }
                exit;
            }
        }

        $this->data['page_title'] = 'Dashboard Prodi';
        $this->data['prodi_id'] = $this->prodi_id;
    }
}
<?php
defined('BASEPATH') OR exit('No direct script access allowed');

require_once APPPATH . 'core/MY_Controller.php';

/**
 * Kohort Module Controller
 * Handles CRUD operations for Kohort Management
 * Role: Admin Pusat Karir, Super Admin
 */
class Kohort extends MY_Controller {

    public function __construct() {
        parent::__construct();
        
        // Check authentication (already done in MY_Controller, but explicit check for clarity)
        if (!$this->is_logged_in) {
            redirect('auth/login');
        }

        // Load model
        $this->load->model('kohort_model');
        
        // Check role authorization
        $role = $this->session->userdata('role');
        if (!in_array($role, ['admin_pusat_karir', 'super_admin'])) {
            show_error('Akses ditolak. Hanya Admin Pusat Karir dan Super Admin yang dapat mengakses modul ini.', 403);
        }
    }

    /**
     * Display list of kohorts
     */
    public function index() {
        $data['title'] = 'Kelola Kohort Alumni';
        
        // Ambil semua kohort dari database
        $all_kohorts = $this->kohort_model->get_all();
        
        // Hitung kohort aktif secara manual (graduation_year >= tahun sekarang)
        $current_year = date('Y');
        $data['active_count'] = 0;
        foreach ($all_kohorts as $k) {
            if (isset($k->graduation_year) && $k->graduation_year >= $current_year) {
                $data['active_count']++;
            }
        }
        
        // Hitung alumni tanpa kohort
        $data['unassigned_count'] = $this->kohort_model->count_unassigned();
        
        $data['kohorts'] = $all_kohorts;
        
        $this->load->view('templates/admin/header');
        $this->load->view('templates/admin/sidebar');
        $this->load->view('kohort/index', $data);
        $this->load->view('templates/admin/footer');
    }

    /**
     * Show form to create new kohort
     */
    public function create() {
        $data['title'] = 'Tambah Kohort Baru';
        $data['action'] = site_url('kohort/store');
        
        $this->load->view('templates/admin/header');
        $this->load->view('templates/admin/sidebar');
        $this->load->view('kohort/form', $data);
        $this->load->view('templates/admin/footer');
    }

    /**
     * Store new kohort to database
     */
    public function store() {
        $this->form_validation->set_rules('name', 'Nama Kohort', 'required|trim|max_length[100]');
        $this->form_validation->set_rules('graduation_year', 'Tahun Lulus', 'required|integer|min_length[4]|max_length[4]');
        
        if ($this->form_validation->run() == FALSE) {
            $this->session->set_flashdata('error', validation_errors());
            redirect('kohort/create');
        } else {
            $graduation_year = $this->input->post('graduation_year', TRUE);
            $data = [
                'name' => $this->input->post('name', TRUE),
                'graduation_year' => $graduation_year
                // is_active ditentukan otomatis berdasarkan graduation_year >= tahun sekarang
            ];
            
            // Check if graduation year already exists
            $existing = $this->kohort_model->get_by_year($data['graduation_year']);
            if ($existing) {
                $this->session->set_flashdata('error', 'Kohort untuk tahun ' . $data['graduation_year'] . ' sudah ada.');
                redirect('kohort/create');
            }
            
            $result = $this->kohort_model->create($data);
            
            if ($result) {
                $this->session->set_flashdata('success', 'Kohort berhasil ditambahkan.');
                
                // Auto-assign alumni to this kohort based on graduation year
                $this->kohort_model->auto_assign_alumni($data['graduation_year']);
                
                redirect('kohort');
            } else {
                $this->session->set_flashdata('error', 'Gagal menambahkan kohort.');
                redirect('kohort/create');
            }
        }
    }

    /**
     * Show form to edit existing kohort
     */
    public function edit($id) {
        $data['title'] = 'Edit Kohort';
        $data['action'] = site_url('kohort/update/' . $id);
        $data['kohort'] = $this->kohort_model->get_by_id($id);
        
        if (!$data['kohort']) {
            show_404();
        }
        
        $this->load->view('templates/admin/header');
        $this->load->view('templates/admin/sidebar');
        $this->load->view('kohort/form', $data);
        $this->load->view('templates/admin/footer');
    }

    /**
     * Update existing kohort
     */
    public function update($id) {
        $this->form_validation->set_rules('name', 'Nama Kohort', 'required|trim|max_length[100]');
        $this->form_validation->set_rules('graduation_year', 'Tahun Lulus', 'required|integer|min_length[4]|max_length[4]');
        
        if ($this->form_validation->run() == FALSE) {
            $this->session->set_flashdata('error', validation_errors());
            redirect('kohort/edit/' . $id);
        } else {
            $graduation_year = $this->input->post('graduation_year', TRUE);
            $data = [
                'name' => $this->input->post('name', TRUE),
                'graduation_year' => $graduation_year
                // is_active ditentukan otomatis berdasarkan graduation_year >= tahun sekarang
            ];
            
            // Check if graduation year already exists for other kohort
            $existing = $this->kohort_model->get_by_year($data['graduation_year']);
            if ($existing && $existing->id != $id) {
                $this->session->set_flashdata('error', 'Kohort untuk tahun ' . $data['graduation_year'] . ' sudah ada.');
                redirect('kohort/edit/' . $id);
            }
            
            $result = $this->kohort_model->update($id, $data);
            
            if ($result) {
                $this->session->set_flashdata('success', 'Kohort berhasil diupdate.');
                redirect('kohort');
            } else {
                $this->session->set_flashdata('error', 'Gagal mengupdate kohort.');
                redirect('kohort/edit/' . $id);
            }
        }
    }

    /**
     * Delete kohort
     */
    public function delete($id) {
        $kohort = $this->kohort_model->get_by_id($id);
        
        if (!$kohort) {
            $this->session->set_flashdata('error', 'Kohort tidak ditemukan.');
            redirect('kohort');
        }
        
        // Prevent deletion if has assigned alumni (optional business rule)
        $alumni_count = $this->kohort_model->count_alumni($id);
        if ($alumni_count > 0) {
            $this->session->set_flashdata('warning', 'Tidak dapat menghapus kohort yang memiliki ' . $alumni_count . ' alumni. Nonaktifkan saja jika tidak digunakan.');
            redirect('kohort');
        }
        
        $result = $this->kohort_model->delete($id);
        
        if ($result) {
            $this->session->set_flashdata('success', 'Kohort berhasil dihapus.');
        } else {
            $this->session->set_flashdata('error', 'Gagal menghapus kohort.');
        }
        
        redirect('kohort');
    }

    /**
     * Toggle active status (deprecated - status now auto-calculated based on graduation_year)
     * This method is kept for backward compatibility but will redirect with info message
     */
    public function toggle_status($id) {
        $this->session->set_flashdata('info', 'Status kohort sekarang ditentukan otomatis berdasarkan tahun lulus. Kohort dengan tahun lulus >= tahun ini dianggap aktif.');
        redirect('kohort');
    }

    /**
     * Auto-generate kohort for current year if not exists
     */
    public function auto_generate() {
        $current_year = date('Y');
        $existing = $this->kohort_model->get_by_year($current_year);
        
        if (!$existing) {
            $data = [
                'name' => 'Kohort ' . $current_year,
                'graduation_year' => $current_year
            ];
            
            $result = $this->kohort_model->create($data);
            
            if ($result) {
                // Auto-assign alumni
                $this->kohort_model->auto_assign_alumni($current_year);
                $this->session->set_flashdata('success', 'Kohort ' . $current_year . ' berhasil dibuat otomatis.');
            } else {
                $this->session->set_flashdata('error', 'Gagal membuat kohort otomatis.');
            }
        } else {
            $this->session->set_flashdata('info', 'Kohort ' . $current_year . ' sudah ada.');
        }
        
        redirect('kohort');
    }
}

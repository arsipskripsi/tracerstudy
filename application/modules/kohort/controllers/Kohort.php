<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Kohort Module Controller
 * Handles CRUD operations for Kohort Management
 * Role: Admin Pusat Karir, Super Admin
 */
class Kohort extends MX_Controller {

    public function __construct() {
        parent::__construct();
        
        // Check authentication
        if (!$this->session->userdata('logged_in')) {
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
        $data['kohorts'] = $this->kohort_model->get_all();
        
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
            $data = [
                'name' => $this->input->post('name', TRUE),
                'graduation_year' => $this->input->post('graduation_year', TRUE),
                'is_active' => $this->input->post('is_active', 1)
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
            $data = [
                'name' => $this->input->post('name', TRUE),
                'graduation_year' => $this->input->post('graduation_year', TRUE),
                'is_active' => $this->input->post('is_active', 1)
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
     * Toggle active status
     */
    public function toggle_status($id) {
        $kohort = $this->kohort_model->get_by_id($id);
        
        if (!$kohort) {
            $this->session->set_flashdata('error', 'Kohort tidak ditemukan.');
            redirect('kohort');
        }
        
        $new_status = $kohort->is_active ? 0 : 1;
        $result = $this->kohort_model->update($id, ['is_active' => $new_status]);
        
        if ($result) {
            $this->session->set_flashdata('success', 'Status kohort berhasil diubah.');
        } else {
            $this->session->set_flashdata('error', 'Gagal mengubah status kohort.');
        }
        
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
                'graduation_year' => $current_year,
                'is_active' => 1
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

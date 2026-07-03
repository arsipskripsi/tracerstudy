<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Alumni Admin Controller - Manajemen Data Alumni dari Admin Panel
 */
class Alumni extends Admin_Controller {

    public function __construct() {
        parent::__construct();
        $this->auth_lib->requireRole(['super_admin', 'admin_pusat_karir']);
        $this->load->helper('tracer_audit');
        $this->load->model('auth/user_model');
    }

    public function index() {
        $data['page_title'] = 'Data Alumni';
        $data['page_subtitle'] = 'Kelola data alumni';
        
        // Get all alumni with user and prodi info
        $this->db->select('ap.*, u.username, u.email, ps.nama as prodi_nama, ps.kode as prodi_kode');
        $this->db->from('alumni ap');
        $this->db->join('users u', 'ap.user_id = u.id', 'left');
        $this->db->join('program_studi ps', 'ap.prodi_id = ps.id', 'left');
        $this->db->order_by('ap.created_at', 'DESC');
        $data['alumni'] = $this->db->get()->result_array();
        
        $this->load->view('admin/templates/header', $data);
        $this->load->view('admin/alumni/index', $data);
        $this->load->view('admin/templates/footer');
    }
    
    /**
     * Tambah alumni baru
     */
    public function add() {
        $data['page_title'] = 'Tambah Alumni';
        $data['page_subtitle'] = 'Tambah data alumni baru';
        
        if ($this->input->post()) {
            $this->form_validation->set_rules('nim', 'NIM', 'required|is_unique[alumni.nim]');
            $this->form_validation->set_rules('nama_lengkap', 'Nama Lengkap', 'required');
            $this->form_validation->set_rules('prodi_id', 'Program Studi', 'required');
            $this->form_validation->set_rules('tanggal_lulus', 'Tanggal Lulus', 'required');
            
            if ($this->form_validation->run()) {
                $alumni_data = [
                    'nim' => $this->input->post('nim'),
                    'nama_lengkap' => $this->input->post('nama_lengkap'),
                    'prodi_id' => $this->input->post('prodi_id'),
                    'tempat_lahir' => $this->input->post('tempat_lahir'),
                    'tanggal_lahir' => $this->input->post('tanggal_lahir'),
                    'jenis_kelamin' => $this->input->post('jenis_kelamin'),
                    'email_pribadi' => $this->input->post('email_pribadi'),
                    'no_hp' => $this->input->post('no_hp'),
                    'alamat_domisili' => $this->input->post('alamat_domisili'),
                    'tanggal_lulus' => $this->input->post('tanggal_lulus'),
                    'ipk' => $this->input->post('ipk'),
                    'created_at' => date('Y-m-d H:i:s')
                ];
                
                $this->db->insert('alumni', $alumni_data);
                $alumni_id = $this->db->insert_id();
                
                audit_log('create', 'alumni', 'Menambah alumni baru: ' . $alumni_data['nama_lengkap'], $this->session->userdata('user_id'));
                
                $this->session->set_flashdata('message', 'Alumni berhasil ditambahkan');
                $this->session->set_flashdata('message_type', 'success');
                redirect('admin/alumni');
            }
        }
        
        // Get list program studi
        $this->db->select('id, nama, kode');
        $this->db->order_by('nama', 'ASC');
        $data['program_studi'] = $this->db->get('program_studi')->result_array();
        
        $this->load->view('admin/templates/header', $data);
        $this->load->view('admin/alumni/form', $data);
        $this->load->view('admin/templates/footer');
    }
    
    /**
     * Edit alumni
     */
    public function edit($id) {
        $data['page_title'] = 'Edit Alumni';
        $data['page_subtitle'] = 'Ubah data alumni';
        
        $alumni = $this->db->get_where('alumni', ['id' => $id])->row_array();
        
        if (!$alumni) {
            show_404();
        }
        
        if ($this->input->post()) {
            $this->form_validation->set_rules('nim', 'NIM', 'required');
            $this->form_validation->set_rules('nama_lengkap', 'Nama Lengkap', 'required');
            $this->form_validation->set_rules('prodi_id', 'Program Studi', 'required');
            $this->form_validation->set_rules('tanggal_lulus', 'Tanggal Lulus', 'required');
            
            if ($this->form_validation->run()) {
                $update_data = [
                    'nim' => $this->input->post('nim'),
                    'nama_lengkap' => $this->input->post('nama_lengkap'),
                    'prodi_id' => $this->input->post('prodi_id'),
                    'tempat_lahir' => $this->input->post('tempat_lahir'),
                    'tanggal_lahir' => $this->input->post('tanggal_lahir'),
                    'jenis_kelamin' => $this->input->post('jenis_kelamin'),
                    'email_pribadi' => $this->input->post('email_pribadi'),
                    'no_hp' => $this->input->post('no_hp'),
                    'alamat_domisili' => $this->input->post('alamat_domisili'),
                    'tanggal_lulus' => $this->input->post('tanggal_lulus'),
                    'ipk' => $this->input->post('ipk'),
                    'updated_at' => date('Y-m-d H:i:s')
                ];
                
                $this->db->where('id', $id)->update('alumni', $update_data);
                
                audit_log('update', 'alumni', 'Mengedit alumni: ' . $alumni['nama_lengkap'], $this->session->userdata('user_id'));
                
                $this->session->set_flashdata('message', 'Alumni berhasil diupdate');
                $this->session->set_flashdata('message_type', 'success');
                redirect('admin/alumni');
            }
        }
        
        $data['alumni'] = $alumni;
        
        // Get list program studi
        $this->db->select('id, nama, kode');
        $this->db->order_by('nama', 'ASC');
        $data['program_studi'] = $this->db->get('program_studi')->result_array();
        
        $this->load->view('admin/templates/header', $data);
        $this->load->view('admin/alumni/form', $data);
        $this->load->view('admin/templates/footer');
    }
    
    /**
     * Delete alumni
     */
    public function delete($id) {
        $alumni = $this->db->get_where('alumni', ['id' => $id])->row_array();
        
        if ($alumni) {
            $this->db->delete('alumni', ['id' => $id]);
            audit_log('delete', 'alumni', 'Menghapus alumni: ' . $alumni['nama_lengkap'], $this->session->userdata('user_id'));
            
            echo json_encode(['success' => true, 'message' => 'Alumni berhasil dihapus']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Alumni tidak ditemukan']);
        }
    }
}

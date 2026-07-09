<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Kurikulum Controller untuk Admin Prodi
 * Mengelola kurikulum dan CPL program studi sendiri
 */
class Kurikulum extends MY_Prodi_Controller {
    
    public function __construct() {
        parent::__construct();
        $this->load->model('kurikulum/kurikulum_model');
        $this->load->helper(['form', 'url']);
    }
    
    public function index() {
        $data['page_title'] = 'Manajemen Kurikulum';
        $data['page_subtitle'] = 'Kelola kurikulum dan CPL Program Studi';
        
        $prodi_id = $this->prodi_id;
        
        $this->db->select('k.*, 
                          (SELECT COUNT(*) FROM cpl WHERE kurikulum_id = k.id) as jumlah_cpl')
                 ->from('kurikulum k')
                 ->where('k.prodi_id', $prodi_id)
                 ->order_by('k.tahun_mulai', 'DESC');
        $data['kurikulum_list'] = $this->db->get()->result_array();
        
        $this->load->view('prodi/templates/header', $data);
        $this->load->view('prodi/kurikulum/index', $data);
        $this->load->view('prodi/templates/footer');
    }
    
    public function create() {
        $data['page_title'] = 'Tambah Kurikulum';
        $data['page_subtitle'] = 'Buat kurikulum baru';
        
        if ($this->input->post()) {
            // CSRF validation is handled by MY_Controller hooks
            
            $this->form_validation->set_rules('nama_kurikulum', 'Nama Kurikulum', 'required|trim|max_length[100]');
            $this->form_validation->set_rules('tahun_mulai', 'Tahun Mulai', 'required|integer|min_length[4]|max_length[4]');
            $this->form_validation->set_rules('total_sks', 'Total SKS', 'required|integer|min_length[2]|max_length[3]');
            
            if ($this->form_validation->run() == TRUE) {
                $data_insert = [
                    'prodi_id' => $this->prodi_id,
                    'nama_kurikulum' => $this->input->post('nama_kurikulum'),
                    'tahun_mulai' => $this->input->post('tahun_mulai'),
                    'tahun_selesai' => $this->input->post('tahun_selesai') ?: null,
                    'deskripsi' => $this->input->post('deskripsi'),
                    'total_sks' => $this->input->post('total_sks'),
                    'is_active' => 1,
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s')
                ];
                
                $this->db->insert('kurikulum', $data_insert);
                $kurikulum_id = $this->db->insert_id();
                
                // Log activity
                $this->db->insert('activity_logs', [
                    'user_id' => $this->session->userdata('user_id'),
                    'action' => 'create',
                    'module' => 'kurikulum',
                    'table_name' => 'kurikulum',
                    'record_id' => $kurikulum_id,
                    'new_values' => json_encode($data_insert),
                    'ip_address' => $this->input->ip_address(),
                    'user_agent' => $this->agent->agent_string(),
                    'created_at' => date('Y-m-d H:i:s')
                ]);
                
                $this->session->set_flashdata('success', 'Kurikulum berhasil ditambahkan!');
                redirect('prodi/kurikulum');
            }
        }
        
        $this->load->view('prodi/templates/header', $data);
        $this->load->view('prodi/kurikulum/create', $data);
        $this->load->view('prodi/templates/footer');
    }
    
    public function edit($id) {
        $data['page_title'] = 'Edit Kurikulum';
        $data['page_subtitle'] = 'Ubah data kurikulum';
        
        $prodi_id = $this->prodi_id;
        
        $this->db->where('id', $id)->where('prodi_id', $prodi_id);
        $data['kurikulum'] = $this->db->get('kurikulum')->row_array();
        
        if (!$data['kurikulum']) {
            show_error('Kurikulum tidak ditemukan atau tidak ada hak akses.');
            return;
        }
        
        if ($this->input->post()) {
            $this->form_validation->set_rules('nama_kurikulum', 'Nama Kurikulum', 'required|trim|max_length[100]');
            $this->form_validation->set_rules('tahun_mulai', 'Tahun Mulai', 'required|integer|min_length[4]|max_length[4]');
            $this->form_validation->set_rules('total_sks', 'Total SKS', 'required|integer|min_length[2]|max_length[3]');
            
            if ($this->form_validation->run() == TRUE) {
                $data_update = [
                    'nama_kurikulum' => $this->input->post('nama_kurikulum'),
                    'tahun_mulai' => $this->input->post('tahun_mulai'),
                    'tahun_selesai' => $this->input->post('tahun_selesai') ?: null,
                    'deskripsi' => $this->input->post('deskripsi'),
                    'total_sks' => $this->input->post('total_sks'),
                    'is_active' => $this->input->post('is_active') ? 1 : 0,
                    'updated_at' => date('Y-m-d H:i:s')
                ];
                
                // Get old values for audit log
                $old_values = $data['kurikulum'];
                
                $this->db->where('id', $id)->update('kurikulum', $data_update);
                
                // Log activity
                $this->db->insert('activity_logs', [
                    'user_id' => $this->session->userdata('user_id'),
                    'action' => 'update',
                    'module' => 'kurikulum',
                    'table_name' => 'kurikulum',
                    'record_id' => $id,
                    'old_values' => json_encode($old_values),
                    'new_values' => json_encode($data_update),
                    'ip_address' => $this->input->ip_address(),
                    'user_agent' => $this->agent->agent_string(),
                    'created_at' => date('Y-m-d H:i:s')
                ]);
                
                $this->session->set_flashdata('success', 'Kurikulum berhasil diperbarui!');
                redirect('prodi/kurikulum');
            }
        }
        
        $this->load->view('prodi/templates/header', $data);
        $this->load->view('prodi/kurikulum/edit', $data);
        $this->load->view('prodi/templates/footer');
    }
    
    public function delete($id) {
        $prodi_id = $this->prodi_id;
        
        $this->db->where('id', $id)->where('prodi_id', $prodi_id);
        $kurikulum = $this->db->get('kurikulum')->row_array();
        
        if (!$kurikulum) {
            $this->session->set_flashdata('error', 'Kurikulum tidak ditemukan atau tidak ada hak akses.');
            redirect('prodi/kurikulum');
            return;
        }
        
        // Check if has CPL
        $this->db->where('kurikulum_id', $id);
        $cpl_count = $this->db->count_all_results('cpl');
        
        if ($cpl_count > 0) {
            $this->session->set_flashdata('error', 'Tidak dapat menghapus kurikulum yang sudah memiliki CPL.');
            redirect('prodi/kurikulum');
            return;
        }
        
        $this->db->delete('kurikulum', ['id' => $id]);
        
        // Log activity
        $this->db->insert('activity_logs', [
            'user_id' => $this->session->userdata('user_id'),
            'action' => 'delete',
            'module' => 'kurikulum',
            'table_name' => 'kurikulum',
            'record_id' => $id,
            'old_values' => json_encode($kurikulum),
            'ip_address' => $this->input->ip_address(),
            'user_agent' => $this->agent->agent_string(),
            'created_at' => date('Y-m-d H:i:s')
        ]);
        
        $this->session->set_flashdata('success', 'Kurikulum berhasil dihapus!');
        redirect('prodi/kurikulum');
    }
    
    public function cpl($kurikulum_id) {
        $data['page_title'] = 'Capaian Pembelajaran Lulusan (CPL)';
        $data['page_subtitle'] = 'Kelola CPL kurikulum';
        
        $prodi_id = $this->prodi_id;
        
        // Verify kurikulum belongs to this prodi
        $this->db->where('id', $kurikulum_id)->where('prodi_id', $prodi_id);
        $data['kurikulum'] = $this->db->get('kurikulum')->row_array();
        
        if (!$data['kurikulum']) {
            show_error('Kurikulum tidak ditemukan atau tidak ada hak akses.');
            return;
        }
        
        $this->db->select('cpl.*, 
                          (SELECT COUNT(*) FROM cpl_mapping WHERE cpl_id = cpl.id) as mapping_count')
                 ->from('cpl')
                 ->where('kurikulum_id', $kurikulum_id)
                 ->order_by('kode_cpl', 'ASC');
        $data['cpl_list'] = $this->db->get()->result_array();
        
        $this->load->view('prodi/templates/header', $data);
        $this->load->view('prodi/kurikulum/cpl', $data);
        $this->load->view('prodi/templates/footer');
    }
    
    public function add_cpl($kurikulum_id) {
        $prodi_id = $this->prodi_id;
        
        $this->db->where('id', $kurikulum_id)->where('prodi_id', $prodi_id);
        $kurikulum = $this->db->get('kurikulum')->row_array();
        
        if (!$kurikulum) {
            show_error('Kurikulum tidak ditemukan atau tidak ada hak akses.');
            return;
        }
        
        if ($this->input->post()) {
            $this->form_validation->set_rules('kode_cpl', 'Kode CPL', 'required|trim|max_length[20]');
            $this->form_validation->set_rules('jenis', 'Jenis CPL', 'required|in_list[Sikap,Pengetahuan,Keterampilan_Umum,Keterampilan_Khusus]');
            $this->form_validation->set_rules('deskripsi', 'Deskripsi CPL', 'required|trim');
            
            if ($this->form_validation->run() == TRUE) {
                $data_insert = [
                    'kurikulum_id' => $kurikulum_id,
                    'kode_cpl' => $this->input->post('kode_cpl'),
                    'jenis' => $this->input->post('jenis'),
                    'deskripsi' => $this->input->post('deskripsi'),
                    'level' => $this->input->post('level') ?: null,
                    'target_industri' => $this->input->post('target_industri') ?: 4.00,
                    'created_at' => date('Y-m-d H:i:s')
                ];
                
                $this->db->insert('cpl', $data_insert);
                $cpl_id = $this->db->insert_id();
                
                $this->session->set_flashdata('success', 'CPL berhasil ditambahkan!');
                redirect('prodi/kurikulum/cpl/' . $kurikulum_id);
            }
        }
        
        redirect('prodi/kurikulum/cpl/' . $kurikulum_id);
    }
    
    public function delete_cpl($id) {
        $prodi_id = $this->prodi_id;
        
        $this->db->select('kurikulum_id')->from('cpl')->where('id', $id);
        $cpl = $this->db->get()->row_array();
        
        if (!$cpl) {
            $this->session->set_flashdata('error', 'CPL tidak ditemukan.');
            redirect('prodi/kurikulum');
            return;
        }
        
        // Verify kurikulum belongs to this prodi
        $this->db->where('id', $cpl['kurikulum_id'])->where('prodi_id', $prodi_id);
        $kurikulum = $this->db->get('kurikulum')->row_array();
        
        if (!$kurikulum) {
            show_error('Tidak ada hak akses untuk menghapus CPL ini.');
            return;
        }
        
        $this->db->delete('cpl', ['id' => $id]);
        
        $this->session->set_flashdata('success', 'CPL berhasil dihapus!');
        redirect('prodi/kurikulum/cpl/' . $kurikulum['kurikulum_id']);
    }
}

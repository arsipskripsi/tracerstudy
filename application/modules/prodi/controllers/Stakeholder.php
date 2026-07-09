<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Stakeholder Controller untuk Admin Prodi
 * Mengelola survey pengguna lulusan (stakeholder/employer)
 */
class Stakeholder extends MY_Prodi_Controller {
    
    public function __construct() {
        parent::__construct();
        $this->load->helper(['form', 'url']);
    }
    
    public function index() {
        $data['page_title'] = 'Survey Pengguna Lulusan';
        $data['page_subtitle'] = 'Kelola survey stakeholder dan employer';
        
        $prodi_id = $this->prodi_id;
        
        // Get stakeholders linked to alumni from this prodi
        $this->db->select('DISTINCT s.*, 
                          COUNT(ss.id) as total_survey,
                          AVG(sr.overall_score) as avg_rating')
                 ->from('stakeholders s')
                 ->join('stakeholder_surveys ss', 's.id = ss.stakeholder_id')
                 ->join('alumni a', 'ss.alumni_id = a.id')
                 ->join('survey_responses sr', 'ss.response_id = sr.id', 'left')
                 ->where('a.prodi_id', $prodi_id)
                 ->group_by('s.id')
                 ->order_by('s.created_at', 'DESC');
        $data['stakeholders'] = $this->db->get()->result_array();
        
        // Get surveys for stakeholder
        $this->db->select('surveys.*, 
                          (SELECT COUNT(*) FROM stakeholder_surveys WHERE survey_id = surveys.id) as response_count')
                 ->from('surveys')
                 ->where('type', 'stakeholder')
                 ->where('prodi_id', $prodi_id)
                 ->order_by('created_at', 'DESC');
        $data['surveys'] = $this->db->get()->result_array();
        
        $this->load->view('prodi/templates/header', $data);
        $this->load->view('prodi/stakeholder/index', $data);
        $this->load->view('prodi/templates/footer');
    }
    
    public function add() {
        $data['page_title'] = 'Tambah Stakeholder';
        $data['page_subtitle'] = 'Registrasi pengguna lulusan baru';
        
        if ($this->input->post()) {
            $this->form_validation->set_rules('nama_instansi', 'Nama Instansi', 'required|trim|max_length[200]');
            $this->form_validation->set_rules('jenis_instansi', 'Jenis Instansi', 'required|in_list[Pemerintahan,Swasta,BUMN,BUMD,LSM,Internasional,Startup,Wirausaha]');
            
            if ($this->form_validation->run() == TRUE) {
                $data_insert = [
                    'nama_instansi' => $this->input->post('nama_instansi'),
                    'jenis_instansi' => $this->input->post('jenis_instansi'),
                    'bidang_usaha' => $this->input->post('bidang_usaha'),
                    'alamat' => $this->input->post('alamat'),
                    'provinsi' => $this->input->post('provinsi'),
                    'kota' => $this->input->post('kota'),
                    'website' => $this->input->post('website'),
                    'namakontak_pic' => $this->input->post('namakontak_pic'),
                    'jabatan_pic' => $this->input->post('jabatan_pic'),
                    'email_pic' => $this->input->post('email_pic'),
                    'no_hp_pic' => $this->input->post('no_hp_pic'),
                    'is_verified' => 1,
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s')
                ];
                
                $this->db->insert('stakeholders', $data_insert);
                $stakeholder_id = $this->db->insert_id();
                
                $this->session->set_flashdata('success', 'Stakeholder berhasil ditambahkan!');
                redirect('prodi/stakeholder');
            }
        }
        
        $this->load->view('prodi/templates/header', $data);
        $this->load->view('prodi/stakeholder/add', $data);
        $this->load->view('prodi/templates/footer');
    }
    
    public function survey_create() {
        $data['page_title'] = 'Buat Survey Stakeholder';
        $data['page_subtitle'] = 'Survey kepuasan pengguna lulusan';
        
        if ($this->input->post()) {
            $this->form_validation->set_rules('title', 'Judul Survey', 'required|trim|max_length[200]');
            $this->form_validation->set_rules('start_date', 'Tanggal Mulai', 'required');
            $this->form_validation->set_rules('end_date', 'Tanggal Selesai', 'required');
            
            if ($this->form_validation->run() == TRUE) {
                $data_insert = [
                    'title' => $this->input->post('title'),
                    'description' => $this->input->post('description'),
                    'type' => 'stakeholder',
                    'prodi_id' => $this->prodi_id,
                    'tahun_periode' => date('Y'),
                    'start_date' => $this->input->post('start_date') . ' 00:00:00',
                    'end_date' => $this->input->post('end_date') . ' 23:59:59',
                    'is_active' => 1,
                    'require_auth' => 1,
                    'show_progress_bar' => 1,
                    'created_by' => $this->session->userdata('user_id'),
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s')
                ];
                
                $this->db->insert('surveys', $data_insert);
                $survey_id = $this->db->insert_id();
                
                // Add default stakeholder questions
                $questions = [
                    [
                        'question_text' => 'Bagaimana penilaian Anda terhadap kinerja alumni di tempat kerja?',
                        'type' => 'rating',
                        'is_required' => 1,
                        'order' => 1
                    ],
                    [
                        'question_text' => 'Seberapa sesuai kompetensi alumni dengan kebutuhan pekerjaan?',
                        'type' => 'rating',
                        'is_required' => 1,
                        'order' => 2
                    ],
                    [
                        'question_text' => 'Kompetensi apa yang perlu ditingkatkan oleh program studi?',
                        'type' => 'long_answer',
                        'is_required' => 0,
                        'order' => 3
                    ]
                ];
                
                foreach ($questions as $q) {
                    $q['survey_id'] = $survey_id;
                    $this->db->insert('survey_questions', $q);
                }
                
                $this->session->set_flashdata('success', 'Survey stakeholder berhasil dibuat!');
                redirect('prodi/stakeholder');
            }
        }
        
        $this->load->view('prodi/templates/header', $data);
        $this->load->view('prodi/stakeholder/survey_create', $data);
        $this->load->view('prodi/templates/footer');
    }
    
    public function gap_analysis() {
        $data['page_title'] = 'Gap Analysis CPL';
        $data['page_subtitle'] = 'Analisis kesenjangan CPL dengan kebutuhan industri';
        
        $prodi_id = $this->prodi_id;
        
        // Get CPL with ratings from alumni and stakeholder
        $this->db->select('cpl.kode_cpl, cpl.deskripsi, cpl.jenis, cpl.target_industri,
                          AVG(CASE WHEN sr.source = \'alumni\' THEN ra.rating END) as alumni_rating,
                          AVG(CASE WHEN sr.source = \'stakeholder\' THEN rs.rating END) as stakeholder_rating')
                 ->from('cpl')
                 ->join('kurikulum k', 'cpl.kurikulum_id = k.id')
                 ->join('survey_answers sa_alumni', 'cpl.id = sa_alumni.cpl_id', 'left')
                 ->join('survey_responses sr', 'sa_alumni.response_id = sr.id', 'left')
                 ->where('k.prodi_id', $prodi_id)
                 ->group_by('cpl.id')
                 ->order_by('cpl.jenis, cpl.kode_cpl');
        $data['gap_data'] = $this->db->get()->result_array();
        
        $this->load->view('prodi/templates/header', $data);
        $this->load->view('prodi/stakeholder/gap_analysis', $data);
        $this->load->view('prodi/templates/footer');
    }
}

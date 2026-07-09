<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Survey Builder Controller untuk Admin Prodi
 * CRUD survey untuk prodi sendiri dengan pertanyaan inti Belmawa
 */
class Survey_builder extends MY_Prodi_Controller {
    
    private $core_questions = [
        ['question_text' => 'Apakah Anda puas dengan kualitas pembelajaran di program studi Anda?', 'type' => 'rating', 'is_core' => 1],
        ['question_text' => 'Seberapa baik dosen dalam menjelaskan materi kuliah?', 'type' => 'rating', 'is_core' => 1],
        ['question_text' => 'Apakah fasilitas laboratorium memadai untuk mendukung pembelajaran?', 'type' => 'multiple_choice', 'options' => 'Sangat Memadai|Memadai|Cukup|Kurang|Sangat Kurang', 'is_core' => 1],
        ['question_text' => 'Bagaimana ketersediaan literatur di perpustakaan?', 'type' => 'multiple_choice', 'options' => 'Sangat Lengkap|Lengkap|Cukup|Kurang|Sangat Kurang', 'is_core' => 1],
        ['question_text' => 'Apakah kurikulum sesuai dengan kebutuhan industri saat ini?', 'type' => 'rating', 'is_core' => 1],
        ['question_text' => 'Seberapa efektif metode evaluasi pembelajaran yang diterapkan?', 'type' => 'rating', 'is_core' => 1],
        ['question_text' => 'Apakah Anda mendapatkan bimbingan yang memadai dari dosen pembimbing?', 'type' => 'rating', 'is_core' => 1],
        ['question_text' => 'Bagaimana kualitas layanan administrasi akademik?', 'type' => 'rating', 'is_core' => 1],
        ['question_text' => 'Apakah terdapat kesempatan magang atau praktik kerja lapangan?', 'type' => 'multiple_choice', 'options' => 'Ya, sangat banyak|Ya, cukup|Tidak ada', 'is_core' => 1],
        ['question_text' => 'Seberapa besar kontribusi kegiatan organisasi terhadap pengembangan soft skill Anda?', 'type' => 'rating', 'is_core' => 1],
        ['question_text' => 'Apakah lingkungan kampus mendukung proses belajar?', 'type' => 'rating', 'is_core' => 1],
        ['question_text' => 'Bagaimana kualitas koneksi internet di kampus?', 'type' => 'multiple_choice', 'options' => 'Sangat Baik|Baik|Cukup|Kurang|Sangat Kurang', 'is_core' => 1],
        ['question_text' => 'Apakah Anda merasa siap bekerja setelah lulus?', 'type' => 'rating', 'is_core' => 1],
        ['question_text' => 'Seberapa relevan tugas akhir/skripsi dengan bidang minat Anda?', 'type' => 'rating', 'is_core' => 1],
        ['question_text' => 'Apakah informasi akademik disampaikan dengan jelas dan tepat waktu?', 'type' => 'rating', 'is_core' => 1],
        ['question_text' => 'Bagaimana penilaian Anda terhadap etika dan profesionalisme dosen?', 'type' => 'rating', 'is_core' => 1],
        ['question_text' => 'Apakah terdapat dukungan karir dari kampus?', 'type' => 'multiple_choice', 'options' => 'Sangat Baik|Baik|Cukup|Kurang|Tidak Ada', 'is_core' => 1],
        ['question_text' => 'Seberapa puas Anda dengan keseluruhan pengalaman kuliah?', 'type' => 'rating', 'is_core' => 1],
        ['question_text' => 'Apakah Anda akan merekomendasikan program studi ini kepada calon mahasiswa?', 'type' => 'multiple_choice', 'options' => 'Sangat Merekomendasikan|Merekomendasikan|Ragu-ragu|Tidak Merekomendasikan', 'is_core' => 1],
        ['question_text' => 'Saran perbaikan untuk program studi:', 'type' => 'long_answer', 'is_core' => 1]
    ];
    
    public function __construct() {
        parent::__construct();
        $this->load->helper(['form', 'url']);
    }
    
    public function index() {
        $data['page_title'] = 'Survey Builder';
        $data['page_subtitle'] = 'Kelola survei Program Studi';
        
        $prodi_id = $this->prodi_id;
        
        $this->db->select('s.*, u.username as creator_name,
                          (SELECT COUNT(*) FROM survey_questions WHERE survey_id = s.id) as question_count,
                          (SELECT COUNT(*) FROM survey_responses WHERE survey_id = s.id) as response_count')
                 ->from('surveys s')
                 ->join('users u', 's.created_by = u.id', 'left')
                 ->where('s.prodi_id', $prodi_id)
                 ->order_by('s.created_at', 'DESC');
        $data['surveys'] = $this->db->get()->result_array();
        
        $this->load->view('prodi/templates/header', $data);
        $this->load->view('prodi/survey_builder/index', $data);
        $this->load->view('prodi/templates/footer');
    }
    
    public function create() {
        $data['page_title'] = 'Buat Survey Baru';
        $data['page_subtitle'] = 'Tambah survey dengan pertanyaan inti Belmawa';
        $data['core_questions'] = $this->core_questions;
        
        if ($this->input->post()) {
            $this->form_validation->set_rules('title', 'Judul Survey', 'required|trim|max_length[200]');
            $this->form_validation->set_rules('start_date', 'Tanggal Mulai', 'required');
            $this->form_validation->set_rules('end_date', 'Tanggal Selesai', 'required');
            
            if ($this->form_validation->run() == TRUE) {
                $data_insert = [
                    'title' => $this->input->post('title'),
                    'description' => $this->input->post('description'),
                    'type' => 'tracer_study',
                    'prodi_id' => $this->prodi_id,
                    'tahun_periode' => date('Y'),
                    'start_date' => $this->input->post('start_date') . ' 00:00:00',
                    'end_date' => $this->input->post('end_date') . ' 23:59:59',
                    'status' => 'draft',
                    'is_active' => 0,
                    'require_auth' => 1,
                    'show_progress_bar' => 1,
                    'created_by' => $this->session->userdata('user_id'),
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s')
                ];
                
                $this->db->insert('surveys', $data_insert);
                $survey_id = $this->db->insert_id();
                
                // Add core questions if requested
                if ($this->input->post('add_core') === '1') {
                    foreach ($this->core_questions as $index => $q) {
                        $question_data = [
                            'survey_id' => $survey_id,
                            'question_text' => $q['question_text'],
                            'type' => $q['type'],
                            'options' => isset($q['options']) ? $q['options'] : null,
                            'is_required' => 1,
                            'is_core' => 1,
                            'is_belma_inti' => 1,
                            'order' => $index + 1
                        ];
                        $this->db->insert('survey_questions', $question_data);
                    }
                }
                
                $this->session->set_flashdata('success', 'Survey berhasil dibuat! Silakan tambahkan pertanyaan kustom jika diperlukan.');
                redirect('prodi/survey_builder/questions/' . $survey_id);
            }
        }
        
        $this->load->view('prodi/templates/header', $data);
        $this->load->view('prodi/survey_builder/create', $data);
        $this->load->view('prodi/templates/footer');
    }
    
    public function edit($id) {
        $data['page_title'] = 'Edit Survey';
        $data['page_subtitle'] = 'Ubah data survey';
        
        $prodi_id = $this->prodi_id;
        
        $this->db->where('id', $id)->where('prodi_id', $prodi_id);
        $data['survey'] = $this->db->get('surveys')->row_array();
        
        if (!$data['survey']) {
            show_error('Survey tidak ditemukan atau tidak ada hak akses.');
            return;
        }
        
        if ($this->input->post()) {
            $data_update = [
                'title' => $this->input->post('title'),
                'description' => $this->input->post('description'),
                'start_date' => $this->input->post('start_date') . ' 00:00:00',
                'end_date' => $this->input->post('end_date') . ' 23:59:59',
                'thank_you_message' => $this->input->post('thank_you_message'),
                'allow_multiple_responses' => $this->input->post('allow_multiple_responses') ? 1 : 0,
                'updated_at' => date('Y-m-d H:i:s')
            ];
            
            $this->db->where('id', $id)->update('surveys', $data_update);
            
            $this->session->set_flashdata('success', 'Survey berhasil diperbarui!');
            redirect('prodi/survey_builder');
        }
        
        $this->load->view('prodi/templates/header', $data);
        $this->load->view('prodi/survey_builder/edit', $data);
        $this->load->view('prodi/templates/footer');
    }
    
    public function delete($id) {
        $prodi_id = $this->prodi_id;
        
        $this->db->where('id', $id)->where('prodi_id', $prodi_id);
        $survey = $this->db->get('surveys')->row_array();
        
        if (!$survey) {
            $this->session->set_flashdata('error', 'Survey tidak ditemukan atau tidak ada hak akses.');
            redirect('prodi/survey_builder');
            return;
        }
        
        // Check if has responses
        $this->db->where('survey_id', $id);
        $response_count = $this->db->count_all_results('survey_responses');
        
        if ($response_count > 0) {
            $this->session->set_flashdata('error', 'Tidak dapat menghapus survey yang sudah memiliki responden.');
            redirect('prodi/survey_builder');
            return;
        }
        
        $this->db->delete('surveys', ['id' => $id]);
        
        $this->session->set_flashdata('success', 'Survey berhasil dihapus!');
        redirect('prodi/survey_builder');
    }
    
    public function questions($survey_id) {
        $data['page_title'] = 'Kelola Pertanyaan';
        $data['page_subtitle'] = 'Tambah/hapus pertanyaan survey';
        
        $prodi_id = $this->prodi_id;
        
        $this->db->where('id', $survey_id)->where('prodi_id', $prodi_id);
        $data['survey'] = $this->db->get('surveys')->row_array();
        
        if (!$data['survey']) {
            show_error('Survey tidak ditemukan atau tidak ada hak akses.');
            return;
        }
        
        $this->db->select('*')->from('survey_questions')
                 ->where('survey_id', $survey_id)
                 ->order_by('order', 'ASC');
        $data['questions'] = $this->db->get()->result_array();
        
        $this->load->view('prodi/templates/header', $data);
        $this->load->view('prodi/survey_builder/questions', $data);
        $this->load->view('prodi/templates/footer');
    }
    
    public function add_question($survey_id) {
        $prodi_id = $this->prodi_id;
        
        $this->db->where('id', $survey_id)->where('prodi_id', $prodi_id);
        $survey = $this->db->get('surveys')->row_array();
        
        if (!$survey) {
            show_error('Survey tidak ditemukan atau tidak ada hak akses.');
            return;
        }
        
        if ($this->input->post()) {
            $this->form_validation->set_rules('question_text', 'Pertanyaan', 'required|trim');
            $this->form_validation->set_rules('type', 'Tipe Pertanyaan', 'required|in_list[short_answer,long_answer,multiple_choice,dropdown,checkbox,rating,date,number]');
            
            if ($this->form_validation->run() == TRUE) {
                // Get max order
                $this->db->select_max('order')->where('survey_id', $survey_id);
                $max_order = $this->db->get('survey_questions')->row()->order ?: 0;
                
                $data_insert = [
                    'survey_id' => $survey_id,
                    'question_text' => $this->input->post('question_text'),
                    'type' => $this->input->post('type'),
                    'options' => $this->input->post('options') ?: null,
                    'is_required' => $this->input->post('is_required') ? 1 : 0,
                    'is_core' => 0,
                    'is_belma_inti' => 0,
                    'order' => $max_order + 1
                ];
                
                $this->db->insert('survey_questions', $data_insert);
                
                $this->session->set_flashdata('success', 'Pertanyaan berhasil ditambahkan!');
                redirect('prodi/survey_builder/questions/' . $survey_id);
            }
        }
        
        redirect('prodi/survey_builder/questions/' . $survey_id);
    }
    
    public function delete_question($id) {
        $this->db->select('survey_id, is_belma_inti')->from('survey_questions')->where('id', $id);
        $question = $this->db->get()->row_array();
        
        if (!$question) {
            $this->session->set_flashdata('error', 'Pertanyaan tidak ditemukan.');
            redirect('prodi/survey_builder');
            return;
        }
        
        // Cannot delete core questions (Belmawa)
        if ($question['is_belma_inti']) {
            $this->session->set_flashdata('error', 'Pertanyaan inti Belmawa tidak dapat dihapus.');
            redirect('prodi/survey_builder/questions/' . $question['survey_id']);
            return;
        }
        
        $this->db->delete('survey_questions', ['id' => $id]);
        
        $this->session->set_flashdata('success', 'Pertanyaan berhasil dihapus!');
        redirect('prodi/survey_builder/questions/' . $question['survey_id']);
    }
    
    public function toggle_status($id) {
        $prodi_id = $this->prodi_id;
        
        $this->db->where('id', $id)->where('prodi_id', $prodi_id);
        $survey = $this->db->get('surveys')->row_array();
        
        if (!$survey) {
            $this->session->set_flashdata('error', 'Survey tidak ditemukan.');
            redirect('prodi/survey_builder');
            return;
        }
        
        $new_status = $survey['is_active'] ? 0 : 1;
        $this->db->update('surveys', ['is_active' => $new_status], ['id' => $id]);
        
        $this->session->set_flashdata('success', 'Status survey berhasil diubah!');
        redirect('prodi/survey_builder');
    }
}

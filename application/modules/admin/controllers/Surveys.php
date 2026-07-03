<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Surveys Admin Controller - Manajemen Survei dari Admin Panel
 */
class Surveys extends Admin_Controller {

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
        $this->auth_lib->requireRole(['super_admin', 'admin_pusat_karir']);
        $this->load->helper('tracer_audit');
        $this->load->model('survey_model');
        $this->load->helper(['form', 'url']);
    }

    public function index() {
        $data['page_title'] = 'Manajemen Survei';
        $data['page_subtitle'] = 'Kelola survei dan kuesioner';
        
        $this->db->select('s.*, u.username as creator');
        $this->db->from('surveys s');
        $this->db->join('users u', 's.created_by = u.id', 'left');
        $this->db->order_by('s.created_at', 'DESC');
        $data['surveys'] = $this->db->get()->result_array();
        
        $this->load->view('admin/templates/header', $data);
        $this->load->view('admin/surveys/index', $data);
        $this->load->view('admin/templates/footer');
    }

    /**
     * Tampilkan form tambah survey baru
     */
    public function create() {
        $data['page_title'] = 'Tambah Survei Baru';
        $data['page_subtitle'] = 'Buat survei baru dengan pertanyaan inti Belmawa';
        $data['core_questions'] = $this->core_questions;
        
        $this->load->view('admin/templates/header', $data);
        $this->load->view('admin/surveys/create', $data);
        $this->load->view('admin/templates/footer');
    }

    /**
     * Proses insert survey baru
     */
    public function store() {
        $this->form_validation->set_rules('title', 'Judul Survey', 'required|trim|max_length[255]');
        $this->form_validation->set_rules('description', 'Deskripsi', 'trim');

        if ($this->form_validation->run() == FALSE) {
            $data['page_title'] = 'Tambah Survei Baru';
            $data['page_subtitle'] = 'Buat survei baru dengan pertanyaan inti Belmawa';
            $data['core_questions'] = $this->core_questions;
            
            $this->load->view('admin/templates/header', $data);
            $this->load->view('admin/surveys/create', $data);
            $this->load->view('admin/templates/footer');
        } else {
            $data = [
                'title' => $this->input->post('title'),
                'description' => $this->input->post('description'),
                'status' => 'draft',
                'created_by' => $this->session->userdata('user_id'),
                'created_at' => date('Y-m-d H:i:s')
            ];

            $survey_id = $this->survey_model->insert($data);

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
                        'order' => $index + 1
                    ];
                    $this->survey_model->insert_question($question_data);
                }
            }

            // Audit log
            log_tracer_audit(
                'surveys',
                $survey_id,
                'create',
                'Survey created: ' . $data['title'],
                $this->session->userdata('user_id')
            );

            $this->session->set_flashdata('success', 'Survey berhasil dibuat!');
            redirect('admin/surveys');
        }
    }

    /**
     * Tampilkan form edit survey
     */
    public function edit($id) {
        $data['page_title'] = 'Edit Survei';
        $data['page_subtitle'] = 'Ubah informasi survei';
        
        $data['survey'] = $this->survey_model->get_by_id($id);
        
        if (!$data['survey']) {
            show_404();
        }

        if ($data['survey']->status === 'published') {
            $this->session->set_flashdata('error', 'Survey yang sudah dipublikasikan tidak dapat diubah strukturnya.');
            redirect('admin/surveys');
        }

        $data['questions'] = $this->survey_model->get_questions($id);
        
        $this->load->view('admin/templates/header', $data);
        $this->load->view('admin/surveys/edit', $data);
        $this->load->view('admin/templates/footer');
    }

    /**
     * Proses update survey
     */
    public function update($id) {
        $survey = $this->survey_model->get_by_id($id);
        
        if (!$survey || $survey->status === 'published') {
            $this->session->set_flashdata('error', 'Survey tidak ditemukan atau sudah dipublikasikan.');
            redirect('admin/surveys');
        }

        $this->form_validation->set_rules('title', 'Judul Survey', 'required|trim|max_length[255]');
        $this->form_validation->set_rules('description', 'Deskripsi', 'trim');

        if ($this->form_validation->run() == FALSE) {
            $this->session->set_flashdata('error', validation_errors());
            redirect('admin/surveys/edit/' . $id);
        } else {
            $data = [
                'title' => $this->input->post('title'),
                'description' => $this->input->post('description'),
                'updated_at' => date('Y-m-d H:i:s')
            ];

            $this->survey_model->update($id, $data);

            // Audit log
            log_tracer_audit(
                'surveys',
                $id,
                'update',
                'Survey updated: ' . $data['title'],
                $this->session->userdata('user_id')
            );

            $this->session->set_flashdata('success', 'Survey berhasil diperbarui!');
            redirect('admin/surveys');
        }
    }

    /**
     * Hapus survey
     */
    public function delete($id) {
        $survey = $this->survey_model->get_by_id($id);
        
        if (!$survey) {
            show_404();
        }

        if ($survey->status === 'published') {
            $this->session->set_flashdata('error', 'Survey yang sudah dipublikasikan tidak dapat dihapus.');
            redirect('admin/surveys');
        }

        $survey_title = $survey->title;
        $this->survey_model->delete($id);

        // Audit log
        log_tracer_audit(
            'surveys',
            $id,
            'delete',
            'Survey deleted: ' . $survey_title,
            $this->session->userdata('user_id')
        );

        $this->session->set_flashdata('success', 'Survey berhasil dihapus!');
        redirect('admin/surveys');
    }

    /**
     * Publish survey
     */
    public function publish($id) {
        $survey = $this->survey_model->get_by_id($id);
        
        if (!$survey || $survey->status === 'published') {
            $this->output->set_status_header(403);
            echo json_encode(['success' => false, 'message' => 'Survey tidak valid untuk dipublikasikan.']);
            return;
        }

        // BR-SUR-002: Min 20 pertanyaan inti untuk publish
        $core_count = $this->survey_model->count_core_questions($id);
        
        if ($core_count < 20) {
            $this->output->set_status_header(400);
            echo json_encode([
                'success' => false, 
                'message' => "Jumlah pertanyaan inti harus minimal 20. Saat ini: {$core_count}"
            ]);
            return;
        }

        $data = [
            'status' => 'published',
            'published_at' => date('Y-m-d H:i:s'),
            'published_by' => $this->session->userdata('user_id')
        ];

        $this->survey_model->update($id, $data);

        // Audit log
        log_tracer_audit(
            'surveys',
            $id,
            'publish',
            'Survey published: ' . $survey->title,
            $this->session->userdata('user_id')
        );

        echo json_encode(['success' => true, 'message' => 'Survey berhasil dipublikasikan!']);
    }
}

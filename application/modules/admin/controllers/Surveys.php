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
                        'question_type' => $q['type'],
                        'options' => isset($q['options']) ? json_encode(explode('|', $q['options'])) : null,
                        'is_belma_inti' => 1,
                        'order' => $index + 1
                    ];
                    $this->survey_model->insert_question($question_data);
                }
            }

            // Audit log
            audit_log(
                'create',
                'surveys',
                'Survey created: ' . $data['title'],
                $this->session->userdata('user_id'),
                null,
                $data
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
            audit_log(
                'update',
                'surveys',
                'Survey updated: ' . $data['title'],
                $this->session->userdata('user_id'),
                null,
                $data
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
        audit_log(
            'delete',
            'surveys',
            'Survey deleted: ' . $survey_title,
            $this->session->userdata('user_id'),
            null,
            ['id' => $id, 'title' => $survey_title]
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
        audit_log(
            'publish',
            'surveys',
            'Survey published: ' . $survey->title,
            $this->session->userdata('user_id'),
            null,
            ['id' => $id, 'title' => $survey->title, 'status' => 'published']
        );

        echo json_encode(['success' => true, 'message' => 'Survey berhasil dipublikasikan!']);
    }

    /**
     * Get single question data via AJAX
     */
    public function get_question($question_id) {
        $this->load->model('survey_question_model');
        $question = $this->survey_question_model->get_by_id($question_id);
        
        if (!$question) {
            $this->output->set_status_header(404);
            echo json_encode(['success' => false, 'message' => 'Pertanyaan tidak ditemukan.']);
            return;
        }

        echo json_encode([
            'success' => true,
            'question' => $question
        ]);
    }

    /**
     * Store new question
     */
    public function store_question($survey_id) {
        $survey = $this->survey_model->get_by_id($survey_id);
        
        if (!$survey || $survey->status === 'published') {
            $this->output->set_status_header(403);
            echo json_encode(['success' => false, 'message' => 'Survey sudah dipublikasikan.']);
            return;
        }

        $this->form_validation->set_rules('question_text', 'Pertanyaan', 'required|trim');
        $this->form_validation->set_rules('question_type', 'Tipe Pertanyaan', 'required|in_list[text,textarea,number,date,radio,checkbox,dropdown,matrix,file,scale_likert]');
        $this->form_validation->set_rules('is_required', 'Wajib Diisi', 'numeric|in_list[0,1]');

        if ($this->form_validation->run() == FALSE) {
            $this->output->set_status_header(400);
            echo json_encode(['success' => false, 'message' => validation_errors()]);
            return;
        }

        $question_type = $this->input->post('question_type');
        $options = null;
        
        if (in_array($question_type, ['radio', 'checkbox', 'dropdown'])) {
            $options_str = $this->input->post('options');
            if (empty($options_str)) {
                $this->output->set_status_header(400);
                echo json_encode(['success' => false, 'message' => 'Opsi harus diisi untuk tipe pertanyaan ini.']);
                return;
            }
            // Convert newline-separated options to JSON array
            $options = json_encode(array_map('trim', explode("\n", $options_str)));
        }

        // Get max order
        $max_order = $this->survey_model->get_max_order($survey_id);
        
        $data = [
            'survey_id' => $survey_id,
            'question_text' => $this->input->post('question_text'),
            'question_type' => $question_type,
            'options' => $options,
            'help_text' => $this->input->post('help_text') ?? null,
            'placeholder' => $this->input->post('placeholder') ?? null,
            'is_required' => $this->input->post('is_required') ?? 0,
            'is_belma_inti' => 0,
            'order' => $max_order + 1
        ];

        $question_id = $this->survey_model->insert_question($data);
        
        echo json_encode([
            'success' => true, 
            'message' => 'Pertanyaan berhasil ditambahkan!',
            'question_id' => $question_id
        ]);
    }

    /**
     * Update existing question
     */
    public function update_question($survey_id, $question_id) {
        $survey = $this->survey_model->get_by_id($survey_id);
        
        if (!$survey || $survey->status === 'published') {
            $this->output->set_status_header(403);
            echo json_encode(['success' => false, 'message' => 'Survey sudah dipublikasikan.']);
            return;
        }

        $question = $this->survey_model->get_question_by_id($question_id);
        
        if (!$question) {
            $this->output->set_status_header(404);
            echo json_encode(['success' => false, 'message' => 'Pertanyaan tidak ditemukan.']);
            return;
        }

        // BR-SUR-001: Pertanyaan inti tidak dapat diubah
        if ($question->is_belma_inti == 1) {
            $this->output->set_status_header(403);
            echo json_encode(['success' => false, 'message' => 'Pertanyaan inti tidak dapat diubah.']);
            return;
        }

        $this->form_validation->set_rules('question_text', 'Pertanyaan', 'required|trim');
        $this->form_validation->set_rules('question_type', 'Tipe Pertanyaan', 'required|in_list[text,textarea,number,date,radio,checkbox,dropdown,matrix,file,scale_likert]');
        $this->form_validation->set_rules('is_required', 'Wajib Diisi', 'numeric|in_list[0,1]');

        if ($this->form_validation->run() == FALSE) {
            $this->output->set_status_header(400);
            echo json_encode(['success' => false, 'message' => validation_errors()]);
            return;
        }

        $question_type = $this->input->post('question_type');
        $options = null;
        
        if (in_array($question_type, ['radio', 'checkbox', 'dropdown'])) {
            $options_str = $this->input->post('options');
            if (empty($options_str)) {
                $this->output->set_status_header(400);
                echo json_encode(['success' => false, 'message' => 'Opsi harus diisi.']);
                return;
            }
            $options = json_encode(array_map('trim', explode("\n", $options_str)));
        }

        $data = [
            'question_text' => $this->input->post('question_text'),
            'question_type' => $question_type,
            'options' => $options,
            'help_text' => $this->input->post('help_text') ?? null,
            'placeholder' => $this->input->post('placeholder') ?? null,
            'is_required' => $this->input->post('is_required') ?? 0,
            'updated_at' => date('Y-m-d H:i:s')
        ];

        $this->survey_model->update_question($question_id, $data);
        
        echo json_encode(['success' => true, 'message' => 'Pertanyaan berhasil diperbarui!']);
    }

    /**
     * Delete question
     */
    public function delete_question($survey_id, $question_id) {
        $survey = $this->survey_model->get_by_id($survey_id);
        
        if (!$survey || $survey->status === 'published') {
            $this->output->set_status_header(403);
            echo json_encode(['success' => false, 'message' => 'Survey sudah dipublikasikan.']);
            return;
        }

        $question = $this->survey_model->get_question_by_id($question_id);
        
        if (!$question) {
            $this->output->set_status_header(404);
            echo json_encode(['success' => false, 'message' => 'Pertanyaan tidak ditemukan.']);
            return;
        }

        // BR-SUR-001: Pertanyaan inti tidak dapat dihapus
        if ($question->is_belma_inti == 1) {
            $this->output->set_status_header(403);
            echo json_encode(['success' => false, 'message' => 'Pertanyaan inti tidak dapat dihapus.']);
            return;
        }

        $this->survey_model->delete_question($question_id);
        
        // Reorder remaining questions
        $this->survey_model->reorder_after_delete($survey_id, $question->order);
        
        echo json_encode(['success' => true, 'message' => 'Pertanyaan berhasil dihapus!']);
    }

    /**
     * Reorder questions
     */
    public function reorder_question() {
        if (!$this->input->is_ajax_request()) {
            show_error('Unauthorized access', 403);
            return;
        }

        $survey_id = $this->input->post('survey_id');
        $orders = $this->input->post('orders'); // Array of [question_id => new_order]

        $survey = $this->survey_model->get_by_id($survey_id);
        
        if (!$survey || $survey->status === 'published') {
            $this->output->set_status_header(403);
            echo json_encode(['success' => false, 'message' => 'Survey sudah dipublikasikan.']);
            return;
        }

        if (empty($orders)) {
            $this->output->set_status_header(400);
            echo json_encode(['success' => false, 'message' => 'Data urutan tidak valid.']);
            return;
        }

        foreach ($orders as $question_id => $order) {
            $this->survey_model->update_question_order($question_id, $order);
        }

        echo json_encode(['success' => true, 'message' => 'Urutan pertanyaan berhasil diperbarui!']);
    }
}

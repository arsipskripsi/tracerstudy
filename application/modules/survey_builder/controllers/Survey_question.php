<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Survey Question Controller
 * Handles question management within surveys
 */
class Survey_question extends MY_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model(['survey_model', 'survey_question_model']);
        $this->load->helper(['form', 'url']);
        if (!$this->session->userdata('logged_in')) {
            redirect('auth/login');
        }
    }

    public function index($survey_id) {
        $survey = $this->survey_model->get_by_id($survey_id);
        
        if (!$survey) {
            show_404();
        }

        $data['survey'] = $survey;
        $data['questions'] = $this->survey_question_model->get_by_survey($survey_id);
        $this->load->view('survey_builder/questions', $data);
    }

    public function create($survey_id) {
        $survey = $this->survey_model->get_by_id($survey_id);
        
        if (!$survey || $survey->status === 'published') {
            $this->session->set_flashdata('error', 'Survey tidak ditemukan atau sudah dipublikasikan.');
            redirect('admin/surveys/edit/' . $survey_id);
        }

        $data['survey'] = $survey;
        $data['question_types'] = [
            'text' => 'Jawaban Singkat (Text)',
            'textarea' => 'Jawaban Panjang (Textarea)',
            'number' => 'Angka',
            'date' => 'Tanggal',
            'radio' => 'Pilihan Ganda (Radio)',
            'checkbox' => 'Checkbox',
            'dropdown' => 'Dropdown',
            'matrix' => 'Matriks',
            'file' => 'Upload File',
            'scale_likert' => 'Skala Likert'
        ];
        
        $this->load->view('survey_builder/question_form', $data);
    }

    public function store($survey_id) {
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
        $max_order = $this->survey_question_model->get_max_order($survey_id);
        
        $data = [
            'survey_id' => $survey_id,
            'question_text' => $this->input->post('question_text'),
            'question_type' => $question_type,
            'options' => $options,
            'is_required' => $this->input->post('is_required') ?? 0,
            'is_belma_inti' => 0,
            'order' => $max_order + 1
        ];

        $question_id = $this->survey_question_model->insert($data);
        
        echo json_encode([
            'success' => true, 
            'message' => 'Pertanyaan berhasil ditambahkan!',
            'question_id' => $question_id
        ]);
    }

    public function edit($survey_id, $question_id) {
        $survey = $this->survey_model->get_by_id($survey_id);
        
        if (!$survey || $survey->status === 'published') {
            $this->session->set_flashdata('error', 'Survey tidak ditemukan atau sudah dipublikasikan.');
            redirect('admin/surveys/edit/' . $survey_id);
        }

        $question = $this->survey_question_model->get_by_id($question_id);
        
        if (!$question) {
            show_404();
        }

        // BR-SUR-001: Pertanyaan inti tidak dapat diubah
        if ($question->is_belma_inti == 1) {
            $this->session->set_flashdata('error', 'Pertanyaan inti tidak dapat diubah oleh Admin Prodi.');
            redirect('admin/surveys/edit/' . $survey_id);
        }

        $data['survey'] = $survey;
        $data['question'] = $question;
        $data['question_types'] = [
            'text' => 'Jawaban Singkat (Text)',
            'textarea' => 'Jawaban Panjang (Textarea)',
            'number' => 'Angka',
            'date' => 'Tanggal',
            'radio' => 'Pilihan Ganda (Radio)',
            'checkbox' => 'Checkbox',
            'dropdown' => 'Dropdown',
            'matrix' => 'Matriks',
            'file' => 'Upload File',
            'scale_likert' => 'Skala Likert'
        ];
        
        $this->load->view('survey_builder/question_form', $data);
    }

    public function update($survey_id, $question_id) {
        $survey = $this->survey_model->get_by_id($survey_id);
        
        if (!$survey || $survey->status === 'published') {
            $this->output->set_status_header(403);
            echo json_encode(['success' => false, 'message' => 'Survey sudah dipublikasikan.']);
            return;
        }

        $question = $this->survey_question_model->get_by_id($question_id);
        
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
            'is_required' => $this->input->post('is_required') ?? 0,
            'updated_at' => date('Y-m-d H:i:s')
        ];

        $this->survey_question_model->update($question_id, $data);
        
        echo json_encode(['success' => true, 'message' => 'Pertanyaan berhasil diperbarui!']);
    }

    public function delete($survey_id, $question_id) {
        $survey = $this->survey_model->get_by_id($survey_id);
        
        if (!$survey || $survey->status === 'published') {
            $this->output->set_status_header(403);
            echo json_encode(['success' => false, 'message' => 'Survey sudah dipublikasikan.']);
            return;
        }

        $question = $this->survey_question_model->get_by_id($question_id);
        
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

        $this->survey_question_model->delete($question_id);
        
        // Reorder remaining questions
        $this->survey_question_model->reorder_after_delete($survey_id, $question->order);
        
        echo json_encode(['success' => true, 'message' => 'Pertanyaan berhasil dihapus!']);
    }

    public function reorder() {
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
            $this->survey_question_model->update_order($question_id, $order);
        }

        echo json_encode(['success' => true, 'message' => 'Urutan pertanyaan berhasil diperbarui!']);
    }
}

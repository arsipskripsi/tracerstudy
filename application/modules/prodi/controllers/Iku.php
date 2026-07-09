<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * IKU Controller untuk Admin Prodi
 * Menampilkan dashboard IKU-1 khusus untuk prodi sendiri
 */
class Iku extends MY_Prodi_Controller {
    
    public function __construct() {
        parent::__construct();
        $this->load->model('iku/iku_model');
        $this->load->model('kohort/kohort_model');
        $this->load->helper(['form', 'url']);
    }
    
    public function index() {
        $data['page_title'] = 'Dashboard IKU-1';
        $data['page_subtitle'] = 'Indikator Kinerja Utama Program Studi';
        
        $prodi_id = $this->prodi_id;
        
        // Get all active kohorts for this prodi
        $data['kohorts'] = $this->db->where('prodi_id', $prodi_id)
                                     ->where('is_active', 1)
                                     ->order_by('tahun_lulus', 'DESC')
                                     ->get('kohorts')->result_array();
        
        // Get selected kohort (default to latest)
        $selected_kohort_id = $this->input->get('kohort_id') ?: (count($data['kohorts']) > 0 ? $data['kohorts'][0]['id'] : null);
        
        if ($selected_kohort_id) {
            // Get IKU calculations for this prodi and kohort
            $this->db->select('iku.*, k.nama_kohort, k.tahun_lulus')
                     ->from('iku_calculations iku')
                     ->join('kohorts k', 'iku.kohort_id = k.id')
                     ->where('iku.prodi_id', $prodi_id)
                     ->where('iku.kohort_id', $selected_kohort_id)
                     ->order_by('iku_number', 'ASC');
            $data['iku_data'] = $this->db->get()->result_array();
            
            // Calculate overall IKU-1 score
            $total_bobot = 0;
            $total_responden = 0;
            foreach ($data['iku_data'] as $row) {
                if ($row['iku_number'] == 1) { // IKU-1 is about employment
                    $total_bobot += $row['numerator'];
                    $total_responden += $row['denominator'];
                }
            }
            $data['iku_1_score'] = $total_responden > 0 ? ($total_bobot / $total_responden) * 100 : 0;
            
            // Get kohort details
            $this->db->select('*')->from('kohorts')->where('id', $selected_kohort_id);
            $data['current_kohort'] = $this->db->get()->row_array();
        } else {
            $data['iku_data'] = [];
            $data['iku_1_score'] = 0;
            $data['current_kohort'] = null;
        }
        
        $data['selected_kohort_id'] = $selected_kohort_id;
        
        $this->load->view('prodi/templates/header', $data);
        $this->load->view('prodi/iku/index', $data);
        $this->load->view('prodi/templates/footer');
    }
    
    public function detail($iku_number = 1) {
        $data['page_title'] = 'Detail IKU-' . $iku_number;
        $data['page_subtitle'] = 'Rincian perhitungan IKU-' . $iku_number;
        
        $prodi_id = $this->prodi_id;
        $kohort_id = $this->input->get('kohort_id') ?: null;
        
        if (!$kohort_id) {
            redirect('prodi/iku');
            return;
        }
        
        $this->db->select('iku.*, k.nama_kohort, k.tahun_lulus')
                 ->from('iku_calculations iku')
                 ->join('kohorts k', 'iku.kohort_id = k.id')
                 ->where('iku.prodi_id', $prodi_id)
                 ->where('iku.kohort_id', $kohort_id)
                 ->where('iku.iku_number', $iku_number);
        $data['iku_detail'] = $this->db->get()->row_array();
        
        if (!$data['iku_detail']) {
            show_error('Data IKU tidak ditemukan');
            return;
        }
        
        $this->load->view('prodi/templates/header', $data);
        $this->load->view('prodi/iku/detail', $data);
        $this->load->view('prodi/templates/footer');
    }
}

<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Integrasi Nasional Controller
 * Handles integration with tracerstudy.kemdikbud.go.id (Belmawa)
 * 
 * Features:
 * - Export data format XML/JSON sesuai standar nasional
 * - Push data via API ke server nasional
 * - Track pengiriman data (immutable setelah dikirim)
 * - Sync status dari nasional
 * 
 * Role Access: Admin Pusat Karir, Super Admin
 */
class Integrasi extends MX_Controller {

    protected $user_data;
    protected $api_base_url;
    protected $api_key;

    public function __construct() {
        parent::__construct();
        
        // Check authentication
        if (!$this->session->userdata('logged_in')) {
            redirect('auth/login');
        }

        $this->load->model('integrasi_model');
        $this->load->library('auth_lib');
        
        // Check role authorization
        $this->user_data = $this->auth_lib->getUserData();
        if (!in_array($this->user_data['role'], ['admin_pusat_karir', 'super_admin'])) {
            show_error('Akses ditolak. Hanya Admin Pusat Karir dan Super Admin yang dapat mengakses modul ini.', 403);
        }

        // Load API configuration
        $this->api_base_url = $this->config->item('belmawa_api_url') ?: 'https://tracerstudy.kemdikbud.go.id/api';
        $this->api_key = $this->config->item('belmawa_api_key');
    }

    /**
     * Dashboard integrasi nasional
     */
    public function index() {
        $data['page_title'] = 'Integrasi Pelaporan Nasional';
        $data['user'] = $this->user_data;

        // Get statistics
        $data['total_kohort'] = $this->db->count_all('kohorts');
        $data['total_sent'] = $this->db->where('sent_to_belmawa_at IS NOT NULL')->count_all_results('iku_calculations');
        $data['total_pending'] = $this->db->where('sent_to_belmawa_at IS NULL')->count_all_results('iku_calculations');
        
        // Get recent sent data
        $this->db->select('ic.*, k.nama as kohort_nama, p.nama as prodi_nama, u.nama as sent_by_name');
        $this->db->from('iku_calculations ic');
        $this->db->join('kohorts k', 'ic.kohort_id = k.id');
        $this->db->join('program_studi p', 'ic.prodi_id = p.id', 'left');
        $this->db->join('users u', 'ic.sent_by = u.id', 'left');
        $this->db->where('ic.sent_to_belmawa_at IS NOT NULL');
        $this->db->order_by('ic.sent_to_belmawa_at', 'DESC');
        $this->db->limit(10);
        $data['recent_sent'] = $this->db->get()->result_array();

        // Get pending data ready to send
        $this->db->select('ic.*, k.nama as kohort_nama, p.nama as prodi_nama');
        $this->db->from('iku_calculations ic');
        $this->db->join('kohorts k', 'ic.kohort_id = k.id');
        $this->db->join('program_studi p', 'ic.prodi_id = p.id', 'left');
        $this->db->where('ic.sent_to_belmawa_at IS NULL');
        $this->db->where('ic.verified_at IS NOT NULL'); // Only verified data can be sent
        $this->db->order_by('ic.created_at', 'DESC');
        $data['pending_send'] = $this->db->get()->result_array();

        $this->load->view('templates/header', $data);
        $this->load->view('integrasi/index', $data);
        $this->load->view('templates/footer');
    }

    /**
     * Prepare data for export in Belmawa format
     */
    public function prepareExport($calculation_id) {
        if (!in_array($this->user_data['role'], ['super_admin', 'admin_pusat_karir'])) {
            $this->_output(['success' => false, 'message' => 'Unauthorized']);
            return;
        }

        // Get calculation data
        $calculation = $this->db->get_where('iku_calculations', ['id' => $calculation_id])->row();
        
        if (!$calculation) {
            $this->_output(['success' => false, 'message' => 'Data perhitungan tidak ditemukan']);
            return;
        }

        // Check if already sent (immutable)
        if ($calculation->sent_to_belmawa_at) {
            $this->_output([
                'success' => false, 
                'message' => 'Data sudah dikirim ke Belmawa pada ' . $calculation->sent_to_belmawa_at . '. Data immutable.'
            ]);
            return;
        }

        // Prepare export data
        $export_data = $this->integrasi_model->prepareBelmawaFormat($calculation_id);
        
        $this->_output([
            'success' => true,
            'message' => 'Data berhasil disiapkan',
            'data' => $export_data
        ]);
    }

    /**
     * Send data to Belmawa API
     */
    public function sendToBelmawa($calculation_id) {
        if (!in_array($this->user_data['role'], ['super_admin', 'admin_pusat_karir'])) {
            $this->_output(['success' => false, 'message' => 'Unauthorized']);
            return;
        }

        // Get calculation data
        $calculation = $this->db->get_where('iku_calculations', ['id' => $calculation_id])->row();
        
        if (!$calculation) {
            $this->_output(['success' => false, 'message' => 'Data perhitungan tidak ditemukan']);
            return;
        }

        // Check if already sent (immutable - BR-IKU-006)
        if ($calculation->sent_to_belmawa_at) {
            $this->_output([
                'success' => false, 
                'message' => 'Data sudah dikirim ke Belmawa pada ' . $calculation->sent_to_belmawa_at . '. Data tidak dapat diubah.'
            ]);
            return;
        }

        // Check verification status (must be verified before sending)
        if (!$calculation->verified_at) {
            $this->_output([
                'success' => false, 
                'message' => 'Data harus diverifikasi sebelum dikirim ke Belmawa. Silakan lakukan verifikasi terlebih dahulu.'
            ]);
            return;
        }

        // Prepare data
        $export_data = $this->integrasi_model->prepareBelmawaFormat($calculation_id);
        
        // Send via API
        $response = $this->_sendAPI($export_data);
        
        if ($response['success']) {
            // Update database - mark as sent (immutable flag)
            $update_data = [
                'sent_to_belmawa_at' => date('Y-m-d H:i:s'),
                'sent_by' => $this->user_data['id'],
                'belmawa_response' => json_encode($response['data'])
            ];
            
            $this->db->where('id', $calculation_id);
            $this->db->update('iku_calculations', $update_data);

            // Log activity
            $this->_logActivity('send_to_belmawa', [
                'calculation_id' => $calculation_id,
                'kohort_id' => $calculation->kohort_id,
                'prodi_id' => $calculation->prodi_id,
                'response' => $response
            ]);

            $this->_output([
                'success' => true, 
                'message' => 'Data berhasil dikirim ke Belmawa',
                'data' => $response['data']
            ]);
        } else {
            $this->_output([
                'success' => false, 
                'message' => 'Gagal mengirim data ke Belmawa: ' . $response['message']
            ]);
        }
    }

    /**
     * Bulk send multiple calculations to Belmawa
     */
    public function bulkSend() {
        if (!in_array($this->user_data['role'], ['super_admin', 'admin_pusat_karir'])) {
            $this->_output(['success' => false, 'message' => 'Unauthorized']);
            return;
        }

        $ids = $this->input->post('ids');
        
        if (empty($ids) || !is_array($ids)) {
            $this->_output(['success' => false, 'message' => 'Tidak ada data yang dipilih']);
            return;
        }

        $success_count = 0;
        $fail_count = 0;
        $results = [];

        foreach ($ids as $id) {
            $calculation = $this->db->get_where('iku_calculations', ['id' => $id])->row();
            
            if (!$calculation) {
                $results[] = ['id' => $id, 'status' => 'error', 'message' => 'Data not found'];
                $fail_count++;
                continue;
            }

            if ($calculation->sent_to_belmawa_at) {
                $results[] = ['id' => $id, 'status' => 'skipped', 'message' => 'Already sent'];
                continue;
            }

            if (!$calculation->verified_at) {
                $results[] = ['id' => $id, 'status' => 'error', 'message' => 'Not verified'];
                $fail_count++;
                continue;
            }

            // Send to Belmawa
            $export_data = $this->integrasi_model->prepareBelmawaFormat($id);
            $response = $this->_sendAPI($export_data);
            
            if ($response['success']) {
                $update_data = [
                    'sent_to_belmawa_at' => date('Y-m-d H:i:s'),
                    'sent_by' => $this->user_data['id'],
                    'belmawa_response' => json_encode($response['data'])
                ];
                
                $this->db->where('id', $id);
                $this->db->update('iku_calculations', $update_data);
                
                $success_count++;
                $results[] = ['id' => $id, 'status' => 'success'];
            } else {
                $fail_count++;
                $results[] = ['id' => $id, 'status' => 'error', 'message' => $response['message']];
            }
        }

        $this->_output([
            'success' => true,
            'message' => "Bulk send completed: {$success_count} success, {$fail_count} failed",
            'results' => $results
        ]);
    }

    /**
     * Download export file in various formats
     */
    public function downloadExport($calculation_id, $format = 'json') {
        if (!in_array($this->user_data['role'], ['super_admin', 'admin_pusat_karir'])) {
            show_error('Unauthorized', 403);
            return;
        }

        $export_data = $this->integrasi_model->prepareBelmawaFormat($calculation_id);
        
        if ($format === 'json') {
            $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode($export_data, JSON_PRETTY_PRINT));
        } elseif ($format === 'xml') {
            $this->output
                ->set_content_type('application/xml')
                ->set_output($this->_arrayToXml($export_data));
        } elseif ($format === 'csv') {
            $this->output
                ->set_content_type('text/csv')
                ->set_output($this->_arrayToCsv($export_data));
        }
    }

    /**
     * Sync status from Belmawa
     */
    public function syncStatus() {
        if (!in_array($this->user_data['role'], ['super_admin', 'admin_pusat_karir'])) {
            $this->_output(['success' => false, 'message' => 'Unauthorized']);
            return;
        }

        // Get all sent calculations
        $this->db->where('sent_to_belmawa_at IS NOT NULL');
        $calculations = $this->db->get('iku_calculations')->result();
        
        $synced_count = 0;
        
        foreach ($calculations as $calc) {
            // Call Belmawa API to check status
            $response = $this->_checkStatusAPI($calc->id);
            
            if ($response['success']) {
                $synced_count++;
            }
        }

        $this->_output([
            'success' => true,
            'message' => "Sync completed. {$synced_count} records synced."
        ]);
    }

    /**
     * Send data via API to Belmawa
     */
    private function _sendAPI($data) {
        // Simulate API call (replace with actual API implementation)
        // In production, use cURL or Guzzle
        
        if (!$this->api_key) {
            return [
                'success' => false,
                'message' => 'API key not configured'
            ];
        }

        $ch = curl_init($this->api_base_url . '/submit');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $this->api_key
        ]);
        
        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($http_code === 200) {
            return [
                'success' => true,
                'data' => json_decode($response, true)
            ];
        } else {
            return [
                'success' => false,
                'message' => 'API error: HTTP ' . $http_code
            ];
        }
    }

    /**
     * Check status from Belmawa API
     */
    private function _checkStatusAPI($calculation_id) {
        // Simulate API call
        return ['success' => true];
    }

    /**
     * Convert array to XML
     */
    private function _arrayToXml($data, $root = 'data') {
        $xml = '<?xml version="1.0" encoding="UTF-8"?>';
        $xml .= '<' . $root . '>';
        
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $xml .= $this->_arrayToXml($value, $key);
            } else {
                $xml .= '<' . $key . '>' . htmlspecialchars($value) . '</' . $key . '>';
            }
        }
        
        $xml .= '</' . $root . '>';
        return $xml;
    }

    /**
     * Convert array to CSV
     */
    private function _arrayToCsv($data) {
        $output = '';
        // Implement CSV conversion logic
        return $output;
    }

    /**
     * Output JSON response
     */
    private function _output($data) {
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }

    /**
     * Log activity
     */
    private function _logActivity($action, $details) {
        $this->db->insert('activity_logs', [
            'user_id' => $this->user_data['id'],
            'action' => $action,
            'details' => json_encode($details),
            'created_at' => date('Y-m-d H:i:s')
        ]);
    }
}

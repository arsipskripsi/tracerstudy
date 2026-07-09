<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Integrasi Model
 * Handles database operations for national integration
 */
class Integrasi_model extends CI_Model {

    public function __construct() {
        parent::__construct();
    }

    /**
     * Prepare data in Belmawa format
     * Format sesuai standar nasional untuk IKU-1
     */
    public function prepareBelmawaFormat($calculation_id) {
        // Get calculation data
        $this->db->select('ic.*, k.nama as kohort_nama, k.tahun as graduation_year, p.nama as prodi_nama, p.kode as prodi_kode');
        $this->db->from('iku_calculations ic');
        $this->db->join('kohorts k', 'ic.kohort_id = k.id');
        $this->db->join('program_studi p', 'ic.prodi_id = p.id', 'left');
        $this->db->where('ic.id', $calculation_id);
        $calculation = $this->db->get()->row_array();
        
        if (!$calculation) {
            return null;
        }

        // Get mapping data (alumni details)
        $mapping_ids = json_decode($calculation['mapping_data'], true) ?: [];
        $alumni_data = [];
        
        if (!empty($mapping_ids)) {
            $this->db->select('a.*, p.nama as prodi_nama, f.nama as fakultas_nama');
            $this->db->from('alumni a');
            $this->db->join('program_studi p', 'a.prodi_id = p.id', 'left');
            $this->db->join('fakultas f', 'p.fakultas_id = f.id', 'left');
            $this->db->where_in('a.id', $mapping_ids);
            $alumni_list = $this->db->get()->result_array();
            
            foreach ($alumni_list as $alumni) {
                // Determine status according to BR-SUR-008 priority
                $status = $this->_determineStatus($alumni);
                
                $alumni_data[] = [
                    'nim' => $alumni['nim'],
                    'nama' => $alumni['nama_lengkap'],
                    'prodi' => $alumni['prodi_nama'],
                    'fakultas' => $alumni['fakultas_nama'],
                    'tahun_lulus' => date('Y', strtotime($alumni['tanggal_lulus'])),
                    'status' => $status,
                    'pekerjaan' => $alumni['posisi_pekerjaan'] ?? '',
                    'nama_perusahaan' => $alumni['nama_instansi_perusahaan'] ?? '',
                    'gaji' => $alumni['gaji_aktual'] ?? $alumni['gaji'] ?? 0,
                    'provinsi_domisili' => $alumni['provinsi_domisili'] ?? '',
                    'tanggal_survey' => $alumni['tanggal_response'] ?? ''
                ];
            }
        }

        // Prepare final export structure
        $export_data = [
            'header' => [
                'institution_code' => $this->config->item('institution_code') ?: '',
                'institution_name' => $this->config->item('institution_name') ?: '',
                'report_type' => 'IKU-1',
                'period' => $calculation['tahun_iku'],
                'generated_at' => date('Y-m-d H:i:s'),
                'total_responden' => $calculation['total_responden'],
                'response_rate' => $calculation['response_rate'],
                'vv_rate' => $calculation['vv_rate'],
                'final_score' => $calculation['final_score'],
                'penalty_applied' => $calculation['penalty_applied'] ? 1 : 0
            ],
            'detail' => $alumni_data
        ];

        return $export_data;
    }

    /**
     * Determine alumni status based on BR-SUR-008
     * Priority: Bekerja > Wirausaha > Studi Lanjut > Belum Bekerja
     */
    private function _determineStatus($alumni) {
        // Check working status first (highest priority)
        if (!empty($alumni['status_bekerja']) && $alumni['status_bekerja'] == 1) {
            return 'Bekerja';
        }
        
        // Check entrepreneurship
        if (!empty($alumni['status_wirausaha']) && $alumni['status_wirausaha'] == 1) {
            return 'Wirausaha';
        }
        
        // Check further study
        if (!empty($alumni['status_studi_lanjut']) && $alumni['status_studi_lanjut'] == 1) {
            return 'Studi Lanjut';
        }
        
        // Default: Belum bekerja
        return 'Belum Bekerja';
    }

    /**
     * Get all pending calculations ready to send
     */
    public function getPendingCalculations() {
        $this->db->select('ic.*, k.nama as kohort_nama, p.nama as prodi_nama');
        $this->db->from('iku_calculations ic');
        $this->db->join('kohorts k', 'ic.kohort_id = k.id');
        $this->db->join('program_studi p', 'ic.prodi_id = p.id', 'left');
        $this->db->where('ic.sent_to_belmawa_at IS NULL');
        $this->db->where('ic.verified_at IS NOT NULL');
        $this->db->order_by('ic.created_at', 'DESC');
        return $this->db->get()->result_array();
    }

    /**
     * Get sent calculations history
     */
    public function getSentCalculations($limit = 50) {
        $this->db->select('ic.*, k.nama as kohort_nama, p.nama as prodi_nama, u.nama as sent_by_name');
        $this->db->from('iku_calculations ic');
        $this->db->join('kohorts k', 'ic.kohort_id = k.id');
        $this->db->join('program_studi p', 'ic.prodi_id = p.id', 'left');
        $this->db->join('users u', 'ic.sent_by = u.id', 'left');
        $this->db->where('ic.sent_to_belmawa_at IS NOT NULL');
        $this->db->order_by('ic.sent_to_belmawa_at', 'DESC');
        $this->db->limit($limit);
        return $this->db->get()->result_array();
    }
}

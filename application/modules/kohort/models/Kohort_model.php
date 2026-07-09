<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Kohort Model
 * Handles database operations for kohort management
 */
class Kohort_model extends CI_Model {

    private $table = 'kohort';
    private $alumni_table = 'alumni';

    public function __construct() {
        parent::__construct();
    }

    /**
     * Get all kohorts
     */
    public function get_all() {
        return $this->db->order_by('tahun_selesai', 'DESC')->get($this->table)->result();
    }

    /**
     * Get kohort by ID
     */
    public function get_by_id($id) {
        return $this->db->get_where($this->table, ['id' => $id])->row();
    }

    /**
     * Get kohort by graduation year (matches tahun_selesai)
     */
    public function get_by_year($year) {
        return $this->db->get_where($this->table, ['tahun_selesai' => $year])->row();
    }

    /**
     * Get active kohorts only
     */
    public function get_active() {
        // Filter aktif berdasarkan status = 'aktif'
        return $this->db->where('status', 'aktif')
                        ->order_by('tahun_selesai', 'DESC')
                        ->get($this->table)
                        ->result();
    }

    /**
     * Count active kohorts
     */
    public function count_active() {
        return $this->db->where('status', 'aktif')
                        ->count_all_results($this->table);
    }

    /**
     * Create new kohort
     */
    public function create($data) {
        return $this->db->insert($this->table, $data);
    }

    /**
     * Update existing kohort
     */
    public function update($id, $data) {
        $this->db->where('id', $id);
        return $this->db->update($this->table, $data);
    }

    /**
     * Delete kohort
     */
    public function delete($id) {
        $this->db->where('id', $id);
        return $this->db->delete($this->table);
    }

    /**
     * Count alumni in a kohort
     */
    public function count_alumni($kohort_id) {
        $kohort = $this->get_by_id($kohort_id);
        if (!$kohort) return 0;
        
        return $this->db->where('YEAR(tanggal_lulus)', $kohort->tahun_selesai)
                        ->count_all_results($this->alumni_table);
    }

    /**
     * Auto-assign alumni to kohort based on graduation year
     * This links alumni to kohort conceptually (no foreign key needed)
     */
    public function auto_assign_alumni($graduation_year) {
        // Since we're using YEAR(tanggal_lulus) for matching, 
        // no explicit assignment is needed. Alumni are automatically 
        // part of the kohort based on their graduation year.
        
        return TRUE;
    }

    /**
     * Get statistics for a kohort
     */
    public function get_statistics($kohort_id) {
        $kohort = $this->get_by_id($kohort_id);
        if (!$kohort) return NULL;
        
        $year = $kohort->tahun_selesai;
        
        // Total alumni
        $total_alumni = $this->db->where('YEAR(tanggal_lulus)', $year)
                                 ->count_all_results($this->alumni_table);
        
        // Total responded - cek keberadaan di survey_responses
        $this->db->select('COUNT(DISTINCT a.id) as responded');
        $this->db->from('alumni a');
        $this->db->join('survey_responses sr', 'a.id = sr.alumni_id', 'inner');
        $this->db->where('YEAR(a.tanggal_lulus)', $year);
        $result = $this->db->get()->row();
        $total_responded = $result->responded;
        
        // Response rate
        $response_rate = $total_alumni > 0 ? ($total_responded / $total_alumni) * 100 : 0;
        
        return [
            'total_alumni' => $total_alumni,
            'total_responded' => $total_responded,
            'response_rate' => round($response_rate, 2)
        ];
    }

    /**
     * Count alumni without assigned kohort (no matching graduation year)
     */
    public function count_unassigned() {
        // Get all graduation years from kohort table (using tahun_selesai)
        $this->db->select('tahun_selesai');
        $kohorts = $this->db->get($this->table)->result();
        
        if (empty($kohorts)) {
            // If no kohorts exist, all alumni are unassigned
            return $this->db->count_all_results($this->alumni_table);
        }
        
        $years = array_column($kohorts, 'tahun_selesai');
        
        // Count alumni whose graduation year is NOT in kohort list
        $this->db->where_not_in('YEAR(tanggal_lulus)', $years);
        return $this->db->count_all_results($this->alumni_table);
    }
}

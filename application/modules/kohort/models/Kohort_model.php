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
        return $this->db->order_by('graduation_year', 'DESC')->get($this->table)->result();
    }

    /**
     * Get kohort by ID
     */
    public function get_by_id($id) {
        return $this->db->get_where($this->table, ['id' => $id])->row();
    }

    /**
     * Get kohort by graduation year
     */
    public function get_by_year($year) {
        return $this->db->get_where($this->table, ['graduation_year' => $year])->row();
    }

    /**
     * Get active kohorts only
     */
    public function get_active() {
        return $this->db->where('is_active', 1)
                        ->order_by('graduation_year', 'DESC')
                        ->get($this->table)
                        ->result();
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
        
        return $this->db->where('YEAR(tanggal_lulus)', $kohort->graduation_year)
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
        
        // However, if you want to add a kohort_id column to alumni table:
        // $this->db->where('YEAR(tanggal_lulus)', $graduation_year)
        //          ->set('kohort_id', $kohort_id)
        //          ->update($this->alumni_table);
        
        return TRUE;
    }

    /**
     * Get statistics for a kohort
     */
    public function get_statistics($kohort_id) {
        $kohort = $this->get_by_id($kohort_id);
        if (!$kohort) return NULL;
        
        $year = $kohort->graduation_year;
        
        // Total alumni
        $total_alumni = $this->db->where('YEAR(tanggal_lulus)', $year)
                                 ->count_all_results($this->alumni_table);
        
        // Total responded
        $total_responded = $this->db->where('YEAR(tanggal_lulus)', $year)
                                    ->where('status_response', 'responded')
                                    ->count_all_results($this->alumni_table);
        
        // Response rate
        $response_rate = $total_alumni > 0 ? ($total_responded / $total_alumni) * 100 : 0;
        
        return [
            'total_alumni' => $total_alumni,
            'total_responded' => $total_responded,
            'response_rate' => round($response_rate, 2)
        ];
    }
}

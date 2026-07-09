<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Dashboard Controller - Halaman Utama Admin/Superadmin
 * 
 * Menampilkan overview sistem tracer study dengan statistik dan informasi penting
 */
class Dashboard extends Admin_Controller {

    public function __construct() {
        parent::__construct();
        
         // Hanya super_admin, admin_pusat_karir, dan admin yang bisa akses
        $this->auth_lib->requireRole(['super_admin', 'admin_pusat_karir', 'admin']);
        
        $this->load->model('auth/user_model');
        $this->load->helper('tracer_audit');
    }

    /**
     * Index: Dashboard utama admin
     */
    public function index() {
        $data['page_title'] = 'Dashboard Admin';
        $data['page_subtitle'] = 'Overview Sistem Tracer Study';
        
        // Statistik umum
        $data['total_users'] = $this->db->count_all('users');
        $data['total_alumni'] = $this->db->count_all('alumni');
        $data['total_surveys'] = $this->db->count_all('surveys');
        $data['total_responses'] = $this->db->count_all('survey_responses');
        
        // Hitung total kohort aktif (untuk Admin Pusat Karir)
        $this->db->where('is_active', 1);
        $data['active_kohorts'] = $this->db->count_all_results('kohort');
        
        // Hitung alumni belum terassign kohort
        $this->db->where('kohort_id IS NULL OR kohort_id = 0', NULL, FALSE);
        $data['alumni_without_kohort'] = $this->db->count_all_results('alumni');
        
        // Statistik IKU untuk dashboard
        $role = $this->session->userdata('role');
        if (in_array($role, ['super_admin', 'admin_pusat_karir'])) {
            // Total perhitungan IKU
            $data['total_iku_calculations'] = $this->db->count_all_results('iku_calculations');
            
            // IKU sudah diverifikasi
            $this->db->where('verified_at IS NOT NULL');
            $data['verified_iku'] = $this->db->count_all_results('iku_calculations');
            
            // IKU menunggu verifikasi
            $this->db->where('verified_at IS NULL');
            $data['pending_iku'] = $this->db->count_all_results('iku_calculations');
            
            // Data sudah dikirim ke Belmawa
            $this->db->where('sent_to_belmawa_at IS NOT NULL');
            $data['sent_to_belmawa'] = $this->db->count_all_results('iku_calculations');
        } else {
            $data['total_iku_calculations'] = 0;
            $data['verified_iku'] = 0;
            $data['pending_iku'] = 0;
            $data['sent_to_belmawa'] = 0;
        }
        
        // Statistik berdasarkan role
        $this->db->where('role', 'super_admin');
        $data['total_super_admin'] = $this->db->count_all_results('users');
        
        $this->db->where('role', 'admin_pusat_karir');
        $data['total_admin_pusat'] = $this->db->count_all_results('users');
        
        $this->db->where('role', 'admin_prodi');
        $data['total_admin_prodi'] = $this->db->count_all_results('users');
        
		
        $this->db->where('role', 'admin_fakultas');
        $data['total_admin_fakultas'] = $this->db->count_all_results('users');

        $this->db->where('role', 'dosen');
        $data['total_dosen'] = $this->db->count_all_results('users');

        $this->db->where('role', 'reviewer');
        $data['total_reviewer'] = $this->db->count_all_results('users');
		
		
        $this->db->where('role', 'alumni');
        $data['total_alumni_users'] = $this->db->count_all_results('users');
        
        // Statistik stakeholder
        $this->db->where('role', 'stakeholder');
        $data['total_stakeholder'] = $this->db->count_all_results('users');
        
        // Survey aktif
        $this->db->where('status', 'active');
        $data['active_surveys'] = $this->db->count_all_results('surveys');
        
        // Survey draft
        $this->db->where('status', 'draft');
        $data['draft_surveys'] = $this->db->count_all_results('surveys');
        
        // Response rate (sederhana)
        if ($data['total_surveys'] > 0) {
            $data['response_rate'] = round(($data['total_responses'] / ($data['total_surveys'] * 100)) * 100, 2);
        } else {
            $data['response_rate'] = 0;
        }
        
        // Activity logs terbaru (5 terakhir)
        $this->db->select('al.*, u.username');
        $this->db->from('activity_logs al');
        $this->db->join('users u', 'al.user_id = u.id', 'left');
        $this->db->order_by('al.created_at', 'DESC');
        $this->db->limit(5);
        $data['recent_activities'] = $this->db->get()->result_array();
        
        // Load view
        $this->load->view('admin/templates/header', $data);
        $this->load->view('admin/dashboard/index', $data);
        $this->load->view('admin/templates/footer');
    }
}

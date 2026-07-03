<?php
defined('BASEPATH') OR exit('No direct script access allowed');


/**
 * Audit Trail Controller
 * 
 * Menampilkan log aktivitas sistem dengan fitur filter dan export
 * BR-SEC-001: Tidak ada method delete() - audit trail immutable
 * 
 * @package Tracer Study
 * @subpackage Admin
 */
class Audit extends Admin_Controller {

    public function __construct() {
        parent::__construct();
        
        // BR-SEC-001: Hanya super_admin dan admin_pusat_karir yang bisa akses
        $this->auth_lib->requireRole(['super_admin', 'admin_pusat_karir']);
        
        $this->load->model('auth/user_model');
        $this->load->helper('tracer_audit');
    }

    /**
     * Index: List log dengan filter
     * Menampilkan halaman utama audit trail
     */
    public function index() {
        $data['page_title'] = 'Audit Trail System';
        $data['page_subtitle'] = 'Monitoring Aktivitas Sistem';
        
        // Ambil list module unik untuk dropdown filter (dari kolom module)
        $this->db->select('module');
        $this->db->distinct();
        $this->db->where('module IS NOT NULL');
        $this->db->where('module !=', '');
        $this->db->order_by('module', 'ASC');
        $modules = $this->db->get('activity_logs')->result_array();
        $data['modules'] = array_column($modules, 'module');
        
        // Ambil list action unik
        $this->db->select('action');
        $this->db->distinct();
        $this->db->order_by('action', 'ASC');
        $actions = $this->db->get('activity_logs')->result_array();
        $data['actions'] = array_column($actions, 'action');
        
        // Ambil list user untuk dropdown filter
        $this->db->select('id, username');
        $this->db->from('users');
        $this->db->order_by('username', 'ASC');
        $users = $this->db->get()->result_array();
        $data['users'] = $users;
        
        // Load view
        $this->load->view('admin/templates/header', $data);
        $this->load->view('admin/audit/index', $data);
        $this->load->view('admin/templates/footer');
    }

    /**
     * API Endpoint untuk DataTables Server-Side Processing
     * 
     * @return JSON
     */
    public function get_data() {
        // Setup DataTables Server Side
        $draw = $this->input->get('draw');
        $start = $this->input->get('start');
        $length = $this->input->get('length');
        $search = $this->input->get('search')['value'];
        
        // Filters dari request
        $filter_module = $this->input->get('module');
        $filter_action = $this->input->get('action');
        $filter_user = $this->input->get('user_id');
        $date_from = $this->input->get('date_from');
        $date_to = $this->input->get('date_to');

        // Build query untuk total records (tanpa filter apapun)
        $this->db->select('al.id');
        $this->db->from('activity_logs al');
        $total_records = $this->db->count_all_results();
        
        // Reset dan rebuild query untuk apply filters
        $this->db->reset_query();
        $this->db->select('al.*, u.username, u.role');
        $this->db->from('activity_logs al');
        $this->db->join('users u', 'al.user_id = u.id', 'left');
        
        // Apply Filters
        if ($filter_module) {
            $this->db->where('al.module', $filter_module);
        }
        if ($filter_action) {
            $this->db->where('al.action', $filter_action);
        }
        if ($filter_user) {
            $this->db->where('al.user_id', $filter_user);
        }
        if ($date_from) {
            $this->db->where('al.created_at >=', $date_from . ' 00:00:00');
        }
        if ($date_to) {
            $this->db->where('al.created_at <=', $date_to . ' 23:59:59');
        }
        
        // Global search
        if (!empty($search)) {
            $this->db->group_start();
            $this->db->like('al.new_values', $search);
            $this->db->or_like('u.username', $search);
            $this->db->or_like('al.module', $search);
            $this->db->or_like('al.ip_address', $search);
            $this->db->group_end();
        }

        // Hitung filtered records - perlu rebuild query karena count_all_results mengubah state
        $filtered_records = $this->db->count_all_results();
        
        // Reset lagi dan rebuild untuk query utama (dengan order dan limit)
        $this->db->reset_query();
        $this->db->select('al.*, u.username, u.role');
        $this->db->from('activity_logs al');
        $this->db->join('users u', 'al.user_id = u.id', 'left');
        
        // Re-apply filters untuk data retrieval
        if ($filter_module) {
            $this->db->where('al.module', $filter_module);
        }
        if ($filter_action) {
            $this->db->where('al.action', $filter_action);
        }
        if ($filter_user) {
            $this->db->where('al.user_id', $filter_user);
        }
        if ($date_from) {
            $this->db->where('al.created_at >=', $date_from . ' 00:00:00');
        }
        if ($date_to) {
            $this->db->where('al.created_at <=', $date_to . ' 23:59:59');
        }
        if (!empty($search)) {
            $this->db->group_start();
            $this->db->like('al.new_values', $search);
            $this->db->or_like('u.username', $search);
            $this->db->or_like('al.module', $search);
            $this->db->or_like('al.ip_address', $search);
            $this->db->group_end();
        }
        
        // Ordering
        $order_col = $this->input->get('order')[0]['column'] ?? 0;
        $order_dir = $this->input->get('order')[0]['dir'] ?? 'desc';
        $columns = ['al.created_at', 'u.username', 'al.action', 'al.module', 'al.new_values', 'al.ip_address'];
        $this->db->order_by($columns[$order_col] ?? 'al.created_at', $order_dir);
        
        // Pagination
        $this->db->limit($length, $start);
        $query = $this->db->get();
        $results = $query->result_array();

        // Format data untuk DataTables
        $data = [];
        foreach ($results as $row) {
            $nestedData = [];
            $nestedData[] = date('d M Y H:i:s', strtotime($row['created_at']));
            $nestedData[] = $row['username'] ? '<strong>' . htmlspecialchars($row['username']) . '</strong><br><small class="text-muted">' . $row['role'] . '</small>' : '<em class="text-muted">System</em>';
            $nestedData[] = '<span class="badge bg-' . $this->_get_badge_color($row['action']) . '">' . strtoupper(htmlspecialchars($row['action'])) . '</span>';
            $nestedData[] = htmlspecialchars($row['module'] ?? '-');
            
            // Extract description from new_values JSON
            $description = '-';
            if (!empty($row['new_values'])) {
                $decoded = json_decode($row['new_values'], true);
                if (is_array($decoded)) {
                    $description = $decoded['description'] ?? json_encode($decoded, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
                } else {
                    $description = $row['new_values'];
                }
            }
            $nestedData[] = '<small>' . htmlspecialchars(substr($description, 0, 80)) . (strlen($description) > 80 ? '...' : '') . '</small>';
            $nestedData[] = '<button class="btn btn-sm btn-outline-primary view-log" data-id="'.$row['id'].'" title="View Detail"><i class="fas fa-eye"></i></button>';
            $data[] = $nestedData;
        }

        $output = [
            "draw" => intval($draw),
            "recordsTotal" => $total_records,
            "recordsFiltered" => $filtered_records,
            "data" => $data
        ];

        echo json_encode($output);
    }

    /**
     * View Detail Log
     * 
     * @param int $id
     * @return JSON
     */
    public function view($id) {
        $this->db->select('al.*, u.username, u.role');
        $this->db->from('activity_logs al');
        $this->db->join('users u', 'al.user_id = u.id', 'left');
        $this->db->where('al.id', $id);
        $log = $this->db->get()->row();
        
        if (!$log) {
            echo json_encode(['success' => false, 'message' => 'Log not found']);
            return;
        }

        // Extract description from new_values JSON
        $description = '-';
        if (!empty($log->new_values)) {
            $decoded = json_decode($log->new_values, true);
            if (is_array($decoded)) {
                $description = $decoded['description'] ?? json_encode($decoded, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            } else {
                $description = $log->new_values;
            }
        }

        $response = [
            'success' => true,
            'data' => [
                'id' => $log->id,
                'created_at' => $log->created_at,
                'username' => $log->username ?? 'System',
                'role' => $log->role ?? '-',
                'ip_address' => $log->ip_address,
                'user_agent' => $log->user_agent,
                'action' => $log->action,
                'module' => $log->module ?? '-',
                'table_name' => $log->table_name ?? '-',
                'record_id' => $log->record_id,
                'description' => $description,
                'old_values' => $log->old_values,
                'new_values' => $log->new_values
            ]
        ];
        
        echo json_encode($response);
    }

    /**
     * Export Log ke Excel/CSV
     * 
     * BR-SEC-001: Tidak ada delete, hanya export
     * 
     * @return void
     */
    public function export() {
        $format = $this->input->get('format') ?? 'excel';
        
        // Get filters
        $filter_module = $this->input->get('module');
        $filter_action = $this->input->get('action');
        $filter_user = $this->input->get('user_id');
        $date_from = $this->input->get('date_from');
        $date_to = $this->input->get('date_to');

        // Build query
        $this->db->select('al.*, u.username, u.role');
        $this->db->from('activity_logs al');
        $this->db->join('users u', 'al.user_id = u.id', 'left');
        
        if ($filter_module) {
            $this->db->where('al.module', $filter_module);
        }
        if ($filter_action) {
            $this->db->where('al.action', $filter_action);
        }
        if ($filter_user) {
            $this->db->where('al.user_id', $filter_user);
        }
        if ($date_from) {
            $this->db->where('al.created_at >=', $date_from . ' 00:00:00');
        }
        if ($date_to) {
            $this->db->where('al.created_at <=', $date_to . ' 23:59:59');
        }
        
        $this->db->order_by('al.created_at', 'DESC');
        $logs = $this->db->get()->result_array();

        // Log activity export
        audit_log('export', 'audit', 'Export audit trail to ' . strtoupper($format), $this->session->userdata('user_data')['id']);

        if ($format === 'excel' || $format === 'csv') {
            // Set headers
            header('Content-Type: application/vnd.ms-excel');
            header('Content-Disposition: attachment; filename="audit_trail_' . date('Y-m-d_His') . '.xls"');
            header('Pragma: no-cache');
            header('Expires: 0');
            
            // Output Excel XML format
            echo '<?xml version="1.0" encoding="UTF-8"?>';
            echo '<?mso-application progid="Excel.Sheet"?>';
            echo '<Workbook xmlns="urn:schemas-microsoft-com:office:spreadsheetml"';
            echo ' xmlns:o="urn:schemas-microsoft-com:office:office"';
            echo ' xmlns:x="urn:schemas-microsoft-com:office:excel"';
            echo ' xmlns:ss="urn:schemas-microsoft-com:office:spreadsheetml"';
            echo ' xmlns:html="http://www.w3.org/TR/REC-html40">';
            echo '<Worksheet ss:Name="Audit Trail">';
            echo '<Table>';
            echo '<Row>';
            echo '<Cell><Data ss:Type="String">Timestamp</Data></Cell>';
            echo '<Cell><Data ss:Type="String">User</Data></Cell>';
            echo '<Cell><Data ss:Type="String">Role</Data></Cell>';
            echo '<Cell><Data ss:Type="String">Action</Data></Cell>';
            echo '<Cell><Data ss:Type="String">Module</Data></Cell>';
            echo '<Cell><Data ss:Type="String">Description</Data></Cell>';
            echo '<Cell><Data ss:Type="String">IP Address</Data></Cell>';
            echo '</Row>';
            
            foreach ($logs as $log) {
                // Extract description from new_values JSON for export
                $description = '-';
                if (!empty($log['new_values'])) {
                    $decoded = json_decode($log['new_values'], true);
                    if (is_array($decoded)) {
                        $description = $decoded['description'] ?? json_encode($decoded, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
                    } else {
                        $description = $log['new_values'];
                    }
                }
                
                echo '<Row>';
                echo '<Cell><Data ss:Type="String">' . htmlspecialchars($log['created_at']) . '</Data></Cell>';
                echo '<Cell><Data ss:Type="String">' . htmlspecialchars($log['username'] ?? 'System') . '</Data></Cell>';
                echo '<Cell><Data ss:Type="String">' . htmlspecialchars($log['role'] ?? '-') . '</Data></Cell>';
                echo '<Cell><Data ss:Type="String">' . htmlspecialchars($log['action']) . '</Data></Cell>';
                echo '<Cell><Data ss:Type="String">' . htmlspecialchars($log['module'] ?? '-') . '</Data></Cell>';
                echo '<Cell><Data ss:Type="String">' . htmlspecialchars($description) . '</Data></Cell>';
                echo '<Cell><Data ss:Type="String">' . htmlspecialchars($log['ip_address']) . '</Data></Cell>';
                echo '</Row>';
            }
            
            echo '</Table></Worksheet></Workbook>';
        } else {
            show_error('Format export tidak didukung. Gunakan excel atau csv.');
        }
    }

    /**
     * Helper: Dapatkan warna badge berdasarkan action type
     * 
     * @param string $action
     * @return string
     */
    private function _get_badge_color($action) {
        $colors = [
            'create' => 'success',
            'insert' => 'success',
            'update' => 'warning',
            'edit' => 'warning',
            'delete' => 'danger',
            'login' => 'info',
            'logout' => 'secondary',
            'export' => 'primary',
            'import' => 'primary',
            'sync' => 'dark'
        ];
        
        return $colors[strtolower($action)] ?? 'light';
    }
}

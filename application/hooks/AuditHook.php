<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * AuditHook - Audit Trail Hook
 *
 * Logs user activities for audit trail purposes.
 *
 * @package     Tracer Study
 * @subpackage  Hooks
 * @category    Hooks
 * @author      Tracer Study Team
 */

class AuditHook
{
    /**
     * CodeIgniter instance
     * @var CI_Controller
     */
    private $CI;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->CI =& get_instance();
    }

    /**
     * Log user activity
     *
     * This hook runs after controller execution.
     * It logs important user actions to the database.
     *
     * @return void
     */
    public function log_activity()
    {
        // Only log if user is logged in
        if (!$this->CI->session->has_userdata('user_id')) {
            return;
        }

        // Get current request info
        $user_id = $this->CI->session->userdata('user_id');
        $controller = $this->CI->router->class;
        $method = $this->CI->router->method;
        $uri_string = $this->CI->uri->uri_string();
        $ip_address = $this->CI->input->ip_address();
        $user_agent = $this->CI->input->user_agent();
        $request_method = $this->CI->input->method(TRUE);
        $request_time = date('Y-m-d H:i:s');

        // Define actions that should be logged
        $loggable_actions = array(
            'insert', 'create', 'add', 'store',
            'update', 'edit', 'save',
            'delete', 'remove', 'destroy',
            'export', 'download', 'upload',
            'login', 'logout', 'register'
        );

        // Check if current action should be logged
        $should_log = FALSE;

        foreach ($loggable_actions as $action) {
            if (strpos($method, $action) !== FALSE) {
                $should_log = TRUE;
                break;
            }
        }

        // Also log specific controllers
        $loggable_controllers = array('auth', 'laporan', 'iku', 'survey');

        if (in_array($controller, $loggable_controllers)) {
            $should_log = TRUE;
        }

        if (!$should_log) {
            return;
        }

        // Prepare audit data
        $audit_data = array(
            'user_id' => $user_id,
            'controller' => $controller,
            'method' => $method,
            'uri_string' => $uri_string,
            'ip_address' => $ip_address,
            'user_agent' => $user_agent,
            'request_method' => $request_method,
            'action' => $this->_determine_action($controller, $method),
            'description' => $this->_generate_description($controller, $method),
            'created_at' => $request_time
        );

        // Insert audit log to database
        // Note: Make sure 'audit_logs' table exists
        try {
            $this->CI->db->insert('audit_logs', $audit_data);
        } catch (Exception $e) {
            // Log error but don't interrupt the flow
            log_message('error', 'AuditHook: Failed to log activity - ' . $e->getMessage());
        }
    }

    /**
     * Determine action type from method name
     *
     * @param string $controller Controller name
     * @param string $method Method name
     * @return string
     */
    private function _determine_action($controller, $method)
    {
        $action_map = array(
            'index' => 'VIEW',
            'view' => 'VIEW',
            'show' => 'VIEW',
            'create' => 'CREATE',
            'store' => 'CREATE',
            'add' => 'CREATE',
            'edit' => 'UPDATE',
            'update' => 'UPDATE',
            'save' => 'UPDATE',
            'delete' => 'DELETE',
            'destroy' => 'DELETE',
            'remove' => 'DELETE',
            'export' => 'EXPORT',
            'download' => 'DOWNLOAD',
            'upload' => 'UPLOAD',
            'login' => 'LOGIN',
            'logout' => 'LOGOUT',
            'register' => 'REGISTER'
        );

        return isset($action_map[$method]) ? $action_map[$method] : 'OTHER';
    }

    /**
     * Generate human-readable description
     *
     * @param string $controller Controller name
     * @param string $method Method name
     * @return string
     */
    private function _generate_description($controller, $method)
    {
        $action = $this->_determine_action($controller, $method);
        $module = ucfirst($controller);

        $descriptions = array(
            'VIEW' => "Melihat data {$module}",
            'CREATE' => "Menambah data {$module} baru",
            'UPDATE' => "Mengubah data {$module}",
            'DELETE' => "Menghapus data {$module}",
            'EXPORT' => "Mengekspor data {$module}",
            'DOWNLOAD' => "Mengunduh file {$module}",
            'UPLOAD' => "Mengunggah file {$module}",
            'LOGIN' => "Login ke sistem",
            'LOGOUT' => "Logout dari sistem",
            'REGISTER' => "Registrasi akun baru"
        );

        return isset($descriptions[$action]) ? $descriptions[$action] : "Aksi {$method} pada {$module}";
    }
}

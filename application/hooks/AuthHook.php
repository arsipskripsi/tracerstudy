<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * AuthHook - Authentication Hook
 *
 * Handles authentication checks before controller execution.
 *
 * @package     Tracer Study
 * @subpackage  Hooks
 * @category    Hooks
 * @author      Tracer Study Team
 */

class AuthHook
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
     * Check if user is authenticated
     *
     * This hook runs before controller execution.
     * It checks if the current page requires authentication.
     *
     * @return void
     */
    public function check_auth()
    {
        // Get current class and method
        $class = $this->CI->router->class;
        $method = $this->CI->router->method;

        // Define public pages that don't require authentication
        $public_pages = array(
            'auth' => array('login', 'register', 'forgot_password', 'reset_password'),
            'welcome' => array('index')
        );

        // Check if current page is public
        if (isset($public_pages[$class]) && in_array($method, $public_pages[$class])) {
            return;
        }

        // Check if user is logged in
        $is_logged_in = $this->CI->session->has_userdata('user_id');

        if (!$is_logged_in) {
            // For API requests, return JSON response
            if ($this->CI->input->is_ajax_request() || strpos($this->CI->uri->uri_string(), 'api/') !== FALSE) {
                $this->CI->output
                    ->set_status_header(401)
                    ->set_content_type('application/json')
                    ->set_output(json_encode(array(
                        'status' => 'error',
                        'message' => 'Unauthorized. Please login first.'
                    )));
                exit;
            }

            // Redirect to login page
            $this->CI->session->set_flashdata('info', 'Silakan login untuk mengakses halaman ini.');
            redirect('auth/login');
        }
    }

    /**
     * Set user data for views
     *
     * This hook runs after controller constructor.
     * It makes user data available to all views.
     *
     * @return void
     */
    public function set_user_data()
    {
        if ($this->CI->session->has_userdata('user_id')) {
            $user_data = $this->CI->session->userdata('user_data');

            // Make user data available to all views
            $this->CI->load->vars(array(
                'current_user' => $user_data,
                'user_id' => $this->CI->session->userdata('user_id'),
                'user_role' => $this->CI->session->userdata('role'),
                'is_logged_in' => TRUE
            ));
        } else {
            $this->CI->load->vars(array(
                'current_user' => NULL,
                'user_id' => NULL,
                'user_role' => NULL,
                'is_logged_in' => FALSE
            ));
        }
    }

    /**
     * Check user role/permission
     *
     * @param array $roles Allowed roles
     * @return bool
     */
    public function check_role($roles = array())
    {
        if (!is_array($roles)) {
            $roles = array($roles);
        }

        $user_role = $this->CI->session->userdata('role');

        if (empty($roles) || in_array($user_role, $roles)) {
            return TRUE;
        }

        return FALSE;
    }
}

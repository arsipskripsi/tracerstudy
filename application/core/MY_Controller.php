<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * MY_Controller - Base Controller for HMVC
 *
 * This is the base controller that all other controllers will extend.
 * It provides common functionality across the application.
 *
 * @package     Tracer Study
 * @subpackage  Core
 * @category    Core
 * @author      Tracer Study Team
 */

class MY_Controller extends CI_Controller
{
    /**
     * Data yang akan dikirim ke view
     * @var array
     */
    protected $data = array();

    /**
     * Status autentikasi user
     * @var bool
     */
    protected $is_logged_in = FALSE;

    /**
     * Data user yang login
     * @var object|null
     */
    protected $user_data = NULL;

    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();

        // Load common libraries
        $this->load->library('session');
        $this->load->helper(array('url', 'form', 'security'));

        // Check if user is logged in
        $this->is_logged_in = $this->session->has_userdata('user_id');

        if ($this->is_logged_in) {
            $this->user_data = $this->session->userdata('user_data');
        }

        // Set default data for views
        $this->data['base_url'] = base_url();
        $this->data['site_title'] = 'Sistem Tracer Study v3.1';
        $this->data['current_year'] = date('Y');
        $this->data['is_logged_in'] = $this->is_logged_in;
        $this->data['user_data'] = $this->user_data;

        // CSRF Protection
        if (config_item('csrf_protection') === TRUE) {
            $this->security->csrf_verify();
        }
    }

    /**
     * Render view dengan layout
     *
     * @param string $view Nama file view
     * @param array  $data Data untuk view
     * @param bool   $return Jika TRUE, return sebagai string
     * @return string|void
     */
    protected function render($view, $data = array(), $return = FALSE)
    {
        $this->data = array_merge($this->data, $data);

        if ($return) {
            return $this->load->view($view, $this->data, TRUE);
        }

        $this->load->view($view, $this->data);
    }

    /**
     * Redirect dengan pesan flashdata
     *
     * @param string $url URL tujuan
     * @param string $message Pesan flashdata
     * @param string $type Tipe pesan (success, error, warning, info)
     * @return void
     */
    protected function redirect_with_message($url, $message, $type = 'success')
    {
        $this->session->set_flashdata('message', $message);
        $this->session->set_flashdata('message_type', $type);
        redirect($url);
    }

    /**
     * Format response JSON
     *
     * @param mixed  $data Data response
     * @param string $status Status response
     * @param string $message Pesan response
     * @return void
     */
    protected function json_response($data = NULL, $status = 'success', $message = '')
    {
        $response = array(
            'status' => $status,
            'message' => $message,
            'data' => $data
        );

        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode($response));
    }

    /**
     * Upload file helper
     *
     * @param string $field Nama field input
     * @param string $path Path upload
     * @param array  $config Konfigurasi upload
     * @return array|bool
     */
    protected function upload_file($field, $path, $config = array())
    {
        $default_config = array(
            'upload_path'   => FCPATH . 'public/uploads/' . $path,
            'allowed_types' => 'gif|jpg|png|jpeg|pdf|doc|docx|xls|xlsx',
            'max_size'      => 2048,
            'encrypt_name'  => TRUE
        );

        $config = array_merge($default_config, $config);

        // Create directory if not exists
        if (!file_exists($config['upload_path'])) {
            mkdir($config['upload_path'], 0755, TRUE);
        }

        $this->load->library('upload', $config);

        if ($this->upload->do_upload($field)) {
            return $this->upload->data();
        }

        return array('error' => $this->upload->display_errors());
    }
}

/**
 * Admin_Controller - Base Controller untuk Admin Area
 */
class Admin_Controller extends MY_Controller
{
    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();

        // Check if user is admin
        if (!$this->is_logged_in) {
            redirect('auth/login');
        }

        // Check user role (implementasi sesuai kebutuhan)
        if (!isset($this->user_data->role) || $this->user_data->role !== 'admin') {
            show_error('Akses ditolak. Anda tidak memiliki hak akses ke halaman ini.', 403);
        }

        // Load admin specific libraries/helpers
        $this->data['page_title'] = 'Admin Panel';
    }
}

/**
 * Public_Controller - Base Controller untuk Public Area
 */
class Public_Controller extends MY_Controller
{
    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();

        // Public area - no authentication required
        $this->data['page_title'] = 'Public Area';
    }
}

/**
 * API_Controller - Base Controller untuk API
 */
class API_Controller extends MY_Controller
{
    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();

        // API specific initialization
        $this->output->set_content_type('application/json');
    }

    /**
     * Override json_response untuk API
     */
    protected function json_response($data = NULL, $status = 'success', $message = '', $code = 200)
    {
        $response = array(
            'status' => $status,
            'message' => $message,
            'data' => $data
        );

        $this->output
            ->set_status_header($code)
            ->set_content_type('application/json')
            ->set_output(json_encode($response));
    }
}

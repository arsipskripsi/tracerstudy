<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * System Settings Model
 *
 * Mengelola konfigurasi sistem secara dinamis.
 * Mendukung berbagai tipe data: string, number, boolean, json, file
 *
 * Fitur:
 * - Get/Set settings by key
 * - Caching untuk performa
 * - Group settings by category
 * - Type casting otomatis
 *
 * @package Tracer Study
 * @subpackage Models
 */
class System_settings_model extends MY_Model {

    protected $table_name = 'system_settings';
    protected $primary_key = 'id';
    protected $soft_delete = FALSE;

    // Cache untuk menyimpan settings yang sudah di-load
    private static $cache = NULL;

    // Fillable fields
    protected $fillable = [
        'setting_key',
        'setting_value',
        'setting_type',
        'category',
        'label',
        'description',
        'is_public'
    ];

    public function __construct()
    {
        parent::__construct();
        $this->load->database();
    }

    /**
     * Load all settings into cache
     *
     * @param bool $force_refresh Force reload from database
     * @return void
     */
    private function loadCache($force_refresh = FALSE)
    {
        if (self::$cache === NULL || $force_refresh) {
            $query = $this->db->get('system_settings');
            self::$cache = [];

            foreach ($query->result_array() as $row) {
                self::$cache[$row['setting_key']] = $row;
            }
        }
    }

    /**
     * Get setting value by key
     *
     * @param string $key Setting key
     * @param mixed $default Default value if not found
     * @return mixed Setting value (auto-casted to type) or default
     */
    public function get($key, $default = NULL)
    {
        $this->loadCache();

        if (!isset(self::$cache[$key])) {
            return $default;
        }

        $setting = self::$cache[$key];
        return $this->castValue($setting['setting_value'], $setting['setting_type']);
    }

    /**
     * Get setting with full details
     *
     * @param string $key Setting key
     * @return object|NULL Setting row or NULL if not found
     */
    public function getDetail($key)
    {
        $this->loadCache();

        if (!isset(self::$cache[$key])) {
            return NULL;
        }

        return (object)self::$cache[$key];
    }

    /**
     * Set/update setting value
     *
     * @param string $key Setting key
     * @param mixed $value Setting value
     * @return bool TRUE if successful
     */
    public function set($key, $value)
    {
        // Check if setting exists
        $existing = $this->getDetail($key);

        if ($existing) {
            // Update existing
            $data = [
                'setting_value' => is_array($value) || is_object($value) ? json_encode($value) : $value,
                'updated_at' => date('Y-m-d H:i:s')
            ];

            $this->db->where('setting_key', $key);
            $success = $this->db->update('system_settings', $data);
        } else {
            // Create new
            $data = [
                'setting_key' => $key,
                'setting_value' => is_array($value) || is_object($value) ? json_encode($value) : $value,
                'setting_type' => is_bool($value) ? 'boolean' : (is_numeric($value) ? 'number' : 'string'),
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ];

            $success = $this->db->insert('system_settings', $data);
        }

        // Refresh cache on success
        if ($success) {
            self::$cache = NULL;
            $this->loadCache(TRUE);
        }

        return $success;
    }

    /**
     * Cast value according to setting type
     *
     * @param mixed $value Raw value
     * @param string $type Setting type
     * @return mixed Casted value
     */
    private function castValue($value, $type)
    {
        if ($value === NULL) {
            return NULL;
        }

        switch ($type) {
            case 'boolean':
                return in_array(strtolower($value), ['1', 'true', 'yes', 'on']) ? TRUE : FALSE;

            case 'number':
                return is_numeric($value) ? floatval($value) : 0;

            case 'json':
                $decoded = json_decode($value, TRUE);
                return json_last_error() === JSON_ERROR_NONE ? $decoded : $value;

            case 'string':
            default:
                return (string)$value;
        }
    }

    /**
     * Get all settings by category
     *
     * @param string $category Category name
     * @return array Settings in category (key => value format)
     */
    public function getByCategory($category)
    {
        $this->loadCache();

        $result = [];
        foreach (self::$cache as $key => $setting) {
            if ($setting['category'] === $category) {
                $result[$key] = $this->castValue($setting['setting_value'], $setting['setting_type']);
            }
        }

        return $result;
    }

    /**
     * Get all categories
     *
     * @return array List of unique categories
     */
    public function getCategories()
    {
        $this->loadCache();

        $categories = [];
        foreach (self::$cache as $setting) {
            if (!in_array($setting['category'], $categories)) {
                $categories[] = $setting['category'];
            }
        }

        sort($categories);
        return $categories;
    }

    /**
     * Get public settings (is_public = 1)
     *
     * @return array Public settings (key => value)
     */
    public function getPublicSettings()
    {
        $this->loadCache();

        $result = [];
        foreach (self::$cache as $key => $setting) {
            if ($setting['is_public'] == 1) {
                $result[$key] = $this->castValue($setting['setting_value'], $setting['setting_type']);
            }
        }

        return $result;
    }

    /**
     * Get settings for admin panel
     * Grouped by category with full details
     *
     * @return array Settings grouped by category
     */
    public function getAdminSettings()
    {
        $this->loadCache();

        $grouped = [];
        foreach (self::$cache as $key => $setting) {
            $category = $setting['category'];

            if (!isset($grouped[$category])) {
                $grouped[$category] = [];
            }

            $grouped[$category][] = [
                'key' => $setting['setting_key'],
                'value' => $this->castValue($setting['setting_value'], $setting['setting_type']),
                'raw_value' => $setting['setting_value'],
                'type' => $setting['setting_type'],
                'label' => $setting['label'],
                'description' => $setting['description'],
                'is_public' => $setting['is_public'] == 1
            ];
        }

        // Sort each category by label
        foreach ($grouped as &$settings) {
            usort($settings, function($a, $b) {
                return strcmp($a['label'], $b['label']);
            });
        }

        return $grouped;
    }

    /**
     * Update multiple settings at once
     *
     * @param array $settings Array of key => value pairs
     * @return int Number of settings updated
     */
    public function updateBatch($settings)
    {
        $count = 0;

        foreach ($settings as $key => $value) {
            if ($this->set($key, $value)) {
                $count++;
            }
        }

        return $count;
    }

    /**
     * Delete a setting
     *
     * @param string $key Setting key
     * @return bool TRUE if successful
     */
    public function deleteByKey($key)
    {
        $this->db->where('setting_key', $key);
        $success = $this->db->delete('system_settings');

        if ($success) {
            self::$cache = NULL;
            $this->loadCache(TRUE);
        }

        return $success;
    }

    /**
     * Check if a setting exists
     *
     * @param string $key Setting key
     * @return bool TRUE if exists
     */
    public function exists($key)
    {
        $this->loadCache();
        return isset(self::$cache[$key]);
    }

    /**
     * Get app configuration helper
     * Shortcut untuk common app settings
     *
     * @return array App configuration
     */
    public function getAppConfig()
    {
        return [
            'name' => $this->get('app_name', 'Tracer Study System'),
            'short_name' => $this->get('app_short_name', 'Tracer Study'),
            'version' => $this->get('app_version', '1.0.0'),
            'logo_url' => $this->get('logo_url', '/assets/images/logo.png'),
            'favicon_url' => $this->get('favicon_url', '/assets/images/favicon.ico'),
            'footer_text' => $this->get('footer_text', '© 2024 Universitas - Tracer Study System'),
            'primary_color' => $this->get('primary_color', '#4e73df'),
            'maintenance_mode' => $this->get('maintenance_mode', FALSE),
            'registration_open' => $this->get('registration_open', TRUE)
        ];
    }

    /**
     * Get contact information
     *
     * @return array Contact details
     */
    public function getContactInfo()
    {
        return [
            'institution_name' => $this->get('institution_name', 'Universitas'),
            'address' => $this->get('institution_address', ''),
            'phone' => $this->get('institution_phone', ''),
            'email' => $this->get('institution_email', ''),
            'website' => $this->get('institution_website', ''),
            'admin_email' => $this->get('admin_email', ''),
            'whatsapp' => $this->get('support_whatsapp', '')
        ];
    }

    /**
     * Get email configuration
     *
     * @return array Email SMTP config
     */
    public function getEmailConfig()
    {
        return [
            'smtp_host' => $this->get('smtp_host', ''),
            'smtp_port' => $this->get('smtp_port', 587),
            'smtp_user' => $this->get('smtp_user', ''),
            'smtp_encryption' => $this->get('smtp_encryption', 'tls'),
            'from_name' => $this->get('email_from_name', 'Tracer Study'),
            'from_address' => $this->get('email_from_address', 'noreply@university.ac.id')
        ];
    }
}
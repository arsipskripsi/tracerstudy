<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * MY_Model - Base Model for HMVC
 *
 * This is the base model that all other models will extend.
 * It provides common database operations and utilities.
 *
 * @package     Tracer Study
 * @subpackage  Core
 * @category    Core
 * @author      Tracer Study Team
 */

class MY_Model extends CI_Model
{
    /**
     * Nama tabel database
     * @var string
     */
    protected $table_name = '';

    /**
     * Primary key tabel
     * @var string
     */
    protected $primary_key = 'id';

    /**
     * Field-field yang dapat di-set melalui create/update
     * @var array
     */
    protected $fillable = array();

    /**
     * Field-field yang tidak dapat di-set
     * @var array
     */
    protected $guarded = array();

    /**
     * Format timestamp (created_at, updated_at)
     * @var bool
     */
    protected $timestamps = TRUE;

    /**
     * Field untuk created_at
     * @var string
     */
    protected $created_field = 'created_at';

    /**
     * Field untuk updated_at
     * @var string
     */
    protected $updated_field = 'updated_at';

    /**
     * Field untuk deleted_at (soft delete)
     * @var string
     */
    protected $deleted_field = 'deleted_at';

    /**
     * Enable soft delete
     * @var bool
     */
    protected $soft_delete = FALSE;

    /**
     * Return type: 'array', 'object', 'json'
     * @var string
     */
    protected $return_type = 'object';

    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();
        $this->load->database();
    }

    // =========================================================================
    // READ OPERATIONS
    // =========================================================================

    /**
     * Get all records
     *
     * @param array $where Condition WHERE
     * @param string $order_by Order by field
     * @param string $order Direction (ASC/DESC)
     * @param int $limit Limit records
     * @param int $offset Offset records
     * @return mixed
     */
    public function get_all($where = array(), $order_by = NULL, $order = 'ASC', $limit = NULL, $offset = NULL)
    {
        if ($this->soft_delete) {
            $this->db->where($this->deleted_field, NULL);
        }

        if (!empty($where)) {
            $this->db->where($where);
        }

        if ($order_by) {
            $this->db->order_by($order_by, $order);
        }

        if ($limit) {
            $this->db->limit($limit, $offset);
        }

        $query = $this->db->get($this->table_name);

        return $this->_format_result($query);
    }

    /**
     * Get single record by ID
     *
     * @param mixed $id Primary key value
     * @return mixed
     */
    public function get_by_id($id)
    {
        if ($this->soft_delete) {
            $this->db->where($this->deleted_field, NULL);
        }

        $query = $this->db->get_where($this->table_name, array($this->primary_key => $id));

        return $query->row($this->return_type);
    }

    /**
     * Get single record by condition
     *
     * @param array $where Condition WHERE
     * @return mixed
     */
    public function get_by($where = array())
    {
        if ($this->soft_delete) {
            $this->db->where($this->deleted_field, NULL);
        }

        $query = $this->db->get_where($this->table_name, $where);

        return $query->row($this->return_type);
    }

    /**
     * Count records
     *
     * @param array $where Condition WHERE
     * @return int
     */
    public function count($where = array())
    {
        if ($this->soft_delete) {
            $this->db->where($this->deleted_field, NULL);
        }

        if (!empty($where)) {
            $this->db->where($where);
        }

        return $this->db->count_all_results($this->table_name);
    }

    /**
     * Check if record exists
     *
     * @param array $where Condition WHERE
     * @return bool
     */
    public function exists($where = array())
    {
        return $this->count($where) > 0;
    }

    // =========================================================================
    // CREATE OPERATIONS
    // =========================================================================

    /**
     * Insert new record
     *
     * @param array $data Data to insert
     * @param bool $return_id Return insert ID
     * @return mixed
     */
    public function insert($data, $return_id = TRUE)
    {
        $data = $this->_prepare_data($data, 'insert');

        $this->db->insert($this->table_name, $data);

        if ($return_id) {
            return $this->db->insert_id();
        }

        return $this->db->affected_rows() > 0;
    }

    /**
     * Insert multiple records
     *
     * @param array $data Array of data to insert
     * @return bool
     */
    public function insert_batch($data)
    {
        $prepared = array();

        foreach ($data as $row) {
            $prepared[] = $this->_prepare_data($row, 'insert');
        }

        return $this->db->insert_batch($this->table_name, $prepared);
    }

    // =========================================================================
    // UPDATE OPERATIONS
    // =========================================================================

    /**
     * Update record by ID
     *
     * @param mixed $id Primary key value
     * @param array $data Data to update
     * @return bool
     */
    public function update($id, $data)
    {
        $data = $this->_prepare_data($data, 'update');

        $this->db->where($this->primary_key, $id);

        return $this->db->update($this->table_name, $data);
    }

    /**
     * Update records by condition
     *
     * @param array $where Condition WHERE
     * @param array $data Data to update
     * @return bool
     */
    public function update_by($where, $data)
    {
        $data = $this->_prepare_data($data, 'update');

        $this->db->where($where);

        return $this->db->update($this->table_name, $data);
    }

    /**
     * Update multiple records by IDs
     *
     * @param array $ids Array of primary key values
     * @param array $data Data to update
     * @return bool
     */
    public function update_many($ids, $data)
    {
        $data = $this->_prepare_data($data, 'update');

        $this->db->where_in($this->primary_key, $ids);

        return $this->db->update($this->table_name, $data);
    }

    // =========================================================================
    // DELETE OPERATIONS
    // =========================================================================

    /**
     * Delete record by ID
     *
     * @param mixed $id Primary key value
     * @return bool
     */
    public function delete($id)
    {
        if ($this->soft_delete) {
            return $this->_soft_delete($id);
        }

        $this->db->where($this->primary_key, $id);

        return $this->db->delete($this->table_name);
    }

    /**
     * Delete records by condition
     *
     * @param array $where Condition WHERE
     * @return bool
     */
    public function delete_by($where)
    {
        if ($this->soft_delete) {
            return $this->_soft_delete_by($where);
        }

        $this->db->where($where);

        return $this->db->delete($this->table_name);
    }

    /**
     * Delete multiple records by IDs
     *
     * @param array $ids Array of primary key values
     * @return bool
     */
    public function delete_many($ids)
    {
        if ($this->soft_delete) {
            $this->db->where_in($this->primary_key, $ids);
            $this->db->where($this->deleted_field, NULL);

            $data = array($this->deleted_field => date('Y-m-d H:i:s'));

            return $this->db->update($this->table_name, $data);
        }

        $this->db->where_in($this->primary_key, $ids);

        return $this->db->delete($this->table_name);
    }

    /**
     * Restore soft-deleted record
     *
     * @param mixed $id Primary key value
     * @return bool
     */
    public function restore($id)
    {
        if (!$this->soft_delete) {
            return FALSE;
        }

        $data = array($this->deleted_field => NULL);

        $this->db->where($this->primary_key, $id);

        return $this->db->update($this->table_name, $data);
    }

    /**
     * Get trashed (soft-deleted) records
     *
     * @param array $where Condition WHERE
     * @return mixed
     */
    public function only_trashed($where = array())
    {
        if (!$this->soft_delete) {
            return array();
        }

        $this->db->where_not_null($this->deleted_field);

        if (!empty($where)) {
            $this->db->where($where);
        }

        $query = $this->db->get($this->table_name);

        return $this->_format_result($query);
    }

    /**
     * Include trashed records in query
     *
     * @return MY_Model
     */
    public function with_trashed()
    {
        // Temporary disable soft delete filter
        $this->db->ignore_soft_delete = TRUE;

        return $this;
    }

    // =========================================================================
    // HELPER METHODS
    // =========================================================================

    /**
     * Prepare data for insert/update
     *
     * @param array $data Input data
     * @param string $type Operation type (insert/update)
     * @return array
     */
    protected function _prepare_data($data, $type = 'insert')
    {
        // Filter fillable fields
        if (!empty($this->fillable)) {
            $filtered = array();

            foreach ($this->fillable as $field) {
                if (isset($data[$field])) {
                    $filtered[$field] = $data[$field];
                }
            }

            $data = $filtered;
        }

        // Guard protected fields
        if (!empty($this->guarded)) {
            foreach ($this->guarded as $field) {
                unset($data[$field]);
            }
        }

        // Add timestamps
        if ($this->timestamps) {
            $now = date('Y-m-d H:i:s');

            if ($type === 'insert') {
                $data[$this->created_field] = $now;
                $data[$this->updated_field] = $now;
            } elseif ($type === 'update') {
                $data[$this->updated_field] = $now;
            }
        }

        return $data;
    }

    /**
     * Soft delete implementation
     *
     * @param mixed $id Primary key value
     * @return bool
     */
    private function _soft_delete($id)
    {
        $data = array($this->deleted_field => date('Y-m-d H:i:s'));

        $this->db->where($this->primary_key, $id);
        $this->db->where($this->deleted_field, NULL);

        return $this->db->update($this->table_name, $data);
    }

    /**
     * Soft delete by condition
     *
     * @param array $where Condition WHERE
     * @return bool
     */
    private function _soft_delete_by($where)
    {
        $data = array($this->deleted_field => date('Y-m-d H:i:s'));

        $this->db->where($where);
        $this->db->where($this->deleted_field, NULL);

        return $this->db->update($this->table_name, $data);
    }

    /**
     * Format query result based on return_type
     *
     * @param object $query CI_DB_result
     * @return mixed
     */
    private function _format_result($query)
    {
        if ($this->return_type === 'json') {
            return json_encode($query->result_array());
        }

        if ($this->return_type === 'array') {
            return $query->result_array();
        }

        return $query->result();
    }

    /**
     * Set return type
     *
     * @param string $type Return type (array/object/json)
     * @return MY_Model
     */
    public function set_return_type($type)
    {
        $this->return_type = $type;

        return $this;
    }

    /**
     * Begin database transaction
     *
     * @return bool
     */
    public function begin_transaction()
    {
        return $this->db->trans_begin();
    }

    /**
     * Commit database transaction
     *
     * @return bool
     */
    public function commit_transaction()
    {
        $this->db->trans_commit();

        return $this->db->trans_status();
    }

    /**
     * Rollback database transaction
     *
     * @return bool
     */
    public function rollback_transaction()
    {
        $this->db->trans_rollback();

        return TRUE;
    }
}

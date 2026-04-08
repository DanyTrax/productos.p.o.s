<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Expence_model extends CI_Model
{

    var $table = 'zarest_expences';

    /** Columnas buscables (tabla zarest_expences) */
    var $column = array(
        'date',
        'reference',
        'amount',
        'category_id',
        'store_id',
        'created_by',
    );

    /** Índice DataTables (thead) → columna SQL para ORDER BY (sin columna Acción) */
    var $order_columns = array(
        'date',
        'reference',
        'amount',
        'category_id',
        'store_id',
        'created_by',
    );

    var $order = array(
        'created_date' => 'desc'
    );

    public function __construct()
    {
        parent::__construct();
        $this->load->database();
    }

    private function _get_datatables_query()
    {
        $this->db->from($this->table);

        $search = '';
        if (isset($_POST['search']['value']) && $_POST['search']['value'] !== '') {
            $search = ltrim($_POST['search']['value'], '0');
        }

        if ($search !== '') {
            $first = true;
            foreach ($this->column as $item) {
                if ($first) {
                    $this->db->like($item, $search);
                    $first = false;
                } else {
                    $this->db->or_like($item, $search);
                }
            }
        }

        if (isset($_POST['order'][0]['column'], $_POST['order'][0]['dir'])) {
            $idx = (int) $_POST['order'][0]['column'];
            $dir = strtolower($_POST['order'][0]['dir']) === 'asc' ? 'asc' : 'desc';
            if ($idx >= 0 && $idx < count($this->order_columns)) {
                $this->db->order_by($this->order_columns[$idx], $dir);
            } elseif (isset($this->order)) {
                $order = $this->order;
                $this->db->order_by(key($order), $order[key($order)]);
            }
        } elseif (isset($this->order)) {
            $order = $this->order;
            $this->db->order_by(key($order), $order[key($order)]);
        }
    }

    function get_datatables()
    {
        $this->_get_datatables_query();
        $length = isset($_POST['length']) ? (int) $_POST['length'] : 10;
        $start = isset($_POST['start']) ? (int) $_POST['start'] : 0;
        if ($length !== -1) {
            $this->db->limit($length, $start);
        }
        $query = $this->db->get();
        return $query->result();
    }

    function count_filtered()
    {
        $this->_get_datatables_query();
        $query = $this->db->get();
        return $query->num_rows();
    }

    public function count_all()
    {
        $this->db->from($this->table);
        return $this->db->count_all_results();
    }

    public function get_by_id($id)
    {
        $this->db->from($this->table);
        $this->db->where('id', $id);
        $query = $this->db->get();

        return $query->row();
    }

    public function save($data)
    {
        $this->db->insert($this->table, $data);
        return $this->db->insert_id();
    }

    public function update($where, $data)
    {
        $this->db->update($this->table, $data, $where);
        return $this->db->affected_rows();
    }

    public function delete_by_id($id)
    {
        $this->db->where('id', $id);
        $this->db->delete($this->table);
    }
}

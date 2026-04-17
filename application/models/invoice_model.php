<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Invoice_model extends CI_Model
{

    var $table = 'zarest_sales';

    /** Índices alineados con las columnas de la tabla DataTables (servidor). */
    var $column = array(
        'id',
        'clientname',
        'created_at',
        'tax',
        'discount',
        'total',
        'created_by',
        'totalitems',
        'paidmethod',
        'status',
    );

    var $order = array(
        'id' => 'desc',
    );

    public function __construct()
    {
        parent::__construct();
        $this->load->database();
    }

    /**
     * Filtro por rango de fechas (POST date_start / date_end en Y-m-d).
     */
    private function _apply_date_range()
    {
        $ds = isset($_POST['date_start']) ? trim((string) $_POST['date_start']) : '';
        $de = isset($_POST['date_end']) ? trim((string) $_POST['date_end']) : '';
        if ($ds !== '' && $de !== ''
            && preg_match('/^\d{4}-\d{2}-\d{2}$/', $ds)
            && preg_match('/^\d{4}-\d{2}-\d{2}$/', $de)
        ) {
            $this->db->where('DATE(created_at) >=', $ds);
            $this->db->where('DATE(created_at) <=', $de);
        }
    }

    /**
     * Misma clave que guarda el POS en paidmethod (primer segmento antes de ~).
     * Solo aplica filtro si la clave es no vacía y numérica (no altera consultas si no se envía).
     *
     * @param string|null $explicit Si no es null, usa este valor en lugar de POST.
     */
    private function _apply_payment_method_filter($explicit = null)
    {
        $key = $explicit !== null ? trim((string) $explicit) : '';
        if ($key === '' && isset($_POST['payment_method_key'])) {
            $key = trim((string) $_POST['payment_method_key']);
        }
        if ($key === '' || strcasecmp($key, 'all') === 0) {
            return;
        }
        if (! preg_match('/^[0-9]+$/', $key)) {
            return;
        }
        $this->db->where(
            'SUBSTRING_INDEX(TRIM(IFNULL(paidmethod, \'\')), \'~\', 1) = ' . $this->db->escape($key),
            null,
            false
        );
    }

    private function _get_datatables_query()
    {
        $this->db->from($this->table);
        $this->_apply_date_range();
        $this->_apply_payment_method_filter();

        $column = $this->column;
        $searchVal = isset($_POST['search']['value']) ? trim((string) $_POST['search']['value']) : '';
        if ($searchVal !== '') {
            $searchVal = ltrim($searchVal, '0');
            $i = 0;
            foreach ($this->column as $item) {
                ($i === 0) ? $this->db->like($item, $searchVal) : $this->db->or_like($item, $searchVal);
                $i ++;
            }
        }

        if (isset($_POST['order']) && isset($_POST['order']['0']['column'])) {
            $idx = (int) $_POST['order']['0']['column'];
            $dir = isset($_POST['order']['0']['dir']) ? $_POST['order']['0']['dir'] : 'desc';
            if (isset($column[$idx])) {
                $this->db->order_by($column[$idx], $dir === 'asc' ? 'asc' : 'desc');
            }
        } else {
            if (isset($this->order)) {
                $order = $this->order;
                $this->db->order_by(key($order), $order[key($order)]);
            }
        }
    }

    function get_datatables()
    {
        $this->_get_datatables_query();
        if ($_POST['length'] != - 1) {
            $this->db->limit($_POST['length'], $_POST['start']);
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

    /**
     * Total de filas con el mismo filtro de fechas que la lista (sin búsqueda de texto).
     */
    public function count_all()
    {
        $this->db->from($this->table);
        $this->_apply_date_range();
        $this->_apply_payment_method_filter();

        return $this->db->count_all_results();
    }

    /**
     * Todas las ventas en rango (y búsqueda opcional) para exportar PDF/CSV.
     *
     * @param string $date_start Y-m-d
     * @param string $date_end   Y-m-d
     * @param string $search     mismo criterio que DataTables search
     * @param string $payment_method_key misma clave que POST payment_method_key (opcional)
     * @return array
     */
    public function get_sales_for_export($date_start, $date_end, $search = '', $payment_method_key = '')
    {
        $this->db->from($this->table);
        $ds = trim((string) $date_start);
        $de = trim((string) $date_end);
        if ($ds !== '' && $de !== ''
            && preg_match('/^\d{4}-\d{2}-\d{2}$/', $ds)
            && preg_match('/^\d{4}-\d{2}-\d{2}$/', $de)
        ) {
            $this->db->where('DATE(created_at) >=', $ds);
            $this->db->where('DATE(created_at) <=', $de);
        }
        $this->_apply_payment_method_filter($payment_method_key);
        $searchVal = trim((string) $search);
        if ($searchVal !== '') {
            $searchVal = ltrim($searchVal, '0');
            $i = 0;
            foreach ($this->column as $item) {
                ($i === 0) ? $this->db->like($item, $searchVal) : $this->db->or_like($item, $searchVal);
                $i ++;
            }
        }
        $this->db->order_by('id', 'desc');

        return $this->db->get()->result();
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

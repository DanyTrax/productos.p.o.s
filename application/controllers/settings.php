<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Settings extends MY_Controller
{

    function __construct()
    {
        parent::__construct();
        if (! $this->user) {
            redirect('login');
        }
        if ($this->user->role !== "admin") {
            redirect('');
        }
    }

    public function index()
    {
      $this->view_data['warehouses'] = Warehouse::all();
        $this->view_data['Users'] = User::all();
        $this->view_data['stores'] = Store::all();
        $this->view_data['Timezones'] = $this->tz_list();
        $this->view_data['payment_methods_list'] = $this->fetch_payment_methods();
        $this->content_view = 'setting/setting';
    }

    public function addPaymentMethod()
    {
        $name = trim((string) $this->input->post('name'));
        if ($name === '') {
            redirect('/settings?tab=payment_methods', 'location');
        }
        $type = $this->input->post('type_code');
        $allowed = array('cash', 'card', 'cheque', 'other');
        if (! in_array($type, $allowed, true)) {
            $type = 'other';
        }
        $last = Payment_method::find('first', array('order' => 'sort_order desc'));
        $order = $last ? ((int) $last->sort_order + 1) : 10;
        Payment_method::create(array(
            'name' => $name,
            'type_code' => $type,
            'sort_order' => $order,
            'legacy_key' => null,
        ));
        redirect('/settings?tab=payment_methods', 'location');
    }

    public function updatePaymentMethod()
    {
        $id = (int) $this->input->post('id');
        if ($id < 1) {
            redirect('/settings?tab=payment_methods', 'location');
        }
        $pm = Payment_method::find($id);
        $name = trim((string) $this->input->post('name'));
        if ($name === '') {
            redirect('/settings?tab=payment_methods', 'location');
        }
        $attrs = array(
            'name' => $name,
            'sort_order' => (int) $this->input->post('sort_order'),
        );
        if ($pm->legacy_key === null || $pm->legacy_key === '') {
            $type = $this->input->post('type_code');
            $allowed = array('cash', 'card', 'cheque', 'other');
            if (in_array($type, $allowed, true)) {
                $attrs['type_code'] = $type;
            }
        }
        $pm->update_attributes($attrs);
        redirect('/settings?tab=payment_methods', 'location');
    }

    public function deletePaymentMethod($id)
    {
        $id = (int) $id;
        if ($id < 1) {
            redirect('/settings?tab=payment_methods', 'location');
        }
        $pm = Payment_method::find($id);
        if ($pm->legacy_key === null || $pm->legacy_key === '') {
            $pm->delete();
        }
        redirect('/settings?tab=payment_methods', 'location');
    }

    public function deleteUser($id)
    {
        $user = User::find($id);
        unlink('./files/Avatars/' . $user->avatar);
        $user->delete();
        redirect("/settings?tab=users", "location");
    }

    public function addUser()
    {
        date_default_timezone_set($this->setting->timezone);
        $date = date("Y-m-d H:i:s");
        $role = $this->input->post('role');
        $_POST['can_open_register'] = ($role === 'waiter' && strval($this->input->post('can_open_register')) === '1') ? 1 : 0;
        $_POST['can_close_register'] = ($role === 'waiter' && strval($this->input->post('can_close_register')) === '1') ? 1 : 0;
        $config['upload_path'] = './files/Avatars/';
        $config['encrypt_name'] = TRUE;
        $config['allowed_types'] = 'gif|jpg|jpeg|png';
        $config['max_width'] = '1000';
        $config['max_height'] = '1000';

        $this->load->library('upload', $config);
        if ($this->upload->do_upload()) {
            $data = array(
                'upload_data' => $this->upload->data()
            );
            $image = $data['upload_data']['file_name'];
            $_POST['avatar'] = $image;
            $_POST['created_at'] = $date;
            unset($_POST['PasswordRepeat']);
            $user = User::create($_POST);
            redirect("/settings?tab=users", "location");
        } else {
            $_POST['created_at'] = $date;
            unset($_POST['PasswordRepeat']);
            $user = User::create($_POST);
            redirect("/settings?tab=users", "location");
        }
    }

    public function editUser($id = FALSE)
    {
        date_default_timezone_set($this->setting->timezone);
        $date = date("Y-m-d H:i:s");
        if ($_POST) {
            $role = $this->input->post('role');
            $_POST['can_open_register'] = ($role === 'waiter' && strval($this->input->post('can_open_register')) === '1') ? 1 : 0;
            $_POST['can_close_register'] = ($role === 'waiter' && strval($this->input->post('can_close_register')) === '1') ? 1 : 0;
            $config['upload_path'] = './files/Avatars/';
            $config['encrypt_name'] = TRUE;
            $config['allowed_types'] = 'gif|jpg|jpeg|png';
            $config['max_width'] = '1000';
            $config['max_height'] = '1000';

            $user = User::find($id);

            $this->load->library('upload', $config);
            if ($this->upload->do_upload()) {
                $data = array(
                    'upload_data' => $this->upload->data()
                );
                $image = $data['upload_data']['file_name'];
                unlink('./files/Avatars/' . $user->avatar);
                $_POST['avatar'] = $image;
                $_POST['created_at'] = $date;
                unset($_POST['PasswordRepeat']);
                if ($_POST['password'] === '')
                    unset($_POST['password']);
                $user->update_attributes($_POST);
                redirect("/settings?tab=users", "location");
            } else {
                $_POST['created_at'] = $date;
                unset($_POST['PasswordRepeat']);
                if ($_POST['password'] === '')
                    unset($_POST['password']);
                $user->update_attributes($_POST);
                redirect("/settings?tab=users", "location");
            }
        } else {
          $this->view_data['stores'] = Store::all();
            $this->view_data['user'] = User::find($id);
            $this->content_view = 'setting/modifyUser';
        }
    }

    // Settings
    /**
     * Borra archivos de caché de CodeIgniter (application/cache, system/cache) y
     * intenta reiniciar OPcache si está activo. Solo POST, solo admin.
     */
    public function purgeCache()
    {
        if (strtolower($this->input->server('REQUEST_METHOD')) !== 'post') {
            redirect('settings?tab=system', 'location');
            return;
        }
        $removed = $this->_purge_ci_cache_files();
        $opcache_ok = false;
        if (function_exists('opcache_reset')) {
            $st = @opcache_get_status(false);
            if (is_array($st) && ! empty($st['opcache_enabled'])) {
                $opcache_ok = @opcache_reset();
            }
        }
        $this->session->set_flashdata('cache_purge_result', array(
            'files' => (int) $removed,
            'opcache' => (bool) $opcache_ok,
        ));
        redirect('settings?tab=system', 'location');
    }

    /**
     * Ejecuta scripts SQL de actualización (idempotentes) desde application/sql.
     * Solo POST, solo admin.
     */
    public function applySqlUpdates()
    {
        if (strtolower($this->input->server('REQUEST_METHOD')) !== 'post') {
            redirect('settings?tab=system', 'location');
            return;
        }
        $files = array(
            APPPATH . 'sql' . DIRECTORY_SEPARATOR . 'zarest_users_register_permissions.sql',
        );
        $result = array(
            'applied' => 0,
            'ignored' => 0,
            'failed' => 0,
            'messages' => array(),
        );
        foreach ($files as $filePath) {
            $r = $this->_run_sql_update_file($filePath);
            $result['applied'] += (int) $r['applied'];
            $result['ignored'] += (int) $r['ignored'];
            $result['failed'] += (int) $r['failed'];
            if (! empty($r['messages'])) {
                $result['messages'] = array_merge($result['messages'], $r['messages']);
            }
        }
        $this->session->set_flashdata('sql_update_result', $result);
        redirect('settings?tab=system', 'location');
    }

    /**
     * @return int número de archivos eliminados
     */
    private function _purge_ci_cache_files()
    {
        $removed = 0;
        $roots = array(
            rtrim(APPPATH, '/\\') . DIRECTORY_SEPARATOR . 'cache',
            rtrim(BASEPATH, '/\\') . DIRECTORY_SEPARATOR . 'cache',
        );
        $keepNames = array('index.html', '.htaccess', 'index.php');
        foreach ($roots as $root) {
            if (! is_dir($root)) {
                continue;
            }
            $this->_purge_cache_directory($root, $keepNames, $removed);
        }

        return $removed;
    }

    private function _purge_cache_directory($dir, array $keepNames, &$removed)
    {
        $items = @scandir($dir);
        if ($items === false) {
            return;
        }
        foreach ($items as $name) {
            if ($name === '.' || $name === '..') {
                continue;
            }
            if (in_array($name, $keepNames, true)) {
                continue;
            }
            $path = $dir . DIRECTORY_SEPARATOR . $name;
            if (is_file($path)) {
                if (@unlink($path)) {
                    $removed++;
                }
            } elseif (is_dir($path)) {
                $this->_purge_cache_directory($path, array(), $removed);
                @rmdir($path);
            }
        }
    }

    private function _run_sql_update_file($filePath)
    {
        $out = array(
            'applied' => 0,
            'ignored' => 0,
            'failed' => 0,
            'messages' => array(),
        );
        if (! is_file($filePath)) {
            $out['failed'] = 1;
            $out['messages'][] = 'No existe el archivo SQL: ' . $filePath;
            return $out;
        }
        $sql = @file_get_contents($filePath);
        if ($sql === false || trim($sql) === '') {
            $out['failed'] = 1;
            $out['messages'][] = 'Archivo SQL vacío o no legible: ' . $filePath;
            return $out;
        }
        $statements = preg_split('/;\s*(?:\r\n|\r|\n)/', $sql);
        if (! is_array($statements)) {
            $statements = array($sql);
        }
        foreach ($statements as $stmt) {
            $stmt = trim((string) $stmt);
            if ($stmt === '' || strpos($stmt, '--') === 0) {
                continue;
            }
            $ok = $this->db->query($stmt);
            if ($ok) {
                $out['applied']++;
                continue;
            }
            $err = $this->db->error();
            $code = isset($err['code']) ? (int) $err['code'] : 0;
            $msg = isset($err['message']) ? strtolower((string) $err['message']) : '';
            $ignorableCodes = array(1050, 1060, 1061, 1091);
            $isIgnorableText = (strpos($msg, 'duplicate') !== false) || (strpos($msg, 'already exists') !== false) || (strpos($msg, "can't drop") !== false);
            if (in_array($code, $ignorableCodes, true) || $isIgnorableText) {
                $out['ignored']++;
                continue;
            }
            $out['failed']++;
            $out['messages'][] = 'SQL error [' . $code . ']: ' . (isset($err['message']) ? $err['message'] : 'unknown');
        }

        return $out;
    }

    public function updateSettings()
    {
        $config['upload_path'] = './files/Setting/';
        $config['encrypt_name'] = TRUE;
        $config['allowed_types'] = 'gif|jpg|jpeg|png';
        $config['max_width'] = '1000';
        $config['max_height'] = '1000';

        $setting = Setting::find(1);

        $d = $this->input->post('decimals');
        $_POST['decimals'] = ($d === null || $d === '') ? 2 : max(0, min(3, (int) $d));

        $this->load->library('upload', $config);
        if ($this->upload->do_upload()) {
            $data = array(
                'upload_data' => $this->upload->data()
            );
            $image = $data['upload_data']['file_name'];
            unlink('./files/Setting/' . $setting->logo);
            $_POST['logo'] = $image;
            $setting->update_attributes($_POST);
            redirect("/settings?tab=setting", "location");
        } else {
            $setting->update_attributes($_POST);
            redirect("/settings?tab=setting", "location");
        }
    }
}

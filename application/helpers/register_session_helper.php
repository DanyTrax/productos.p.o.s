<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Si la sesión apunta a un registro de caja que ya no existe (p. ej. borrado en BD),
 * limpia register y selectedTable para evitar RecordNotFound y error 500.
 */
function sync_register_session(CI_Controller $CI)
{
    $rid = $CI->session->userdata('register');
    if (! $rid) {
        return;
    }
    $reg = Register::find('first', array(
        'conditions' => array('id = ?', $rid),
    ));
    if (! $reg) {
        $CI->session->unset_userdata('register');
        $CI->session->unset_userdata('selectedTable');
    }
}

<?php

class Payment_method extends ActiveRecord\Model
{
    public static $table_name = 'zarest_payment_methods';

    /**
     * Primer segmento guardado en ventas/pagos (compatible con 0,1,2 histórico o id numérico).
     */
    public function stored_key()
    {
        if ($this->legacy_key !== null && $this->legacy_key !== '') {
            return (string) (int) $this->legacy_key;
        }

        return (string) (int) $this->id;
    }

    public static function find_for_paidmethod($paidmethod)
    {
        if ($paidmethod === null || $paidmethod === '') {
            return null;
        }
        $parts = explode('~', $paidmethod);
        $first = isset($parts[0]) ? $parts[0] : '';
        if ($first === '0' || $first === '1' || $first === '2') {
            return static::find('first', array('conditions' => array('legacy_key = ?', (int) $first)));
        }
        if (ctype_digit((string) $first)) {
            return static::find('first', array('conditions' => array('id = ?', (int) $first)));
        }

        return null;
    }

    /**
     * @return array{type: string, parts: array, display_name: string}
     */
    public static function parse($paidmethod)
    {
        $parts = explode('~', (string) $paidmethod);
        $pm = static::find_for_paidmethod($paidmethod);
        $type = 'cash';
        $name = function_exists('label') ? label('Cash') : 'Cash';
        if ($pm) {
            $type = $pm->type_code;
            $name = $pm->name;
        } else {
            $f = isset($parts[0]) ? $parts[0] : '0';
            if ($f === '1') {
                $type = 'card';
                $name = function_exists('label') ? label('CreditCard') : 'Credit Card';
            } elseif ($f === '2') {
                $type = 'cheque';
                $name = function_exists('label') ? label('Cheque') : 'Cheque';
            } elseif ($f === '0') {
                $type = 'cash';
                $name = function_exists('label') ? label('Cash') : 'Cash';
            } elseif (ctype_digit((string) $f)) {
                $type = 'other';
                $name = 'Medio #' . $f;
            }
        }

        return array('type' => $type, 'parts' => $parts, 'display_name' => $name);
    }

    /** cash | cc | cheque — para cierre de caja (other y cash → efectivo). */
    public static function close_bucket($paidmethod)
    {
        $info = static::parse($paidmethod);
        if ($info['type'] === 'card') {
            return 'cc';
        }
        if ($info['type'] === 'cheque') {
            return 'cheque';
        }

        return 'cash';
    }

    public static function display_label($paidmethod)
    {
        $info = static::parse($paidmethod);

        return $info['display_name'];
    }
}

<?php
defined('BASEPATH') or exit('No direct script access allowed');

require_once (APPPATH . 'third_party/Stripe/Stripe.php');

class Pos extends CI_Controller
{

    function __construct()
    {
        parent::__construct();
        $lang = $this->session->userdata("lang") == null ? "english" : $this->session->userdata("lang");
        $this->lang->load($lang, $lang);
        $this->register = $this->session->userdata('register') ? $this->session->userdata('register') : FALSE;
        $this->store = $this->session->userdata('store') ? $this->session->userdata('store') : FALSE;
        $this->selectedTable = $this->session->userdata('selectedTable') ? $this->session->userdata('selectedTable') : FALSE;

        $this->setting = Setting::find(1);
        date_default_timezone_set($this->setting->timezone);
    }

    public function findproduct($code)
    {
        $product = Product::find('first', array(
            'conditions' => array(
                'code = ?',
                $code
            )
        ));
        echo $product->id;
    }

    public function openregister($id = 0, $userRole = '')
    {
        if ($_POST) {
            $cash = $this->input->post('cash');
            $id = $this->input->post('store');
            $waitersCach = $this->input->post('waitersCach');
            if (! is_array($waitersCach)) {
                $waitersCach = array();
            }
            $waitercc = '';
            foreach ($waitersCach as $key => $value) {
               $waitercc .= $value ? $key.','.$value.',' : '';
            }
            $data = array(
                "status" => 1,
                "user_id" => $this->session->userdata('user_id'),
                "cash_inhand" => $cash,
                "waiterscih" => $waitercc,
                "store_id" => $id
            );
            $register = Register::create($data);

            $store = Store::find($id);
            $store->status = 1;
            $store->save();
            $CI = & get_instance();
            $CI->session->set_userdata('register', $register->id);
            $CI->session->set_userdata('store', $id);
            redirect("", "location");
        }
        $open_reg = Register::find('first', array(
            'conditions' => array(
                'store_id = ? AND status= ?',
                $id,
                1
            )
        ));
        $CI = & get_instance();
        $CI->session->set_userdata('register', $open_reg->id);
        $CI->session->set_userdata('store', $id);
        if($userRole === 'kitchen') {
          redirect("kitchens", "location");
        }else {
          redirect("", "location");
        }
    }

    public function selectTable($id)
    {
      $hold = Hold::find('first', array('conditions' => array('register_id = ? AND table_id = ?', $this->register, $id)));
      if(!$hold){
         $attributes = array(
            'number' => 1,
            'time' => date("H:i"),
            "table_id" => $id,
            'register_id' => $this->register
         );
         Hold::create($attributes);
      }else{
      Posale::update_all(array(
            'set' => array(
               'status' => 1
            ),
            'conditions' => array(
               'number = ? AND register_id = ? AND table_id = ?',
               1,
               $this->register,
               $id
            )
         ));
      }
      if($id > 0){

         $table = Table::find($id);
         if($table->status != 1){
            $table->status = 1;
            $table->time = date("H:i");
            $table->save();
         }
      }
      $CI = & get_instance();
      $CI->session->set_userdata('selectedTable', $id.'h');
      redirect("", "location");

    }

    public function switshregister()
    {
        $CI = & get_instance();
        $CI->session->set_userdata('register', 0);
        $CI->session->set_userdata('store', 0);
        redirect("", "location");
    }

    public function switshtable()
    {
      Posale::update_all(array(
          'set' => array(
             'status' => 0
          ),
          'conditions' => array(
             'status = ? AND register_id = ?',
             1,
             $this->register
          )
      ));
        $CI = & get_instance();
        $CI->session->set_userdata('selectedTable', 0);
        redirect("", "location");
    }

    /** Alias por compatibilidad con clientes que llaman pos/addpdr en lugar de addpdc */
    public function addpdr()
    {
        $this->addpdc();
    }

    /** Alias por compatibilidad con URLs pos/addpos */
    public function addpos()
    {
        $this->addpdc();
    }

    public function addpdc()
    {
      $pid = $this->input->post('product_id');
      if ($pid === null || $pid === '') {
          $this->output->set_status_header(400);
          header('Content-Type: application/json; charset=utf-8');
          echo json_encode(array('status' => false, 'error' => 'missing_product_id'));
          return;
      }
      $product = Product::find($pid);
      if (!$product) {
          $this->output->set_status_header(404);
          header('Content-Type: application/json; charset=utf-8');
          echo json_encode(array('status' => false, 'error' => 'product_not_found'));
          return;
      }
      if (!$this->register) {
          $this->output->set_status_header(403);
          header('Content-Type: application/json; charset=utf-8');
          echo json_encode(array('status' => false, 'error' => 'no_register'));
          return;
      }
      $register = Register::find($this->register);
      if (!$register) {
          $this->output->set_status_header(403);
          header('Content-Type: application/json; charset=utf-8');
          echo json_encode(array('status' => false, 'error' => 'register_not_found'));
          return;
      }

      $rawHold = $this->input->post('number');
      $holdNum = ($rawHold !== null && $rawHold !== '' && ctype_digit((string) $rawHold)) ? (int) $rawHold : 1;
      $tableIdForRow = $this->_active_posales_table_id_for_query();

      $PostPrice = $this->input->post('price');
      $taxRate = floatval($product->tax);
      $price = !$product->taxmethod || $product->taxmethod == '0' ? floatval($PostPrice) : floatval($PostPrice) * (1 + $taxRate / 100);
      /******************************************* sock version *************************************************************/
      if($product->type == '0')
      {
         $stock = Stock::find('first', array('conditions' => array('store_id = ? AND product_id = ?', $register->store_id, $this->input->post('product_id'))));
         $quantity = $stock ? $stock->quantity : 0;
        $posale = Posale::find('first', array(
            'conditions' => array(
                'status = ? AND register_id = ? AND product_id = ? AND table_id = ?',
                1,
                $this->register,
                $this->input->post('product_id'),
                $tableIdForRow
            )
        ));
        if ($posale) {
           if($posale->qt < $quantity) {
            $posale->qt ++;
            $posale->time = date("Y-m-d H:i:s");
            $posale->save();
            echo json_encode(array(
                "status" => TRUE
            ));
         }else {
            echo 'stock';
         }
      } else if($quantity != 0){
            $data = array(
                "product_id" => $this->input->post('product_id'),
                "name" => $this->input->post('name'),
                "price" => $price,
                "number" => $holdNum,
                "register_id" => $this->input->post('registerid'),
                "table_id" => $tableIdForRow,
                "qt" => 1,
                "status" => 1,
                "time" => date("Y-m-d H:i:s")
            );
            Posale::create($data);
            echo json_encode(array(
                "status" => TRUE
            ));
        }else {
           echo 'stock';
        }
       /******************************************* combo version *************************************************************/
     }elseif ($product->type == '2') {
        $posale = Posale::find('first', array(
           'conditions' => array(
             'status = ? AND register_id = ? AND product_id = ? AND table_id = ?',
             1,
             $this->register,
             $this->input->post('product_id'),
             $tableIdForRow
          )
       ));
        $quantity = 1;
        $combos = Combo_item::find('all', array('conditions' => array('product_id = ?', $this->input->post('product_id'))));
        foreach ($combos as $combo) {
           $prd = Product::find($combo->item_id);
           if (!$prd) {
               continue;
           }
           if($prd->type == '0'){
               $stock = Stock::find('first', array('conditions' => array('store_id = ? AND product_id = ?', $register->store_id, $combo->item_id)));
               if ($posale)
                  $diff = $stock ? ($stock->quantity - $combo->quantity*($posale->qt+1)) : 1;
               else
                 $diff = $stock ? ($stock->quantity - $combo->quantity) : 1;
              $quantity = $stock ? ($diff >= 0 ? 1 : 0) : $quantity;
           }
        }
      if ($posale) {
          if($quantity > 0) {
           $posale->qt ++;
           $posale->time = date("Y-m-d H:i:s");
           $posale->save();
           echo json_encode(array(
               "status" => TRUE
           ));
        }else {
           echo 'stock';
        }
     } elseif($quantity > 0){
           $data = array(
               "product_id" => $this->input->post('product_id'),
               "name" => $this->input->post('name'),
               "price" => $price,
               "number" => $holdNum,
               "register_id" => $this->input->post('registerid'),
               "table_id" => $tableIdForRow,
               "qt" => 1,
               "status" => 1,
               "time" => date("Y-m-d H:i:s")
           );
           Posale::create($data);
           echo json_encode(array(
               "status" => TRUE
           ));
      }else {
          echo 'stock';
      }
     }
     /******************************************* service version *************************************************************/
     else {
        $posale = Posale::find('first', array(
            'conditions' => array(
                'status = ? AND register_id = ? AND product_id = ? AND table_id = ?',
                1,
                $this->register,
                $this->input->post('product_id'),
                $tableIdForRow
            )
        ));
        if ($posale) {
            $posale->qt ++;
            $posale->time = date("Y-m-d H:i:s");
            $posale->save();
            echo json_encode(array(
                "status" => TRUE
            ));
        } else {
            $data = array(
                "product_id" => $this->input->post('product_id'),
                "name" => $this->input->post('name'),
                "price" => $price,
                "number" => $holdNum,
                "register_id" => $this->input->post('registerid'),
                "table_id" => $tableIdForRow,
                "qt" => 1,
                "status" => 1,
                "time" => date("Y-m-d H:i:s")
            );
            Posale::create($data);
            echo json_encode(array(
                "status" => TRUE
            ));
        }
     }
    }

    /**
     * table_id en sesión suele ser "5h", "0h" (selectTable); en zarest_posales la columna es INT (solo el número).
     * Comparar con el entero evita que el listado quede vacío y el subtotal venga de otra petición con criterio distinto.
     */
    private function _active_posales_table_id_for_query()
    {
        $t = $this->selectedTable;
        if ($t === false || $t === null || $t === '') {
            return 0;
        }
        if (is_string($t) && preg_match('/^(\d+)h$/', $t, $m)) {
            return (int) $m[1];
        }
        if (is_numeric($t)) {
            return (int) $t;
        }

        return 0;
    }

    /**
     * Condiciones para líneas del carrito activas (lista, subtotal, totiems).
     * register + mesa (numérico) + status.
     *
     * @return array lista para ActiveRecord conditions
     */
    private function _active_posales_conditions()
    {
        return array(
            'status = ? AND register_id = ? AND table_id = ?',
            1,
            (int) $this->register,
            $this->_active_posales_table_id_for_query()
        );
    }

    public function load_posales()
    {
        $posales = Posale::find('all', array(
            'conditions' => $this->_active_posales_conditions()
        ));
        $data = '';
        /* Setting::find(1, array('select'=>'currency')) provoca fallo silencioso (respuesta vacía) en este AR/PHP; usar $this->setting del constructor. */
        $currency = $this->setting && isset($this->setting->currency) ? $this->setting->currency : '';
        if (! empty($posales)) {
            foreach ($posales as $posale) {
               $product = Product::find($posale->product_id);
               $options = $posale->options;
               $options = trim($options, ",");
               $regRow = Register::find($this->register);
               $storeid = $regRow ? $regRow->store_id : 0;
               $alert = '';
               if (!$product) {
                   $alert = 'background-color:#ffe8e8;border-left:4px solid #c9302c;';
               } elseif (isset($product->type) && strval($product->type) === '0') {
                   $alertqt = $product->alertqt;
                   $stock = Stock::find('first', array('conditions' => array('product_id = ? AND store_id = ?', $posale->product_id, $storeid)));
                   $alert = ($stock && ($stock->quantity - $posale->qt <= $alertqt)) ? 'background-color:pink' : '';
               }
               $nameLine = $posale->name;
               if (!$product) {
                   $nameLine .= ' <span class="text-danger small">(' . label('PosaleMissingProduct') . ')</span>';
               }
               $pid = intval($posale->product_id);
               $poid = intval($posale->id);
               $optionsBtn = $product
                   ? '<button type="button" onclick="addoptions(' . $pid . ', ' . $poid . ')" class="btn btn-success btn-xs">' . label("Options") . '</button> '
                   : '';
                /* Misma estructura que backup + script: jQuery .load() ejecuta el script (delegados en vista también cubren +/-). */
                $row = '<div class="col-xs-12"><div class="panel panel-default product-details"><div class="panel-body" style="'.$alert.'"><div class="col-xs-5 nopadding"><div class="col-xs-2 nopadding"><a href="javascript:void(0)" onclick="delete_posale(' . "'" . $posale->id . "'" . ')"><span class="fa-stack fa-sm productD"><i class="fa fa-circle fa-stack-2x delete-product"></i><i class="fa fa-times fa-stack-1x fa-fw fa-inverse"></i></span></a></div><div class="col-xs-10 nopadding"><span class="textPD">' . $nameLine . '</span></div></div><div class="col-xs-2"><span class="textPD">' . number_format((float)$posale->price, $this->setting->decimals, '.', '') . '</span></div><div class="col-xs-3 nopadding productNum"><a href="javascript:void(0)"><span class="fa-stack fa-sm decbutton"><i class="fa fa-square fa-stack-2x light-grey"></i><i class="fa fa-minus fa-stack-1x fa-inverse white"></i></span></a><input type="text" id="qt-' . $posale->id . '" onchange="edit_posale(' . $posale->id . ')" class="form-control" value="' . $posale->qt . '" placeholder="0" maxlength="3"><a href="javascript:void(0)"><span class="fa-stack fa-sm incbutton"><i class="fa fa-square fa-stack-2x light-grey"></i><i class="fa fa-plus fa-stack-1x fa-inverse white"></i></span></a></div><div class="col-xs-2 nopadding "><span class="subtotal textPD">' . number_format((float)$posale->price*$posale->qt, $this->setting->decimals, '.', '') . '  ' . $currency . '</span></div></div>' . $optionsBtn . '<span id="pooptions-'.$posale->id.'"> '.$options.'</span></div></div>';

                $data .= $row;
            }
            $data .= '<script type="text/javascript">$(".incbutton").on("click", function() {var $button = $(this);var oldValue = $button.parent().parent().find("input").val();var newVal = parseFloat(oldValue) + 1;$button.parent().parent().find("input").val(newVal);edit_posale($button.parent().parent().find("input").attr("id").slice(3));});$(".decbutton").on("click", function() {var $button = $(this);var oldValue = $button.parent().parent().find("input").val();if (oldValue > 1) {var newVal = parseFloat(oldValue) - 1;} else {newVal = 1;}$button.parent().parent().find("input").val(newVal);edit_posale($button.parent().parent().find("input").attr("id").slice(3));});</script>';
        } else {

            $data = '<div class="messageVide">' . label("EmptyList") . ' <span>(' . label("SelectProduct") . ')</span></div>';
        }
        $this->output->set_output($data);
    }

    public function delete($id)
    {
        $id = (int) $id;
        if ($id > 0) {
            try {
                Posale::delete_all(array(
                    'conditions' => array('id = ?', $id)
                ));
            } catch (\Throwable $e) {
            }
        }
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(array(
            "status" => TRUE
        ));
    }

    public function edit($id)
    {
        $posale = Posale::find($id);
        if (!$posale) {
            echo json_encode(array("status" => FALSE));
            return;
        }
        $product = Product::find($posale->product_id);
        if (!$product) {
            $data = array(
                "qt" => $this->input->post('qt'),
                "time" => date('Y-m-d H:i:s')
            );
            $posale->update_attributes($data);
            echo json_encode(array(
                "status" => TRUE
            ));
            return;
        }
       if($product->type == '0'){
          $register = Register::find($this->register);
          $stock = Stock::find('first', array('conditions' => array('store_id = ? AND product_id = ?', $register->store_id, $posale->product_id)));
          $quantity = $stock ? $stock->quantity : 0;
          if(intval($this->input->post('qt')) <= intval($quantity)) {

             $data = array(
                 "qt" => $this->input->post('qt'),
                 "time" => date('Y-m-d H:i:s')
             );
             $posale->update_attributes($data);
             echo json_encode(array(
                 "status" => TRUE
             ));

        }else {
           echo 'stock';
        }
    /******************************************* combo version *************************************************************/
   }elseif ($product->type == '2') {
     $register = Register::find($this->register);
     $quantity = 1;
     $combos = Combo_item::find('all', array('conditions' => array('product_id = ?', $posale->product_id)));
     foreach ($combos as $combo) {
         $prd = Product::find($combo->item_id);
         if (!$prd) {
             continue;
         }
         if($prd->type == '0'){
             $stock = Stock::find('first', array('conditions' => array('store_id = ? AND product_id = ?', $register->store_id, $combo->item_id)));
            $diff = $stock ? ($stock->quantity - $combo->quantity*($this->input->post('qt'))) : 1;
            $quantity = $stock ? ($diff >= 0 ? 1 : 0) : $quantity;
         }
     }
        if($quantity > 0) {
           $data = array(
              "qt" => $this->input->post('qt'),
              "time" => date('Y-m-d H:i:s')
          );
          $posale->update_attributes($data);
          echo json_encode(array(
              "status" => TRUE
          ));
     }else {
         echo 'stock';
     }
   }else {
        $data = array(
            "qt" => $this->input->post('qt'),
            "time" => date('Y-m-d H:i:s')
        );
        $posale->update_attributes($data);
        echo json_encode(array(
            "status" => TRUE
        ));
     }

    }

    public function subtot()
    {
        $posales = Posale::find('all', array(
            'conditions' => $this->_active_posales_conditions()
        ));
        $sub = 0;
        foreach ($posales as $posale) {
            $sub += $posale->price * $posale->qt;
        }
        echo number_format((float)$sub, $this->setting->decimals, '.', '');
    }

    public function totiems()
    {
        $posales = Posale::find('all', array(
            'conditions' => $this->_active_posales_conditions()
        ));
        $sub = 0;
        foreach ($posales as $posale) {
            $sub += $posale->qt;
        }
        echo $sub;
    }

    public function GetDiscount($id)
    {
        $customer = Customer::find($id);
        $Discount = stripos($customer->discount, '%') > 0 ? $customer->discount : number_format((float)$customer->discount, $this->setting->decimals, '.', '');
        echo $Discount . '~' . $customer->name;
    }

    public function ResetPos()
    {
        Posale::delete_all(array(
            'conditions' => array(
                'status = ? AND register_id = ?',
                1,
                $this->register
            )
        ));
        echo json_encode(array(
            "status" => TRUE
        ));
    }

    public function AddNewSale($type)
    {
        date_default_timezone_set($this->setting->timezone);
        $date = date("Y-m-d H:i:s");
        $_POST['created_at'] = $date;
        $_POST['register_id'] = $this->register;
        $register = Register::find($this->register);
        $store = Store::find($register->store_id);
        if ($type == 2) {
            try {
                Stripe::setApiKey($this->setting->stripe_secret_key);
                $myCard = array(
                    'number' => $this->input->post('ccnum'),
                    'exp_month' => $this->input->post('ccmonth'),
                    'exp_year' => $this->input->post('ccyear'),
                    "cvc" => $this->input->post('ccv')
                );
                $charge = Stripe_Charge::create(array(
                    'card' => $myCard,
                    'amount' => (floatval($this->input->post('paid')) * 100),
                    'currency' => $this->setting->currency
                ));
                echo "<p class='bg-success text-center'>" . label('saleStripesccess') . '</p>';
            } catch (Stripe_CardError $e) {
                // Since it's a decline, Stripe_CardError will be caught
                $body = $e->getJsonBody();
                $err = $body['error'];
                echo "<p class='bg-danger text-center'>" . $err['message'] . '</p>';
            }
        }
        unset($_POST['ccnum']);
        unset($_POST['ccmonth']);
        unset($_POST['ccyear']);
        unset($_POST['ccv']);
        $paystatus = $_POST['paid'] - $_POST['total'];
        $_POST['firstpayement'] = $paystatus > 0 ? $_POST['total'] : $_POST['paid'];
        $sale = Sale::create($_POST);
        $posales = Posale::find('all', array(
            'conditions' => $this->_active_posales_conditions()
        ));
        foreach ($posales as $posale) {
            $data = array(
                "product_id" => $posale->product_id,
                "name" => $posale->name,
                "price" => $posale->price,
                "qt" => $posale->qt,
                "subtotal" => $posale->qt * $posale->price,
                "sale_id" => $sale->id,
                "date" => $date
            );
            $number = $posale->number;
            $register = Register::find($this->register);
            $prod = Product::find($posale->product_id);
            if($prod->type == "2"){
            /****************************************** combo case *************************************************************/
            $combos = Combo_item::find('all', array('conditions' => array('product_id = ?', $posale->product_id)));
            foreach ($combos as $combo) {
               $prd = Product::find($combo->item_id);
               if($prd->type == '0'){
                  $stock = Stock::find('first', array('conditions' => array('store_id = ? AND product_id = ?', $register->store_id, $combo->item_id)));
                  $stock->quantity = $stock->quantity - ($combo->quantity*$posale->qt);
                  $stock->save();
               }
            }
            /*******************************************************************************************************/
         }else if($prod->type == "0"){
            $stock = Stock::find('first', array('conditions' => array('store_id = ? AND product_id = ?', $register->store_id, $posale->product_id)));
            $stock->quantity = $stock->quantity - $posale->qt;
            $stock->save();
         }
            $pos = Sale_item::create($data);
        }

        $ticket = '<div class="col-md-12"><div class="text-center">' . $this->setting->receiptheader . '</div><div style="clear:both;"><h4 class="text-center">' . label("SaleNum") . '.: ' . sprintf("%05d", $sale->id) . '</h4> <div style="clear:both;"></div><span class="float-left">' . label("Date") . ': ' . $sale->created_at->format('d-m-Y H:i:s') . '</span><br><div style="clear:both;"><span class="float-left">' . label("Customer") . ': ' . $sale->clientname . '</span><div style="clear:both;"><table class="table" cellspacing="0" border="0"><thead><tr><th><em>#</em></th><th>' . label("Product") . '</th><th>' . label("Quantity") . '</th><th>' . label("SubTotal") . '</th></tr></thead><tbody>';

        $i = 1;
        foreach ($posales as $posale) {
            $ticket .= '<tr><td style="text-align:center; width:30px;">' . $i . '</td><td style="text-align:left; width:180px;">' . $posale->name . '</td><td style="text-align:center; width:50px;">' . $posale->qt . '</td><td style="text-align:right; width:70px; ">' . number_format((float)($posale->qt * $posale->price), $this->setting->decimals, '.', '') . ' ' . $this->setting->currency . '</td></tr>';
            $i ++;
        }

        $bcs = 'code128';
        $height = 20;
        $width = 3;
        $ticket .= '</tbody></table><table class="table" cellspacing="0" border="0" style="margin-bottom:8px;"><tbody><tr><td style="text-align:left;">' . label("TotalItems") . '</td><td style="text-align:right; padding-right:1.5%;">' . $sale->totalitems . '</td><td style="text-align:left; padding-left:1.5%;">' . label("Total") . '</td><td style="text-align:right;font-weight:bold;">' . $sale->subtotal . ' ' . $this->setting->currency . '</td></tr>';
        if (intval($sale->discount))
            $ticket .= '<tr><td style="text-align:left; padding-left:1.5%;"></td><td style="text-align:right;font-weight:bold;"></td><td style="text-align:left;">' . label("Discount") . '</td><td style="text-align:right; padding-right:1.5%;font-weight:bold;">' . $sale->discount . '</td></tr>';
        if (intval($sale->tax))
            $ticket .= '<tr><td style="text-align:left;"></td><td style="text-align:right; padding-right:1.5%;font-weight:bold;"></td><td style="text-align:left; padding-left:1.5%;">' . label("tax") . '</td><td style="text-align:right;font-weight:bold;">' . $sale->tax . '</td></tr>';
        $ticket .= '<tr><td colspan="2" style="text-align:left; font-weight:bold; padding-top:5px;">' . label("GrandTotal") . '</td><td colspan="2" style="border-top:1px dashed #000; padding-top:5px; text-align:right; font-weight:bold;">' . number_format((float)$sale->total, $this->setting->decimals, '.', '') . ' ' . $this->setting->currency . '</td></tr><tr>';

        $PayMethode = explode('~', $sale->paidmethod);
        $pmInfo = Payment_method::parse($sale->paidmethod);
        $methodLabel = htmlspecialchars($pmInfo['display_name'], ENT_QUOTES, 'UTF-8');

        switch ($pmInfo['type']) {
            case 'card':
                $last4 = (isset($PayMethode[1]) && strlen((string) $PayMethode[1]) >= 4) ? substr($PayMethode[1], - 4) : '****';
                $holder = isset($PayMethode[2]) ? $PayMethode[2] : '';
                $ticket .= '<td colspan="2" style="text-align:left; font-weight:bold; padding-top:5px;">' . label("CreditCard") . '</td><td colspan="2" style="padding-top:5px; text-align:right; font-weight:bold;">xxxx xxxx xxxx ' . $last4 . '</td></tr><tr><td colspan="2" style="text-align:left; font-weight:bold; padding-top:5px;">' . label("CreditCardHold") . '</td><td colspan="2" style="padding-top:5px; text-align:right; font-weight:bold;">' . $holder . '</td></tr></tbody></table>';
                break;
            case 'cheque':
                $chq = isset($PayMethode[1]) ? $PayMethode[1] : '';
                $ticket .= '<td colspan="2" style="text-align:left; font-weight:bold; padding-top:5px;">' . label("ChequeNum") . '</td><td colspan="2" style="padding-top:5px; text-align:right; font-weight:bold;">' . $chq . '</td></tr></tbody></table>';
                break;
            default:
                $ticket .= '<td colspan="2" style="text-align:left; font-weight:bold; padding-top:5px;">' . label("Paid") . ' (' . $methodLabel . ')</td><td colspan="2" style="padding-top:5px; text-align:right; font-weight:bold;">' . number_format((float)$sale->paid, $this->setting->decimals, '.', '') . ' ' . $this->setting->currency . '</td></tr><tr><td colspan="2" style="text-align:left; font-weight:bold; padding-top:5px;">' . label("Change") . '</td><td colspan="2" style="padding-top:5px; text-align:right; font-weight:bold;">' . number_format((float)(floatval($sale->paid) - floatval($sale->total)), $this->setting->decimals, '.', '') . ' ' . $this->setting->currency . '</td></tr></tbody></table>';
        }

        $ticket .= '<div style="border-top:1px solid #000; padding-top:10px;"><span class="float-left">' . $store->name . '</span><span class="float-right">' . label("Tel") . ' ' . ($store->phone ? $store->phone : $this->setting->phone) . '</span><div style="clear:both;"><center><img style="margin-top:30px" src="' . site_url('pos/GenerateBarcode/' . sprintf("%05d", $sale->id) . '/' . $bcs . '/' . $height . '/' . $width) . '" alt="' . $sale->id . '" /></center><p class="text-center" style="margin:0 auto;margin-top:10px;">' . $store->footer_text . '</p><div class="text-center" style="background-color:#000;padding:5px;width:85%;color:#fff;margin:0 auto;border-radius:3px;margin-top:20px;">' . $this->setting->receiptfooter . '</div></div>';

        $tidAfterSale = $this->_active_posales_table_id_for_query();
        Posale::delete_all(array(
            'conditions' => array(
                'status = ? AND register_id = ? AND table_id = ?',
                1,
                $this->register,
                $tidAfterSale
            )
        ));
        if (isset($number)) {
            if ($number != 1)
                Hold::delete_all(array(
                    'conditions' => array(
                        'number = ? AND register_id = ? AND table_id = ?',
                        $number,
                        $this->register,
                        $tidAfterSale
                    )
                ));
        }
        $hold = Hold::find('last', array(
            'conditions' => array(
                'register_id = ? AND table_id = ?',
                $this->register,
                $tidAfterSale
            )
        ));
        if ($hold) {
            Posale::update_all(array(
                'set' => array(
                    'status' => 1
                ),
                'conditions' => array(
                    'number = ? AND register_id = ? AND table_id = ?',
                    $hold->number,
                    $this->register,
                    $tidAfterSale
                )
            ));
        }
        echo $ticket;
    }

    function GenerateBarcode($code = NULL, $bcs = 'code128', $height = 60, $width = 1)
    {
        $this->load->library('zend');
        $this->zend->load('Zend/Barcode');
        $barcodeOptions = array(
            'text' => $code,
            'barHeight' => $height,
            'barThinWidth' => $width,
            'drawText' => FALSE
        );
        $rendererOptions = array(
            'imageType' => 'png',
            'horizontalPosition' => 'center',
            'verticalPosition' => 'middle'
        );
        $imageResource = Zend_Barcode::render($bcs, 'image', $barcodeOptions, $rendererOptions);
        return $imageResource;
    }

    // ******************************************************** hold functions
    public function holdList($registerid)
    {
        $tid = $this->_active_posales_table_id_for_query();
        $holds = Hold::find('all', array(
            'conditions' => array(
                'register_id = ? AND table_id = ?',
                $registerid,
                $tid
            ),
            'order' => 'number asc'
        ));
        $posale = Posale::find('last', array(
            'conditions' => array(
                'status = ? AND register_id = ? AND table_id = ?',
                1,
                $this->register,
                $tid
            )
        ));
        $Tholds = '';
        if (empty($holds))
            $Tholds = '<span class="Hold selectedHold">1<span id="Time">' . date("H:i") . '</span></span>';
        else {
            if (empty($posale)) {
                $numItems = count($holds);
                $i = 0;
                foreach ($holds as $hold) {
                    if (++ $i === $numItems)
                        $Tholds .= '<span class="Hold selectedHold" id="' . $hold->number . '"  onclick="SelectHold(' . $hold->number . ')">' . $hold->number . '<span id="Time">' . $hold->time . '</span></span>';
                    else
                        $Tholds .= '<span class="Hold" id="' . $hold->number . '"  onclick="SelectHold(' . $hold->number . ')">' . $hold->number . '<span id="Time">' . $hold->time . '</span></span>';
                }
            } else {
                foreach ($holds as $hold) {
                    if ($hold->number == $posale->number)
                        $selected = 'selectedHold';
                    else
                        $selected = '';
                    $Tholds .= '<span class="Hold ' . $selected . '" id="' . $hold->number . '" onclick="SelectHold(' . $hold->number . ')">' . $hold->number . '<span id="Time">' . $hold->time . '</span></span>';
                }
            }
        }
        echo $Tholds;
    }

    public function AddHold($registerid)
    {
        $tid = $this->_active_posales_table_id_for_query();
        $hold = Hold::find('last', array(
            'conditions' => array(
                'register_id = ? AND table_id = ?',
                $registerid,
                $tid
            )
        ));
        $number = ! empty($hold) ? intval($hold->number) + 1 : 1;
        Posale::update_all(array(
            'set' => array(
                'status' => 0
            ),
            'conditions' => array(
                'status = ? AND register_id = ?',
                1,
                $this->register
            )
        ));
        $attributes = array(
            'number' => $number,
            'time' => date("H:i"),
            'register_id' => $registerid,
            'table_id' => $tid
        );
        Hold::create($attributes);
        echo json_encode(array(
            "status" => TRUE
        ));
    }

    public function RemoveHold($number, $registerid)
    {
        $tid = $this->_active_posales_table_id_for_query();
        $hold = Hold::find('first', array(
            'conditions' => array(
                'number = ? AND register_id = ? AND table_id = ?',
                $number,
                $registerid,
                $tid
            )
        ));
        if (!$hold) {
            echo json_encode(array(
                "status" => FALSE,
                "error" => "hold_not_found"
            ));
            return;
        }
        $hold->delete();
        Posale::delete_all(array(
            'conditions' => array(
                'number = ? AND register_id = ?',
                $number,
                $registerid
            )
        ));
        $hold = Hold::find('last', array(
            'conditions' => array(
                'register_id = ? AND table_id = ?',
                $registerid,
                $tid
            )
        ));
        if ($hold) {
            Posale::update_all(array(
                'set' => array(
                    'status' => 1
                ),
                'conditions' => array(
                    'number = ? AND register_id = ?',
                    $hold->number,
                    $registerid
                )
            ));
        }
        echo json_encode(array(
            "status" => TRUE
        ));
    }

    public function SelectHold($number)
    {
        Posale::update_all(array(
            'set' => array(
                'status' => 0
            ),
            'conditions' => array(
                'status = ? AND register_id = ?',
                1,
                $this->register
            )
        ));
        Posale::update_all(array(
            'set' => array(
                'status' => 1
            ),
            'conditions' => array(
                'number = ? AND register_id = ?',
                $number,
                $this->register
            )
        ));
        echo json_encode(array(
            "status" => TRUE
        ));
    }

    /**
     * ****************** register functions ***************
     */
     public function CloseRegister()
     {
         $register = Register::find($this->register);
         $user = User::find($register->user_id);
         $sales = Sale::find('all', array(
             'conditions' => array(
                 'register_id = ?',
                 $this->register
             )
         ));
         $payaments = Payement::find('all', array(
             'conditions' => array(
                 'register_id = ?',
                 $this->register
             )
         ));

         $waiters = Waiter::find('all', array('conditions' => array('store_id = ?', $register->store_id)));

         $cash = 0;
         $cheque = 0;
         $cc = 0;
         $CashinHand = $register->cash_inhand;
         $date = $register->date;
         $createdBy = $user->firstname . ' ' . $user->lastname;

         foreach ($payaments as $payament) {
            $bucket = Payment_method::close_bucket($payament->paidmethod);
            if ($bucket === 'cc') {
                $cc += $payament->paid;
            } elseif ($bucket === 'cheque') {
                $cheque += $payament->paid;
            } else {
                $cash += $payament->paid;
            }
        }

         foreach ($sales as $sale) {
             $paystatus = $sale->paid - $sale->total;
             $bucket = Payment_method::close_bucket($sale->paidmethod);
             $add = $paystatus > 0 ? $sale->total : $sale->firstpayement;
             if ($bucket === 'cc') {
                 $cc += $add;
             } elseif ($bucket === 'cheque') {
                 $cheque += $add;
             } else {
                 $cash += $add;
             }
         }

         $byMethodTotals = $this->aggregate_close_register_expected_by_method($sales, $payaments);
         $dec = (int) $this->setting->decimals;
         $currencySym = $this->setting->currency;
         $methodBreakdown = '<h2>' . label('CloseRegisterByMethod') . '</h2><table class="table table-striped table-bordered"><thead><tr><th width="70%">' . label('PayementType') . '</th><th class="text-right" width="30%">' . label('EXPECTED') . ' (' . $currencySym . ')</th></tr></thead><tbody>';
         $methodBreakdownHas = false;
         foreach ($byMethodTotals as $mrow) {
             if (abs($mrow['amount']) < 0.00001) {
                 continue;
             }
             $methodBreakdownHas = true;
             $methodBreakdown .= '<tr><td>' . htmlspecialchars($mrow['label'], ENT_QUOTES, 'UTF-8') . '</td><td class="text-right">' . number_format((float) $mrow['amount'], $dec, '.', '') . '</td></tr>';
         }
         if (!$methodBreakdownHas) {
             $methodBreakdown .= '<tr><td colspan="2" class="text-muted text-center">' . label('CloseRegisterNoMethodTotals') . '</td></tr>';
         }
         $methodBreakdown .= '</tbody></table>';

         $summaryHead = '<h2>' . label("PaymentsSummary") . '</h2><table class="table table-striped close-register-summary"><thead><tr><th width="25%">' . label("PayementType") . '</th><th width="25%">' . label("EXPECTED") . ' (' . $this->setting->currency . ')</th><th width="25%">' . label("COUNTED") . ' (' . $this->setting->currency . ')</th><th width="25%">' . label("DIFFERENCES") . ' (' . $this->setting->currency . ')</th></tr></thead><tbody>';
         $summaryBody = '';
         $grandExpected = 0.0;
         $summaryHasRow = false;
         foreach ($byMethodTotals as $mrow) {
             if (abs($mrow['amount']) < 0.00001) {
                 continue;
             }
             $summaryHasRow = true;
             $grandExpected += (float) $mrow['amount'];
             $amtStr = number_format((float) $mrow['amount'], $dec, '.', '');
             $keyEsc = htmlspecialchars($mrow['key'], ENT_QUOTES, 'UTF-8');
             $labelEsc = htmlspecialchars($mrow['label'], ENT_QUOTES, 'UTF-8');
             $typeEsc = htmlspecialchars($mrow['type_code'], ENT_QUOTES, 'UTF-8');
             $isCash = ($mrow['type_code'] === 'cash');
             $ro = $isCash ? '' : ' readonly="readonly" tabindex="-1"';
             $lockedClass = $isCash ? '' : ' cr-counted-locked';
             $summaryBody .= '<tr class="close-reg-line" data-method-key="' . $keyEsc . '" data-type-code="' . $typeEsc . '" data-method-label="' . $labelEsc . '"><td>' . $labelEsc . '</td><td><span class="cr-expected">' . $amtStr . '</span></td><td><input type="text" class="total-input cr-counted' . $lockedClass . '" value="' . $amtStr . '" placeholder="0.00" maxlength="14"' . $ro . '></td><td><span class="cr-diff">0</span></td></tr>';
         }
         if (! $summaryHasRow) {
             $summaryBody .= '<tr><td colspan="4" class="text-muted text-center">' . label('CloseRegisterNoMethodTotals') . '</td></tr>';
         }
         $grandStr = number_format((float) $grandExpected, $dec, '.', '');
         $summaryFoot = '<tr class="warning close-reg-total"><td>' . label("Total") . '</td><td><span id="total">' . $grandStr . '</span></td><td><span id="countedtotal">' . $grandStr . '</span></td><td><span id="difftotal">0</span></td></tr></tbody></table>';
         $paymentsSummary = $summaryHead . $summaryBody . $summaryFoot;

         $data = '<div class="col-md-3"><blockquote><footer>' . label("Openedby") . '</footer><p>' . $createdBy . '</p></blockquote></div><div class="col-md-3"><blockquote><footer>' . label("CashinHand") . '</footer><p>' . number_format((float)$CashinHand, $this->setting->decimals, '.', '') . ' ' . $this->setting->currency . '</p></blockquote></div><div class="col-md-4"><blockquote><footer>' . label("Openingtime") . '</footer><p>' . $date->format('Y-m-d h:i:s') . '</p></blockquote></div><div class="col-md-2"><img src="' . site_url() . '/assets/img/register.svg" alt=""></div>' . $methodBreakdown . $paymentsSummary;

         foreach ($waiters as $waiter) {
            $cih = explode(',', trim($register->waiterscih, ","));
            $cachin = 0;
            for($i = 0; $i < sizeof($cih); $i += 2){if($cih[$i] == $waiter->id){$cachin = $cih[$i+1];}}
            $cashw = 0;
            $chequew = 0;
            $ccw = 0;
            foreach ($payaments as $payament) {
               if($payament->waiter_id == $waiter->id){
                  $bucket = Payment_method::close_bucket($payament->paidmethod);
                  if ($bucket === 'cc') {
                      $ccw += $payament->paid;
                  } elseif ($bucket === 'cheque') {
                      $chequew += $payament->paid;
                  } else {
                      $cashw += $payament->paid;
                  }
               }
           }
            foreach ($sales as $sale) {
               if($sale->waiter_id == $waiter->id){
                   $paystatus = $sale->paid - $sale->total;
                   $bucket = Payment_method::close_bucket($sale->paidmethod);
                   $addw = $paystatus > 0 ? $sale->total : $sale->firstpayement;
                   if ($bucket === 'cc') {
                       $ccw += $addw;
                   } elseif ($bucket === 'cheque') {
                       $chequew += $addw;
                   } else {
                       $cashw += $addw;
                   }
               }
            }
            $Wtotal = $ccw + $chequew + $cashw + $cachin;
            $data .= '<div class="waitercount"><ul><li><h4>'.$waiter->name.' :</h4></li><li><b>' . label("CashinHand") . ' : </b>' . number_format((float)$cachin, $this->setting->decimals, '.', '') . ' ' . $this->setting->currency . '</li><li><b>' . label("Cash") . ' : </b>' . number_format((float)$cashw, $this->setting->decimals, '.', '') . ' ' . $this->setting->currency . '</li><li><b>' . label("CreditCard") . ' : </b>' . number_format((float)$ccw, $this->setting->decimals, '.', '') . ' ' . $this->setting->currency . '</li><li><b>' . label("Cheque") . ' : </b>' . number_format((float)$chequew, $this->setting->decimals, '.', '') . ' ' . $this->setting->currency . '</li></ul><div style="clear:both;"></div><div class="wtotal"><h3>' . label("Total") . ' : ' . number_format((float)$Wtotal, $this->setting->decimals, '.', '') . '</h3></div></div>';
         }

         $data .= '<div  class="form-group"><h2>' . label("note") . '</h2><textarea id="RegisterNote" class="form-control" rows="3"></textarea></div>';

         echo $data;
     }

    public function SubmitRegister()
    {
        date_default_timezone_set($this->setting->timezone);
        $date = date("Y-m-d H:i:s");
        $noteText = (string) $this->input->post('RegisterNote');
        $cashE = 0.0;
        $cashC = 0.0;
        $ccE = 0.0;
        $ccC = 0.0;
        $chE = 0.0;
        $chC = 0.0;
        $lines = json_decode((string) $this->input->post('close_lines'), true);
        if (is_array($lines) && count($lines) > 0) {
            foreach ($lines as $ln) {
                if (! is_array($ln)) {
                    continue;
                }
                $exp = $this->parse_close_register_amount(isset($ln['expected']) ? $ln['expected'] : 0);
                $cnt = $this->parse_close_register_amount(isset($ln['counted']) ? $ln['counted'] : 0);
                $tc = isset($ln['type']) ? (string) $ln['type'] : 'cash';
                if ($tc === 'card') {
                    $ccE += $exp;
                    $ccC += $cnt;
                } elseif ($tc === 'cheque') {
                    $chE += $exp;
                    $chC += $cnt;
                } else {
                    $cashE += $exp;
                    $cashC += $cnt;
                }
            }
            $noteText .= "\n\n[REGISTER_CLOSE_LINES_JSON]" . json_encode($lines, JSON_UNESCAPED_UNICODE) . "[/REGISTER_CLOSE_LINES_JSON]";
        } else {
            $cashE = $this->parse_close_register_amount($this->input->post('expectedcash'));
            $cashC = $this->parse_close_register_amount($this->input->post('countedcash'));
            $ccE = $this->parse_close_register_amount($this->input->post('expectedcc'));
            $ccC = $this->parse_close_register_amount($this->input->post('countedcc'));
            $chE = $this->parse_close_register_amount($this->input->post('expectedcheque'));
            $chC = $this->parse_close_register_amount($this->input->post('countedcheque'));
        }
        $dec = (int) $this->setting->decimals;
        $data = array(
            "cash_total" => number_format($cashE, $dec, '.', ''),
            "cash_sub" => number_format($cashC, $dec, '.', ''),
            "cc_total" => number_format($ccE, $dec, '.', ''),
            "cc_sub" => number_format($ccC, $dec, '.', ''),
            "cheque_total" => number_format($chE, $dec, '.', ''),
            "cheque_sub" => number_format($chC, $dec, '.', ''),
            "note" => $noteText,
            "closed_by" => $this->session->userdata('user_id'),
            "closed_at" => $date,
            "status" => 0
        );

        $Register = Register::find($this->register);

        $store = Store::find($Register->store_id);
        $store->status = 0;
        $store->save();

        $tables = Table::find('all', array('conditions' => array('store_id = ?', $Register->store_id)));
        foreach ($tables as $table) {
           $table->status = 0;
           $table->time = '';
           $table->save();
        }

        $Register->update_attributes($data);

        Hold::delete_all(array(
            'conditions' => array(
                'register_id = ?',
                $Register->id
            )
        ));
        Posale::delete_all(array(
            'conditions' => array(
                'register_id = ?',
                $Register->id
            )
        ));

        $CI = & get_instance();
        $CI->session->set_userdata('register', 0);

        echo json_encode(array(
            "status" => TRUE
        ));
    }

    public function email()
    {
        $email = $this->input->post('email');
        $content = $this->input->post('content');
        $this->load->library('email');

        $this->email->set_mailtype("html");
        $this->email->from('no-reply@' . $this->setting->companyname . '.com', $this->setting->companyname);
        $this->email->to('$email');

        $this->email->subject('your Receipt');
        $this->email->message($content);

        $this->email->send();

        echo json_encode(array(
            "status" => TRUE
        ));
    }

    public function pdfreceipt()
    {
        $content = $this->input->post('content');
        $this->load->library('Pdf');
        require_once APPPATH . 'libraries/Report_exporter.php';
        Report_exporter::clear_output_buffers();
        $pdf = new Pdf('P', 'mm', 'A4', true, 'UTF-8', false);
        $pdf->SetTitle('Pdf');
        $pdf->SetHeaderMargin(30);
        $pdf->SetTopMargin(20);
        $pdf->setFooterMargin(20);
        $pdf->SetAutoPageBreak(true);
        $pdf->SetAuthor('Author');
        $pdf->SetDisplayMode('real', 'default');
        $pdf->AddPage();
        $pdf->writeHTMLCell(0, 0, '', '', $content, 0, 1, 0, true, '', true);
        $pdf->Output('pdfexample.pdf', 'D');
    }

    public function storewaitercash($id)
    {
      $waiters = Waiter::find('all', array('conditions' => array('store_id = ?', $id)));
      $content = '';
      foreach ($waiters as $waiter) {
         $content .= '<div class="form-group"><label for="CashinHand"><u>'.$waiter->name.'</u> '.label("CashinHand").'</label><input type="number" step="any" class="form-control" id="waiterid" waiter-id="'.$waiter->id.'" placeholder="'.$waiter->name.' '.label("CashinHand").'" Required></div>';
      }
      echo $content;
   }

   public function WaiterName($num = null)
   {
      $tid = $this->_active_posales_table_id_for_query();
      $waiterid = Hold::find('first', array(
          'conditions' => array(
             'number = ? AND register_id = ? AND table_id = ?',
             $num,
             $this->register,
             $tid
          )
      ))->waiter_id;
      echo $waiterid;
   }
   public function changewaiterS()
   {
      $num = $this->input->post('num');
      $id = $this->input->post('id');
      $tid = $this->_active_posales_table_id_for_query();
      $hold = Hold::find('first', array(
          'conditions' => array(
             'number = ? AND register_id = ? AND table_id = ?',
             $num,
             $this->register,
             $tid
          )
      ));
      $hold->waiter_id = $id;
      $hold->save();

      echo json_encode(array(
          "status" => TRUE
      ));
   }

   public function CustomerName($num = null)
   {
      $tid = $this->_active_posales_table_id_for_query();
      $customerid = Hold::find('first', array(
          'conditions' => array(
             'number = ? AND register_id = ? AND table_id = ?',
             $num,
             $this->register,
             $tid
          )
      ))->customer_id;
      echo $customerid;
   }
   public function changecustomerS()
   {
      $num = $this->input->post('num');
      $id = $this->input->post('id');
      $tid = $this->_active_posales_table_id_for_query();
      $hold = Hold::find('first', array(
          'conditions' => array(
             'number = ? AND register_id = ? AND table_id = ?',
             $num,
             $this->register,
             $tid
          )
      ));
      $hold->customer_id = $id;
      $hold->save();

      echo json_encode(array(
          "status" => TRUE
      ));
   }

   public function showticket($num, $subtotal, $totalitems, $waiter)
   {
      // $hold = Hold::find($num);
      $waiterN = $waiter > 0 ? Waiter::find($waiter)->name : label('withoutWaiter');
      $store = Store::find($this->store);
      $date = date("Y-m-d H:i:s");
      $tid = $this->_active_posales_table_id_for_query();
      $tableRow = $tid > 0 ? Table::find($tid) : null;
      $tableN = $tableRow ? $tableRow->name : '';

      $posales = Posale::find('all', array(
           'conditions' => array(
               'status = ? AND register_id = ? AND table_id = ?',
               1,
               $this->register,
               $tid
           )
      ));

      $ticket = '<div class="col-md-12"><div class="text-center">' . $this->setting->receiptheader . '</div><div style="clear:both;"><br><div style="clear:both;"><div style="clear:both;"><span class="float-left">' . label("Date") . ': ' . $date . '</span><br><div style="clear:both;"><span class="float-left">' . label("Waiter") . ': ' . $waiterN . '<br> ' . label("Table") . ' :' . $tableN . '</span><div style="clear:both;"><br><br><table class="table" cellspacing="0" border="0"><thead><tr><th><em>#</em></th><th>' . label("Product") . '</th><th>' . label("Quantity") . '</th><th>' . label("SubTotal") . '</th></tr></thead><tbody>';

      $i = 1;
      foreach ($posales as $posale) {
           $ticket .= '<tr><td style="text-align:center; width:30px;">' . $i . '</td><td style="text-align:left; width:180px;">' . $posale->name . '<br><span style="font-size:12px;color:#666">'.$posale->options.'</span></td><td style="text-align:center; width:50px;">' . $posale->qt . '</td><td style="text-align:right; width:70px;font-size:14px; ">' . number_format((float)($posale->qt * $posale->price), $this->setting->decimals, '.', '') . ' ' . $this->setting->currency . '</td></tr>';
           $i ++;
      }

      $ticket .= '</tbody></table><table class="table" cellspacing="0" border="0" style="margin-bottom:8px;"><tbody><tr><td style="text-align:left;">' . label("TotalItems") . '</td><td style="text-align:right; padding-right:1.5%;">' . $totalitems . '</td><td style="text-align:left; padding-left:1.5%;">' . label("Total") . ' ( + tax)</td><td style="text-align:right;font-weight:bold;">' . $subtotal . ' ' . $this->setting->currency . '</td></tr>';

      $ticket .= '</tbody></table><br><br><div style="border-top:1px solid #000; padding-top:10px;"><span class="float-left">' . $store->name . '</span><span class="float-right">' . label("Tel") . ' ' . ($store->phone ? $store->phone : $this->setting->phone) . '</span><div style="clear:both;"><p class="text-center" style="margin:0 auto;margin-top:10px;">' . $store->footer_text . '</p><div class="text-center" style="background-color:#000;padding:5px;width:85%;color:#fff;margin:0 auto;border-radius:3px;margin-top:20px;">' . $this->setting->receiptfooter . '</div></div>';

      echo $ticket;
   }

   public function showticketKit($tableid)
   {
      $table = Table::find($tableid);
      $tableN = $table->name;
      $posales = Posale::find('all', array(
        'conditions' => array(
          'table_id = ?',
          $tableid
        )
      ));
      foreach ($posales as $posale) {
        $d1 = new DateTime($posale->time);
        $d2 = new DateTime($table->checked);
        if($d1 < $d2){
          $posale->time = 'y';
        }else{
          $posale->time = 'n';
        }
      }
      $table->checked = date("Y-m-d H:i:s");
      $table->save();



      $ticket = '<div class="col-md-12"><div class="text-center">' . $this->setting->receiptheader . '</div><div style="clear:both;"><br><div style="clear:both;"><div style="clear:both;"><br><div style="clear:both;">' . label("Table") . ' :' . $tableN . '</span><div style="clear:both;"><br><br><table class="table" cellspacing="0" border="0"><thead><tr><th><em>#</em></th><th>' . label("Product") . '</th><th>' . label("Quantity") . '</th><th>' . label("SubTotal") . '</th></tr></thead><tbody>';

      $i = 1;
      foreach ($posales as $posale) {
           $ticket .= '<tr style="'.($posale->time == "n" ? 'background-color:#FFC0CB;' : '').'"><td style="text-align:center; width:30px;">' . $i . '</td><td style="text-align:left; width:180px;">' . $posale->name . '<br><span style="font-size:12px;color:#666">'.$posale->options.'</span></td><td style="text-align:center; width:50px;">' . $posale->qt . '</td><td style="text-align:right; width:70px;font-size:14px; ">' . number_format((float)($posale->qt * $posale->price), $this->setting->decimals, '.', '') . ' ' . $this->setting->currency . '</td></tr>';
           $i ++;
      }

      $ticket .= '</tbody></table>';


      echo $ticket;
   }

   public function getoptions($id, $posale)
   {
      $options = Product::find($id)->options;
      $options = trim($options, ",");
      $array = explode(',', $options); //split string into array seperated by ','
      $poOptions = Posale::find($posale)->options;
      $poOptions = trim($poOptions, ",");
      $array2 = explode(',', $poOptions); //split string into array seperated by ','
      $result = '<div class="col-md-12"><input type="hidden" value="'.$posale.'" id="optprd"><select class="js-select-basic-multiple form-control" multiple="multiple" id="optionsselect">';
      foreach ($array as $value) {
         $selected = '';
         foreach ($array2 as $value2) { $selected = $value == $value2 ? 'selected="selected"' : $selected;}
         $result .= '<option value="'.$value.'" '.$selected.'>'.$value.'</option>';
      }
      $result .= '</select></div>';
      echo $result;
   }

   public function addposaleoptions()
   {
      $options = $this->input->post('options');
      $posaleid = $this->input->post('posale');
      $option = '';
      foreach ($options as $value) {
         $option .= $value.',';
      }
      $posale = Posale::find($posaleid);
      $posale->options = $option;
      $posale->time = date("Y-m-d H:i:s");
      $posale->save();

      echo json_encode(array(
          "status" => TRUE
      ));
   }

   public function CloseTable()
   {
      $tid = $this->_active_posales_table_id_for_query();
      Hold::delete_all(array(
          'conditions' => array(
              'table_id = ? AND register_id = ?',
              $tid,
              $this->register
          )
      ));
      Posale::delete_all(array(
          'conditions' => array(
             'table_id = ? AND register_id = ?',
             $tid,
             $this->register
          )
      ));

      if ($tid > 0) {

         $table = Table::find($tid);
            $table->status = 0;
            $table->time = '';
            $table->save();
      }

      $CI = & get_instance();
      $CI->session->set_userdata('selectedTable', 0);

      echo json_encode(array(
          "status" => TRUE
      ));
   }

    /**
     * Clave estable para agrupar montos del cierre por método de pago (id de zarest_payment_methods o primer segmento legacy).
     */
    private function close_register_method_key($paidmethod)
    {
        $pm = Payment_method::find_for_paidmethod($paidmethod);
        if ($pm) {
            return 'pm:' . (int) $pm->id;
        }
        $parts = explode('~', (string) $paidmethod);

        return 'raw:' . (isset($parts[0]) ? $parts[0] : '');
    }

    /**
     * Suma esperada por medio: cobros en Payement + primera cuota / total de ventas.
     * Cada fila incluye type_code (cash|card|cheque|other) para el resumen del cierre sin mezclar Nequi con efectivo.
     *
     * @return array Lista de filas ['key','label','amount','type_code'], ordenada por monto descendente.
     */
    private function aggregate_close_register_expected_by_method($sales, $payaments)
    {
        $totals = array();
        foreach ($payaments as $payament) {
            $key = $this->close_register_method_key($payament->paidmethod);
            $info = Payment_method::parse($payament->paidmethod);
            if (! isset($totals[$key])) {
                $totals[$key] = array(
                    'key' => $key,
                    'label' => Payment_method::display_label($payament->paidmethod),
                    'amount' => 0.0,
                    'type_code' => $info['type'],
                );
            }
            $totals[$key]['amount'] += (float) $payament->paid;
        }
        foreach ($sales as $sale) {
            $paystatus = (float) $sale->paid - (float) $sale->total;
            $add = $paystatus > 0 ? (float) $sale->total : (float) $sale->firstpayement;
            $key = $this->close_register_method_key($sale->paidmethod);
            $info = Payment_method::parse($sale->paidmethod);
            if (! isset($totals[$key])) {
                $totals[$key] = array(
                    'key' => $key,
                    'label' => Payment_method::display_label($sale->paidmethod),
                    'amount' => 0.0,
                    'type_code' => $info['type'],
                );
            }
            $totals[$key]['amount'] += $add;
        }
        uasort($totals, function ($a, $b) {
            $diff = $b['amount'] - $a['amount'];
            if (abs($diff) < 0.00001) {
                return strcasecmp($a['label'], $b['label']);
            }

            return $diff > 0 ? 1 : -1;
        });

        return array_values($totals);
    }

    /**
     * @param string|float $raw
     */
    private function parse_close_register_amount($raw)
    {
        if ($raw === null || $raw === '') {
            return 0.0;
        }
        if (is_numeric($raw)) {
            return (float) $raw;
        }
        $s = trim((string) $raw);
        $s = preg_replace('/[^\d,.\-]/', '', $s);
        $s = str_replace(',', '.', $s);

        return (float) $s;
    }
}

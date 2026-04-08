<!-- Page Content -->
<div class="container">
   <div class="row" style="margin-top:100px;">
      <div class="col-md-12">
         <!-- tab navigation -->
         <?php $tab = (isset($_GET['tab'])) ? $_GET['tab'] : null; ?>
         <ul class="nav nav-tabs">
            <li class="<?php echo ($tab == 'setting') ? 'active' : ''; ?>"><a href="#setting" data-toggle="tab"><i class="fa fa-cog" aria-hidden="true"></i> <?=label("Settings");?></a></li>
            <li class="<?php echo ($tab == 'users') ? 'active' : ''; ?>"><a href="#users" data-toggle="tab"><i class="fa fa-users" aria-hidden="true"></i> <?=label("users");?></a></li>
            <li class="<?php echo ($tab == 'warehouses') ? 'active' : ''; ?>"><a href="#warehouses" data-toggle="tab"><i class="fa fa-building" aria-hidden="true"></i> <?=label("Warehouses");?></a></li>
            <li class="<?php echo ($tab == 'webapp') ? 'active' : ''; ?>"><a href="#webapp" data-toggle="tab"><i class="fa fa-mobile" aria-hidden="true"></i> <?=label("WebAppTab");?></a></li>
            <li class="<?php echo ($tab == 'payment_methods') ? 'active' : ''; ?>"><a href="#payment_methods" data-toggle="tab"><i class="fa fa-money" aria-hidden="true"></i> <?=label("PaymentMethodsTab");?></a></li>
         </ul>

         <!-- tab sections -->
         <div class="tab-content">
            <!-- General setting tab -->
            <div class="tab-pane fade in <?php echo ($tab == 'setting') ? 'active' : ''; ?>" id="setting">
               <h1><?=label("Settings");?></h1>
               <p><?=label("SettingsDesciption");?></p>
               <?php echo form_open_multipart('settings/updateSettings'); ?>
                 <div class="form-group col-md-6">
                   <label for="companyName"><?=label("Company");?></label>
                   <input type="text" value="<?=$this->setting->companyname;?>" name="companyname" class="form-control" id="companyName" placeholder="<?=label("Company");?>">
                 </div>
                 <div class="form-group col-md-6">
                    <label for="logo"><?=label("CompanyLogo");?></label>
                    <input type="file" name="userfile" id="logo">
                    <?php if($this->setting->logo){ ?><img src="<?=base_url()?>files/Setting/<?=$this->setting->logo;?>" alt="" class="float-right" width="100px"/><?php } else { ?><img src="<?=base_url()?>assets/img/logo.png" alt="logo" class="float-right" width="100px"><?php } ?>
                 </div>
                 <div class="form-group col-md-6">
                   <label for="phone"><?=label("Phone");?></label>
                   <input type="text" value="<?=$this->setting->phone;?>" name="phone" class="form-control" id="phone" placeholder="<?=label("Phone");?>">
                 </div>
                 <div class="form-group col-md-6">
                   <label for="currency"><?=label("Currency");?></label>
                   <input type="text" value="<?=$this->setting->currency;?>" name="currency" class="form-control" id="currency" placeholder="<?=label("Currency");?>">
                 </div>
                 <div class="form-group col-md-3">
                   <label for="DefaultDiscount"><?=label("DefaultDiscount");?></label>
                   <input type="text" value="<?=$this->setting->discount;?>" name="discount" class="form-control" id="DefaultDiscount" placeholder="<?=label("DefaultDiscount");?>">
                 </div>
                 <div class="form-group col-md-3">
                   <label for="DefualtTax"><?=label("DefualtTax");?></label>
                   <input type="text" value="<?=$this->setting->tax;?>" name="tax" class="form-control" id="DefualtTax" placeholder="<?=label("DefualtTax");?>">
                 </div>
                 <div class="form-group col-md-6">
                   <label for="numberDecimal"><?=label("numberDecimal");?></label>
                   <select class="form-control" name="decimals" id="numberDecimal">
                      <option value="1" <?=$this->setting->decimals===1 ? 'selected' : '';?>>0.1</option>
                      <option value="2" <?=$this->setting->decimals===2 ? 'selected' : '';?>>0.01</option>
                      <option value="3" <?=$this->setting->decimals===3 ? 'selected' : '';?>>0.001</option>
                   </select>
                 </div>
                 <div class="form-group col-md-6">
                   <label>
                     <input type="hidden" name="keyboard" value="0" />
                     <input type="checkbox" name="keyboard" value="1" <?=strval($this->setting->keyboard) === '1' ? 'checked' : '';?>>
                     <span class="label-text"><?=label("keyboardDisplay");?></span>
                   </label>
                 </div>
                 <div class="form-group col-md-6">
                   <label>
                      <select name="timezone" class="form-control">
                         <option value="0"><?= label('timezone');?></option>
                         <?php foreach($Timezones as $t) { ?>
                           <option value="<?php print $t['zone'] ?>" <?= $t['zone'] === $this->setting->timezone ? 'selected' : ''; ?>>
                             <?php print $t['diff_from_GMT'] . ' - ' . $t['zone'] ?>
                           </option>
                         <?php } ?>
                       </select>
                   </label>
                 </div>

                 <div class="col-md-6">
                    <h4><?=label("ReceiptHeader");?></h4>
                    <textarea id="summernote" name="receiptheader"><?=$this->setting->receiptheader;?></textarea>
                  </div>
                  <div class="form-group col-md-6">
                     <h4><?=label("ReceiptFooter");?></h4>
                     <textarea  id="summernote2" name="receiptfooter"><?=$this->setting->receiptfooter;?></textarea>
                  </div>
                  <div class="form-group col-md-12">
                    <label data-toggle="collapse" data-target="#collapseStripe">
                      <input type="hidden" name="stripe" value="0" />
                      <input type="checkbox" name="stripe" value="1" <?=strval($this->setting->stripe) === '1' ? 'checked' : '';?>>
                      <span class="label-text">Stripe</span>
                    </label>
                  </div>
                  <div id="collapseStripe" class="panel-collapse collapse <?=strval($this->setting->stripe) === '1' ? 'in' : '';?>">
                     <div class="panel-body">
                       <div class="form-group col-md-6">
                          <label for="stripe_secret_key">stripe secret key</label>
                          <input type="text" value="<?=$this->setting->stripe_secret_key;?>" name="stripe_secret_key" class="form-control" id="stripe_secret_key" placeholder="stripe secret key">
                       </div>
                       <div class="form-group col-md-6">
                          <label for="stripe_publishable_key">stripe publishable key</label>
                          <input type="text" value="<?=$this->setting->stripe_publishable_key;?>" name="stripe_publishable_key" class="form-control" id="stripe_publishable_key" placeholder="stripe publishable key">
                       </div>
                     </div>
                  </div>
                  <div class="form-group col-md-12">
                     <h4><?=label("themesPick");?></h4>
                     <label class="themesPick col-md-3">
                        <input type="radio" name="theme" value="Light" <?=$this->setting->theme === 'Light' ? 'checked' : '';?>/>
                        <img src="<?=base_url()?>assets/img/Light-theme.jpg" alt="Light-theme">
                      </label>
                      <label class="themesPick col-md-3">
                        <input type="radio" name="theme" value="Dark" <?=$this->setting->theme === 'Dark' ? 'checked' : '';?> />
                        <img src="<?=base_url()?>assets/img/Dark-theme.jpg" alt="Dark-theme">
                      </label>
                  </div>
                 <div class="col-md-12">
                    <button type="submit" class="btn btn-add btn-lg"><?=label("Submit");?></button>
                 </div>
               <?php echo form_close(); ?>
            </div>
            <!-- users tab -->
            <div class="tab-pane fade in <?php echo ($tab == 'users') ? 'active' : ''; ?>" id="users">
               <table class="table">
                  <tr>
                     <th><b><?=label("Avatar");?></b></th>
                     <th><b><?=label("firstname");?></b></th>
                     <th><b><?=label("lastname");?></b></th>
                     <th><b><?=label("Username");?></b></th>
                     <th><b><?=label("Role");?></b></th>
                     <th><b><?=label("lastActive");?></b></th>
                     <th><b><?=label("Action");?></b></th>
                     <th><b><?=label("Store");?></b></th>
                  </tr>
                  <?php foreach ($Users as $user):?>
                   <tr>
                      <td><img class="img-circle topbar-userpic hidden-xs" src="<?=$user->avatar ? base_url().'files/Avatars/'.$user->avatar : base_url().'assets/img/Avatar.jpg' ?>" width="30px" height="30px"></td>
                      <td><?=$user->firstname;?></td>
                      <td><?=$user->lastname;?></td>
                      <td><?=$user->username;?></td>
                      <td><?=$user->role;?></td>
                      <td><?=$user->last_active;?></td>
                      <td><div class="btn-group">
                            <?php if($user->id !== 1){?><a class="btn btn-default" href="settings/deleteUser/<?=$user->id;?>" data-toggle="tooltip" data-placement="top" title="<?=label('Delete');?>"><i class="fa fa-times"></i></a><?php } ?>
                            <a class="btn btn-default" href="settings/editUser/<?=$user->id;?>" data-toggle="tooltip" data-placement="top" title="<?=label('Edit');?>"><i class="fa fa-pencil"></i></a>
                          </div>
                       </td>
                       <td><?php foreach ($stores as $store):?>
                          <?php if($store->id == $user->store_id) { echo $store->name; }?>
                       <?php endforeach;?></td>
                   </tr>
                <?php endforeach;?>
               </table>
               <!-- Button trigger modal -->
               <button type="button" class="btn btn-add btn-lg" data-toggle="modal" data-target="#AddUser">
                  <?=label("Adduser");?>
               </button>
            </div>
            <!-- Warehouse tab -->
            <div class="tab-pane fade in <?php echo ($tab == 'warehouses') ? 'active' : ''; ?>" id="warehouses">
               <table class="table">
                  <tr>
                     <th><?=label("WarehouseName");?></th>
                     <th><?=label("WarehousePhone");?></th>
                     <th><?=label("Email");?></th>
                     <th><?=label("Adresse");?></th>
                     <th><?=label("Action");?></th>
                  </tr>
                  <?php foreach ($warehouses as $warehouse):?>
                   <tr>
                      <td><?=$warehouse->name;?></td>
                      <td><?=$warehouse->phone;?></td>
                      <td><?=$warehouse->email;?></td>
                      <td><?=$warehouse->adresse;?></td>
                      <td><div class="btn-group">
                            <a class="btn btn-default" href="warehouses/delete/<?=$warehouse->id;?>" data-toggle="tooltip" data-placement="top" title="<?=label('Delete');?>"><i class="fa fa-times"></i></a>
                            <a class="btn btn-default" href="warehouses/edit/<?=$warehouse->id;?>" data-toggle="tooltip" data-placement="top" title="<?=label('Edit');?>"><i class="fa fa-pencil"></i></a>
                          </div>
                       </td>
                   </tr>
                   <?php endforeach;?>
                  </table>
                  <!-- Button trigger modal -->
                  <button type="button" class="btn btn-add btn-lg" data-toggle="modal" data-target="#AddWarehouse">
                     <?=label("AddWarehouse");?>
                  </button>
            </div>
            <div class="tab-pane fade in <?php echo ($tab == 'payment_methods') ? 'active' : ''; ?>" id="payment_methods">
               <h1><?=label("PaymentMethodsTab");?></h1>
               <p><?=label("PaymentMethodsIntro");?></p>
               <div class="alert alert-info"><?=label("PaymentMethodsDeleteHint");?></div>
               <?php if (! isset($payment_methods_list) || ! is_array($payment_methods_list)) { $payment_methods_list = array(); } ?>
               <?php if (count($payment_methods_list) === 0) { ?>
                  <div class="alert alert-warning"><?=label("PaymentMethodsRunSql");?> <code>application/sql/zarest_payment_methods.sql</code></div>
               <?php } else { ?>
               <p class="text-muted small" style="margin-bottom:10px;"><i class="fa fa-sort-numeric-asc" aria-hidden="true"></i> <?=label("PaymentMethodSortHelp");?></p>
               <table class="table table-striped">
                  <thead>
                     <tr>
                        <th><?=label("PaymentMethodName");?></th>
                        <th><?=label("PaymentMethodBehavior");?></th>
                        <th title="<?= htmlspecialchars(label('PaymentMethodSortHelp'), ENT_QUOTES, 'UTF-8'); ?>"><?=label("PaymentMethodSort");?> <i class="fa fa-question-circle text-muted" aria-hidden="true"></i></th>
                        <th><?=label("Action");?></th>
                     </tr>
                  </thead>
                  <tbody>
                  <?php foreach ($payment_methods_list as $pmm) {
                      $lk = isset($pmm->legacy_key) ? $pmm->legacy_key : null;
                      $is_builtin = ($lk !== null && $lk !== '');
                  ?>
                     <tr>
                        <td colspan="4">
                           <?php echo form_open('settings/updatePaymentMethod'); ?>
                           <input type="hidden" name="id" value="<?= (int) $pmm->id; ?>">
                           <div class="row" style="margin:0;">
                              <div class="col-md-4">
                                 <label class="sr-only"><?=label("PaymentMethodName");?></label>
                                 <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($pmm->name, ENT_QUOTES, 'UTF-8'); ?>" required>
                              </div>
                              <div class="col-md-3">
                                 <label class="sr-only"><?=label("PaymentMethodBehavior");?></label>
                                 <?php if ($is_builtin) { ?>
                                    <p class="form-control-static" style="margin:7px 0 0;"><?= htmlspecialchars($pmm->type_code, ENT_QUOTES, 'UTF-8'); ?></p>
                                 <?php } else { ?>
                                    <select name="type_code" class="form-control">
                                       <option value="cash" <?= $pmm->type_code === 'cash' ? 'selected' : ''; ?>><?=label("PaymentTypeCash");?></option>
                                       <option value="card" <?= $pmm->type_code === 'card' ? 'selected' : ''; ?>><?=label("PaymentTypeCard");?></option>
                                       <option value="cheque" <?= $pmm->type_code === 'cheque' ? 'selected' : ''; ?>><?=label("PaymentTypeCheque");?></option>
                                       <option value="other" <?= $pmm->type_code === 'other' ? 'selected' : ''; ?>><?=label("PaymentTypeOther");?></option>
                                    </select>
                                 <?php } ?>
                              </div>
                              <div class="col-md-2">
                                 <label class="sr-only"><?=label("PaymentMethodSort");?></label>
                                 <input type="number" name="sort_order" class="form-control" value="<?= (int) $pmm->sort_order; ?>">
                              </div>
                              <div class="col-md-3">
                                 <button type="submit" class="btn btn-default"><?=label("Submit");?></button>
                                 <?php if (! $is_builtin) { ?>
                                    <a class="btn btn-danger" href="<?= site_url('settings/deletePaymentMethod/'.$pmm->id); ?>" onclick="return confirm('<?= htmlspecialchars(label('Areyousure'), ENT_QUOTES, 'UTF-8'); ?>');"><?=label("Delete");?></a>
                                 <?php } else { ?>
                                    <span class="text-muted" style="margin-left:8px;white-space:nowrap;" title="<?= htmlspecialchars(label('PaymentMethodsDeleteHint'), ENT_QUOTES, 'UTF-8'); ?>"><?=label("PaymentMethodBuiltin");?></span>
                                 <?php } ?>
                              </div>
                           </div>
                           <?php echo form_close(); ?>
                        </td>
                     </tr>
                  <?php } ?>
                  </tbody>
               </table>
               <?php } ?>
               <h3><?=label("PaymentMethodAddNew");?></h3>
               <?php echo form_open('settings/addPaymentMethod'); ?>
               <div class="row">
                  <div class="col-md-4 form-group">
                     <label><?=label("PaymentMethodName");?></label>
                     <input type="text" name="name" class="form-control" placeholder="<?=label("PaymentMethodNamePlaceholder");?>" required>
                  </div>
                  <div class="col-md-4 form-group">
                     <label><?=label("PaymentMethodBehavior");?></label>
                     <select name="type_code" class="form-control">
                        <option value="other"><?=label("PaymentTypeOther");?> (Nequi, transferencia…)</option>
                        <option value="cash"><?=label("PaymentTypeCash");?></option>
                        <option value="card"><?=label("PaymentTypeCard");?></option>
                        <option value="cheque"><?=label("PaymentTypeCheque");?></option>
                     </select>
                  </div>
                  <div class="col-md-4 form-group" style="padding-top:24px;">
                     <button type="submit" class="btn btn-add"><?=label("PaymentMethodAddBtn");?></button>
                  </div>
               </div>
               <?php echo form_close(); ?>
            </div>
            <div class="tab-pane fade in <?php echo ($tab == 'webapp') ? 'active' : ''; ?>" id="webapp">
               <?php
               $_sw_path = parse_url(base_url(), PHP_URL_PATH);
               $_sw_scope = ($_sw_path !== null && $_sw_path !== '' && $_sw_path !== '/') ? rtrim($_sw_path, '/') . '/' : '/';
               ?>
               <h1><i class="fa fa-mobile"></i> <?=label("WebAppTab");?></h1>
               <div class="well">
                  <p><?=label("WebAppIntro");?></p>
                  <ul>
                     <li><?=label("WebAppStep1");?></li>
                     <li><?=label("WebAppStep2");?></li>
                     <li><?=label("WebAppStep3");?></li>
                  </ul>
                  <p class="text-muted small"><?=label("WebAppHttpsNote");?></p>
                  <hr>
                  <h4><?=label("WebAppPreferTitle");?></h4>
                  <p><?=label("WebAppPreferHelp");?></p>
                  <div class="checkbox">
                     <label>
                        <input type="checkbox" id="plateaWebAppLayout"> <?=label("WebAppPreferCheckbox");?>
                     </label>
                  </div>
                  <p id="pwa-sw-status" class="small text-muted"></p>
                  <button type="button" class="btn btn-default" id="btnPwaRegister"><?=label("WebAppRegisterSw");?></button>
               </div>
               <script type="application/json" id="platea-pwa-i18n"><?= json_encode(array(
                  'noSw' => label('WebAppNoSw'),
                  'swOk' => label('WebAppSwOk'),
                  'swFail' => label('WebAppSwFail'),
                  'swActive' => label('WebAppSwActive'),
               ), JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE); ?></script>
               <script>
               (function ($) {
                 var i18n = {};
                 try { i18n = JSON.parse(document.getElementById('platea-pwa-i18n').textContent); } catch (e) {}
                 $('#plateaWebAppLayout').prop('checked', localStorage.getItem('platea_webapp_layout') === '1');
                 $('#plateaWebAppLayout').on('change', function () {
                   if (this.checked) { localStorage.setItem('platea_webapp_layout', '1'); }
                   else { localStorage.removeItem('platea_webapp_layout'); }
                   location.reload();
                 });
                 function swMsg(t) { $('#pwa-sw-status').text(t); }
                 $('#btnPwaRegister').on('click', function () {
                   if (!('serviceWorker' in navigator)) { swMsg(i18n.noSw || ''); return; }
                   var swUrl = <?= json_encode(site_url('pwa/sw')); ?>;
                   var scope = <?= json_encode($_sw_scope); ?>;
                   navigator.serviceWorker.register(swUrl, { scope: scope })
                     .then(function () { swMsg(i18n.swOk || ''); })
                     .catch(function (e) { swMsg((i18n.swFail || '') + ' ' + (e && e.message ? e.message : '')); });
                 });
                 if ('serviceWorker' in navigator) {
                   navigator.serviceWorker.getRegistration().then(function (r) {
                     if (r) { swMsg(i18n.swActive || ''); }
                   });
                 }
               })(jQuery);
               </script>
            </div>
         </div>
      </div>
   </div>
</div>
<!-- /.container -->
<!-- add user Modal -->
<div class="modal fade" id="AddUser" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
 <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title" id="myModalLabel"><?=label("Adduser");?></h4>
      </div>
      <?php echo form_open_multipart('settings/addUser'); ?>
      <div class="modal-body">
            <div class="form-group">
             <label for="username"><?=label("Username");?> *</label>
             <input type="text" name="username" class="form-control" id="username" placeholder="<?=label("Username");?>" required>
           </div>
           <div class="form-group">
             <label for="firstname"><?=label("firstname");?> *</label>
             <input type="text" name="firstname" class="form-control" id="firstname" placeholder="<?=label("firstname");?>" required>
           </div>
           <div class="form-group">
             <label for="lastname"><?=label("lastname");?></label>
             <input type="text" name="lastname" class="form-control" id="lastname" placeholder="<?=label("lastname");?>">
           </div>
           <div class="form-group">
               <label for="role"><?=label("Role");?> *</label><br>
               <label class="radio-inline">
                 <input type="radio" name="role" id="role" value="admin" checked> <?=label("RoleAdimn");?>
               </label>
               <label class="radio-inline">
                 <input type="radio" name="role" id="role" value="sales"> <?=label("RoleSales");?>
               </label>
               <label class="radio-inline">
                 <input type="radio" name="role" id="role" value="waiter"> <?=label("Waiter");?>
               </label>
               <label class="radio-inline">
                 <input type="radio" name="role" id="role" value="kitchen"> <?=label("Kitchen");?>
               </label>
            </div>
            <div class="form-group" id="Storeslist">
              <label for="store_id"><?=label("Store");?></label>
                    <select class="form-control" name="store_id" id="store_id">
                      <?php foreach ($stores as $store):?>
                         <option value="<?=$store->id;?>"><?=$store->name;?></option>
                      <?php endforeach;?>
                    </select>

            </div>
           <div class="form-group">
             <label for="email"><?=label("Email");?></label>
             <input type="email" name="email" class="form-control" id="email" placeholder="<?=label("Email");?>">
           </div>
           <div class="form-group">
             <label for="password"><?=label("Password");?> *</label>
             <input type="password" name="password" class="form-control" id="password" placeholder="<?=label('Password');?>" required>
          </div>
           <div class="form-group">
             <label for="confirm_password"><?=label("PasswordRepeat");?> *</label>
             <input type="password" name="PasswordRepeat" class="form-control" id="confirm_password" placeholder="<?=label('PasswordRepeat');?>" required>
           </div>
           <div class="form-group">
             <label for="Avatar"><?=label("Avatar");?></label>
             <input type="file" name="userfile" id="Avatar">
           </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal"><?=label("Close");?></button>
        <button type="submit" class="btn btn-add"><?=label("Submit");?></button>
      </div>
   <?php echo form_close(); ?>
    </div>
 </div>
</div>
<!-- /.Modal -->


<!-- add warehouse Modal -->
<div class="modal fade" id="AddWarehouse" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
 <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title" id="myModalLabel"><?=label("AddWarehouse");?></h4>
      </div>
      <?php echo form_open_multipart('warehouses/add'); ?>
      <div class="modal-body">
            <div class="form-group">
             <label for="WarehouseName"><?=label("WarehouseName");?> *</label>
             <input type="text" name="name" class="form-control" id="WarehouseName" placeholder="<?=label("WarehouseName");?>" required>
           </div>
           <div class="form-group">
             <label for="WarehousePhone"><?=label("WarehousePhone");?></label>
             <input type="text" name="phone" class="form-control" id="WarehousePhone" placeholder="<?=label("WarehousePhone");?>">
          </div>
           <div class="form-group">
             <label for="email"><?=label("Email");?></label>
             <input type="email" name="email" class="form-control" id="email" placeholder="<?=label("Email");?>">
          </div>
           <div class="form-group">
             <label for="Adresse"><?=label("Adresse");?></label>
             <input type="text" name="adresse" class="form-control" id="Adresse" placeholder="<?=label("Adresse");?>">
           </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal"><?=label("Close");?></button>
        <button type="submit" class="btn btn-add"><?=label("Submit");?></button>
      </div>
   <?php echo form_close(); ?>
    </div>
 </div>
</div>
<!-- /.Modal -->

<script type="text/javascript">

/******** passwors confirmation validation ****************/

var currency = document.getElementById("currency");

function validatecurrency(){
  if(currency.value.length < 3) {
    currency.setCustomValidity("The Currency code must be at least 3 characters length");
  } else {
    currency.setCustomValidity('');
  }
}
if(currency) currency.onchange = validatecurrency;


$(document).ready(function () {

$("#Storeslist").slideUp();

$('input[type=radio][name=role]').on('change', function() {
  if( this.value == "waiter" || this.value == "kitchen" ) //if waiter or kitchen
  {
    $("#Storeslist").slideDown();
  } else {
     $("#Storeslist").slideUp();
  }
});

});
$('.collapse').collapse()
</script>

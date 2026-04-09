<div class="container container-small">
   <div class="row" style="margin-top:100px;">
      <a class="btn btn-default float-right" href="#" onclick="history.back(-1)"style="margin-bottom:10px;">
         <i class="fa fa-arrow-left"></i> <?=label("Back");?></a>
      <?php echo form_open_multipart('settings/editUser/'.$user->id); ?>

            <div class="form-group">
             <label for="username"><?=label("Username");?></label>
             <input type="text" name="username" value="<?=$user->username?>" class="form-control" id="username" placeholder="<?=label("Username");?>">
           </div>
           <div class="form-group">
             <label for="firstname"><?=label("firstname");?></label>
             <input type="text" name="firstname" value="<?=$user->firstname?>" class="form-control" id="firstname" placeholder="<?=label("firstname");?>">
           </div>
           <div class="form-group">
             <label for="lastname"><?=label("lastname");?></label>
             <input type="text" name="lastname" value="<?=$user->lastname?>" class="form-control" id="lastname" placeholder="<?=label("lastname");?>">
           </div>
           <div class="form-group">
               <label for="role"><?=label("Role");?></label><br>
               <label class="radio-inline">
                <input type="radio" name="role" id="role" value="admin" <?=$user->role==='admin' ? 'checked' : '';?>> <?=label("RoleAdimn");?>
               </label>
               <label class="radio-inline">
                <input type="radio" name="role" id="role" value="sales" <?=$user->role==='sales' ? 'checked' : '';?>> <?=label("RoleSales");?>
               </label>
               <label class="radio-inline">
                <input type="radio" name="role" id="role" value="waiter" <?=$user->role==='waiter' ? 'checked' : '';?>> <?=label("Waiter");?>
               </label>
               <label class="radio-inline">
                <input type="radio" name="role" id="role" value="kitchen" <?=$user->role==='kitchen' ? 'checked' : '';?>> <?=label("Kitchen");?>
               </label>
            </div>

            <div class="form-group" id="Storeslist">
              <?php
              $assignedStoreIds = array();
              if (isset($user->store_ids) && trim((string)$user->store_ids) !== '') {
                  foreach (explode(',', (string)$user->store_ids) as $sid) {
                      $sid = (int) trim($sid);
                      if ($sid > 0) {
                          $assignedStoreIds[] = $sid;
                      }
                  }
              } elseif (isset($user->store_id) && (int)$user->store_id > 0) {
                  $assignedStoreIds[] = (int) $user->store_id;
              }
              $assignedStoreIds = array_values(array_unique($assignedStoreIds));
              ?>
              <label for="store_ids"><?=label("Store");?></label>
                    <select class="form-control" name="store_ids[]" id="store_ids" multiple size="5">
                      <?php foreach ($stores as $store):?>
                         <option value="<?=$store->id;?>" <?=in_array((int)$store->id, $assignedStoreIds, true) ? 'selected' : '';?>><?=$store->name;?></option>
                      <?php endforeach;?>
                    </select>
                    <p class="text-muted small" style="margin-top:6px;">Puedes seleccionar una o varias tiendas.</p>

            </div>
            <div class="form-group" id="waiterPermissions">
              <label>Permisos de caja (camarero)</label>
              <div class="checkbox">
                <label>
                  <input type="hidden" name="can_open_register" value="0">
                  <input type="checkbox" name="can_open_register" value="1" <?=isset($user->can_open_register) && strval($user->can_open_register) === '1' ? 'checked' : '';?>>
                  <span class="label-text">Permitir apertura de tienda/caja en POS</span>
                </label>
              </div>
              <div class="checkbox">
                <label>
                  <input type="hidden" name="can_close_register" value="0">
                  <input type="checkbox" name="can_close_register" value="1" <?=isset($user->can_close_register) && strval($user->can_close_register) === '1' ? 'checked' : '';?>>
                  <span class="label-text">Permitir cierre de caja en POS</span>
                </label>
              </div>
            </div>
           <div class="form-group">
             <label for="email"><?=label("Email");?></label>
             <input type="email" name="email" value="<?=$user->email?>" class="form-control" id="email" placeholder="<?=label("Email");?>">
           </div>
           <div class="form-group">
             <label for="password"><?=label("Password");?></label>
             <input type="password" name="password" class="form-control" id="password" placeholder="<?=label('Password');?>">
          </div>
           <div class="form-group">
             <label for="PasswordRepeat"><?=label("PasswordRepeat");?></label>
             <input type="password" name="PasswordRepeat" class="form-control" id="PasswordRepeat" placeholder="<?=label('PasswordRepeat');?>">
           </div>
           <div class="form-group">
             <label for="Avatar"><?=label("Avatar");?></label>
             <input type="file" name="userfile" id="Avatar">
           </div>
           <?php if($user->avatar){ ?><img src="<?=base_url()?>files/Avatars/<?=$user->avatar;?>" alt="" class="float-right" width="150px"/><?php }else{ ?><img src="<?=base_url()?>assets/img/Avatar.jpg" alt="" class="float-right" width="150px"/><?php } ?>

      <div class="form-group">
        <button type="submit" class="btn btn-green col-md-6 flat-box-btn"><?=label("Submit");?></button>
      </div>
      <?php echo form_close(); ?>
    </div>
</div>

<script type="text/javascript">
$(document).ready(function () {

<?=$user->role==='admin' || $user->role==='sales' ? '$("#Storeslist").slideUp();' : '';?>

function toggleWaiterFieldsByRole(roleValue) {
  var isStoreRole = (roleValue === "waiter" || roleValue === "kitchen");
  var isWaiterRole = (roleValue === "waiter");
  if (isStoreRole) {
    $("#Storeslist").slideDown();
  } else {
    $("#Storeslist").slideUp();
  }
  if (isWaiterRole) {
    $("#waiterPermissions").slideDown();
  } else {
    $("#waiterPermissions").slideUp();
  }
}

$('input[type=radio][name=role]').on('change', function() {
  toggleWaiterFieldsByRole(this.value);
});
toggleWaiterFieldsByRole($('input[type=radio][name=role]:checked').val());

});
</script>

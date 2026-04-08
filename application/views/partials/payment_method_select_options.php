<?php defined('BASEPATH') or exit('No direct script access allowed');
$__pms = ! empty($payment_methods) ? $payment_methods : array();
if (count($__pms) > 0) :
    foreach ($__pms as $pm) :
        ?>
<option value="<?= htmlspecialchars($pm->stored_key(), ENT_QUOTES, 'UTF-8'); ?>" data-type="<?= htmlspecialchars($pm->type_code, ENT_QUOTES, 'UTF-8'); ?>"><?= htmlspecialchars($pm->name, ENT_QUOTES, 'UTF-8'); ?></option>
        <?php
    endforeach;
else :
    ?>
<option value="0" data-type="cash"><?= label('Cash'); ?></option>
<option value="1" data-type="card"><?= label('CreditCard'); ?></option>
<option value="2" data-type="cheque"><?= label('Cheque'); ?></option>
    <?php
endif;

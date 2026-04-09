<?php

class Setting extends ActiveRecord\Model {

   public static $table_name = 'zarest_settings';

   public static $after_construct = array('apply_decimals_bounds');

   /**
    * Asegura decimals entre 0 y 3; NULL/vacío → 2 (compatibilidad con datos antiguos).
    */
   public function apply_decimals_bounds()
   {
      $dec = $this->decimals;
      $this->decimals = ($dec === null || $dec === '') ? 2 : max(0, min(3, (int) $dec));
   }
}

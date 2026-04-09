<div class="container">
   <ul class="nav nav-tabs" id="statsMainTabs" role="tablist" style="margin-top:60px;margin-bottom:8px;">
      <li role="presentation" class="active"><a href="#statsTabOverview" aria-controls="statsTabOverview" role="tab" data-toggle="tab"><?=label('StatsTabStatistics');?></a></li>
      <li role="presentation"><a href="#statsTabReports" aria-controls="statsTabReports" role="tab" data-toggle="tab"><?=label('Reports');?></a></li>
   </ul>
   <div class="tab-content">
   <div role="tabpanel" class="tab-pane active" id="statsTabOverview">
   <div class="row" style="margin-top:20px;">
      <div class="col-md-4">
         <div class="statCart Statcolor01">
   			<i class="fa fa-users" aria-hidden="true"></i>
   			<h1 class="count"><?=$CustomerNumber;?></h1><br>
   			<span><?=label('Customers');?></span>
		    </div>
      </div>
      <div class="col-md-4">
         <div class="statCart Statcolor02">
   			<i class="fa fa-archive" aria-hidden="true"></i>
   			<h1 class="count"><?=$ProductNumber;?></h1><br>
   			<span><?=label('Product');?> (<?=label('in');?> <?=$CategoriesNumber;?> <?=label('Categories');?>)</span>
		    </div>
      </div>
      <div class="col-md-4">
         <div class="statCart Statcolor03">
   			<i class="fa fa-money" aria-hidden="true"></i>
   			<h2 style="display: inline"><span class="count"><?=$TodaySales;?></span> <?=$this->setting->currency;?></h2><br>
   			<span><?=label('TodaySale');?></span>
		    </div>
      </div>
   </div>
   <?php
   if (! isset($todaySalesByPaymentMethod) || ! is_array($todaySalesByPaymentMethod)) {
       $todaySalesByPaymentMethod = array();
   }
   if (count($todaySalesByPaymentMethod) > 0) {
       $pmStatColors = array('Statcolor01', 'Statcolor02', 'Statcolor03');
       $pmColorIdx = 0;
   ?>
   <div class="row" style="margin-top:20px;">
      <?php foreach ($todaySalesByPaymentMethod as $pmt) {
          $pmIcon = 'fa-money';
          if (isset($pmt['type_code'])) {
              if ($pmt['type_code'] === 'card') {
                  $pmIcon = 'fa-credit-card';
              } elseif ($pmt['type_code'] === 'cheque') {
                  $pmIcon = 'fa-file-text-o';
              }
          }
          $pmCls = $pmStatColors[$pmColorIdx % 3];
          $pmColorIdx++;
      ?>
      <div class="col-md-4 col-sm-6" style="margin-bottom:15px;">
         <div class="statCart <?=$pmCls;?>">
            <i class="fa <?=$pmIcon;?>" aria-hidden="true"></i>
            <h2 style="display: inline"><span class="count"><?=$pmt['total'];?></span> <?=$this->setting->currency;?></h2><br>
            <span><?=htmlspecialchars($pmt['name'], ENT_QUOTES, 'UTF-8');?> — <?=label('TodaySale');?></span>
         </div>
      </div>
      <?php } ?>
   </div>
   <?php } ?>
   <div class="row" style="margin-top:50px;">
      <div class="col-md-8">
         <!-- chart container  -->
         <div class="statCart">
            <h3><?=label('monthlyStats');?></h3>
            <div style="width:100%">
               <canvas id="canvas" height="330" width="750"></canvas>
            </div>
         </div>
      </div>
      <div class="col-md-4">
         <?php
         if (! isset($Top5categories) || ! is_array($Top5categories)) {
             $Top5categories = array();
         }
         ?>
         <!-- top categorías (histórico) -->
         <div class="statCart" style="margin-bottom:24px;">
            <h3><?=label('TopCategories');?></h3>
            <p class="text-muted small" style="margin-top:-6px;"><?=label('TopCategoriesAllTimeHint');?></p>
            <div id="canvas-holder-categories">
               <?php if (count($Top5categories) > 0) { ?>
               <canvas id="chart-area-categories" width="230" height="230"></canvas>
               <table class="table table-condensed table-striped" style="margin-top:12px;font-size:12px;">
                  <thead>
                     <tr>
                        <th><?=label('Category');?></th>
                        <th class="text-right"><?=label('TopProductsColQty');?></th>
                        <th class="text-right"><?=label('TopProductsColQtyPct');?></th>
                        <th class="text-right"><?=label('TopProductsColRev');?></th>
                        <th class="text-right"><?=label('TopProductsColRevPct');?></th>
                     </tr>
                  </thead>
                  <tbody>
                  <?php
                  $catcolors = array('#8E44AD', '#9B59B6', '#3498DB', '#1ABC9C', '#16A085');
                  foreach ($Top5categories as $ci => $tc) {
                  ?>
                     <tr>
                        <td><span class="label label-default" style="background-color:<?= $catcolors[$ci % 5]; ?>;display:inline-block;max-width:100%;white-space:normal;text-align:left;"><?=htmlspecialchars($tc->name, ENT_QUOTES, 'UTF-8');?></span></td>
                        <td class="text-right"><?= (int) $tc->totalquantity; ?></td>
                        <td class="text-right"><?= htmlspecialchars((string) $tc->pct_quantity, ENT_QUOTES, 'UTF-8'); ?>%</td>
                        <td class="text-right"><?= number_format((float) $tc->total_revenue, $this->setting->decimals, '.', ''); ?> <?=$this->setting->currency;?></td>
                        <td class="text-right"><?= htmlspecialchars((string) $tc->pct_revenue, ENT_QUOTES, 'UTF-8'); ?>%</td>
                     </tr>
                  <?php } ?>
                  </tbody>
               </table>
               <?php } else { ?>
               <h3 style="margin: 50px 0"><?=label("EmptyList");?></h3>
               <?php } ?>
            </div>
         </div>
         <!-- top productos (histórico) -->
         <div class="statCart">
            <h3><?=label('TopProducts');?></h3>
            <p class="text-muted small" style="margin-top:-6px;"><?=label('TopProductsAllTimeHint');?></p>
            <div id="canvas-holder">
               <?php if (count($Top5product) > 0) { ?>
               <canvas id="chart-area2" width="230" height="230"></canvas>
               <table class="table table-condensed table-striped" style="margin-top:12px;font-size:12px;">
                  <thead>
                     <tr>
                        <th><?=label('Product');?></th>
                        <th class="text-right"><?=label('TopProductsColQty');?></th>
                        <th class="text-right"><?=label('TopProductsColQtyPct');?></th>
                        <th class="text-right"><?=label('TopProductsColRev');?></th>
                        <th class="text-right"><?=label('TopProductsColRevPct');?></th>
                     </tr>
                  </thead>
                  <tbody>
                  <?php
                  $top5colors = array('#F3565D', '#FC9D9B', '#FACDAE', '#9FC2C4', '#8297A8');
                  foreach ($Top5product as $ti => $tp) {
                  ?>
                     <tr>
                        <td><span class="label label-default" style="background-color:<?= $top5colors[$ti % 5]; ?>;display:inline-block;max-width:100%;white-space:normal;text-align:left;"><?=htmlspecialchars($tp->name, ENT_QUOTES, 'UTF-8');?></span></td>
                        <td class="text-right"><?= (int) $tp->totalquantity; ?></td>
                        <td class="text-right"><?= htmlspecialchars((string) $tp->pct_quantity, ENT_QUOTES, 'UTF-8'); ?>%</td>
                        <td class="text-right"><?= number_format((float) $tp->total_revenue, $this->setting->decimals, '.', ''); ?> <?=$this->setting->currency;?></td>
                        <td class="text-right"><?= htmlspecialchars((string) $tp->pct_revenue, ENT_QUOTES, 'UTF-8'); ?>%</td>
                     </tr>
                  <?php } ?>
                  </tbody>
               </table>
               <?php } else { ?>
               <h3 style="margin: 50px 0"><?=label("EmptyList");?></h3>
               <?php } ?>
            </div>
         </div>
      </div>
   </div>
   <!-- ************************************************************************************************** -->

   <div class="row rangeStat" style="margin-top:50px; margin-bottom:70px;">
      <div class="col-md-12">
         <div class="statCart">
            <h1 class="statYear"><?=$year;?></h1>
            <button class="btn btn-Year" type="button" onclick="getyearstats('next')"><</button>
            <button class="btn btn-Year" type="button" onclick="getyearstats('prev')">></button>
            <div class="float-right" style="margin-top: 50px;">
               <span class="revenuespan" style="font-size:11px;"><?=label("Revenue");?></span>
               <span class="expencespan" style="font-size:11px;"><?=label("Expense");?></span>
            </div>
            <div id="statyears">
               <table class="StatTable">
                  <tr>
                     <td><span class="revenuespan" data-toggle="tooltip" data-placement="top"  data-html="true" title="<h5><?=label('tax');?> : <b><?=$monthly[0]->januarytax;?> <?=$this->setting->currency;?></b> <br><br> <?=label('Discount');?> : <b><?=$monthly[0]->januarydisc;?> <?=$this->setting->currency;?></b></h5>"><?=$monthly[0]->january;?> <?=$this->setting->currency;?></span><span class="expencespan"><?=$monthlyExp[0]->january;?> <?=$this->setting->currency;?></span><?=label('January');?></td>
                     <td><span class="revenuespan" data-toggle="tooltip" data-placement="top"  data-html="true" title="<h5><?=label('tax');?> : <b><?=$monthly[0]->feburarytax;?> <?=$this->setting->currency;?></b> <br><br> <?=label('Discount');?> : <b><?=$monthly[0]->feburarydisc;?> <?=$this->setting->currency;?></b></h5>"><?=$monthly[0]->feburary;?> <?=$this->setting->currency;?></span><span class="expencespan"><?=$monthlyExp[0]->feburary;?> <?=$this->setting->currency;?></span><?=label('February');?></td>
                     <td><span class="revenuespan" data-toggle="tooltip" data-placement="top"  data-html="true" title="<h5><?=label('tax');?> : <b><?=$monthly[0]->marchtax;?> <?=$this->setting->currency;?></b> <br><br> <?=label('Discount');?> : <b><?=$monthly[0]->marchdisc;?> <?=$this->setting->currency;?></b></h5>"><?=$monthly[0]->march;?> <?=$this->setting->currency;?></span><span class="expencespan"><?=$monthlyExp[0]->march;?> <?=$this->setting->currency;?></span><?=label('March');?></td>
                     <td><span class="revenuespan" data-toggle="tooltip" data-placement="top"  data-html="true" title="<h5><?=label('tax');?> : <b><?=$monthly[0]->apriltax;?> <?=$this->setting->currency;?></b> <br><br> <?=label('Discount');?> : <b><?=$monthly[0]->aprildisc;?> <?=$this->setting->currency;?></b></h5>"><?=$monthly[0]->april;?> <?=$this->setting->currency;?></span><span class="expencespan"><?=$monthlyExp[0]->april;?> <?=$this->setting->currency;?></span><?=label('April');?></td>
                  </tr>
                  <tr>
                     <td><span class="revenuespan" data-toggle="tooltip" data-placement="top"  data-html="true" title="<h5><?=label('tax');?> : <b><?=$monthly[0]->maytax;?> <?=$this->setting->currency;?></b> <br><br> <?=label('Discount');?> : <b><?=$monthly[0]->maydisc;?> <?=$this->setting->currency;?></b></h5>"><?=$monthly[0]->may;?> <?=$this->setting->currency;?></span><span class="expencespan"><?=$monthlyExp[0]->may;?> <?=$this->setting->currency;?></span><?=label('May');?></td>
                     <td><span class="revenuespan" data-toggle="tooltip" data-placement="top"  data-html="true" title="<h5><?=label('tax');?> : <b><?=$monthly[0]->junetax;?> <?=$this->setting->currency;?></b> <br><br> <?=label('Discount');?> : <b><?=$monthly[0]->junedisc;?> <?=$this->setting->currency;?></b></h5>"><?=$monthly[0]->june;?> <?=$this->setting->currency;?></span><span class="expencespan"><?=$monthlyExp[0]->june;?> <?=$this->setting->currency;?></span><?=label('June');?></td>
                     <td><span class="revenuespan" data-toggle="tooltip" data-placement="top"  data-html="true" title="<h5><?=label('tax');?> : <b><?=$monthly[0]->julytax;?> <?=$this->setting->currency;?></b> <br><br> <?=label('Discount');?> : <b><?=$monthly[0]->julydisc;?> <?=$this->setting->currency;?></b></h5>"><?=$monthly[0]->july;?> <?=$this->setting->currency;?></span><span class="expencespan"><?=$monthlyExp[0]->july;?> <?=$this->setting->currency;?></span><?=label('July');?></td>
                     <td><span class="revenuespan" data-toggle="tooltip" data-placement="top"  data-html="true" title="<h5><?=label('tax');?> : <b><?=$monthly[0]->augusttax;?> <?=$this->setting->currency;?></b> <br><br> <?=label('Discount');?> : <b><?=$monthly[0]->augustdisc;?> <?=$this->setting->currency;?></b></h5>"><?=$monthly[0]->august;?> <?=$this->setting->currency;?></span><span class="expencespan"><?=$monthlyExp[0]->august;?> <?=$this->setting->currency;?></span><?=label('August');?></td>
                  </tr>
                  <tr>
                     <td><span class="revenuespan" data-toggle="tooltip" data-placement="top"  data-html="true" title="<h5><?=label('tax');?> : <b><?=$monthly[0]->septembertax;?> <?=$this->setting->currency;?></b> <br><br> <?=label('Discount');?> : <b><?=$monthly[0]->septemberdisc;?> <?=$this->setting->currency;?></b></h5>"><?=$monthly[0]->september;?> <?=$this->setting->currency;?></span><span class="expencespan"><?=$monthlyExp[0]->september;?> <?=$this->setting->currency;?></span><?=label('September');?></td>
                     <td><span class="revenuespan" data-toggle="tooltip" data-placement="top"  data-html="true" title="<h5><?=label('tax');?> : <b><?=$monthly[0]->octobertax;?> <?=$this->setting->currency;?></b> <br><br> <?=label('Discount');?> : <b><?=$monthly[0]->octoberdisc;?> <?=$this->setting->currency;?></b></h5>"><?=$monthly[0]->october;?> <?=$this->setting->currency;?></span><span class="expencespan"><?=$monthlyExp[0]->october;?> <?=$this->setting->currency;?></span><?=label('October');?></td>
                     <td><span class="revenuespan" data-toggle="tooltip" data-placement="top"  data-html="true" title="<h5><?=label('tax');?> : <b><?=$monthly[0]->novembertax;?> <?=$this->setting->currency;?></b> <br><br> <?=label('Discount');?> : <b><?=$monthly[0]->novemberdisc;?> <?=$this->setting->currency;?></b></h5>"><?=$monthly[0]->november;?> <?=$this->setting->currency;?></span><span class="expencespan"><?=$monthlyExp[0]->november;?> <?=$this->setting->currency;?></span><?=label('November');?></td>
                     <td><span class="revenuespan" data-toggle="tooltip" data-placement="top"  data-html="true" title="<h5><?=label('tax');?> : <b><?=$monthly[0]->decembertax;?> <?=$this->setting->currency;?></b> <br><br> <?=label('Discount');?> : <b><?=$monthly[0]->decemberdisc;?> <?=$this->setting->currency;?></b></h5>"><?=$monthly[0]->december;?> <?=$this->setting->currency;?></span><span class="expencespan"><?=$monthlyExp[0]->december;?> <?=$this->setting->currency;?></span><?=label('December');?></td>
                  </tr>
               </table>
            </div>
         </div>
      </div>
   </div>
   </div><!-- /#statsTabOverview -->

   <div role="tabpanel" class="tab-pane" id="statsTabReports">
   <style>
   /* Select2 calcula ancho en px; forzamos 100% del col-md-5 junto al rango de fechas */
   #statsTabReports .select2-container { width: 100% !important; max-width: 100%; }
   </style>
   <div class="row rangeStat" style="margin-top:20px;">
      <h3 class="col-sm-12"><?=label('ClientsStats');?></h3>
      <div class="col-md-5">
         <div class="form-group">
             <label for="customerSelect"><?=label('SelectClient');?></label>
               <select class="js-select-options form-control" id="customerSelect">
                  <option value="0"><?=label("WalkinCustomer");?></option>
                 <?php foreach ($customers as $customer):?>
                    <option value="<?=$customer->id;?>"><?=$customer->name;?></option>
                 <?php endforeach;?>
               </select>
         </div>
      </div>
      <div class="col-md-5">
            <div class="form-group">
                <label><?=label('SelectRange');?></label>
            <div class="input-group margin-bottom-sm">
               <span class="input-group-addon RangePicker"><i class="fa fa-calendar fa-fw" aria-hidden="true"></i></span>
               <input class="form-control" id="CustomerRange" type="text" name="daterange" />
            </div>
         </div>
      </div>
      <div class="col-md-2">
         <button class="cancelBtn btn btn-picker" type="button" onclick="getCustomerReport()"><?=label('GetReport');?></button>
      </div>
   </div>

   <div class="row rangeStat" style="margin-top:50px;">
      <h3 class="col-sm-12"><?=label('CategoriesStats');?></h3>
      <div class="col-md-5">
         <div class="form-group">
             <label for="categorySelect"><?=label('SelectCategory');?></label>
               <select class="js-select-options form-control" id="categorySelect">
                  <option value="0"><?= htmlspecialchars(label('AllCategories'), ENT_QUOTES, 'UTF-8'); ?></option>
                  <?php
                  if (! isset($Categories) || ! is_array($Categories)) {
                      $Categories = array();
                  }
                  foreach ($Categories as $cat) :
                  ?>
                    <option value="<?=(int) $cat->id;?>"><?=htmlspecialchars($cat->name, ENT_QUOTES, 'UTF-8');?></option>
                 <?php endforeach;?>
               </select>
         </div>
      </div>
      <div class="col-md-5">
            <div class="form-group">
                <label><?=label('SelectRange');?></label>
            <div class="input-group margin-bottom-sm">
               <span class="input-group-addon RangePicker"><i class="fa fa-calendar fa-fw" aria-hidden="true"></i></span>
               <input class="form-control" id="CategoryRange" type="text" name="daterangeC" />
            </div>
         </div>
      </div>
      <div class="col-md-2">
         <button class="cancelBtn btn btn-picker" type="button" onclick="getCategoryReport()"><?=label('GetReport');?></button>
      </div>
   </div>

   <div class="row rangeStat" style="margin-top:50px;">
      <h3 class="col-sm-12"><?=label('ProductsStats');?></h3>
      <div class="col-md-5">
         <div class="form-group">
             <label for="customerSelect"><?=label('SelectProduct');?></label>
               <select class="js-select-options form-control" id="productSelect">
                  <?php foreach ($Products as $product):?>
                    <option value="<?=$product->id;?>"><?=$product->name;?></option>
                 <?php endforeach;?>
               </select>
         </div>
      </div>
      <div class="col-md-5">
            <div class="form-group">
                <label><?=label('SelectRange');?></label>
            <div class="input-group margin-bottom-sm">
               <span class="input-group-addon RangePicker"><i class="fa fa-calendar fa-fw" aria-hidden="true"></i></span>
               <input class="form-control" id="ProductRange" type="text" name="daterangeP" />
            </div>
         </div>
      </div>
      <div class="col-md-2">
         <button class="cancelBtn btn btn-picker" type="button" onclick="getProductReport()"><?=label('GetReport');?></button>
      </div>
   </div>
   <div class="row rangeStat" style="margin-top:50px;">
      <h3 class="col-sm-12"><?=label('RegisterStats');?></h3>
      <div class="col-md-5">
         <div class="form-group">
             <label for="customerSelect"><?=label('SelectStore');?></label>
               <select class="js-select-options form-control" id="StoresSelect">
                  <?php foreach ($Stores as $store):?>
                    <option value="<?=$store->id;?>"><?=$store->name;?></option>
                 <?php endforeach;?>
               </select>
         </div>
      </div>
      <div class="col-md-5">
            <div class="form-group">
                <label><?=label('SelectRange');?></label>
            <div class="input-group margin-bottom-sm">
               <span class="input-group-addon RangePicker"><i class="fa fa-calendar fa-fw" aria-hidden="true"></i></span>
               <input class="form-control" id="RegisterRange" type="text" name="daterangeR" />
            </div>
         </div>
      </div>
      <div class="col-md-2">
         <button class="cancelBtn btn btn-picker" type="button" onclick="getRegisterReport()"><?=label('GetReport');?></button>
      </div>
   </div>
   <!-- ********************************************* warehouses report ***************************************************** -->
   <div class="row rangeStat" style="margin-top:50px;margin-bottom: 100px;">
      <h3 class="col-sm-12"><?=label('StockStatsTitle');?></h3>
      <div class="col-md-5">
         <div class="form-group">
             <label for="customerSelect"><?=label('SelectStore');?></label>
               <select class="js-select-options form-control" id="StockSelect">
                  <?php foreach ($Stores as $store):?>
                    <option value="S<?=$store->id;?>"><?=$store->name;?></option>
                 <?php endforeach;?>
                 <?php foreach ($Warehouses as $warehouse):?>
                   <option value="W<?=$warehouse->id;?>"><?=$warehouse->name;?></option>
                <?php endforeach;?>
               </select>
         </div>
      </div>
      <div class="col-md-5">
            <div class="form-group">
                <label></label>
            <div class="input-group margin-bottom-sm">
               <span class="input-group-addon RangePicker"><i class="fa fa-calendar fa-fw" aria-hidden="true"></i></span>
               <input class="form-control" id="" type="text" name="" disabled />
            </div>
         </div>
      </div>
      <div class="col-md-2">
         <button class="cancelBtn btn btn-picker" type="button" onclick="getStockReport()"><?=label('GetReport');?></button>
      </div>
   </div>
   </div><!-- /#statsTabReports -->
   </div><!-- /.tab-content -->

</div>
<!--[ footer ] -->
<div id="footer" style="background-color: #8297A8;width: 100%;">
  <div class="container">
    <p class="footer-block" style="margin: 20px 0;color:#fff;"><?=label('title');?> <?= $this->setting->companyname;?>.</p>
  </div>
</div>

	<script>
   /******* Range date picker *******/
   $(function() {
      $('input[name="daterange"]').daterangepicker();
      $('input[name="daterangeP"]').daterangepicker();
      $('input[name="daterangeR"]').daterangepicker();
      $('input[name="daterangeC"]').daterangepicker();
      var d = new Date().getFullYear();
      $('#ProductRange').val('01/01/'+d+' - 12/31/'+d);
      $('#CategoryRange').val('01/01/'+d+' - 12/31/'+d);
      $('#CustomerRange').val('01/01/'+d+' - 12/31/'+d);
      $('#RegisterRange').val('01/01/'+d+' - 12/31/'+d);

   });
   /************************ Chart Data *************************/
		var randomScalingFactor = function(){ return Math.round(Math.random()*100)};
		var lineChartData = {
			labels : ["<?=label('January');?>","<?=label('February');?>","<?=label('March');?>","<?=label('April');?>","<?=label('May');?>","<?=label('June');?>","<?=label('July');?>","<?=label('August');?>","<?=label('September');?>","<?=label('October');?>","<?=label('November');?>","<?=label('December');?>"],
			datasets : [
            {
               label: "<?=label('Expences');?>",
               backgroundColor: "rgba(255,99,132,0.2)",
               borderColor: "#FE9375",
               pointBackgroundColor: "#FE9375",
               pointBorderColor: "#fff",
               pointHoverBackgroundColor: "#fff",
               pointHoverBorderColor: "#FE9375",
               data: [<?=$monthlyExp[0]->january;?>,<?=$monthlyExp[0]->feburary;?>,<?=$monthlyExp[0]->march;?>,<?=$monthlyExp[0]->april;?>,<?=$monthlyExp[0]->may;?>,<?=$monthlyExp[0]->june;?>,<?=$monthlyExp[0]->july;?>,<?=$monthlyExp[0]->august;?>,<?=$monthlyExp[0]->september;?>,<?=$monthlyExp[0]->october;?>,<?=$monthlyExp[0]->november;?>,<?=$monthlyExp[0]->december;?>]
            },
				{
					label: "<?=label('Revenue');?>",
					backgroundColor : "#2AC4C0",
					borderColor : "#26a5a2",
					pointBackgroundColor : "#2AC4C0",
					pointBorderColor : "#fff",
					pointHoverBackgroundColor : "#fff",
					pointHoverBorderColor : "#fff",
					data : [<?=$monthly[0]->january;?>,<?=$monthly[0]->feburary;?>,<?=$monthly[0]->march;?>,<?=$monthly[0]->april;?>,<?=$monthly[0]->may;?>,<?=$monthly[0]->june;?>,<?=$monthly[0]->july;?>,<?=$monthly[0]->august;?>,<?=$monthly[0]->september;?>,<?=$monthly[0]->october;?>,<?=$monthly[0]->november;?>,<?=$monthly[0]->december;?>]
				}
			]
		}
	window.onload = function(){

      // Chart.defaults.global.gridLines.display = false;

		var ctx = document.getElementById("canvas").getContext("2d");
		window.myLine = new Chart(ctx, {
    type: 'line',
    data: lineChartData,
    options: {
         scales : {
           xAxes : [ {
                   gridLines : {
                      display : false
                   }
              } ],
           yAxes : [ {
                   gridLines : {
                      display : true
                   }
              } ]
          },
         scaleFontSize: 9,
         tooltipFillColor: "rgba(0, 0, 0, 0.71)",
         tooltipFontSize: 10,
			responsive: true
		}});

      /********************* pie (top categorías histórico) **********************/
      <?php
      if (! isset($Top5categories) || ! is_array($Top5categories)) {
          $Top5categories = array();
      }
      if (count($Top5categories) > 0) {
          $catPieLabels = array();
          $catPieQty = array();
          $catPiePctQty = array();
          $catPiePctRev = array();
          foreach ($Top5categories as $crow) {
              $catPieLabels[] = $crow->name;
              $catPieQty[] = (float) $crow->totalquantity;
              $catPiePctQty[] = (float) $crow->pct_quantity;
              $catPiePctRev[] = (float) $crow->pct_revenue;
          }
          $catN = count($catPieLabels);
          $catBg = array_slice(array('#8E44AD', '#9B59B6', '#3498DB', '#1ABC9C', '#16A085'), 0, $catN);
          $catHoverBg = array_slice(array('#5B2C6F', '#6C3483', '#21618C', '#117A65', '#0E6655'), 0, $catN);
          $catHoverW = array_fill(0, $catN, 5);
          $catTipQty = json_encode(label('TopProductsTooltipQty'));
          $catTipQtyPct = json_encode(label('TopProductsTooltipQtyPct'));
          $catTipRevPct = json_encode(label('TopProductsTooltipRevPct'));
      ?>
      var catPieLabels = <?= json_encode($catPieLabels, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE); ?>;
      var catPieQty = <?= json_encode($catPieQty); ?>;
      var catPiePctQty = <?= json_encode($catPiePctQty); ?>;
      var catPiePctRev = <?= json_encode($catPiePctRev); ?>;
      var catTipQty = <?= $catTipQty; ?>;
      var catTipQtyPct = <?= $catTipQtyPct; ?>;
      var catTipRevPct = <?= $catTipRevPct; ?>;

      var catPieData = {
          labels: catPieLabels,
          datasets: [{
               data: catPieQty,
               backgroundColor: <?= json_encode($catBg); ?>,
               hoverBackgroundColor: <?= json_encode($catHoverBg); ?>,
               hoverBorderWidth: <?= json_encode($catHoverW); ?>
          }]
      };

      var elCatPie = document.getElementById("chart-area-categories");
      if (elCatPie) {
         var ctxCat = elCatPie.getContext("2d");
         window.myPieCategories = new Chart(ctxCat, {
             type: 'doughnut',
             data: catPieData,
             options: {
                tooltips: {
                   callbacks: {
                      label: function (tooltipItem, data) {
                         var i = tooltipItem.index;
                         var qty = data.datasets[0].data[i];
                         return data.labels[i] + ': ' + qty + ' ' + catTipQty + '\n' + catTipQtyPct + ': ' + catPiePctQty[i] + '%\n' + catTipRevPct + ': ' + catPiePctRev[i] + '%';
                      }
                   }
                }
             }
         });
      }
      <?php } ?>

      /********************* pie (top productos histórico) **********************/
      <?php
      if (count($Top5product) > 0) {
          $pieLabels = array();
          $pieQty = array();
          $piePctQty = array();
          $piePctRev = array();
          foreach ($Top5product as $row) {
              $pieLabels[] = $row->name;
              $pieQty[] = (float) $row->totalquantity;
              $piePctQty[] = (float) $row->pct_quantity;
              $piePctRev[] = (float) $row->pct_revenue;
          }
          $pieN = count($pieLabels);
          $pieBg = array_slice(array('#F3565D', '#FC9D9B', '#FACDAE', '#9FC2C4', '#8297A8'), 0, $pieN);
          $pieHoverBg = array_slice(array('#3e5367', '#95a5a6', '#f5fbfc', '#459eda', '#2dc6a8'), 0, $pieN);
          $pieHoverW = array_fill(0, $pieN, 5);
          $pieTipQty = json_encode(label('TopProductsTooltipQty'));
          $pieTipQtyPct = json_encode(label('TopProductsTooltipQtyPct'));
          $pieTipRevPct = json_encode(label('TopProductsTooltipRevPct'));
      ?>
      var pieLabels = <?= json_encode($pieLabels, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE); ?>;
      var pieQty = <?= json_encode($pieQty); ?>;
      var piePctQty = <?= json_encode($piePctQty); ?>;
      var piePctRev = <?= json_encode($piePctRev); ?>;
      var pieTipQty = <?= $pieTipQty; ?>;
      var pieTipQtyPct = <?= $pieTipQtyPct; ?>;
      var pieTipRevPct = <?= $pieTipRevPct; ?>;

      var pieData = {
          labels: pieLabels,
          datasets: [{
               data: pieQty,
               backgroundColor: <?= json_encode($pieBg); ?>,
               hoverBackgroundColor: <?= json_encode($pieHoverBg); ?>,
               hoverBorderWidth: <?= json_encode($pieHoverW); ?>
          }]
      };

      Chart.defaults.global.legend.display = false;

      var elPie = document.getElementById("chart-area2");
      if (elPie) {
         var ctx2 = elPie.getContext("2d");
         window.myPie = new Chart(ctx2, {
             type: 'doughnut',
             data: pieData,
             options: {
                tooltips: {
                   callbacks: {
                      label: function (tooltipItem, data) {
                         var i = tooltipItem.index;
                         var qty = data.datasets[0].data[i];
                         return data.labels[i] + ': ' + qty + ' ' + pieTipQty + '\n' + pieTipQtyPct + ': ' + piePctQty[i] + '%\n' + pieTipRevPct + ': ' + piePctRev[i] + '%';
                      }
                   }
                }
             }
         });
      }
      <?php } ?>

       $('.count').each(function (index) {
       var size = $(this).text().split(".")[1] ? $(this).text().split(".")[1].length : 0;
 	    $(this).prop('count',0).animate({
 	        Counter: $(this).text()
 	    }, {
 	        duration: 2000,
 	        easing: 'swing',
 	        step: function (now) {
 	            $(this).text(parseFloat(now).toFixed(size));
 	        }
 	    });
     	});
	}


   /********************************** Get repports functions ************************************/

   function bindReportExport(reportType, params) {
      $('#stats').data('reportType', reportType);
      $('#stats').data('reportParams', params);
      $('#btnExportPdf, #btnExportExcel').prop('disabled', false);
   }

   function submitReportExport(format) {
      var type = $('#stats').data('reportType');
      var p = $('#stats').data('reportParams');
      if (!type || !p) { return; }
      var form = $('<form>', { method: 'POST', action: '<?php echo site_url('reports/export'); ?>', target: '_blank' });
      form.append($('<input>', { type: 'hidden', name: 'report_type', value: type }));
      form.append($('<input>', { type: 'hidden', name: 'format', value: format }));
      $.each(p, function (k, v) {
         form.append($('<input>', { type: 'hidden', name: k, value: v }));
      });
      $('body').append(form);
      form.submit();
      form.remove();
   }

   $('#stats').on('hidden.bs.modal', function () {
      $('#btnExportPdf, #btnExportExcel').prop('disabled', true);
      $('#stats').removeData('reportType').removeData('reportParams');
   });

   $(document).on('click', '#btnExportPdf', function () { submitReportExport('pdf'); });
   $(document).on('click', '#btnExportExcel', function () { submitReportExport('excel'); });

   function getCustomerReport()
   {
      var client_id = $('#customerSelect').find('option:selected').val();
      var Range = $('#CustomerRange').val();
      var start = Range.slice(6,10)+'-'+Range.slice(0,2)+'-'+Range.slice(3,5);
      var end = Range.slice(19,23)+'-'+Range.slice(13,15)+'-'+Range.slice(16,18);
           // ajax delete data to database
           $.ajax({
               url : "<?php echo site_url('reports/getCustomerReport')?>/",
               type: "POST",
               data: {client_id: client_id, start: start, end: end},
               success: function(data)
               {
                  $('#statsSection').html(data);
                  bindReportExport('customer', { client_id: client_id, start: start, end: end });
                  $('#stats').modal('show');
                  if ($('#Table').length) {
                  var table = $('#Table').DataTable( {
                      dom: 'T<"clear">lfrtip',
                      tableTools: {
                          'bProcessing'    : true
                       }
                    });
                  }
               },
               error: function (jqXHR, textStatus, errorThrown)
               {
                  alert("error");
               }
           });

   }

   function getCategoryReport()
   {
      var category_id = $('#categorySelect').find('option:selected').val();
      var Range = $('#CategoryRange').val();
      var start = Range.slice(6,10)+'-'+Range.slice(0,2)+'-'+Range.slice(3,5);
      var end = Range.slice(19,23)+'-'+Range.slice(13,15)+'-'+Range.slice(16,18);
      $.ajax({
         url : "<?php echo site_url('reports/getCategoryReport')?>/",
         type: "POST",
         data: {category_id: category_id, start: start, end: end},
         success: function(data)
         {
            $('#statsSection').html(data);
            bindReportExport('category', { category_id: category_id, start: start, end: end });
            $('#stats').modal('show');
            if ($('#Table').length) {
               var table = $('#Table').DataTable( {
                   dom: 'T<"clear">lfrtip',
                   tableTools: {
                       'bProcessing'    : true
                    }
                 });
            }
         },
         error: function (jqXHR, textStatus, errorThrown)
         {
            alert("error");
         }
      });
   }

   function getProductReport()
   {
      var product_id = $('#productSelect').find('option:selected').val();
      var Range = $('#ProductRange').val();
      var start = Range.slice(6,10)+'-'+Range.slice(0,2)+'-'+Range.slice(3,5);
      var end = Range.slice(19,23)+'-'+Range.slice(13,15)+'-'+Range.slice(16,18);
           // ajax set data to database
        $.ajax({
            url : "<?php echo site_url('reports/getProductReport')?>/",
            type: "POST",
            data: {product_id: product_id, start: start, end: end},
            success: function(data)
            {
               $('#statsSection').html(data);
               bindReportExport('product', { product_id: product_id, start: start, end: end });
               $('#stats').modal('show');
               if ($('#Table').length) {
               var table = $('#Table').DataTable( {
                   dom: 'T<"clear">lfrtip',
                   tableTools: {
                       'bProcessing'    : true
                    }
                 });
               }
            },
            error: function (jqXHR, textStatus, errorThrown)
            {
               alert("error");
            }
       });
   }

   function getRegisterReport()
   {
      var store_id = $('#StoresSelect').find('option:selected').val();
      var Range = $('#RegisterRange').val();
      var start = Range.slice(6,10)+'-'+Range.slice(0,2)+'-'+Range.slice(3,5);
      var end = Range.slice(19,23)+'-'+Range.slice(13,15)+'-'+Range.slice(16,18);
           // ajax set data to database
        $.ajax({
            url : "<?php echo site_url('reports/getRegisterReport')?>/",
            type: "POST",
            data: {store_id: store_id, start: start, end: end},
            success: function(data)
            {
               $('#statsSection').html(data);
               bindReportExport('register', { store_id: store_id, start: start, end: end });
               $('#stats').modal('show');
               if ($('#Table').length) {
               var table = $('#Table').DataTable( {
                   dom: 'T<"clear">lfrtip',
                   tableTools: {
                       'bProcessing'    : true
                    }
                 });
               }
            },
            error: function (jqXHR, textStatus, errorThrown)
            {
               alert("error");
            }
       });
   }

   function getStockReport()
   {
      var stock_id = $('#StockSelect').find('option:selected').val();
           // ajax set data to database
        $.ajax({
            url : "<?php echo site_url('reports/getStockReport')?>/",
            type: "POST",
            data: {stock_id: stock_id},
            success: function(data)
            {
               $('#statsSection').html(data);
               bindReportExport('stock', { stock_id: stock_id });
               $('#stats').modal('show');
               if ($('#Table').length) {
               var table = $('#Table').DataTable( {
                   dom: 'T<"clear">lfrtip',
                   tableTools: {
                       'bProcessing'    : true
                    }
                 });
               }
            },
            error: function (jqXHR, textStatus, errorThrown)
            {
               alert("error");
            }
       });
   }

   function getyearstats(direction) {
      var currentyear = parseInt($('.statYear').text());
      var year = direction === 'next' ? currentyear-1 : currentyear+1;

        $.ajax({
            url : "<?php echo site_url('reports/getyearstats')?>/"+year,
            type: "POST",
            success: function(data)
            {
               $('#statyears').html(data);
               $('.statYear').text(year);
               $('[data-toggle="tooltip"]').tooltip();
            },
            error: function (jqXHR, textStatus, errorThrown)
            {
               alert("error");
            }
      });
   }

   function RegisterDetails(id) {
      $('#RegisterDetail').data('register-id', id);
      $.ajax({
         url : "<?php echo site_url('reports/RegisterDetails')?>/"+id,
         type: "POST",
         success: function(data)
         {
            $('#RegisterDetails').html(data);
            $('#stats').modal('hide');
            $('#RegisterDetail').modal('show');
         },
         error: function (jqXHR, textStatus, errorThrown)
         {
             alert("error");
         }
      });
   }

   function CloseRegisterDetails(){
      $('#RegisterDetail').modal('hide');
      $('#stats').modal('show');
   }

   function RegisterDetailsPdf() {
      var id = $('#RegisterDetail').data('register-id');
      if (!id) {
         return;
      }
      window.open("<?php echo site_url('reports/RegisterDetailsPdf'); ?>/" + id, '_blank');
   }

   function delete_register(id){
      swal({   title: '<?=label("Areyousure");?>',
      text: '<?=label("Deletemessage");?>',
      type: "warning",
      showCancelButton: true,
      confirmButtonColor: "#DD6B55",
      confirmButtonText: '<?=label("yesiam");?>',
      closeOnConfirm: false },
      function(){
         $.ajax({
             url : "<?php echo site_url('reports/delete_register')?>/"+id,
             type: "POST",
             error: function (jqXHR, textStatus, errorThrown)
             {
                alert("error");
             }
        });
      $('#stats').modal('hide');
      swal('<?=label("Deleted");?>', '<?=label("Deletedmessage");?>', "success"); });
   }


	</script>

   <!-- Modal stats -->
   <div class="modal fade" id="stats" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
    <div class="modal-dialog modal-lg" role="document" id="statsModal">
       <div class="modal-content">
         <div class="modal-header">
           <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
           <h4 class="modal-title" id="stats"><?=label("Stats");?></h4>
         </div>
         <div class="modal-body" id="modal-body">
            <div id="statsSection">
               <!-- stats goes here -->
            </div>
         </div>
         <div class="modal-footer">
           <button type="button" class="btn btn-danger" id="btnExportPdf" disabled="disabled"><?=label('ExportPDF');?></button>
           <button type="button" class="btn btn-success" id="btnExportExcel" disabled="disabled"><?=label('ExportExcel');?></button>
           <button type="button" class="btn btn-default hiddenpr" data-dismiss="modal"><?=label("Close");?></button>
         </div>
       </div>
    </div>
   </div>
   <!-- /.Modal -->

   <!-- Modal register -->
   <div class="modal fade" id="RegisterDetail" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
    <div class="modal-dialog modal-lg" role="document">
       <div class="modal-content">
         <div class="modal-header">
           <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
           <h4 class="modal-title" id="myModalLabel"><?=label("RegisterDetails");?></h4>
         </div>
         <div class="modal-body">
            <div id="RegisterDetails">
               <!-- close register detail goes here -->
            </div>
         </div>
         <div class="modal-footer">
           <div class="row">
             <div class="col-md-6" style="margin-bottom:8px;">
               <a href="javascript:void(0)" onclick="RegisterDetailsPdf()" class="btn btn-danger col-md-12 flat-box-btn"><?=label('ExportPDF');?></a>
             </div>
             <div class="col-md-6">
               <a href="javascript:void(0)" onclick="CloseRegisterDetails()" class="btn btn-orange col-md-12 flat-box-btn"><?=label("Return");?></a>
             </div>
           </div>
         </div>
       </div>
    </div>
   </div>
   <!-- /.Modal -->

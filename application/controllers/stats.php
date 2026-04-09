<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Stats extends MY_Controller
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
        date_default_timezone_set($this->setting->timezone);
        $date = date("Y-m-d");
        $year = date("Y");
        $TodaySales = Sale::find_by_sql(
            "SELECT SUM(total) AS sum FROM zarest_sales WHERE DATE(created_at) = ?",
            array($date)
        );
        $pmTodayRows = Sale::find_by_sql(
            "SELECT SUBSTRING_INDEX(TRIM(IFNULL(paidmethod, '')), '~', 1) AS pm_key, SUM(total) AS sum "
            . "FROM zarest_sales WHERE DATE(created_at) = ? "
            . "GROUP BY pm_key HAVING pm_key <> ''",
            array($date)
        );
        $sumsByPmKey = array();
        foreach ($pmTodayRows as $row) {
            $sumsByPmKey[(string) $row->pm_key] = (float) $row->sum;
        }
        $todaySalesByPaymentMethod = array();
        foreach ($this->fetch_payment_methods() as $pm) {
            $key = $pm->stored_key();
            if (! isset($sumsByPmKey[$key])) {
                continue;
            }
            $total = $sumsByPmKey[$key];
            if ($total <= 0) {
                continue;
            }
            $todaySalesByPaymentMethod[] = array(
                'name' => $pm->name,
                'type_code' => $pm->type_code,
                'total' => number_format($total, $this->setting->decimals, '.', ''),
            );
        }
        $grandTotals = Sale_item::find_by_sql(
            'SELECT COALESCE(SUM(qt), 0) AS grand_qt, COALESCE(SUM(qt * price), 0) AS grand_rev FROM zarest_sale_items'
        );
        $grandQt = isset($grandTotals[0]) ? (float) $grandTotals[0]->grand_qt : 0;
        $grandRev = isset($grandTotals[0]) ? (float) $grandTotals[0]->grand_rev : 0;
        $top5rows = Sale_item::find_by_sql(
            'SELECT product_id, MAX(name) AS name, SUM(qt) AS totalquantity, SUM(qt * price) AS total_revenue '
            . 'FROM zarest_sale_items GROUP BY product_id ORDER BY SUM(qt) DESC LIMIT 5'
        );
        // No asignar propiedades extra al Model: __set() lanza UndefinedPropertyException.
        $Top5product = array();
        foreach ($top5rows as $p) {
            $tq = (float) $p->totalquantity;
            $tr = (float) $p->total_revenue;
            $row = new \stdClass();
            $row->product_id = $p->product_id;
            $row->name = $p->name;
            $row->totalquantity = $p->totalquantity;
            $row->total_revenue = $p->total_revenue;
            $row->pct_quantity = $grandQt > 0 ? round(100 * $tq / $grandQt, 1) : 0;
            $row->pct_revenue = $grandRev > 0 ? round(100 * $tr / $grandRev, 1) : 0;
            $Top5product[] = $row;
        }
        $catRows = Sale_item::find_by_sql(
            'SELECT COALESCE(c.name, NULLIF(TRIM(p.category), \'\'), \'__UNCAT__\') AS category_name, '
            . 'SUM(si.qt) AS totalquantity, SUM(si.qt * si.price) AS total_revenue '
            . 'FROM zarest_sale_items si '
            . 'LEFT JOIN zarest_products p ON p.id = si.product_id '
            . 'LEFT JOIN zarest_categories c ON c.name = p.category '
            . 'GROUP BY COALESCE(c.name, NULLIF(TRIM(p.category), \'\'), \'__UNCAT__\') '
            . 'ORDER BY SUM(si.qt) DESC LIMIT 5'
        );
        $uncatLabel = $this->lang->line('TopCategoriesUncategorized');
        if ($uncatLabel === false || $uncatLabel === '') {
            $uncatLabel = 'Sin categoría';
        }
        $Top5categories = array();
        foreach ($catRows as $c) {
            $tq = (float) $c->totalquantity;
            $tr = (float) $c->total_revenue;
            $row = new \stdClass();
            $row->name = ($c->category_name === '__UNCAT__') ? $uncatLabel : $c->category_name;
            $row->totalquantity = $c->totalquantity;
            $row->total_revenue = $c->total_revenue;
            $row->pct_quantity = $grandQt > 0 ? round(100 * $tq / $grandQt, 1) : 0;
            $row->pct_revenue = $grandRev > 0 ? round(100 * $tr / $grandRev, 1) : 0;
            $Top5categories[] = $row;
        }
        $monthlySales = Sale::find_by_sql("SELECT SUM(IF(MONTH = 1, numRecords, 0)) AS 'january',SUM(IF(MONTH = 1, totaltax, 0)) AS 'januarytax',SUM(IF(MONTH = 1, totaldiscount, 0)) AS 'januarydisc',SUM(IF(MONTH = 2, numRecords, 0)) AS 'feburary',SUM(IF(MONTH = 2, totaltax, 0)) AS 'feburarytax',SUM(IF(MONTH = 2, totaldiscount, 0)) AS 'feburarydisc',SUM(IF(MONTH = 3, numRecords, 0)) AS 'march',SUM(IF(MONTH = 3, totaltax, 0)) AS 'marchtax',SUM(IF(MONTH = 3, totaldiscount, 0)) AS 'marchdisc',SUM(IF(MONTH = 4, numRecords, 0)) AS 'april',SUM(IF(MONTH = 4, totaltax, 0)) AS 'apriltax',SUM(IF(MONTH = 4, totaldiscount, 0)) AS 'aprildisc',SUM(IF(MONTH = 5, numRecords, 0)) AS 'may',SUM(IF(MONTH = 5, totaltax, 0)) AS 'maytax',SUM(IF(MONTH = 5, totaldiscount, 0)) AS 'maydisc',SUM(IF(MONTH = 6, numRecords, 0)) AS 'june',SUM(IF(MONTH = 6, totaltax, 0)) AS 'junetax',SUM(IF(MONTH = 6, totaldiscount, 0)) AS 'junedisc',SUM(IF(MONTH = 7, numRecords, 0)) AS 'july',SUM(IF(MONTH = 7, totaltax, 0)) AS 'julytax',SUM(IF(MONTH = 7, totaldiscount, 0)) AS 'julydisc',SUM(IF(MONTH = 8, numRecords, 0)) AS 'august',SUM(IF(MONTH = 8, totaltax, 0)) AS 'augusttax',SUM(IF(MONTH = 8, totaldiscount, 0)) AS 'augustdisc',SUM(IF(MONTH = 9, numRecords, 0)) AS 'september',SUM(IF(MONTH = 9, totaltax, 0)) AS 'septembertax',SUM(IF(MONTH = 9, totaldiscount, 0)) AS 'septemberdisc',SUM(IF(MONTH = 10, numRecords, 0)) AS 'october',SUM(IF(MONTH = 10, totaltax, 0)) AS 'octobertax',SUM(IF(MONTH = 10, totaldiscount, 0)) AS 'octoberdisc',SUM(IF(MONTH = 11, numRecords, 0)) AS 'november',SUM(IF(MONTH = 11, totaltax, 0)) AS 'novembertax',SUM(IF(MONTH = 11, totaldiscount, 0)) AS 'novemberdisc',SUM(IF(MONTH = 12, numRecords, 0)) AS 'december',SUM(IF(MONTH = 12, totaltax, 0)) AS 'decembertax',SUM(IF(MONTH = 12, totaldiscount, 0)) AS 'decemberdisc',SUM(numRecords) AS total, SUM(totaltax) AS totalstax, SUM(totaldiscount) AS totaldisc FROM ( SELECT id, MONTH(created_at) AS MONTH, ROUND(sum(total)) AS numRecords, ROUND(sum(taxamount)) AS totaltax, ROUND(sum(discountamount)) AS totaldiscount FROM zarest_sales WHERE DATE_FORMAT(created_at, '%Y') = $year GROUP BY id, MONTH ) AS SubTable1");
        $monthlyExp = Expence::find_by_sql("SELECT SUM(IF(MONTH = 1, numRecords, 0)) AS 'january', SUM(IF(MONTH = 2, numRecords, 0)) AS 'feburary', SUM(IF(MONTH = 3, numRecords, 0)) AS 'march', SUM(IF(MONTH = 4, numRecords, 0)) AS 'april', SUM(IF(MONTH = 5, numRecords, 0)) AS 'may', SUM(IF(MONTH = 6, numRecords, 0)) AS 'june', SUM(IF(MONTH = 7, numRecords, 0)) AS 'july', SUM(IF(MONTH = 8, numRecords, 0)) AS 'august', SUM(IF(MONTH = 9, numRecords, 0)) AS 'september', SUM(IF(MONTH = 10, numRecords, 0)) AS 'october', SUM(IF(MONTH = 11, numRecords, 0)) AS 'november', SUM(IF(MONTH = 12, numRecords, 0)) AS 'december', SUM(numRecords) AS total FROM ( SELECT id, MONTH(date) AS MONTH, ROUND(sum(amount)) AS numRecords FROM zarest_expences WHERE DATE_FORMAT(date, '%Y') = $year GROUP BY id, MONTH ) AS SubTable1");
        $this->view_data['customers'] = Customer::all();
        $this->view_data['Categories'] = Category::all();
        $this->view_data['Products'] = Product::all();
        $this->view_data['Stores'] = Store::all();
        $this->view_data['Warehouses'] = Warehouse::all();
        $this->view_data['monthly'] = $monthlySales;
        $this->view_data['monthlyExp'] = $monthlyExp;
        $this->view_data['year'] = $year;
        $this->view_data['Top5product'] = $Top5product;
        $this->view_data['Top5categories'] = $Top5categories;
        $this->view_data['TodaySales'] = number_format((float) $TodaySales[0]->sum, $this->setting->decimals, '.', '');
        $this->view_data['todaySalesByPaymentMethod'] = $todaySalesByPaymentMethod;
        $this->view_data['CustomerNumber'] = Customer::count();
        ;
        $this->view_data['CategoriesNumber'] = Category::count();
        ;
        $this->view_data['ProductNumber'] = Product::count();
        ;
        $this->content_view = 'stats';
    }
}

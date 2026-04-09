<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Reports extends CI_Controller
{

    public function __construct()
    {
        parent::__construct();

        $lang = $this->session->userdata("lang") == null ? "english" : $this->session->userdata("lang");
        $this->lang->load($lang, $lang);
        $this->user = $this->session->userdata('user_id') ? User::find_by_id($this->session->userdata('user_id')) : FALSE;

        $this->setting = Setting::find(1);
    }

    public function getCustomerReport()
    {
        $client_id = $this->input->post('client_id');
        $start = $this->input->post('start');
        $end = $this->input->post('end');
        $b = $this->_bundle_customer_report($client_id, $start, $end);
        if ($b === null) {
            echo '<p class="text-muted">' . label('EmptyList') . '</p>';
            return;
        }
        echo $this->_html_report_table($b['headers'], $b['rows'], false) . $b['footer_html'];
    }

    public function getProductReport()
    {
        $product_id = $this->input->post('product_id');
        $start = $this->input->post('start');
        $end = $this->input->post('end');
        $b = $this->_bundle_product_report($product_id, $start, $end);
        if ($b === null) {
            echo '<p class="text-muted">' . label('EmptyList') . '</p>';
            return;
        }
        echo $this->_html_report_table($b['headers'], $b['rows'], false) . $b['footer_html'];
    }

    public function getCategoryReport()
    {
        $category_id = (int) $this->input->post('category_id');
        $start = $this->input->post('start');
        $end = $this->input->post('end');
        $b = $this->_bundle_category_report($category_id, $start, $end);
        if ($b === null) {
            echo '<p class="text-muted">' . label('EmptyList') . '</p>';
            return;
        }
        echo $this->_html_report_table($b['headers'], $b['rows'], false) . $b['footer_html'];
    }

    public function getRegisterReport()
    {
        $store_id = $this->input->post('store_id');
        $start = $this->input->post('start');
        $end = $this->input->post('end');
        $b = $this->_bundle_register_report($store_id, $start, $end, true);
        if ($b === null) {
            echo '<p class="text-muted">' . label('EmptyList') . '</p>';
            return;
        }
        echo $this->_html_report_table($b['headers'], $b['rows'], true) . $b['footer_html'];
    }

    /**
     * Exportar reporte actual a PDF o Excel (CSV) con los mismos filtros.
     * POST: report_type (customer|product|category|register|stock), format (pdf|excel), + parámetros del reporte.
     */
    public function export()
    {
        if (! $this->user) {
            show_error('Unauthorized', 403);
            return;
        }
        require_once APPPATH . 'libraries/Report_exporter.php';
        $type = (string) $this->input->post('report_type');
        $format = strtolower((string) $this->input->post('format'));
        if (! in_array($format, array('pdf', 'excel'), true)) {
            show_error('Invalid format', 400);
            return;
        }
        $post = $this->input->post();
        $bundle = null;
        switch ($type) {
            case 'customer':
                $bundle = $this->_bundle_customer_report($post['client_id'], $post['start'], $post['end']);
                break;
            case 'product':
                $bundle = $this->_bundle_product_report($post['product_id'], $post['start'], $post['end']);
                break;
            case 'category':
                $bundle = $this->_bundle_category_report((int) $post['category_id'], $post['start'], $post['end']);
                break;
            case 'register':
                $bundle = $this->_bundle_register_report($post['store_id'], $post['start'], $post['end'], false);
                break;
            case 'stock':
                $bundle = $this->_bundle_stock_report(isset($post['stock_id']) ? $post['stock_id'] : '');
                break;
            default:
                show_error('Invalid report', 400);
                return;
        }
        if ($bundle === null) {
            show_error(label('EmptyList'), 400);
            return;
        }
        $expHeaders = isset($bundle['headers_export']) ? $bundle['headers_export'] : $bundle['headers'];
        $expRows = isset($bundle['rows_export']) ? $bundle['rows_export'] : $bundle['rows'];
        if ($format === 'pdf') {
            Report_exporter::send_pdf(
                $bundle['file_slug'],
                $bundle['title'],
                $bundle['subtitle'],
                $expHeaders,
                $expRows,
                $bundle['footer_plain']
            );
        } else {
            Report_exporter::send_excel_csv(
                $bundle['file_slug'],
                $expHeaders,
                $expRows,
                $bundle['footer_csv'],
                (string) $this->setting->currency
            );
        }
    }

    public function delete_register($id){

      $register = Register::find($id);
      $sales = Sale::find('all', array(
          'conditions' => array(
             'register_id = ?',
             $id
          )
      ));
      foreach ($sales as $sale) {
         Sale_item::delete_all(array(
             'conditions' => array(
                'sale_id = ?',
                $sale->id
             )
         ));
      }
      Sale::delete_all(array(
          'conditions' => array(
             'register_id = ?',
             $id
          )
      ));
      Payement::delete_all(array(
          'conditions' => array(
             'register_id = ?',
             $id
          )
      ));

      $register->delete();
   }

    public function getyearstats($year)
    {
        $monthly = Sale::find_by_sql("SELECT SUM(IF(MONTH = 1, numRecords, 0)) AS 'january',SUM(IF(MONTH = 1, totaltax, 0)) AS 'januarytax',SUM(IF(MONTH = 1, totaldiscount, 0)) AS 'januarydisc',SUM(IF(MONTH = 2, numRecords, 0)) AS 'feburary',SUM(IF(MONTH = 2, totaltax, 0)) AS 'feburarytax',SUM(IF(MONTH = 2, totaldiscount, 0)) AS 'feburarydisc',SUM(IF(MONTH = 3, numRecords, 0)) AS 'march',SUM(IF(MONTH = 3, totaltax, 0)) AS 'marchtax',SUM(IF(MONTH = 3, totaldiscount, 0)) AS 'marchdisc',SUM(IF(MONTH = 4, numRecords, 0)) AS 'april',SUM(IF(MONTH = 4, totaltax, 0)) AS 'apriltax',SUM(IF(MONTH = 4, totaldiscount, 0)) AS 'aprildisc',SUM(IF(MONTH = 5, numRecords, 0)) AS 'may',SUM(IF(MONTH = 5, totaltax, 0)) AS 'maytax',SUM(IF(MONTH = 5, totaldiscount, 0)) AS 'maydisc',SUM(IF(MONTH = 6, numRecords, 0)) AS 'june',SUM(IF(MONTH = 6, totaltax, 0)) AS 'junetax',SUM(IF(MONTH = 6, totaldiscount, 0)) AS 'junedisc',SUM(IF(MONTH = 7, numRecords, 0)) AS 'july',SUM(IF(MONTH = 7, totaltax, 0)) AS 'julytax',SUM(IF(MONTH = 7, totaldiscount, 0)) AS 'julydisc',SUM(IF(MONTH = 8, numRecords, 0)) AS 'august',SUM(IF(MONTH = 8, totaltax, 0)) AS 'augusttax',SUM(IF(MONTH = 8, totaldiscount, 0)) AS 'augustdisc',SUM(IF(MONTH = 9, numRecords, 0)) AS 'september',SUM(IF(MONTH = 9, totaltax, 0)) AS 'septembertax',SUM(IF(MONTH = 9, totaldiscount, 0)) AS 'septemberdisc',SUM(IF(MONTH = 10, numRecords, 0)) AS 'october',SUM(IF(MONTH = 10, totaltax, 0)) AS 'octobertax',SUM(IF(MONTH = 10, totaldiscount, 0)) AS 'octoberdisc',SUM(IF(MONTH = 11, numRecords, 0)) AS 'november',SUM(IF(MONTH = 11, totaltax, 0)) AS 'novembertax',SUM(IF(MONTH = 11, totaldiscount, 0)) AS 'novemberdisc',SUM(IF(MONTH = 12, numRecords, 0)) AS 'december',SUM(IF(MONTH = 12, totaltax, 0)) AS 'decembertax',SUM(IF(MONTH = 12, totaldiscount, 0)) AS 'decemberdisc',SUM(numRecords) AS total, SUM(totaltax) AS totalstax, SUM(totaldiscount) AS totaldisc FROM ( SELECT id, MONTH(created_at) AS MONTH, ROUND(sum(total)) AS numRecords, ROUND(sum(taxamount)) AS totaltax, ROUND(sum(discountamount)) AS totaldiscount FROM zarest_sales WHERE DATE_FORMAT(created_at, '%Y') = $year GROUP BY id, MONTH ) AS SubTable1");
        $monthlyExp = Expence::find_by_sql("SELECT SUM(IF(MONTH = 1, numRecords, 0)) AS 'january', SUM(IF(MONTH = 2, numRecords, 0)) AS 'feburary', SUM(IF(MONTH = 3, numRecords, 0)) AS 'march', SUM(IF(MONTH = 4, numRecords, 0)) AS 'april', SUM(IF(MONTH = 5, numRecords, 0)) AS 'may', SUM(IF(MONTH = 6, numRecords, 0)) AS 'june', SUM(IF(MONTH = 7, numRecords, 0)) AS 'july', SUM(IF(MONTH = 8, numRecords, 0)) AS 'august', SUM(IF(MONTH = 9, numRecords, 0)) AS 'september', SUM(IF(MONTH = 10, numRecords, 0)) AS 'october', SUM(IF(MONTH = 11, numRecords, 0)) AS 'november', SUM(IF(MONTH = 12, numRecords, 0)) AS 'december', SUM(numRecords) AS total FROM ( SELECT id, MONTH(date) AS MONTH, ROUND(sum(amount)) AS numRecords FROM zarest_expences WHERE DATE_FORMAT(date, '%Y') = $year GROUP BY id, MONTH ) AS SubTable1");
        $result = '<table class="StatTable"><tr><td><span class="revenuespan" data-toggle="tooltip" data-placement="top"  data-html="true" title="<h5>' . label('tax') . ' : <b>' . $monthly[0]->januarytax . ' ' . $this->setting->currency . '</b> <br><br> ' . label('Discount') . ' : <b>' . $monthly[0]->januarydisc . ' ' . $this->setting->currency . '</b></h5>">' . $monthly[0]->january . ' ' . $this->setting->currency . '</span><span class="expencespan">' . $monthlyExp[0]->january . ' ' . $this->setting->currency . '</span>' . label('January') . '</td><td><span class="revenuespan" data-toggle="tooltip" data-placement="top"  data-html="true" title="<h5>' . label('tax') . ' : <b>' . $monthly[0]->feburarytax . ' ' . $this->setting->currency . '</b> <br><br> ' . label('Discount') . ' : <b>' . $monthly[0]->feburarydisc . ' ' . $this->setting->currency . '</b></h5>">' . $monthly[0]->feburary . ' ' . $this->setting->currency . '</span><span class="expencespan">' . $monthlyExp[0]->feburary . ' ' . $this->setting->currency . '</span>' . label('February') . '</td><td><span class="revenuespan" data-toggle="tooltip" data-placement="top"  data-html="true" title="<h5>' . label('tax') . ' : <b>' . $monthly[0]->marchtax . ' ' . $this->setting->currency . '</b> <br><br> ' . label('Discount') . ' : <b>' . $monthly[0]->marchdisc . ' ' . $this->setting->currency . '</b></h5>">' . $monthly[0]->march . ' ' . $this->setting->currency . '</span><span class="expencespan">' . $monthlyExp[0]->march . ' ' . $this->setting->currency . '</span>' . label('March') . '</td><td><span class="revenuespan" data-toggle="tooltip" data-placement="top"  data-html="true" title="<h5>' . label('tax') . ' : <b>' . $monthly[0]->apriltax . ' ' . $this->setting->currency . '</b> <br><br> ' . label('Discount') . ' : <b>' . $monthly[0]->aprildisc . ' ' . $this->setting->currency . '</b></h5>">' . $monthly[0]->april . ' ' . $this->setting->currency . '</span><span class="expencespan">' . $monthlyExp[0]->april . ' ' . $this->setting->currency . '</span>' . label('April') . '</td></tr><tr><td><span class="revenuespan" data-toggle="tooltip" data-placement="top"  data-html="true" title="<h5>' . label('tax') . ' : <b>' . $monthly[0]->maytax . ' ' . $this->setting->currency . '</b> <br><br> ' . label('Discount') . ' : <b>' . $monthly[0]->maydisc . ' ' . $this->setting->currency . '</b></h5>">' . $monthly[0]->may . ' ' . $this->setting->currency . '</span><span class="expencespan">' . $monthlyExp[0]->may . ' ' . $this->setting->currency . '</span>' . label('May') . '</td><td><span class="revenuespan" data-toggle="tooltip" data-placement="top"  data-html="true" title="<h5>' . label('tax') . ' : <b>' . $monthly[0]->junetax . ' ' . $this->setting->currency . '</b> <br><br> ' . label('Discount') . ' : <b>' . $monthly[0]->junedisc . ' ' . $this->setting->currency . '</b></h5>">' . $monthly[0]->june . ' ' . $this->setting->currency . '</span><span class="expencespan">' . $monthlyExp[0]->june . ' ' . $this->setting->currency . '</span>' . label('June') . '</td><td><span class="revenuespan" data-toggle="tooltip" data-placement="top"  data-html="true" title="<h5>' . label('tax') . ' : <b>' . $monthly[0]->julytax . ' ' . $this->setting->currency . '</b> <br><br> ' . label('Discount') . ' : <b>' . $monthly[0]->julydisc . ' ' . $this->setting->currency . '</b></h5>">' . $monthly[0]->july . ' ' . $this->setting->currency . '</span><span class="expencespan">' . $monthlyExp[0]->july . ' ' . $this->setting->currency . '</span>' . label('July') . '</td><td><span class="revenuespan" data-toggle="tooltip" data-placement="top"  data-html="true" title="<h5>' . label('tax') . ' : <b>' . $monthly[0]->augusttax . ' ' . $this->setting->currency . '</b> <br><br> ' . label('Discount') . ' : <b>' . $monthly[0]->augustdisc . ' ' . $this->setting->currency . '</b></h5>">' . $monthly[0]->august . ' ' . $this->setting->currency . '</span><span class="expencespan">' . $monthlyExp[0]->august . ' ' . $this->setting->currency . '</span>' . label('August') . '</td></tr><tr><td><span class="revenuespan" data-toggle="tooltip" data-placement="top"  data-html="true" title="<h5>' . label('tax') . ' : <b>' . $monthly[0]->septembertax . ' ' . $this->setting->currency . '</b> <br><br> ' . label('Discount') . ' : <b>' . $monthly[0]->septemberdisc . ' ' . $this->setting->currency . '</b></h5>">' . $monthly[0]->september . ' ' . $this->setting->currency . '</span><span class="expencespan">' . $monthlyExp[0]->september . ' ' . $this->setting->currency . '</span>' . label('September') . '</td><td><span class="revenuespan" data-toggle="tooltip" data-placement="top"  data-html="true" title="<h5>' . label('tax') . ' : <b>' . $monthly[0]->octobertax . ' ' . $this->setting->currency . '</b> <br><br> ' . label('Discount') . ' : <b>' . $monthly[0]->octoberdisc . ' ' . $this->setting->currency . '</b></h5>">' . $monthly[0]->october . ' ' . $this->setting->currency . '</span><span class="expencespan">' . $monthlyExp[0]->october . ' ' . $this->setting->currency . '</span>' . label('October') . '</td><td><span class="revenuespan" data-toggle="tooltip" data-placement="top"  data-html="true" title="<h5>' . label('tax') . ' : <b>' . $monthly[0]->novembertax . ' ' . $this->setting->currency . '</b> <br><br> ' . label('Discount') . ' : <b>' . $monthly[0]->novemberdisc . ' ' . $this->setting->currency . '</b></h5>">' . $monthly[0]->november . ' ' . $this->setting->currency . '</span><span class="expencespan">' . $monthlyExp[0]->november . ' ' . $this->setting->currency . '</span>' . label('November') . '</td><td><span class="revenuespan" data-toggle="tooltip" data-placement="top"  data-html="true" title="<h5>' . label('tax') . ' : <b>' . $monthly[0]->decembertax . ' ' . $this->setting->currency . '</b> <br><br> ' . label('Discount') . ' : <b>' . $monthly[0]->decemberdisc . ' ' . $this->setting->currency . '</b></h5>">' . $monthly[0]->december . ' ' . $this->setting->currency . '</span><span class="expencespan">' . $monthlyExp[0]->december . ' ' . $this->setting->currency . '</span>' . label('December') . '</td></tr></table>';

        echo $result;
    }

    /**
     * ****************** register functions ***************
     */
    public function RegisterDetails($id)
    {
        $register = Register::find($id);
        if (! $register) {
            echo '<p class="text-muted">' . label('EmptyList') . '</p>';

            return;
        }
        echo $this->_html_register_details($register);
    }

    /**
     * PDF del detalle de un cierre de caja (mismo contenido que el modal).
     */
    public function RegisterDetailsPdf($id = null)
    {
        if (! $this->user) {
            show_error('Unauthorized', 403);

            return;
        }
        $id = (int) $id;
        $register = Register::find($id);
        if (! $register) {
            show_404();

            return;
        }
        require_once APPPATH . 'libraries/Report_exporter.php';
        Report_exporter::clear_output_buffers();
        require_once APPPATH . 'libraries/tcpdf/tcpdf.php';
        $title = label('RegisterDetails') . ' #' . $register->id;
        $pdf = new TCPDF('L', PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
        $pdf->SetCreator('Platea');
        $pdf->SetTitle($title);
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);
        $pdf->SetMargins(10, 12, 10);
        $pdf->SetAutoPageBreak(true, 14);
        $pdf->AddPage();
        $pdf->SetFont('dejavusans', '', 9);
        $html = $this->_html_register_details($register, true);
        $pdf->writeHTML($html, true, false, true, false, '');
        $fn = 'registro_caja_' . $register->id;
        $pdf->Output(preg_replace('/\W+/', '_', $fn) . '.pdf', 'I');
        exit;
    }

    /**
     * @param object $register
     * @param bool   $forPdf estilos compactos para TCPDF
     * @return string
     */
    private function _html_register_details($register, $forPdf = false)
    {
        $dec = (int) $this->setting->decimals;
        $cur = $this->setting->currency;
        $createdBy = '-';
        try {
            $user = User::find($register->user_id);
            if ($user) {
                $createdBy = $user->firstname . ' ' . $user->lastname;
            }
        } catch (\Exception $e) {
        }
        $closedBy = '-';
        try {
            $u2 = User::find($register->closed_by);
            if ($u2) {
                $closedBy = $u2->firstname . ' ' . $u2->lastname;
            }
        } catch (\Exception $e) {
        }
        $CashinHand = number_format((float) $register->cash_inhand, $dec, '.', '');
        $date = $register->date;
        $closedate = $register->closed_at ? (string) $register->closed_at : '-';

        $lines = $this->_register_close_lines_from_note($register->note);
        $tbl = '';
        $sumE = 0.0;
        $sumC = 0.0;
        if (is_array($lines) && count($lines) > 0) {
            foreach ($lines as $ln) {
                if (! is_array($ln)) {
                    continue;
                }
                $exp = $this->_parse_register_amount_for_report(isset($ln['expected']) ? $ln['expected'] : 0);
                $cnt = $this->_parse_register_amount_for_report(isset($ln['counted']) ? $ln['counted'] : 0);
                if (abs($exp) < 0.00001 && abs($cnt) < 0.00001) {
                    continue;
                }
                $sumE += $exp;
                $sumC += $cnt;
                $lbl = isset($ln['label']) ? htmlspecialchars((string) $ln['label'], ENT_QUOTES, 'UTF-8') : '?';
                $tbl .= '<tr><td>' . $lbl . '</td><td class="text-right">' . number_format($exp, $dec, '.', '') . '</td><td class="text-right">'
                    . number_format($cnt, $dec, '.', '') . '</td><td class="text-right">' . number_format($cnt - $exp, $dec, '.', '') . '</td></tr>';
            }
        } else {
            $pairs = array(
                array('lbl' => label('Cash'), 'e' => (float) $register->cash_total, 'c' => (float) $register->cash_sub),
                array('lbl' => label('CreditCard'), 'e' => (float) $register->cc_total, 'c' => (float) $register->cc_sub),
                array('lbl' => label('Cheque'), 'e' => (float) $register->cheque_total, 'c' => (float) $register->cheque_sub),
            );
            foreach ($pairs as $p) {
                if (abs($p['e']) < 0.00001 && abs($p['c']) < 0.00001) {
                    continue;
                }
                $sumE += $p['e'];
                $sumC += $p['c'];
                $tbl .= '<tr><td>' . htmlspecialchars($p['lbl'], ENT_QUOTES, 'UTF-8') . '</td><td class="text-right">'
                    . number_format($p['e'], $dec, '.', '') . '</td><td class="text-right">' . number_format($p['c'], $dec, '.', '')
                    . '</td><td class="text-right">' . number_format($p['c'] - $p['e'], $dec, '.', '') . '</td></tr>';
            }
        }
        if ($tbl === '') {
            $tbl = '<tr><td colspan="4" class="text-muted text-center">' . htmlspecialchars(label('CloseRegisterNoMethodTotals'), ENT_QUOTES, 'UTF-8') . '</td></tr>';
        }
        $tbl .= '<tr class="warning"><td><b>' . htmlspecialchars(label('Total'), ENT_QUOTES, 'UTF-8') . '</b></td><td class="text-right"><b>'
            . number_format($sumE, $dec, '.', '') . '</b></td><td class="text-right"><b>' . number_format($sumC, $dec, '.', '')
            . '</b></td><td class="text-right"><b>' . number_format($sumC - $sumE, $dec, '.', '') . '</b></td></tr>';

        $noteUser = $this->_note_without_close_json($register->note);
        $noteHtml = $noteUser !== '' ? '<p style="white-space:pre-wrap;">' . nl2br(htmlspecialchars($noteUser, ENT_QUOTES, 'UTF-8')) . '</p>' : '';

        $wrap = $forPdf ? 'font-size:9px;' : '';
        $img = $forPdf ? '' : '<div class="col-md-2"><img src="' . site_url() . '/assets/img/register.svg" alt=""></div>';

        return '<div class="register-detail-root" style="' . $wrap . '"><div class="row"><div class="col-md-3"><blockquote><footer>'
            . htmlspecialchars(label('Openedby'), ENT_QUOTES, 'UTF-8') . '</footer><p>' . htmlspecialchars($createdBy, ENT_QUOTES, 'UTF-8')
            . '</p></blockquote></div><div class="col-md-3"><blockquote><footer>' . htmlspecialchars(label('CashinHand'), ENT_QUOTES, 'UTF-8')
            . '</footer><p>' . htmlspecialchars($CashinHand . ' ' . $cur, ENT_QUOTES, 'UTF-8') . '</p></blockquote></div><div class="col-md-4"><blockquote><footer>'
            . htmlspecialchars(label('Openingtime'), ENT_QUOTES, 'UTF-8') . '</footer><p>' . htmlspecialchars($date->format('Y-m-d H:i:s'), ENT_QUOTES, 'UTF-8')
            . '</p></blockquote></div>' . $img . '</div><h2>' . htmlspecialchars(label('PaymentsSummary'), ENT_QUOTES, 'UTF-8') . ' (' . htmlspecialchars($cur, ENT_QUOTES, 'UTF-8')
            . ')</h2><table class="table table-striped" border="1" cellpadding="4" cellspacing="0" width="100%"><thead><tr><th width="28%">'
            . htmlspecialchars(label('PayementType'), ENT_QUOTES, 'UTF-8') . '</th><th width="24%" class="text-right">'
            . htmlspecialchars(label('EXPECTED'), ENT_QUOTES, 'UTF-8') . '</th><th width="24%" class="text-right">'
            . htmlspecialchars(label('COUNTED'), ENT_QUOTES, 'UTF-8') . '</th><th width="24%" class="text-right">'
            . htmlspecialchars(label('DIFFERENCES'), ENT_QUOTES, 'UTF-8') . '</th></tr></thead><tbody>' . $tbl . '</tbody></table><p>- '
            . htmlspecialchars(label('ClosedRegister') . ' ' . $closedBy . ' ' . label('at') . ' ' . $closedate, ENT_QUOTES, 'UTF-8')
            . '</p><div class="form-group"><h2>' . htmlspecialchars(label('note'), ENT_QUOTES, 'UTF-8') . '</h2>' . $noteHtml . '</div></div>';
    }

    /**
     * @return array|null
     */
    private function _register_close_lines_from_note($note)
    {
        if (! preg_match('/\[REGISTER_CLOSE_LINES_JSON\](.*?)\[\/REGISTER_CLOSE_LINES_JSON\]/s', (string) $note, $m)) {
            return null;
        }
        $j = json_decode($m[1], true);

        return is_array($j) ? $j : null;
    }

    private function _note_without_close_json($note)
    {
        return trim(preg_replace('/\s*\[REGISTER_CLOSE_LINES_JSON\].*?\[\/REGISTER_CLOSE_LINES_JSON\]\s*/s', '', (string) $note));
    }

    /**
     * @param mixed $raw
     */
    private function _parse_register_amount_for_report($raw)
    {
        if ($raw === null || $raw === '') {
            return 0.0;
        }
        if (is_numeric($raw)) {
            return (float) $raw;
        }
        $s = preg_replace('/[^\d,.\-]/', '', trim((string) $raw));
        $s = str_replace(',', '.', $s);

        return (float) $s;
    }

    /**
     * Etiqueta de medio =&gt; importe esperado (cierre), para columnas del listado de registros.
     *
     * @param object $register
     * @return array<string,float>
     */
    private function _register_close_breakdown_map($register)
    {
        $lines = $this->_register_close_lines_from_note($register->note);
        $map = array();
        if (is_array($lines) && count($lines) > 0) {
            foreach ($lines as $ln) {
                if (! is_array($ln)) {
                    continue;
                }
                $lbl = isset($ln['label']) ? trim((string) $ln['label']) : '';
                if ($lbl === '') {
                    $lbl = '?';
                }
                $exp = $this->_parse_register_amount_for_report(isset($ln['expected']) ? $ln['expected'] : 0);
                if (! isset($map[$lbl])) {
                    $map[$lbl] = 0.0;
                }
                $map[$lbl] += $exp;
            }

            return $map;
        }
        $c = (float) $register->cash_total;
        $cc = (float) $register->cc_total;
        $ch = (float) $register->cheque_total;
        if (abs($c) >= 0.00001) {
            $map[label('Cash')] = $c;
        }
        if (abs($cc) >= 0.00001) {
            $map[label('CreditCard')] = $cc;
        }
        if (abs($ch) >= 0.00001) {
            $map[label('Cheque')] = $ch;
        }

        return $map;
    }

    /**
     * Orden de columnas: efectivo, tarjeta, cheque, resto alfabético.
     *
     * @param array<string,bool> $labelsPresent
     * @return string[]
     */
    private function _register_close_labels_ordered(array $labelsPresent)
    {
        $cash = label('Cash');
        $cc = label('CreditCard');
        $ch = label('Cheque');
        $order = array();
        foreach (array($cash, $cc, $ch) as $p) {
            if (! empty($labelsPresent[$p])) {
                $order[] = $p;
            }
        }
        $rest = array_diff(array_keys($labelsPresent), $order);
        sort($rest, SORT_NATURAL | SORT_FLAG_CASE);

        return array_merge($order, $rest);
    }

    public function getStockReport()
    {
        $store_id = $this->input->post('stock_id');
        $b = $this->_bundle_stock_report($store_id);
        if ($b === null) {
            echo '<p class="text-muted">' . label('EmptyList') . '</p>';
            return;
        }
        $result = '<table id="Table" class="table table-striped table-bordered" cellspacing="0" width="100%"><thead><tr>';
        foreach ($b['headers'] as $h) {
            $result .= '<th>' . $h . '</th>';
        }
        $result .= '</tr></thead><tbody>';
        foreach ($b['rows_raw'] as $rr) {
            $result .= '<tr class="' . $rr['class'] . '"><td>' . $rr['name'] . '</td><td>' . $rr['qty'] . '</td></tr>';
        }
        $result .= '</tbody></table>';
        echo $result;
    }

    private function _report_dates_ok($start, $end)
    {
        return is_string($start) && is_string($end)
            && preg_match('/^\d{4}-\d{2}-\d{2}$/', $start)
            && preg_match('/^\d{4}-\d{2}-\d{2}$/', $end);
    }

    private function _html_report_table(array $headers, array $rows, $cells_contain_html = false)
    {
        $html = '<table id="Table" class="table table-striped table-bordered" cellspacing="0" width="100%"><thead><tr>';
        foreach ($headers as $h) {
            $html .= '<th>' . htmlspecialchars((string) $h, ENT_QUOTES, 'UTF-8') . '</th>';
        }
        $html .= '</tr></thead><tbody>';
        foreach ($rows as $r) {
            $html .= '<tr>';
            foreach ($r as $cell) {
                $html .= '<td>' . ($cells_contain_html ? $cell : htmlspecialchars((string) $cell, ENT_QUOTES, 'UTF-8')) . '</td>';
            }
            $html .= '</tr>';
        }
        $html .= '</tbody></table>';

        return $html;
    }

    private function _bundle_customer_report($client_id, $start, $end)
    {
        if (! $this->_report_dates_ok($start, $end)) {
            return null;
        }
        $sales = Sale::find_by_sql(
            'SELECT * FROM zarest_sales WHERE client_id = ? AND DATE(created_at) BETWEEN ? AND ? ORDER BY created_at',
            array((int) $client_id, $start, $end)
        );
        $headers = array(
            label('Number'),
            label('CustomerName'),
            label('Discount'),
            label('Total'),
            label('Createdby'),
            label('TotalItems'),
        );
        $rows_plain = array();
        $totals = 0;
        foreach ($sales as $sale) {
            $tot = number_format((float) $sale->total, $this->setting->decimals, '.', '') . ' ' . $this->setting->currency;
            $rows_plain[] = array(
                (string) $sale->id,
                (string) $sale->clientname,
                (string) $sale->discount,
                $tot,
                (string) $sale->created_by,
                (string) $sale->totalitems,
            );
            $totals += (float) $sale->total;
        }
        $totals_fmt = number_format((float) $totals, $this->setting->decimals, '.', '') . ' ' . $this->setting->currency;
        $footer_html = '<h1>' . label('Total') . ' : <span class="ReportTotal">' . $totals_fmt . '</span></h1>';
        $footer_plain = label('Total') . ': ' . $totals_fmt;
        $footer_csv = array(label('Total'), '', '', '', '', $totals_fmt);

        return array(
            'headers' => $headers,
            'rows' => $rows_plain,
            'footer_html' => $footer_html,
            'footer_plain' => $footer_plain,
            'footer_csv' => $footer_csv,
            'title' => label('ClientsStats'),
            'subtitle' => label('SelectRange') . ': ' . $start . ' — ' . $end,
            'file_slug' => 'reporte_clientes_' . $start . '_' . $end,
        );
    }

    private function _bundle_product_report($product_id, $start, $end)
    {
        if (! $this->_report_dates_ok($start, $end)) {
            return null;
        }
        $prduct = Product::find($product_id);
        if (! $prduct) {
            return null;
        }
        $prducts = Sale_item::find_by_sql(
            'SELECT * FROM zarest_sale_items WHERE product_id = ? AND date BETWEEN ? AND ? ORDER BY date',
            array((int) $product_id, $start, $end)
        );
        $headers = array(
            label('SaleNum'),
            label('ProductName'),
            label('Cost'),
            label('Price'),
            label('Quantity'),
            label('tax'),
            label('Total'),
            label('Profit'),
        );
        $rows_plain = array();
        $totalprofit = 0;
        foreach ($prducts as $prd) {
            $tax = $prd->subtotal * intval(substr($prduct->tax, 0, - 1)) / 100;
            $profit = $prd->subtotal - $tax - ($prduct->cost * $prd->qt);
            $totalprofit += $profit;
            $price = number_format((float) $prd->price, $this->setting->decimals, '.', '') . ' ' . $this->setting->currency;
            $prof = number_format((float) $profit, $this->setting->decimals, '.', '') . ' ' . $this->setting->currency;
            $taxcell = $prduct->tax . '(' . $tax . ' ' . $this->setting->currency . ')';
            $rowp = array(
                (string) $prd->id,
                (string) $prd->name,
                (string) $prduct->cost . ' ' . $this->setting->currency,
                $price,
                (string) $prd->qt,
                $taxcell,
                (string) $prd->subtotal . ' ' . $this->setting->currency,
                $prof,
            );
            $rows_plain[] = $rowp;
        }
        $tpf = number_format((float) $totalprofit, $this->setting->decimals, '.', '') . ' ' . $this->setting->currency;
        $footer_html = '<h1>' . label('TotalProfit') . ' : <span class="ReportTotal">' . $tpf . '</span></h1>';
        $footer_plain = label('TotalProfit') . ': ' . $tpf;
        $footer_csv = array_merge(array(label('TotalProfit')), array_fill(0, 6, ''), array($tpf));

        return array(
            'headers' => $headers,
            'rows' => $rows_plain,
            'footer_html' => $footer_html,
            'footer_plain' => $footer_plain,
            'footer_csv' => $footer_csv,
            'title' => label('ProductsStats') . ' — ' . $prduct->name,
            'subtitle' => label('SelectRange') . ': ' . $start . ' — ' . $end,
            'file_slug' => 'reporte_producto_' . $start . '_' . $end,
        );
    }

    private function _bundle_category_report($category_id, $start, $end)
    {
        if (! $this->_report_dates_ok($start, $end)) {
            return null;
        }
        if ($category_id < 0) {
            return null;
        }
        $filterSingleCategory = ($category_id > 0);
        if ($filterSingleCategory) {
            $category = Category::find($category_id);
            if (! $category) {
                return null;
            }
            $reportCatLabel = $category->name;
            $prducts = Sale_item::find_by_sql(
                'SELECT si.* FROM zarest_sale_items si '
                . 'INNER JOIN zarest_products p ON p.id = si.product_id '
                . 'WHERE p.category = ? AND si.date BETWEEN ? AND ? ORDER BY si.date, si.id',
                array($reportCatLabel, $start, $end)
            );
        } else {
            $reportCatLabel = label('AllCategories');
            $prducts = Sale_item::find_by_sql(
                'SELECT si.* FROM zarest_sale_items si '
                . 'INNER JOIN zarest_products p ON p.id = si.product_id '
                . 'WHERE si.date BETWEEN ? AND ? ORDER BY si.date, si.id',
                array($start, $end)
            );
        }
        $headers = array(
            label('SaleNum'),
            label('ProductName'),
            label('Category'),
            label('Cost'),
            label('Price'),
            label('Quantity'),
            label('tax'),
            label('Total'),
            label('Profit'),
        );
        $rows_plain = array();
        $totalprofit = 0;
        foreach ($prducts as $prd) {
            $prduct = Product::find($prd->product_id);
            if (! $prduct) {
                continue;
            }
            $rowCategory = $filterSingleCategory ? $reportCatLabel : (string) $prduct->category;
            $tax = $prd->subtotal * intval(substr($prduct->tax, 0, - 1)) / 100;
            $profit = $prd->subtotal - $tax - ($prduct->cost * $prd->qt);
            $totalprofit += $profit;
            $price = number_format((float) $prd->price, $this->setting->decimals, '.', '') . ' ' . $this->setting->currency;
            $prof = number_format((float) $profit, $this->setting->decimals, '.', '') . ' ' . $this->setting->currency;
            $taxcell = $prduct->tax . '(' . $tax . ' ' . $this->setting->currency . ')';
            $rows_plain[] = array(
                (string) $prd->id,
                (string) $prd->name,
                (string) $rowCategory,
                (string) $prduct->cost . ' ' . $this->setting->currency,
                $price,
                (string) $prd->qt,
                $taxcell,
                (string) $prd->subtotal . ' ' . $this->setting->currency,
                $prof,
            );
        }
        $tpf = number_format((float) $totalprofit, $this->setting->decimals, '.', '') . ' ' . $this->setting->currency;
        $footer_html = '<h1>' . label('Category') . ': <span class="ReportTotal">' . htmlspecialchars($reportCatLabel, ENT_QUOTES, 'UTF-8')
            . '</span> — ' . label('TotalProfit') . ' : <span class="ReportTotal">' . $tpf . '</span></h1>';
        $footer_plain = label('Category') . ': ' . $reportCatLabel . ' — ' . label('TotalProfit') . ': ' . $tpf;
        $footer_csv = array(label('TotalProfit'), '', '', '', '', '', '', '', $tpf);

        return array(
            'headers' => $headers,
            'rows' => $rows_plain,
            'footer_html' => $footer_html,
            'footer_plain' => $footer_plain,
            'footer_csv' => $footer_csv,
            'title' => label('CategoriesStats') . ' — ' . $reportCatLabel,
            'subtitle' => label('SelectRange') . ': ' . $start . ' — ' . $end,
            'file_slug' => 'reporte_categoria_' . $start . '_' . $end,
        );
    }

    private function _bundle_register_report($store_id, $start, $end, $include_action_column)
    {
        if (! $this->_report_dates_ok($start, $end)) {
            return null;
        }
        $register = Register::find_by_sql(
            'SELECT * FROM zarest_registers WHERE store_id = ? AND date BETWEEN ? AND ? ORDER BY date',
            array((int) $store_id, $start, $end)
        );
        if (count($register) === 0) {
            return null;
        }
        $labelsPresent = array();
        $breakdowns = array();
        foreach ($register as $reg) {
            $map = $this->_register_close_breakdown_map($reg);
            $breakdowns[$reg->id] = $map;
            foreach ($map as $lbl => $amt) {
                if (abs((float) $amt) >= 0.00001) {
                    $labelsPresent[$lbl] = true;
                }
            }
        }
        $methodCols = $this->_register_close_labels_ordered($labelsPresent);
        $headers_export = array(
            label('Openingtime'),
            label('closedat'),
            label('Openedby'),
        );
        foreach ($methodCols as $colLabel) {
            $headers_export[] = $colLabel;
        }
        $headers = $headers_export;
        if ($include_action_column) {
            $headers[] = ' ';
        }
        $rows_html = array();
        $rows_export = array();
        $totalRev = 0;
        $curSuf = ' ' . $this->setting->currency;
        foreach ($register as $reg) {
            $map = isset($breakdowns[$reg->id]) ? $breakdowns[$reg->id] : array();
            if (count($map) > 0) {
                $totalRev += array_sum($map);
            } else {
                $totalRev += (float) $reg->cash_total + (float) $reg->cc_total + (float) $reg->cheque_total;
            }
            try {
                $uname = User::find($reg->user_id)->username;
            } catch (\Exception $e) {
                $uname = '-';
            }
            $openStr = $reg->date->format('Y-m-d h:i:s');
            $closed = $reg->closed_at ? (string) $reg->closed_at : label('Stillopen');
            $rowPlain = array($openStr, $closed, $uname);
            foreach ($methodCols as $colLabel) {
                $v = isset($map[$colLabel]) ? (float) $map[$colLabel] : 0.0;
                $cell = abs($v) < 0.00001 ? '' : (number_format($v, $this->setting->decimals, '.', '') . $curSuf);
                $rowPlain[] = $cell;
            }
            $rows_export[] = $rowPlain;
            $openHtml = '<a href="javascript:void(0)" ' . ($reg->closed_at ? 'onclick="RegisterDetails(' . (int) $reg->id . ')"' : '') . '>'
                . htmlspecialchars($openStr, ENT_QUOTES, 'UTF-8') . '</a>';
            $rowh = array($openHtml, htmlspecialchars($closed, ENT_QUOTES, 'UTF-8'), htmlspecialchars($uname, ENT_QUOTES, 'UTF-8'));
            foreach ($methodCols as $colLabel) {
                $v = isset($map[$colLabel]) ? (float) $map[$colLabel] : 0.0;
                $cell = abs($v) < 0.00001 ? '' : (number_format($v, $this->setting->decimals, '.', '') . $curSuf);
                $rowh[] = $cell;
            }
            if ($include_action_column) {
                if ($this->user->role === 'admin') {
                    $rowh[] = '<div class="btn-group"><a class="btn btn-default" href="javascript:void(0)" onclick="delete_register('
                        . (int) $reg->id . ')" title="' . label('Delete') . '"><i class="fa fa-times"></i></a></div>';
                } else {
                    $rowh[] = '-';
                }
            }
            $rows_html[] = $rowh;
        }
        $trf = number_format((float) $totalRev, $this->setting->decimals, '.', '') . $curSuf;
        $footer_html = '<h1>' . label('TotalRevenue') . ' : <span class="ReportTotal">' . $trf . '</span></h1>';
        $footer_plain = label('TotalRevenue') . ': ' . $trf;
        $ncols = count($headers_export);
        $footer_csv = array_fill(0, $ncols, '');
        $footer_csv[0] = label('TotalRevenue');
        $footer_csv[$ncols - 1] = $trf;

        $store = Store::find((int) $store_id);
        $storeName = $store ? $store->name : (string) $store_id;

        return array(
            'headers' => $headers,
            'rows' => $rows_html,
            'headers_export' => $headers_export,
            'rows_export' => $rows_export,
            'footer_html' => $footer_html,
            'footer_plain' => $footer_plain,
            'footer_csv' => $footer_csv,
            'title' => label('RegisterStats') . ' — ' . $storeName,
            'subtitle' => label('SelectRange') . ': ' . $start . ' — ' . $end,
            'file_slug' => 'reporte_caja_' . $start . '_' . $end,
        );
    }

    private function _bundle_stock_report($stock_id)
    {
        if ($stock_id === null || $stock_id === '' || strlen((string) $stock_id) < 2) {
            return null;
        }
        $store_id = substr((string) $stock_id, 1);
        $prefix = ((string) $stock_id)[0];
        $stype = (strcmp($prefix, 'S') === 0) ? 'store_id' : 'warehouse_id';
        $products = Product::find('all');
        $headers = array(
            label('Product') . ' (' . label('ProductCode') . ')',
            label('Quantity'),
        );
        $rows_raw = array();
        $rows_plain = array();
        foreach ($products as $prod) {
            if ($prod->type != '0') {
                continue;
            }
            $class = '';
            $stock = Stock::find('first', array('conditions' => array($stype . ' = ? AND product_id = ?', $store_id, $prod->id)));
            $qty = $stock ? (string) $stock->quantity : '-';
            if ($stock && (int) $stock->quantity < (int) $prod->alertqt) {
                $class = 'danger';
            }
            $label = $prod->name . ' (' . $prod->code . ')';
            $rows_raw[] = array('class' => $class, 'name' => htmlspecialchars($label, ENT_QUOTES, 'UTF-8'), 'qty' => $qty);
            $rows_plain[] = array($label, $qty);
        }
        if (count($rows_plain) === 0) {
            return null;
        }
        $footer_html = '';
        $footer_plain = '';
        $footer_csv = null;

        return array(
            'headers' => $headers,
            'rows' => $rows_plain,
            'rows_raw' => $rows_raw,
            'footer_html' => $footer_html,
            'footer_plain' => $footer_plain,
            'footer_csv' => $footer_csv,
            'title' => label('StockStatsTitle'),
            'subtitle' => label('StockStatsSubtitle'),
            'file_slug' => 'reporte_stock_' . preg_replace('/\W+/', '_', $stock_id),
        );
    }
}

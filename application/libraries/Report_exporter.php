<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Salida de tablas de reporte a PDF (TCPDF) o CSV (Excel).
 */
class Report_exporter
{
    /**
     * Vacía los buffers de salida (p. ej. CodeIgniter) para que TCPDF no aborte
     * con "Some data has already been output" al comprobar ob_get_contents().
     */
    public static function clear_output_buffers()
    {
        while (ob_get_level() > 0) {
            ob_end_clean();
        }
    }

    /**
     * @param string $filename nombre sugerido (solo caracteres seguros)
     * @param string $title
     * @param string $subtitle rango de fechas u otro contexto
     * @param array $headers
     * @param array $rows filas: cada una array de celdas (mismo número que cabeceras)
     * @param string $footerLine texto bajo la tabla (totales, etc.)
     */
    public static function send_pdf($filename, $title, $subtitle, array $headers, array $rows, $footerLine = '')
    {
        self::clear_output_buffers();
        require_once APPPATH . 'libraries/tcpdf/tcpdf.php';

        $pdf = new TCPDF('L', PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
        $pdf->SetCreator('Platea');
        $pdf->SetAuthor('Platea');
        $pdf->SetTitle($title);
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);
        $pdf->SetMargins(8, 10, 8);
        $pdf->SetAutoPageBreak(true, 12);
        $pdf->AddPage();
        $pdf->SetFont('dejavusans', '', 8);

        $html = '<h3 style="margin:0 0 4px 0;">' . self::h($title) . '</h3>';
        if ($subtitle !== '') {
            $html .= '<p style="font-size:9px;margin:0 0 8px 0;">' . self::h($subtitle) . '</p>';
        }
        $html .= '<table border="1" cellpadding="3" cellspacing="0" width="100%"><thead><tr>';
        foreach ($headers as $h) {
            $html .= '<th align="center" bgcolor="#dddddd"><b>' . self::h($h) . '</b></th>';
        }
        $html .= '</tr></thead><tbody>';
        foreach ($rows as $row) {
            $html .= '<tr>';
            foreach ($row as $cell) {
                $html .= '<td>' . self::h((string) $cell) . '</td>';
            }
            $html .= '</tr>';
        }
        $html .= '</tbody></table>';
        if ($footerLine !== '') {
            $html .= '<p style="margin-top:8px;"><b>' . self::h($footerLine) . '</b></p>';
        }

        $pdf->writeHTML($html, true, false, true, false, '');
        // 'I' = inline: se ve el PDF en la pestaña (target=_blank + 'D' suele dejarla en blanco)
        $pdf->Output(self::safe_filename($filename) . '.pdf', 'I');
        exit;
    }

    /**
     * CSV UTF-8 con separador ; (adecuado para Excel en locale ES).
     *
     * @param array|null $footer_row una fila extra (ej. totales) o null
     * @param string $currency código de moneda del sistema (ej. EUR, $); si no está vacío, importes en celdas pasan a valor numérico sin sufijo de moneda
     */
    public static function send_excel_csv($filename, array $headers, array $rows, $footer_row = null, $currency = '')
    {
        self::clear_output_buffers();
        $cur = trim((string) $currency);
        if ($cur !== '') {
            $rows = self::rows_cells_numeric_for_csv($rows, $cur);
            if ($footer_row !== null && is_array($footer_row)) {
                $footer_row = self::row_cells_numeric_for_csv($footer_row, $cur);
            }
        }

        $fn = self::safe_filename($filename) . '.csv';
        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="' . $fn . '"');
        header('Cache-Control: max-age=0');
        echo "\xEF\xBB\xBF";
        $out = fopen('php://output', 'w');
        fputcsv($out, $headers, ';');
        foreach ($rows as $r) {
            fputcsv($out, $r, ';');
        }
        if ($footer_row !== null && is_array($footer_row)) {
            fputcsv($out, $footer_row, ';');
        }
        fclose($out);
        exit;
    }

    /**
     * Convierte celdas monetarias / numéricas a texto solo-número (punto decimal) para que Excel no las trate como texto con moneda.
     *
     * @param array $rows
     * @param string $currency
     * @return array
     */
    private static function rows_cells_numeric_for_csv(array $rows, $currency)
    {
        $out = array();
        foreach ($rows as $r) {
            $out[] = self::row_cells_numeric_for_csv($r, $currency);
        }

        return $out;
    }

    /**
     * @param array $row
     * @param string $currency
     * @return array
     */
    private static function row_cells_numeric_for_csv(array $row, $currency)
    {
        $mapped = array();
        foreach ($row as $cell) {
            $mapped[] = self::csv_numeric_cell($cell, $currency);
        }

        return $mapped;
    }

    /**
     * @param mixed $cell
     * @param string $currency
     * @return string|int|float
     */
    private static function csv_numeric_cell($cell, $currency)
    {
        if ($cell === null || $cell === '') {
            return '';
        }
        if (is_int($cell) || is_float($cell)) {
            return $cell;
        }
        $s = trim((string) $cell);
        if ($s === '-') {
            return '';
        }
        $cur = preg_quote($currency, '/');
        if ($cur !== '') {
            // Columna tipo impuesto: "21%(3.50 EUR)" → 3.50
            if (preg_match('/\(([0-9]+(?:\.[0-9]+)?)\s*' . $cur . '\)/u', $s, $m)) {
                return $m[1];
            }
            // Celda solo importe + moneda: "12.34 EUR"
            if (preg_match('/^([0-9]+(?:\.[0-9]+)?)\s*' . $cur . '$/u', $s, $m)) {
                return $m[1];
            }
        }
        // Solo dígitos y un punto (cantidades, ids numéricos como string)
        if (preg_match('/^-?[0-9]+(?:\.[0-9]+)?$/', $s)) {
            return $s;
        }
        // Descuento u otros "12%" → número sin símbolo
        if (preg_match('/^([0-9]+(?:\.[0-9]+)?)\s*%$/u', $s, $m)) {
            return $m[1];
        }

        return $s;
    }

    private static function h($s)
    {
        return htmlspecialchars($s, ENT_QUOTES, 'UTF-8');
    }

    private static function safe_filename($name)
    {
        $name = preg_replace('/[^a-zA-Z0-9_-]+/', '_', $name);
        $name = trim($name, '_');
        if ($name === '') {
            $name = 'reporte';
        }

        return substr($name, 0, 80);
    }
}

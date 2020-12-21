<?php
declare(strict_types=1);

namespace Corona\Utils;
/**
 * Creates a table in HTML Format.
 */
class TableGenerator
{
    public function generateTable(array $headers, array $dataCSV): string
    {
        $result = '<table style="border: 1px solid black">';
        $result .= '<tr>';
        foreach ($headers as $header) {
            $result .= '<th>' . $header . '</th>';
        }
        $result .= '</tr>';
        foreach ($dataCSV as $item) {
            $cells = explode(';', $item);
            $result .= '<tr>';
            foreach ($cells as $cell) {
                $result .= '<td style="align-content: center; border: 1px solid black">' . $cell . '</td>';
            }
            $result .= '</tr>';
        }
        $result .= '</table>';
        return $result;
    }

}
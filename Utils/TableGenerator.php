<?php
declare(strict_types=1);
namespace Corona\Utils;

/**
 * Class TableGenerator
 * @package Corona\Utils
 * Responsibility: Generate a table that can be used in a HTML document.
 */
class TableGenerator
{
    public function generateTable(array $headers, array $dataCSV): string
    {
        $result = '<table>';
        $result .= '<tr>';
        foreach ($headers as $header) {
            $result .= '<th>' . $header . '</th>';
        }
        $result .= '</tr>';
        foreach ($dataCSV as $item) {
            $cells = explode(';', $item);
            $result .= '<tr>';
            foreach ($cells as $cell) {
                $result .= '<td style="align-content: center">' . $cell . '</td>';
            }
            $result .= '</tr>';
        }
        $result .= '</table>';
        return $result;
    }

}
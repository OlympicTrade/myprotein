<?php

namespace Aptero\MSOffice;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class Excel
{
    /**
     * @var \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet
     */
    protected $spreadsheet;

    /**
     * @var \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet
     */
    protected $as; //active sheet

    protected $coords = array('x' => 1, 'y' => 1);

    protected $col = array(
        1  => 'A', 2  => 'B', 3  => 'C', 4  => 'D', 5  => 'E', 6  => 'F', 7  => 'G', 8  => 'H', 9  => 'I', 10 => 'J',
        11 => 'K', 12 => 'M', 13 => 'N', 14 => 'L', 15 => 'O', 16 => 'P', 17 => 'Q', 18 => 'R', 19 => 'S', 20 => 'T',
    );

    public function __construct()
    {
        $this->spreadsheet = new Spreadsheet();
        $this->as = $this->spreadsheet->getActiveSheet();
    }

    public function getAs()
    {
        return $this->as;
    }

    public function setTitle($title)
    {
        /*$this->excel->getProperties()
            ->setTitle($title)
            ->setSubject($title);*/

        return $this;
    }

    public function setColWidth($col, $width)
    {
        $this->as->getColumnDimension($this->col[$col])->setWidth($width);
        return $this;
    }
    public function getCell()
    {
        return $this->getAs()->getCell($this->getCoords());
    }

    public function setVal($val, $cel = null, $row = null, $opts = [])
    {
        if($opts['bold']) {
            $this->setBold();
        }

        if($opts['border_bot']) {
            $this->setBorder(['bot' => true]);
        }

        if(!$cel) {
            $cel = $this->coords['x'];
            $this->coords['x']++;
        } else {
            $this->coords['x'] = $cel;
        }

        if(!$row) {
            $row = $this->coords['y'];
        } else {
            $this->coords['y'] = $row;
        }

        $this->as
            ->setCellValue($this->getCoords($row, $cel), $val);

        return $this;
    }

    public function nextRow()
    {
        $this->coords['y']++;
        $this->coords['x'] = 1;

        return $this;
    }

    public function setCoords($row, $cel)
    {
        $this->coords['y'] = $row;
        $this->coords['x'] = $cel;
    }

    public function getCoords($row = null, $cel = null)
    {
        $cel = !$cel ? $this->coords['x'] : $cel;
        $row = !$row ? $this->coords['y'] : $row;

        return $this->col[$cel] . $row;
    }

    public function getRange($row1 = null, $cel1 = null, $row2 = null, $cell2 = null)
    {
        return $this->getCoords($row1, $cel1) . ':' . $this->getCoords($row2, $cell2);
    }

    public function setBold($bold = true)
    {
        $styleArray = ['font' => ['bold' => true]];
        $this->getAs()->getStyle($this->getCoords())->applyFromArray($styleArray);
        return $this;
    }

    public function seBorder($opts = ['bot' => true])
    {
        $styleArray = ['borders' => ['bottom' => [
            'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
        ]]];

        $this->getAs()->getStyle($this->getCoords())->applyFromArray($styleArray);
        return $this;
    }

    public function send($filename = 'export')
    {
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '.xlsx"');
        header('Cache-Control: max-age=0');

        header ('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
        header ('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT');
        header ('Pragma: public');

        $writer = new Xlsx($this->spreadsheet);
        $writer->save('php://output');

        die();
    }

    public function x($newVal = null)
    {
        if($newVal) {
            $this->coords['x'] = $newVal;
            return $this;
        }

        return $this->coords['x'];
    }

    public function y($newVal = null)
    {
        if($newVal) {
            $this->coords['y'] = $newVal;
            return $this;
        }

        return $this->coords['y'];
    }
}
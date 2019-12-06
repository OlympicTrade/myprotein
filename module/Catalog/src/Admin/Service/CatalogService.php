<?php
namespace CatalogAdmin\Service;

use Aptero\Service\Admin\TableService;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class CatalogService extends TableService
{
    public function sendOzonFile()
    {
        $select = $this->getSql()->select('products_stock');
        $select->order('product_id');
        $select->order('size_id');
        $select->order('taste_id');

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setCellValue('A1', 'артикул');
        $sheet->setCellValue('B1', 'имя (необязательно)');
        $sheet->setCellValue('C1', 'количество');

        $i = 1;
        foreach ($this->execute($select) as $row) {
            $i++;

            $sheet->setCellValue('A' . $i, $row['product_id'] . '-' . $row['size_id'] . '-' . $row['taste_id']);
            $sheet->setCellValue('C' . $i, $row['count']);
        }

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="ozon.xlsx');

        $writer =  new Xlsx($spreadsheet);
        $writer->save('php://output');
        die();
    }
}
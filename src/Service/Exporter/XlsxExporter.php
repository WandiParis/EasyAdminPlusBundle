<?php

namespace Lle\EasyAdminPlusBundle\Service\Exporter;

use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\HttpFoundation\Response;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class XlsxExporter implements ExporterInterface
{



    public function generateResponse(array $data, string $filename = 'export'): Response
    {
        return $this->arrayToExcelResponse($data, $filename.'.'. $this->getFormat());
    }

    public function getFormat():string
    {
        return 'xlsx';
    }

    public function arrayToExcelResponse(array $data, string $filename = 'export.xlsx'): StreamedResponse
    {
        $objPHPExcel = new Spreadsheet();
        $objWorksheet = $objPHPExcel->getActiveSheet();
        $number = 1;

        foreach($data as $row) {
            $objWorksheet->fromArray($row, null, 'A' . $number++);
        }

        $objWriter = new Xlsx($objPHPExcel);

        if (ob_get_length()) ob_end_clean();
        $response = new StreamedResponse(
            function () use ($objWriter) {
                $objWriter->save('php://output');
            }
        );
        $response->headers->set('Content-Type', 'application/download');
        $response->headers->set('Content-Disposition', sprintf('attachment; filename=%s', $filename));
        return $response;
    }

}
<?php

namespace Lle\EasyAdminPlusBundle\Service;

use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\PropertyAccess\PropertyAccessor;
use EasyCorp\Bundle\EasyAdminBundle\Configuration\ConfigManager;
use Symfony\Component\HttpFoundation\Response;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\Translation\DataCollectorTranslator;

class ExportManager
{

    /** @var PropertyAccessor */
    private $propertyAccessor;
    private $configManager;
    private $translator;

    const EXT_CSV = 'csv';
    const EXT_EXCEL = 'xlsx';

    public function __construct(ConfigManager $configManager, PropertyAccessor $propertyAccessor, DataCollectorTranslator $translator)
    {
        $this->configManager = $configManager;
        $this->propertyAccessor = $propertyAccessor;
        $this->translator = $translator;
    }

    public function getExportableValue($entity, array $field): ?string{
        $value = $this->propertyAccessor->getValue($entity, $field['property']);
        if($value instanceOf \DateTime){
            return $value->format($field['format']);
        }elseif(is_array($value)){
            return implode(',', $value);
        }elseif(is_object($value)){
            return (string)$value;
        }
        return $value;
    }

    public function generateData(iterable $paginator, array $fields): array{
        $data= [];
        $keys = array_keys($fields);
        for ($i = 0, $count = count($keys); $i < $count; $i++) {
            $data[0][$i] = $this->translator->trans([$keys[$i]]['label'] ?? $keys[$i]);
        }
        $i = 1;
        foreach ($paginator as $entity) {
            foreach ($fields as $field) {
                $data[$i][] = $this->getExportableValue($entity, $field);
            }
            $i++;
        }
        return $data;
    }

    public function generateResponse(iterable $paginator, array $fields, string $filename,  string $format = self::EXT_CSV): Response{
        $data = $this->generateData($paginator, $fields);
        if($format == self::EXT_EXCEL){
            return $this->arrayToExcelResponse($data, $filename.'.'.$format);
        }else {
            return $this->arrayToCsvResponse($data, $filename.'.'.$format);
        }
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


    public function arrayToCsvResponse(array $data, string $filename = 'export.csv'): Response{
        $handle = tmpfile();
        foreach ($data as $line) {
            fputcsv($handle, $line,';','"');
        }
        rewind($handle);
        $content = stream_get_contents($handle);
        fclose($handle);
        return new Response($content, 200, array(
            'Content-Type' => 'application/force-download',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"'
        ));
    }
}
<?php

namespace Lle\EasyAdminPlusBundle\Service;

use Symfony\Component\PropertyAccess\PropertyAccessor;
use EasyCorp\Bundle\EasyAdminBundle\Configuration\ConfigManager;
use Symfony\Component\HttpFoundation\Response;

class ExportManager
{

    /** @var PropertyAccessor */
    private $propertyAccessor;

    const EXT_CSV = 'csv';
    const EXT_EXCEL = 'xlsx';

    public function __construct(ConfigManager $configManager, PropertyAccessor $propertyAccessor)
    {
        $this->configManager = $configManager;
        $this->propertyAccessor = $propertyAccessor;
    }

    public function getExportableValue($entity, $field){
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

    public function generateResponse($paginator, $fields, $filename,  $format = self::EXT_CSV){
        $csv = [];
        $keys = array_keys($fields);
        for ($i = 0, $count = count($keys); $i < $count; $i++) {
            $csv[0][$i] = $fields[$keys[$i]]['label'] ?? $keys[$i];
        }
        $i=1;
        foreach($paginator as $entity){
            foreach($fields as $field) {
                $csv[$i][] = $this->getExportableValue($entity, $field);
            }
            $i++;
        }
        return $this->arrayToCsvResponse($csv, $filename);
    }

    public function arrayToCsvResponse($data,$name = 'export.csv'){
        $handle = tmpfile();
        foreach ($data as $line) {
            fputcsv($handle, $line,';','"');
        }
        rewind($handle);
        $content = stream_get_contents($handle);
        fclose($handle);
        return new Response($content, 200, array(
            'Content-Type' => 'application/force-download',
            'Content-Disposition' => 'attachment; filename="'.$name.'"'
        ));
    }
}
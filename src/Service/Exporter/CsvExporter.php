<?php

namespace Lle\EasyAdminPlusBundle\Service\Exporter;

use Symfony\Component\HttpFoundation\Response;

class CsvExporter implements ExporterInterface
{


    public function generateResponse(array $data, string $filename): Response
    {
        return $this->arrayToCsvResponse($data, $filename. '.' . $this->getFormat());
    }

    public function getFormat():string
    {
        return 'csv';
    }

    public function arrayToCsvResponse(array $data, string $filename = 'export.csv'): Response
    {
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
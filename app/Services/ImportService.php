<?php

namespace App\Services;

use App\Jobs\ProcessCsvImport;
use Exception;

class ImportService
{
    public function processCsv($file)
    {
        $handle = fopen($file->getRealPath(), "r");
        $header = true;
        $insertData = [];

        while (($row = fgetcsv($handle, 1000, ",")) !== false) {
            if ($header) { $header = false; continue; }

            if (count($row) >= 3) {
                $insertData[] = [
                    'receipt_no' => $row[0],
                    'trx_date' => $row[1],
                    'total_amount' => $row[2],
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
        }
        fclose($handle);

        if (empty($insertData)) {
            throw new Exception("File CSV kosong atau format tidak sesuai.");
        }

        // Kirim data ke Job (Antrean)
        ProcessCsvImport::dispatch($insertData);

        return count($insertData);
    }
}

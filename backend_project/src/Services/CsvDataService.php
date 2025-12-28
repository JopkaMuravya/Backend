<?php

namespace App\Services;

class CsvDataService
{
    public function __construct(
        private string $housesFilePath,
        private string $bookingsFilePath
    ){}

    public function readHouses(): array
    {
        return $this->readCSV($this->housesFilePath);
    }

    public function readBookings(): array
    {
        return $this->readCSV($this->bookingsFilePath);
    }

    public function writeHouses(array $houses): bool
    {
        return $this->writeCSV($this->housesFilePath, $houses);
    }

    public function writeBookings(array $bookings): bool
    {
        return $this->writeCSV($this->bookingsFilePath, $bookings);
    }

    private function readCSV(string $file): array
    {
        if (!file_exists($file)) {
            return [];
        }

        $rows = [];
        $handle = fopen($file, 'r');
        
        if ($handle !== false) {
            $headers = fgetcsv($handle);
            
            while (($data = fgetcsv($handle)) !== false) {
                $row = [];
                foreach ($headers as $index => $header) {
                    $row[$header] = $data[$index] ?? '';
                }
                $rows[] = $row;
            }
            
            fclose($handle);
        }
        
        return $rows;
    }

    private function writeCSV(string $file, array $data): bool
    {
        if (empty($data)) {
            return false;
        }

        $handle = fopen($file, 'w');
        if ($handle === false) {
            return false;
        }

        fputcsv($handle, array_keys($data[0]));
        foreach ($data as $row) {
            fputcsv($handle, $row);
        }

        fclose($handle);
        return true;
    }
}
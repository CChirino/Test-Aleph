<?php

namespace App\Services;

use App\Contracts\AlephServiceInterface;
use App\Models\Cmdb;
use Carbon\Carbon;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\IOFactory;

class CmdbExportImportService
{
    private const REQUIRED_COLUMNS = ['Nombre', 'Identificador'];

    public function __construct(
        private readonly AlephServiceInterface $alephService,
        private readonly string $exportDirectory = 'reports',
        private readonly string $exportDisk = 'public'
    ) {}

    public function export(int $categoryId, array $records): string
    {
        $category = $this->validateCategory($categoryId);
        $spreadsheet = $this->createSpreadsheet($records);
        return $this->saveSpreadsheet($spreadsheet, $category['nombre']);
    }

    public function import(int $categoryId, UploadedFile $file): int
    {
        $this->validateImportFile($file);
        $this->validateCategory($categoryId);
        $records = $this->parseExcelFile($file);
        return $this->saveRecords($categoryId, $records);
    }

    private function validateCategory(int $categoryId): array
    {
        $category = $this->alephService->getCategoryById($categoryId);
        if (!$category) {
            throw new \Exception('Categoría no encontrada');
        }
        return $category;
    }

    private function validateImportFile(UploadedFile $file): void
    {
        $allowedExtensions = config('aleph.imports.allowed_extensions', ['xlsx', 'xls']);
        $maxSize = config('aleph.imports.max_file_size', 5120);

        if (!in_array($file->getClientOriginalExtension(), $allowedExtensions)) {
            throw new \Exception('Tipo de archivo no permitido. Use: ' . implode(', ', $allowedExtensions));
        }

        if ($file->getSize() > $maxSize * 1024) {
            throw new \Exception("El archivo excede el tamaño máximo permitido ({$maxSize}KB)");
        }
    }

    private function createSpreadsheet(array $records): Spreadsheet
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $headers = $this->getHeaders($records);
        $this->writeHeaders($sheet, $headers);
        $this->writeData($sheet, $records, $headers);
        $this->autoSizeColumns($sheet, count($headers));

        return $spreadsheet;
    }

    private function getHeaders(array $records): array
    {
        $additionalFields = $this->getAdditionalFields($records);
        return array_merge(self::REQUIRED_COLUMNS, $additionalFields);
    }

    private function getAdditionalFields(array $records): array
    {
        $fields = collect($records)
            ->pluck('campos_cmdb')
            ->filter()
            ->flatMap(fn($fields) => array_keys($fields))
            ->unique()
            ->sort()
            ->values()
            ->all();

        return array_map('ucfirst', $fields);
    }

    private function writeHeaders($sheet, array $headers): void
    {
        foreach ($headers as $col => $header) {
            $sheet->setCellValue(
                Coordinate::stringFromColumnIndex($col + 1) . '1',
                $header
            );
        }
    }

    private function writeData($sheet, array $records, array $headers): void
    {
        foreach ($records as $row => $record) {
            $this->writeRecord($sheet, $row + 2, $record, $headers);
        }
    }

    private function writeRecord($sheet, int $row, array $record, array $headers): void
    {
        foreach ($headers as $col => $header) {
            $value = $this->getRecordValue($record, $header);
            $sheet->setCellValue(
                Coordinate::stringFromColumnIndex($col + 1) . $row,
                $value
            );
        }
    }

    private function getRecordValue(array $record, string $header): string
    {
        if ($header === 'Nombre') return $record['nombre'] ?? '';
        if ($header === 'Identificador') return $record['identificador'] ?? '';
        
        $key = strtolower($header);
        return $record['campos_cmdb'][$key] ?? '';
    }

    private function autoSizeColumns($sheet, int $columnCount): void
    {
        for ($col = 1; $col <= $columnCount; $col++) {
            $sheet->getColumnDimension(Coordinate::stringFromColumnIndex($col))->setAutoSize(true);
        }
    }

    private function saveSpreadsheet(Spreadsheet $spreadsheet, string $categoryName): string
    {
        $timestamp = Carbon::now()->format('Y-m-d_H-i-s');
        $filename = $this->sanitizeFilename("{$categoryName}_{$timestamp}.xlsx");
        $path = "{$this->exportDirectory}/{$filename}";

        Storage::disk($this->exportDisk)->makeDirectory($this->exportDirectory);
        
        $writer = new Xlsx($spreadsheet);
        $writer->save(Storage::disk($this->exportDisk)->path($path));

        return $filename;
    }

    private function sanitizeFilename(string $filename): string
    {
        return preg_replace('/[^a-zA-Z0-9._-]/', '_', $filename);
    }

    private function parseExcelFile(UploadedFile $file): array
    {
        $spreadsheet = IOFactory::load($file->getPathname());
        $worksheet = $spreadsheet->getActiveSheet();
        $rows = $worksheet->toArray();
        
        $headers = array_map('trim', $rows[0]);
        $this->validateHeaders($headers);
        
        return $this->parseRows($rows, $headers);
    }

    private function validateHeaders(array $headers): void
    {
        $missingColumns = array_diff(self::REQUIRED_COLUMNS, $headers);
        if (!empty($missingColumns)) {
            throw new \Exception('Columnas requeridas faltantes: ' . implode(', ', $missingColumns));
        }
    }

    private function parseRows(array $rows, array $headers): array
    {
        $records = [];
        for ($i = 1; $i < count($rows); $i++) {
            $record = $this->parseRow($rows[$i], $headers);
            if ($record) {
                $records[] = $record;
            }
        }
        return $records;
    }

    private function parseRow(array $row, array $headers): ?array
    {
        $record = [];
        $campos_cmdb = [];

        foreach ($headers as $index => $header) {
            $value = $this->cleanValue($row[$index] ?? '');
            
            if (in_array($header, self::REQUIRED_COLUMNS)) {
                if (empty($value)) {
                    return null;
                }
                $record[strtolower($header)] = $value;
            } else {
                $campos_cmdb[$header] = $value;
            }
        }

        if (!empty($campos_cmdb)) {
            $record['campos_cmdb'] = $campos_cmdb;
        }

        return $record;
    }

    private function cleanValue($value): string
    {
        if (is_null($value)) {
            return '';
        }

        $value = trim($value);
        $value = str_replace(['"', "'"], '', $value);
        $value = preg_replace('/\s+/', ' ', $value);
        return $value;
    }

    private function saveRecords(int $categoryId, array $records): int
    {
        $count = 0;
        foreach ($records as $record) {
            if ($this->saveRecord($categoryId, $record)) {
                $count++;
            }
        }
        return $count;
    }

    private function saveRecord(int $categoryId, array $record): bool
    {
        try {
            Cmdb::updateOrCreate(
                [
                    'categoria_id' => $categoryId,
                    'identificador' => $record['identificador']
                ],
                [
                    'nombre' => $record['nombre'],
                    'campos_adicionales' => $record['campos_cmdb'] ?? null
                ]
            );
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }
}

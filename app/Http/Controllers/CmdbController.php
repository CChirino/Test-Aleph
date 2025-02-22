<?php

namespace App\Http\Controllers;

use App\Contracts\AlephServiceInterface;
use App\Http\Requests\ImportCmdbRequest;
use App\Services\CmdbExportImportService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class CmdbController extends Controller
{
    public function __construct(
        private readonly AlephServiceInterface $alephService,
        private readonly CmdbExportImportService $exportImportService
    ) {}

    public function index(int $categoryId): View|RedirectResponse
    {
        $category = $this->alephService->getCategoryById($categoryId);
        
        if (!$category) {
            return redirect()
                ->route('categories.index')
                ->with('error', 'Categoría no encontrada');
        }

        $records = $this->alephService->getCmdbRecords($categoryId);
        return view('cmdb.index', compact('category', 'records'));
    }

    public function export(int $categoryId): RedirectResponse
    {
        try {
            $category = $this->alephService->getCategoryById($categoryId);
            if (!$category) {
                return $this->errorResponse('Categoría no encontrada');
            }

            $records = $this->alephService->getCmdbRecords($categoryId);
            if (empty($records)) {
                return $this->errorResponse('No hay registros para exportar');
            }

            $filename = $this->exportImportService->export($categoryId, $records);
            
            return redirect()->back()->with([
                'success' => 'Archivo exportado correctamente',
                'download_url' => Storage::url("reports/{$filename}")
            ]);
        } catch (\Exception $e) {
            return $this->errorResponse("Error al exportar: {$e->getMessage()}");
        }
    }

    public function import(ImportCmdbRequest $request, int $categoryId): RedirectResponse
    {
        try {
            $category = $this->alephService->getCategoryById($categoryId);
            if (!$category) {
                return $this->errorResponse('Categoría no encontrada');
            }

            $count = $this->exportImportService->import($categoryId, $request->file('file'));
            
            return redirect()
                ->back()
                ->with('success', "Se importaron {$count} registros correctamente");
        } catch (\Exception $e) {
            return $this->errorResponse("Error al importar: {$e->getMessage()}");
        }
    }

    private function errorResponse(string $message): RedirectResponse
    {
        return redirect()->back()->with('error', $message);
    }
}

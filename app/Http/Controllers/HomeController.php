<?php

namespace App\Http\Controllers;

use App\Models\Cmdb;
use App\Services\AlephService;
use App\Exceptions\AlephApiException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function __construct(
        private readonly AlephService $alephService
    ) {}

    public function index(): View
    {
        try {
            $categories = $this->alephService->getCategories();
            $categoryCount = count($categories);

            return view('home', [
                'categoryCount' => $categoryCount
            ]);
        } catch (AlephApiException $e) {
            Log::warning('Error al obtener categorías en Home', [
                'code' => $e->getApiErrorCode(),
                'message' => $e->getMessage()
            ]);
            
            return view('home', [
                'categoryCount' => 0,
                'error' => $e->getMessage()
            ]);
        } catch (\Exception $e) {
            Log::error('Error inesperado en Home', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return view('home', [
                'categoryCount' => 0,
                'error' => 'Error al cargar la información'
            ]);
        }
    }
}

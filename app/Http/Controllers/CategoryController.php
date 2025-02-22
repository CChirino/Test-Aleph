<?php

namespace App\Http\Controllers;

use App\Services\AlephService;
use Illuminate\View\View;

class CategoryController extends Controller
{
    public function __construct(
        private readonly AlephService $alephService
    ) {}

    public function index(): View
    {
        try {
            $categories = $this->alephService->getCategories();
            return view('categories.index', compact('categories'));
        } catch (\Exception $e) {
            return view('categories.index', [
                'categories' => [],
                'error' => 'Error al obtener las categorías: ' . $e->getMessage()
            ]);
        }
    }
}

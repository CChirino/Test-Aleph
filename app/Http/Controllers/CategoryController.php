<?php

namespace App\Http\Controllers;

use App\Services\AlephService;
use App\Exceptions\AlephApiException;
use Illuminate\View\View;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    private const PER_PAGE = 10;

    public function __construct(
        private readonly AlephService $alephService
    ) {}

    public function index(Request $request): View
    {
        try {
            $categories = $this->alephService->getCategories();
            $paginatedCategories = $this->paginateCategories($categories, $request);

            return view('categories.index', ['categories' => $paginatedCategories]);
        } catch (AlephApiException $e) {
            return view('categories.index', [
                'categories' => [],
                'error' => $e->getMessage()
            ]);
        } catch (\Exception $e) {
            return view('categories.index', [
                'categories' => [],
                'error' => 'Error inesperado al obtener las categorías. Por favor, inténtelo de nuevo más tarde.'
            ]);
        }
    }

    private function paginateCategories(array $categories, Request $request): LengthAwarePaginator
    {
        $currentPage = $request->get('page', 1);
        $offset = ($currentPage - 1) * self::PER_PAGE;
        
        return new LengthAwarePaginator(
            array_slice($categories, $offset, self::PER_PAGE),
            count($categories),
            self::PER_PAGE,
            $currentPage,
            ['path' => $request->url()]
        );
    }
}

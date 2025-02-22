<?php

namespace App\Services;

use App\Contracts\AlephServiceInterface;
use App\Exceptions\AlephApiException;
use App\Models\Cmdb;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class AlephService implements AlephServiceInterface
{
    private const API_ENDPOINTS = [
        'categories' => '/API/get_categorias/',
        'cmdb' => '/API/get_cmdb/'
    ];

    public function __construct(
        private readonly string $baseUrl = '',
        private readonly string $apiKey = ''
    ) {}

    public function getCategories(): array
    {
        try {
            return Cache::remember('aleph_categories', now()->addHours(1), function () {
                $response = $this->makeApiCall(self::API_ENDPOINTS['categories']);
                return $response['categorias'] ?? [];
            });
        } catch (AlephApiException $e) {
            Log::error('Error en API de Aleph al obtener categorías', [
                'code' => $e->getApiErrorCode(),
                'message' => $e->getMessage()
            ]);
            return Cache::get('aleph_categories', []);
        } catch (\Exception $e) {
            Log::error('Error inesperado al obtener categorías', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return Cache::get('aleph_categories', []);
        }
    }

    public function getCmdbRecords(?int $categoryId = null): array
    {
        $records = collect($this->getApiRecords($categoryId));

        if ($categoryId) {
            $localRecords = $this->getLocalRecords($categoryId);
            $records = $this->mergeRecords($records, $localRecords);
        }

        return $records->values()->all();
    }

    public function getCategoryById($categoryId): ?array
    {
        return collect($this->getCategories())
            ->first(fn ($category) => (string)$category['id'] === (string)$categoryId);
    }

    private function getApiRecords(?int $categoryId = null): array
    {
        try {
            $params = ['api_key' => $this->getApiKey()];
            if ($categoryId) {
                $params['categoria_id'] = $categoryId;
            }

            $response = $this->makeApiCall(self::API_ENDPOINTS['cmdb'], $params);
            $records = $response['cmdb'] ?? [];

            if ($categoryId) {
                $records = array_filter(
                    $records,
                    fn($record) => isset($record['categoria_id']) && 
                                 (string)$record['categoria_id'] === (string)$categoryId
                );
            }

            return $records;
        } catch (AlephApiException $e) {
            Log::error('Error en API de Aleph al obtener registros CMDB', [
                'code' => $e->getApiErrorCode(),
                'message' => $e->getMessage(),
                'category_id' => $categoryId
            ]);
            return [];
        } catch (\Exception $e) {
            Log::error('Error inesperado al obtener registros CMDB', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'category_id' => $categoryId
            ]);
            return [];
        }
    }

    private function getLocalRecords(int $categoryId): Collection
    {
        return Cmdb::byCategory($categoryId)
            ->get()
            ->map(fn($record) => [
                'categoria_id' => $record->categoria_id,
                'identificador' => $record->identificador,
                'nombre' => $record->nombre,
                'campos_cmdb' => $record->campos_cmdb,
                'source' => 'local'
            ]);
    }

    private function mergeRecords(Collection $apiRecords, Collection $localRecords): Collection
    {
        return $apiRecords->concat($localRecords)
            ->groupBy('identificador')
            ->map(fn($group) => $group->firstWhere('source', 'local') ?? $group->first());
    }

    private function makeApiCall(string $endpoint, array $params = []): array
    {
        try {
            $params['api_key'] = $this->getApiKey();
            
            $response = Http::asForm()
                ->post($this->getBaseUrl() . $endpoint, $params);

            if ($response->status() === 401) {
                throw AlephApiException::unauthorized();
            }

            if ($response->status() === 403) {
                throw AlephApiException::apiKeyNotFound();
            }

            if ($response->status() === 500) {
                throw AlephApiException::internalServerError();
            }

            if (!$response->successful()) {
                throw AlephApiException::unexpectedError(
                    "Error HTTP: " . $response->status() . " - " . $response->body()
                );
            }

            return $response->json() ?? [];
        } catch (AlephApiException $e) {
            throw $e;
        } catch (\Exception $e) {
            throw AlephApiException::unexpectedError($e->getMessage());
        }
    }

    private function getBaseUrl(): string
    {
        return $this->baseUrl ?: config('aleph.base_url');
    }

    private function getApiKey(): string
    {
        return $this->apiKey ?: config('aleph.api_key');
    }
}

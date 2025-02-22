<?php

namespace App\Contracts;

interface AlephServiceInterface
{
    /**
     * Get all categories from Aleph API
     *
     * @return array
     */
    public function getCategories(): array;

    /**
     * Get CMDB records by category ID
     *
     * @param int|null $categoryId
     * @return array
     */
    public function getCmdbRecords(?int $categoryId = null): array;

    /**
     * Get category by ID
     *
     * @param int|string $categoryId
     * @return array|null
     */
    public function getCategoryById($categoryId): ?array;
}

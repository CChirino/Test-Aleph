<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Cmdb extends Model
{
    protected $table = 'cmdb';
    
    protected $fillable = [
        'categoria_id',
        'identificador',
        'nombre',
        'campos_adicionales'
    ];

    protected $casts = [
        'campos_adicionales' => 'array',
        'categoria_id' => 'integer'
    ];

    public function getCamposCmdbAttribute(): array
    {
        return $this->campos_adicionales ?? [];
    }

    public function scopeByCategory(Builder $query, int $categoryId): Builder
    {
        return $query->where('categoria_id', $categoryId);
    }

    public function scopeByIdentifier(Builder $query, string $identifier): Builder
    {
        return $query->where('identificador', $identifier);
    }
}

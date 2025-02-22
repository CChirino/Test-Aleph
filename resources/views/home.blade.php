@extends('layouts.app')

@section('content')
<div class="container">
    <h1 class="mb-4">Panel de Control</h1>
    
    <div class="row">
        <div class="col-md-4 mb-4">
            <div class="card h-100">
                <div class="card-body text-center pt-3 pb-3">
                    <div class="mb-3">
                        <i class="fas fa-list fa-3x text-primary"></i>
                    </div>
                    <h5 class="card-title">Total de Categorías</h5>
                    <div class="display-4 my-3">{{ $categoryCount }}</div>
                    <p class="card-text text-muted">Categorías disponibles</p>
                    <a href="{{ route('categories.index') }}" class="btn btn-primary">
                        <i class="fas fa-eye"></i> Ver Categorías
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

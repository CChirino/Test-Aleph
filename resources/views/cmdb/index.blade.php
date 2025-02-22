@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row mb-4">
        <div class="col">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h2>{{ $category['nombre'] }}</h2>
                    <p class="text-muted mb-0">{{ $category['descripcion'] ?? 'Sin descripción' }}</p>
                </div>
                <div>
                    <button type="button" class="btn btn-primary btn-sm me-2" data-bs-toggle="modal" data-bs-target="#importModal">
                        <i class="fas fa-upload"></i> Importar
                    </button>
                    <a href="{{ route('cmdb.export', $category['id']) }}" class="btn btn-success btn-sm me-2">
                        <i class="fas fa-download"></i> Exportar
                    </a>
                    <a href="{{ route('categories.index') }}" class="btn btn-secondary btn-sm">
                        <i class="fas fa-arrow-left"></i> Volver
                    </a>
                </div>
            </div>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
            @if(session('download_url'))
                <a href="{{ session('download_url') }}" class="alert-link">Descargar archivo</a>
            @endif
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger">
            {{ session('error') }}
        </div>
    @endif

    @if(empty($records))
        <div class="alert alert-info">
            <h5 class="alert-heading">No hay registros</h5>
            <p class="mb-0">No se encontraron registros CMDB para la categoría "{{ $category['nombre'] }}".</p>
        </div>
    @else
        <div class="row mb-3">
            <div class="col">
                <div class="alert alert-info">
                    <strong>Total de registros encontrados:</strong> {{ count($records) }}
                </div>
            </div>
        </div>

        @foreach($records as $record)
            <div class="card mb-4 cmdb-detail">
                <div class="card-header">
                    <h5 class="card-title mb-0">{{ $record['nombre'] }}</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <dl class="row">
                                <dt class="col-sm-4">Identificador</dt>
                                <dd class="col-sm-8">{{ $record['identificador'] }}</dd>
                            </dl>
                        </div>
                        @if(!empty($record['campos_cmdb']))
                            <div class="col-md-6">
                                <h6 class="mb-3">Campos CMDB</h6>
                                <dl class="row">
                                    @foreach($record['campos_cmdb'] as $campo => $valor)
                                        <dt class="col-sm-4">{{ $campo }}</dt>
                                        <dd class="col-sm-8">{{ $valor }}</dd>
                                    @endforeach
                                </dl>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        @endforeach
    @endif
</div>

<!-- Modal de Importación -->
<div class="modal fade" id="importModal" tabindex="-1" aria-labelledby="importModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="importModalLabel">Importar Registros CMDB</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('cmdb.import', $category['id']) }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="file" class="form-label">Archivo Excel (XLSX)</label>
                        <input type="file" class="form-control" id="file" name="file" accept=".xlsx,.xls" required>
                    </div>
                    <div class="alert alert-info">
                        <h6>Instrucciones:</h6>
                        <ul class="mb-0">
                            <li>El archivo debe ser formato Excel (.xlsx, .xls)</li>
                            <li>Debe contener las columnas: Nombre, Identificador</li>
                            <li>Puede incluir columnas adicionales para campos específicos</li>
                        </ul>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Importar</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

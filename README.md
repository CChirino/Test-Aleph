# Aleph CMDB Integration

Aplicación Laravel para la integración con el API de Aleph Manager, permitiendo la gestión y sincronización de registros CMDB.

## Requisitos Previos

- PHP 8.1 o superior
- Composer
- MySQL 5.7 o superior
- Node.js y NPM (para los assets)
- Git

## Instalación

1. Clonar el repositorio:
```bash
git clone <repository-url>
cd test-aleph
```

2. Instalar dependencias de PHP:
```bash
composer install
```

3. Copiar el archivo de configuración:
```bash
cp .env.example .env
```

4. Configurar el archivo .env:
```env
APP_NAME="Aleph CMDB"
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=aleph_cmdb
DB_USERNAME=root
DB_PASSWORD=

# Configuración de Aleph
ALEPH_BASE_URL=https://qa.alephmanager.com
ALEPH_API_KEY=tu_api_key_aqui
```

5. Generar la clave de la aplicación:
```bash
php artisan key:generate
```

6. Crear la base de datos:
```sql
CREATE DATABASE aleph_cmdb CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

7. Ejecutar las migraciones:
```bash
php artisan migrate
```

8. Crear el enlace simbólico para el almacenamiento:
```bash
php artisan storage:link
```

9. Configurar permisos de carpetas:
```bash
chmod -R 775 storage bootstrap/cache
chown -R $USER:www-data storage bootstrap/cache
```

## Configuración

### API de Aleph

1. Obtener las credenciales de API de Aleph Manager
2. Configurar las credenciales en el archivo .env:
```env
ALEPH_BASE_URL=https://qa.alephmanager.com
ALEPH_API_KEY=tu_api_key_aqui
```

### Configuración de Importación/Exportación

Los siguientes valores pueden ser configurados en `config/aleph.php`:

```php
return [
    'base_url' => env('ALEPH_BASE_URL'),
    'api_key' => env('ALEPH_API_KEY'),
    
    'exports' => [
        'directory' => 'reports',
        'disk' => 'public',
    ],
    
    'imports' => [
        'allowed_extensions' => ['xlsx', 'xls'],
        'max_file_size' => 5120, // 5MB
    ],
];
```

## Uso

### Iniciar el servidor de desarrollo

```bash
php artisan serve
```

La aplicación estará disponible en `http://localhost:5000`

### Funcionalidades Principales

1. **Listado de Categorías**
   - Acceder a la página principal para ver todas las categorías disponibles
   - Hacer clic en una categoría para ver sus registros CMDB

2. **Gestión de Registros CMDB**
   - Ver registros por categoría
   - Exportar registros a Excel
   - Importar registros desde Excel

### Formato de Archivo de Importación

El archivo Excel debe contener las siguientes columnas obligatorias:
- `Nombre`
- `Identificador`

Columnas adicionales serán tratadas como campos personalizados.

## Estructura de Base de Datos

La tabla principal `cmdb` tiene la siguiente estructura:

```sql
CREATE TABLE cmdb (
    id bigint unsigned NOT NULL AUTO_INCREMENT,
    categoria_id varchar(255) NOT NULL,
    identificador varchar(255) NOT NULL,
    nombre varchar(255) NOT NULL,
    campos_adicionales json DEFAULT NULL,
    created_at timestamp NULL DEFAULT NULL,
    updated_at timestamp NULL DEFAULT NULL,
    PRIMARY KEY (id)
);
```

## Solución de Problemas

### Errores Comunes

1. **Error de Permisos**
   ```bash
   chmod -R 775 storage bootstrap/cache
   chown -R $USER:www-data storage bootstrap/cache
   ```

2. **Error de Conexión a la API**
   - Verificar que ALEPH_BASE_URL y ALEPH_API_KEY estén correctamente configurados
   - Confirmar que la API sea accesible desde el servidor

3. **Errores de Importación**
   - Verificar que el archivo Excel tenga las columnas requeridas
   - Comprobar que el archivo no exceda el tamaño máximo permitido

## Mantenimiento

### Limpiar Caché

```bash
php artisan cache:clear
php artisan config:clear
php artisan view:clear
```

### Actualizar Dependencias

```bash
composer update
```

## Seguridad

- Las credenciales de API se almacenan en el archivo .env
- Los archivos exportados se guardan en storage/app/public/reports
- Se implementa validación de archivos durante la importación
- Se sanitizan los nombres de archivo para exportación

## Soporte

Para reportar problemas o solicitar ayuda, por favor crear un issue en el repositorio.

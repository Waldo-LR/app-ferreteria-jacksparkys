# Ferretería Jack Sparkys

Aplicación web para la gestión de ventas, alquileres de herramientas, inventario, compras, caja y cobranzas. Está construida con PHP & MySQL (Framework: Laravel, Livewire, Tailwindcss, AdminLTE).

## Requisitos

- PHP 8.3 o superior con las extensiones requeridas por Laravel y Composer.
- Composer.
- Node.js y npm.
- MySQL (puede ejecutarse desde XAMPP).

## Instalación local

1. Coloca el proyecto dentro de `C:\xampp\htdocs\app-ferreteria` y habilita Apache y MySQL en XAMPP.
2. Instala las dependencias PHP y JavaScript:

   ```powershell
   composer install
   npm install
   ```

3. Copia `.env.example` como `.env` si todavía no existe y configura `APP_URL` y la conexión MySQL (`DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`).
4. Genera la clave de la aplicación:

   ```powershell
   php artisan key:generate
   ```

5. Prepara los recursos de interfaz:

   ```powershell
   npm run build
   ```

6. Ejecuta las migraciones únicamente si la base de datos está vacía o si ya respaldaste y revisaste el impacto sobre la base existente:

   ```powershell
   php artisan migrate
   ```

   El seeder de demostración reinicia y vuelve a poblar tablas del sistema. No lo ejecutes sobre una base con información que debas conservar.

Para desarrollo, inicia Vite en otra terminal con `npm run dev`. También puedes iniciar Laravel con `php artisan serve` y abrir la URL que indique Artisan. En Apache, configura el sitio para que su raíz pública sea la carpeta `public` del proyecto.

## Verificación

```powershell
php artisan test
php artisan route:list
npm run build
```

## Módulos

Inicio de sesión y panel, ventas/POS, alquileres, cobranzas, caja, inventario, compras, catálogo de productos, categorías, marcas, clientes, proveedores, usuarios, auditoría, reportes y configuración.

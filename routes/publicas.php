<?php

declare(strict_types=1);

use App\Controladores\Cuenta\CuentaController;
use App\Controladores\Autenticacion\AutenticacionController;
use App\Controladores\Compra\CarritoController;
use App\Controladores\Campanias\CampaniaController;
use App\Controladores\Catalogo\CatalogoController;
use App\Controladores\Compra\ProcesoCompraController;
use App\Controladores\Publico\InicioController;
use App\Controladores\Publico\PaginaController;
use App\Controladores\Productos\ProductoController;
use App\Controladores\ComercioInteligente\ComercioInteligenteController;
use App\Middleware\AutenticacionMiddleware;
use App\Middleware\AccesoRutaRolMiddleware;
use App\Middleware\CsrfMiddleware;
use App\Middleware\LimiteSolicitudesMiddleware;
use App\Nucleo\Enrutamiento\Enrutador;

/**
 * Archivo de rutas públicas y de la aplicación principal.
 * Define los endpoints accesibles para clientes y visitantes del sitio web.
 */
return static function (Enrutador $enrutador): void {

    // 1. CAMPAÑAS Y PÁGINA PRINCIPAL

    $enrutador->post('/campaigns/{id}/track', [CampaniaController::class, 'registrarEvento'], [CsrfMiddleware::class]);
    $enrutador->obtener('/', [InicioController::class, 'indice']);

    // 2. CATÁLOGO Y PRODUCTOS

    $enrutador->obtener('/catalog', [CatalogoController::class, 'indice']);
    $enrutador->obtener('/products/{id}', [ProductoController::class, 'detalle']);

    // 3. COMERCIO INTELIGENTE Y ASISTENTE

    $enrutador->obtener('/smart/recommend', [ComercioInteligenteController::class, 'recomendador']);
    $enrutador->obtener('/smart/compare', [ComercioInteligenteController::class, 'comparar']);
    $enrutador->obtener('/smart/optimizer', [ComercioInteligenteController::class, 'optimizador'], [AccesoRutaRolMiddleware::class]);
    $enrutador->obtener('/mayorista', [ComercioInteligenteController::class, 'mayorista'], [AccesoRutaRolMiddleware::class]);
    $enrutador->obtener('/smart/ads', [ComercioInteligenteController::class, 'publicidadInteligente'], [AccesoRutaRolMiddleware::class]);
    $enrutador->obtener('/smart/assistant', [ComercioInteligenteController::class, 'asistente']);
    $enrutador->obtener('/smart/assistant/reply', [ComercioInteligenteController::class, 'respuestaAsistente']);

    // 4. CARRITO Y PROCESO DE COMPRA

    $enrutador->obtener('/cart', [CarritoController::class, 'indice'], [AccesoRutaRolMiddleware::class]);
    $enrutador->post('/cart', [CarritoController::class, 'actualizar'], [AccesoRutaRolMiddleware::class, CsrfMiddleware::class]);
    $enrutador->obtener('/checkout', [ProcesoCompraController::class, 'indice'], [AccesoRutaRolMiddleware::class]);
    $enrutador->post('/checkout', [ProcesoCompraController::class, 'guardar'], [AccesoRutaRolMiddleware::class, CsrfMiddleware::class]);

    // 5. AUTENTICACIÓN Y CUENTA

    $enrutador->obtener('/login', [AutenticacionController::class, 'formularioInicioSesion']);
    $enrutador->post('/login', [AutenticacionController::class, 'iniciarSesion'], [LimiteSolicitudesMiddleware::class, CsrfMiddleware::class]);
    $enrutador->obtener('/register', [AutenticacionController::class, 'formularioRegistro']);
    $enrutador->post('/register', [AutenticacionController::class, 'registrar'], [LimiteSolicitudesMiddleware::class, CsrfMiddleware::class]);
    $enrutador->obtener('/logout', [AutenticacionController::class, 'formularioCierreSesion'], [AutenticacionMiddleware::class]);
    $enrutador->post('/logout', [AutenticacionController::class, 'cerrarSesion'], [AutenticacionMiddleware::class, CsrfMiddleware::class]);
    $enrutador->obtener('/account', [CuentaController::class, 'indice'], [AutenticacionMiddleware::class]);

    // 6. PÁGINAS INFORMATIVAS Y CONTACTO
  
    $enrutador->obtener('/about', [PaginaController::class, 'nosotros']);
    $enrutador->obtener('/contact', [PaginaController::class, 'contacto']);
    $enrutador->post('/contact', [PaginaController::class, 'enviarContacto'], [CsrfMiddleware::class]);
};
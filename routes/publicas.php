<?php

declare(strict_types=1);

use App\Http\Controllers\CuentaController;
use App\Http\Controllers\AutenticacionController;
use App\Http\Controllers\CarritoController;
use App\Http\Controllers\CampaniaController;
use App\Http\Controllers\CatalogoController;
use App\Http\Controllers\ProcesoCompraController;
use App\Http\Controllers\InicioController;
use App\Http\Controllers\PaginaController;
use App\Http\Controllers\ProductoController;
use App\Http\Controllers\ComercioInteligenteController;
use App\Http\Middleware\AutenticacionMiddleware;
use App\Http\Middleware\AccesoRutaRolMiddleware;
use App\Http\Middleware\CsrfMiddleware;
use App\Http\Middleware\LimiteSolicitudesMiddleware;
use App\Http\Enrutamiento\Enrutador;

return static function (Enrutador $enrutador): void {
    $enrutador->post('/campaigns/{id}/track', [CampaniaController::class, 'registrarEvento'], [CsrfMiddleware::class]);
    $enrutador->obtener('/', [InicioController::class, 'indice']);
    $enrutador->obtener('/catalog', [CatalogoController::class, 'indice']);
    $enrutador->obtener('/products/{id}', [ProductoController::class, 'detalle']);
    $enrutador->obtener('/smart/recommend', [ComercioInteligenteController::class, 'recomendador']);
    $enrutador->obtener('/smart/compare', [ComercioInteligenteController::class, 'comparar']);
    $enrutador->obtener('/smart/optimizer', [ComercioInteligenteController::class, 'optimizador'], [AccesoRutaRolMiddleware::class]);
    $enrutador->obtener('/mayorista', [ComercioInteligenteController::class, 'mayorista'], [AccesoRutaRolMiddleware::class]);
    $enrutador->obtener('/smart/ads', [ComercioInteligenteController::class, 'publicidadInteligente'], [AccesoRutaRolMiddleware::class]);
    $enrutador->obtener('/smart/assistant', [ComercioInteligenteController::class, 'asistente']);
    $enrutador->obtener('/smart/assistant/reply', [ComercioInteligenteController::class, 'respuestaAsistente']);
    $enrutador->obtener('/cart', [CarritoController::class, 'indice'], [AccesoRutaRolMiddleware::class]);
    $enrutador->post('/cart', [CarritoController::class, 'actualizar'], [AccesoRutaRolMiddleware::class, CsrfMiddleware::class]);
    $enrutador->obtener('/checkout', [ProcesoCompraController::class, 'indice'], [AccesoRutaRolMiddleware::class]);
    $enrutador->post('/checkout', [ProcesoCompraController::class, 'guardar'], [AccesoRutaRolMiddleware::class, CsrfMiddleware::class]);
    $enrutador->obtener('/login', [AutenticacionController::class, 'formularioInicioSesion']);
    $enrutador->post('/login', [AutenticacionController::class, 'iniciarSesion'], [LimiteSolicitudesMiddleware::class, CsrfMiddleware::class]);
    $enrutador->obtener('/register', [AutenticacionController::class, 'formularioRegistro']);
    $enrutador->post('/register', [AutenticacionController::class, 'registrar'], [LimiteSolicitudesMiddleware::class, CsrfMiddleware::class]);
    $enrutador->obtener('/logout', [AutenticacionController::class, 'formularioCierreSesion'], [AutenticacionMiddleware::class]);
    $enrutador->post('/logout', [AutenticacionController::class, 'cerrarSesion'], [AutenticacionMiddleware::class, CsrfMiddleware::class]);
    $enrutador->obtener('/account', [CuentaController::class, 'indice'], [AutenticacionMiddleware::class]);
    $enrutador->obtener('/about', [PaginaController::class, 'nosotros']);
    $enrutador->obtener('/contact', [PaginaController::class, 'contacto']);
    $enrutador->post('/contact', [PaginaController::class, 'enviarContacto'], [CsrfMiddleware::class]);
};

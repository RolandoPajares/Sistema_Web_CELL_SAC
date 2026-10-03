<?php

declare(strict_types=1);

use App\Controladores\Panel\PanelRolController;
use App\Middleware\CsrfMiddleware;
use App\Middleware\RolMiddleware;
use App\Nucleo\Enrutamiento\Enrutador;

/**
 * Archivo de rutas del panel de roles.
 * Configura los endpoints protegidos por middlewares de rol y protección CSRF.
 */
return static function (Enrutador $enrutador): void {
    $protegida = [RolMiddleware::class];
    $protegidaPost = [RolMiddleware::class, CsrfMiddleware::class];

    // GESTIÓN DE TABLERO Y MÓDULOS DE ROL

    $enrutador->obtener('/panel', [PanelRolController::class, 'tablero'], $protegida);
    $enrutador->obtener('/panel/{module}', [PanelRolController::class, 'indice'], $protegida);
    $enrutador->post('/panel/{module}', [PanelRolController::class, 'guardar'], $protegidaPost);
    $enrutador->post('/panel/{module}/{id}', [PanelRolController::class, 'actualizar'], $protegidaPost);
    $enrutador->post('/panel/{module}/{id}/deactivate', [PanelRolController::class, 'desactivar'], $protegidaPost);
};
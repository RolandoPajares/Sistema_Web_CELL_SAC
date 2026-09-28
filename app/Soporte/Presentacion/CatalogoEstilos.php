<?php

declare(strict_types=1);

namespace App\Soporte\Presentacion;

use App\Soporte\Autorizacion\AccesoRol;

final class CatalogoEstilos
{
    /** @return array<int, string> */
    public static function para(string $ruta, string $plantilla, string $rol = '', string $modulo = ''): array
    {
        $ruta = self::normalizarRuta($ruta);
        $rol = AccesoRol::normalize($rol);
        $modulo = self::modulo($ruta, $modulo);

        if ($plantilla === 'interno') {
            $estilos = [
                'assets/css/modulos/comunes/panel.css',
                self::archivoRol($rol),
            ];

            if ($ruta === '/smart/assistant') {
                $estilos[] = 'assets/css/modulos/comercio-inteligente/asistente-ia-interno.css';
            } elseif ($modulo === 'perfil') {
                $estilos[] = 'assets/css/modulos/cuenta/perfil.css';
            } elseif (in_array($modulo, ['productos', 'admin-products'], true)) {
                $estilos[] = 'assets/css/modulos/productos/productos.css';
            } elseif (in_array($modulo, ['pedidos', 'pedidos-mayoristas', 'admin-orders'], true)) {
                $estilos[] = 'assets/css/modulos/pedidos/pedidos.css';
            } elseif (in_array($modulo, ['campanias', 'admin-campaigns'], true)) {
                $estilos[] = 'assets/css/modulos/marketing/campanias.css';
            }

            if (in_array($ruta, ['/admin', '/panel'], true)) {
                $estilos[] = 'assets/css/modulos/comunes/dashboard.css';
            }

            $estilos[] = 'assets/css/responsive/adaptable.css';

            return self::limpiar($estilos);
        }

        $estilos = [
            'assets/css/estructura/navbar.css',
            'assets/css/estructura/sitio.css',
            'assets/css/componentes/promociones.css',
            'assets/css/componentes/publicidad_dinamica.css',
        ];
        if ($rol !== '') {
            $estilos[] = self::archivoRol($rol);
        }

        if ($ruta === '/') {
            $estilos[] = 'assets/css/publico/catalogo.css';
            $estilos[] = 'assets/css/publico/inicio.css';
        } elseif ($ruta === '/catalog') {
            $estilos[] = 'assets/css/publico/catalogo.css';
        } elseif (preg_match('#^/products/[^/]+$#', $ruta) === 1) {
            $estilos[] = 'assets/css/publico/catalogo.css';
            $estilos[] = 'assets/css/publico/producto.css';
        } elseif (in_array($ruta, ['/login', '/register', '/logout'], true)) {
            $estilos[] = 'assets/css/publico/autenticacion.css';
        } elseif ($ruta === '/checkout') {
            $estilos[] = 'assets/css/publico/autenticacion.css';
        } elseif ($ruta === '/cart') {
            $estilos[] = 'assets/css/publico/catalogo.css';
            $estilos[] = 'assets/css/publico/carrito.css';
            $estilos[] = 'assets/css/modulos/comercio-inteligente/carrito-inteligente.css';
        } elseif ($ruta === '/smart/recommend') {
            $estilos[] = 'assets/css/modulos/comercio-inteligente/base.css';
            $estilos[] = 'assets/css/modulos/comercio-inteligente/recomendador.css';
        } elseif ($ruta === '/smart/compare') {
            $estilos[] = 'assets/css/modulos/comercio-inteligente/base.css';
            $estilos[] = 'assets/css/modulos/comercio-inteligente/comparador.css';
        } elseif ($ruta === '/smart/optimizer') {
            $estilos[] = 'assets/css/modulos/comercio-inteligente/base.css';
            $estilos[] = 'assets/css/modulos/comercio-inteligente/optimizador.css';
        } elseif ($ruta === '/smart/assistant') {
            $estilos[] = 'assets/css/modulos/comercio-inteligente/base.css';
            $estilos[] = 'assets/css/modulos/comercio-inteligente/asistente-ia.css';
        } elseif ($ruta === '/smart/ads') {
            $estilos[] = 'assets/css/modulos/marketing/publicidad-inteligente.css';
        } elseif ($ruta === '/mayorista') {
            $estilos[] = 'assets/css/publico/inicio.css';
            $estilos[] = 'assets/css/modulos/mayorista/portal-mayorista.css';
        } elseif (str_starts_with($ruta, '/panel') && str_starts_with($rol, 'cliente_')) {
            $estilos[] = 'assets/css/modulos/cuenta/cuenta.css';
        }

        $estilos[] = 'assets/css/responsive/adaptable.css';

        return self::limpiar($estilos);
    }

    public static function clasesCuerpo(string $ruta, string $rol = '', string $modulo = ''): string
    {
        $ruta = self::normalizarRuta($ruta);
        $clases = [];
        if ($rol !== '') {
            $clases[] = 'rol-' . str_replace('_', '-', AccesoRol::normalize($rol));
        }
        $modulo = self::modulo($ruta, $modulo);
        if ($modulo !== '') {
            $clases[] = 'modulo-' . preg_replace('/[^a-z0-9-]+/', '-', str_replace('_', '-', $modulo));
        }

        return implode(' ', array_filter($clases));
    }

    private static function modulo(string $ruta, string $modulo): string
    {
        if ($modulo !== '') {
            return $modulo;
        }
        if ($ruta === '/' || $ruta === '') {
            return 'inicio';
        }

        return match (true) {
            $ruta === '/admin' || $ruta === '/panel' => 'dashboard',
            $ruta === '/admin/products' || str_starts_with($ruta, '/admin/products/') => 'admin-products',
            $ruta === '/admin/orders' => 'admin-orders',
            $ruta === '/admin/campaigns' => 'admin-campaigns',
            $ruta === '/admin/users' => 'usuarios',
            $ruta === '/smart/recommend' => 'recomendador',
            $ruta === '/smart/compare' => 'comparador',
            $ruta === '/smart/optimizer' => 'optimizador',
            $ruta === '/smart/assistant' => 'asistente-ia',
            $ruta === '/smart/ads' => 'publicidad-inteligente',
            $ruta === '/mayorista' => 'portal-mayorista',
            preg_match('#^/panel/([^/]+)$#', $ruta, $coincidencia) === 1 => $coincidencia[1],
            default => trim($ruta, '/') !== '' ? str_replace('/', '-', trim($ruta, '/')) : 'inicio',
        };
    }

    private static function normalizarRuta(string $ruta): string
    {
        $ruta = (string) (parse_url($ruta, PHP_URL_PATH) ?: '/');
        $ruta = '/' . ltrim(str_replace('\\', '/', $ruta), '/');
        $rutaScript = '/' . ltrim(str_replace('\\', '/', (string) ($_SERVER['SCRIPT_NAME'] ?? '')), '/');
        $directorio = rtrim(str_replace('\\', '/', dirname($rutaScript)), '/');

        if ($directorio !== '' && $directorio !== '/' && str_starts_with($ruta, $directorio . '/')) {
            $ruta = substr($ruta, strlen($directorio));
        }

        return $ruta === '' ? '/' : '/' . trim($ruta, '/');
    }

    private static function archivoRol(string $rol): string
    {
        $roles = [
            'administrador',
            'compras_logistica',
            'ventas_mayoristas',
            'ventas_minoristas',
            'marketing',
            'cliente_minorista',
            'cliente_mayorista',
        ];
        if (!in_array($rol, $roles, true)) {
            return '';
        }

        $nombre = str_replace('_', '-', $rol);
        $grupo = str_starts_with($rol, 'cliente_') ? 'externos' : 'internos';

        return "assets/css/roles/{$grupo}/{$nombre}.css";
    }

    /** @param array<int, string> $estilos @return array<int, string> */
    private static function limpiar(array $estilos): array
    {
        return array_values(array_unique(array_filter($estilos)));
    }
}

<?php

declare(strict_types=1);

namespace App\Soporte\Autorizacion;

final class AccesoRol
{
    /** @var array<string, array<int, string>> */
    private const ACCESS = [
        'cliente_minorista' => [
            'dashboard', 'perfil', 'pedidos', 'historial', 'direcciones', 'smartcommerce',
            'recomendador', 'comparador', 'asistente',
        ],
        'cliente_mayorista' => [
            'dashboard', 'catalogo-b2b', 'perfil', 'cotizaciones', 'pedidos-mayoristas', 'historial',
            'smartcommerce', 'recomendador', 'comparador', 'asistente', 'optimizador',
        ],
        'administrador' => ['*'],
        'compras_logistica' => [
            'dashboard', 'inventario', 'productos', 'proveedores', 'compras', 'movimientos-stock',
            'preparacion-pedidos', 'logistica-mayorista', 'alertas-stock',
            'recomendador', 'comparador', 'asistente',
        ],
        'ventas_mayoristas' => [
            'dashboard', 'productos', 'clientes-mayoristas', 'cotizaciones', 'pedidos-mayoristas',
            'historial-cliente', 'seguimiento-comercial', 'optimizador',
            'recomendador', 'comparador', 'asistente',
        ],
        'ventas_minoristas' => [
            'dashboard', 'productos', 'inventario', 'clientes', 'ventas', 'pedidos', 'garantias',
            'devoluciones', 'reclamaciones', 'recomendador', 'comparador', 'asistente',
        ],
        'marketing' => [
            'dashboard', 'publicidad', 'campanias', 'catalogo-digital', 'destacados', 'promociones',
            'contenido', 'analitica', 'consultas-digitales', 'segmentacion', 'smartcommerce-analytics',
            'recomendador', 'comparador', 'asistente',
        ],
    ];

    /** @var array<string, array<int, array{slug:string,label:string,icon:string}>> */
    private const NAVIGATION = [
        'cliente_minorista' => [
            ['slug' => 'dashboard', 'label' => 'Mi cuenta', 'icon' => 'bi-person-circle'],
            ['slug' => 'catalogo', 'label' => 'Catálogo', 'icon' => 'bi-grid'],
            ['slug' => 'pedidos', 'label' => 'Mis pedidos', 'icon' => 'bi-box-seam'],
            ['slug' => 'historial', 'label' => 'Historial', 'icon' => 'bi-clock-history'],
            ['slug' => 'perfil', 'label' => 'Mis datos', 'icon' => 'bi-person'],
            ['slug' => 'direcciones', 'label' => 'Direcciones', 'icon' => 'bi-geo-alt'],
            ['slug' => 'smartcommerce', 'label' => 'MD SmartCommerce', 'icon' => 'bi-bag-check'],
            ['slug' => 'recomendador', 'label' => 'Recomendador', 'icon' => 'bi-lightbulb'],
            ['slug' => 'comparador', 'label' => 'Comparador', 'icon' => 'bi-columns-gap'],
            ['slug' => 'asistente', 'label' => 'MD Assistant', 'icon' => 'bi-robot'],
        ],
        'cliente_mayorista' => [
            ['slug' => 'dashboard', 'label' => 'Portal mayorista', 'icon' => 'bi-buildings'],
            ['slug' => 'catalogo-b2b', 'label' => 'Catálogo B2B', 'icon' => 'bi-grid'],
            ['slug' => 'cotizaciones', 'label' => 'Mis cotizaciones', 'icon' => 'bi-file-earmark-text'],
            ['slug' => 'pedidos-mayoristas', 'label' => 'Mis pedidos', 'icon' => 'bi-truck'],
            ['slug' => 'historial', 'label' => 'Historial', 'icon' => 'bi-clock-history'],
            ['slug' => 'perfil', 'label' => 'Área mayorista', 'icon' => 'bi-building-gear'],
            ['slug' => 'smartcommerce', 'label' => 'MD SmartCommerce B2B', 'icon' => 'bi-bag-check'],
            ['slug' => 'recomendador', 'label' => 'Recomendador B2B', 'icon' => 'bi-lightbulb'],
            ['slug' => 'comparador', 'label' => 'Comparador B2B', 'icon' => 'bi-columns-gap'],
            ['slug' => 'asistente', 'label' => 'MD Assistant B2B', 'icon' => 'bi-robot'],
            ['slug' => 'optimizador', 'label' => 'Optimizador mayorista', 'icon' => 'bi-graph-up-arrow'],
        ],
        'administrador' => [
            ['slug' => 'dashboard', 'label' => 'Dashboard ejecutivo', 'icon' => 'bi-speedometer2'],
            ['slug' => 'productos', 'label' => 'Productos', 'icon' => 'bi-box-seam'],
            ['slug' => 'categorias', 'label' => 'Categorías', 'icon' => 'bi-diagram-3'],
            ['slug' => 'inventario', 'label' => 'Inventario', 'icon' => 'bi-archive'],
            ['slug' => 'pedidos', 'label' => 'Pedidos', 'icon' => 'bi-receipt'],
            ['slug' => 'clientes', 'label' => 'Clientes', 'icon' => 'bi-people'],
            ['slug' => 'proveedores', 'label' => 'Proveedores', 'icon' => 'bi-truck'],
            ['slug' => 'publicidad', 'label' => 'Publicidad', 'icon' => 'bi-megaphone'],
            ['slug' => 'usuarios', 'label' => 'Usuarios', 'icon' => 'bi-person-gear'],
            ['slug' => 'reportes', 'label' => 'Reportes', 'icon' => 'bi-bar-chart'],
            ['slug' => 'auditoria', 'label' => 'Auditoría', 'icon' => 'bi-shield-check'],
        ],
        'compras_logistica' => [
            ['slug' => 'dashboard', 'label' => 'Dashboard de operaciones', 'icon' => 'bi-speedometer2'],
            ['slug' => 'inventario', 'label' => 'Inventario', 'icon' => 'bi-archive'],
            ['slug' => 'productos', 'label' => 'Productos', 'icon' => 'bi-box-seam'],
            ['slug' => 'proveedores', 'label' => 'Proveedores', 'icon' => 'bi-people'],
            ['slug' => 'compras', 'label' => 'Compras', 'icon' => 'bi-cart-check'],
            ['slug' => 'movimientos-stock', 'label' => 'Movimientos de stock', 'icon' => 'bi-list-check'],
            ['slug' => 'preparacion-pedidos', 'label' => 'Pedidos por preparar', 'icon' => 'bi-clipboard-check'],
            ['slug' => 'logistica-mayorista', 'label' => 'Logística mayorista', 'icon' => 'bi-truck'],
            ['slug' => 'alertas-stock', 'label' => 'Alertas de reposición', 'icon' => 'bi-bell'],
        ],
        'ventas_mayoristas' => [
            ['slug' => 'dashboard', 'label' => 'Dashboard comercial B2B', 'icon' => 'bi-speedometer2'],
            ['slug' => 'productos', 'label' => 'Productos', 'icon' => 'bi-box-seam'],
            ['slug' => 'clientes-mayoristas', 'label' => 'Clientes mayoristas', 'icon' => 'bi-buildings'],
            ['slug' => 'cotizaciones', 'label' => 'Cotizaciones', 'icon' => 'bi-file-earmark-text'],
            ['slug' => 'pedidos-mayoristas', 'label' => 'Pedidos mayoristas', 'icon' => 'bi-truck'],
            ['slug' => 'historial-cliente', 'label' => 'Historial del cliente', 'icon' => 'bi-clock-history'],
            ['slug' => 'asistente', 'label' => 'MD Assistant B2B', 'icon' => 'bi-robot'],
            ['slug' => 'optimizador', 'label' => 'Optimizador mayorista', 'icon' => 'bi-graph-up-arrow'],
            ['slug' => 'seguimiento-comercial', 'label' => 'Seguimiento comercial', 'icon' => 'bi-kanban'],
        ],
        'ventas_minoristas' => [
            ['slug' => 'dashboard', 'label' => 'Punto de venta / Dashboard B2C', 'icon' => 'bi-speedometer2'],
            ['slug' => 'productos', 'label' => 'Productos', 'icon' => 'bi-box-seam'],
            ['slug' => 'inventario', 'label' => 'Stock', 'icon' => 'bi-archive'],
            ['slug' => 'clientes', 'label' => 'Clientes', 'icon' => 'bi-people'],
            ['slug' => 'ventas', 'label' => 'Nueva venta', 'icon' => 'bi-cash-coin'],
            ['slug' => 'pedidos', 'label' => 'Pedidos', 'icon' => 'bi-receipt'],
            ['slug' => 'garantias', 'label' => 'Garantías', 'icon' => 'bi-shield-check'],
            ['slug' => 'devoluciones', 'label' => 'Devoluciones', 'icon' => 'bi-arrow-counterclockwise'],
            ['slug' => 'reclamaciones', 'label' => 'Reclamaciones', 'icon' => 'bi-exclamation-triangle'],
        ],
        'marketing' => [
            ['slug' => 'dashboard', 'label' => 'Dashboard de marketing', 'icon' => 'bi-speedometer2'],
            ['slug' => 'publicidad', 'label' => 'Publicidad', 'icon' => 'bi-megaphone'],
            ['slug' => 'campanias', 'label' => 'Campañas', 'icon' => 'bi-bullseye'],
            ['slug' => 'catalogo-digital', 'label' => 'Catálogo digital', 'icon' => 'bi-cart'],
            ['slug' => 'destacados', 'label' => 'Productos destacados', 'icon' => 'bi-star'],
            ['slug' => 'promociones', 'label' => 'Promociones', 'icon' => 'bi-tag'],
            ['slug' => 'contenido', 'label' => 'Contenido web', 'icon' => 'bi-file-richtext'],
            ['slug' => 'analitica', 'label' => 'Analítica de campañas', 'icon' => 'bi-bar-chart'],
            ['slug' => 'consultas-digitales', 'label' => 'Consultas digitales', 'icon' => 'bi-people'],
            ['slug' => 'segmentacion', 'label' => 'Segmentación', 'icon' => 'bi-pie-chart'],
            ['slug' => 'smartcommerce-analytics', 'label' => 'SmartCommerce Analytics', 'icon' => 'bi-graph-up'],
        ],
    ];

    public static function normalize(string $rol): string
    {
        return $rol;
    }

    public static function can(string $rol, string $modulo): bool
    {
        $permitidos = self::ACCESS[self::normalize($rol)] ?? [];
        return in_array('*', $permitidos, true) || in_array($modulo, $permitidos, true);
    }

    /** @return array<int, array{slug:string,label:string,icon:string}> */
    public static function navigation(string $rol): array
    {
        return self::NAVIGATION[self::normalize($rol)] ?? [];
    }

    public static function label(string $rol): string
    {
        return match (self::normalize($rol)) {
            'administrador' => 'Administrador / Gerente General',
            'compras_logistica' => 'Compras y Logística',
            'ventas_mayoristas' => 'Ejecutivo de Ventas Mayoristas',
            'ventas_minoristas' => 'Vendedor Minorista / Atención al Cliente',
            'marketing' => 'Marketing Digital y Canales Online',
            'cliente_mayorista' => 'Cliente mayorista',
            default => 'Cliente minorista',
        };
    }

    public static function destino(string $rol, string $modulo): string
    {
        $rol = self::normalize($rol);
        if ($modulo === 'dashboard') {
            return $rol === 'administrador' ? 'admin' : 'panel';
        }
        if ($modulo === 'catalogo') {
            return 'catalog';
        }
        if ($modulo === 'catalogo-b2b') {
            return 'mayorista';
        }
        if (in_array($modulo, ['smartcommerce', 'recomendador', 'comparador', 'asistente', 'optimizador'], true)) {
            return match ($modulo) {
                'smartcommerce', 'recomendador' => 'smart/recommend',
                'comparador' => 'smart/compare',
                'optimizador' => 'smart/optimizer',
                default => 'smart/assistant',
            };
        }
        if ($rol === 'administrador') {
            return match ($modulo) {
                'productos' => 'admin/products',
                'categorias' => 'admin/categories',
                'inventario' => 'admin/inventory',
                'clientes' => 'admin/customers',
                'proveedores' => 'admin/suppliers',
                'publicidad', 'campanias' => 'admin/campaigns',
                'pedidos' => 'admin/orders',
                'usuarios' => 'admin/users',
                'reportes' => 'admin/reports',
                'auditoria' => 'admin/audit',
                default => 'admin',
            };
        }
        return 'panel/' . $modulo;
    }
}

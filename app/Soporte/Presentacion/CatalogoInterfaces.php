<?php

declare(strict_types=1);

namespace App\Soporte\Presentacion;

final class CatalogoInterfaces
{
    /**
     * @param array<string, int|float> $resumen
     * @return array<string, mixed>
     */
    public static function tablero(string $rol, array $resumen): array
    {
        $ventas = 'S/ ' . number_format((float) ($resumen['ventas'] ?? 124580), 2, '.', ',');
        $perfiles = [
            'administrador' => ['Dashboard ejecutivo', 'Resumen general de la operación de MD Technology Cell', [
                ['Ventas de hoy', 'S/ 8,450.00', 'bi-bar-chart-line', 'azul'],
                ['Pedidos', (string) ($resumen['pedidos'] ?? 28), 'bi-cart-check', 'verde'],
                ['Stock bajo', (string) ($resumen['stock_bajo'] ?? 14), 'bi-box-seam', 'ambar'],
                ['Clientes nuevos', '12', 'bi-people', 'violeta'],
                ['Cotizaciones activas', '8', 'bi-file-earmark-text', 'celeste'],
            ], 'Ventas de los últimos 30 días', 'Ventas por categoría'],
            'compras_logistica' => ['Dashboard de operaciones', 'Gestiona compras, proveedores, recepciones e inventario de forma centralizada.', [
                ['Órdenes activas', '24', 'bi-file-earmark-check', 'azul'], ['Recepciones pendientes', '12', 'bi-truck', 'verde'],
                ['Productos por reponer', '38', 'bi-box-seam', 'ambar'], ['Proveedores activos', '28', 'bi-people', 'violeta'],
                ['Costo estimado', 'S/ 124,580', 'bi-cash-coin', 'celeste'],
            ], 'Tendencia de compras', 'Desempeño de proveedores'],
            'ventas_mayoristas' => ['Dashboard comercial B2B', 'Gestiona oportunidades, cotizaciones y pedidos de clientes empresariales.', [
                ['Ventas del mes', 'S/ 124,580', 'bi-cash-coin', 'verde'], ['Cotizaciones activas', '28', 'bi-file-earmark-text', 'azul'],
                ['Clientes activos', '56', 'bi-buildings', 'violeta'], ['Pedidos B2B', '24', 'bi-box-seam', 'ambar'],
                ['Conversión comercial', '38%', 'bi-bullseye', 'celeste'],
            ], 'Tendencia de ventas B2B', 'Resumen de oportunidades'],
            'ventas_minoristas' => ['Punto de venta / Dashboard B2C', 'Atiende a tus clientes, registra ventas y brinda el mejor servicio.', [
                ['Ventas del día', 'S/ 8,450', 'bi-cash-coin', 'verde'], ['Ventas del mes', 'S/ 124,580', 'bi-bar-chart', 'azul'],
                ['Clientes atendidos', '56', 'bi-people', 'violeta'], ['Garantías activas', '28', 'bi-shield-check', 'ambar'],
                ['Reclamos abiertos', '6', 'bi-exclamation-triangle', 'rojo'],
            ], 'Ventas diarias', 'Productos más vendidos'],
            'marketing' => ['Dashboard de marketing', 'Analiza el rendimiento de tus campañas, genera más leads y potencia tu marca.', [
                ['Campañas activas', '12', 'bi-megaphone', 'azul'], ['Alcance total', '256,480', 'bi-people', 'verde'],
                ['Leads generados', '1,248', 'bi-person-plus', 'violeta'], ['ROAS', '4.2x', 'bi-coin', 'ambar'],
                ['Engagement', '8.6%', 'bi-heart', 'rojo'],
            ], 'Rendimiento de campañas', 'Distribución por canales'],
            'cliente_mayorista' => ['Portal mayorista B2B', 'Cotizaciones, compras por volumen y atención empresarial en un solo lugar.', [
                ['Cotizaciones', '8', 'bi-file-earmark-text', 'azul'], ['Pedidos activos', '5', 'bi-truck', 'verde'],
                ['Compras acumuladas', 'S/ 85,420', 'bi-cash-coin', 'violeta'], ['Productos recurrentes', '18', 'bi-box-seam', 'ambar'],
            ], 'Evolución de compras', 'Estado de pedidos'],
            'cliente_minorista' => ['Mi cuenta', 'Consulta tus pedidos, favoritos y datos personales.', [
                ['Pedidos realizados', '12', 'bi-box-seam', 'azul'], ['Total gastado', 'S/ 3,289', 'bi-cash-coin', 'verde'],
                ['Productos diferentes', '8', 'bi-grid', 'violeta'], ['Favoritos', '6', 'bi-heart', 'rojo'],
            ], 'Historial reciente', 'Recomendado para ti'],
        ];

        $perfil = $perfiles[$rol] ?? $perfiles['cliente_minorista'];
        if ($rol === 'administrador' && ($resumen['ventas'] ?? 0) > 0) {
            $perfil[2][0][1] = $ventas;
        }

        return [
            'titulo' => $perfil[0], 'descripcion' => $perfil[1], 'metricas' => $perfil[2],
            'grafico_principal' => $perfil[3], 'grafico_secundario' => $perfil[4],
        ];
    }

    /** @return array<string, mixed> */
    public static function modulo(string $modulo, string $rol): array
    {
        $base = self::catalogo()[$modulo] ?? self::generica($modulo);
        $base['clave'] = $modulo;
        $base['rol'] = $rol;

        return $base;
    }

    /** @return array<string, array<string, mixed>> */
    private static function catalogo(): array
    {
        return [
            'productos' => self::ficha('Gestión de productos', 'Administra tu catálogo, precios, stock y disponibilidad.', 'bi-box-seam', ['Total de productos' => '246', 'Publicados' => '198', 'Borradores' => '28', 'Stock bajo' => '14'], 'tabla', ['SKU', 'Producto', 'Categoría', 'Precio', 'Stock', 'Estado']),
            'categorias' => self::ficha('Gestión de categorías', 'Organiza y administra las categorías de tus productos.', 'bi-diagram-3', ['Total categorías' => '7', 'Categorías activas' => '6', 'Subcategorías' => '18', 'Productos asignados' => '124'], 'arbol', ['Nombre', 'Slug', 'Productos', 'Estado', 'Creación']),
            'inventario' => self::ficha('Control de inventario', 'Gestiona el stock, movimientos y alertas de reposición.', 'bi-archive', ['Stock total' => '1,248', 'Productos críticos' => '18', 'Ingresos recientes' => '324', 'Valoración' => 'S/ 284,320'], 'inventario', ['SKU', 'Producto', 'Categoría', 'Ubicación', 'Stock actual', 'Estado']),
            'compras' => self::ficha('Gestión de compras', 'Administra las órdenes de compra a proveedores.', 'bi-cart-check', ['Compras del mes' => 'S/ 48,750', 'Órdenes abiertas' => '8', 'Recepciones pendientes' => '5', 'Gasto acumulado' => 'S/ 326,410'], 'seguimiento', ['Orden', 'Proveedor', 'Fecha', 'Productos', 'Total', 'Estado']),
            'proveedores' => self::ficha('Proveedores', 'Gestiona proveedores, contactos y condiciones comerciales.', 'bi-truck', ['Proveedores activos' => '28', 'Órdenes abiertas' => '16', 'Entrega promedio' => '5.2 días', 'Evaluación media' => '4.4 / 5'], 'detalle', ['Empresa', 'RUC', 'Contacto', 'Ciudad', 'Pedidos', 'Calificación']),
            'pedidos' => self::ficha('Pedidos', 'Gestiona y da seguimiento a todos los pedidos.', 'bi-receipt', ['Pedidos nuevos' => '24', 'En proceso' => '18', 'Enviados' => '32', 'Entregados' => '156'], 'detalle', ['Pedido', 'Cliente', 'Tipo', 'Fecha', 'Total', 'Estado']),
            'preparacion-pedidos' => self::ficha('Preparación de pedidos', 'Organiza picking, packing y despacho.', 'bi-box2-heart', ['Por preparar' => '18', 'En picking' => '12', 'Empacados' => '15', 'Despachados hoy' => '24', 'Retrasos' => '5'], 'seguimiento', ['Pedido', 'Cliente', 'Ítems', 'Prioridad', 'Operador', 'Estado']),
            'alertas-stock' => self::ficha('Alertas de stock', 'Prioriza productos críticos y reposiciones.', 'bi-exclamation-triangle', ['Críticos' => '18', 'Agotados' => '4', 'Por reponer' => '38', 'Órdenes sugeridas' => '12'], 'inventario', ['SKU', 'Producto', 'Stock actual', 'Stock mínimo', 'Estado', 'Acción']),
            'clientes' => self::ficha('Gestión de clientes', 'Administra información, historial y atención de clientes.', 'bi-people', ['Total de clientes' => '1,248', 'Nuevos este mes' => '86', 'Frecuentes' => '320', 'Tickets atendidos' => '142', 'Ventas generadas' => 'S/ 124,580'], 'detalle', ['Cliente', 'Correo', 'Teléfono', 'Ciudad', 'Compras', 'Estado']),
            'clientes-mayoristas' => self::ficha('Clientes mayoristas', 'Administra tu cartera B2B y fortalece la relación con empresas.', 'bi-buildings', ['Empresas activas' => '124', 'Nuevos leads' => '18', 'Cuentas en seguimiento' => '42', 'Facturación acumulada' => 'S/ 782,450'], 'detalle', ['Empresa', 'RUC', 'Contacto', 'Ciudad', 'Ejecutivo', 'Facturación']),
            'ventas' => self::ficha('Gestión de ventas', 'Registra, consulta y gestiona tus ventas de mostrador.', 'bi-cash-coin', ['Ventas hoy' => 'S/ 8,450', 'Ventas del mes' => 'S/ 124,580', 'Ticket promedio' => 'S/ 320', 'Productos vendidos' => '286', 'Devoluciones' => '12'], 'venta', ['Venta', 'Cliente', 'Productos', 'Total', 'Pago', 'Estado']),
            'cotizaciones' => self::ficha('Cotizaciones', 'Crea propuestas y da seguimiento a cada negociación.', 'bi-file-earmark-text', ['Cotizaciones activas' => '28', 'Aprobadas' => '16', 'Vencidas' => '6', 'Convertidas a venta' => '24'], 'cotizacion', ['Cotización', 'Cliente / Empresa', 'Asesor', 'Productos', 'Monto', 'Estado']),
            'pedidos-mayoristas' => self::ficha('Pedidos mayoristas', 'Gestiona entregas y condiciones de pedidos B2B.', 'bi-truck', ['Pedidos activos' => '24', 'En preparación' => '12', 'En tránsito' => '8', 'Entregados' => '56'], 'seguimiento', ['Pedido', 'Empresa', 'Productos', 'Total', 'Entrega', 'Estado']),
            'seguimiento-comercial' => self::ficha('Seguimiento comercial', 'Visualiza tu embudo de ventas y cada oportunidad.', 'bi-kanban', ['Oportunidades abiertas' => '24', 'Negociaciones avanzadas' => '12', 'Propuestas enviadas' => '8', 'Cierres ganados' => '6', 'Cierres perdidos' => '4'], 'kanban', []),
            'garantias' => self::ficha('Gestión de garantías', 'Administra solicitudes y brinda seguimiento a cada caso.', 'bi-shield-check', ['Garantías activas' => '24', 'En revisión' => '12', 'Resueltas' => '38', 'Rechazadas' => '6', 'Tiempo promedio' => '3.2 días'], 'detalle', ['Código', 'Cliente', 'Producto', 'Solicitud', 'Motivo', 'Estado']),
            'devoluciones' => self::ficha('Gestión de devoluciones', 'Controla solicitudes, inspecciones y reembolsos.', 'bi-arrow-counterclockwise', ['Solicitudes' => '18', 'En evaluación' => '7', 'Aprobadas' => '24', 'Reembolsos' => 'S/ 4,280'], 'detalle', ['Código', 'Cliente', 'Producto', 'Motivo', 'Fecha', 'Estado']),
            'reclamaciones' => self::ficha('Gestión de reclamos', 'Administra y da seguimiento centralizado a los reclamos.', 'bi-chat-left-text', ['Reclamos nuevos' => '12', 'Reclamos abiertos' => '28', 'En proceso' => '16', 'Resueltos' => '44', 'Respuesta' => '4.8 horas'], 'detalle', ['Ticket', 'Cliente', 'Motivo', 'Canal', 'Prioridad', 'Estado']),
            'publicidad' => self::ficha('MD Ads inteligente', 'Crea, gestiona y optimiza campañas publicitarias.', 'bi-megaphone', ['Campañas activas' => '12', 'Presupuesto invertido' => 'S/ 8,450', 'Clics' => '12,580', 'Conversiones' => '1,240', 'CPL' => 'S/ 6.81'], 'campanias', ['Campaña', 'Canal', 'Objetivo', 'Presupuesto', 'Estado', 'Rendimiento']),
            'campanias' => self::ficha('MD Ads - Campañas', 'Gestiona campañas de todos los canales desde un solo lugar.', 'bi-badge-ad', ['Campañas activas' => '12', 'Presupuesto invertido' => 'S/ 8,450', 'Clics' => '12,580', 'Conversiones' => '1,240'], 'campanias', ['Campaña', 'Canal', 'Objetivo', 'Presupuesto', 'Estado', 'Inicio']),
            'contenido' => self::ficha('Contenido digital', 'Crea, gestiona y publica contenido para todos tus canales.', 'bi-file-richtext', ['Piezas publicadas' => '48', 'Programadas' => '16', 'Borradores' => '8', 'Engagement promedio' => '4.8%', 'Campañas con contenido' => '6'], 'contenido', ['Título', 'Tipo', 'Estado', 'Publicación', 'Canal', 'Acciones']),
            'promociones' => self::ficha('Promociones', 'Planifica ofertas, cupones y beneficios comerciales.', 'bi-percent', ['Activas' => '9', 'Programadas' => '6', 'Cupones usados' => '328', 'Ventas atribuidas' => 'S/ 32,480'], 'tabla', ['Promoción', 'Canal', 'Descuento', 'Vigencia', 'Uso', 'Estado']),
            'destacados' => self::ficha('Productos destacados', 'Organiza vitrinas y productos de alto rendimiento.', 'bi-star', ['Destacados' => '18', 'Más vendidos' => '12', 'Nuevos' => '8', 'Sin stock' => '3'], 'tarjetas', ['Producto', 'Categoría', 'Precio', 'Conversión', 'Stock', 'Estado']),
            'segmentacion' => self::ficha('Segmentación de audiencias', 'Construye audiencias para campañas más efectivas.', 'bi-bullseye', ['Audiencias' => '24', 'Usuarios segmentados' => '125,430', 'Similares' => '8', 'Conversiones' => '4,862'], 'analitica', ['Segmento', 'Usuarios', 'Canal', 'Afinidad', 'Conversión', 'Estado']),
            'leads' => self::ficha('Gestión de leads', 'Convierte oportunidades digitales en clientes.', 'bi-person-plus', ['Leads nuevos' => '186', 'Contactados' => '92', 'Calificados' => '48', 'Convertidos' => '24'], 'kanban', []),
            'analitica' => self::ficha('Analítica IA', 'Convierte datos en oportunidades, predicciones y recomendaciones.', 'bi-bar-chart', ['Tráfico analizado' => '125,430', 'Conversiones' => '4,862', 'Revenue generado' => 'S/ 124,580', 'Crecimiento proyectado' => '+42%', 'Precisión del modelo' => '92%'], 'analitica', ['Canal', 'Visitas', 'Conversiones', 'Revenue', 'Variación']),
            'reportes' => self::ficha('Reportes gerenciales', 'Indicadores consolidados para la toma de decisiones.', 'bi-graph-up-arrow', ['Ventas' => 'S/ 284,320', 'Margen' => '28.4%', 'Ticket promedio' => 'S/ 325', 'Crecimiento' => '+18%'], 'analitica', ['Reporte', 'Periodo', 'Responsable', 'Actualización', 'Estado']),
            'auditoria' => self::ficha('Auditoría e historial', 'Traza las acciones críticas realizadas en el sistema.', 'bi-shield-check', ['Eventos hoy' => '86', 'Accesos' => '42', 'Cambios críticos' => '3', 'Alertas' => '2'], 'tabla', ['Fecha', 'Usuario', 'Módulo', 'Acción', 'IP', 'Resultado']),
            'usuarios' => self::ficha('Usuarios y roles', 'Administra cuentas internas y permisos por experiencia.', 'bi-person-gear', ['Usuarios activos' => '28', 'Roles' => '7', 'Sesiones hoy' => '42', 'Bloqueados' => '2'], 'detalle', ['Usuario', 'Correo', 'Rol', 'Último acceso', 'Estado', 'Acciones']),
            'perfil' => self::ficha('Mi perfil', 'Actualiza tus datos personales y preferencias.', 'bi-person-circle', ['Datos completos' => '90%', 'Direcciones' => '2', 'Métodos de pago' => '3', 'Notificaciones' => '5'], 'perfil', []),
            'historial' => self::ficha('Historial de compras', 'Consulta pedidos, comprobantes y vuelve a comprar.', 'bi-clock-history', ['Compras realizadas' => '12', 'Total gastado' => 'S/ 3,289', 'Productos diferentes' => '8', 'Recurrentes' => '3'], 'historial', ['Pedido', 'Fecha', 'Productos', 'Total', 'Estado', 'Comprobante']),
            'direcciones' => self::ficha('Mis direcciones', 'Gestiona tus direcciones de entrega favoritas.', 'bi-geo-alt', ['Direcciones' => '2', 'Principal' => 'Bagua', 'Entregas' => '12', 'Cobertura' => 'Disponible'], 'tarjetas', []),
            'favoritos' => self::ficha('Mis favoritos', 'Guarda y compara los productos que te interesan.', 'bi-heart', ['Favoritos' => '6', 'Con descuento' => '3', 'Disponibles' => '5', 'Alertas de precio' => '2'], 'tarjetas', []),
            'catalogo-b2b' => self::ficha('Catálogo B2B', 'Precios por volumen y stock para tu empresa.', 'bi-grid', ['Productos B2B' => '246', 'Marcas' => '28', 'Ofertas por volumen' => '18', 'Stock disponible' => '1,248'], 'tarjetas', ['Producto', '1–9 und.', '10–49 und.', '50+ und.', 'Stock']),
            'recepciones' => self::ficha('Recepciones', 'Registra y controla la llegada de órdenes al almacén.', 'bi-truck-flatbed', ['Programadas' => '12', 'En tránsito' => '8', 'Recibidas hoy' => '6', 'Con incidencias' => '2'], 'seguimiento', ['Recepción', 'Proveedor', 'Orden', 'Fecha estimada', 'Productos', 'Estado']),
            'almacenes' => self::ficha('Almacenes', 'Organiza ubicaciones, capacidad y disponibilidad.', 'bi-house-door', ['Almacenes' => '4', 'Ubicaciones' => '186', 'Ocupación' => '72%', 'Movimientos hoy' => '48'], 'inventario', ['Almacén', 'Ubicación', 'Categoría', 'Capacidad', 'Ocupación', 'Estado']),
            'configuracion' => self::ficha('Configuración', 'Personaliza las opciones de tu experiencia y operación.', 'bi-gear', ['Preferencias' => '12', 'Notificaciones' => '8', 'Integraciones' => '4', 'Seguridad' => 'Activa'], 'perfil', []),
            'marketing-b2b' => self::ficha('Marketing B2B', 'Campañas y materiales para clientes empresariales.', 'bi-bullseye', ['Campañas B2B' => '8', 'Empresas alcanzadas' => '2,480', 'Leads' => '186', 'Conversión' => '12.8%'], 'campanias', ['Campaña', 'Segmento', 'Canal', 'Alcance', 'Leads', 'Estado']),
            'audiencias' => self::ficha('Audiencias', 'Crea segmentos para campañas más relevantes.', 'bi-people', ['Audiencias' => '24', 'Usuarios' => '125,430', 'Similares' => '8', 'Activas' => '18'], 'analitica', ['Audiencia', 'Usuarios', 'Origen', 'Afinidad', 'Conversión', 'Estado']),
            'redes-sociales' => self::ficha('Redes sociales', 'Programa y analiza publicaciones de tus canales.', 'bi-share', ['Publicaciones' => '48', 'Programadas' => '16', 'Interacciones' => '8,640', 'Alcance' => '92,480'], 'contenido', ['Publicación', 'Red', 'Fecha', 'Alcance', 'Interacción', 'Estado']),
            'automatizaciones' => self::ficha('Automatizaciones', 'Conecta acciones de marketing y seguimiento.', 'bi-gear-wide-connected', ['Flujos activos' => '12', 'Ejecuciones' => '8,420', 'Conversiones' => '640', 'Ahorro estimado' => '42 h'], 'seguimiento', ['Automatización', 'Disparador', 'Audiencia', 'Ejecuciones', 'Conversión', 'Estado']),
            'integraciones' => self::ficha('Integraciones', 'Administra los canales conectados al ecosistema digital.', 'bi-plugin', ['Conectadas' => '6', 'Disponibles' => '18', 'Sincronizaciones' => '1,248', 'Alertas' => '1'], 'tarjetas', []),
        ];
    }

    /**
     * @param array<string, string> $metricas
     * @param array<int, string> $columnas
     * @return array<string, mixed>
     */
    private static function ficha(string $titulo, string $descripcion, string $icono, array $metricas, string $tipo, array $columnas): array
    {
        $colores = ['azul', 'verde', 'ambar', 'violeta', 'rojo'];
        $iconos = [$icono, 'bi-check-circle', 'bi-clock', 'bi-bar-chart-line', 'bi-bullseye'];
        $tarjetas = [];
        $indice = 0;
        foreach ($metricas as $etiqueta => $valor) {
            $tarjetas[] = ['etiqueta' => $etiqueta, 'valor' => $valor, 'color' => $colores[$indice % 5], 'icono' => $iconos[$indice % 5]];
            $indice++;
        }

        return ['titulo' => $titulo, 'descripcion' => $descripcion, 'icono' => $icono, 'metricas' => $tarjetas, 'tipo' => $tipo, 'columnas' => $columnas];
    }

    /** @return array<string, mixed> */
    private static function generica(string $modulo): array
    {
        $titulo = ucfirst(str_replace('-', ' ', $modulo));
        return self::ficha($titulo, 'Gestiona la información y las acciones de este módulo.', 'bi-window-stack', ['Registros' => '24', 'Activos' => '18', 'Pendientes' => '6', 'Avance' => '75%'], 'tabla', ['Código', 'Nombre', 'Fecha', 'Responsable', 'Estado']);
    }
}

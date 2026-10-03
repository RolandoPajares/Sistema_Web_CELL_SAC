<?php

declare(strict_types=1);

namespace App\Soporte\Presentacion;

/** Prepara los datos simulados que usan los paneles de demostración. */
final class DatosDemostracionPanel
{
    /**
     * @return array<int, array{codigo:string,fecha:string,producto:string,tipo_producto:string,total:string,estado:string,texto_accion:string}>
     */
    public static function pedidosCuenta(): array
    {
        return [
            [
                'codigo' => 'MD-2024-000158',
                'fecha' => '15 ene. 2024, 10:24 a. m.',
                'producto' => 'iPhone 15 128GB Azul',
                'tipo_producto' => 'telefono',
                'total' => 'S/ 4,399.00',
                'estado' => 'Entregado',
                'texto_accion' => 'Descargar comprobante',
            ],
            [
                'codigo' => 'MD-2024-000142',
                'fecha' => '8 ene. 2024, 4:18 p. m.',
                'producto' => 'Parlante JBL Flip 6',
                'tipo_producto' => 'audio_portatil',
                'total' => 'S/ 499.00',
                'estado' => 'En tránsito',
                'texto_accion' => 'Seguir envío',
            ],
            [
                'codigo' => 'MD-2024-000128',
                'fecha' => '3 ene. 2024, 11:06 a. m.',
                'producto' => 'Audífonos Beats Studio Pro',
                'tipo_producto' => 'audifonos',
                'total' => 'S/ 1,299.00',
                'estado' => 'Preparando',
                'texto_accion' => 'Ver detalle',
            ],
            [
                'codigo' => 'MD-2023-000987',
                'fecha' => '28 dic. 2023, 2:30 p. m.',
                'producto' => 'Xiaomi Redmi Note 13',
                'tipo_producto' => 'telefono',
                'total' => 'S/ 849.00',
                'estado' => 'Entregado',
                'texto_accion' => 'Descargar comprobante',
            ],
            [
                'codigo' => 'MD-2023-000876',
                'fecha' => '15 dic. 2023, 9:15 a. m.',
                'producto' => 'Samsung Galaxy A55 5G',
                'tipo_producto' => 'telefono',
                'total' => 'S/ 1,299.00',
                'estado' => 'Entregado',
                'texto_accion' => 'Descargar comprobante',
            ],
        ];
    }

    /**
     * @param array<string, mixed> $interfaz
     * @return array<string, mixed>
     */
    public static function preparar(array $interfaz): array
    {
        $nombresTabla = [
            'Distribuidora Andina SAC',
            'Comercial Nova SAC',
            'TechSolutions Perú',
            'Inversiones Globales',
            'Grupo Empresarial R&G',
            'ElectroSur SAC',
            'Soluciones Digitales EIRL',
            'Retail Center',
        ];
        $columnasTabla = $interfaz['columnas'] ?: ['Código', 'Nombre', 'Fecha', 'Responsable', 'Estado'];
        $filasTabla = [];
        foreach ($nombresTabla as $indice => $nombre) {
            $valores = [
                sprintf('%s-2025-%04d', mb_strtoupper(mb_substr((string) $interfaz['clave'], 0, 3)), 48 - $indice),
                $nombre,
                $indice % 2 ? '10 Jun 2025' : '08 Jun 2025',
                'S/ ' . number_format(1299 + ($indice * 850)),
                $indice % 3 ? 'Activo' : 'En proceso',
            ];
            $fila = [];
            foreach ($columnasTabla as $posicion => $columna) {
                $valor = $valores[$posicion] ?? ($indice % 2 ? 'Completado' : 'Pendiente');
                $fila[] = [
                    'valor' => $valor,
                    'es_estado' => $posicion >= 4,
                    'clase_estado' => 'estado--' . ($indice % 3 ? 'verde' : 'ambar'),
                    'atributoEstadoOculto' => $posicion >= 4 ? '' : 'hidden',
                    'atributoValorOculto' => $posicion >= 4 ? 'hidden' : '',
                ];
            }
            $filasTabla[] = $fila;
        }

        $productos = ['iPhone 15 128GB', 'Samsung Galaxy A55', 'Xiaomi Redmi Note 13', 'AirPods Pro 2', 'JBL Flip 6', 'Samsung Galaxy Book4', 'Apple Watch SE', 'Cargador USB-C 20W'];
        $marcas = ['Apple', 'Samsung', 'Xiaomi', 'Apple', 'JBL', 'Samsung', 'Apple', 'Apple'];
        $iconos = ['bi-phone', 'bi-phone', 'bi-phone', 'bi-earbuds', 'bi-speaker', 'bi-laptop', 'bi-smartwatch', 'bi-plug'];
        $tarjetas = [];
        foreach ($productos as $indice => $producto) {
            $tarjetas[] = [
                'etiqueta' => $indice % 3 === 0 ? 'Oferta' : 'Disponible',
                'icono' => $iconos[$indice],
                'marca' => $marcas[$indice],
                'nombre' => $producto,
                'valoracion' => '4.' . (5 + ($indice % 4)),
                'precio' => number_format(129 + ($indice * 410), 2),
            ];
        }

        $titulosContenido = ['Innovación que impulsa tu negocio', '5 claves para digitalizar tu empresa', 'Soluciones tecnológicas para crecer', 'Descuento especial en laptops', 'Transforma tu oficina con tecnología', 'Tips de productividad con Microsoft 365'];
        $tiposContenido = ['Banner', 'Blog post', 'Video', 'Promoción'];
        $canalesContenido = ['Instagram', 'Sitio web', 'YouTube', 'Facebook'];
        $contenido = [];
        foreach ($titulosContenido as $indice => $titulo) {
            $contenido[] = [
                'titulo' => $titulo,
                'tipo' => $tiposContenido[$indice % 4],
                'estado' => $indice % 3 ? 'Publicado' : 'Programado',
                'clase_estado' => $indice % 3 ? 'verde' : 'ambar',
                'fecha' => (string) (10 + $indice) . ' Jun 2025',
                'canal' => $canalesContenido[$indice % 4],
            ];
        }

        $pedidos = ['#PED001245', '#PED001198', '#PED001156', '#PED001102', '#PED001078'];
        $fechasPedidos = ['15 mar. 2025', '28 feb. 2025', '10 ene. 2025', '12 dic. 2024', '25 nov. 2024'];
        $historial = [];
        foreach ($pedidos as $indice => $pedido) {
            $historial[] = [
                'pedido' => $pedido,
                'fecha' => $fechasPedidos[$indice],
                'cantidad_adicional' => $indice + 1,
                'total' => number_format(499 + ($indice * 350), 2),
                'comprobante' => (string) (23456 - $indice),
            ];
        }
        $productosFavoritos = ['AirPods Pro', 'JBL Flip 6', 'Cable USB-C', 'Cargador 20W'];
        $iconosFavoritos = ['bi-earbuds', 'bi-speaker', 'bi-usb-c', 'bi-plug'];
        $favoritos = [];
        foreach ($productosFavoritos as $indice => $producto) {
            $favoritos[] = ['nombre' => $producto, 'icono' => $iconosFavoritos[$indice], 'precio' => 89 + ($indice * 120)];
        }
        $productosRecomendados = ['iPhone 15', 'Audífonos JBL', 'Xiaomi Watch'];
        $iconosRecomendados = ['bi-phone', 'bi-headphones', 'bi-smartwatch'];
        $recomendados = [];
        foreach ($productosRecomendados as $indice => $producto) {
            $recomendados[] = ['nombre' => $producto, 'icono' => $iconosRecomendados[$indice], 'precio' => 249 + ($indice * 450)];
        }

        $etapasKanban = ['Nuevo lead', 'Contactado', 'Propuesta enviada', 'Negociación', 'Cierre'];
        $coloresKanban = ['azul', 'verde', 'ambar', 'violeta', 'verde'];
        $empresasKanban = ['Distribuidora Andina SAC', 'Supermercados Sol', 'Comercial del Sur', 'MegaRetail SAC'];
        $responsablesKanban = ['Laura Torres', 'Carlos Mendoza', 'Andrea Ruiz'];
        $kanban = [];
        foreach ($etapasKanban as $indiceEtapa => $etapa) {
            $oportunidades = [];
            foreach ($empresasKanban as $indice => $empresa) {
                $oportunidades[] = [
                    'empresa' => $empresa,
                    'iniciales' => mb_substr($empresa, 0, 2),
                    'total' => number_format(180000 + ($indice * 42000) + ($indiceEtapa * 10000)),
                    'responsable' => $responsablesKanban[$indice % 3],
                    'fecha' => (string) (12 + $indice) . ' Jun 2025',
                ];
            }
            $kanban[] = [
                'etapa' => $etapa,
                'color' => $coloresKanban[$indiceEtapa],
                'cantidad' => 5 + $indiceEtapa,
                'total' => number_format(84200 + ($indiceEtapa * 138000)),
                'oportunidades' => $oportunidades,
            ];
        }

        $alturasAnalitica = [55, 72, 68, 94, 83, 112, 92, 126, 106, 138, 119, 148];
        $mesesAnalitica = ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun'];
        $serieAnalitica = [];
        foreach ($alturasAnalitica as $indice => $altura) {
            $serieAnalitica[] = ['altura' => $altura, 'mes' => $mesesAnalitica[$indice % 6]];
        }
        $accionesIA = ['Analizar campaña', 'Segmentar audiencia', 'Generar reporte IA', 'Optimizar contenido', 'Sugerir keywords', 'Crear audiencia similar'];
        $textosRecomendacionesIA = ['Incrementa la inversión en campañas de video.', 'Segmenta audiencias similares a tus mejores clientes.', 'Optimiza títulos y descripciones.', 'Reactiva usuarios en riesgo.'];
        $recomendacionesIA = array_map(
            static fn (string $texto, int $indice): array => ['numero' => $indice + 1, 'texto' => $texto],
            $textosRecomendacionesIA,
            array_keys($textosRecomendacionesIA)
        );
        $alertasIA = ['Caída del 28% en tráfico', 'Aumento inusual de CPC', 'Conversión superior al promedio', 'Nuevo segmento potencial'];
        $canales = ['Google Ads', 'Facebook', 'Instagram', 'Email Marketing', 'SEO'];
        $rendimientoCanales = [];
        foreach ($canales as $indice => $canal) {
            $rendimientoCanales[] = ['nombre' => $canal, 'visitas' => number_format(42320 - ($indice * 5400)), 'variacion' => 28 - ($indice * 3)];
        }

        return [
            'titulo_minuscula' => mb_strtolower((string) ($interfaz['titulo'] ?? '')),
            'tabla' => ['columnas' => $columnasTabla, 'filas' => $filasTabla],
            'productos_tarjetas' => $tarjetas,
            'contenido' => $contenido,
            'historial' => $historial,
            'favoritos' => $favoritos,
            'recomendados' => $recomendados,
            'kanban' => $kanban,
            'serie_analitica' => $serieAnalitica,
            'audiencia_total' => '125,430',
            'leyenda_audiencia' => [
                ['etiqueta' => 'Nuevos visitantes', 'porcentaje' => '40%', 'color' => 'azul'],
                ['etiqueta' => 'Recurrentes', 'porcentaje' => '28%', 'color' => 'verde'],
                ['etiqueta' => 'Leads', 'porcentaje' => '18%', 'color' => 'ambar'],
                ['etiqueta' => 'Otros', 'porcentaje' => '14%', 'color' => 'violeta'],
            ],
            'acciones_ia' => $accionesIA,
            'recomendaciones_ia' => $recomendacionesIA,
            'alertas_ia' => $alertasIA,
            'rendimiento_canales' => $rendimientoCanales,
            'perfil' => [
                'iniciales' => 'CM',
                'nombre' => 'Carlos Mendoza',
                'tipo_cliente' => 'Cliente de MD Technology Cell',
                'estado' => 'Cuenta activa',
                'datos' => [
                    ['etiqueta' => 'Nombres', 'valor' => 'Carlos'],
                    ['etiqueta' => 'Apellidos', 'valor' => 'Mendoza Pérez'],
                    ['etiqueta' => 'Correo electrónico', 'valor' => 'carlos.mendoza@email.com'],
                    ['etiqueta' => 'Teléfono', 'valor' => '987 654 321'],
                    ['etiqueta' => 'Documento', 'valor' => '74852631'],
                    ['etiqueta' => 'Ciudad', 'valor' => 'Bagua, Amazonas'],
                ],
                'dias_desde_actualizacion' => 38,
            ],
        ];
    }
}

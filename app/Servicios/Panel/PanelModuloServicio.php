<?php

declare(strict_types=1);

namespace App\Servicios\Panel;

use App\DAO\Contratos\RepositorioModuloInterfaz;
use App\Servicios\Categorias\CategoriaServicio;
use App\Servicios\Clientes\ClienteServicio;
use App\Servicios\Inventario\InventarioServicio;
use App\Servicios\Pedidos\PedidoServicio;
use App\Servicios\Proveedores\ProveedorServicio;
use App\Soporte\Autorizacion\AccesoRol;
use App\Soporte\Registros\RegistradorArchivo;

/** Coordina la configuración y los casos de uso de módulos genéricos por rol. */
final class PanelModuloServicio
{
    public function __construct(
        private RepositorioModuloInterfaz $modulos,
        private CategoriaServicio $categorias,
        private ClienteServicio $clientes,
        private ProveedorServicio $proveedores,
        private InventarioServicio $inventario,
        private PedidoServicio $pedidos,
        private RegistradorArchivo $registro
    ) {
    }

    /** @return array<string, mixed> */
    public function configuracion(string $modulo): array
    {
        $texto = ['type' => 'text', 'max' => 160];
        $requerido = ['type' => 'text', 'required' => true, 'max' => 160];
        $configuraciones = [
            'categorias' => ['titulo' => 'Gestión de categorías', 'descripcion' => 'Organiza el catálogo y conserva una clasificación coherente.', 'crud' => true, 'campos' => ['nombre' => $requerido + ['label' => 'Nombre'], 'descripcion' => ['type' => 'textarea', 'label' => 'Descripción', 'max' => 500]]],
            'proveedores' => ['titulo' => 'Proveedores', 'descripcion' => 'Gestiona datos de contacto y abastecimiento.', 'crud' => true, 'campos' => ['nombre' => $requerido + ['label' => 'Nombre'], 'ruc' => $requerido + ['label' => 'RUC', 'max' => 20], 'correo' => ['type' => 'email', 'label' => 'Correo', 'required' => true, 'max' => 160], 'telefono' => $texto + ['label' => 'Teléfono', 'max' => 30], 'ciudad' => $texto + ['label' => 'Ciudad', 'max' => 80]]],
            'clientes' => ['titulo' => 'Gestión de clientes', 'descripcion' => 'Administra clientes minoristas y mayoristas.', 'crud' => true, 'campos' => $this->camposCliente()],
            'clientes-mayoristas' => ['titulo' => 'Clientes mayoristas', 'descripcion' => 'Gestiona la cartera comercial B2B.', 'crud' => true, 'campos' => $this->camposCliente(false)],
            'cotizaciones' => ['titulo' => 'Cotizaciones', 'descripcion' => 'Crea propuestas y controla su estado comercial.', 'crud' => true, 'campos' => ['cliente_id' => ['type' => 'select-data', 'label' => 'Cliente', 'required' => true], 'total' => ['type' => 'number', 'label' => 'Total', 'required' => true, 'min' => 0], 'estado' => ['type' => 'select', 'label' => 'Estado', 'required' => true, 'options' => ['Borrador' => 'Borrador', 'Enviada' => 'Enviada', 'Aprobada' => 'Aprobada', 'Rechazada' => 'Rechazada']], 'notas' => ['type' => 'textarea', 'label' => 'Notas', 'max' => 500]]],
            'compras' => ['titulo' => 'Gestión de compras', 'descripcion' => 'Registra órdenes de compra a proveedores.', 'crud' => true, 'campos' => ['proveedor_id' => ['type' => 'select-data', 'label' => 'Proveedor', 'required' => true], 'total' => ['type' => 'number', 'label' => 'Total', 'required' => true, 'min' => 0], 'estado' => ['type' => 'select', 'label' => 'Estado', 'required' => true, 'options' => ['Pendiente' => 'Pendiente', 'Aprobada' => 'Aprobada', 'Recibida' => 'Recibida', 'Cancelada' => 'Cancelada']], 'fecha' => ['type' => 'date', 'label' => 'Fecha', 'required' => true]]],
            'inventario' => ['titulo' => 'Control de inventario', 'descripcion' => 'Consulta existencias y registra entradas, salidas o ajustes mediante transacciones.', 'crud' => true, 'create_only' => true, 'campos' => ['producto_id' => ['type' => 'select-data', 'label' => 'Producto', 'required' => true], 'tipo_movimiento' => ['type' => 'select', 'label' => 'Movimiento', 'required' => true, 'options' => ['entrada' => 'Entrada', 'salida' => 'Salida', 'ajuste' => 'Ajuste']], 'cantidad' => ['type' => 'number', 'label' => 'Cantidad', 'required' => true, 'min' => 1], 'notas' => ['type' => 'textarea', 'label' => 'Motivo', 'required' => true, 'max' => 500]]],
            'pedidos' => ['titulo' => 'Pedidos', 'descripcion' => 'Consulta pedidos y actualiza su estado.', 'crud' => true, 'update_only' => true, 'campos' => ['estado' => ['type' => 'select', 'label' => 'Estado', 'required' => true, 'options' => ['Pendiente' => 'Pendiente', 'En proceso' => 'En proceso', 'Enviado' => 'Enviado', 'Entregado' => 'Entregado', 'Cancelado' => 'Cancelado']]]],
            'pedidos-mayoristas' => ['titulo' => 'Pedidos mayoristas', 'descripcion' => 'Seguimiento de pedidos B2B.', 'crud' => true, 'update_only' => true, 'campos' => ['estado' => ['type' => 'select', 'label' => 'Estado', 'required' => true, 'options' => ['Pendiente' => 'Pendiente', 'En proceso' => 'En proceso', 'Enviado' => 'Enviado', 'Entregado' => 'Entregado', 'Cancelado' => 'Cancelado']]]],
        ];

        if (isset($configuraciones[$modulo])) {
            return $configuraciones[$modulo];
        }

        $titulos = [
            'productos' => 'Productos', 'ventas' => 'Ventas', 'publicidad' => 'Publicidad y campañas', 'campanias' => 'MD Ads y campañas',
            'usuarios' => 'Usuarios y roles', 'reportes' => 'Reportes gerenciales', 'auditoria' => 'Auditoría e historial',
            'preparacion-pedidos' => 'Preparación de pedidos', 'alertas-stock' => 'Alertas de stock', 'seguimiento-comercial' => 'Seguimiento comercial',
            'garantias' => 'Gestión de garantías', 'devoluciones' => 'Gestión de devoluciones', 'reclamaciones' => 'Gestión de reclamaciones',
            'contenido' => 'Contenido digital', 'promociones' => 'Promociones', 'destacados' => 'Productos destacados', 'segmentacion' => 'Segmentación',
            'leads' => 'Gestión de leads', 'analitica' => 'Analítica visual', 'perfil' => 'Mi perfil', 'historial' => 'Historial',
            'direcciones' => 'Mis direcciones', 'favoritos' => 'Favoritos', 'catalogo-b2b' => 'Catálogo B2B',
            'recepciones' => 'Recepciones', 'almacenes' => 'Almacenes', 'configuracion' => 'Configuración',
            'marketing-b2b' => 'Marketing B2B', 'audiencias' => 'Audiencias', 'redes-sociales' => 'Redes sociales',
            'automatizaciones' => 'Automatizaciones', 'integraciones' => 'Integraciones',
        ];

        return [
            'titulo' => $titulos[$modulo] ?? ucfirst(str_replace('-', ' ', $modulo)),
            'descripcion' => 'Interfaz preparada y navegable, alineada con el flujo del rol.',
            'crud' => false,
            'campos' => [],
        ];
    }

    /** @return array<string, int|float> */
    public function resumen(): array
    {
        return $this->consultaSegura(fn (): array => $this->modulos->resumen(), []);
    }

    /** @return array<int, array<string, mixed>> */
    public function listar(string $modulo): array
    {
        return $this->consultaSegura(fn (): array => match ($modulo) {
            'categorias' => $this->categorias->todas(),
            'proveedores' => $this->proveedores->todos(),
            'clientes' => array_values(array_filter(
                $this->clientes->todos(),
                static fn (array $cliente): bool => ($cliente['tipo'] ?? '') === 'minorista'
            )),
            'clientes-mayoristas' => array_values(array_filter(
                $this->clientes->todos(),
                static fn (array $cliente): bool => ($cliente['tipo'] ?? '') === 'mayorista'
            )),
            'inventario' => $this->inventario->existencias(),
            default => $this->modulos->listar($modulo),
        }, []);
    }

    /** @param array<string, mixed> $datos */
    public function crear(string $modulo, array $datos, int $idUsuario): int
    {
        return match ($modulo) {
            'categorias' => $this->categorias->guardar([
                'nombre' => (string) $datos['nombre'],
                'descripcion' => (string) ($datos['descripcion'] ?? ''),
            ]),
            'proveedores' => $this->proveedores->guardar([
                'nombre' => (string) $datos['nombre'],
                'ruc' => (string) $datos['ruc'],
                'correo' => (string) $datos['correo'],
                'telefono' => (string) ($datos['telefono'] ?? ''),
                'ciudad' => (string) ($datos['ciudad'] ?? ''),
            ]),
            'clientes', 'clientes-mayoristas' => $this->clientes->guardarParaSegmento(
                $datos,
                $modulo === 'clientes-mayoristas' ? 'mayorista' : 'minorista'
            ),
            'inventario' => $this->inventario->registrar($datos, $idUsuario),
            default => $this->modulos->crear($modulo, $datos, $idUsuario),
        };
    }

    /** @param array<string, mixed> $datos */
    public function actualizar(string $modulo, int $idRegistro, array $datos): void
    {
        if ($modulo === 'categorias') {
            $this->categorias->guardar([
                'nombre' => (string) $datos['nombre'],
                'descripcion' => (string) ($datos['descripcion'] ?? ''),
            ], $idRegistro);
            return;
        }
        if ($modulo === 'proveedores') {
            $this->proveedores->guardar([
                'nombre' => (string) $datos['nombre'],
                'ruc' => (string) $datos['ruc'],
                'correo' => (string) $datos['correo'],
                'telefono' => (string) ($datos['telefono'] ?? ''),
                'ciudad' => (string) ($datos['ciudad'] ?? ''),
            ], $idRegistro);
            return;
        }
        if (in_array($modulo, ['clientes', 'clientes-mayoristas'], true)) {
            $this->clientes->guardarParaSegmento(
                $datos,
                $modulo === 'clientes-mayoristas' ? 'mayorista' : 'minorista',
                $idRegistro
            );
            return;
        }
        if (in_array($modulo, ['pedidos', 'pedidos-mayoristas'], true)) {
            $rolCliente = $modulo === 'pedidos-mayoristas' ? 'cliente_mayorista' : 'cliente_minorista';
            $this->pedidos->actualizarEstadoParaRolCliente($idRegistro, (string) $datos['estado'], $rolCliente);
            return;
        }

        $this->modulos->actualizar($modulo, $idRegistro, $datos);
    }

    public function desactivar(string $modulo, int $idRegistro): void
    {
        if ($modulo === 'categorias') {
            $this->categorias->desactivar($idRegistro);
            return;
        }
        if ($modulo === 'proveedores') {
            $this->proveedores->desactivar($idRegistro);
            return;
        }
        if (in_array($modulo, ['clientes', 'clientes-mayoristas'], true)) {
            $this->clientes->desactivarParaSegmento(
                $idRegistro,
                $modulo === 'clientes-mayoristas' ? 'mayorista' : 'minorista'
            );
            return;
        }

        $this->modulos->desactivar($modulo, $idRegistro);
    }

    public function puedeOperar(string $rol, string $modulo, string $operacion): bool
    {
        if (!AccesoRol::puedeOperar($rol, $modulo, $operacion)) {
            return false;
        }

        $configuracion = $this->configuracion($modulo);
        if (($configuracion['crud'] ?? false) !== true) {
            return false;
        }

        return match ($operacion) {
            'crear' => ($configuracion['update_only'] ?? false) !== true,
            'editar', 'cambiar_estado' => ($configuracion['create_only'] ?? false) !== true,
            'desactivar' => ($configuracion['create_only'] ?? false) !== true
                && ($configuracion['update_only'] ?? false) !== true
                && in_array($modulo, ['categorias', 'proveedores', 'clientes', 'clientes-mayoristas', 'cotizaciones', 'compras'], true),
            default => false,
        };
    }

    public function operacionActualizacion(string $modulo): string
    {
        return in_array($modulo, ['pedidos', 'pedidos-mayoristas'], true)
            ? 'cambiar_estado'
            : 'editar';
    }

    /** @return array<string, array<int, array{id:int,etiqueta:string}>> */
    public function opciones(string $modulo): array
    {
        $tipos = match ($modulo) {
            'inventario' => ['productos'],
            'compras' => ['proveedores'],
            'cotizaciones' => ['clientes'],
            default => [],
        };
        $opciones = [];

        foreach ($tipos as $tipo) {
            $opciones[$tipo] = $this->consultaSegura(fn (): array => $this->modulos->opciones($tipo), []);
        }

        return $opciones;
    }

    /** @return array<string, array<string, mixed>> */
    private function camposCliente(bool $incluirTipo = true): array
    {
        $campos = [];
        if ($incluirTipo) {
            $campos['tipo'] = ['type' => 'select', 'label' => 'Tipo', 'required' => true, 'options' => ['minorista' => 'Minorista']];
        }
        $campos += [
            'documento' => ['type' => 'text', 'label' => 'DNI o RUC', 'required' => true, 'max' => 20],
            'empresa' => ['type' => 'text', 'label' => 'Empresa', 'max' => 160],
            'contacto' => ['type' => 'text', 'label' => 'Contacto', 'required' => true, 'max' => 160],
            'correo' => ['type' => 'email', 'label' => 'Correo', 'required' => true, 'max' => 160],
            'telefono' => ['type' => 'text', 'label' => 'Teléfono', 'max' => 30],
            'ciudad' => ['type' => 'text', 'label' => 'Ciudad', 'max' => 80],
        ];

        return $campos;
    }

    /**
     * @template TClave of array-key
     * @template TValor
     * @param callable(): array<TClave, TValor> $consulta
     * @param array<TClave, TValor> $predeterminado
     * @return array<TClave, TValor>
     */
    private function consultaSegura(callable $consulta, array $predeterminado): array
    {
        try {
            return $consulta();
        } catch (\Throwable $excepcion) {
            $this->registro->advertencia('Consulta del panel no disponible.', ['message' => $excepcion->getMessage()]);

            return $predeterminado;
        }
    }
}

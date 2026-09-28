<?php

declare(strict_types=1);

namespace App\Controladores\Panel;

use App\DAO\Panel\ModuloDAO;
use App\Nucleo\Http\Solicitud;
use App\Validacion\Panel\SolicitudModulo;
use App\Nucleo\Http\Respuesta;
use App\Nucleo\Presentacion\Vista;
use App\Soporte\Registros\RegistradorArchivo;
use App\Soporte\Mensajes\MensajeFlashServicio;
use App\Soporte\Autorizacion\AccesoRol;
use App\Soporte\Excepciones\ExcepcionValidacion;
use App\Soporte\Presentacion\CatalogoInterfaces;

final class PanelRolController
{
    public function __construct(
        private Vista $vista,
        private ModuloDAO $modulos,
        private MensajeFlashServicio $mensajes,
        private RegistradorArchivo $registro,
    ) {
    }

    public function tablero(Solicitud $solicitud): Respuesta
    {
        $usuario = current_user() ?? [];
        $resumen = $this->consultaSegura(fn (): array => $this->modulos->resumen(), []);
        $rol = AccesoRol::normalize((string) ($usuario['rol'] ?? ''));

        if (str_starts_with($rol, 'cliente_')) {
            return $this->vista->renderizar('modulos.cuenta.panel', [
                'tituloPagina' => 'Mi cuenta',
                'modulo' => 'dashboard',
                'interfaz' => CatalogoInterfaces::tablero($rol, $resumen),
                'usuarioCuenta' => $usuario,
            ]);
        }

        return $this->vista->renderizar('modulos.panel.tablero', [
            'tituloPagina' => 'Panel ' . AccesoRol::label((string) ($usuario['rol'] ?? '')),
            'resumen' => $resumen,
            'interfaz' => CatalogoInterfaces::tablero($rol, $resumen),
            'navegacionRol' => AccesoRol::navigation((string) ($usuario['rol'] ?? '')),
        ], 'interno');
    }

    public function indice(Solicitud $solicitud): Respuesta
    {
        $modulo = (string) $solicitud->parametroRuta('module');
        $configuracion = $this->configuracion($modulo);
        $rol = AccesoRol::normalize((string) (current_user()['rol'] ?? ''));
        $registros = $this->consultaSegura(fn (): array => $this->modulos->listar($modulo), []);
        $edicion = null;
        $idEdicion = filter_var($solicitud->consulta('edit'), FILTER_VALIDATE_INT);
        if ($idEdicion) {
            foreach ($registros as $registro) {
                if ((int) ($registro['id'] ?? 0) === (int) $idEdicion) {
                    $edicion = $registro;
                    break;
                }
            }
        }

        if (str_starts_with($rol, 'cliente_')) {
            return $this->vista->renderizar('modulos.cuenta.panel', [
                'tituloPagina' => $configuracion['titulo'],
                'modulo' => $modulo,
                'interfaz' => CatalogoInterfaces::modulo($modulo, $rol),
                'usuarioCuenta' => current_user() ?? [],
            ]);
        }

        return $this->vista->renderizar('modulos.panel.indice', [
            'tituloPagina' => $configuracion['titulo'],
            'modulo' => $modulo,
            'configuracionModulo' => $configuracion,
            'interfaz' => CatalogoInterfaces::modulo($modulo, $rol),
            'registros' => $registros,
            'registroEdicion' => $edicion,
            'resumen' => $this->consultaSegura(fn (): array => $this->modulos->resumen(), []),
            'opciones' => $this->opciones($modulo),
            'exito' => $this->mensajes->extraer('success'),
            'error' => $this->mensajes->extraer('error'),
        ], 'interno');
    }

    public function guardar(Solicitud $solicitud): Respuesta
    {
        $modulo = (string) $solicitud->parametroRuta('module');
        try {
            $configuracion = $this->configuracion($modulo);
            if (($configuracion['crud'] ?? false) !== true) {
                throw new \RuntimeException('Este módulo es de consulta en la primera unidad.');
            }
            $datos = SolicitudModulo::validar($solicitud, (array) $configuracion['campos']);
            $this->modulos->crear($modulo, $datos, (int) (current_user()['id'] ?? 0));
            $this->mensajes->exito('Registro guardado correctamente.');
        } catch (ExcepcionValidacion $excepcion) {
            $errores = $excepcion->errores();
            $this->mensajes->error(reset($errores) ?: 'Revisa los datos ingresados.');
        } catch (\Throwable $excepcion) {
            $this->registro->error('No se pudo crear el registro del módulo.', ['module' => $modulo, 'message' => $excepcion->getMessage()]);
            $this->mensajes->error($excepcion instanceof \RuntimeException ? $excepcion->getMessage() : 'No se pudo guardar el registro.');
        }

        return redirect('panel/' . $modulo);
    }

    public function actualizar(Solicitud $solicitud): Respuesta
    {
        $modulo = (string) $solicitud->parametroRuta('module');
        $id = filter_var($solicitud->parametroRuta('id'), FILTER_VALIDATE_INT);
        try {
            if (!$id) {
                throw new \RuntimeException('El identificador recibido no es válido.');
            }
            $configuracion = $this->configuracion($modulo);
            $datos = SolicitudModulo::validar($solicitud, (array) $configuracion['campos']);
            $this->modulos->actualizar($modulo, (int) $id, $datos);
            $this->mensajes->exito('Registro actualizado correctamente.');
        } catch (ExcepcionValidacion $excepcion) {
            $errores = $excepcion->errores();
            $this->mensajes->error(reset($errores) ?: 'Revisa los datos ingresados.');
        } catch (\Throwable $excepcion) {
            $this->registro->error('No se pudo actualizar el registro del módulo.', ['module' => $modulo, 'message' => $excepcion->getMessage()]);
            $this->mensajes->error($excepcion instanceof \RuntimeException ? $excepcion->getMessage() : 'No se pudo actualizar el registro.');
        }

        return redirect('panel/' . $modulo);
    }

    public function desactivar(Solicitud $solicitud): Respuesta
    {
        $modulo = (string) $solicitud->parametroRuta('module');
        $id = filter_var($solicitud->parametroRuta('id'), FILTER_VALIDATE_INT);
        try {
            if (!$id) {
                throw new \RuntimeException('El identificador recibido no es válido.');
            }
            $this->modulos->desactivar($modulo, (int) $id);
            $this->mensajes->exito('Registro desactivado correctamente.');
        } catch (\Throwable $excepcion) {
            $this->registro->advertencia('No se pudo desactivar el registro.', ['module' => $modulo, 'message' => $excepcion->getMessage()]);
            $this->mensajes->error($excepcion->getMessage());
        }

        return redirect('panel/' . $modulo);
    }

    /** @return array<string, mixed> */
    private function configuracion(string $modulo): array
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

        return ['titulo' => $titulos[$modulo] ?? ucfirst(str_replace('-', ' ', $modulo)), 'descripcion' => 'Interfaz preparada y navegable, alineada con el flujo del rol.', 'crud' => false, 'campos' => []];
    }

    /** @return array<string, array<string, mixed>> */
    private function camposCliente(bool $incluirTipo = true): array
    {
        $campos = [];
        if ($incluirTipo) {
            $campos['tipo'] = ['type' => 'select', 'label' => 'Tipo', 'required' => true, 'options' => ['minorista' => 'Minorista', 'mayorista' => 'Mayorista']];
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

    /** @return array<string, array<int, array{id:int,etiqueta:string}>> */
    private function opciones(string $modulo): array
    {
        $resultado = [];
        $tipos = match ($modulo) {
            'inventario' => ['productos'],
            'compras' => ['proveedores'],
            'cotizaciones' => ['clientes'],
            default => [],
        };
        foreach ($tipos as $tipo) {
            $resultado[$tipo] = $this->consultaSegura(fn (): array => $this->modulos->opciones($tipo), []);
        }

        return $resultado;
    }

    private function consultaSegura(callable $consulta, mixed $predeterminado): mixed
    {
        try {
            return $consulta();
        } catch (\Throwable $excepcion) {
            $this->registro->advertencia('Consulta del panel no disponible.', ['message' => $excepcion->getMessage()]);
            return $predeterminado;
        }
    }
}

<?php

declare(strict_types=1);

namespace App\Controladores\Panel;

use App\Nucleo\Http\Solicitud;
use App\Validacion\Panel\SolicitudModulo;
use App\Nucleo\Http\Respuesta;
use App\Nucleo\Presentacion\Vista;
use App\Nucleo\Presentacion\Panel\PresentadorPanelRol;
use App\Soporte\Registros\RegistradorArchivo;
use App\Soporte\Mensajes\MensajeFlashServicio;
use App\Soporte\Autorizacion\AccesoRol;
use App\Soporte\Excepciones\ExcepcionValidacion;
use App\Soporte\Presentacion\CatalogoInterfaces;
use App\Soporte\Presentacion\DatosDemostracionPanel;
use App\Validacion\Inventario\SolicitudMovimientoInventario;
use App\Servicios\Panel\PanelModuloServicio;

final class PanelRolController
{
    public function __construct(
        private Vista $vista,
        private PanelModuloServicio $panel,
        private MensajeFlashServicio $mensajes,
        private RegistradorArchivo $registro
    ) {
    }

    /**
     * Prepara los datos que se muestran en el tablero del módulo.
     */
    public function tablero(Solicitud $solicitud): Respuesta
    {
        $usuario = usuario_actual() ?? [];
        $rol = AccesoRol::normalizarRol((string) ($usuario['rol'] ?? ''));

        if ($rol === 'administrador') {
            return redirigir('admin');
        }

        if (str_starts_with($rol, 'cliente_')) {
            $resumen = [];
            $datosCuenta = array_merge(
                PresentadorPanelRol::prepararDatosCuenta($usuario, $rol), [
                'tituloPagina' => 'Mi cuenta',
                'modulo' => 'dashboard',
                'interfaz' => CatalogoInterfaces::tablero($rol, $resumen),
                'pedidosCuenta' => PresentadorPanelRol::presentarPedidosCuenta(
                    array_slice(DatosDemostracionPanel::pedidosCuenta(), 0, 3)
                ),
            ]);

            return $this->renderizarPanelCuenta('dashboard', $datosCuenta);
        }

        $resumen = $this->panel->resumen();

        return $this->vista->renderizar('modulos.panel.tablero', [
            'tituloPagina' => 'Panel ' . AccesoRol::etiqueta((string) ($usuario['rol'] ?? '')),
            'resumen' => $resumen,
            'interfaz' => CatalogoInterfaces::tablero($rol, $resumen),
        ], 'interno');
    }

    /**
     * Prepara los datos de la página y muestra el listado principal del módulo.
     */
    public function indice(Solicitud $solicitud): Respuesta
    {
        $modulo = (string) $solicitud->parametroRuta('module');
        $configuracion = $this->panel->configuracion($modulo);
        $rol = AccesoRol::normalizarRol((string) (usuario_actual()['rol'] ?? ''));
        $interfaz = CatalogoInterfaces::modulo($modulo, $rol);
        $datosDemostracion = DatosDemostracionPanel::preparar($interfaz);

        if (str_starts_with($rol, 'cliente_')) {
            $usuarioCuenta = usuario_actual() ?? [];
            $datosCuenta = array_merge(
                PresentadorPanelRol::prepararDatosCuenta($usuarioCuenta, $rol), [
                'tituloPagina' => $configuracion['titulo'],
                'modulo' => $modulo,
                'interfaz' => $interfaz,
                'datosDemostracion' => $datosDemostracion,
                'usuarioCuenta' => $usuarioCuenta,
                'pedidosCuenta' => PresentadorPanelRol::presentarPedidosCuenta(
                    array_slice(DatosDemostracionPanel::pedidosCuenta(), 0, 5)
                ),
            ]);

            return $this->renderizarPanelCuenta($modulo, $datosCuenta);
        }

        $registros = $this->panel->listar($modulo);
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

        $campos = (array) ($configuracion['campos'] ?? []);
        $puedeCrear = $this->panel->puedeOperar($rol, $modulo, 'crear');
        $puedeActualizar = $this->panel->puedeOperar(
            $rol,
            $modulo,
            $this->panel->operacionActualizacion($modulo)
        );
        $puedeDesactivar = $this->panel->puedeOperar($rol, $modulo, 'desactivar');
        $usarInterfazDavid = in_array($rol, ['compras_logistica', 'marketing'], true);
        $opciones = $this->panel->opciones($modulo);

        $presentacion = PresentadorPanelRol::prepararIndiceModulo(
            $modulo,
            $configuracion,
            $interfaz,
            $datosDemostracion,
            $registros,
            $edicion,
            $opciones,
            $this->panel->resumen(),
            $puedeCrear,
            $puedeActualizar,
            $puedeDesactivar,
            $usarInterfazDavid
        );

        $presentacion['camposFormulario'] = array_map(function (array $campo): array {
            $vistaControl = match ($campo['tipo']) {
                'textarea' => 'textarea',
                'select' => 'select',
                'select-data' => 'select-data',
                default => 'entrada',
            };
            $campo['controlHtml'] = $this->vista->renderizar(
                'modulos.panel._parciales.controles.' . $vistaControl,
                $campo,
                '',
            )->contenido();

            return $campo;
        }, $presentacion['camposFormulario']);

        $datosVista = array_merge($presentacion, [
            'modulo' => $modulo,
            'interfaz' => $interfaz,
            'registros' => $registros,
            'exito' => $this->mensajes->extraer('success'),
            'error' => $this->mensajes->extraer('error'),
        ]);
        $vistaModulo = $usarInterfazDavid
            ? 'modulos.panel._parciales.david.indice'
            : 'modulos.panel.indice';

        return $this->vista->renderizar($vistaModulo, $datosVista, 'interno');
    }

    /**
     * Renderiza solo el bloque correspondiente al módulo de cuenta seleccionado.
     *
     * @param array<string, mixed> $datosCuenta
     */
    private function renderizarPanelCuenta(string $modulo, array $datosCuenta): Respuesta
    {
        $datosCuenta['modulo'] = $modulo;
        $datosCuenta['atributoCuentaBreadcrumbModuloOculto'] = $modulo === 'dashboard' ? 'hidden' : '';
        $vistaContenido = PresentadorPanelRol::vistaContenidoCuenta($modulo);
        $datosCuenta['contenidoCuentaHtml'] = $this->vista->renderizar(
            $vistaContenido,
            $datosCuenta,
            '',
        )->contenido();

        return $this->vista->renderizar('modulos.cuenta.panel', $datosCuenta);
    }

    public function guardar(Solicitud $solicitud): Respuesta
    {
        $modulo = (string) $solicitud->parametroRuta('module');
        $rol = AccesoRol::normalizarRol((string) (usuario_actual()['rol'] ?? ''));
        if (!$this->panel->puedeOperar($rol, $modulo, 'crear')) {
            $this->mensajes->error('No tienes autorización para crear registros en este módulo.');
            return redirigir('panel/' . $modulo);
        }

        try {
            $configuracion = $this->panel->configuracion($modulo);
            $datos = $modulo === 'inventario'
                ? SolicitudMovimientoInventario::validar($solicitud)
                : SolicitudModulo::validar($solicitud, (array) $configuracion['campos']);
            $this->panel->crear($modulo, $datos, (int) (usuario_actual()['id'] ?? 0));
            $this->mensajes->exito('Registro guardado correctamente.');
        } catch (ExcepcionValidacion $excepcion) {
            $errores = $excepcion->errores();
            $this->mensajes->error(reset($errores) ?: 'Revisa los datos ingresados.');
        } catch (\DomainException $excepcion) {
            $this->mensajes->error($excepcion->getMessage());
        } catch (\Throwable $excepcion) {
            $this->registro->error('No se pudo crear el registro del módulo.', ['module' => $modulo, 'message' => $excepcion->getMessage()]);
            $this->mensajes->error($excepcion instanceof \RuntimeException ? $excepcion->getMessage() : 'No se pudo guardar el registro.');
        }

        return redirigir('panel/' . $modulo);
    }

    public function actualizar(Solicitud $solicitud): Respuesta
    {
        $modulo = (string) $solicitud->parametroRuta('module');
        $idRegistro = filter_var($solicitud->parametroRuta('id'), FILTER_VALIDATE_INT);
        $rol = AccesoRol::normalizarRol((string) (usuario_actual()['rol'] ?? ''));
        if (!$this->panel->puedeOperar($rol, $modulo, $this->panel->operacionActualizacion($modulo))) {
            $this->mensajes->error('No tienes autorización para actualizar registros en este módulo.');
            return redirigir('panel/' . $modulo);
        }

        try {
            if (!$idRegistro) {
                throw new \RuntimeException('El identificador recibido no es válido.');
            }
            $configuracion = $this->panel->configuracion($modulo);
            $datos = SolicitudModulo::validar($solicitud, (array) $configuracion['campos']);
            $this->panel->actualizar($modulo, (int) $idRegistro, $datos);
            $this->mensajes->exito('Registro actualizado correctamente.');
        } catch (ExcepcionValidacion $excepcion) {
            $errores = $excepcion->errores();
            $this->mensajes->error(reset($errores) ?: 'Revisa los datos ingresados.');
        } catch (\DomainException $excepcion) {
            $this->mensajes->error($excepcion->getMessage());
        } catch (\Throwable $excepcion) {
            $this->registro->error('No se pudo actualizar el registro del módulo.', ['module' => $modulo, 'message' => $excepcion->getMessage()]);
            $this->mensajes->error($excepcion instanceof \RuntimeException ? $excepcion->getMessage() : 'No se pudo actualizar el registro.');
        }

        return redirigir('panel/' . $modulo);
    }

    /**
     * Marca como inactivo el registro seleccionado, sin borrar su historial.
     */
    public function desactivar(Solicitud $solicitud): Respuesta
    {
        $modulo = (string) $solicitud->parametroRuta('module');
        $idRegistro = filter_var($solicitud->parametroRuta('id'), FILTER_VALIDATE_INT);
        $rol = AccesoRol::normalizarRol((string) (usuario_actual()['rol'] ?? ''));
        if (!$this->panel->puedeOperar($rol, $modulo, 'desactivar')) {
            $this->mensajes->error('No tienes autorización para desactivar registros en este módulo.');
            return redirigir('panel/' . $modulo);
        }

        try {
            if (!$idRegistro) {
                throw new \RuntimeException('El identificador recibido no es válido.');
            }
            $this->panel->desactivar($modulo, (int) $idRegistro);
            $this->mensajes->exito('Registro desactivado correctamente.');
        } catch (\Throwable $excepcion) {
            $this->registro->advertencia('No se pudo desactivar el registro.', ['module' => $modulo, 'message' => $excepcion->getMessage()]);
            $this->mensajes->error($excepcion->getMessage());
        }

        return redirigir('panel/' . $modulo);
    }

}

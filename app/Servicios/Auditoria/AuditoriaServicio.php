<?php

declare(strict_types=1);

namespace App\Servicios\Auditoria;

use App\DAO\Contratos\RepositorioAuditoriaInterfaz;
use App\Soporte\Registros\RegistradorArchivo;

final class AuditoriaServicio
{
    public function __construct(private RepositorioAuditoriaInterfaz $auditoria, private RegistradorArchivo $registro)
    {
    }

    /**
     * Filtra valores sensibles y registra la acción sin interrumpir la operación si falla la auditoría.
     * @param array<string, mixed>|null $valoresAnteriores
     * @param array<string, mixed>|null $valoresNuevos
     */
    public function registrar(
        string $accion,
        string $entidad,
        ?int $idEntidad,
        ?array $valoresAnteriores,
        ?array $valoresNuevos,
        string $direccionIp,
    ): void {
        $valoresAnteriores = $this->sinValoresSensibles($valoresAnteriores);
        $valoresNuevos = $this->sinValoresSensibles($valoresNuevos);

        try {
            $this->auditoria->registrar(
                isset(usuario_actual()['id']) ? (int) usuario_actual()['id'] : null,
                $accion,
                $entidad,
                $idEntidad,
                $valoresAnteriores,
                $valoresNuevos,
                $direccionIp
            );
        } catch (\Throwable $excepcion) {
            $this->registro->advertencia(
                'No fue posible guardar el registro de auditoría.',
                ['action' => $accion, 'message' => $excepcion->getMessage()]
            );
        }
    }

    /**
     * Obtiene el valor del parámetro de consulta indicado, si está disponible.
     *
     * @return array{desde:string,hasta:string,registros:array<int,array<string,mixed>>,resumen:array<string,int>,actividad:array<int,array<string,mixed>>}
     */
    public function consulta(string $desde = '', string $hasta = '', string $entidad = '', int $idUsuario = 0): array
    {
        $fechaHasta = $this->fechaValida($hasta) ?? new \DateTimeImmutable('today');
        $fechaDesde = $this->fechaValida($desde) ?? $fechaHasta->modify('-29 days');
        if ($fechaDesde > $fechaHasta) {
            [$fechaDesde, $fechaHasta] = [$fechaHasta, $fechaDesde];
        }
        $desdeNormalizado = $fechaDesde->format('Y-m-d');
        $hastaNormalizado = $fechaHasta->format('Y-m-d');

        return [
            'desde' => $desdeNormalizado,
            'hasta' => $hastaNormalizado,
            'registros' => $this->auditoria->listar($desdeNormalizado, $hastaNormalizado, trim($entidad), $idUsuario),
            'resumen' => $this->auditoria->resumen(),
            'actividad' => $this->auditoria->actividadPorEntidad(),
        ];
    }

    /**
     * Copia los datos omitiendo claves y valores que no deben registrarse.
     *
     * @param array<string, mixed>|null $valores
     * @return array<string, mixed>|null
     */
    private function sinValoresSensibles(?array $valores): ?array
    {
        if ($valores !== null) {
            unset($valores['contrasena'], $valores['token'], $valores['csrf']);
        }

        return $valores;
    }

    /**
     * Comprueba que la fecha recibida tenga un formato válido.
     */
    private function fechaValida(string $fecha): ?\DateTimeImmutable
    {
        $valor = \DateTimeImmutable::createFromFormat('!Y-m-d', $fecha);

        return $valor && $valor->format('Y-m-d') === $fecha ? $valor : null;
    }
}

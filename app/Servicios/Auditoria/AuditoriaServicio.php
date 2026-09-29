<?php

declare(strict_types=1);

namespace App\Servicios\Auditoria;

use App\DAO\Auditoria\RegistroAuditoriaDAO;
use App\Soporte\Registros\RegistradorArchivo;

final class AuditoriaServicio
{
    public function __construct(private RegistroAuditoriaDAO $auditoria, private RegistradorArchivo $registro)
    {
    }

    /**
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
                isset(current_user()['id']) ? (int) current_user()['id'] : null,
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

    /** @return array{desde:string,hasta:string,registros:array<int,array<string,mixed>>,resumen:array<string,int>,actividad:array<int,array<string,mixed>>} */
    public function consulta(string $desde = '', string $hasta = '', string $entidad = '', int $usuarioId = 0): array
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
            'registros' => $this->auditoria->listar($desdeNormalizado, $hastaNormalizado, trim($entidad), $usuarioId),
            'resumen' => $this->auditoria->resumen(),
            'actividad' => $this->auditoria->actividadPorEntidad(),
        ];
    }

    /**
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

    private function fechaValida(string $fecha): ?\DateTimeImmutable
    {
        $valor = \DateTimeImmutable::createFromFormat('!Y-m-d', $fecha);

        return $valor && $valor->format('Y-m-d') === $fecha ? $valor : null;
    }
}

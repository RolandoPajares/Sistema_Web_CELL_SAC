<?php

declare(strict_types=1);

namespace App\Servicios;

use App\DAO\RegistroAuditoriaDAO;
use App\Infraestructura\Registros\RegistradorArchivo;

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
}

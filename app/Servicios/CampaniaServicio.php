<?php

declare(strict_types=1);

namespace App\Servicios;

use App\DAO\CampaniaDAO;

final class CampaniaServicio
{
    public function __construct(private CampaniaDAO $campanias)
    {
    }

    public function conexionDisponible(): bool
    {
        return $this->campanias->estaDisponible();
    }

    /** @return array<int, array<string, mixed>> */
    public function todosParaAdministrador(): array
    {
        if (!$this->conexionDisponible()) {
            return [];
        }

        return $this->campanias->todosParaAdministrador();
    }

    /** @return array<string, mixed>|null */
    public function buscar(int $idCampania): ?array
    {
        if (!$this->conexionDisponible()) {
            return null;
        }
        return $this->campanias->buscar($idCampania);
    }

    /** @return array<string, array<string, mixed>> */
    public function ubicacionesActivas(): array
    {
        if (!$this->conexionDisponible()) {
            return [];
        }

        $ubicaciones = [];
        foreach ($this->campanias->ubicacionesActivas() as $campania) {
            $ubicacion = (string) $campania['ubicacion'];
            if (!isset($ubicaciones[$ubicacion])) {
                $ubicaciones[$ubicacion] = $campania;
            }
        }

        return $ubicaciones;
    }

    /** @param array<string, mixed> $datos */
    public function guardar(array $datos, ?int $idCampania = null): int
    {
        $datosValidados = $this->validar($datos);
        if ($idCampania && $idCampania > 0) {
            if ($this->campanias->buscar($idCampania) === null) {
                throw new \InvalidArgumentException('La campaña no existe.');
            }
            $this->campanias->actualizar($idCampania, $datosValidados);

            return $idCampania;
        }

        return $this->campanias->crear($datosValidados);
    }

    public function desactivar(int $idCampania): void
    {
        if ($idCampania <= 0 || $this->campanias->buscar($idCampania) === null) {
            throw new \InvalidArgumentException('La campaña no existe.');
        }
        $this->campanias->desactivar($idCampania);
    }

    public function registrarEvento(int $idCampania, string $evento): void
    {
        if ($idCampania <= 0 || !$this->conexionDisponible() || !in_array($evento, ['view', 'click'], true)) {
            return;
        }
        $this->campanias->registrarEvento($idCampania, $evento);
    }

    /**
     * @param array<string, mixed> $datos
     * @return array<string, mixed>
     */
    private function validar(array $datos): array
    {
        $texto = static function (mixed $valor, string $predeterminado = ''): string {
            return is_scalar($valor) ? trim((string) $valor) : $predeterminado;
        };
        $ubicacion = $texto($datos['ubicacion'] ?? null, 'emergente');
        if (!in_array($ubicacion, ['emergente', 'lateral', 'barra_superior'], true)) {
            throw new \InvalidArgumentException('Tipo de publicidad no válido.');
        }

        $nombre = $texto($datos['nombre'] ?? null);
        $titulo = $texto($datos['titulo'] ?? null);
        $fechaInicio = str_replace('T', ' ', $texto($datos['inicia_en'] ?? null));
        $fechaFin = str_replace('T', ' ', $texto($datos['finaliza_en'] ?? null));
        if ($nombre === '' || $titulo === '' || mb_strlen($nombre) > 120 || mb_strlen($titulo) > 160) {
            throw new \InvalidArgumentException('Nombre, título y fechas son obligatorios.');
        }

        $inicio = \DateTimeImmutable::createFromFormat('!Y-m-d H:i', $fechaInicio);
        $fin = \DateTimeImmutable::createFromFormat('!Y-m-d H:i', $fechaFin);
        if (!$inicio || !$fin || $inicio->format('Y-m-d H:i') !== $fechaInicio || $fin->format('Y-m-d H:i') !== $fechaFin) {
            throw new \InvalidArgumentException('Ingresa fechas válidas.');
        }
        if ($fin < $inicio) {
            throw new \InvalidArgumentException('La fecha final debe ser posterior a la fecha inicial.');
        }

        $precio = static function (mixed $valor): ?float {
            if ($valor === '' || $valor === null) {
                return null;
            }
            if (!is_numeric($valor) || !is_finite((float) $valor) || (float) $valor < 0) {
                throw new \InvalidArgumentException('El precio de la campaña no es válido.');
            }

            return (float) $valor;
        };

        return [
            'nombre' => $nombre,
            'ubicacion' => $ubicacion,
            'titulo' => $titulo,
            'descripcion' => mb_substr($texto($datos['descripcion'] ?? null), 0, 500),
            'texto_boton' => mb_substr($texto($datos['texto_boton'] ?? null, 'Ver oferta') ?: 'Ver oferta', 0, 80),
            'url_boton' => mb_substr($texto($datos['url_boton'] ?? null, 'catalog') ?: 'catalog', 0, 255),
            'url_imagen' => mb_substr($texto($datos['url_imagen'] ?? null), 0, 255) ?: null,
            'precio_anterior' => $precio($datos['precio_anterior'] ?? null),
            'precio_oferta' => $precio($datos['precio_oferta'] ?? null),
            'inicia_en' => $inicio->format('Y-m-d H:i:s'),
            'finaliza_en' => $fin->format('Y-m-d H:i:s'),
            'activo' => isset($datos['activo']) ? 1 : 0,
        ];
    }
}

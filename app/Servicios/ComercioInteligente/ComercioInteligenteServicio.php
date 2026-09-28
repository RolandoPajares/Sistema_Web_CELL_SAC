<?php

declare(strict_types=1);

namespace App\Servicios\ComercioInteligente;

use App\Servicios\Productos\ProductoServicio;

final class ComercioInteligenteServicio
{
    public function __construct(private ProductoServicio $productos)
    {
    }

    /** @return array<int,array<string,mixed>> */
    public function recomendar(float $presupuesto, string $uso, string $prioridad): array
    {
        $pesos = [
            'gaming' => ['rendimiento' => 1.7, 'bateria' => 1.1, 'camara' => .4, 'valor' => 1.2],
            'camera' => ['rendimiento' => .6, 'bateria' => .7, 'camara' => 1.8, 'valor' => 1.0],
            'study' => ['rendimiento' => 1.0, 'bateria' => 1.4, 'camara' => .5, 'valor' => 1.5],
            'work' => ['rendimiento' => 1.4, 'bateria' => 1.3, 'camara' => .6, 'valor' => 1.1],
            'social' => ['rendimiento' => .8, 'bateria' => 1.0, 'camara' => 1.5, 'valor' => 1.2],
        ][$uso] ?? ['rendimiento' => 1, 'bateria' => 1, 'camara' => 1, 'valor' => 1];
        if (isset($pesos[$prioridad])) {
            $pesos[$prioridad] += .9;
        }

        $resultados = [];
        foreach ($this->celulares() as $producto) {
            if ($presupuesto > 0 && (float) $producto['precio'] > $presupuesto) {
                continue;
            }
            $puntajes = $this->puntajes($producto);
            $totalPonderado = 0.0;
            $divisor = 0.0;
            foreach ($pesos as $clave => $peso) {
                $totalPonderado += $puntajes[$clave] * $peso;
                $divisor += $peso;
            }
            $producto['puntajes_inteligentes'] = $puntajes;
            $producto['coincidencia'] = (int) round($totalPonderado / max(1, $divisor));
            $producto['razones'] = $this->razones($puntajes, $uso);
            $resultados[] = $producto;
        }
        usort(
            $resultados,
            static fn (array $productoA, array $productoB): int => $productoB['coincidencia'] <=> $productoA['coincidencia']
        );

        return array_slice($resultados, 0, 5);
    }

    /**
     * @param array<int, int> $idsProductos
     * @return array<int, array<string, mixed>>
     */
    public function comparar(array $idsProductos): array
    {
        $resultado = [];
        foreach (array_slice(array_values(array_unique($idsProductos)), 0, 3) as $idProducto) {
            $producto = $this->productos->buscarActivo((int) $idProducto);
            if ($producto && stripos((string) $producto['categoria'], 'cel') !== false) {
                $producto['puntajes_inteligentes'] = $this->puntajes($producto);
                $resultado[] = $producto;
            }
        }

        return $resultado;
    }

    /** @return array<string,mixed> */
    public function optimizar(float $presupuesto, string $objetivo): array
    {
        $presupuesto = max(0, $presupuesto);
        $celulares = $this->celulares();
        usort(
            $celulares,
            static fn (array $productoA, array $productoB): int =>
                ((float) $productoA['precio']) <=> ((float) $productoB['precio'])
        );
        if ($objetivo === 'margin') {
            usort(
                $celulares,
                fn (array $productoA, array $productoB): int =>
                    $this->puntajes($productoB)['valor'] <=> $this->puntajes($productoA)['valor']
            );
        } elseif ($objetivo === 'premium') {
            usort(
                $celulares,
                static fn (array $productoA, array $productoB): int =>
                    ((float) $productoB['precio']) <=> ((float) $productoA['precio'])
            );
        }

        $lineas = [];
        $invertido = 0.0;
        $usados = [];
        $iteracion = 0;
        while ($iteracion++ < 60 && $celulares) {
            $agregado = false;
            foreach ($celulares as $producto) {
                $precio = (float) $producto['precio'];
                if ($precio <= 0 || $invertido + $precio > $presupuesto) {
                    continue;
                }
                if ($objetivo === 'variety' && isset($usados[$producto['id']])) {
                    continue;
                }
                $idProducto = (int) $producto['id'];
                if (!isset($lineas[$idProducto])) {
                    $lineas[$idProducto] = ['producto' => $producto, 'cantidad' => 0, 'subtotal' => 0.0];
                }
                $lineas[$idProducto]['cantidad']++;
                $lineas[$idProducto]['subtotal'] += $precio;
                $usados[$idProducto] = true;
                $invertido += $precio;
                $agregado = true;
                if ($objetivo === 'variety' && count($usados) >= min(5, count($celulares))) {
                    break 2;
                }
                if ($objetivo === 'premium') {
                    break;
                }
            }
            if (!$agregado) {
                break;
            }
        }
        $margen = 0.0;
        foreach ($lineas as $linea) {
            $margen += $linea['subtotal'] * ($objetivo === 'margin' ? .20 : .15);
        }

        return [
            'articulos' => array_values($lineas),
            'invertido' => $invertido,
            'saldo' => max(0, $presupuesto - $invertido),
            'margen_estimado' => $margen,
        ];
    }

    /**
     * @param array<int, array<string, mixed>> $articulosCarrito
     * @return array<int, array<string, mixed>>
     */
    public function sugerenciasCarrito(array $articulosCarrito): array
    {
        if (!$articulosCarrito) {
            return [];
        }
        $tieneCelular = false;
        $marcas = [];
        $idsCarrito = [];
        foreach ($articulosCarrito as $articulo) {
            $idsCarrito[(int) $articulo['id']] = true;
            if (stripos((string) $articulo['categoria'], 'cel') !== false) {
                $tieneCelular = true;
                $marcas[(string) $articulo['marca']] = true;
            }
        }
        $sugerencias = [];
        foreach ($this->productos->todosActivos() as $producto) {
            if (isset($idsCarrito[(int) $producto['id']])) {
                continue;
            }
            $esAccesorio = stripos((string) $producto['categoria'], 'aud') !== false
                || stripos((string) $producto['categoria'], 'acc') !== false;
            if ($tieneCelular && $esAccesorio) {
                $producto['compatibilidad'] = isset($marcas[(string) $producto['marca']])
                    ? 'Compatibilidad alta por marca'
                    : 'Accesorio universal / verificar modelo';
                $sugerencias[] = $producto;
            }
        }
        if (!$sugerencias && $tieneCelular) {
            foreach ($this->productos->todosActivos() as $producto) {
                if (
                    !isset($idsCarrito[(int) $producto['id']])
                    && stripos((string) $producto['categoria'], 'aud') !== false
                ) {
                    $producto['compatibilidad'] = 'Complemento recomendado para tu compra';
                    $sugerencias[] = $producto;
                }
            }
        }

        return array_slice($sugerencias, 0, 4);
    }

    /** @return array<string,mixed> */
    public function responder(string $mensaje): array
    {
        $texto = strtolower(trim($mensaje));
        $textoNormalizado = strtr(
            $texto,
            ['á' => 'a', 'é' => 'e', 'í' => 'i', 'ó' => 'o', 'ú' => 'u', 'ñ' => 'n']
        );

        if (preg_match('/\b(hola|buenas|hey|hi|buenos dias|buenas tardes|buenas noches)\b/', $textoNormalizado)) {
            return [
                'mensaje' => '¡Hola! Soy MD Assistant. Puedo recomendarte celulares por presupuesto y uso, orientarte sobre cámara, batería o rendimiento y mostrarte opciones disponibles del catálogo. ¿Qué estás buscando?',
                'productos' => [],
            ];
        }

        if (preg_match('/\b(gracias|thank)\b/', $textoNormalizado)) {
            return [
                'mensaje' => '¡Con gusto! Puedes seguir preguntándome. Si me indicas tu presupuesto y el uso principal que le darás al celular, puedo afinar mucho más la recomendación.',
                'productos' => [],
            ];
        }

        $presupuesto = 0.0;
        if (preg_match('/(?:s\/?\.?\s*)?(\d{3,5})/i', $textoNormalizado, $coincidencia)) {
            $presupuesto = (float) $coincidencia[1];
        }

        $uso = str_contains($textoNormalizado, 'gaming')
            || str_contains($textoNormalizado, 'juego')
            || str_contains($textoNormalizado, 'jugar') ? 'gaming'
            : (str_contains($textoNormalizado, 'camara')
                || str_contains($textoNormalizado, 'foto')
                || str_contains($textoNormalizado, 'video') ? 'camera'
                : (str_contains($textoNormalizado, 'estudio')
                    || str_contains($textoNormalizado, 'univers')
                    || str_contains($textoNormalizado, 'clase') ? 'study'
                    : (str_contains($textoNormalizado, 'trabajo')
                        || str_contains($textoNormalizado, 'oficina') ? 'work' : 'social')));

        $prioridad = $uso === 'camera' ? 'camara' : ($uso === 'gaming' ? 'rendimiento' : 'valor');
        if (str_contains($textoNormalizado, 'bateria') || str_contains($textoNormalizado, 'duracion')) {
            $prioridad = 'bateria';
        }
        if (
            str_contains($textoNormalizado, 'barato')
            || str_contains($textoNormalizado, 'economico')
            || str_contains($textoNormalizado, 'calidad precio')
        ) {
            $prioridad = 'valor';
        }

        $marca = null;
        foreach (['samsung', 'xiaomi', 'honor', 'motorola', 'iphone', 'apple', 'oppo', 'realme'] as $candidata) {
            if (str_contains($textoNormalizado, $candidata)) {
                $marca = $candidata === 'apple' ? 'iphone' : $candidata;
                break;
            }
        }

        $productos = $this->recomendar($presupuesto, $uso, $prioridad);
        if ($marca !== null) {
            $filtrados = array_values(array_filter($productos, static function (array $producto) use ($marca): bool {
                $textoProducto = strtolower((string) (($producto['marca'] ?? '') . ' ' . ($producto['nombre'] ?? '')));

                return str_contains($textoProducto, $marca);
            }));
            if ($filtrados) {
                $productos = $filtrados;
            }
        }

        if (
            str_contains($textoNormalizado, 'mas barato')
            || str_contains($textoNormalizado, 'economico')
            || str_contains($textoNormalizado, 'barato')
        ) {
            usort(
                $productos,
                static fn (array $productoA, array $productoB): int =>
                    ((float) $productoA['precio']) <=> ((float) $productoB['precio'])
            );
        }

        if (!$productos) {
            return [
                'mensaje' => $presupuesto > 0
                    ? 'No encontré una coincidencia clara dentro de S/ ' . number_format($presupuesto, 0) . ' con esos criterios. Puedes subir un poco el presupuesto o decirme qué característica es indispensable.'
                    : 'No encontré una coincidencia clara con esa consulta. Prueba indicando presupuesto, marca o uso, por ejemplo: “Samsung para estudiar hasta S/1400”.',
                'productos' => [],
            ];
        }

        $criterios = [];
        if ($presupuesto > 0) {
            $criterios[] = 'dentro de S/ ' . number_format($presupuesto, 0);
        }
        if ($marca !== null) {
            $criterios[] = 'de ' . ucfirst($marca);
        }
        $descripcionCriterios = $criterios ? ' ' . implode(' y ', $criterios) : '';
        $principal = $productos[0];
        $respuesta = 'Encontré opciones' . $descripcionCriterios . ' orientadas a ' . $this->etiquetaUso($uso) . '. ';
        $respuesta .= 'Mi primera sugerencia es ' . ($principal['marca'] ?? '') . ' ' . ($principal['nombre'] ?? '');
        $respuesta .= ' por ' . number_format((float) ($principal['precio'] ?? 0), 2) . ' soles, con ';
        $respuesta .= (int) ($principal['coincidencia'] ?? 0) . '% de coincidencia según tus criterios.';
        $respuesta .= ' Puedes seguir preguntándome para cambiar presupuesto, marca o prioridad.';

        return ['mensaje' => $respuesta, 'productos' => array_slice($productos, 0, 3)];
    }

    /**
     * @param array<string, mixed> $producto
     * @return array<string, int>
     */
    public function puntajes(array $producto): array
    {
        $precio = (float) ($producto['precio'] ?? 0);
        $almacenamiento = (string) ($producto['almacenamiento'] ?? '');
        $nombre = strtolower(
            (string) ($producto['marca'] ?? '') . ' ' . (string) ($producto['nombre'] ?? '')
        );
        $puntajePremium = min(30, (int) round($precio / 140));
        $puntajeAlmacenamiento = str_contains($almacenamiento, '512')
            ? 16
            : (str_contains($almacenamiento, '256') ? 12 : 7);
        $rendimiento = min(
            98,
            55 + $puntajePremium + $puntajeAlmacenamiento
            + (str_contains($nombre, 'pro') || str_contains($nombre, 'ultra') ? 8 : 0)
        );
        $camara = min(
            98,
            58 + (str_contains($nombre, 'iphone') ? 18 : 0)
            + (str_contains($nombre, 'pro') ? 12 : 0) + $puntajePremium / 2
        );
        $bateria = min(
            97,
            68 + (str_contains($nombre, 'honor') || str_contains($nombre, 'redmi') ? 12 : 0)
            + ($precio < 1800 ? 5 : 0)
        );
        $valor = max(
            58,
            min(
                98,
                98 - (int) ($precio / 85) + $puntajeAlmacenamiento
                + (str_contains($nombre, 'redmi') || str_contains($nombre, 'a26') ? 10 : 0)
            )
        );

        return [
            'rendimiento' => (int) $rendimiento,
            'camara' => (int) $camara,
            'bateria' => (int) $bateria,
            'valor' => (int) $valor,
        ];
    }

    /** @return array<int,array<string,mixed>> */
    private function celulares(): array
    {
        return array_values(array_filter(
            $this->productos->todosActivos(),
            static fn (array $producto): bool =>
                stripos((string) $producto['categoria'], 'cel') !== false && (int) $producto['existencias'] > 0
        ));
    }

    /**
     * @param array<string, int> $puntajes
     * @return array<int, string>
     */
    private function razones(array $puntajes, string $uso): array
    {
        arsort($puntajes);
        $etiquetas = [
            'rendimiento' => 'Buen rendimiento',
            'camara' => 'Cámara competitiva',
            'bateria' => 'Buena autonomía',
            'valor' => 'Buena relación precio/beneficio',
        ];
        $razones = [];
        foreach (array_slice(array_keys($puntajes), 0, 3) as $clave) {
            $razones[] = $etiquetas[$clave] . ' (' . $puntajes[$clave] . '/100)';
        }

        return $razones;
    }

    private function etiquetaUso(string $uso): string
    {
        return [
            'gaming' => 'gaming',
            'camera' => 'fotografía',
            'study' => 'estudio',
            'work' => 'trabajo',
            'social' => 'redes sociales',
        ][$uso] ?? 'uso diario';
    }
}

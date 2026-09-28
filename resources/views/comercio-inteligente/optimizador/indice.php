<section class="smart-page">
    <div class="container">
        <div class="smart-hero">
            <span class="eyebrow">Modo emprendedor</span>
            <h1><i class="bi bi-cash-coin" aria-hidden="true"></i> Optimizador por presupuesto</h1>
            <p>El sistema arma automáticamente una propuesta de compra respetando tu presupuesto y objetivo comercial.</p>
        </div>

        <form class="smart-form panel" method="get">
            <label>
                Presupuesto
                <input class="input" type="number" name="budget" min="500" step="100" value="<?= e($presupuesto ?: '') ?>" placeholder="Ej. 10000">
            </label>
            <label>
                Objetivo
                <select name="goal">
                    <option value="variety" <?= $objetivo === 'variety' ? 'selected' : '' ?>>Más variedad</option>
                    <option value="units" <?= $objetivo === 'units' ? 'selected' : '' ?>>Más unidades</option>
                    <option value="margin" <?= $objetivo === 'margin' ? 'selected' : '' ?>>Mayor margen estimado</option>
                    <option value="premium" <?= $objetivo === 'premium' ? 'selected' : '' ?>>Equipos premium</option>
                </select>
            </label>
            <button class="btn btn-primary">Generar propuesta</button>
        </form>

        <?php if ($propuesta): ?>
            <div class="panel proposal">
                <div class="proposal-kpis">
                    <div><span>Inversión</span><strong><?= money($propuesta['invertido']) ?></strong></div>
                    <div><span>Saldo</span><strong><?= money($propuesta['saldo']) ?></strong></div>
                    <div><span>Margen estimado</span><strong><?= money($propuesta['margen_estimado']) ?></strong></div>
                </div>
                <table class="table">
                    <thead>
                        <tr><th>Producto</th><th>Cant.</th><th>Precio</th><th>Subtotal</th></tr>
                    </thead>
                    <tbody>
                        <?php foreach ($propuesta['articulos'] as $linea): ?>
                            <?php $producto = $linea['producto']; ?>
                            <tr>
                                <td><?= e($producto['marca'] . ' ' . $producto['nombre']) ?></td>
                                <td><?= (int) $linea['cantidad'] ?></td>
                                <td><?= money($producto['precio']) ?></td>
                                <td><?= money($linea['subtotal']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <p class="product-note">Margen referencial calculado para simulación académica; no constituye una garantía de rentabilidad.</p>
            </div>
        <?php endif; ?>
    </div>
</section>

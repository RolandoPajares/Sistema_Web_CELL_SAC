<h1>Usuarios</h1>
<div class="panel table-wrap">
    <table class="table">
        <tr>
            <th>ID</th>
            <th>Nombre</th>
            <th>Correo</th>
            <th>Rol</th>
            <th>Registro</th>
        </tr>
        <?php foreach ($usuarios as $usuario): ?>
            <tr>
                <td><?= (int) $usuario['id'] ?></td>
                <td><?= e($usuario['nombre']) ?></td>
                <td><?= e($usuario['correo']) ?></td>
                <td><?= e($usuario['rol']) ?></td>
                <td><?= e($usuario['creado_en']) ?></td>
            </tr>
        <?php endforeach; ?>
    </table>
</div>

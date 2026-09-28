<div class="auth-wrap panel">
    <h1>Cerrar sesión</h1>
    <p>Confirma que quieres cerrar tu sesión actual.</p>
    <form method="post" action="<?= e(url('logout')) ?>">
        <?= csrf_field() ?>
        <button class="btn btn-primary" style="width:100%">Cerrar sesión</button>
    </form>
</div>
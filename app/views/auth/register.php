<?php require __DIR__ . '/../layouts/header.php'; ?>

<div class="row justify-content-center mt-4">
    <div class="col-md-5">
        <div class="card shadow-sm border-0">
            <div class="card-body p-4">
                <h4 class="card-title text-center mb-1 fw-bold text-primary">Crear una cuenta</h4>
                <p class="text-center text-muted small mb-4">Registro para dueños de mascotas</p>

                <?php if (!empty($errores)): ?>
                    <div class="alert alert-danger py-2">
                        <ul class="mb-0 ps-3">
                            <?php foreach ($errores as $e): ?>
                                <li><?= htmlspecialchars($e) ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <form id="registroForm" action="index.php?action=registro_guardar" method="POST" novalidate>
                    <div class="mb-3">
                        <label for="nombre" class="form-label">Nombre completo</label>
                        <input type="text" class="form-control" id="nombre" name="nombre"
                               value="<?= htmlspecialchars($old['nombre'] ?? '') ?>"
                               minlength="3" maxlength="100" required>
                        <div class="invalid-feedback">Ingresa tu nombre (mínimo 3 caracteres).</div>
                    </div>

                    <div class="mb-3">
                        <label for="correo" class="form-label">Correo electrónico</label>
                        <input type="email" class="form-control" id="correo" name="correo"
                               value="<?= htmlspecialchars($old['correo'] ?? '') ?>"
                               maxlength="100" required>
                        <div class="invalid-feedback">Ingresa un correo válido.</div>
                    </div>

                    <div class="mb-3">
                        <label for="password" class="form-label">Contraseña</label>
                        <input type="password" class="form-control" id="password" name="password"
                               minlength="6" required>
                        <div class="form-text">Mínimo 6 caracteres.</div>
                        <div class="invalid-feedback">La contraseña debe tener al menos 6 caracteres.</div>
                    </div>

                    <div class="mb-3">
                        <label for="password_confirm" class="form-label">Confirmar contraseña</label>
                        <input type="password" class="form-control" id="password_confirm" name="password_confirm"
                               minlength="6" required>
                        <div class="invalid-feedback" id="msgConfirm">Las contraseñas no coinciden.</div>
                    </div>

                    <button type="submit" class="btn btn-primary w-100 fw-semibold">Crear cuenta</button>
                </form>

                <div class="text-center mt-3">
                    <a href="index.php?action=login">Volver al inicio de sesión</a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require __DIR__ . '/../layouts/footer.php'; ?>

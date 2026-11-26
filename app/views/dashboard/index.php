<?php require __DIR__ . '/../layouts/header.php'; ?>

<!-- Alertas de respuesta del RF03 -->
<?php if (isset($_GET['success']) &&$_GET['success'] === 'cita_agendada'): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="bi bi-check-circle-fill me-2"></i> ¡Cita solicitada correctamente! Su estado actual es <strong>Pendiente</strong>.
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
    </div>
<?php endif; ?>

<?php if (isset($_GET['error'])): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="bi bi-exclamation-triangle-fill me-2"></i>
        <?php
            echo match($_GET['error']) {
                'horario_ocupado' => 'El horario seleccionado acaba de ser ocupado. Por favor elige otro.',
                'campos_vacios'   => 'Por favor completa todos los campos obligatorios.',
                default           => 'Ocurrió un error al registrar la cita.'
            };
        ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
    </div>
<?php endif; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h3 class="fw-bold mb-0">Panel de Gestión de Citas</h3>
        <p class="text-muted small mb-0">Vista adaptada al rol: <strong><?= htmlspecialchars($usuario['rol']) ?></strong></p>
    </div>

    <?php if ($usuario['rol'] === 'cliente'): ?>
        <div class="d-flex gap-2">
            <!-- TODO [Sprint 2 - RF02]: Conectar este botón a un modal o vista para registrar mascota -->
            <button class="btn btn-outline-primary btn-sm" onclick="mostrarAlertaSprint('Sprint 2 - RF02: Formulario de nueva mascota')">
                <i class="bi bi-plus-circle me-1"></i> Nueva Mascota
            </button>
            <!-- RF03: Botón conectado al Modal -->
            <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#modalSolicitarCita">
                <i class="bi bi-calendar-plus me-1"></i> Solicitar Cita
            </button>
        </div>
    <?php endif; ?>
</div>

<?php if ($usuario['rol'] === 'cliente'): ?>
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-header bg-white fw-semibold">
            <i class="bi bi-paw me-1 text-primary"></i> Mis Mascotas Registradas
        </div>
        <div class="card-body">
            <?php if (empty($mascotas)): ?>
                <p class="text-muted mb-0">No tienes mascotas registradas aún.</p>
            <?php else: ?>
                <div class="row g-3">
                    <?php foreach ($mascotas as$m): ?>
                        <div class="col-md-4">
                            <div class="border rounded p-3 bg-light">
                                <h6 class="fw-bold mb-1"><?= htmlspecialchars($m['nombre']) ?></h6>
                                <span class="badge bg-secondary"><?= htmlspecialchars($m['especie']) ?></span>
                                <span class="small text-muted ms-1"><?= htmlspecialchars($m['raza']) ?> • <?= (int)$m['edad'] ?> años</span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
<?php endif; ?>

<div class="card shadow-sm border-0">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <span class="fw-semibold"><i class="bi bi-calendar-check me-1 text-primary"></i> Listado de Citas</span>
        <select id="filtroEstado" class="form-select form-select-sm w-auto">
            <option value="todas">Todos los estados</option>
            <option value="pendiente">Pendiente</option>
            <option value="confirmada">Confirmada</option>
            <option value="atendida">Atendida</option>
            <option value="cancelada">Cancelada</option>
        </select>
    </div>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0" id="tablaCitas">
            <thead class="table-light">
                <tr>
                    <th>Fecha y Hora</th>
                    <th>Mascota</th>
                    <?php if ($usuario['rol'] !== 'cliente'): ?>
                        <th>Dueño</th>
                    <?php endif; ?>
                    <th>Veterinario</th>
                    <th>Motivo</th>
                    <th>Estado</th>
                    <th>Acciones / Nota</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($citas)): ?>
                    <tr><td colspan="7" class="text-center text-muted py-4">No hay citas registradas.</td></tr>
                <?php else: ?>
                    <?php foreach ($citas as$c): ?>
                        <?php
                            $badgeClass = match($c['estado']) {
                                'pendiente'  => 'bg-warning text-dark',
                                'confirmada' => 'bg-info text-dark',
                                'atendida'   => 'bg-success',
                                'cancelada'  => 'bg-danger',
                                default      => 'bg-secondary'
                            };
                        ?>
                        <tr data-estado="<?= htmlspecialchars($c['estado']) ?>">
                            <td><?= date('d/m/Y H:i', strtotime($c['fecha_hora'])) ?></td>
                            <td>
                                <strong><?= htmlspecialchars($c['mascota']) ?></strong>
                                <div class="small text-muted"><?= htmlspecialchars($c['especie']) ?></div>
                            </td>
                            <?php if ($usuario['rol'] !== 'cliente'): ?>
                                <td><?= htmlspecialchars($c['dueno']) ?></td>
                            <?php endif; ?>
                            <td><?= htmlspecialchars($c['veterinario']) ?></td>
                            <td><?= htmlspecialchars($c['motivo']) ?></td>
                            <td><span class="badge <?= $badgeClass ?> text-uppercase"><?= htmlspecialchars($c['estado']) ?></span></td>
                            <td>
                                <?php if ($usuario['rol'] === 'recepcionista'): ?>
                                    <!-- TODO [Sprint 3 - RF05]: Conectar botones de confirmar / cancelar -->
                                    <button class="btn btn-sm btn-outline-success" onclick="mostrarAlertaSprint('Sprint 3 - RF05: Confirmar cita #<?= $c['id'] ?>')">Confirmar</button>
                                    <button class="btn btn-sm btn-outline-danger" onclick="mostrarAlertaSprint('Sprint 3 - RF05: Cancelar cita #<?= $c['id'] ?>')">Cancelar</button>
                                <?php elseif ($usuario['rol'] === 'veterinario'): ?>
                                    <!-- TODO [Sprint 3 - RF06]: Conectar modal para registrar diagnóstico y marcar atendida -->
                                    <button class="btn btn-sm btn-outline-primary" onclick="mostrarAlertaSprint('Sprint 3 - RF06: Atender cita #<?= $c['id'] ?>')">Atender</button>
                                <?php else: ?>
                                    <span class="small text-muted"><?= htmlspecialchars($c['nota_clinica'] ?? 'Sin notas') ?></span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- MODAL RF03: Solicitar Cita (Solo visible para clientes) -->
<?php if ($usuario['rol'] === 'cliente'): ?>
<div class="modal fade" id="modalSolicitarCita" tabindex="-1" aria-labelledby="modalSolicitarCitaLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold" id="modalSolicitarCitaLabel">
                    <i class="bi bi-calendar-plus text-primary me-2"></i>Solicitar Nueva Cita
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <form action="index.php?action=agendarCita" method="POST">
                <div class="modal-body">
                    <?php if (empty($mascotas)): ?>
                        <div class="alert alert-warning mb-0">
                            Debes registrar al menos una mascota antes de solicitar una cita.
                        </div>
                    <?php else: ?>
                        <!-- 1. Mascota -->
                        <div class="mb-3">
                            <label for="id_mascotas" class="form-label fw-semibold">Mascota</label>
                            <select class="form-select" id="id_mascotas" name="id_mascotas" required>
                                <option value="">Seleccione una mascota...</option>
                                <?php foreach ($mascotas as$m): ?>
                                    <option value="<?= (int)$m['id'] ?>">
                                        <?= htmlspecialchars($m['nombre']) ?> (<?= htmlspecialchars($m['especie']) ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- 2. Veterinario -->
                        <div class="mb-3">
                            <label for="id_veterinario" class="form-label fw-semibold">Veterinario</label>
                            <select class="form-select" id="id_veterinario" name="id_veterinario" required>
                                <option value="">Seleccione un veterinario...</option>
                                <?php foreach ($veterinarios as$v): ?>
                                    <option value="<?= (int)$v['id'] ?>">
                                        <?= htmlspecialchars($v['nombre']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- 3. Fecha -->
                        <div class="mb-3">
                            <label for="fecha" class="form-label fw-semibold">Fecha</label>
                            <input type="date" class="form-control" id="fecha" name="fecha" min="<?= date('Y-m-d') ?>" required>
                        </div>

                        <!-- 4. Hora -->
                        <div class="mb-3">
                            <label for="hora" class="form-label fw-semibold">Hora Disponible</label>
                            <select class="form-select" id="hora" name="hora" required>
                                <option value="">Seleccione veterinario y fecha primero...</option>
                            </select>
                        </div>

                        <!-- 5. Motivo -->
                        <div class="mb-3">
                            <label for="motivo" class="form-label fw-semibold">Motivo de la consulta</label>
                            <textarea class="form-control" id="motivo" name="motivo" rows="3" placeholder="Describe brevemente el motivo..." required></textarea>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <?php if (!empty($mascotas)): ?>
                        <button type="submit" class="btn btn-primary">Guardar Cita</button>
                    <?php endif; ?>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endif; ?>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
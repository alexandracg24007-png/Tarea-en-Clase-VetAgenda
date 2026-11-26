<?php require __DIR__ . '/../layouts/header.php'; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h3 class="fw-bold mb-0">Panel de Gestión de Citas</h3>
        <p class="text-muted small mb-0">Vista adaptada al rol: <strong><?= htmlspecialchars($usuario['rol']) ?></strong></p>
    </div>

    <?php if ($usuario['rol'] === 'cliente'): ?>
        <div class="d-flex gap-2">
            <!-- [RF02] Registrar mascota -->
            <button class="btn btn-outline-primary btn-sm" data-bs-toggle="modal" data-bs-target="#modalMascota">
                <i class="bi bi-plus-circle me-1"></i> Nueva Mascota
            </button>
            <!-- [RF03] Solicitar cita (requiere al menos una mascota) -->
            <span <?= empty($mascotas) ? 'title="Primero registra una mascota"' : '' ?>>
                <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#modalCita" <?= empty($mascotas) ? 'disabled' : '' ?>>
                    <i class="bi bi-calendar-plus me-1"></i> Solicitar Cita
                </button>
            </span>
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
                    <?php foreach ($mascotas as $m): ?>
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
        <!-- TODO [Sprint 4]: Activar filtro dinámico en JS por estado -->
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
                    <?php foreach ($citas as $c): ?>
                        <?php
                            $badgeClass = match($c['estado']) {
                                'pendiente' => 'bg-warning text-dark',
                                'confirmada' => 'bg-info text-dark',
                                'atendida' => 'bg-success',
                                'cancelada' => 'bg-danger',
                                default => 'bg-secondary'
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

<?php if ($usuario['rol'] === 'cliente'): ?>
<?php
    $oldDatos = $old['datos'] ?? [];
    $val = fn($k, $d = '') => htmlspecialchars($oldDatos[$k] ?? $d);
    $especies = ['Perro', 'Gato', 'Ave', 'Conejo', 'Hámster', 'Reptil', 'Otro'];
?>
<!-- Si un formulario falló, el JS reabre este modal con los datos escritos -->
<div id="estadoModal" hidden
     data-abrir="<?= htmlspecialchars($old['modal'] ?? '') ?>"
     data-hora="<?= $val('hora') ?>"></div>

<!-- [RF02] Modal: nueva mascota -->
<div class="modal fade" id="modalMascota" tabindex="-1" aria-labelledby="tituloMascota" aria-hidden="true">
    <div class="modal-dialog">
        <form class="modal-content" id="mascotaForm" action="index.php?action=mascota_guardar" method="POST" novalidate>
            <div class="modal-header">
                <h5 class="modal-title" id="tituloMascota"><i class="bi bi-paw me-1"></i> Registrar mascota</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label for="m_nombre" class="form-label">Nombre</label>
                    <input type="text" class="form-control" id="m_nombre" name="nombre" maxlength="80"
                           value="<?= ($old['modal'] ?? '') === 'modalMascota' ? $val('nombre') : '' ?>" required>
                    <div class="invalid-feedback">Ingresa el nombre de la mascota.</div>
                </div>
                <div class="mb-3">
                    <label for="m_especie" class="form-label">Especie</label>
                    <select class="form-select" id="m_especie" name="especie" required>
                        <option value="">Selecciona...</option>
                        <?php foreach ($especies as $e): ?>
                            <option value="<?= $e ?>" <?= (($old['modal'] ?? '') === 'modalMascota' && ($oldDatos['especie'] ?? '') === $e) ? 'selected' : '' ?>><?= $e ?></option>
                        <?php endforeach; ?>
                    </select>
                    <div class="invalid-feedback">Selecciona una especie.</div>
                </div>
                <div class="row">
                    <div class="col-8 mb-3">
                        <label for="m_raza" class="form-label">Raza <span class="text-muted small">(opcional)</span></label>
                        <input type="text" class="form-control" id="m_raza" name="raza" maxlength="80" placeholder="Mestizo"
                               value="<?= ($old['modal'] ?? '') === 'modalMascota' ? $val('raza') : '' ?>">
                    </div>
                    <div class="col-4 mb-3">
                        <label for="m_edad" class="form-label">Edad (años)</label>
                        <input type="number" class="form-control" id="m_edad" name="edad" min="0" max="40" step="1"
                               value="<?= ($old['modal'] ?? '') === 'modalMascota' ? $val('edad', '0') : '0' ?>" required>
                        <div class="invalid-feedback">0 a 40.</div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button>
                <button type="submit" class="btn btn-primary">Guardar mascota</button>
            </div>
        </form>
    </div>
</div>

<!-- [RF03] Modal: solicitar cita -->
<div class="modal fade" id="modalCita" tabindex="-1" aria-labelledby="tituloCita" aria-hidden="true">
    <div class="modal-dialog">
        <form class="modal-content" id="citaForm" action="index.php?action=cita_guardar" method="POST" novalidate>
            <div class="modal-header">
                <h5 class="modal-title" id="tituloCita"><i class="bi bi-calendar-plus me-1"></i> Solicitar cita</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                <?php $esCita = ($old['modal'] ?? '') === 'modalCita'; ?>
                <div class="mb-3">
                    <label for="c_mascota" class="form-label">Mascota</label>
                    <select class="form-select" id="c_mascota" name="id_mascota" required>
                        <option value="">Selecciona una mascota...</option>
                        <?php foreach ($mascotas as $m): ?>
                            <option value="<?= (int)$m['id'] ?>" <?= ($esCita && (int)($oldDatos['id_mascota'] ?? 0) === (int)$m['id']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($m['nombre']) ?> (<?= htmlspecialchars($m['especie']) ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <div class="invalid-feedback">Selecciona tu mascota.</div>
                </div>
                <div class="mb-3">
                    <label for="c_vet" class="form-label">Veterinario</label>
                    <select class="form-select" id="c_vet" name="id_veterinario" required>
                        <option value="">Selecciona un veterinario...</option>
                        <?php foreach ($veterinarios as $v): ?>
                            <option value="<?= (int)$v['id'] ?>" <?= ($esCita && (int)($oldDatos['id_veterinario'] ?? 0) === (int)$v['id']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($v['nombre']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <div class="invalid-feedback">Selecciona un veterinario.</div>
                </div>
                <div class="row">
                    <div class="col-6 mb-3">
                        <label for="c_fecha" class="form-label">Fecha</label>
                        <input type="date" class="form-control" id="c_fecha" name="fecha"
                               value="<?= $esCita ? $val('fecha') : '' ?>" required>
                        <div class="invalid-feedback">Elige una fecha.</div>
                    </div>
                    <div class="col-6 mb-3">
                        <label for="c_hora" class="form-label">Hora disponible</label>
                        <select class="form-select" id="c_hora" name="hora" required disabled>
                            <option value="">Elige veterinario y fecha</option>
                        </select>
                        <div class="invalid-feedback">Selecciona una hora.</div>
                    </div>
                </div>
                <div id="avisoHoras" class="small text-muted mb-3"></div>
                <div class="mb-3">
                    <label for="c_motivo" class="form-label">Motivo de la consulta</label>
                    <textarea class="form-control" id="c_motivo" name="motivo" rows="2" minlength="5" maxlength="255" required><?= $esCita ? $val('motivo') : '' ?></textarea>
                    <div class="invalid-feedback">Describe el motivo (mínimo 5 caracteres).</div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button>
                <button type="submit" class="btn btn-primary">Solicitar cita</button>
            </div>
        </form>
    </div>
</div>
<?php endif; ?>

<?php require __DIR__ . '/../layouts/footer.php'; ?>

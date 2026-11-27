<?php require __DIR__ . '/../layouts/header.php'; ?>

<!-- Alertas por URL (RF03 Cliente, RF05 Recepcionista, RF06 Veterinario) -->
<?php if (isset($_GET['success'])): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="bi bi-check-circle-fill me-2"></i>
        <?php
            echo match($_GET['success']) {
                'cita_agendada'      => '¡Cita solicitada correctamente! Su estado actual es <strong>Pendiente</strong>.',
                'estado_actualizado' => 'El estado de la cita fue actualizado correctamente.',
                'cita_reagendada'    => 'La cita fue reagendada y confirmada correctamente.',
                'cita_atendida'      => 'La cita fue marcada como atendida y la nota clínica quedó guardada.',
                default              => 'Operación realizada con éxito.'
            };
        ?>
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
                'datos_invalidos' => 'Los datos enviados no son válidos.',
                default           => 'Ocurrió un error al procesar la solicitud.'
            };
        ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
    </div>
<?php endif; ?>

<!-- Alertas por Sesión (RF02 Registro de Mascota) -->
<?php if (!empty($_SESSION['exito'])): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="bi bi-check-circle-fill me-2"></i>
        <?= htmlspecialchars($_SESSION['exito']) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
    </div>
    <?php unset($_SESSION['exito']); ?>
<?php endif; ?>

<?php if (!empty($_SESSION['error'])): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="bi bi-exclamation-triangle-fill me-2"></i>
        <?= htmlspecialchars($_SESSION['error']) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
    </div>
    <?php unset($_SESSION['error']); ?>
<?php endif; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h3 class="fw-bold mb-0">Panel de Gestión de Citas</h3>
        <p class="text-muted small mb-0">Vista adaptada al rol: <strong><?= htmlspecialchars($usuario['rol']) ?></strong></p>
    </div>

    <?php if ($usuario['rol'] === 'cliente'): ?>
        <div class="d-flex gap-2">
            <!-- RF02: Botón conectado al Modal Nueva Mascota -->
            <button class="btn btn-outline-primary btn-sm" data-bs-toggle="modal" data-bs-target="#modalNuevaMascota">
                <i class="bi bi-plus-circle me-1"></i> Nueva Mascota
            </button>
            <!-- RF03: Botón conectado al Modal Solicitar Cita -->
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
                                    <?php if ($c['estado'] !== 'atendida' && $c['estado'] !== 'cancelada'): ?>
                                        <div class="d-flex flex-wrap gap-1">
                                            <?php if ($c['estado'] === 'pendiente'): ?>
                                                <form action="index.php?action=cambiarEstadoCita" method="POST" class="d-inline">
                                                    <input type="hidden" name="id_cita" value="<?= (int)$c['id'] ?>">
                                                    <input type="hidden" name="estado" value="confirmada">
                                                    <button type="submit" class="btn btn-sm btn-outline-success" title="Aceptar cita">
                                                        <i class="bi bi-check-lg"></i> Aceptar
                                                    </button>
                                                </form>
                                            <?php endif; ?>

                                            <button type="button"
                                                    class="btn btn-sm btn-outline-primary"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#modalReagendarCita"
                                                    data-id="<?= (int)$c['id'] ?>"
                                                    data-vet="<?= (int)$c['id_veterinario'] ?>"
                                                    data-mascota="<?= htmlspecialchars($c['mascota']) ?>"
                                                    data-vetnombre="<?= htmlspecialchars($c['veterinario']) ?>"
                                                    onclick="prepararReagendar(this)">
                                                <i class="bi bi-calendar-event"></i> Reagendar
                                            </button>

                                            <form action="index.php?action=cambiarEstadoCita" method="POST" class="d-inline" onsubmit="return confirm('¿Seguro que deseas cancelar esta cita?');">
                                                <input type="hidden" name="id_cita" value="<?= (int)$c['id'] ?>">
                                                <input type="hidden" name="estado" value="cancelada">
                                                <button type="submit" class="btn btn-sm btn-outline-danger" title="Cancelar cita">
                                                    <i class="bi bi-x-lg"></i> Cancelar
                                                </button>
                                            </form>
                                        </div>
                                    <?php else: ?>
                                        <span class="small text-muted">Sin acciones disponibles</span>
                                    <?php endif; ?>

                                <?php elseif ($usuario['rol'] === 'veterinario'): ?>
                                    <?php if ($c['estado'] === 'confirmada' || $c['estado'] === 'pendiente'): ?>
                                        <button type="button"
                                                class="btn btn-sm btn-outline-primary"
                                                data-bs-toggle="modal"
                                                data-bs-target="#modalAtenderCita"
                                                data-id="<?= (int)$c['id'] ?>"
                                                data-mascota="<?= htmlspecialchars($c['mascota']) ?>"
                                                onclick="prepararAtender(this)">
                                            <i class="bi bi-clipboard2-pulse"></i> Atender
                                        </button>
                                    <?php else: ?>
                                        <span class="small text-muted"><?= htmlspecialchars($c['nota_clinica'] ?? 'Sin notas') ?></span>
                                    <?php endif; ?>

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

<!-- MODALES CLIENTE (RF02 Nueva Mascota y RF03 Solicitar Cita) -->
<?php if ($usuario['rol'] === 'cliente'): ?>

<!-- MODAL RF02: Nueva Mascota -->
<div class="modal fade" id="modalNuevaMascota" tabindex="-1" aria-labelledby="modalNuevaMascotaLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold" id="modalNuevaMascotaLabel">
                    <i class="bi bi-plus-circle text-primary me-2"></i>Registrar Nueva Mascota
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <form action="index.php?action=registrar_mascota" method="POST">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="mascota_nombre" class="form-label fw-semibold">Nombre de la mascota</label>
                        <input type="text" class="form-control" id="mascota_nombre" name="nombre" placeholder="Ej: Max" required>
                    </div>
                    <div class="mb-3">
                        <label for="mascota_especie" class="form-label fw-semibold">Especie</label>
                        <select class="form-select" id="mascota_especie" name="especie" required>
                            <option value="">Seleccione...</option>
                            <option value="Perro">Perro</option>
                            <option value="Gato">Gato</option>
                            <option value="Ave">Ave</option>
                            <option value="Conejo">Conejo</option>
                            <option value="Otro">Otro</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="mascota_raza" class="form-label fw-semibold">Raza</label>
                        <input type="text" class="form-control" id="mascota_raza" name="raza" placeholder="Ej: Labrador, Siamés, Mestizo..." required>
                    </div>
                    <div class="mb-3">
                        <label for="mascota_edad" class="form-label fw-semibold">Edad (en años)</label>
                        <input type="number" class="form-control" id="mascota_edad" name="edad" min="0" max="40" placeholder="Ej: 2" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Guardar Mascota</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- MODAL RF03: Solicitar Cita -->
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
                        <div class="mb-3">
                            <label for="id_mascotas" class="form-label fw-semibold">Mascota</label>
                            <select class="form-select" id="id_mascotas" name="id_mascotas" required>
                                <option value="">Seleccione una mascota...</option>
                                <?php foreach ($mascotas as $m): ?>
                                    <option value="<?= (int)$m['id'] ?>">
                                        <?= htmlspecialchars($m['nombre']) ?> (<?= htmlspecialchars($m['especie']) ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="id_veterinario" class="form-label fw-semibold">Veterinario</label>
                            <select class="form-select" id="id_veterinario" name="id_veterinario" required>
                                <option value="">Seleccione un veterinario...</option>
                                <?php foreach ($veterinarios as $v): ?>
                                    <option value="<?= (int)$v['id'] ?>">
                                        <?= htmlspecialchars($v['nombre']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="fecha" class="form-label fw-semibold">Fecha</label>
                            <input type="date" class="form-control" id="fecha" name="fecha" min="<?= date('Y-m-d') ?>" required>
                        </div>

                        <div class="mb-3">
                            <label for="hora" class="form-label fw-semibold">Hora Disponible</label>
                            <select class="form-select" id="hora" name="hora" required>
                                <option value="">Seleccione veterinario y fecha primero...</option>
                            </select>
                        </div>

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

<!-- MODAL RF05: Reagendar Cita (Solo visible para recepcionista) -->
<?php if ($usuario['rol'] === 'recepcionista'): ?>
<div class="modal fade" id="modalReagendarCita" tabindex="-1" aria-labelledby="modalReagendarCitaLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold" id="modalReagendarCitaLabel">
                    <i class="bi bi-calendar-event text-primary me-2"></i>Reagendar Cita
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <form action="index.php?action=reagendarCita" method="POST">
                <div class="modal-body">
                    <input type="hidden" name="id_cita" id="reagendar_id_cita">
                    <input type="hidden" name="id_veterinario" id="reagendar_id_veterinario">

                    <div class="alert alert-light border mb-3 small">
                        <div><strong>Mascota:</strong> <span id="reagendar_info_mascota"></span></div>
                        <div><strong>Veterinario:</strong> <span id="reagendar_info_vet"></span></div>
                    </div>

                    <div class="mb-3">
                        <label for="reagendar_fecha" class="form-label fw-semibold">Nueva Fecha</label>
                        <input type="date" class="form-control" id="reagendar_fecha" name="fecha" min="<?= date('Y-m-d') ?>" onchange="cargarHorasReagendar()" required>
                    </div>

                    <div class="mb-3">
                        <label for="reagendar_hora" class="form-label fw-semibold">Nueva Hora Disponible</label>
                        <select class="form-select" id="reagendar_hora" name="hora" required>
                            <option value="">Seleccione una fecha primero...</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                    <button type="submit" class="btn btn-primary">Guardar Nuevo Horario</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function prepararReagendar(btn) {
    document.getElementById('reagendar_id_cita').value = btn.getAttribute('data-id');
    document.getElementById('reagendar_id_veterinario').value = btn.getAttribute('data-vet');
    document.getElementById('reagendar_info_mascota').textContent = btn.getAttribute('data-mascota');
    document.getElementById('reagendar_info_vet').textContent = btn.getAttribute('data-vetnombre');
    document.getElementById('reagendar_fecha').value = '';
    document.getElementById('reagendar_hora').innerHTML = '<option value="">Seleccione una fecha primero...</option>';
}

async function cargarHorasReagendar() {
    const idVeterinario = document.getElementById('reagendar_id_veterinario').value;
    const fecha = document.getElementById('reagendar_fecha').value;
    const selectHora = document.getElementById('reagendar_hora');

    if (!idVeterinario || !fecha) return;

    selectHora.innerHTML = '<option value="">Cargando horarios...</option>';

    try {
        const response = await fetch(`index.php?action=horasDisponibles&id_veterinario=${encodeURIComponent(idVeterinario)}&fecha=${encodeURIComponent(fecha)}`);
        const horas = await response.json();

        selectHora.innerHTML = '';

        if (horas.length === 0) {
            selectHora.innerHTML = '<option value="">No hay horarios disponibles</option>';
        } else {
            selectHora.innerHTML = '<option value="">Seleccione una hora...</option>';
            horas.forEach(hora => {
                const option = document.createElement('option');
                option.value = hora;
                option.textContent = hora;
                selectHora.appendChild(option);
            });
        }
    } catch (error) {
        console.error('Error al cargar horas para reagendar:', error);
        selectHora.innerHTML = '<option value="">Error al cargar horarios</option>';
    }
}
</script>
<?php endif; ?>

<!-- MODAL RF06: Atender Cita y Registrar Nota Clínica (Solo veterinarios) -->
<?php if ($usuario['rol'] === 'veterinario'): ?>
<div class="modal fade" id="modalAtenderCita" tabindex="-1" aria-labelledby="modalAtenderCitaLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="index.php?action=atenderCita" method="POST">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold" id="modalAtenderCitaLabel">
                        <i class="bi bi-clipboard2-pulse text-primary me-2"></i>Registrar Atención Clínica
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="id_cita" id="atender_id_cita">
                    <p class="small text-muted mb-3">
                        Paciente: <strong id="atender_info_mascota" class="text-dark"></strong>
                    </p>
                    <div class="mb-3">
                        <label for="nota_clinica" class="form-label">Diagnóstico / Tratamiento *</label>
                        <textarea class="form-control" id="nota_clinica" name="nota_clinica" rows="4" placeholder="Escribe aquí el diagnóstico y el tratamiento indicado..." required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-success">Guardar y Marcar Atendida</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function prepararAtender(btn) {
    document.getElementById('atender_id_cita').value = btn.getAttribute('data-id');
    document.getElementById('atender_info_mascota').textContent = btn.getAttribute('data-mascota');
    document.getElementById('nota_clinica').value = '';
}
</script>
<?php endif; ?>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
document.addEventListener('DOMContentLoaded', () => {
    // Validación Bootstrap del formulario de login
    const loginForm = document.getElementById('loginForm');
    if (loginForm) {
        loginForm.addEventListener('submit', (event) => {
            if (!loginForm.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }
            loginForm.classList.add('was-validated');
        });
    }

    // Filtro rápido por estado en tabla de citas (Base funcional para Sprint 4)
    const filtroEstado = document.getElementById('filtroEstado');
    const tablaCitas = document.getElementById('tablaCitas');

    if (filtroEstado && tablaCitas) {
        filtroEstado.addEventListener('change', () => {
            const valor = filtroEstado.value;
            const filas = tablaCitas.querySelectorAll('tbody tr[data-estado]');
            filas.forEach(fila => {
                if (valor === 'todas' || fila.dataset.estado === valor) {
                    fila.style.display = '';
                } else {
                    fila.style.display = 'none';
                }
            });
        });
    }

    // [Sprint 2 - RF03]: Cargar horas disponibles dinámicamente al elegir veterinario y fecha (Cliente)
    const selectVeterinario = document.getElementById('id_veterinario');
    const inputFecha = document.getElementById('fecha');
    const selectHora = document.getElementById('hora');

    if (selectVeterinario && inputFecha && selectHora) {
        const cargarHorasDisponibles = async () => {
            const idVeterinario = selectVeterinario.value;
            const fecha = inputFecha.value;

            if (!idVeterinario || !fecha) {
                selectHora.innerHTML = '<option value="">Seleccione veterinario y fecha primero...</option>';
                return;
            }

            selectHora.innerHTML = '<option value="">Cargando horarios...</option>';

            try {
                const response = await fetch(`index.php?action=horasDisponibles&id_veterinario=${encodeURIComponent(idVeterinario)}&fecha=${encodeURIComponent(fecha)}`);
                const horas = await response.json();

                selectHora.innerHTML = '';

                if (horas.length === 0) {
                    selectHora.innerHTML = '<option value="">No hay horarios disponibles para esta fecha</option>';
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
                console.error('Error al cargar horarios:', error);
                selectHora.innerHTML = '<option value="">Error al cargar horarios</option>';
            }
        };

        selectVeterinario.addEventListener('change', cargarHorasDisponibles);
        inputFecha.addEventListener('change', cargarHorasDisponibles);
    }

    // [Sprint 3 - RF05]: Modal Reagendar Cita (Recepcionista)
    const modalReagendar = document.getElementById('modalReagendarCita');
    const reagendarIdCita = document.getElementById('reagendar_id_cita');
    const reagendarIdVet = document.getElementById('reagendar_id_veterinario');
    const reagendarInfoMascota = document.getElementById('reagendar_info_mascota');
    const reagendarInfoVet = document.getElementById('reagendar_info_vet');
    const reagendarFecha = document.getElementById('reagendar_fecha');
    const reagendarHora = document.getElementById('reagendar_hora');

    if (modalReagendar && reagendarFecha && reagendarHora) {
        modalReagendar.addEventListener('show.bs.modal', (event) => {
            const btn = event.relatedTarget;
            if (!btn) return;

            reagendarIdCita.value = btn.getAttribute('data-id');
            reagendarIdVet.value = btn.getAttribute('data-vet');
            reagendarInfoMascota.textContent = btn.getAttribute('data-mascota');
            reagendarInfoVet.textContent = btn.getAttribute('data-vetnombre');
            reagendarFecha.value = '';
            reagendarHora.innerHTML = '<option value="">Seleccione una fecha primero...</option>';
        });

        reagendarFecha.addEventListener('change', async () => {
            const idVeterinario = reagendarIdVet.value;
            const fecha = reagendarFecha.value;

            if (!idVeterinario || !fecha) return;

            reagendarHora.innerHTML = '<option value="">Cargando horarios...</option>';

            try {
                const response = await fetch(`index.php?action=horasDisponibles&id_veterinario=${encodeURIComponent(idVeterinario)}&fecha=${encodeURIComponent(fecha)}`);
                const horas = await response.json();

                reagendarHora.innerHTML = '';

                if (horas.length === 0) {
                    reagendarHora.innerHTML = '<option value="">No hay horarios disponibles</option>';
                } else {
                    reagendarHora.innerHTML = '<option value="">Seleccione una hora...</option>';
                    horas.forEach(hora => {
                        const option = document.createElement('option');
                        option.value = hora;
                        option.textContent = hora;
                        reagendarHora.appendChild(option);
                    });
                }
            } catch (error) {
                console.error('Error al cargar horas para reagendar:', error);
                reagendarHora.innerHTML = '<option value="">Error al cargar horarios</option>';
            }
        });
    }
});

// Función auxiliar para guiar a los estudiantes en los componentes pendientes
function mostrarAlertaSprint(mensaje) {
    alert('Tarea pendiente por desarrollar:\n' + mensaje);
}
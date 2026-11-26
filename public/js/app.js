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

    // [RF01] Validación del formulario de registro
    const registroForm = document.getElementById('registroForm');
    if (registroForm) {
        const pass = document.getElementById('password');
        const pass2 = document.getElementById('password_confirm');

        const validarCoincidencia = () => {
            // setCustomValidity marca el campo como inválido si no coinciden
            pass2.setCustomValidity(pass.value !== pass2.value ? 'No coinciden' : '');
        };
        pass.addEventListener('input', validarCoincidencia);
        pass2.addEventListener('input', validarCoincidencia);

        registroForm.addEventListener('submit', (event) => {
            validarCoincidencia();
            if (!registroForm.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }
            registroForm.classList.add('was-validated');
        });
    }

    // [RF02] Validación del formulario de nueva mascota
    const mascotaForm = document.getElementById('mascotaForm');
    if (mascotaForm) {
        mascotaForm.addEventListener('submit', (event) => {
            if (!mascotaForm.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }
            mascotaForm.classList.add('was-validated');
        });
    }

    // [RF03] Formulario de solicitud de cita con horas disponibles dinámicas
    const citaForm = document.getElementById('citaForm');
    if (citaForm) {
        const selVet = document.getElementById('c_vet');
        const inpFecha = document.getElementById('c_fecha');
        const selHora = document.getElementById('c_hora');
        const aviso = document.getElementById('avisoHoras');
        const estado = document.getElementById('estadoModal');
        let horaPreseleccionada = estado ? estado.dataset.hora : '';

        // No permitir fechas pasadas (se usa la fecha local del navegador)
        const hoy = new Date();
        const yyyy = hoy.getFullYear();
        const mm = String(hoy.getMonth() + 1).padStart(2, '0');
        const dd = String(hoy.getDate()).padStart(2, '0');
        inpFecha.min = `${yyyy}-${mm}-${dd}`;

        const resetHoras = (texto) => {
            selHora.innerHTML = `<option value="">${texto}</option>`;
            selHora.disabled = true;
        };

        const cargarHoras = async () => {
            aviso.textContent = '';
            if (!selVet.value || !inpFecha.value) {
                resetHoras('Elige veterinario y fecha');
                return;
            }
            resetHoras('Cargando...');
            try {
                const url = `index.php?action=horas_disponibles&id_veterinario=${encodeURIComponent(selVet.value)}&fecha=${encodeURIComponent(inpFecha.value)}`;
                const resp = await fetch(url);
                const data = await resp.json();
                if (!resp.ok) throw new Error(data.error || 'Error');

                if (!data.horas || data.horas.length === 0) {
                    resetHoras('Sin horarios disponibles');
                    aviso.textContent = data.mensaje || 'No hay horarios libres ese día. Prueba con otra fecha.';
                    return;
                }
                selHora.innerHTML = '<option value="">Selecciona una hora...</option>';
                data.horas.forEach(h => {
                    const opt = document.createElement('option');
                    opt.value = h;
                    opt.textContent = h;
                    if (h === horaPreseleccionada) opt.selected = true;
                    selHora.appendChild(opt);
                });
                selHora.disabled = false;
                horaPreseleccionada = '';
            } catch (e) {
                resetHoras('No se pudieron cargar las horas');
                aviso.textContent = 'Error al consultar la disponibilidad. Intenta nuevamente.';
            }
        };

        selVet.addEventListener('change', cargarHoras);
        inpFecha.addEventListener('change', cargarHoras);

        citaForm.addEventListener('submit', (event) => {
            if (!citaForm.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }
            citaForm.classList.add('was-validated');
        });

        if (selVet.value && inpFecha.value) cargarHoras();
    }

    // Reabre el modal que falló en el servidor, con los datos que el usuario escribió
    const estadoModal = document.getElementById('estadoModal');
    if (estadoModal && estadoModal.dataset.abrir) {
        const modalEl = document.getElementById(estadoModal.dataset.abrir);
        if (modalEl) new bootstrap.Modal(modalEl).show();
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
});

// Función auxiliar para guiar a los estudiantes en los componentes pendientes
function mostrarAlertaSprint(mensaje) {
    alert('Tarea pendiente por desarrollar:\n' + mensaje);
}

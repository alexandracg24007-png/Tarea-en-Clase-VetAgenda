# VetAgenda — Versión COMPLETA (RF01 + RF02 + RF03)

Proyecto PHP MVC + Bootstrap 5 con los tres requisitos implementados.

## RF01 — Gestión de usuarios y roles
Registro de dueños (`action=registro`) e inicio de sesión diferenciado por rol (cliente, recepcionista, veterinario). Validación en cliente y servidor, correo único, contraseñas con `password_hash`, rol de registro siempre `cliente`.

## RF02 — Registro de mascotas
- Botón **Nueva Mascota** (solo rol cliente) abre un modal: nombre, especie, raza (opcional, por defecto "Mestizo") y edad (0–40).
- Un dueño puede registrar **una o más** mascotas; siempre se vinculan al `id` de la sesión (nunca al que llegue por el formulario).
- Se rechaza una mascota duplicada (mismo nombre y especie del mismo dueño).

## RF03 — Solicitud de citas
- Botón **Solicitar Cita** (solo rol cliente, habilitado si ya tiene mascotas): mascota, veterinario, fecha, **hora disponible** y motivo.
- Las horas se cargan por AJAX (`action=horas_disponibles`) según veterinario y fecha. Agenda: lunes a viernes, 08:00–17:00 en bloques de 30 min, sin citas de 12:00 a 13:00 (configurable en las constantes de `app/models/Cita.php`).
- Se ocultan horas ya ocupadas (citas no canceladas) y horas pasadas.
- El servidor **vuelve a validar todo** (mascota propia, veterinario real, fecha no pasada, día laborable, hora libre) y usa una transacción con bloqueo para evitar que dos clientes reserven el mismo horario.
- La cita se crea en estado `pendiente`.
- Si algo falla, el modal se reabre con lo que el usuario había escrito.

## Archivos nuevos / modificados
```text
app/controllers/MascotaController.php   (nuevo, RF02)
app/controllers/RegistroController.php  (nuevo, RF01)
app/controllers/CitaController.php      (agendarCita, horasDisponibles)
app/controllers/AuthController.php
app/models/Mascota.php                  (crear, existeParaDueno, perteneceADueno)
app/models/Cita.php                     (horasDisponibles, verificarDisponibilidad, crear)
app/models/Usuario.php                  (existeEmail, crearDueno)
app/views/dashboard/index.php           (modales de mascota y cita)
app/views/auth/register.php, login.php, layouts/header.php
public/index.php                        (rutas)
public/js/app.js                        (validaciones y horas dinámicas)
database/schema.sql                     (hash correcto de 123456)
```

Zona horaria: `public/index.php` usa `America/Guayaquil`; cámbiala si necesitas otra.

## Instalación rápida (XAMPP / Laragon / WAMP)
1. Copia la carpeta `ejercicioUML` dentro de `htdocs` (XAMPP) o `www` (Laragon).
2. En phpMyAdmin importa `database/schema.sql` (crea la BD `vetagenda` con datos de prueba).
3. Revisa las credenciales en `app/config/Database.php` (por defecto: `localhost`, BD `vetagenda`, usuario `root`, sin contraseña).
4. Abre `http://localhost/ejercicioUML/public/`

> **Importante:** si ya habías importado el `schema.sql` anterior, vuelve a importar este. Los usuarios de prueba ahora tienen un hash bcrypt real de `123456` (antes el hash correspondía a otra clave y el código tenía una "puerta trasera" que aceptaba `123456` para cualquier cuenta; fue eliminada).

## Usuarios de prueba (contraseña: `123456`)
| Rol | Correo |
|---|---|
| Recepcionista | `recepcion@vetagenda.com` |
| Veterinario | `vet@vetagenda.com` |
| Cliente (dueño) | `cliente1@vetagenda.com` / `cliente2@vetagenda.com` |

## Pendiente (siguientes sprints)
RF05 (confirmar/cancelar citas – recepción), RF06 (atender y nota clínica – veterinario) y filtros del Sprint 4.

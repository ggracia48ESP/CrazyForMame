# Brief: panel-administracion

## Problem
El propietario necesita una forma simple de ver las solicitudes entrantes, aprobarlas o rechazarlas, bloquear fechas (vacaciones, compromisos previos) y que el cliente reciba confirmación automática. Sin esto, la gestión vuelve al teléfono/email manual.

## Current State
No existe panel. Las solicitudes llegan por email (definido en calendario-solicitudes) pero no hay forma de gestionarlas desde la web.

## Desired Outcome
- Panel accesible en `/admin` protegido con usuario/contraseña simples (sin sistema de login complejo)
- Lista de solicitudes con estado: Pendiente / Aprobada / Rechazada
- Acción de aprobar: cambia estado a Aprobada, envía email de confirmación al cliente, marca la fecha como ocupada en el calendario
- Acción de rechazar: cambia estado a Rechazada, envía email de rechazo al cliente
- Gestión de bloqueos de fecha: el admin puede añadir/eliminar fechas bloqueadas (vacaciones, eventos propios)
- Vista de calendario en el panel con solicitudes y bloqueos superpuestos

## Approach
Razor Pages bajo `/admin/`. Protección simple con middleware de autenticación básica (usuario/contraseña en appsettings.json). Acciones de aprobar/rechazar via HTMX para actualizar la fila sin recargar. Emails de confirmación/rechazo con MailKit. Vista de calendario reutiliza el componente de calendario-solicitudes con capa adicional de gestión.

## Scope
- **In**: Listado de solicitudes con acciones, aprobación/rechazo con email al cliente, gestión de bloqueos de fecha, vista de calendario admin, protección básica de acceso
- **Out**: Roles múltiples de admin, histórico de cambios/auditoría, estadísticas/reportes, integración con calendario externo (Google Calendar)

## Boundary Candidates
- Vista de solicitudes (listado + acciones)
- Lógica de aprobación/rechazo (cambio de estado + email)
- Gestión de bloqueos de fecha (CRUD)
- Protección de acceso al panel

## Out of Boundary
- Sistema de autenticación completo (OAuth, JWT) — fuera de scope
- Edición del catálogo de máquinas desde el panel — fuera de scope inicial

## Upstream / Downstream
- **Upstream**: infraestructura (IAlquilerRepository, modelos), calendario-solicitudes (solicitudes creadas)
- **Downstream**: ninguno (es el final del flujo)

## Existing Spec Touchpoints
- **Extends**: calendario-solicitudes (reutiliza lógica de disponibilidad y componente de calendario)
- **Adjacent**: catalogo-tarifas (podría enlazar en el futuro a edición de máquinas)

## Constraints
- Autenticación: Basic Auth o cookie simple con credenciales en appsettings.json
- Email: mismo proveedor SMTP que calendario-solicitudes
- HTMX para acciones inline (sin recargas de página completa)

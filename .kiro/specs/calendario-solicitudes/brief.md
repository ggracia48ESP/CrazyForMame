# Brief: calendario-solicitudes

## Problem
Los clientes no tienen forma de saber si una fecha está disponible antes de contactar, lo que genera fricciones y consultas innecesarias. Tampoco pueden hacer una solicitud formal directamente desde la web.

## Current State
No existe calendario ni formulario online. Todo se gestiona por teléfono o email.

## Desired Outcome
- Calendario mensual visible públicamente con fechas disponibles/ocupadas
- Al seleccionar una fecha disponible en el calendario, se abre el formulario de solicitud de alquiler
- El formulario recoge: nombre, email, teléfono, fecha del evento, máquinas deseadas, duración, notas adicionales
- Al enviar, la solicitud queda guardada en el repositorio con estado "Pendiente"
- El cliente ve una confirmación inmediata en pantalla
- El propietario recibe un email de aviso con los datos de la solicitud

## Approach
Razor Page `Calendario.cshtml` con HTMX: el calendario renderiza el mes actual y permite navegar entre meses sin recargar la página completa. Al clicar una fecha disponible, HTMX carga el formulario de solicitud en un panel lateral/modal. El envío del formulario es via HTMX POST; la respuesta es un fragmento HTML de confirmación. Email al propietario con SmtpClient o MailKit.

## Scope
- **In**: Calendario mensual público, selección de fecha, formulario de solicitud, confirmación en pantalla, email de aviso al propietario, guardado en repositorio
- **Out**: Pago online, selección de hora exacta (solo día), recordatorios automáticos, email de confirmación al cliente (eso va en panel-administracion)

## Boundary Candidates
- Componente de calendario (navegación, renderizado de disponibilidad)
- Formulario de solicitud (campos, validación, envío HTMX)
- Lógica de disponibilidad (días bloqueados por BloqueoFecha o Solicitud confirmada)

## Out of Boundary
- Aprobación/rechazo de solicitudes (panel-administracion)
- Gestión de fechas bloqueadas por el admin (panel-administracion)

## Upstream / Downstream
- **Upstream**: infraestructura (modelos Solicitud, BloqueoFecha, IAlquilerRepository)
- **Downstream**: panel-administracion (consume las solicitudes creadas aquí)

## Existing Spec Touchpoints
- **Extends**: infraestructura (usa BloqueoFecha y Solicitud)
- **Adjacent**: panel-administracion (comparte el concepto de disponibilidad)

## Constraints
- HTMX para interactividad del calendario (sin JS custom complejo)
- Email: configuración SMTP en appsettings.json (localhost/mailtrap para desarrollo)
- Validación server-side con DataAnnotations; HTMX muestra errores en línea

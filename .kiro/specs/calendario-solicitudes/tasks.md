# Implementation Plan

- [ ] 1. Foundation — configuración, modelos y registro de servicios
- [ ] 1.1 (P) Configurar EmailSettings en appsettings.json y registrar los servicios en DI
  - Añadir la sección `EmailSettings` en `appsettings.json`: `SmtpHost`, `SmtpPort`, `SmtpUser`, `SmtpPassword`, `OwnerEmail`, `SenderEmail` con valores de desarrollo (localhost:1025)
  - Registrar `EmailSettings` con `builder.Services.Configure<EmailSettings>(builder.Configuration.GetSection("EmailSettings"))` en `Program.cs`
  - Registrar `builder.Services.AddScoped<IDisponibilidadService, DisponibilidadService>()`
  - Registrar `builder.Services.AddSingleton<IEmailService, SmtpEmailService>()`
  - La aplicación compila y arranca sin errores con los nuevos registros DI
  - _Requirements: 6_
  - _Boundary: Program.cs, appsettings.json_

- [ ] 1.2 (P) Definir SolicitudInputModel con validación de campos requeridos
  - Crear `Models/SolicitudInputModel.cs` con propiedades: `NombreCliente`, `EmailCliente` (`[EmailAddress]`), `TelefonoCliente`, `FechaEvento` (`DateOnly`), `MaquinaDeseada`, `DuracionEvento` (todos `[Required]`), y `NotasAdicionales` (`string?` opcional)
  - El modelo compila con `[Required]` y `[EmailAddress]` sin referencias externas al proyecto
  - _Requirements: 4_
  - _Boundary: Models/SolicitudInputModel.cs_

- [ ] 2. Servicios y partials — implementación paralela de lógica y vistas
- [ ] 2.1 (P) Implementar DisponibilidadService para calcular fechas ocupadas por mes
  - Crear `Services/IDisponibilidadService.cs` con método `HashSet<DateOnly> GetOccupiedDates(int año, int mes)`
  - Crear `Services/DisponibilidadService.cs`: obtener todos los `BloqueoFecha` y todas las `Solicitud` con `Estado == Aprobada` del repositorio
  - Para cada registro, expandir el rango `FechaInicio..FechaFin` en días individuales y añadirlos al `HashSet<DateOnly>`
  - `GetOccupiedDates(2026, 6)` con un bloqueo del 10 al 15 de junio devuelve un `HashSet` con los 6 días; sin datos devuelve `HashSet` vacío
  - _Requirements: 1, 2_
  - _Boundary: Services/IDisponibilidadService.cs, Services/DisponibilidadService.cs_

- [ ] 2.2 (P) Implementar SmtpEmailService para notificar al propietario por email
  - Crear `Services/IEmailService.cs` con método `Task SendOwnerNotificationAsync(Solicitud solicitud)`
  - Crear `Services/SmtpEmailService.cs` que lee `IOptions<EmailSettings>` y usa `System.Net.Mail.SmtpClient` para enviar el email al `OwnerEmail`
  - El cuerpo del email incluye: nombre, email, teléfono, máquina deseada, fecha del evento, duración, y notas si las hay
  - El fallo de SMTP (host no disponible) es capturado en `try/catch` con log de error; el método retorna sin relanzar la excepción
  - `SendOwnerNotificationAsync` completa sin lanzar excepción tanto en envío exitoso como en fallo SMTP
  - _Requirements: 6_
  - _Boundary: Services/IEmailService.cs, Services/SmtpEmailService.cs_
  - _Depends: 1.1_

- [ ] 2.3 (P) Crear el partial `_CalendarioMes.cshtml` con el grid mensual y triggers HTMX
  - El partial recibe el `CalendarioModel` como modelo y renderiza un grid del mes con los días distribuidos en semanas
  - Cada día muestra su estado visual: disponible (clicable), ocupado (no clicable), o pasado (atenuado)
  - Los días disponibles tienen `hx-get="/Calendario?handler=Formulario&fecha=YYYY-MM-DD"`, `hx-target="#formulario-panel"`, y `hx-swap="innerHTML"`
  - Los botones de mes anterior y siguiente tienen `hx-get="/Calendario?handler=Mes&año=X&mes=Y"`, `hx-target="#calendario-container"`, y `hx-swap="innerHTML"`
  - El partial muestra el nombre del mes y el año como encabezado
  - _Requirements: 1, 2, 3_
  - _Boundary: Pages/Shared/_CalendarioMes.cshtml_

- [ ] 2.4 (P) Crear el partial `_FormularioSolicitud.cshtml` con campos y validación inline
  - El partial recibe `SolicitudInputModel` como modelo y renderiza el formulario con todos los campos requeridos y el campo opcional de notas
  - La fecha del evento aparece pre-rellenada con el valor pasado desde `OnGetFormulario`
  - Cada campo tiene `asp-validation-for` para mostrar errores de validación inline junto al campo afectado
  - El formulario usa `hx-post="/Calendario"`, `hx-target="#formulario-panel"`, y `hx-swap="innerHTML"` para envío HTMX
  - El partial renderiza correctamente tanto en estado vacío inicial como con errores de validación
  - _Requirements: 3, 4_
  - _Boundary: Pages/Shared/_FormularioSolicitud.cshtml_
  - _Depends: 1.2_

- [ ] 2.5 (P) Crear el partial `_ConfirmacionSolicitud.cshtml` con el mensaje de confirmación
  - El partial muestra un mensaje claro indicando que la solicitud ha sido recibida y está pendiente de revisión
  - El contenido no requiere modelo; es HTML estático con el mensaje de confirmación
  - _Requirements: 5_
  - _Boundary: Pages/Shared/_ConfirmacionSolicitud.cshtml_

- [ ] 3. Integración — CalendarioModel y página completa
- [ ] 3.1 Implementar CalendarioModel con todos los handlers HTMX y POST
  - `OnGet()`: inicializa `MesActual` y `AñoActual` con la fecha actual, llama `DisponibilidadService.GetOccupiedDates()` y popula `FechasOcupadas`
  - `OnGetMes(int año, int mes)`: recalcula `FechasOcupadas` para el mes indicado y retorna `Partial("_CalendarioMes", this)`
  - `OnGetFormulario(DateOnly fecha)`: si la fecha está en `FechasOcupadas` o es pasada, retorna `Content("<p>Fecha no disponible.</p>", "text/html")`; si disponible, retorna `Partial("_FormularioSolicitud", Input)` con `Input.FechaEvento = fecha`
  - `OnPostAsync()`: si `ModelState.IsValid`, crea `Solicitud` desde `Input`, llama `AddSolicitud`, llama `SendOwnerNotificationAsync` en `try/catch`, retorna `Partial("_ConfirmacionSolicitud")`; si no válido, retorna `Partial("_FormularioSolicitud", Input)`
  - `GET /Calendario?handler=Formulario&fecha=2026-06-15` con fecha disponible retorna partial del formulario; con fecha ocupada retorna el mensaje de no disponible
  - `POST /Calendario` con datos válidos guarda la solicitud en el repositorio y retorna partial de confirmación
  - _Requirements: 1, 2, 3, 4, 5, 6_

- [ ] 3.2 Crear `Calendario.cshtml` con los contenedores HTMX y el layout de la página
  - La página incluye un `<div id="calendario-container">` que contiene `_CalendarioMes` en el renderizado inicial
  - La página incluye un `<div id="formulario-panel">` vacío donde HTMX inyecta el formulario o la confirmación
  - `GET /Calendario` devuelve HTTP 200 con el calendario del mes actual y el panel de formulario vacío visibles en el HTML
  - _Requirements: 1, 5_
  - _Depends: 3.1_

- [ ] 4. Verificación — confirmar el comportamiento completo del flujo
- [ ]* 4.1 Tests unitarios de DisponibilidadService y validación del formulario
  - Verificar que `DisponibilidadService.GetOccupiedDates` marca correctamente fechas dentro de un `BloqueoFecha`
  - Verificar que `DisponibilidadService.GetOccupiedDates` marca fechas de `Solicitud` con `Estado == Aprobada` y no las de `Pendiente`
  - Verificar que `CalendarioModel.OnGetFormulario` con fecha ocupada retorna el mensaje de "no disponible"
  - Verificar que `CalendarioModel.OnPostAsync` con `ModelState` inválido no llama `AddSolicitud`
  - _Requirements: 1, 4_

- [ ]* 4.2 Tests de integración de los endpoints del calendario
  - Verificar que `GET /Calendario` devuelve HTTP 200 con HTML del mes actual
  - Verificar que `GET /Calendario?handler=Mes&año=2026&mes=8` devuelve partial HTML con agosto 2026
  - Verificar que `GET /Calendario?handler=Formulario&fecha=FECHA_DISPONIBLE` devuelve partial de formulario
  - Verificar que `POST /Calendario` con datos válidos guarda la solicitud (estado Pendiente) y retorna partial de confirmación
  - Verificar que `POST /Calendario` con email inválido retorna partial de formulario con error de validación
  - _Requirements: 1, 2, 3, 4, 5, 6_

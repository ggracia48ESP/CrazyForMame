# Implementation Plan

- [ ] 1. Foundation — autenticación y configuración del área admin
- [ ] 1.1 Configurar Cookie Authentication y AdminSettings en Program.cs y appsettings.json
  - Añadir sección `AdminSettings` en `appsettings.json`: `Username` y `Password`
  - Registrar `builder.Services.Configure<AdminSettings>(...)` y `AddAuthentication(CookieAuthenticationDefaults).AddCookie(options => options.LoginPath = "/Admin/Login")` en `Program.cs`
  - Añadir `app.UseAuthentication()` antes de `app.UseAuthorization()` en el pipeline
  - `GET /Admin` sin cookie redirige automáticamente a `/Admin/Login`
  - _Requirements: 1_

- [ ] 1.2 (P) Extender IEmailService con métodos de notificación al cliente
  - Añadir `Task SendClientApprovalEmailAsync(Solicitud solicitud)` y `Task SendClientRejectionEmailAsync(Solicitud solicitud)` a `Services/IEmailService.cs`
  - Implementar ambos métodos en `SmtpEmailService.cs`: email al `solicitud.EmailCliente` con asunto y cuerpo apropiados para aprobación y rechazo respectivamente
  - Mismo patrón try/catch que `SendOwnerNotificationAsync`: fallo capturado con log, sin relanzar
  - `SmtpEmailService` compila implementando los tres métodos de la interfaz sin errores
  - _Requirements: 3, 4_
  - _Boundary: Services/IEmailService.cs, Services/SmtpEmailService.cs_

- [ ] 1.3 (P) Definir BloqueoFechaInputModel con validación
  - Crear `Models/BloqueoFechaInputModel.cs` con propiedades `[Required] FechaInicio` y `[Required] FechaFin` (ambas `DateOnly`) y `Motivo` (`string?` opcional)
  - El modelo compila sin referencias externas al proyecto
  - _Requirements: 5_
  - _Boundary: Models/BloqueoFechaInputModel.cs_

- [ ] 2. Login — página y validación de acceso
- [ ] 2.1 Implementar AdminLoginModel y Login.cshtml
  - Crear `Pages/Admin/Login.cshtml.cs`: `OnPostAsync()` compara credenciales con `IOptions<AdminSettings>`; si correctas, llama `HttpContext.SignInAsync` y redirige a `/Admin`; si incorrectas, asigna `ErrorMessage` sin revelar qué campo falló
  - Crear `Pages/Admin/Login.cshtml`: formulario con campos Username y Password, botón de envío, y área para mostrar `ErrorMessage`
  - `POST /Admin/Login` con credenciales correctas establece cookie y redirige a `/Admin`; con incorrectas muestra error sin acceso
  - _Requirements: 1_
  - _Depends: 1.1_

- [ ] 3. Partials y vistas de administración
- [ ] 3.1 (P) Crear partial _SolicitudRow con estado y acciones inline HTMX
  - Recibe `Solicitud` como modelo y muestra: nombre, email, teléfono, máquina, fecha, duración, notas, estado actual
  - Si estado es `Pendiente`: muestra botones Aprobar y Rechazar con `hx-post="/Admin?handler=Aprobar&id=@s.Id"` y `hx-target` apuntando a la fila propia; si `Aprobada` o `Rechazada`: solo muestra el estado, sin botones de acción
  - El partial renderiza correctamente tanto con estado Pendiente (con botones) como con Aprobada/Rechazada (sin botones)
  - _Requirements: 2, 3, 4_
  - _Boundary: Pages/Admin/Shared/_SolicitudRow.cshtml_

- [ ] 3.2 (P) Crear partial _BloqueosLista con lista de bloqueos activos y acción de eliminar
  - Recibe `IReadOnlyList<BloqueoFecha>` y muestra cada bloqueo con: fecha inicio, fecha fin, motivo (si existe) y botón de eliminar con `hx-post="/Admin?handler=RemoveBloqueo&id=@b.Id"`
  - Si la lista está vacía, muestra mensaje indicando que no hay bloqueos activos
  - El partial renderiza correctamente tanto con bloqueos existentes (con botón eliminar) como con lista vacía
  - _Requirements: 5_
  - _Boundary: Pages/Admin/Shared/_BloqueosLista.cshtml_

- [ ] 3.3 (P) Crear partial _CalendarioAdmin con grid mensual admin
  - Recibe `AdminCalendarioModel` y renderiza el mes con días diferenciados: disponible, bloqueado por `BloqueoFecha`, con solicitud `Pendiente`, con solicitud `Aprobada`
  - Incluye botones de mes anterior/siguiente con `hx-get="/Admin/Calendario?handler=Mes&año=X&mes=Y"`
  - El calendario muestra el mes y año como encabezado y diferencia visualmente los cuatro estados
  - _Requirements: 6_
  - _Boundary: Pages/Admin/Shared/_CalendarioAdmin.cshtml_

- [ ] 4. Integración — AdminIndexModel y página principal
- [ ] 4.1 Implementar AdminIndexModel con todos los handlers
  - `[Authorize]` en la clase; `OnGet()` carga `Solicitudes` ordenadas por `FechaCreacion` desc y `Bloqueos`
  - `OnPostAprobar(int id)`: obtiene solicitud, actualiza estado a `Aprobada` con `UpdateSolicitud`, llama `SendClientApprovalEmailAsync` en try/catch, retorna `Partial("_SolicitudRow", solicitud)`
  - `OnPostRechazar(int id)`: ídem con `Rechazada` y `SendClientRejectionEmailAsync`
  - `OnPostAddBloqueo()`: valida `FechaInicio <= FechaFin`; si OK crea y guarda `BloqueoFecha`, retorna `Partial("_BloqueosLista", bloqueos)`; si error, retorna partial con mensaje de error
  - `OnPostRemoveBloqueo(int id)`: elimina bloqueo del repositorio, retorna `Partial("_BloqueosLista", bloqueos)`
  - `POST /Admin?handler=Aprobar&id=X` cambia estado a Aprobada y retorna fila actualizada sin recarga; `POST /Admin?handler=AddBloqueo` con fechas válidas guarda el bloqueo y retorna lista actualizada
  - _Requirements: 2, 3, 4, 5_
  - _Depends: 1.2, 1.3, 3.1, 3.2_

- [ ] 4.2 Crear Index.cshtml — página principal del panel
  - Muestra la lista de solicitudes con `_SolicitudRow` por cada solicitud
  - Incluye formulario de alta de bloqueo (FechaInicio, FechaFin, Motivo) con `hx-post` y `hx-target="#bloqueos-panel"`
  - Incluye `<div id="bloqueos-panel">` con `_BloqueosLista` renderizado inicialmente
  - `GET /Admin` con cookie válida devuelve HTTP 200 con solicitudes y lista de bloqueos visibles
  - _Requirements: 2, 5_
  - _Depends: 4.1_

- [ ] 5. Calendario admin
- [ ] 5.1 Implementar AdminCalendarioModel y Calendario.cshtml
  - `[Authorize]` en la clase; `OnGet()` carga el mes actual con solicitudes y bloqueos del repositorio; `OnGetMes(int año, int mes)` retorna `Partial("_CalendarioAdmin", this)`
  - `Calendario.cshtml` incluye `<div id="calendario-admin-container">` con `_CalendarioAdmin` inicial y el heading del mes
  - `GET /Admin/Calendario` con cookie válida devuelve HTTP 200 con el calendario del mes actual mostrando solicitudes y bloqueos diferenciados
  - _Requirements: 6_
  - _Depends: 3.3_

- [ ] 6. Verificación
- [ ]* 6.1 Tests de integración del flujo admin completo
  - Verificar que `GET /Admin` sin cookie redirige a `/Admin/Login`
  - Verificar que `POST /Admin/Login` con credenciales correctas establece cookie y accede al panel
  - Verificar que `POST /Admin?handler=Aprobar&id=X` cambia estado a Aprobada y retorna partial de fila
  - Verificar que `POST /Admin?handler=AddBloqueo` con `FechaInicio > FechaFin` no guarda el bloqueo
  - Verificar que `POST /Admin?handler=RemoveBloqueo&id=X` elimina el bloqueo del repositorio
  - _Requirements: 1, 2, 3, 4, 5_

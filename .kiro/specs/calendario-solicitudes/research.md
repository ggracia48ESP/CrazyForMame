# Research Log: calendario-solicitudes

## Discovery Scope
- **Feature type**: Complex Integration (HTMX multi-step, SMTP, availability logic)
- **Discovery process**: Full discovery

## Key Findings

### HTMX handlers en Razor Pages (multi-handler pattern)
- Razor Pages soporta múltiples handlers nombrados en el mismo PageModel: `OnGetMes`, `OnGetFormulario`, `OnPost`
- Cada handler responde a `?handler=NombreHandler` en la URL
- `return Partial("_NombrePartial", model)` devuelve solo el fragmento HTML
- Para el POST con HTMX, `hx-post="/Calendario"` + `hx-target="#formulario-panel"` swap la respuesta

### Email con System.Net.Mail vs MailKit
| Criterio | System.Net.Mail | MailKit |
|----------|----------------|---------|
| NuGet adicional | No (BCL) | Sí |
| Soporte .NET 10 | Sí (marcado obsoleto en advertencias) | Sí (activamente mantenido) |
| TLS/STARTTLS | Limitado | Completo |
| Recomendación para prod | No | Sí |

- **Decisión**: `System.Net.Mail` para esta fase (sin NuGet). Documentado como candidato a reemplazo por MailKit antes de producción.
- **Config desarrollo**: Mailtrap o Mailhog en `localhost:1025` (sin TLS, sin credenciales).

### Lógica de disponibilidad
- Una fecha es ocupada si cae dentro del rango `FechaInicio..FechaFin` de algún `BloqueoFecha`, O dentro del rango de alguna `Solicitud` con `Estado == Aprobada`.
- Las solicitudes `Pendiente` NO bloquean fechas — el admin decide tras revisar.
- El servicio computa un `HashSet<DateOnly>` por mes para O(1) lookup en la vista.

### HTMX calendar navigation pattern
- El grid de calendario se implementa como partial `_CalendarioMes.cshtml` que recibe el mes, año y fechas ocupadas
- La navegación entre meses usa `hx-get="/Calendario?handler=Mes&año=X&mes=Y"` con `hx-target="#calendario-container"` y `hx-swap="innerHTML"`
- El calendario incluye botones de mes anterior/siguiente con los parámetros correctos generados server-side

## Design Decisions

| Decisión | Elegida | Alternativa | Razón |
|----------|---------|-------------|-------|
| Email | System.Net.Mail (BCL) | MailKit (NuGet) | Sin dependencias adicionales; suficiente para desarrollo con Mailtrap |
| Disponibilidad | DisponibilidadService injectable (Scoped) | Lógica inline en PageModel | Testable, reutilizable por panel-administracion |
| Validación | DataAnnotations + ModelState | FluentValidation | Ya disponible en ASP.NET Core, sin NuGet adicional |
| Fallo de email | Silencioso (try/catch + log) | Falla visible al usuario | El guardado de solicitud no debe depender del email |
| Modelo de formulario | SolicitudInputModel separado | BindProperties directas | Validación agrupada, reutilizable, más limpio |

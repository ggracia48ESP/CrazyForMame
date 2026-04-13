# Research Log: panel-administracion

## Discovery Scope
- **Feature type**: Complex Integration (auth, HTMX multi-handler, extensión cross-boundary de email)
- **Discovery process**: Full discovery

## Key Findings

### Cookie Authentication en ASP.NET Core 10
- `builder.Services.AddAuthentication(CookieAuthenticationDefaults.AuthenticationScheme).AddCookie(options => { options.LoginPath = "/Admin/Login"; })` — en el metapaquete, sin NuGet adicional
- `app.UseAuthentication()` debe estar ANTES de `app.UseAuthorization()` en el pipeline
- `[Authorize]` en PageModel protege la página; el middleware redirige automáticamente a `LoginPath`
- `HttpContext.SignInAsync(CookieAuthenticationDefaults.AuthenticationScheme, principal)` establece la cookie
- La cookie expira al cerrar el navegador por defecto (configurable con `options.ExpireTimeSpan`)

### Extensión cross-boundary de IEmailService
- `IEmailService` y `SmtpEmailService` pertenecen al boundary de `calendario-solicitudes`
- Este spec necesita dos nuevos métodos: `SendClientApprovalEmailAsync` y `SendClientRejectionEmailAsync`
- Opción 1: Extender la interfaz existente (elegida) — simple, un solo servicio SMTP, no requiere nueva interfaz
- Opción 2: Nueva `IAdminEmailService` — más limpio en boundaries pero duplica la configuración SMTP
- **Decisión**: Extender `IEmailService` + `SmtpEmailService` — declarado como revalidation trigger en calendario-solicitudes

### HTMX + Anti-Forgery Token en Razor Pages
- Razor Pages valida el token CSRF por defecto en POST
- Para HTMX, incluir `@Html.AntiForgeryToken()` en los formularios o pasar el token en `hx-headers`
- Alternativa: `[IgnoreAntiforgeryToken]` en handlers específicos (no recomendado para producción)
- Solución elegida: forms con `method="post"` incluyen el token automáticamente con Tag Helpers

### Credenciales de admin en appsettings.json
- En desarrollo: credenciales en texto plano en `appsettings.Development.json` (no commiteado)
- En producción: usar variables de entorno o Azure Key Vault
- Para este proyecto localhost: texto plano aceptable con documentación de riesgo

## Design Decisions

| Decisión | Elegida | Alternativa | Razón |
|----------|---------|-------------|-------|
| Auth | Cookie Auth (BCL) | Basic Auth HTTP | Mejor UX (login form), sin NuGet adicional |
| Email cliente | Extender IEmailService | Nueva interfaz IAdminEmailService | Simplicidad, mismo transporte SMTP |
| Calendario admin | Nuevo partial _CalendarioAdmin | Reutilizar _CalendarioMes | Admin necesita mostrar más información (tipos de solicitud) |
| Credenciales | appsettings.json plaintext | Hash BCrypt | Alcance localhost/dev; hash es mejora futura |
| CSRF en HTMX | Form tag helpers (token automático) | hx-headers manual | Más simple, compatible con Razor Pages por defecto |

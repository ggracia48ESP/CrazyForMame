# Roadmap

## Overview
Página web para el alquiler de máquinas recreativas artesanales destinadas a eventos (bodas, cumpleaños, fiestas). El sitio permite a los visitantes explorar el catálogo de máquinas, consultar tarifas, ver disponibilidad en un calendario y enviar solicitudes de alquiler. El propietario dispone de un panel simple para gestionar solicitudes y marcar fechas ocupadas.

## Approach Decision
- **Elegido**: ASP.NET Core 10 (backend) + HTMX + HTML/CSS (frontend), repositorio en memoria con patrón Repository (intercambiable por SQLite u otro almacenamiento posterior)
- **Por qué**: .NET 10 es la preferencia del usuario; HTMX encaja perfectamente con Razor Pages/MVC para interactividad sin un framework JS separado; el patrón Repository desacopla la capa de datos desde el inicio
- **Alternativas descartadas**: Blazor (más complejo para este caso), Vue.js (requiere API separada y más configuración), HTML puro sin HTMX (interactividad del calendario y formulario sería más laboriosa)

## Scope
- **In**: Catálogo de 6 máquinas (3 grandes, 3 pequeñas), tarifas, calendario de disponibilidad público, formulario de solicitud de alquiler, panel de administración básico, notificaciones por email
- **Out**: Pasarela de pago, sistema de login para clientes, app móvil nativa, CMS completo, multi-idioma

## Constraints
- Backend: .NET 10 Core
- Frontend: HTMX + HTML + CSS (sin frameworks JS)
- Servidor: localhost (desarrollo); producción TBD
- Base de datos: en memoria por ahora, con patrón Repository para facilitar migración futura
- Sin autenticación de cliente (panel de admin protegido con credenciales simples)

## Boundary Strategy
- **Por qué este split**: Cada spec puede implementarse y probarse de forma independiente; infraestructura es la base que desbloquea el resto; catálogo y tarifas no dependen de lógica de negocio compleja; calendario/solicitudes y panel comparten los modelos de datos de infraestructura
- **Costuras compartidas a vigilar**: El modelo `Solicitud` y `Maquina` se definen en infraestructura y lo usan todos los specs posteriores; el repositorio en memoria debe ser thread-safe desde el inicio

## Specs (dependency order)
- [ ] infraestructura -- Proyecto .NET 10, modelos de datos, repositorio en memoria, layout base HTMX. Dependencies: none
- [ ] catalogo-tarifas -- Catálogo de 6 máquinas con fotos/descripción y página de tarifas. Dependencies: infraestructura
- [ ] calendario-solicitudes -- Calendario público de disponibilidad + formulario de solicitud integrado. Dependencies: infraestructura
- [ ] panel-administracion -- Panel de gestión de solicitudes, aprobación/rechazo, bloqueo de fechas, email de confirmación. Dependencies: infraestructura, calendario-solicitudes

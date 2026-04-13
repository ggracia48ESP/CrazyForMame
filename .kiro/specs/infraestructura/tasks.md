# Implementation Plan

- [ ] 1. Fundación — crear y configurar el proyecto base
- [ ] 1.1 Inicializar el proyecto ASP.NET Core 10 con Razor Pages y la estructura de directorios
  - Crear la solución .NET 10 con plantilla `webapp` (Razor Pages) mediante `dotnet new webapp`
  - Crear los directorios `Models/`, `Data/` (la plantilla genera `Pages/Shared/`, `wwwroot/css/` automáticamente)
  - La plantilla genera archivos iniciales (`_Layout.cshtml`, `_ViewStart.cshtml`, `_ViewImports.cshtml`, `Index.cshtml`, `site.css`, `Program.cs`) que las tareas posteriores modificarán
  - La aplicación compila sin errores con `dotnet build` y responde en localhost con `dotnet run`
  - _Requirements: 1.1, 1.2_

- [ ] 2. Modelos de dominio — definir las entidades del sistema de alquiler
- [ ] 2.1 (P) Definir la entidad Maquina con su categoría
  - Crear `Models/Maquina.cs` con clase `Maquina`: Id (int), Nombre, Descripcion, ImagenUrl (string), Categoria (CategoriaMaquina), PrecioBase (decimal)
  - Definir enum `CategoriaMaquina { Grande, Pequeña }` en el mismo archivo
  - La clase es pública y compilable sin referencias externas al proyecto
  - _Requirements: 2.1, 2.4_
  - _Boundary: Models/Maquina.cs_

- [ ] 2.2 (P) Definir la entidad Solicitud con el ciclo de vida del alquiler
  - Crear `Models/Solicitud.cs` con clase `Solicitud`: Id (int), MaquinaId (int), NombreCliente, EmailCliente, TelefonoCliente (string), FechaInicio, FechaFin (DateOnly), Estado (EstadoSolicitud), FechaCreacion (DateTime)
  - Definir enum `EstadoSolicitud { Pendiente, Aprobada, Rechazada }` en el mismo archivo
  - `Estado` se inicializa a `EstadoSolicitud.Pendiente` y `FechaCreacion` a `DateTime.UtcNow` como valores por defecto
  - _Requirements: 2.2, 2.4_
  - _Boundary: Models/Solicitud.cs_

- [ ] 2.3 (P) Definir la entidad BloqueoFecha para la indisponibilidad de máquinas
  - Crear `Models/BloqueoFecha.cs` con clase `BloqueoFecha`: Id (int), MaquinaId (int), FechaInicio, FechaFin (DateOnly), Motivo (string?)
  - La clase es pública y compilable sin referencias externas al proyecto
  - _Requirements: 2.3, 2.4_
  - _Boundary: Models/BloqueoFecha.cs_

- [ ] 3. Capa de repositorio — contrato de datos e implementación en memoria
- [ ] 3.1 Definir la interfaz IAlquilerRepository
  - Crear `Data/IAlquilerRepository.cs` con interfaz pública que declara: `GetAllMaquinas`, `GetMaquinaById`, `AddMaquina`, `UpdateMaquina`; `GetAllSolicitudes`, `GetSolicitudById`, `AddSolicitud`, `UpdateSolicitud`; `GetAllBloqueos`, `GetBloqueosByMaquina`, `AddBloqueo`, `RemoveBloqueo`
  - Los métodos `GetById` retornan tipo nullable (`T?`); los métodos `GetAll`/`GetBy` retornan `IReadOnlyList<T>` (nunca null)
  - La interfaz compila con las entidades de `Models/` ya definidas
  - _Requirements: 3.1, 3.5_

- [ ] 3.2 Implementar InMemoryAlquilerRepository thread-safe
  - Crear `Data/InMemoryAlquilerRepository.cs` implementando `IAlquilerRepository` con tres `ConcurrentDictionary<int, T>` privados: `_maquinas`, `_solicitudes`, `_bloqueos`
  - Usar `Interlocked.Increment` sobre contadores privados para generar IDs únicos al ejecutar cualquier método `Add`
  - El método `AddMaquina` (y sus equivalentes) asigna el Id generado a la entidad antes de insertar en el diccionario
  - Múltiples llamadas concurrentes a `AddSolicitud` no producen IDs duplicados ni corrupción de datos
  - _Requirements: 3.2, 3.3, 3.4, 3.5_

- [ ] 4. Layout base y estilos — estructura visual compartida con HTMX
- [ ] 4.1 (P) Crear el layout HTML base con cabecera, navegación, pie de página y HTMX
  - Reemplazar el contenido de `Pages/Shared/_Layout.cshtml` generado por la plantilla con la estructura definitiva: `<header>`, `<nav>`, `<main>@RenderBody()</main>`, `<footer>`
  - Incluir el script de HTMX 2.x via CDN: `<script src="https://unpkg.com/htmx.org@2/dist/htmx.min.js"></script>`
  - Incluir el enlace a `site.css`: `<link rel="stylesheet" href="~/css/site.css" />`
  - El HTML del layout es válido y renderiza la estructura esperada cuando se usa como layout base
  - _Requirements: 4.1, 4.3_
  - _Boundary: Pages/Shared/_Layout.cshtml_
  - _Depends: 1.1_

- [ ] 4.2 (P) Crear la hoja de estilos global site.css
  - Reemplazar el contenido de `wwwroot/css/site.css` generado por la plantilla con los estilos definitivos: reset/normalize, contenedor de layout, estilos del `<header>`, `<nav>` y `<footer>`
  - El archivo es CSS válido sin errores de sintaxis
  - _Requirements: 4.2_
  - _Boundary: wwwroot/css/site.css_
  - _Depends: 1.1_

- [ ] 5. Integración — conectar repositorio, configurar páginas y crear la entrada al sitio
- [ ] 5.1 (P) Configurar el pipeline HTTP y registrar el repositorio en el contenedor DI
  - Modificar `Program.cs` para registrar `builder.Services.AddSingleton<IAlquilerRepository, InMemoryAlquilerRepository>()`
  - Confirmar que `AddRazorPages()` y `MapRazorPages()` están presentes en el pipeline
  - Dos resoluciones de `IAlquilerRepository` desde el contenedor devuelven la misma instancia (Singleton verificado)
  - _Requirements: 1.1, 1.2, 3.3_
  - _Boundary: Program.cs_

- [ ] 5.2 (P) Configurar _ViewStart y _ViewImports para aplicar el layout globalmente
  - Confirmar/actualizar `Pages/_ViewStart.cshtml` con `Layout = "_Layout"` para que todas las páginas hereden el layout por defecto
  - Confirmar/actualizar `Pages/_ViewImports.cshtml` con `@addTagHelper *, Microsoft.AspNetCore.Mvc.TagHelpers` y directivas `@using` para los namespaces de `Models/` y `Data/`
  - Una nueva Razor Page creada sin especificar layout hereda automáticamente `_Layout.cshtml`
  - _Requirements: 4.1, 4.4_
  - _Boundary: Pages/_ViewStart.cshtml, Pages/_ViewImports.cshtml_

- [ ] 5.3 Crear la página de inicio como punto de entrada del sitio
  - Confirmar/actualizar `Pages/Index.cshtml` con contenido placeholder y `Pages/Index.cshtml.cs` con `OnGet()` mínimo (sin lógica)
  - `GET /` devuelve HTTP 200 con HTML que incluye el script HTMX CDN, el enlace a `site.css` y los elementos `<header>`, `<nav>` y `<footer>` visibles en el HTML renderizado
  - _Requirements: 5.1, 5.2_
  - _Depends: 5.1, 5.2_

- [ ] 6. Verificación — validar el funcionamiento completo de la infraestructura base
- [ ] 6.1 Tests de integración: startup, DI y renderizado del layout
  - Verificar que la aplicación arranca (`WebApplication.CreateBuilder().Build().Run()`) sin excepciones
  - Verificar que el contenedor DI resuelve `IAlquilerRepository` como `InMemoryAlquilerRepository` y que dos resoluciones devuelven la misma instancia
  - Verificar que `GET /` devuelve HTTP 200 y que el HTML de respuesta contiene el atributo `src` del CDN de HTMX
  - Verificar que el HTML renderizado incluye `<header>`, elemento de navegación y `<footer>`
  - _Requirements: 1.1, 1.2, 3.3, 4.1, 4.3, 5.1, 5.2_

- [ ]* 6.2 Tests unitarios del repositorio en memoria
  - Verificar que `AddMaquina` asigna un Id único incremental a la entidad añadida
  - Verificar que `GetMaquinaById` devuelve `null` para un Id inexistente
  - Verificar que `GetBloqueosByMaquina` filtra y devuelve únicamente los bloqueos del `MaquinaId` indicado
  - Verificar concurrencia: múltiples llamadas concurrentes a `AddSolicitud` producen Ids únicos sin duplicados
  - _Requirements: 3.1, 3.2, 3.4, 3.5_

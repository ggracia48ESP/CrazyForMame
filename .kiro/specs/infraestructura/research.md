# Research & Design Decisions

---
- **Feature**: `infraestructura`
- **Discovery Scope**: New Feature (greenfield)
- **Key Findings**:
  - Sin código existente; todas las decisiones son nuevas.
  - El stack (ASP.NET Core 10 + Razor Pages + HTMX CDN) está completamente definido en el brief y el roadmap; no requirió investigación externa.
  - `ConcurrentDictionary` + `Interlocked.Increment` es la solución BCL más simple para thread-safety sin dependencias externas.

---

## Research Log

### Stack y restricciones de plataforma
- **Context**: El brief especifica .NET 10 SDK, HTMX via CDN (sin npm/bundler) y repositorio en memoria thread-safe.
- **Sources Consulted**: brief.md, roadmap.md (steering)
- **Findings**:
  - ASP.NET Core 10 + Razor Pages: patrón server-side rendering con enrutamiento por convención de archivos en `Pages/`.
  - HTMX 2.x CDN: `unpkg.com/htmx.org@2/dist/htmx.min.js` sirve la última versión 2.x sin breaking changes del major.
  - `ConcurrentDictionary<TKey, TValue>` es thread-safe para lectura y escritura concurrente; `Interlocked.Increment` garantiza IDs únicos sin locks adicionales.
- **Implications**: Sin dependencias NuGet externas. El proyecto es el metapaquete `Microsoft.AspNetCore.App`.

---

## Architecture Pattern Evaluation

| Option | Description | Strengths | Risks / Limitations | Notes |
|--------|-------------|-----------|---------------------|-------|
| Razor Pages + Repository | Server-side rendering por página + abstracción de datos | Simple, convención clara, alineado con el stack | Escalabilidad limitada para SPA — fuera de scope | **Elegido** — encaja perfectamente con el brief |
| MVC + Repository | Controladores separados de vistas | Mayor separación para APIs complejas | Más boilerplate para páginas simples | Descartado — Razor Pages es más directo para este caso |
| Generic Repository `IRepository<T>` | Interfaz genérica única para todas las entidades | Menos código de interfaz | Más abstracto, dificulta consultas específicas por entidad | Descartado — `IAlquilerRepository` específico es más legible y suficiente |

---

## Design Decisions

### Decision: `IAlquilerRepository` específico vs. `IRepository<T>` genérico
- **Context**: Req 3.1 y 3.5 requieren una interfaz de acceso a datos intercambiable para las tres entidades.
- **Alternatives Considered**:
  1. `IRepository<T>` genérico — interfaz única paramétrica para cualquier entidad
  2. `IAlquilerRepository` específico — interfaz con métodos explícitos por entidad
- **Selected Approach**: `IAlquilerRepository` específico con secciones por entidad (`Maquina`, `Solicitud`, `BloqueoFecha`).
- **Rationale**: Sólo existe una implementación prevista (en memoria, con posible reemplazo futuro por una única implementación SQL). Un genérico añadiría indirección sin beneficio real. La interfaz específica es más legible para specs downstream.
- **Trade-offs**: Menos código reutilizable si se añaden más entidades en el futuro; a cambio, cada método es explícito y fácil de descubrir.
- **Follow-up**: Si en el futuro se añaden entidades nuevas, extender `IAlquilerRepository` o crear sub-interfaces.

### Decision: Seed data diferido a `catalogo-tarifas`
- **Context**: `InMemoryAlquilerRepository` arranca vacío. El roadmap menciona 6 máquinas.
- **Alternatives Considered**:
  1. Seed en el constructor de `InMemoryAlquilerRepository`
  2. Seed en `Program.cs` tras `builder.Build()`
  3. Sin seed en infraestructura — responsabilidad del spec que lo necesite primero
- **Selected Approach**: Sin seed en infraestructura.
- **Rationale**: Los datos de las máquinas (nombre, descripción, imagen, precio) pertenecen al dominio de `catalogo-tarifas`. Añadirlos aquí crearía acoplamiento de contenido con la infraestructura.
- **Trade-offs**: La página de inicio muestra contenido placeholder sin datos reales hasta que `catalogo-tarifas` esté implementado.
- **Follow-up**: `catalogo-tarifas` debe añadir seed data al construir su página — puede hacerlo en `Program.cs` o en un método de extensión de `IAlquilerRepository`.

### Decision: `DateOnly` para fechas de `Solicitud` y `BloqueoFecha`
- **Context**: Las fechas de inicio y fin representan días de calendario, no instantes de tiempo.
- **Selected Approach**: `DateOnly` (.NET 6+) en lugar de `DateTime`.
- **Rationale**: `DateOnly` es semánticamente correcto para rangos de alquiler por día; evita ambigüedades de zona horaria en operaciones de comparación de fechas.
- **Trade-offs**: Requiere .NET 6+ (cubierto por .NET 10); serialización JSON requiere el handler de `DateOnly` de `System.Text.Json` (incluido en .NET 7+).

---

## Synthesis Outcomes

### Generalization
- Los tres modelos son entidades independientes sin jerarquía → no se generalizó con herencia ni con interfaces de entidad base. Correcto para este alcance.
- Las operaciones de repositorio por entidad son similares (Add/Get/Update) pero tienen firmas específicas → se mantuvo `IAlquilerRepository` sin genericidad.

### Build vs Adopt
- Todo el stack es adoptado (ASP.NET Core, HTMX, ConcurrentDictionary). Sin componentes custom necesarios.

### Simplification
- Sin value objects, domain events, mediadores ni CQRS — plain C# classes es el nivel correcto de abstracción para este scope.
- Sin TagHelpers custom ni ViewComponents para el layout base — Razor puro es suficiente.

---

## Risks & Mitigations
- **Cambio de firma de `IAlquilerRepository`** → Requiere actualización coordinada en todos los specs downstream. Mitigation: tratar `IAlquilerRepository` como API pública; cualquier cambio es un cambio breaking y debe ser explícito.
- **Pérdida de datos al reiniciar** → Esperado y documentado. Mitigation: documentar claramente en README y en la página de inicio que los datos son volátiles hasta que se implemente persistencia real.
- **Versión de HTMX CDN** → unpkg sirve siempre la última 2.x; cambios de comportamiento menores en parches podrían afectar features. Mitigation: anclar a versión específica en producción cuando sea necesario.

---

## References
- Brief de discovery: `.kiro/specs/infraestructura/brief.md`
- Roadmap del proyecto: `.kiro/steering/roadmap.md`
- ASP.NET Core Razor Pages — convención de archivos en `Pages/`
- HTMX 2.x CDN: `https://unpkg.com/htmx.org@2/dist/htmx.min.js`
- `System.Collections.Concurrent.ConcurrentDictionary` — .NET BCL
- `System.Threading.Interlocked.Increment` — .NET BCL

# Research Log: catalogo-tarifas

## Discovery Scope
- **Feature type**: Simple Addition (UI + seed de datos)
- **Discovery process**: Minimal — patrones establecidos en `infraestructura`

## Key Findings

### Patrón HTMX handler en Razor Pages
- Razor Pages soporta handlers nombrados: `OnGetDetalle(int id)` es invocado por `GET /Catalogo?handler=Detalle&id=X`
- HTMX `hx-get="/Catalogo?handler=Detalle&id=@m.Id"` con `hx-target="#detalle-panel"` inyecta el partial sin recarga
- `return Partial("_MaquinaDetalle", maquina)` devuelve solo el HTML del partial, adecuado para HTMX

### Navegación fluida entre páginas
- `hx-boost="true"` en el elemento `<body>` o `<nav>` intercepta los clicks en `<a>` y hace requests AJAX, actualizando solo `<body>` — comportamiento SPA sin JS propio
- Compatible con Razor Pages sin configuración adicional

### Seed de datos
- Patrón recomendado: resolver `IAlquilerRepository` del `app.Services` tras `builder.Build()` y llamar `AddMaquina` antes de `app.Run()`
- En memoria, el seed es automáticamente idempotente porque el store se vacía en cada reinicio

## Design Decisions

| Decisión | Elegida | Alternativa descartada | Razón |
|----------|---------|----------------------|-------|
| Detalle de máquina | HTMX partial handler | Modal JS | Sin JS propio; handler Razor Pages es suficiente |
| Tarifas | Datos estáticos en vista | Tabla en repositorio | Tarifas no cambian frecuentemente; fuera del scope del repositorio |
| Seed | En Program.cs | Constructor del repositorio | Mayor visibilidad y control; separación de responsabilidades |
| Navegación | hx-boost en layout | Links normales | Mejora experiencia sin complejidad adicional |

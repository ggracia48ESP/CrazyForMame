# Implementation Plan

- [ ] 1. Seed de datos — cargar las 6 máquinas en el repositorio al arrancar
- [ ] 1.1 Inicializar las 6 máquinas en Program.cs tras `builder.Build()`
  - Resolver `IAlquilerRepository` del contenedor DI y llamar `AddMaquina` 6 veces antes de `app.Run()`
  - 3 máquinas con `Categoria = CategoriaMaquina.Grande` (nombres representativos de máquinas grandes artesanales)
  - 3 máquinas con `Categoria = CategoriaMaquina.Pequeña` (nombres representativos de máquinas pequeñas artesanales)
  - Cada máquina con: nombre descriptivo, descripción breve del atractivo del evento, `ImagenUrl = "/images/placeholder.jpg"`, y precio base realista
  - `GET /Catalogo` muestra 6 tarjetas tras el arranque sin ninguna configuración manual
  - _Requirements: 4.1, 4.2_

- [ ] 2. Componentes del catálogo — lógica y fragmento HTMX de detalle
- [ ] 2.1 (P) Implementar CatalogoModel con listado agrupado y handler de detalle
  - `OnGet()` carga todas las máquinas del repositorio y las separa en dos colecciones: `MaquinasGrandes` y `MaquinasPequenas`
  - `OnGetDetalle(int id)` invoca `GetMaquinaById(id)`; si existe, retorna `Partial("_MaquinaDetalle", maquina)`; si no, retorna `Content("<p>Máquina no encontrada.</p>", "text/html")`
  - `GET /Catalogo?handler=Detalle&id=1` retorna HTML de detalle de la máquina 1 sin recarga de página completa
  - `GET /Catalogo?handler=Detalle&id=999` retorna el mensaje de "no encontrada" sin error 500
  - _Requirements: 1.1, 2.1, 2.2_
  - _Boundary: CatalogoModel_

- [ ] 2.2 (P) Crear el partial `_MaquinaDetalle.cshtml` con la vista de detalle completo
  - El partial recibe un modelo de tipo `Maquina` y muestra: foto (o placeholder), nombre, descripción completa, categoría, y precio base
  - Incluye un enlace o botón "Solicitar alquiler" que apunta a `/Calendario`
  - El fragmento HTML es autónomo y puede inyectarse en cualquier contenedor de la página
  - _Requirements: 1.2, 2.1, 3.3_
  - _Boundary: _MaquinaDetalle partial_

- [ ] 3. Vistas de catálogo y tarifas
- [ ] 3.1 Crear `Catalogo.cshtml` con grid agrupado y panel de detalle HTMX
  - Renderiza dos secciones: "Máquinas Grandes" y "Máquinas Pequeñas", cada una con tarjetas de las máquinas correspondientes
  - Cada tarjeta incluye foto (o placeholder), nombre, descripción breve, y categoría
  - Las tarjetas tienen `hx-get="/Catalogo?handler=Detalle&id=@m.Id"`, `hx-target="#detalle-panel"`, y `hx-swap="innerHTML"` para cargar el detalle sin recarga
  - Incluye un `<div id="detalle-panel">` donde HTMX inyecta el partial de detalle
  - Las tarjetas de cada categoría son visualmente distinguibles (por ejemplo, borde o etiqueta de color diferente)
  - El diseño es responsive: grid de columnas en escritorio, columna única en móvil
  - `GET /Catalogo` devuelve HTTP 200 con las 6 tarjetas visibles y el panel de detalle vacío
  - _Requirements: 1.1, 1.2, 1.3, 2.1, 5.2, 5.3_
  - _Depends: 2.1, 2.2_

- [ ] 3.2 (P) Crear la página de tarifas con precios y CTA
  - `TarifasModel.OnGet()` mínimo sin lógica (los datos son estáticos en la vista)
  - `Tarifas.cshtml` muestra una tabla de precios con filas para categoría Grande y Pequeña, y columnas para al menos dos duraciones (día completo y fin de semana)
  - Incluye un enlace CTA visible que dirige al visitante a `/Calendario` para consultar disponibilidad
  - `GET /Tarifas` devuelve HTTP 200 con la tabla de precios y el enlace CTA presentes en el HTML
  - _Requirements: 3.1, 3.2, 3.3, 5.2_

- [ ] 4. Navegación fluida — activar hx-boost en el layout
- [ ] 4.1 Añadir `hx-boost="true"` al layout para navegación parcial entre páginas
  - Añadir el atributo `hx-boost="true"` al elemento `<body>` en `Pages/Shared/_Layout.cshtml`
  - La navegación entre `/Catalogo` y `/Tarifas` mediante los enlaces del nav actualiza el contenido sin recarga completa de página (HTMX intercepta el click)
  - _Requirements: 5.1_

- [ ] 5. Verificación — confirmar el comportamiento completo del catálogo y tarifas
- [ ]* 5.1 Tests de integración: catálogo, detalle HTMX y tarifas
  - Verificar que `GET /Catalogo` devuelve HTTP 200 y el HTML contiene 6 máquinas
  - Verificar que `GET /Catalogo?handler=Detalle&id=1` devuelve HTML con el nombre de la máquina 1
  - Verificar que `GET /Catalogo?handler=Detalle&id=999` devuelve el mensaje de "no encontrada" y no lanza excepción
  - Verificar que `GET /Tarifas` devuelve HTTP 200 y el HTML contiene precios para Grande y Pequeña y un enlace a `/Calendario`
  - _Requirements: 1.1, 1.2, 2.1, 2.2, 3.1, 3.2, 3.3_

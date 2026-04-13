# Brief: catalogo-tarifas

## Problem
Los visitantes necesitan conocer las máquinas disponibles y sus precios antes de hacer una solicitud de alquiler. Sin un catálogo claro, no pueden evaluar si el servicio encaja con su evento.

## Current State
No existe catálogo. Las máquinas y tarifas solo se conocen contactando directamente al propietario.

## Desired Outcome
- Página de catálogo con las 6 máquinas (3 grandes, 3 pequeñas), cada una con: foto, nombre, descripción breve, categoría
- Página de tarifas con precios diferenciados por categoría (grande/pequeña), duración del evento y posibles extras
- Navegación fluida entre catálogo y tarifas con HTMX (sin recarga de página completa)
- Diseño atractivo y responsive

## Approach
Razor Pages: `Catalogo.cshtml` y `Tarifas.cshtml`. Las máquinas se obtienen del repositorio (IAlquilerRepository). Las tarifas pueden ser estáticas en la vista o en un modelo de configuración simple. HTMX para carga parcial de detalles de máquina al hacer clic.

## Scope
- **In**: Listado de 6 máquinas con foto/descripción/categoría, página de tarifas, datos iniciales (seed) de las 6 máquinas en el repositorio
- **Out**: Edición de máquinas desde la web (CMS), carrito de compra, comparador de máquinas

## Boundary Candidates
- Vista de catálogo (listado + detalle)
- Vista de tarifas
- Seed de datos de máquinas en el repositorio

## Out of Boundary
- Gestión CRUD de máquinas (no está en scope)
- Integración con formulario de solicitud (eso va en calendario-solicitudes)

## Upstream / Downstream
- **Upstream**: infraestructura (modelos Maquina, IAlquilerRepository)
- **Downstream**: calendario-solicitudes (el usuario pasa del catálogo a solicitar)

## Existing Spec Touchpoints
- **Extends**: infraestructura (añade seed de datos de máquinas)
- **Adjacent**: calendario-solicitudes (el CTA "Solicitar alquiler" enlaza a ese flujo)

## Constraints
- Fotos: placeholders inicialmente, el propietario las sustituirá después
- Las tarifas pueden estar hardcodeadas en la vista inicial; no requieren base de datos

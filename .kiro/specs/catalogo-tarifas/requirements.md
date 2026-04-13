# Requirements Document

## Introduction
El spec `catalogo-tarifas` añade las páginas de catálogo y tarifas al sitio de alquiler de máquinas recreativas artesanales. Los visitantes deben poder explorar las 6 máquinas disponibles (3 grandes, 3 pequeñas), ver sus características y consultar los precios antes de hacer una solicitud de alquiler.

## Boundary Context
- **In scope**: Listado de catálogo, vista de detalle de máquina, página de tarifas, seed de datos de las 6 máquinas en el repositorio
- **Out of scope**: Edición o creación de máquinas desde la web, carrito de compra, comparador de máquinas, formulario de solicitud de alquiler
- **Adjacent expectations**: Depende de `infraestructura` para los tipos `Maquina` y `IAlquilerRepository`; la llamada a la acción "Solicitar alquiler" enlaza a `calendario-solicitudes` sin poseer ese flujo

## Requirements

### 1. Listado de catálogo

**Objective:** As a visitante, I want ver todas las máquinas disponibles agrupadas por categoría, so that pueda evaluar qué opciones encajan con mi evento.

#### Acceptance Criteria
1. When a visitor navigates to `/Catalogo`, the Renting Bartops site shall display all 6 machines grouped by category (Grande / Pequeña).
2. The Renting Bartops site shall show, for each machine: photo (or placeholder image), name, brief description, and category label.
3. The Renting Bartops site shall display the catalog page with a responsive layout that adapts to mobile and desktop screen sizes.

---

### 2. Vista de detalle de máquina

**Objective:** As a visitante, I want ver el detalle completo de una máquina sin recargar la página, so that pueda obtener más información de forma rápida y fluida.

#### Acceptance Criteria
1. When a visitor selects a machine in the catalog, the Renting Bartops site shall display the machine detail (name, full description, category, base price, photo) without a full page reload.
2. If the requested machine ID does not exist, the Renting Bartops site shall display a clear "machine not found" message without crashing.

---

### 3. Página de tarifas

**Objective:** As a visitante, I want consultar los precios diferenciados por categoría y duración, so that pueda planificar el presupuesto de mi evento antes de solicitar el alquiler.

#### Acceptance Criteria
1. When a visitor navigates to `/Tarifas`, the Renting Bartops site shall display pricing information differentiated by machine category (Grande / Pequeña).
2. The Renting Bartops site shall display pricing for at least two event durations (e.g., día completo, fin de semana).
3. The Renting Bartops site shall display a visible call-to-action link directing visitors to check availability or submit a rental request.

---

### 4. Datos iniciales de máquinas (Seed)

**Objective:** As a propietario, I want que las 6 máquinas estén disponibles desde el primer arranque, so that los visitantes vean el catálogo completo sin ninguna configuración manual.

#### Acceptance Criteria
1. When the application starts, the Renting Bartops site shall have 6 machines pre-loaded in the repository: 3 of category Grande and 3 of category Pequeña.
2. The Renting Bartops site shall assign each seeded machine a unique ID, name, brief description, category, placeholder image URL, and base price.

---

### 5. Navegación y experiencia visual

**Objective:** As a visitante, I want navegar entre el catálogo y las tarifas de forma ágil y dentro de un diseño coherente, so that la experiencia sea atractiva y fácil de usar.

#### Acceptance Criteria
1. When a visitor navigates between `/Catalogo` and `/Tarifas`, the Renting Bartops site shall use partial page updates where applicable to minimize full page reloads.
2. The Renting Bartops site shall render the catalog and tariff pages within the shared layout (header, navigation, footer) established by `infraestructura`.
3. The Renting Bartops site shall display catalog machine cards in a grid or list that is visually distinguishable by category.

# Requirements Document

## Introduction
Sistema base para el sitio de alquiler de máquinas recreativas artesanales. El proyecto no tiene código existente. Se necesita establecer la infraestructura fundacional que habilite el desarrollo de todos los specs posteriores: una aplicación web ejecutable en localhost, los modelos de dominio compartidos, una capa de repositorio de datos intercambiable, un layout HTML/CSS con HTMX disponible en todas las páginas, y una página de inicio como punto de entrada.

## Boundary Context
- **In scope**: Proyecto arrancable en localhost, entidades de dominio (Maquina, Solicitud, BloqueoFecha), repositorio en memoria con interfaz intercambiable, layout base (cabecera, navegación, pie de página) con HTMX, página de inicio placeholder
- **Out of scope**: Lógica de negocio, catálogo de máquinas, formulario de solicitud, panel de administración, persistencia real, autenticación
- **Adjacent expectations**: Los specs `catalogo-tarifas`, `calendario-solicitudes` y `panel-administracion` dependen de los modelos de dominio y la interfaz de repositorio definidos en este spec; cualquier cambio en esas interfaces tiene impacto en todos los specs downstream

## Requirements

### Requirement 1: Aplicación ejecutable
**Objetivo:** Como desarrollador, quiero un proyecto web ejecutable en localhost, para poder desarrollar y verificar las funcionalidades del sitio de alquiler de manera local.

#### Acceptance Criteria
1. When the developer starts the application, the Sistema shall respond to HTTP requests on localhost.
2. The Sistema shall serve web pages in response to URL navigation requests.

---

### Requirement 2: Modelos de dominio
**Objetivo:** Como desarrollador, quiero entidades de dominio bien definidas, para que todos los specs del proyecto compartan la misma representación de los datos de alquiler.

#### Acceptance Criteria
1. The Sistema shall define a Maquina entity to represent a rental machine available in the catalog.
2. The Sistema shall define a Solicitud entity to represent a client's rental request, including contact information, the requested machine, and the requested dates.
3. The Sistema shall define a BloqueoFecha entity to represent a date range during which a machine is unavailable for rental.
4. The Sistema shall make these entities accessible to all feature areas of the application.

---

### Requirement 3: Capa de repositorio de datos
**Objetivo:** Como desarrollador, quiero una interfaz de acceso a datos intercambiable, para poder reemplazar el almacenamiento en memoria por una base de datos real en el futuro sin cambiar la lógica de negocio.

#### Acceptance Criteria
1. The Sistema shall provide a data access interface that supports reading and writing Maquina, Solicitud, and BloqueoFecha entities.
2. When the application starts, the Sistema shall initialize an in-memory data store as the active implementation of the data access interface.
3. The Sistema shall expose the data access implementation as a shared application-level service so that all feature areas of the application operate on the same data instance.
4. While multiple HTTP requests are processed concurrently, the Sistema shall maintain data integrity for all entity read and write operations.
5. Where the in-memory implementation is replaced by a different storage implementation, the Sistema shall continue to function without changes to code outside the data layer.

---

### Requirement 4: Layout base compartido
**Objetivo:** Como visitante, quiero una estructura visual consistente en todas las páginas del sitio, para poder navegar y orientarme sin esfuerzo.

#### Acceptance Criteria
1. The Sistema shall display a consistent header, navigation menu, and footer on every page of the application.
2. The Sistema shall apply a shared CSS stylesheet to every page of the application.
3. The Sistema shall make HTMX available on every page without requiring per-page configuration.
4. When a visitor navigates between pages, the Sistema shall preserve the common layout structure across all pages.

---

### Requirement 5: Página de inicio
**Objetivo:** Como visitante, quiero una página de inicio operativa, para tener un punto de entrada al sitio desde el que explorar el catálogo y los servicios de alquiler.

#### Acceptance Criteria
1. When a visitor navigates to the root URL of the application, the Sistema shall display the home page.
2. The Sistema shall render the home page within the base layout, including the shared header, navigation, and footer.

# Requirements Document

## Introduction
El spec `panel-administracion` añade el panel de gestión para el propietario del sitio. Desde `/Admin`, el propietario puede revisar las solicitudes entrantes, aprobarlas o rechazarlas (con email automático al cliente), gestionar bloqueos de fechas en el calendario, y consultar una vista de calendario con el estado completo. El acceso está protegido con credenciales simples configuradas en el sistema.

## Boundary Context
- **In scope**: Protección de acceso al panel, listado de solicitudes con acciones, aprobación y rechazo con email al cliente, gestión de bloqueos de fecha (alta y baja), vista de calendario admin con solicitudes y bloqueos superpuestos
- **Out of scope**: Roles múltiples de administrador, histórico de cambios o auditoría, estadísticas o reportes, integración con calendarios externos (Google Calendar), edición del catálogo de máquinas
- **Adjacent expectations**: Depende de `infraestructura` para `IAlquilerRepository`, `Solicitud`, `BloqueoFecha` y `EstadoSolicitud`; depende de `calendario-solicitudes` para el servicio de email compartido (mismo proveedor SMTP); las solicitudes creadas por `calendario-solicitudes` son las que este panel gestiona; los bloqueos creados aquí son leídos por la lógica de disponibilidad de `calendario-solicitudes`

## Requirements

### 1. Acceso protegido al panel de administración

**Objective:** As a propietario, I want que el panel esté protegido con credenciales, so that nadie más pueda gestionar las solicitudes o bloquear fechas.

#### Acceptance Criteria
1. The Renting Bartops site shall restrict access to all pages under `/Admin` to authenticated administrators only.
2. When an unauthenticated visitor navigates to any `/Admin` page, the Renting Bartops site shall redirect them to the login page or display an authentication challenge before allowing access.
3. When an administrator provides correct credentials, the Renting Bartops site shall grant access to the admin panel and maintain the session for subsequent requests.
4. If an administrator provides incorrect credentials, the Renting Bartops site shall deny access and display a clear error message without revealing which credential is wrong.
5. The Renting Bartops site shall read administrator credentials from application configuration, not from hardcoded values in the source code.

---

### 2. Listado de solicitudes

**Objective:** As a propietario, I want ver todas las solicitudes con su estado y datos del cliente, so that pueda revisar y gestionar cada solicitud.

#### Acceptance Criteria
1. When an authenticated administrator navigates to the admin panel, the Renting Bartops site shall display all rental requests with the following information: nombre del cliente, email, teléfono, máquina deseada, fecha del evento, duración, notas adicionales si las hay, y estado actual (Pendiente / Aprobada / Rechazada).
2. The Renting Bartops site shall display requests ordered by creation date, showing the most recent first.
3. The Renting Bartops site shall display the available actions for each request according to its current status: requests with status Pendiente shall show Aprobar and Rechazar actions; requests with status Aprobada or Rechazada shall not show state-change actions.

---

### 3. Aprobación de solicitudes

**Objective:** As a propietario, I want aprobar solicitudes para confirmar el alquiler al cliente, so that el cliente sepa que su evento está confirmado y las fechas queden marcadas como ocupadas.

#### Acceptance Criteria
1. When an authenticated administrator approves a Pendiente request, the Renting Bartops site shall change the request status to Aprobada without a full page reload.
2. When a request is approved, the Renting Bartops site shall send a confirmation email to the client's email address notifying them that their rental request has been approved.
3. When a request is approved, the Renting Bartops site shall reflect the corresponding dates as occupied in the public availability calendar.
4. If the email fails to send during approval, the Renting Bartops site shall still update the request status to Aprobada; the email failure shall not prevent the state change.

---

### 4. Rechazo de solicitudes

**Objective:** As a propietario, I want rechazar solicitudes que no pueda atender, so that el cliente reciba una respuesta y pueda buscar otras opciones.

#### Acceptance Criteria
1. When an authenticated administrator rejects a Pendiente request, the Renting Bartops site shall change the request status to Rechazada without a full page reload.
2. When a request is rejected, the Renting Bartops site shall send a rejection email to the client's email address notifying them that their request could not be accepted.
3. If the email fails to send during rejection, the Renting Bartops site shall still update the request status to Rechazada; the email failure shall not prevent the state change.

---

### 5. Gestión de bloqueos de fecha

**Objective:** As a propietario, I want bloquear y desbloquear rangos de fechas, so that el calendario público refleje mis compromisos previos o períodos de no disponibilidad.

#### Acceptance Criteria
1. When an authenticated administrator adds a date block with a valid start date, end date, and optional reason, the Renting Bartops site shall save the block in the repository and the blocked dates shall appear as occupied in the public availability calendar.
2. When an authenticated administrator removes an existing date block, the Renting Bartops site shall delete the block and the previously blocked dates shall become available again in the public calendar.
3. If the administrator provides a start date after the end date when adding a block, the Renting Bartops site shall display a validation error and shall not save the block.
4. The Renting Bartops site shall display the list of all current date blocks with their start date, end date, and reason (if provided) in the admin panel.

---

### 6. Vista de calendario en el panel de administración

**Objective:** As a propietario, I want ver un calendario con todas las solicitudes y bloqueos superpuestos, so that tenga una visión completa de la ocupación del mes.

#### Acceptance Criteria
1. When an authenticated administrator accesses the admin calendar view, the Renting Bartops site shall display a monthly calendar showing pending requests, approved requests, and date blocks in visually distinguishable states.
2. When an administrator navigates to the next or previous month in the admin calendar, the Renting Bartops site shall update the calendar view without a full page reload.
3. The Renting Bartops site shall display the admin calendar within the same protected panel, without exposing management-specific details to public visitors.

# Requirements Document

## Introduction
El spec `calendario-solicitudes` permite a los visitantes consultar la disponibilidad de las máquinas en un calendario mensual público y enviar solicitudes de alquiler directamente desde la web. El propietario recibe un aviso por email con cada nueva solicitud. La aprobación o rechazo de solicitudes, así como la gestión de bloqueos de fechas por parte del admin, son responsabilidad de `panel-administracion`.

## Boundary Context
- **In scope**: Calendario mensual público con disponibilidad, navegación entre meses, formulario de solicitud al clicar una fecha disponible, confirmación inmediata en pantalla, notificación por email al propietario, guardado de la solicitud en el repositorio con estado Pendiente
- **Out of scope**: Aprobación o rechazo de solicitudes (panel-administracion), gestión de bloqueos de fechas por el admin (panel-administracion), email de confirmación al cliente (panel-administracion), pago online, selección de hora exacta (granularidad de día), recordatorios automáticos
- **Adjacent expectations**: Depende de `infraestructura` para los tipos `Solicitud`, `BloqueoFecha` e `IAlquilerRepository`; las solicitudes creadas aquí son consumidas por `panel-administracion`; el catálogo de máquinas provisto por `catalogo-tarifas` puede usarse como referencia al seleccionar máquinas en el formulario

## Requirements

### 1. Calendario de disponibilidad público

**Objective:** As a visitante, I want ver qué fechas están disponibles y cuáles no, so that pueda elegir una fecha antes de hacer mi solicitud de alquiler.

#### Acceptance Criteria
1. When a visitor navigates to `/Calendario`, the Renting Bartops site shall display a monthly calendar view for the current month.
2. The Renting Bartops site shall visually distinguish between three date states: available, occupied, and past.
3. The Renting Bartops site shall mark a date as occupied if it falls within the date range of any `BloqueoFecha` entry in the repository.
4. The Renting Bartops site shall mark a date as occupied if it falls within the date range of any `Solicitud` with status `Aprobada` in the repository.
5. The Renting Bartops site shall mark a date as available if no `BloqueoFecha` or approved `Solicitud` covers that date and the date is not in the past.
6. When a visitor attempts to interact with an occupied or past date, the Renting Bartops site shall not open the rental request form.

---

### 2. Navegación entre meses

**Objective:** As a visitante, I want navegar al mes anterior o siguiente sin recargar la página, so that pueda explorar la disponibilidad en diferentes períodos.

#### Acceptance Criteria
1. When a visitor clicks the "next month" or "previous month" control, the Renting Bartops site shall update the calendar to display the selected month without a full page reload.
2. The Renting Bartops site shall show the month and year currently displayed as a visible heading in the calendar.
3. While navigating between months, the Renting Bartops site shall maintain the correct availability state for each displayed date.

---

### 3. Selección de fecha y apertura del formulario

**Objective:** As a visitante, I want que al clicar una fecha disponible se abra el formulario de solicitud, so that pueda iniciar mi solicitud directamente desde el calendario.

#### Acceptance Criteria
1. When a visitor clicks an available date on the calendar, the Renting Bartops site shall display the rental request form associated with that date without a full page reload.
2. The Renting Bartops site shall pre-fill the selected date in the rental request form.

---

### 4. Formulario de solicitud de alquiler

**Objective:** As a visitante, I want rellenar y enviar un formulario con mis datos y preferencias de alquiler, so that el propietario pueda evaluar y gestionar mi solicitud.

#### Acceptance Criteria
1. The Renting Bartops site shall collect the following required fields in the rental request form: nombre del cliente, email del cliente, teléfono del cliente, fecha del evento, máquina o máquinas deseadas, y duración del evento.
2. The Renting Bartops site shall include an optional field for notas adicionales in the rental request form.
3. When a visitor submits the rental request form with all required fields valid, the Renting Bartops site shall save the request in the repository with status `Pendiente`.
4. If a visitor submits the rental request form with missing or invalid required fields, the Renting Bartops site shall display inline validation error messages next to the affected fields and shall not save the request.
5. If a visitor submits the rental request form with an invalid email format, the Renting Bartops site shall display a validation error for the email field.

---

### 5. Confirmación inmediata al cliente

**Objective:** As a visitante, I want ver una confirmación en pantalla al enviar mi solicitud, so that sepa que mi solicitud ha sido recibida correctamente.

#### Acceptance Criteria
1. When a rental request is successfully submitted and saved, the Renting Bartops site shall display an immediate confirmation message on screen indicating that the request has been received and is pending review.
2. The Renting Bartops site shall display the confirmation message without a full page reload.

---

### 6. Notificación por email al propietario

**Objective:** As a propietario, I want recibir un email cuando llega una nueva solicitud, so that pueda revisarla y actuar con rapidez.

#### Acceptance Criteria
1. When a rental request is successfully saved, the Renting Bartops site shall send an email notification to the configured owner email address containing: nombre del cliente, email, teléfono, máquina(s) deseadas, fecha del evento, duración, y notas adicionales si las hay.
2. If the email notification fails to send, the Renting Bartops site shall still save the rental request and display the confirmation message to the client; the email failure shall not block the submission flow.
3. The Renting Bartops site shall read the owner email address and SMTP configuration from the application configuration, not from hardcoded values.

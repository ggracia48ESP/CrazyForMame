CREATE TABLE IF NOT EXISTS maquinas (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  nombre VARCHAR(200) NOT NULL,
  descripcion VARCHAR(1000) NOT NULL DEFAULT '',
  categoria ENUM('Grande','Pequeña') NOT NULL,
  imagen_url VARCHAR(500) NOT NULL DEFAULT '/img/placeholder.svg',
  precio_base DECIMAL(10,2) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS solicitudes (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  maquina_id INT UNSIGNED NOT NULL,
  nombre_cliente VARCHAR(100) NOT NULL,
  email_cliente VARCHAR(200) NOT NULL,
  telefono_cliente VARCHAR(30) NOT NULL,
  fecha_inicio DATE NOT NULL,
  fecha_fin DATE NOT NULL,
  estado ENUM('Pendiente','Aprobada','Rechazada') NOT NULL DEFAULT 'Pendiente',
  fecha_creacion DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_solicitudes_maquina FOREIGN KEY (maquina_id) REFERENCES maquinas(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS bloqueos_fecha (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  maquina_id INT UNSIGNED NOT NULL,
  fecha_inicio DATE NOT NULL,
  fecha_fin DATE NOT NULL,
  motivo VARCHAR(300) NULL,
  CONSTRAINT fk_bloqueos_maquina FOREIGN KEY (maquina_id) REFERENCES maquinas(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

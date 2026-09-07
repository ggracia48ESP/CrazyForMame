<?php
declare(strict_types=1);

require __DIR__ . '/../app/bootstrap.php';
require_admin();

$title = 'Panel de administración';
$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    check_csrf();
    $action = $_POST['action'] ?? '';
    try {
        if ($action === 'estado') {
            $estado = $_POST['estado'] ?? '';
            if (!in_array($estado, ['Pendiente', 'Aprobada', 'Rechazada'], true)) throw new RuntimeException('Estado no válido.');
            $pdo->prepare('UPDATE solicitudes SET estado = ? WHERE id = ?')->execute([$estado, (int)($_POST['id'] ?? 0)]);
            $message = 'Estado de la solicitud actualizado.';
        } elseif ($action === 'bloqueo') {
            $inicio = $_POST['fecha_inicio'] ?? ''; $fin = $_POST['fecha_fin'] ?? ''; $maquinaId = (int)($_POST['maquina_id'] ?? 0);
            if (!$maquinaId || !$inicio || !$fin || $fin < $inicio) throw new RuntimeException('Revisa la máquina y el intervalo de fechas.');
            $pdo->prepare('INSERT INTO bloqueos_fecha (maquina_id, fecha_inicio, fecha_fin, motivo) VALUES (?, ?, ?, ?)')->execute([$maquinaId, $inicio, $fin, trim($_POST['motivo'] ?? '')]);
            $message = 'Fechas bloqueadas correctamente.';
        } elseif ($action === 'borrar_bloqueo') {
            $pdo->prepare('DELETE FROM bloqueos_fecha WHERE id = ?')->execute([(int)($_POST['id'] ?? 0)]); $message = 'Bloqueo eliminado.';
        } elseif ($action === 'guardar_maquina') {
            $id = (int)($_POST['id'] ?? 0); $nombre = trim($_POST['nombre'] ?? ''); $descripcion = trim($_POST['descripcion'] ?? ''); $categoria = $_POST['categoria'] ?? ''; $precio = (float)($_POST['precio_base'] ?? 0);
            if ($nombre === '' || !in_array($categoria, ['Grande', 'Pequeña'], true) || $precio < 0) throw new RuntimeException('Nombre, categoría y precio son obligatorios y deben ser válidos.');
            $imagen = null;
            if (!empty($_FILES['imagen']['name']) && is_uploaded_file($_FILES['imagen']['tmp_name'])) {
                $extension = strtolower(pathinfo($_FILES['imagen']['name'], PATHINFO_EXTENSION));
                if (!in_array($extension, ['jpg', 'jpeg', 'png', 'webp'], true)) throw new RuntimeException('La imagen debe ser JPG, PNG o WEBP.');
                $uploadDir = __DIR__ . '/../uploads';
                if (!is_dir($uploadDir) && !mkdir($uploadDir, 0755, true) && !is_dir($uploadDir)) throw new RuntimeException('No se pudo preparar la carpeta de imágenes.');
                $nombreArchivo = bin2hex(random_bytes(12)) . '.' . $extension;
                if (!move_uploaded_file($_FILES['imagen']['tmp_name'], $uploadDir . '/' . $nombreArchivo)) throw new RuntimeException('No se pudo guardar la imagen.');
                $imagen = '/uploads/' . $nombreArchivo;
            }
            if ($id) {
                $sql = 'UPDATE maquinas SET nombre = ?, descripcion = ?, categoria = ?, precio_base = ?'; $params = [$nombre, $descripcion, $categoria, $precio];
                if ($imagen !== null) { $sql .= ', imagen_url = ?'; $params[] = $imagen; }
                $sql .= ' WHERE id = ?'; $params[] = $id; $pdo->prepare($sql)->execute($params); $message = 'Máquina actualizada.';
            } else {
                $pdo->prepare('INSERT INTO maquinas (nombre, descripcion, categoria, precio_base, imagen_url) VALUES (?, ?, ?, ?, ?)')->execute([$nombre, $descripcion, $categoria, $precio, $imagen ?? '/img/placeholder.svg']); $message = 'Máquina añadida al catálogo.';
            }
        } elseif ($action === 'borrar_maquina') {
            $id = (int)($_POST['id'] ?? 0); $q = $pdo->prepare('SELECT COUNT(*) FROM solicitudes WHERE maquina_id = ?'); $q->execute([$id]);
            if ((int)$q->fetchColumn() > 0) throw new RuntimeException('No se puede borrar: la máquina tiene solicitudes asociadas.');
            $pdo->prepare('DELETE FROM maquinas WHERE id = ?')->execute([$id]); $message = 'Máquina eliminada.';
        }
    } catch (Throwable $exception) { $error = $exception->getMessage(); }
}

$solicitudes = $pdo->query('SELECT s.*, m.nombre AS maquina_nombre FROM solicitudes s JOIN maquinas m ON m.id = s.maquina_id ORDER BY s.fecha_creacion DESC')->fetchAll();
$maquinas = $pdo->query('SELECT * FROM maquinas ORDER BY id')->fetchAll();
$bloqueos = $pdo->query('SELECT b.*, m.nombre AS maquina_nombre FROM bloqueos_fecha b JOIN maquinas m ON m.id = b.maquina_id ORDER BY b.fecha_inicio')->fetchAll();
$view_file = __DIR__ . '/dashboard.view.php'; require __DIR__ . '/layout.php';

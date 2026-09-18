<?php
declare(strict_types=1);
require __DIR__ . '/../app/bootstrap.php';
require_admin();

$title = 'Panel de administración'; $message = ''; $error = '';
function save_admin_image(array $file): string {
    if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK || !is_uploaded_file($file['tmp_name'])) throw new RuntimeException('No se pudo recibir una de las imágenes.');
    $extension = strtolower(pathinfo((string)$file['name'], PATHINFO_EXTENSION));
    if (!in_array($extension, ['jpg', 'jpeg', 'png', 'webp'], true)) throw new RuntimeException('Las imágenes deben ser JPG, PNG o WEBP.');
    if (($file['size'] ?? 0) > 8 * 1024 * 1024) throw new RuntimeException('Cada imagen puede ocupar como máximo 8 MB.');
    $uploadDir = __DIR__ . '/../uploads';
    if (!is_dir($uploadDir) && !mkdir($uploadDir, 0755, true) && !is_dir($uploadDir)) throw new RuntimeException('No se pudo preparar la carpeta de imágenes.');
    $filename = bin2hex(random_bytes(12)) . '.' . $extension;
    if (!move_uploaded_file($file['tmp_name'], $uploadDir . '/' . $filename)) throw new RuntimeException('No se pudo guardar la imagen.');
    return '/uploads/' . $filename;
}
function uploaded_files(string $field): array {
    if (empty($_FILES[$field]['name'])) return [];
    $files = [];
    foreach ((array)$_FILES[$field]['name'] as $i => $name) $files[] = ['name'=>$name,'type'=>$_FILES[$field]['type'][$i] ?? '','tmp_name'=>$_FILES[$field]['tmp_name'][$i] ?? '','error'=>$_FILES[$field]['error'][$i] ?? UPLOAD_ERR_NO_FILE,'size'=>$_FILES[$field]['size'][$i] ?? 0];
    return $files;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    check_csrf(); $action = $_POST['action'] ?? '';
    try {
        if ($action === 'estado') {
            $estado = $_POST['estado'] ?? ''; if (!in_array($estado, ['Pendiente', 'Aprobada', 'Rechazada'], true)) throw new RuntimeException('Estado no válido.');
            $pdo->prepare('UPDATE solicitudes SET estado = ? WHERE id = ?')->execute([$estado, (int)($_POST['id'] ?? 0)]); $message = 'Estado de la solicitud actualizado.';
        } elseif ($action === 'bloqueo') {
            $inicio = $_POST['fecha_inicio'] ?? ''; $fin = $_POST['fecha_fin'] ?? ''; $maquinaId = (int)($_POST['maquina_id'] ?? 0);
            if (!$maquinaId || !$inicio || !$fin || $fin < $inicio) throw new RuntimeException('Revisa la máquina y el intervalo de fechas.');
            $pdo->prepare('INSERT INTO bloqueos_fecha (maquina_id, fecha_inicio, fecha_fin, motivo) VALUES (?, ?, ?, ?)')->execute([$maquinaId, $inicio, $fin, trim($_POST['motivo'] ?? '')]); $message = 'Fechas bloqueadas correctamente.';
        } elseif ($action === 'borrar_bloqueo') {
            $pdo->prepare('DELETE FROM bloqueos_fecha WHERE id = ?')->execute([(int)($_POST['id'] ?? 0)]); $message = 'Bloqueo eliminado.';
        } elseif ($action === 'guardar_maquina') {
            $id = (int)($_POST['id'] ?? 0); $nombre = trim($_POST['nombre'] ?? ''); $descripcion = trim($_POST['descripcion'] ?? ''); $categoria = $_POST['categoria'] ?? ''; $precio = (float)($_POST['precio_base'] ?? 0);
            if ($nombre === '' || !in_array($categoria, ['Grande', 'Pequeña'], true) || $precio < 0) throw new RuntimeException('Nombre, categoría y precio son obligatorios y deben ser válidos.');
            $imagen = null; if (!empty($_FILES['imagen']['name'])) $imagen = save_admin_image($_FILES['imagen']);
            if ($id) { $sql = 'UPDATE maquinas SET nombre = ?, descripcion = ?, categoria = ?, precio_base = ?'; $params = [$nombre, $descripcion, $categoria, $precio]; if ($imagen !== null) { $sql .= ', imagen_url = ?'; $params[] = $imagen; } $sql .= ' WHERE id = ?'; $params[] = $id; $pdo->prepare($sql)->execute($params); $message = 'Máquina actualizada.'; }
            else { $pdo->prepare('INSERT INTO maquinas (nombre, descripcion, categoria, precio_base, imagen_url) VALUES (?, ?, ?, ?, ?)')->execute([$nombre, $descripcion, $categoria, $precio, $imagen ?? '/img/placeholder.svg']); $message = 'Máquina añadida al catálogo.'; }
        } elseif ($action === 'borrar_maquina') {
            $id = (int)($_POST['id'] ?? 0); $q = $pdo->prepare('SELECT COUNT(*) FROM solicitudes WHERE maquina_id = ?'); $q->execute([$id]); if ((int)$q->fetchColumn() > 0) throw new RuntimeException('No se puede borrar: la máquina tiene solicitudes asociadas.');
            $pdo->prepare('DELETE FROM maquinas WHERE id = ?')->execute([$id]); $message = 'Máquina eliminada.';
        } elseif ($action === 'guardar_tarifa') {
            $id=(int)($_POST['id']??0); $tipo=$_POST['tipo']??''; $nombre=trim($_POST['nombre']??''); $precio=trim($_POST['precio']??''); $unidad=trim($_POST['unidad']??''); $descripcion=trim($_POST['descripcion']??''); $destacada=!empty($_POST['destacada'])?1:0; $orden=(int)($_POST['orden']??0); $activa=!empty($_POST['activa'])?1:0;
            if (!in_array($tipo,['alquiler','extra'],true) || $nombre==='' || $precio==='') throw new RuntimeException('Tipo, nombre y precio son obligatorios.');
            if($id) $pdo->prepare('UPDATE tarifas SET tipo=?,nombre=?,precio=?,unidad=?,descripcion=?,destacada=?,orden=?,activa=? WHERE id=?')->execute([$tipo,$nombre,$precio,$unidad,$descripcion,$destacada,$orden,$activa,$id]); else $pdo->prepare('INSERT INTO tarifas (tipo,nombre,precio,unidad,descripcion,destacada,orden,activa) VALUES (?,?,?,?,?,?,?,?)')->execute([$tipo,$nombre,$precio,$unidad,$descripcion,$destacada,$orden,$activa]);
            $message='Tarifa guardada correctamente.';
        } elseif ($action === 'borrar_tarifa') {
            $pdo->prepare('DELETE FROM tarifas WHERE id=?')->execute([(int)($_POST['id']??0)]); $message='Tarifa eliminada.';
        } elseif ($action === 'guardar_evento') {
            $id=(int)($_POST['id']??0); $titulo=trim($_POST['titulo']??''); $descripcion=trim($_POST['descripcion']??''); $fecha=trim($_POST['fecha_evento']??'') ?: null;
            if($titulo==='') throw new RuntimeException('El título del evento es obligatorio.');
            if($id) $pdo->prepare('UPDATE galeria_eventos SET titulo=?,descripcion=?,fecha_evento=? WHERE id=?')->execute([$titulo,$descripcion,$fecha,$id]); else { $pdo->prepare('INSERT INTO galeria_eventos (titulo,descripcion,fecha_evento) VALUES (?,?,?)')->execute([$titulo,$descripcion,$fecha]); $id=(int)$pdo->lastInsertId(); }
            foreach(uploaded_files('imagenes') as $file) $pdo->prepare('INSERT INTO galeria_imagenes (evento_id,imagen_url,texto_alternativo) VALUES (?,?,?)')->execute([$id,save_admin_image($file),$titulo]); $message='Evento guardado correctamente.';
        } elseif ($action === 'anadir_imagenes') {
            $id=(int)($_POST['evento_id']??0); $q=$pdo->prepare('SELECT titulo FROM galeria_eventos WHERE id=?'); $q->execute([$id]); $evento=$q->fetch(); if(!$evento) throw new RuntimeException('El evento no existe.');
            foreach(uploaded_files('imagenes') as $file) $pdo->prepare('INSERT INTO galeria_imagenes (evento_id,imagen_url,texto_alternativo) VALUES (?,?,?)')->execute([$id,save_admin_image($file),$evento['titulo']]); $message='Imágenes añadidas a la galería.';
        } elseif ($action === 'borrar_imagen') {
            $pdo->prepare('DELETE FROM galeria_imagenes WHERE id=?')->execute([(int)($_POST['id']??0)]); $message='Imagen eliminada.';
        } elseif ($action === 'borrar_evento') {
            $pdo->prepare('DELETE FROM galeria_eventos WHERE id=?')->execute([(int)($_POST['id']??0)]); $message='Evento eliminado.';
        }
    } catch (Throwable $exception) { $error = $exception->getMessage(); }
}
$solicitudes = $pdo->query('SELECT s.*, m.nombre AS maquina_nombre FROM solicitudes s JOIN maquinas m ON m.id = s.maquina_id ORDER BY s.fecha_creacion DESC')->fetchAll();
$maquinas = $pdo->query('SELECT * FROM maquinas ORDER BY id')->fetchAll();
$bloqueos = $pdo->query('SELECT b.*, m.nombre AS maquina_nombre FROM bloqueos_fecha b JOIN maquinas m ON m.id = b.maquina_id ORDER BY b.fecha_inicio')->fetchAll();
$tarifas = $pdo->query('SELECT * FROM tarifas ORDER BY tipo, orden, id')->fetchAll();
$eventos = $pdo->query('SELECT * FROM galeria_eventos ORDER BY COALESCE(fecha_evento, fecha_creacion) DESC, id DESC')->fetchAll();
$imagenes = $pdo->query('SELECT * FROM galeria_imagenes ORDER BY id')->fetchAll(); $imagenesPorEvento=[]; foreach($imagenes as $imagen) $imagenesPorEvento[(int)$imagen['evento_id']][]=$imagen;
$view_file = __DIR__ . '/dashboard.view.php'; require __DIR__ . '/layout.php';

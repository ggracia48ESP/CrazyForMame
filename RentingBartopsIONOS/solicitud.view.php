<div class="page-header">
    <h1>Solicitar alquiler</h1>
    <p>Rellena el formulario y te confirmaremos disponibilidad en menos de 24 h.</p>
</div>
<section class="solicitar-redes">
    <h2>También puedes contactarnos</h2>
    <div class="social-links">
        <a href="https://wa.me/34679504035" target="_blank" rel="noopener noreferrer">WhatsApp · 679 504 035</a>
        <a href="https://www.instagram.com/crazy4arcade/" target="_blank" rel="noopener noreferrer">Instagram · @crazy4arcade</a>
        <a href="https://www.youtube.com/@crazy4arcade19" target="_blank" rel="noopener noreferrer">YouTube · Crazy4Arcade</a>
    </div>
</section>

<?php if ($sent): ?>
    <?php if ($mail_ok): ?>
        <div class="alert alert-success"><strong>¡Solicitud recibida!</strong> Te hemos enviado una confirmación por email y nos pondremos en contacto contigo en menos de 24 horas.</div>
    <?php else: ?>
        <div class="alert alert-error"><strong>Solicitud registrada, pero el email no se pudo enviar.</strong> Hemos guardado tus datos. Puedes contactarnos por WhatsApp para confirmar que la hemos recibido.</div>
    <?php endif; ?>
<?php else: ?>
    <?php if ($errors): ?><div class="alert alert-error"><?= e(implode(' ', $errors)) ?></div><?php endif; ?>
    <form method="post" class="solicitud-form">
        <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
        <fieldset>
            <legend>Máquina y fechas</legend>
            <div class="form-group">
                <label for="maquina_id">Máquina</label>
                <select name="maquina_id" id="maquina_id" class="form-control" required>
                    <option value="">-- Selecciona una máquina --</option>
                    <?php foreach ($maquinas as $m): ?>
                        <option value="<?= (int)$m['id'] ?>" <?= $input['maquina_id'] == $m['id'] ? 'selected' : '' ?>>
                            <?= e($m['nombre']) ?> (<?= e($m['categoria']) ?> — <?= price($m['precio_base']) ?>/día)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label for="fecha_inicio">Fecha de inicio</label>
                    <input type="date" name="fecha_inicio" id="fecha_inicio" value="<?= e($input['fecha_inicio']) ?>" min="<?= date('Y-m-d') ?>" class="form-control" required>
                </div>
                <div class="form-group">
                    <label for="fecha_fin">Fecha de fin</label>
                    <input type="date" name="fecha_fin" id="fecha_fin" value="<?= e($input['fecha_fin']) ?>" min="<?= date('Y-m-d') ?>" class="form-control" required>
                </div>
            </div>
        </fieldset>
        <fieldset>
            <legend>Tus datos</legend>
            <div class="form-group">
                <label for="nombre_cliente">Nombre completo</label>
                <input name="nombre_cliente" id="nombre_cliente" value="<?= e($input['nombre_cliente']) ?>" class="form-control" required>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label for="email_cliente">Correo electrónico</label>
                    <input type="email" name="email_cliente" id="email_cliente" value="<?= e($input['email_cliente']) ?>" class="form-control" required>
                </div>
                <div class="form-group">
                    <label for="telefono_cliente">Teléfono</label>
                    <input name="telefono_cliente" id="telefono_cliente" value="<?= e($input['telefono_cliente']) ?>" class="form-control" required>
                </div>
            </div>
            <div class="form-group">
                <label for="notas">Notas o más información</label>
                <textarea name="notas" id="notas" class="form-control" maxlength="2000" rows="5" placeholder="Cuéntanos tus dudas, el tipo de evento o cualquier detalle que debamos saber."><?= e($input['notas']) ?></textarea>
            </div>
        </fieldset>
        <div class="form-actions">
            <button type="submit" class="btn btn-primary">Enviar solicitud</button>
            <a href="/catalogo.php" class="btn btn-secondary">Cancelar</a>
        </div>
    </form>
<?php endif; ?>

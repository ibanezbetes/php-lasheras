<!-- ============================================================
     VUsuarioForm.php - Formulario de crear/editar usuario (vista PHP)
     
     NOTA: Este formulario ya NO se usa directamente. La generación
     del formulario se hace ahora desde JavaScript (usuarios.js)
     mediante las funciones mostrarFormularioCrear() y mostrarFormularioEditar().
     
     Se mantiene como referencia de la estructura del formulario.
     
     Recibe (si se usa): $datos['usuario'] con los datos del usuario a editar
     ============================================================ -->

<?php
// Cargar datos del usuario si vienen (modo edición)
$usuario = isset($datos['usuario']) ? $datos['usuario'] : null;
$esEdicion = ($usuario !== null);
?>

<div class="card">
    <div class="card-header">
        <h5 class="card-title mb-0"><?php echo $esEdicion ? 'Editar Usuario' : 'Nuevo Usuario'; ?></h5>
    </div>
    <div class="card-body">
        <div id="mensajesUsuario"></div>
        <form id="formUsuario">
            <?php if ($esEdicion): ?>
                <input type="hidden" id="idUsuario" value="<?php echo $usuario['idUsuario']; ?>">
            <?php endif; ?>

            <!-- Nombre y Primer Apellido -->
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Nombre *</label>
                    <input type="text" class="form-control" id="nombreUsuario" name="nombre"
                           value="<?php echo $esEdicion ? $usuario['nombre'] : ''; ?>" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Primer Apellido *</label>
                    <input type="text" class="form-control" id="apellido1Usuario" name="apellido1"
                           value="<?php echo $esEdicion ? $usuario['apellido1'] : ''; ?>" required>
                </div>
            </div>

            <!-- Segundo Apellido y Email -->
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Segundo Apellido</label>
                    <input type="text" class="form-control" id="apellido2Usuario" name="apellido2"
                           value="<?php echo $esEdicion ? ($usuario['apellido2'] ?? '') : ''; ?>">
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Email *</label>
                    <input type="email" class="form-control" id="mailUsuario" name="mail"
                           value="<?php echo $esEdicion ? $usuario['mail'] : ''; ?>" required>
                </div>
            </div>

            <!-- Móvil y Sexo -->
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Móvil</label>
                    <input type="text" class="form-control" id="movilUsuario" name="movil"
                           value="<?php echo $esEdicion ? ($usuario['movil'] ?? '') : ''; ?>">
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Sexo</label>
                    <select class="form-control" id="sexoUsuario" name="sexo">
                        <option value="H" <?php echo ($esEdicion && $usuario['sexo'] === 'H') ? 'selected' : ''; ?>>Hombre</option>
                        <option value="M" <?php echo ($esEdicion && $usuario['sexo'] === 'M') ? 'selected' : ''; ?>>Mujer</option>
                    </select>
                </div>
            </div>

            <!-- Botones de acción -->
            <div class="d-grid gap-2 d-md-flex justify-content-md-end mt-3">
                <?php if ($esEdicion): ?>
                    <button type="button" class="btn btn-primary" onclick="actualizarUsuario();">Actualizar</button>
                <?php else: ?>
                    <button type="button" class="btn btn-primary" onclick="guardarUsuario();">Guardar</button>
                <?php endif; ?>
                <button type="button" class="btn btn-secondary" onclick="cancelarFormulario();">Cancelar</button>
            </div>
        </form>
    </div>
</div>
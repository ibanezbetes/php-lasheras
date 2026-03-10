<?php
/**
 * login.php - Página de inicio de sesión
 * 
 * Gestiona la autenticación de usuarios:
 * 1. Muestra el formulario de login (usuario + contraseña)
 * 2. Valida las credenciales enviadas por POST
 * 3. Si son correctas, guarda el usuario en $_SESSION y redirige a index.php
 * 4. Si no, muestra un mensaje de error
 * 
 * Credenciales: usuario 'admin', contraseña '123'
 * NOTA: En producción, las credenciales deberían comprobarse contra la BD
 * usando password_hash() / password_verify() en vez de comparación directa.
 */
session_start();

// Inicializar variables de formulario
$usuario = '';
$pass    = '';

// Extraer variables POST a variables locales ($usuario, $pass)
extract($_POST);

$msj = '';  // Mensaje de error/información para el usuario

// ---------------------------------------------------------------
// Validación de credenciales
// ---------------------------------------------------------------
if ($usuario == '' || $pass == '') {
    // Campos vacíos: mostrar aviso (también se valida en JS del cliente)
    $msj = 'Debes completar los campos.';
} else {
    // Comprobar credenciales (hardcodeadas para este proyecto de clase)
    if ($usuario == 'admin' && $pass == '123') {
        // Login correcto: guardar usuario en sesión y redirigir
        $_SESSION['login'] = $usuario;
        header('Location: index.php');
        exit();
    } else {
        // Credenciales incorrectas
        $msj = 'Datos incorrectos.';
    }
}
?>
<!doctype html>
<html lang="es" class="login-page">
    <head>
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Login di25</title>
        <!-- Bootstrap 5 CSS -->
        <link rel="stylesheet" href="librerias/bootstrap-5.3.8-dist/css/bootstrap.min.css">
        <!-- Estilos personalizados (tema oscuro) -->
        <link rel="stylesheet" href="css/estilos.css">
        <!-- Manifest para PWA -->
        <link rel="manifest" href="manifest.json">
        <!-- Bootstrap 5 JS -->
        <script src="librerias/bootstrap-5.3.8-dist/js/bootstrap.bundle.min.js"></script>
    </head>
    <body class="d-flex align-items-center">
        <!-- Formulario de login centrado en pantalla -->
        <form class="formulario" id="formularioLogin" method="post" action="login.php" novalidate>
            <h1 class="h3 mb-3 fw-normal">Identifícate...</h1>

            <!-- Campo: Usuario -->
            <div class="form-floating mb-3">
                <input type="text" class="form-control" 
                    name="usuario" id="usuario" placeholder=" ">
                <label for="usuario">Usuario</label>
            </div>

            <!-- Campo: Contraseña -->
            <div class="form-floating mb-3">
                <input type="password" class="form-control" 
                    name="pass" id="pass" placeholder=" ">
                <label for="pass">Contraseña</label>
            </div>

            <!-- Zona de mensajes de error -->
            <span id="msj" class="msj"><?php echo $msj; ?></span>

            <!-- Botón de envío -->
            <button class="btn btn-primary w-100 py-2" type="submit">Acceder</button>
        </form>

        <script>
            /**
             * Validación del lado del cliente
             * Comprueba que los campos no estén vacíos antes de enviar el formulario.
             * Esto evita enviar peticiones innecesarias al servidor.
             */
            document.getElementById('formularioLogin').addEventListener('submit', function(e) {
                const usuario = document.getElementById('usuario').value.trim();
                const pass    = document.getElementById('pass').value.trim();
                const msjEl   = document.getElementById('msj');

                msjEl.textContent = '';

                if (usuario === '' || pass === '') {
                    e.preventDefault();  // Cancelar el envío del formulario
                    msjEl.textContent = 'Debes completar los campos antes de enviar.';
                    msjEl.classList.add('fw-bold');
                    return false;
                }
                return true;
            });
        </script>
    </body>
</html>
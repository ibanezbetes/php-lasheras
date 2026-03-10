# Guía de Código - Explicación Detallada

Esta guía explica cómo funciona cada parte del código.

## 1. Flujo de una petición AJAX

### Ejemplo: Buscar usuarios

**Paso 1: Usuario hace clic en "Buscar Usuarios"**

```javascript
// En js/usuarios.js
function buscarUsuarios() {
  buscar(
    "Usuarios",
    "getVistaListadoUsuarios",
    "formularioBuscar",
    "capaResultadosBusqueda",
  );
}
```

**Paso 2: Se envía petición AJAX**

```javascript
// En js/utils.js
function buscar(controlador, metodo, formulario, destino) {
  // Construir URL: CFrontal.php?controlador=Usuarios&metodo=getVistaListadoUsuarios&nombre=Juan
  let parametros = "controlador=" + controlador + "&metodo=" + metodo;
  parametros +=
    "&" +
    new URLSearchParams(
      new FormData(document.getElementById(formulario)),
    ).toString();

  // Enviar petición al servidor
  fetch("CFrontal.php?" + parametros)
    .then((res) => res.text())
    .then((vista) => {
      // Mostrar resultado en la página
      document.getElementById(destino).innerHTML = vista;
    });
}
```

**Paso 3: CFrontal.php recibe la petición**

```php
// En CFrontal.php
$controlador = 'CUsuarios';  // De $_GET['controlador']
$metodo = 'getVistaListadoUsuarios';  // De $_GET['metodo']

require_once 'controladores/CUsuarios.php';
$objCont = new CUsuarios();
$objCont->getVistaListadoUsuarios($_GET);  // Ejecuta el método
```

**Paso 4: El controlador consulta la base de datos**

```php
// En controladores/CUsuarios.php
public function getVistaListadoUsuarios($datos=array()){
    $nombre = $datos['nombre'];  // Obtener parámetro de búsqueda

    // Construir consulta SQL
    $sql = "SELECT * FROM usuarios WHERE nombre LIKE '%$nombre%'";

    // Ejecutar consulta
    $usuarios = $this->dao->consultar($sql);

    // Generar HTML con los resultados
    echo '<table>...';
}
```

**Paso 5: DAO ejecuta la consulta**

```php
// En modelos/DAO.php
public function consultar($SQL){
    $res = $this->conexion->query($SQL);
    $filas = array();
    while($reg = $res->fetch_assoc()){
        $filas[] = $reg;  // Cada fila es un array asociativo
    }
    return $filas;  // Devolver array de usuarios
}
```

**Paso 6: El HTML se muestra en la página**
JavaScript recibe el HTML y lo inserta en `capaResultadosBusqueda`.

---

## 2. Estructura de archivos

### CFrontal.php (Enrutador)

```php
// Recibe: ?controlador=Usuarios&metodo=crearUsuario&nombre=Juan&...
// Hace: Carga CUsuarios.php y ejecuta crearUsuario()

$controlador = 'C' . $_GET['controlador'];  // 'CUsuarios'
$metodo = $_GET['metodo'];  // 'crearUsuario'

require_once 'controladores/'.$controlador.'.php';
$obj = new $controlador();
$obj->$metodo($_GET);  // Ejecuta el método con los parámetros
```

### Controladores (CUsuarios.php, CProductos.php)

```php
class CUsuarios extends Controlador{
    private $dao;  // Objeto para acceder a la BD

    public function __construct(){
        $this->dao = new DAO();  // Crear conexión a BD
    }

    // Cada método es una acción que puede hacer el usuario
    public function crearUsuario($datos){
        // 1. Validar datos
        // 2. Construir SQL
        // 3. Ejecutar con $this->dao->insertar($sql)
        // 4. Devolver mensaje
    }
}
```

### Modelo (DAO.php)

```php
class DAO{
    private $conexion;  // Conexión a MySQL

    public function consultar($SQL){
        // Ejecuta SELECT y devuelve array de resultados
    }

    public function insertar($SQL){
        // Ejecuta INSERT y devuelve el ID del nuevo registro
    }

    public function actualizar($SQL){
        // Ejecuta UPDATE y devuelve filas afectadas
    }
}
```

### Vistas (VUsuariosPrincipal.php)

```php
// Solo HTML, sin lógica
echo '<form id="formularioBuscar">
        <input type="text" name="nombre">
        <button onclick="buscarUsuarios();">Buscar</button>
      </form>';
```

### JavaScript (usuarios.js, productos.js, utils.js)

```javascript
// Funciones que se ejecutan cuando el usuario hace clic

function guardarUsuario() {
  // 1. Obtener datos del formulario
  const nombre = document.getElementById("nombreUsuario").value;

  // 2. Validar
  if (!nombre) {
    mostrarError("mensajesUsuario", "El nombre es obligatorio");
    return;
  }

  // 3. Enviar al servidor
  fetch(
    "CFrontal.php?controlador=Usuarios&metodo=crearUsuario&nombre=" + nombre,
  )
    .then((res) => res.text())
    .then((data) => {
      mostrarExito("mensajesUsuario", "Usuario creado");
    });
}
```

---

## 3. Conceptos clave

### AJAX (Asynchronous JavaScript And XML)

Permite enviar y recibir datos del servidor sin recargar la página.

**Sin AJAX** (antiguo):

```
Usuario hace clic → Página se recarga completamente → Servidor devuelve nueva página
```

**Con AJAX** (moderno):

```
Usuario hace clic → JavaScript envía petición → Servidor devuelve solo los datos → JavaScript actualiza la página
```

### Fetch API

```javascript
// Enviar petición GET
fetch("CFrontal.php?controlador=Usuarios&metodo=obtenerUsuario&id=5")
  .then((response) => response.json()) // Convertir respuesta a JSON
  .then((usuario) => {
    console.log(usuario.nombre); // Usar los datos
  });

// Enviar petición POST
fetch("CFrontal.php", {
  method: "POST",
  body: new URLSearchParams({
    controlador: "Usuarios",
    metodo: "crearUsuario",
    nombre: "Juan",
  }),
})
  .then((response) => response.text())
  .then((mensaje) => {
    alert(mensaje);
  });
```

### FormData

Obtiene todos los datos de un formulario automáticamente.

```javascript
// HTML
<form id="miFormulario">
    <input name="nombre" value="Juan">
    <input name="email" value="juan@email.com">
</form>

// JavaScript
const formulario = document.getElementById("miFormulario");
const datos = new FormData(formulario);

// Convertir a URL params: nombre=Juan&email=juan@email.com
const params = new URLSearchParams(datos).toString();
```

### Patrón MVC

**Modelo**: Gestiona los datos (base de datos)

```php
$usuarios = $dao->consultar("SELECT * FROM usuarios");
```

**Vista**: Muestra los datos (HTML)

```php
echo '<table>';
foreach($usuarios as $u){
    echo '<tr><td>'.$u['nombre'].'</td></tr>';
}
echo '</table>';
```

**Controlador**: Coordina Modelo y Vista

```php
public function listarUsuarios(){
    $usuarios = $this->dao->consultar("SELECT * FROM usuarios");  // Modelo
    Vista::render('VUsuarios.php', $usuarios);  // Vista
}
```

---

## 4. Validaciones

### En JavaScript (cliente)

```javascript
function validarEmail(email) {
  // Expresión regular: algo@algo.algo
  const regex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
  return regex.test(email);
}

// Uso
if (!validarEmail(mail)) {
  mostrarError("mensajes", "Email no válido");
  return; // No enviar al servidor
}
```

### En PHP (servidor)

```php
if(empty($nombre) || empty($email)){
    echo '<div class="alert alert-danger">Campos obligatorios</div>';
    return;
}
```

**Importante**: Siempre validar en el servidor, porque JavaScript se puede desactivar.

---

## 5. Seguridad (para mejorar)

### SQL Injection

**Problema actual**:

```php
$sql = "SELECT * FROM usuarios WHERE nombre = '$nombre'";
// Si $nombre = "'; DROP TABLE usuarios; --"
// SQL resultante: SELECT * FROM usuarios WHERE nombre = ''; DROP TABLE usuarios; --'
```

**Solución**: Usar prepared statements

```php
$stmt = $conexion->prepare("SELECT * FROM usuarios WHERE nombre = ?");
$stmt->bind_param("s", $nombre);
$stmt->execute();
```

### Contraseñas

**Problema actual**:

```php
$pass = md5($password);  // MD5 es inseguro
```

**Solución**: Usar password_hash()

```php
$pass = password_hash($password, PASSWORD_DEFAULT);

// Para verificar
if(password_verify($passwordIngresado, $passGuardado)){
    // Login correcto
}
```

---

## 6. Debugging (encontrar errores)

### En JavaScript

```javascript
console.log("Valor de nombre:", nombre); // Ver en Consola del navegador (F12)
console.table(usuarios); // Ver array en formato tabla
debugger; // Pausar ejecución (con F12 abierto)
```

### En PHP

```php
var_dump($usuarios);  // Ver contenido de variable
die("Llegó hasta aquí");  // Detener ejecución
error_log("Error: " . $mensaje);  // Escribir en log de errores
```

### En MySQL

```sql
-- Probar consultas directamente en phpMyAdmin
SELECT * FROM usuarios WHERE nombre LIKE '%Juan%';
```

---

## 7. Consejos

1. **Usa nombres descriptivos**
   - ❌ `function f1(x)`
   - ✅ `function buscarUsuarios(nombre)`

2. **Divide el código en funciones pequeñas**
   - Cada función debe hacer UNA cosa
   - Si una función tiene más de 30 líneas, probablemente se puede dividir

3. **Comenta lo que no es obvio**
   - ❌ `i++; // incrementar i`
   - ✅ `i++; // saltar al siguiente usuario`

4. **Prueba cada función por separado**
   - No escribas todo el código y luego pruebes
   - Escribe una función, pruébala, luego sigue

5. **Usa console.log() abundantemente**
   - Es la forma más rápida de ver qué está pasando
   - Bórralo cuando funcione

---

## 8. Recursos útiles

- **PHP**: https://www.php.net/manual/es/
- **JavaScript**: https://developer.mozilla.org/es/docs/Web/JavaScript
- **Bootstrap**: https://getbootstrap.com/docs/5.3/
- **MySQL**: https://dev.mysql.com/doc/
- **Fetch API**: https://developer.mozilla.org/es/docs/Web/API/Fetch_API

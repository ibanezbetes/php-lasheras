<!-- ============================================================
     VMenuDinamico.php - Vista del menú de navegación dinámico
     
     Genera la barra de navegación (navbar) de forma dinámica a partir
     de los datos de la tabla 'menus' de la base de datos.
     
     Recibe: $menuEstructurado (array jerárquico generado por CMenus)
     Formato:
       [
         ['item' => ['etiqueta'=>'Home', 'accion'=>'#', ...], 'submenus' => []],
         ['item' => ['etiqueta'=>'Datos', 'accion'=>null, ...], 'submenus' => [
             ['etiqueta'=>'Usuarios', 'accion'=>"obtenerVista('Usuarios',...)", ...],
             ...
         ]],
       ]
     ============================================================ -->

<nav class="navbar navbar-expand-lg bg-body-tertiary mt-2">
  <div class="container-fluid">
    <a class="navbar-brand" href="#">Navbar</a>

    <!-- Botón hamburguesa para móviles -->
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" 
            data-bs-target="#navbarDinamico" aria-controls="navbarDinamico" 
            aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse" id="navbarDinamico">
      <ul class="navbar-nav">
        <?php foreach($menuEstructurado as $menuItem): ?>

          <?php if(empty($menuItem['submenus'])): ?>
            <!-- Opción simple (sin desplegable) -->
            <li class="nav-item">
              <a class="nav-link" 
                 <?php if($menuItem['item']['accion'] && $menuItem['item']['accion'] != '#'): ?>
                   onclick="<?php echo htmlspecialchars($menuItem['item']['accion']); ?>"
                   style="cursor: pointer;"
                 <?php else: ?>
                   href="#"
                 <?php endif; ?>>
                <?php echo htmlspecialchars($menuItem['item']['etiqueta']); ?>
              </a>
            </li>

          <?php else: ?>
            <!-- Opción con desplegable (dropdown) -->
            <li class="nav-item dropdown">
              <a class="nav-link dropdown-toggle" href="#" role="button" 
                 data-bs-toggle="dropdown" aria-expanded="false">
                <?php echo htmlspecialchars($menuItem['item']['etiqueta']); ?>
              </a>
              <ul class="dropdown-menu">
                <?php foreach($menuItem['submenus'] as $submenu): ?>
                  <li>
                    <a class="dropdown-item" 
                       <?php if($submenu['accion'] && $submenu['accion'] != '#'): ?>
                         onclick="<?php echo htmlspecialchars($submenu['accion']); ?>"
                         style="cursor: pointer;"
                       <?php else: ?>
                         href="#"
                       <?php endif; ?>>
                      <?php echo htmlspecialchars($submenu['etiqueta']); ?>
                    </a>
                  </li>
                <?php endforeach; ?>
              </ul>
            </li>

          <?php endif; ?>
        <?php endforeach; ?>
      </ul>
    </div>
  </div>
</nav>

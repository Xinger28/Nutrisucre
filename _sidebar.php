<?php
// ============================================================
//  _sidebar.php  —  Sidebar compartido con lógica de roles
//  Incluir con: require_once '_sidebar.php';
//  Requiere que $rol y $paginaActual estén definidos antes.
//  Ejemplo: $paginaActual = 'buscar';
// ============================================================

// Mapa de links según rol
// Estructura: [ href, icono, label, solo_roles (null = todos) ]
$navLinks = [
    ['dashboard.php',              'home',              'Inicio',                  null],
    ['buscar.php',                 'search',            'Buscar Nutricionistas',   ['Paciente','Administrador']],
    ['progreso.php',               'monitoring',        'Mi Progreso',             ['Paciente']],
    ['planes.php',                 'restaurant_menu',   'Mis Planes',              ['Paciente']],
    // Sprint 2: Servicios segun rol
    ['servicios.php',              'medical_services',  'Mis Servicios',           ['Nutricionista']],
    ['servicios.php',              'medical_services',  'Servicios Disponibles',   ['Paciente']],
    ['servicios.php',              'medical_services',  'Validar Servicios',       ['Administrador']],
    ['planes.php',                 'restaurant_menu',   'Gestion de Planes',       ['Nutricionista','Administrador']],
    ['registro_nutricionista.php', 'verified_user',     'Mi Verificacion',         ['Nutricionista']],
    ['admin.php',                  'admin_panel_settings','Administracion',        ['Administrador']],
    ['specs_sprint2.php',          'science',           'Specs Sprint 2 (SDD)',    ['Administrador']],
];
?>
<aside id="sidebar" class="fixed left-0 top-0 h-full flex-col p-4 gap-2 w-64 bg-[#f2f4f5] border-r z-40 hidden md:flex">
  <div class="mb-8 px-2">
    <div class="flex items-center gap-2 mb-1">
      <span class="material-symbols-outlined text-[#22c55e] text-3xl">nutrition</span>
      <span class="text-[#22c55e] font-bold text-2xl">NutriSucre</span>
    </div>
    <p class="text-xs text-gray-500 px-1">Chuquisaca • Bolivia</p>
  </div>

  <nav class="flex-1 flex flex-col gap-1 overflow-y-auto">
    <?php foreach ($navLinks as [$href, $icono, $label, $soloRoles]):
        // Filtrar por rol
        if ($soloRoles !== null && !in_array($rol, $soloRoles)) continue;
        // Detectar página activa
        $esActivo = (isset($paginaActual) && $paginaActual === pathinfo($href, PATHINFO_FILENAME));
        $clases   = $esActivo
            ? 'flex items-center gap-3 px-4 py-3 bg-white text-[#22c55e] shadow-sm rounded-xl font-medium'
            : 'flex items-center gap-3 px-4 py-3 text-slate-500 hover:bg-white hover:text-[#22c55e] rounded-xl font-medium transition-colors';
    ?>
    <a href="<?= $href ?>" class="<?= $clases ?>">
      <span class="material-symbols-outlined"><?= $icono ?></span>
      <span><?= $label ?></span>
    </a>
    <?php endforeach; ?>
  </nav>

  <div class="pt-4 border-t">
    <a onclick="logout()" class="flex items-center gap-3 px-4 py-3 text-red-500 hover:bg-red-50 rounded-xl cursor-pointer font-medium transition-colors">
      <span class="material-symbols-outlined">logout</span>
      <span>Cerrar sesión</span>
    </a>
  </div>
</aside>

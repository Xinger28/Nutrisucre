<?php
// ============================================================
//  _sidebar.php — Sidebar iOS compartido
// ============================================================
if (!isset($rol)) {
    if (session_status() === PHP_SESSION_NONE) session_start();
    $rol = $_SESSION['usuario']['rol'] ?? '';
}
$navLinks = [
    ['dashboard.php',              'home',              'Inicio',                ['Paciente','Nutricionista','Administrador']],
    ['buscar.php',                 'search',            'Buscar',                ['Paciente','Administrador']],
    ['progreso.php',               'monitoring',        'Mi Progreso',           ['Paciente']],
    ['planes.php',                 'restaurant_menu',   'Mis Planes',            ['Paciente']],
    ['servicios.php',              'medical_services',  'Mis Servicios',         ['Nutricionista']],
    ['servicios.php',              'medical_services',  'Servicios',             ['Paciente']],
    ['servicios.php',              'verified',          'Validar Servicios',     ['Administrador']],
    ['planes.php',                 'restaurant_menu',   'Gestión de Planes',     ['Nutricionista','Administrador']],
    ['registro_nutricionista.php', 'badge',             'Mi Verificación',       ['Nutricionista']],
    ['admin.php',                  'admin_panel_settings','Administración',      ['Administrador']],
];
?>
<!-- Toast global -->
<div id="toast"></div>

<aside id="sidebar" class="fixed left-0 top-0 h-full w-64 flex flex-col p-4 gap-1 z-40 hidden md:flex">
  <div class="px-2 py-5 mb-2">
    <div class="flex items-center gap-3">
      <div class="w-10 h-10 bg-gradient-to-br from-[#22c55e] to-[#16a34a] rounded-2xl flex items-center justify-center shadow-lg">
        <span class="icon icon-fill text-white text-xl" style="font-size:20px">nutrition</span>
      </div>
      <div>
        <p class="font-black text-[17px] tracking-tight text-[#1c1c1e]">NutriSucre</p>
        <p class="text-[11px] text-[#8e8e93]">Chuquisaca · Bolivia</p>
      </div>
    </div>
  </div>

  <nav class="flex-1 flex flex-col gap-0.5 overflow-y-auto">
    <?php foreach ($navLinks as [$href, $icono, $label, $roles]):
        if (!in_array($rol, $roles)) continue;
        $activo = isset($paginaActual) && $paginaActual === pathinfo($href, PATHINFO_FILENAME);
    ?>
    <a href="<?= $href ?>" class="nav-item <?= $activo ? 'active' : '' ?>">
      <span class="icon <?= $activo ? 'icon-fill' : '' ?>"><?= $icono ?></span>
      <span><?= $label ?></span>
    </a>
    <?php endforeach; ?>
  </nav>

  <div class="pt-3 border-t border-[rgba(0,0,0,0.06)]">
    <a onclick="logout()" class="nav-item" style="color:#ef4444">
      <span class="icon">logout</span>
      <span>Cerrar sesión</span>
    </a>
  </div>
</aside>

<script>
function toggleSidebar() {
    const s = document.getElementById('sidebar');
    s.classList.toggle('hidden'); s.classList.toggle('flex');
}
async function logout() {
    showToast('Cerrando sesión...');
    await fetch('api/auth.php?accion=logout', { method: 'POST' });
    window.location.href = 'login.php';
}
function showToast(msg, duration = 2500) {
    const t = document.getElementById('toast');
    t.textContent = msg; t.classList.add('show');
    setTimeout(() => t.classList.remove('show'), duration);
}
</script>

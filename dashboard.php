<?php
session_start();
if (empty($_SESSION['usuario'])) { header('Location: login.php'); exit; }
$usuario      = $_SESSION['usuario'];
$rol          = $usuario['rol'];
$nombre       = $usuario['nombre'];
$primerNombre = explode(' ', $nombre)[0];

$estadoVerif = '';
if ($rol === 'Nutricionista') {
    require_once 'config.php';
    $db = getDB();
    $stmt = $db->prepare("SELECT estado_verificacion FROM nutricionistas WHERE usuario_id = ?");
    $stmt->execute([$usuario['id']]);
    $nutri = $stmt->fetch();
    $estadoVerif = $nutri['estado_verificacion'] ?? 'pendiente';
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>NutriSucre - Dashboard</title>
<script src="https://cdn.tailwindcss.com"></script>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet">
<style>
  body { font-family: 'Inter', sans-serif; background: #f8fafb; }
  .material-symbols-outlined { font-variation-settings: 'FILL' 0, 'wght' 300; }
</style>
</head>
<body>

<?php $paginaActual = 'dashboard'; require_once '_sidebar.php'; ?>

<!-- HEADER -->
<header class="flex justify-between items-center px-6 py-4 bg-white/80 backdrop-blur-xl border-b md:pl-72 sticky top-0 z-50">
  <div class="flex items-center gap-4">
    <button onclick="toggleSidebar()" class="md:hidden"><span class="material-symbols-outlined">menu</span></button>
    <h1 class="text-2xl font-bold">Dashboard</h1>
  </div>
  <div class="flex items-center gap-4">
    <div class="text-right">
      <div class="font-semibold"><?= htmlspecialchars($nombre) ?></div>
      <div class="text-xs text-[#22c55e] font-medium"><?= htmlspecialchars($rol) ?></div>
    </div>
    <div onclick="abrirPerfil()" class="w-10 h-10 bg-gray-200 rounded-full flex items-center justify-center cursor-pointer hover:bg-gray-300 text-lg">👤</div>
  </div>
</header>

<!-- MAIN -->
<main class="md:pl-64 p-8 max-w-7xl mx-auto">

  <div class="mb-8">
    <h2 class="text-5xl font-black tracking-tight">¡Bienvenido, <?= htmlspecialchars($primerNombre) ?>!</h2>
    <p class="text-gray-500 text-lg mt-2">
      <?php if ($rol === 'Paciente'): ?>Aquí puedes ver tu progreso y buscar nutricionistas.
      <?php elseif ($rol === 'Nutricionista'): ?>Gestiona tus servicios profesionales y planes nutricionales.
      <?php else: ?>Panel de administración de NutriSucre.<?php endif; ?>
    </p>
  </div>


  <?php if ($rol === 'Nutricionista' && $estadoVerif !== 'aprobado'): ?>
  <!-- Banner verificacion -->
  <?php if ($estadoVerif === 'rechazado'): ?>
  <div id="bannerVerif" class="bg-red-50 border border-red-200 rounded-2xl p-5 mb-6 flex items-start gap-4">
    <span class="material-symbols-outlined text-red-500 text-3xl flex-shrink-0 mt-0.5">dangerous</span>
    <div class="flex-1">
      <p class="font-bold text-red-800">Verificación profesional rechazada</p>
      <p class="text-red-700 text-sm mt-1">Tu postulación fue rechazada por el administrador. Por favor, vuelve a enviar tu documentación corregida para habilitar tu cuenta.</p>
    </div>
    <a href="registro_nutricionista.php"
       class="flex-shrink-0 bg-red-500 hover:bg-red-600 text-white px-5 py-2.5 rounded-xl font-bold text-sm transition-colors">
      Reenviar ahora →
    </a>
  </div>
  <?php else: ?>
  <div id="bannerVerif" class="bg-amber-50 border border-amber-200 rounded-2xl p-5 mb-6 flex items-start gap-4">
    <span class="material-symbols-outlined text-amber-500 text-3xl flex-shrink-0 mt-0.5">verified_user</span>
    <div class="flex-1">
      <p class="font-bold text-amber-800">Completa tu verificación profesional</p>
      <p class="text-amber-700 text-sm mt-1">Para aparecer en el buscador y recibir pacientes, debes enviar tu documentación. El equipo de NutriSucre la revisará.</p>
    </div>
    <a href="registro_nutricionista.php"
       class="flex-shrink-0 bg-amber-500 hover:bg-amber-600 text-white px-5 py-2.5 rounded-xl font-bold text-sm transition-colors">
      Completar ahora →
    </a>
  </div>
  <?php endif; ?>
  <?php endif; ?>

  <!-- HERO -->
  <div class="<?= $rol === 'Nutricionista' ? 'bg-gradient-to-r from-[#2563eb] to-[#1d4ed8]' : 'bg-gradient-to-r from-[#22c55e] to-[#16a34a]' ?> text-white rounded-3xl p-10 mb-8 relative overflow-hidden">
    <div class="max-w-md relative z-10">
      <?php if ($rol === 'Paciente'): ?>
        <h3 class="text-3xl font-bold mb-3">Registra tu progreso hoy</h3>
        <p class="mb-6 opacity-90">Lleva el control de tu peso, medidas y evolución nutricional en un solo lugar.</p>
        <a href="progreso.php" class="inline-block bg-white text-[#22c55e] px-8 py-4 rounded-2xl font-bold">Ver mi progreso →</a>
      <?php elseif ($rol === 'Nutricionista'): ?>
        <h3 class="text-3xl font-bold mb-3">Publica y gestiona tus servicios</h3>
        <p class="mb-6 opacity-90">Registra tus servicios profesionales para que los pacientes puedan encontrarte y agendar consultas.</p>
        <a href="servicios.php" class="inline-block bg-white text-[#1d4ed8] px-8 py-4 rounded-2xl font-bold">Gestionar servicios →</a>
      <?php else: ?>
        <h3 class="text-3xl font-bold mb-3">Panel de Administración</h3>
        <p class="mb-6 opacity-90">Gestiona usuarios, nutricionistas y el funcionamiento de la plataforma.</p>
        <div class="flex gap-3 flex-wrap">
                <a href="servicios.php" class="inline-block bg-white text-[#22c55e] px-6 py-3 rounded-2xl font-bold text-sm">Validar servicios →</a>
                <a href="admin.php" class="inline-block bg-white/20 text-white px-6 py-3 rounded-2xl font-bold text-sm">Usuarios →</a>
              </div>
      <?php endif; ?>
    </div>
    <span class="material-symbols-outlined absolute bottom-[-30px] right-[-30px] text-white/20 text-[220px]">
      <?= $rol === 'Nutricionista' ? 'groups' : 'leafy_green' ?>
    </span>
  </div>

  <!-- STATS CARDS (se llenan con JS via AJAX) -->
  <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-10">
    <div class="bg-white p-7 rounded-3xl shadow-sm border">
      <span class="material-symbols-outlined text-3xl text-[#22c55e]">event</span>
      <p id="stat1Valor" class="text-5xl font-black mt-4">—</p>
      <p id="stat1Label" class="text-gray-500 mt-1">Mis citas</p>
    </div>
    <div class="bg-white p-7 rounded-3xl shadow-sm border">
      <span class="material-symbols-outlined text-3xl text-[#22c55e]">restaurant_menu</span>
      <p id="stat2Valor" class="text-5xl font-black mt-4">—</p>
      <p id="stat2Label" class="text-gray-500 mt-1">Planes activos</p>
    </div>
    <div class="bg-white p-7 rounded-3xl shadow-sm border">
      <span class="material-symbols-outlined text-3xl text-[#22c55e]">monitoring</span>
      <p id="stat3Valor" class="text-5xl font-black mt-4">—</p>
      <p id="stat3Label" class="text-gray-500 mt-1">Progreso de peso</p>
    </div>
  </div>

  <!-- TABLA DE CITAS RECIENTES -->
  <div>
    <div class="flex justify-between items-center mb-5">
      <h3 class="text-2xl font-bold">Citas recientes</h3>
      <a href="buscar.php" class="text-[#22c55e] font-medium text-sm hover:underline">+ Agendar nueva →</a>
    </div>
    <div class="bg-white rounded-3xl overflow-hidden shadow-sm border">
      <table class="w-full">
        <thead class="bg-gray-50 text-sm">
          <tr>
            <th class="px-8 py-5 text-left font-semibold text-gray-600"><?= $rol === 'Paciente' ? 'Nutricionista' : 'Paciente' ?></th>
            <th class="px-8 py-5 text-left font-semibold text-gray-600">Especialidad</th>
            <th class="px-8 py-5 text-left font-semibold text-gray-600">Fecha y hora</th>
            <th class="px-8 py-5 text-left font-semibold text-gray-600">Estado</th>
          </tr>
        </thead>
        <tbody id="tablaCitas" class="divide-y text-sm">
          <tr><td colspan="4" class="px-8 py-8 text-gray-400">Cargando...</td></tr>
        </tbody>
      </table>
    </div>
  </div>
</main>

<!-- Modal Perfil -->
<div id="modalPerfil" class="hidden fixed inset-0 bg-black/60 flex items-center justify-center z-[60] p-4">
  <div class="bg-white rounded-3xl p-8 w-full max-w-md">
    <div class="flex justify-between items-start mb-6">
      <h3 class="text-2xl font-bold">Mi Perfil</h3>
      <button onclick="cerrarPerfil()"><span class="material-symbols-outlined">close</span></button>
    </div>
    <div class="bg-gray-50 p-4 rounded-xl space-y-2 text-sm">
      <p class="font-bold text-[#22c55e] mb-2">Datos de cuenta</p>
      <p><span class="font-semibold">Nombre:</span> <?= htmlspecialchars($nombre) ?></p>
      <p><span class="font-semibold">Email:</span> <?= htmlspecialchars($usuario['email']) ?></p>
      <p><span class="font-semibold">Rol:</span> <?= htmlspecialchars($rol) ?></p>
    </div>
    <button onclick="cerrarPerfil()" class="mt-6 w-full bg-[#22c55e] text-white py-4 rounded-2xl font-semibold">Cerrar</button>
  </div>
</div>

<script>
const ROL = '<?= $rol ?>';

document.addEventListener('DOMContentLoaded', () => {
    cargarStats();
    cargarCitas();
});

// ── Stats reales desde 3 APIs en paralelo ──────────────────
async function cargarStats() {
    try {
        // Promise.all: lanzar todas las peticiones en paralelo
        const [resCitas, resPlanes, resPeso, resServicios] = await Promise.all([
            fetch('api/citas.php'),
            fetch('api/planes.php'),
            fetch('api/seguimiento.php'),
            fetch('api/servicios.php')
        ]);

        const [citas, planes, seguimiento, servicios] = await Promise.all([
            resCitas.json(), resPlanes.json(), resPeso.json(), resServicios.json()
        ]);

        // Stat 1: número de citas (todos los roles)
        document.getElementById('stat1Valor').textContent = Array.isArray(citas) ? citas.length : '—';

        // Stat 2: depende del rol
        if (ROL === 'Paciente') {
            // Paciente: planes activos
            const activos = Array.isArray(planes) ? planes.filter(p => p.estado === 'activo').length : 0;
            document.getElementById('stat2Valor').textContent = activos;
            document.getElementById('stat2Label').textContent = 'Planes activos';
        } else if (ROL === 'Nutricionista') {
            // Nutricionista: servicios aprobados publicados
            const aprobados = Array.isArray(servicios) ? servicios.filter(s => s.estado === 'Aprobado').length : 0;
            document.getElementById('stat2Valor').textContent = aprobados;
            document.getElementById('stat2Label').textContent = 'Servicios publicados';
        } else {
            // Admin: servicios pendientes de validar
            const pendientes = Array.isArray(servicios) ? servicios.filter(s => s.estado === 'Pendiente').length : 0;
            document.getElementById('stat2Valor').textContent = pendientes;
            document.getElementById('stat2Label').textContent = 'Servicios por validar';
            // Mostrar badge de alerta si hay pendientes
            if (pendientes > 0) {
                document.getElementById('stat2Valor').style.color = '#f59e0b';
            }
        }

        // Stat 3: depende del rol
        if (ROL === 'Paciente') {
            // Paciente: diferencia de peso
            if (Array.isArray(seguimiento) && seguimiento.length >= 2) {
                const conPeso = seguimiento.filter(r => r.peso !== null);
                if (conPeso.length >= 2) {
                    const diff = (parseFloat(conPeso[conPeso.length-1].peso) - parseFloat(conPeso[0].peso)).toFixed(1);
                    const el   = document.getElementById('stat3Valor');
                    el.textContent = (diff > 0 ? '+' : '') + diff + ' kg';
                    el.style.color = diff < 0 ? '#22c55e' : diff > 0 ? '#ef4444' : '';
                } else {
                    document.getElementById('stat3Valor').textContent = 'Sin datos';
                }
                document.getElementById('stat3Label').textContent = 'Cambio de peso total';
            }
        } else if (ROL === 'Nutricionista') {
            // Nutricionista: total de servicios registrados
            const total = Array.isArray(servicios) ? servicios.length : 0;
            document.getElementById('stat3Valor').textContent = total;
            document.getElementById('stat3Label').textContent = 'Servicios registrados';
        } else {
            // Admin: total de servicios en la plataforma
            const total = Array.isArray(servicios) ? servicios.length : 0;
            document.getElementById('stat3Valor').textContent = total;
            document.getElementById('stat3Label').textContent = 'Total servicios';
        }
    } catch (e) {
        console.error('Error cargando stats:', e);
    }
}

// ── Tabla de citas recientes ───────────────────────────────
async function cargarCitas() {
    const res  = await fetch('api/citas.php');
    const data = await res.json();
    const tbody = document.getElementById('tablaCitas');

    if (!Array.isArray(data) || data.length === 0) {
        tbody.innerHTML = '<tr><td colspan="4" class="px-8 py-8 text-gray-400">No hay citas registradas aún.</td></tr>';
        return;
    }

    const recientes = data.slice(0, 5);
    tbody.innerHTML = recientes.map(c => {
        const persona = ROL === 'Paciente' ? (c.nutricionista || '—') : (c.paciente || '—');
        const clrEstado = c.estado === 'confirmada' ? 'bg-green-100 text-green-700' : 'bg-yellow-100 text-yellow-700';
        return `
            <tr class="hover:bg-gray-50">
                <td class="px-8 py-5 font-medium">${persona}</td>
                <td class="px-8 py-5 text-[#22c55e] text-sm">${c.especialidad || 'Nutrición General'}</td>
                <td class="px-8 py-5 text-gray-600">${c.fecha}${c.hora ? ' - ' + c.hora.slice(0,5) : ''}</td>
                <td class="px-8 py-5"><span class="px-3 py-1 rounded-full text-xs font-medium ${clrEstado}">${c.estado}</span></td>
            </tr>`;
    }).join('');
}

function toggleSidebar() {
    const s = document.getElementById('sidebar') || document.querySelector('aside');
    s.classList.toggle('hidden'); s.classList.toggle('flex');
}
function abrirPerfil()  { document.getElementById('modalPerfil').classList.remove('hidden'); }
function cerrarPerfil() { document.getElementById('modalPerfil').classList.add('hidden'); }

async function logout() {
    if (!confirm('¿Cerrar sesión?')) return;
    await fetch('api/auth.php?accion=logout', { method: 'POST' });
    window.location.href = 'login.php';
}

document.getElementById('modalPerfil').addEventListener('click', e => {
    if (e.target.id === 'modalPerfil') cerrarPerfil();
});
</script>
</body>
</html>

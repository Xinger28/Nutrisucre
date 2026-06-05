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
$avatarLetra = mb_strtoupper(mb_substr($primerNombre, 0, 1));
?>
<!DOCTYPE html>
<html lang="es">
<head>
<title>NutriSucre · Inicio</title>
<?php require_once '_ios_head.php'; ?>
<style>
  .stat-card { background: white; border-radius: 20px; padding: 20px; border: 1px solid var(--border); transition: all .25s ease; }
  .stat-card:hover { transform: translateY(-2px); box-shadow: var(--shadow-md); }
  .hero-card { border-radius: 28px; padding: 32px; position: relative; overflow: hidden; }
  .avatar { width: 40px; height: 40px; border-radius: 50%; background: linear-gradient(135deg,#22c55e,#16a34a); display:flex; align-items:center; justify-content:center; color:white; font-weight:800; font-size:16px; cursor:pointer; flex-shrink:0; }
</style>
</head>
<body>

<?php $paginaActual = 'dashboard'; require_once '_sidebar.php'; ?>

<!-- Header -->
<header class="ios-header md:pl-64">
  <div class="flex items-center gap-3">
    <button onclick="toggleSidebar()" class="md:hidden ios-btn-icon">
      <span class="icon" style="font-size:20px">menu</span>
    </button>
    <div>
      <p class="font-black text-[18px] tracking-tight">Inicio</p>
    </div>
  </div>
  <div class="flex items-center gap-3">
    <div class="text-right hidden sm:block">
      <p class="font-semibold text-[14px]"><?= htmlspecialchars($nombre) ?></p>
      <p class="text-[12px] text-[#22c55e] font-semibold"><?= htmlspecialchars($rol) ?></p>
    </div>
    <div class="avatar" onclick="abrirPerfil()" title="Mi perfil"><?= $avatarLetra ?></div>
  </div>
</header>

<main class="md:pl-64 p-5 md:p-8 max-w-6xl mx-auto space-y-6">

  <!-- Banner verificación nutricionista -->
  <?php if ($rol === 'Nutricionista' && $estadoVerif !== 'aprobado'): ?>
  <div class="ios-card p-5 flex items-center gap-4 <?= $estadoVerif === 'rechazado' ? 'border-red-200 bg-red-50' : 'border-amber-200 bg-amber-50' ?>" style="border-width:1.5px">
    <span class="icon icon-fill text-3xl <?= $estadoVerif === 'rechazado' ? 'text-red-500' : 'text-amber-500' ?>">
      <?= $estadoVerif === 'rechazado' ? 'cancel' : 'verified_user' ?>
    </span>
    <div class="flex-1">
      <p class="font-bold text-[15px] <?= $estadoVerif === 'rechazado' ? 'text-red-800' : 'text-amber-800' ?>">
        <?= $estadoVerif === 'rechazado' ? 'Verificación rechazada' : 'Completa tu verificación profesional' ?>
      </p>
      <p class="text-[13px] <?= $estadoVerif === 'rechazado' ? 'text-red-600' : 'text-amber-600' ?> mt-0.5">
        <?= $estadoVerif === 'rechazado' ? 'Revisa los comentarios y vuelve a enviar tu documentación.' : 'Envía tu documentación para aparecer en el buscador y recibir pacientes.' ?>
      </p>
    </div>
    <a href="registro_nutricionista.php"
       class="ios-btn text-[13px] whitespace-nowrap <?= $estadoVerif === 'rechazado' ? '' : '' ?>"
       style="background:<?= $estadoVerif === 'rechazado' ? '#ef4444' : '#f59e0b' ?>; box-shadow:none; padding:10px 18px; border-radius:12px">
      <?= $estadoVerif === 'rechazado' ? 'Reenviar' : 'Completar' ?> →
    </a>
  </div>
  <?php endif; ?>

  <!-- Saludo + Hero -->
  <div class="hero-card <?= $rol === 'Nutricionista' ? 'bg-gradient-to-br from-blue-600 to-indigo-700' : ($rol === 'Administrador' ? 'bg-gradient-to-br from-[#1c1c1e] to-[#3a3a3c]' : 'bg-gradient-to-br from-[#22c55e] to-[#15803d]') ?> text-white scale-in">
    <div class="relative z-10 max-w-sm">
      <p class="text-[13px] font-semibold opacity-70 mb-1 uppercase tracking-wider"><?= date('l, d \d\e F') ?></p>
      <h2 class="text-[28px] font-black leading-tight mb-2">¡Hola, <?= htmlspecialchars($primerNombre) ?>! 👋</h2>
      <p class="text-[14px] opacity-80 mb-5">
        <?php if ($rol === 'Paciente'): ?>Sigue tu progreso y encuentra el mejor especialista para ti.
        <?php elseif ($rol === 'Nutricionista'): ?>Gestiona tus servicios y acompaña a tus pacientes.
        <?php else: ?>Administra la plataforma NutriSucre.<?php endif; ?>
      </p>
      <?php if ($rol === 'Paciente'): ?>
        <a href="buscar.php" class="inline-flex items-center gap-2 bg-white/20 hover:bg-white/30 text-white font-bold px-5 py-3 rounded-2xl text-[14px] transition-all backdrop-blur-sm">
          <span class="icon" style="font-size:18px">search</span> Buscar nutricionistas
        </a>
      <?php elseif ($rol === 'Nutricionista'): ?>
        <a href="servicios.php" class="inline-flex items-center gap-2 bg-white/20 hover:bg-white/30 text-white font-bold px-5 py-3 rounded-2xl text-[14px] transition-all backdrop-blur-sm">
          <span class="icon" style="font-size:18px">add_circle</span> Gestionar servicios
        </a>
      <?php else: ?>
        <div class="flex gap-3 flex-wrap">
          <a href="servicios.php" class="inline-flex items-center gap-2 bg-white/20 hover:bg-white/30 text-white font-bold px-5 py-3 rounded-2xl text-[14px] transition-all">
            <span class="icon" style="font-size:18px">verified</span> Validar servicios
          </a>
          <a href="admin.php" class="inline-flex items-center gap-2 bg-white/20 hover:bg-white/30 text-white font-bold px-5 py-3 rounded-2xl text-[14px] transition-all">
            <span class="icon" style="font-size:18px">manage_accounts</span> Usuarios
          </a>
        </div>
      <?php endif; ?>
    </div>
    <!-- Decoración -->
    <div class="absolute right-[-20px] bottom-[-20px] opacity-10 pointer-events-none">
      <span class="icon icon-fill" style="font-size:180px"><?= $rol === 'Nutricionista' ? 'groups' : ($rol === 'Administrador' ? 'admin_panel_settings' : 'nutrition') ?></span>
    </div>
  </div>

  <!-- Stats -->
  <div class="grid grid-cols-3 gap-4">
    <div class="stat-card fade-up" style="animation-delay:.05s">
      <span class="icon text-[#22c55e]" style="font-size:24px">event</span>
      <p id="stat1Valor" class="text-[32px] font-black mt-3 tracking-tight">—</p>
      <p id="stat1Label" class="text-[13px] text-[#8e8e93] mt-0.5 font-medium">Citas</p>
    </div>
    <div class="stat-card fade-up" style="animation-delay:.1s">
      <span class="icon text-[#22c55e]" style="font-size:24px">restaurant_menu</span>
      <p id="stat2Valor" class="text-[32px] font-black mt-3 tracking-tight">—</p>
      <p id="stat2Label" class="text-[13px] text-[#8e8e93] mt-0.5 font-medium">Planes</p>
    </div>
    <div class="stat-card fade-up" style="animation-delay:.15s">
      <span class="icon text-[#22c55e]" style="font-size:24px">monitoring</span>
      <p id="stat3Valor" class="text-[32px] font-black mt-3 tracking-tight">—</p>
      <p id="stat3Label" class="text-[13px] text-[#8e8e93] mt-0.5 font-medium">Progreso</p>
    </div>
  </div>

  <!-- Citas recientes -->
  <div class="ios-card overflow-hidden fade-up" style="animation-delay:.2s">
    <div class="flex justify-between items-center px-6 py-5 border-b border-[rgba(0,0,0,0.05)]">
      <p class="font-black text-[17px]">Citas recientes</p>
      <a href="buscar.php" class="text-[#22c55e] font-bold text-[13px] flex items-center gap-1 hover:opacity-70 transition-opacity">
        <span class="icon" style="font-size:16px">add</span> Nueva
      </a>
    </div>
    <div id="tablaCitas" class="divide-y divide-[rgba(0,0,0,0.04)]">
      <div class="px-6 py-8 text-center text-[#8e8e93] text-[14px]">Cargando...</div>
    </div>
  </div>

</main>

<!-- Modal perfil -->
<div id="modalPerfil" class="ios-modal-bg" onclick="if(event.target===this)cerrarPerfil()">
  <div class="ios-modal p-7 max-w-sm w-full">
    <div class="flex justify-between items-center mb-5">
      <p class="font-black text-[20px]">Mi Perfil</p>
      <button onclick="cerrarPerfil()" class="ios-btn-icon"><span class="icon">close</span></button>
    </div>
    <div class="flex flex-col items-center mb-6">
      <div class="avatar" style="width:64px;height:64px;font-size:28px;margin-bottom:12px"><?= $avatarLetra ?></div>
      <p class="font-bold text-[18px]"><?= htmlspecialchars($nombre) ?></p>
      <p class="text-[13px] text-[#8e8e93]"><?= htmlspecialchars($usuario['email']) ?></p>
      <span class="badge badge-green mt-2"><?= htmlspecialchars($rol) ?></span>
    </div>
    <button onclick="cerrarPerfil()" class="ios-btn w-full" style="border-radius:14px">Cerrar</button>
  </div>
</div>

<script>
const ROL = '<?= $rol ?>';
document.addEventListener('DOMContentLoaded', () => { cargarStats(); cargarCitas(); });

async function cargarStats() {
    try {
        const [resCitas, resPlanes, resPeso, resServ] = await Promise.all([
            fetch('api/citas.php'), fetch('api/planes.php'),
            fetch('api/seguimiento.php'), fetch('api/servicios.php')
        ]);
        const [citas, planes, seg, servs] = await Promise.all([
            resCitas.json(), resPlanes.json(), resPeso.json(), resServ.json()
        ]);
        document.getElementById('stat1Valor').textContent = Array.isArray(citas) ? citas.length : '—';
        if (ROL === 'Paciente') {
            const act = Array.isArray(planes) ? planes.filter(p => p.estado === 'activo').length : 0;
            document.getElementById('stat2Valor').textContent = act;
            document.getElementById('stat2Label').textContent = 'Planes activos';
            const conPeso = Array.isArray(seg) ? seg.filter(r => r.peso !== null) : [];
            if (conPeso.length >= 2) {
                const d = (parseFloat(conPeso[conPeso.length-1].peso) - parseFloat(conPeso[0].peso)).toFixed(1);
                const el = document.getElementById('stat3Valor');
                el.textContent = (d > 0 ? '+' : '') + d + ' kg';
                el.style.color = d < 0 ? '#22c55e' : d > 0 ? '#ef4444' : '';
                document.getElementById('stat3Label').textContent = 'Cambio de peso';
            } else { document.getElementById('stat3Valor').textContent = '—'; }
        } else if (ROL === 'Nutricionista') {
            const ap = Array.isArray(servs) ? servs.filter(s => s.estado === 'Aprobado').length : 0;
            document.getElementById('stat2Valor').textContent = ap;
            document.getElementById('stat2Label').textContent = 'Servicios activos';
            document.getElementById('stat3Valor').textContent = Array.isArray(servs) ? servs.length : '—';
            document.getElementById('stat3Label').textContent = 'Servicios totales';
        } else {
            const pend = Array.isArray(servs) ? servs.filter(s => s.estado === 'Pendiente').length : 0;
            const stat2 = document.getElementById('stat2Valor');
            stat2.textContent = pend;
            stat2.style.color = pend > 0 ? '#f59e0b' : '';
            document.getElementById('stat2Label').textContent = 'Por validar';
            document.getElementById('stat3Valor').textContent = Array.isArray(servs) ? servs.length : '—';
            document.getElementById('stat3Label').textContent = 'Servicios totales';
        }
    } catch(e) { console.error(e); }
}

async function cargarCitas() {
    const res  = await fetch('api/citas.php');
    const data = await res.json();
    const cont = document.getElementById('tablaCitas');
    if (!Array.isArray(data) || data.length === 0) {
        cont.innerHTML = '<div class="px-6 py-8 text-center text-[#8e8e93] text-[14px]">No hay citas registradas aún.</div>';
        return;
    }
    cont.innerHTML = data.slice(0,5).map(c => {
        const persona = ROL === 'Paciente' ? (c.nutricionista || '—') : (c.paciente || '—');
        const badgeCls = c.estado === 'confirmada' ? 'badge-green' : 'badge-yellow';
        return `<div class="flex items-center justify-between px-6 py-4 hover:bg-[#f9f9fb] transition-colors">
            <div>
                <p class="font-semibold text-[14px]">${persona}</p>
                <p class="text-[12px] text-[#8e8e93] mt-0.5">${c.especialidad || 'Nutrición General'} · ${c.fecha}${c.hora ? ' ' + c.hora.slice(0,5) : ''}</p>
            </div>
            <span class="badge ${badgeCls}">${c.estado}</span>
        </div>`;
    }).join('');
}

function abrirPerfil()  { document.getElementById('modalPerfil').classList.add('open'); }
function cerrarPerfil() { document.getElementById('modalPerfil').classList.remove('open'); }
</script>
</body>
</html>

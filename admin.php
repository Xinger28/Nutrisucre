<?php
session_start();
if (empty($_SESSION['usuario']))                     { header('Location: login.php');    exit; }
if ($_SESSION['usuario']['rol'] !== 'Administrador') { header('Location: dashboard.php'); exit; }
$usuario = $_SESSION['usuario'];
$nombre  = $usuario['nombre'];
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>NutriSucre - Administración</title>
<script src="https://cdn.tailwindcss.com"></script>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet">
<style>
  body { font-family:'Inter',sans-serif; background:#f5f7f8; }
  .material-symbols-outlined { font-variation-settings:'FILL' 0,'wght' 300; }
  .tab-panel { display:none; }
  .tab-panel.activo { display:block; }
  .tab-btn.activo { background:#22c55e; color:white; border-color:#22c55e; }
</style>
</head>
<body>

<?php $paginaActual = 'admin'; require_once '_sidebar.php'; ?>

<!-- HEADER -->
<header class="flex justify-between items-center px-6 py-4 bg-white/80 backdrop-blur-xl border-b md:pl-72 sticky top-0 z-50">
  <h1 class="text-2xl font-bold">Panel de Administración</h1>
  <div class="text-right">
    <div class="font-semibold text-sm"><?= htmlspecialchars($nombre) ?></div>
    <div class="text-xs text-[#22c55e]">Administrador</div>
  </div>
</header>

<main class="md:pl-64 p-6">

  <!-- Stats rápidos -->
  <div id="statsAdmin" class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6"></div>

  <!-- Tabs -->
  <div class="flex gap-2 mb-6 flex-wrap">
    <button onclick="cambiarTab('postulaciones')" id="tabPostulaciones"
            class="tab-btn activo px-5 py-2.5 border rounded-2xl text-sm font-semibold transition-all">
      <span class="material-symbols-outlined text-base align-middle mr-1">verified_user</span>Postulaciones
    </button>
    <button onclick="cambiarTab('usuarios')" id="tabUsuarios"
            class="tab-btn px-5 py-2.5 border rounded-2xl text-sm font-semibold transition-all text-gray-600">
      <span class="material-symbols-outlined text-base align-middle mr-1">group</span>Usuarios
    </button>
    <button onclick="cambiarTab('servicios')" id="tabServicios"
            class="tab-btn px-5 py-2.5 border rounded-2xl text-sm font-semibold transition-all text-gray-600">
      <span class="material-symbols-outlined text-base align-middle mr-1">medical_services</span>Servicios
      <span id="badgePendientes" class="hidden bg-amber-500 text-white text-xs rounded-full px-1.5 py-0.5 ml-1 font-bold"></span>
    </button>
  </div>

  <!-- ═══ TAB: Postulaciones de nutricionistas ═══ -->
  <div id="panelPostulaciones" class="tab-panel activo">
    <!-- Filtros de estado -->
    <div class="flex gap-2 mb-5 flex-wrap">
      <button onclick="cargarPostulaciones('')"         class="fil-post px-4 py-2 border rounded-xl text-xs font-semibold transition-all bg-gray-100">Todas</button>
      <button onclick="cargarPostulaciones('pendiente')" class="fil-post px-4 py-2 border rounded-xl text-xs font-semibold transition-all text-amber-600 border-amber-300 bg-amber-50">⏳ Pendientes</button>
      <button onclick="cargarPostulaciones('aprobado')"  class="fil-post px-4 py-2 border rounded-xl text-xs font-semibold transition-all text-green-600 border-green-300 bg-green-50">✅ Aprobadas</button>
      <button onclick="cargarPostulaciones('rechazado')" class="fil-post px-4 py-2 border rounded-xl text-xs font-semibold transition-all text-red-600 border-red-300 bg-red-50">❌ Rechazadas</button>
    </div>
    <div id="feedbackPost" class="hidden mb-4 px-5 py-3 rounded-2xl text-sm font-medium text-center"></div>
    <div id="listaPostulaciones" class="space-y-4"></div>
  </div>

  <!-- ═══ TAB: Servicios (Sprint 2) ═══ -->
  <div id="panelServicios" class="tab-panel">
    <div class="flex items-center justify-between mb-5 flex-wrap gap-3">
      <div class="flex gap-2 flex-wrap">
        <button onclick="cargarServiciosAdmin('')"    class="fil-srv px-4 py-2 border rounded-xl text-xs font-semibold bg-gray-100">Todos</button>
        <button onclick="cargarServiciosAdmin('Pendiente')"  class="fil-srv px-4 py-2 border rounded-xl text-xs font-semibold text-amber-600 border-amber-300 bg-amber-50">⏳ Pendientes</button>
        <button onclick="cargarServiciosAdmin('Aprobado')"   class="fil-srv px-4 py-2 border rounded-xl text-xs font-semibold text-green-600 border-green-300 bg-green-50">✅ Aprobados</button>
        <button onclick="cargarServiciosAdmin('Rechazado')"  class="fil-srv px-4 py-2 border rounded-xl text-xs font-semibold text-red-600 border-red-300 bg-red-50">❌ Rechazados</button>
      </div>
      <a href="servicios.php" class="text-[#22c55e] text-sm font-semibold hover:underline flex items-center gap-1">
        <span class="material-symbols-outlined text-base">open_in_new</span>Ver vista completa
      </a>
    </div>
    <div id="feedbackSrv" class="hidden mb-4 px-5 py-3 rounded-2xl text-sm font-medium text-center"></div>
    <div id="listaServiciosAdmin" class="space-y-3"></div>
  </div>

  <!-- ═══ TAB: Gestión de usuarios ═══ -->
  <div id="panelUsuarios" class="tab-panel">
    <!-- Buscador -->
    <div class="bg-white rounded-2xl px-5 py-4 shadow-sm border mb-5 flex items-center gap-3">
      <span class="material-symbols-outlined text-gray-400">search</span>
      <input id="buscarUsuario" type="text" placeholder="Buscar por nombre o email..."
             class="flex-1 outline-none text-sm" oninput="filtrarTabla()">
      <select id="filtroRol" class="border rounded-xl px-3 py-1.5 text-sm outline-none focus:border-[#22c55e]" onchange="filtrarTabla()">
        <option value="">Todos los roles</option>
        <option>Paciente</option><option>Nutricionista</option><option>Administrador</option>
      </select>
      <button onclick="abrirModal()"
              class="bg-[#22c55e] text-white px-4 py-2 rounded-xl text-sm font-semibold flex items-center gap-1 hover:bg-[#16a34a] transition-colors">
        <span class="material-symbols-outlined text-lg">add</span>Nuevo
      </button>
    </div>
    <div id="feedbackUser" class="hidden mb-4 px-5 py-3 rounded-2xl text-sm font-medium text-center"></div>
    <div class="bg-white rounded-3xl shadow-sm overflow-hidden border">
      <table class="w-full">
        <thead class="bg-gray-50 text-sm">
          <tr>
            <th class="px-6 py-5 text-left font-semibold text-gray-600">Nombre</th>
            <th class="px-6 py-5 text-left font-semibold text-gray-600">Email</th>
            <th class="px-6 py-5 text-left font-semibold text-gray-600">Rol</th>
            <th class="px-6 py-5 text-left font-semibold text-gray-600">Registrado</th>
            <th class="px-6 py-5 text-right font-semibold text-gray-600">Acciones</th>
          </tr>
        </thead>
        <tbody id="tablaBody" class="divide-y text-sm">
          <tr><td colspan="5" class="px-6 py-8 text-gray-400">Cargando...</td></tr>
        </tbody>
      </table>
      <div id="paginacion" class="px-6 py-3 border-t text-sm text-gray-400"></div>
    </div>
  </div>
</main>

<!-- MODAL Postulación - Reporte detallado -->
<div id="modalPost" class="hidden fixed inset-0 bg-black/60 flex items-center justify-center z-50 p-4">
  <div class="bg-white rounded-3xl w-full max-w-2xl max-h-[90vh] overflow-y-auto">
    <div class="bg-gradient-to-r from-gray-800 to-gray-700 p-6 rounded-t-3xl text-white flex justify-between items-start">
      <div>
        <h2 id="post_nombre_modal" class="text-xl font-black"></h2>
        <p class="text-gray-300 text-sm mt-1">Reporte de verificación profesional</p>
      </div>
      <button onclick="cerrarModalPost()"><span class="material-symbols-outlined text-white">close</span></button>
    </div>
    <div id="post_contenido" class="p-6 space-y-5 text-sm"></div>
  </div>
</div>

<!-- MODAL Usuario CRUD -->
<div id="modal" class="hidden fixed inset-0 bg-black/60 flex items-center justify-center z-50 p-4">
  <div class="bg-white rounded-3xl w-full max-w-lg p-8">
    <div class="flex justify-between items-center mb-6">
      <h3 id="modalTitle" class="text-2xl font-bold">Nuevo Usuario</h3>
      <button onclick="cerrarModal()"><span class="material-symbols-outlined text-gray-400">close</span></button>
    </div>
    <input type="hidden" id="editId">
    <div class="space-y-4">
      <div><label class="block text-sm font-semibold mb-1">Nombre *</label>
           <input id="campo_nombre" type="text" class="w-full border rounded-2xl px-5 py-3 focus:border-[#22c55e] outline-none"></div>
      <div><label class="block text-sm font-semibold mb-1">Email *</label>
           <input id="campo_email" type="email" class="w-full border rounded-2xl px-5 py-3 focus:border-[#22c55e] outline-none"></div>
      <div><label class="block text-sm font-semibold mb-1">Contraseña <span id="labelPass" class="text-gray-400 font-normal text-xs"></span></label>
           <input id="campo_password" type="password" class="w-full border rounded-2xl px-5 py-3 focus:border-[#22c55e] outline-none"></div>
      <div><label class="block text-sm font-semibold mb-1">Rol *</label>
           <select id="campo_rol" class="w-full border rounded-2xl px-5 py-3 focus:border-[#22c55e] outline-none">
             <option>Paciente</option><option>Nutricionista</option><option>Administrador</option>
           </select></div>
    </div>
    <div id="mensajeModal" class="hidden mt-4 px-4 py-3 rounded-xl text-sm font-medium"></div>
    <div class="flex gap-3 mt-6">
      <button onclick="cerrarModal()" class="flex-1 py-3 border rounded-2xl font-semibold">Cancelar</button>
      <button onclick="guardarUsuario()" id="btnGuardar"
              class="flex-1 py-3 bg-[#22c55e] text-white rounded-2xl font-bold hover:bg-[#16a34a] transition-colors">Guardar</button>
    </div>
  </div>
</div>

<!-- MODAL Confirmar eliminación -->
<div id="modalEliminar" class="hidden fixed inset-0 bg-black/60 flex items-center justify-center z-50 p-4">
  <div class="bg-white rounded-3xl p-8 w-full max-w-sm text-center">
    <span class="material-symbols-outlined text-red-400 text-6xl">warning</span>
    <h3 class="text-xl font-bold mt-3 mb-2">¿Eliminar usuario?</h3>
    <p id="textoEliminar" class="text-gray-500 text-sm mb-6"></p>
    <div class="flex gap-3">
      <button onclick="cerrarEliminar()" class="flex-1 py-3 border rounded-2xl font-semibold">Cancelar</button>
      <button onclick="ejecutarEliminar()" class="flex-1 py-3 bg-red-500 text-white rounded-2xl font-bold">Eliminar</button>
    </div>
  </div>
</div>

<script>
// ──────────────────────────────────────────
//  Estado
// ──────────────────────────────────────────
let todosLosUsuarios = [];
let todasLasPostulaciones = [];
let idParaEliminar   = null;
const BADGE = { 'Administrador':'bg-purple-100 text-purple-700', 'Nutricionista':'bg-blue-100 text-blue-700', 'Paciente':'bg-green-100 text-green-700' };

document.addEventListener('DOMContentLoaded', () => {
    cargarStats();
    cargarPostulaciones('');
    cargarUsuarios();
    // Pre-cargar conteo de servicios pendientes para el badge
    cargarServiciosAdmin('');
});

// ──────────────────────────────────────────
//  Tabs
// ──────────────────────────────────────────
function cambiarTab(tab) {
    ['postulaciones','usuarios','servicios'].forEach(t => {
        document.getElementById('panel' + t.charAt(0).toUpperCase() + t.slice(1)).classList.remove('activo');
        document.getElementById('tab'   + t.charAt(0).toUpperCase() + t.slice(1)).classList.remove('activo');
        document.getElementById('tab'   + t.charAt(0).toUpperCase() + t.slice(1)).classList.add('text-gray-600');
    });
    document.getElementById('panel' + tab.charAt(0).toUpperCase() + tab.slice(1)).classList.add('activo');
    document.getElementById('tab'   + tab.charAt(0).toUpperCase() + tab.slice(1)).classList.add('activo');
    document.getElementById('tab'   + tab.charAt(0).toUpperCase() + tab.slice(1)).classList.remove('text-gray-600');
    // Cargar datos del tab seleccionado
    if (tab === 'servicios') cargarServiciosAdmin('');
}

// ──────────────────────────────────────────
//  Stats
// ──────────────────────────────────────────
async function cargarStats() {
    const [resU, resP] = await Promise.all([fetch('api/usuarios.php'), fetch('api/postulaciones.php')]);
    const [usuarios, posts] = await Promise.all([resU.json(), resP.json()]);

    const pendientes = Array.isArray(posts) ? posts.filter(p => p.estado === 'pendiente').length : 0;
    const aprobados  = Array.isArray(posts) ? posts.filter(p => p.estado === 'aprobado').length  : 0;
    const total      = Array.isArray(usuarios) ? usuarios.length : 0;

    document.getElementById('statsAdmin').innerHTML = [
        { lbl:'Total usuarios',    val:total,    color:'text-gray-800' },
        { lbl:'Postulaciones',     val:Array.isArray(posts)?posts.length:0, color:'text-blue-600' },
        { lbl:'Pendientes revisión',val:pendientes, color:'text-amber-600' },
        { lbl:'Nutricionistas OK', val:aprobados,  color:'text-[#22c55e]' },
    ].map(s => `
        <div class="bg-white rounded-2xl p-4 shadow-sm border text-center">
            <p class="text-3xl font-black ${s.color}">${s.val}</p>
            <p class="text-xs text-gray-500 mt-1">${s.lbl}</p>
        </div>`).join('');
}

// ──────────────────────────────────────────
//  Postulaciones
// ──────────────────────────────────────────
async function cargarPostulaciones(estado) {
    const url = 'api/postulaciones.php' + (estado ? `?estado=${estado}` : '');
    const res  = await fetch(url);
    const data = await res.json();
    todasLasPostulaciones = Array.isArray(data) ? data : [];
    renderPostulaciones(todasLasPostulaciones);
}

function renderPostulaciones(lista) {
    const cont = document.getElementById('listaPostulaciones');

    if (lista.length === 0) {
        cont.innerHTML = '<div class="bg-white rounded-2xl p-10 text-center text-gray-400">No hay postulaciones en este estado.</div>';
        return;
    }

    cont.innerHTML = lista.map(p => {
        const estadoClr = p.estado === 'aprobado'  ? 'bg-green-100 text-green-700'
                        : p.estado === 'rechazado' ? 'bg-red-100 text-red-700'
                        : 'bg-amber-100 text-amber-700';
        const puntajeClr = p.puntaje_tecnico >= 70 ? 'text-green-600'
                         : p.puntaje_tecnico >= 40 ? 'text-amber-600'
                         : 'text-red-600';

        return `
        <div class="bg-white rounded-2xl shadow-sm border p-5">
            <div class="flex justify-between items-start flex-wrap gap-3">
                <div class="flex-1">
                    <div class="flex items-center gap-2 flex-wrap">
                        <h3 class="font-bold text-lg">${p.nombre}</h3>
                        <span class="${estadoClr} px-3 py-0.5 rounded-full text-xs font-semibold uppercase">${p.estado}</span>
                    </div>
                    <p class="text-gray-500 text-sm">${p.email}</p>
                    <div class="flex gap-4 mt-2 text-xs text-gray-500 flex-wrap">
                        <span>🎓 ${p.universidad || '—'}</span>
                        <span>📋 ${p.titulo_prof || '—'}</span>
                        <span>🔑 Reg: ${p.registro_prof || '—'}</span>
                    </div>
                    ${p.alertas ? `<div class="mt-2 text-xs text-amber-600 bg-amber-50 px-3 py-1.5 rounded-xl">${p.alertas.split('\n').join(' · ')}</div>` : ''}
                </div>
                <div class="text-center">
                    <p class="text-xs text-gray-500">Puntaje técnico</p>
                    <p class="text-3xl font-black ${puntajeClr}">${p.puntaje_tecnico}<span class="text-sm text-gray-400">/100</span></p>
                    <p class="text-xs ${puntajeClr} font-medium">${p.puntaje_tecnico>=70?'✅ Alto':p.puntaje_tecnico>=40?'⚠ Medio':'❌ Bajo'}</p>
                </div>
            </div>

            <!-- Botones de acción -->
            <div class="flex gap-2 mt-4 flex-wrap">
                <button onclick="verReportePorId(${p.id})"
                        class="px-4 py-2 border rounded-xl text-xs font-semibold hover:bg-gray-50 flex items-center gap-1">
                    <span class="material-symbols-outlined text-base">description</span>Ver reporte
                </button>
                ${p.estado !== 'aprobado' ? `
                <button onclick="revisarPost(${p.id},'aprobado')"
                        class="px-4 py-2 bg-green-500 text-white rounded-xl text-xs font-bold hover:bg-green-600 flex items-center gap-1">
                    <span class="material-symbols-outlined text-base">check_circle</span>Aprobar
                </button>` : ''}
                ${p.estado !== 'pendiente' ? `
                <button onclick="revisarPost(${p.id},'pendiente')"
                        class="px-4 py-2 bg-amber-500 text-white rounded-xl text-xs font-bold hover:bg-amber-600 flex items-center gap-1">
                    <span class="material-symbols-outlined text-base">hourglass</span>Pendiente
                </button>` : ''}
                ${p.estado !== 'rechazado' ? `
                <button onclick="revisarPost(${p.id},'rechazado')"
                        class="px-4 py-2 bg-red-500 text-white rounded-xl text-xs font-bold hover:bg-red-600 flex items-center gap-1">
                    <span class="material-symbols-outlined text-base">cancel</span>Rechazar
                </button>` : ''}
            </div>
        </div>`;
    }).join('');
}

function verReporte(p) {
    document.getElementById('post_nombre_modal').textContent = p.nombre;

    const especialidades = (() => { try { return JSON.parse(p.especialidades || '[]'); } catch { return []; } })();
    const espTexto = especialidades.map(e => `${e.nombre} (${e.años || 0} años)`).join(', ') || '—';

    const puntajeClr = p.puntaje_tecnico >= 70 ? '#22c55e' : p.puntaje_tecnico >= 40 ? '#f59e0b' : '#ef4444';
    const recomendacion = p.puntaje_tecnico >= 70 ? '✅ APROBADO' : p.puntaje_tecnico >= 40 ? '⚠ REVISIÓN NECESARIA' : '❌ RECHAZADO';

    document.getElementById('post_contenido').innerHTML = `
        <!-- Resumen ejecutivo -->
        <div class="bg-gray-50 rounded-2xl p-5 border">
            <h3 class="font-bold text-base mb-4 flex items-center gap-2">
                <span class="material-symbols-outlined text-[#22c55e]">summarize</span>Resumen del postulante
            </h3>
            <div class="grid grid-cols-2 gap-3 text-xs">
                <div><p class="text-gray-400 font-semibold uppercase">Nombre</p><p class="font-medium mt-0.5">${p.nombre}</p></div>
                <div><p class="text-gray-400 font-semibold uppercase">Universidad</p><p class="font-medium mt-0.5">${p.universidad || '—'}</p></div>
                <div><p class="text-gray-400 font-semibold uppercase">Título</p><p class="font-medium mt-0.5">${p.titulo_prof || '—'}</p></div>
                <div><p class="text-gray-400 font-semibold uppercase">Registro profesional</p><p class="font-medium mt-0.5">${p.registro_prof || '—'}</p></div>
                <div class="col-span-2"><p class="text-gray-400 font-semibold uppercase">Especialidades</p><p class="font-medium mt-0.5">${espTexto}</p></div>
            </div>
        </div>

        <!-- Estado documental -->
        <div class="bg-gray-50 rounded-2xl p-5 border">
            <h3 class="font-bold text-base mb-3 flex items-center gap-2">
                <span class="material-symbols-outlined text-blue-500">folder_open</span>Estado documental
            </h3>
            <div class="space-y-2 text-xs">
                <div class="flex justify-between"><span>Título profesional</span><span class="font-semibold ${p.titulo_prof ? 'text-green-600':'text-red-500'}">${p.titulo_prof ? '✅ Proporcionado':'❌ Faltante'}</span></div>
                <div class="flex justify-between"><span>Registro profesional</span><span class="font-semibold ${p.registro_prof ? 'text-green-600':'text-red-500'}">${p.registro_prof ? '✅ Proporcionado':'❌ Faltante'}</span></div>
                <div class="flex justify-between"><span>Licencia vigente</span><span class="font-semibold ${p.licencia_vence && p.licencia_vence >= new Date().toISOString().split('T')[0] ? 'text-green-600':'text-amber-500'}">${p.licencia_vence ? '⚠ Verificar manualmente':'❌ Sin fecha'}</span></div>
            </div>
        </div>

        <!-- Puntaje técnico -->
        <div class="rounded-2xl p-5 border" style="background:${puntajeClr}15;border-color:${puntajeClr}40">
            <h3 class="font-bold text-base mb-3">Puntaje técnico IA</h3>
            <div class="flex items-center gap-4 mb-3">
                <p class="text-5xl font-black" style="color:${puntajeClr}">${p.puntaje_tecnico}</p>
                <div>
                    <p class="text-sm text-gray-500">de 100 puntos</p>
                    <p class="font-bold" style="color:${puntajeClr}">${recomendacion}</p>
                </div>
            </div>
            ${p.alertas ? `<div class="bg-amber-50 border border-amber-200 rounded-xl p-3 text-xs text-amber-700">${p.alertas.split('\n').map(a=>`<p>${a}</p>`).join('')}</div>` : '<p class="text-xs text-green-600">✅ Sin alertas detectadas</p>'}
        </div>

        <!-- Respuestas técnicas -->
        <div class="bg-gray-50 rounded-2xl p-5 border">
            <h3 class="font-bold text-base mb-4 flex items-center gap-2">
                <span class="material-symbols-outlined text-purple-500">quiz</span>Respuestas técnicas
            </h3>
            ${[
                ['Obesidad + Diabetes tipo 2', p.resp_tecnica_1],
                ['Indicadores estado nutricional', p.resp_tecnica_2],
                ['Diseño de plan alimenticio', p.resp_tecnica_3],
                ['Desnutrición vs Malnutrición', p.resp_tecnica_4],
                ['Baja adherencia al tratamiento', p.resp_tecnica_5],
            ].map(([q,r], i) => `
                <div class="mb-3">
                    <p class="font-semibold text-xs text-gray-500 uppercase mb-1">${i+1}. ${q}</p>
                    <p class="text-xs text-gray-700 bg-white p-3 rounded-xl border leading-relaxed">${r || '<span class="text-red-400">Sin respuesta</span>'}</p>
                </div>`).join('')}
        </div>

        <!-- Notas del admin y botones -->
        <div>
            <label class="block text-sm font-semibold mb-2">Notas del administrador</label>
            <textarea id="notasAdmin_${p.id}" rows="2" placeholder="Observaciones internas (no visibles al postulante)..."
                      class="w-full border rounded-2xl px-4 py-3 text-sm focus:border-[#22c55e] outline-none resize-none">${p.notas_admin || ''}</textarea>
        </div>
        <div class="flex gap-3 flex-wrap">
            <button onclick="revisarPost(${p.id},'aprobado',true)" class="flex-1 py-3 bg-green-500 text-white rounded-2xl font-bold text-sm hover:bg-green-600">✅ Aprobar</button>
            <button onclick="revisarPost(${p.id},'pendiente',true)" class="flex-1 py-3 bg-amber-500 text-white rounded-2xl font-bold text-sm hover:bg-amber-600">⏳ Pendiente</button>
            <button onclick="revisarPost(${p.id},'rechazado',true)" class="flex-1 py-3 bg-red-500 text-white rounded-2xl font-bold text-sm hover:bg-red-600">❌ Rechazar</button>
        </div>`;

    document.getElementById('modalPost').classList.remove('hidden');
}

function cerrarModalPost() { document.getElementById('modalPost').classList.add('hidden'); }

async function revisarPost(id, estado, desdeModal = false) {
    const notas = document.getElementById(`notasAdmin_${id}`)?.value || '';
    const res   = await fetch('api/postulaciones.php', {
        method: 'PUT', headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ id, estado, notas_admin: notas })
    });
    const data = await res.json();
    if (data.ok) {
        mostrarFeedbackPost('✅ ' + data.mensaje, 'ok');
        if (desdeModal) cerrarModalPost();
        cargarPostulaciones('');
        cargarStats();
    } else {
        mostrarFeedbackPost(data.error || 'Error', 'error');
    }
}

function mostrarFeedbackPost(txt, tipo) {
    const el = document.getElementById('feedbackPost');
    el.textContent = txt;
    el.className = `mb-4 px-5 py-3 rounded-2xl text-sm font-medium text-center ${tipo==='ok'?'bg-green-100 text-green-700':'bg-red-100 text-red-700'}`;
    el.classList.remove('hidden');
    setTimeout(() => el.classList.add('hidden'), 4000);
}

// ──────────────────────────────────────────
//  Gestión de Usuarios (tab)
// ──────────────────────────────────────────
async function cargarUsuarios() {
    const res  = await fetch('api/usuarios.php');
    const data = await res.json();
    if (!data.error) { todosLosUsuarios = data; renderStats2(data); renderTabla(data); }
}

function renderStats2(data) {
    // Stats ya los carga cargarStats() arriba, no duplicar
}

function renderTabla(data) {
    const tbody = document.getElementById('tablaBody');
    document.getElementById('paginacion').textContent = `${data.length} de ${todosLosUsuarios.length} usuarios`;
    if (data.length === 0) {
        tbody.innerHTML = '<tr><td colspan="5" class="px-6 py-8 text-gray-400 text-center">No se encontraron usuarios.</td></tr>';
        return;
    }
    tbody.innerHTML = data.map(u => {
        const fecha = new Date(u.created_at).toLocaleDateString('es-BO',{day:'2-digit',month:'short',year:'numeric'});
        return `<tr class="hover:bg-gray-50">
            <td class="px-6 py-5 font-medium">${u.nombre}</td>
            <td class="px-6 py-5 text-gray-500">${u.email}</td>
            <td class="px-6 py-5"><span class="${BADGE[u.rol]||'bg-gray-100 text-gray-700'} px-3 py-1 rounded-full text-xs font-medium">${u.rol}</span></td>
            <td class="px-6 py-5 text-gray-400 text-xs">${fecha}</td>
            <td class="px-6 py-5 text-right space-x-1">
                <button onclick="abrirEditarPorId(${u.id})"
                        class="p-2 text-[#22c55e] hover:bg-green-50 rounded-xl transition-colors">
                    <span class="material-symbols-outlined text-xl">edit</span></button>
                <button onclick="pedirEliminarPorId(${u.id})"
                        class="p-2 text-red-400 hover:bg-red-50 rounded-xl transition-colors">
                    <span class="material-symbols-outlined text-xl">delete</span></button>
            </td></tr>`;
    }).join('');
}

function filtrarTabla() {
    const t = document.getElementById('buscarUsuario').value.toLowerCase();
    const r = document.getElementById('filtroRol').value;
    renderTabla(todosLosUsuarios.filter(u =>
        (!t || u.nombre.toLowerCase().includes(t) || u.email.toLowerCase().includes(t)) &&
        (!r || u.rol === r)
    ));
}

function abrirModal() {
    document.getElementById('editId').value = '';
    document.getElementById('modalTitle').textContent = 'Nuevo Usuario';
    document.getElementById('campo_nombre').value = '';
    document.getElementById('campo_email').value  = '';
    document.getElementById('campo_password').value = '';
    document.getElementById('campo_rol').value = 'Paciente';
    document.getElementById('labelPass').textContent = '(obligatoria)';
    ocultarMsgModal();
    document.getElementById('modal').classList.remove('hidden');
}
function abrirEditar(id, nombre, email, rol) {
    document.getElementById('editId').value = id;
    document.getElementById('modalTitle').textContent = 'Editar Usuario';
    document.getElementById('campo_nombre').value = nombre;
    document.getElementById('campo_email').value  = email;
    document.getElementById('campo_password').value = '';
    document.getElementById('campo_rol').value = rol;
    document.getElementById('labelPass').textContent = '(vacío = no cambiar)';
    ocultarMsgModal();
    document.getElementById('modal').classList.remove('hidden');
}
function abrirEditarPorId(id) {
    const u = todosLosUsuarios.find(x => x.id === id);
    if (u) abrirEditar(u.id, u.nombre, u.email, u.rol);
}
function pedirEliminarPorId(id) {
    const u = todosLosUsuarios.find(x => x.id === id);
    if (u) pedirEliminar(u.id, u.nombre);
}
function cerrarModal() { document.getElementById('modal').classList.add('hidden'); }

async function guardarUsuario() {
    const id = document.getElementById('editId').value;
    const nombre = document.getElementById('campo_nombre').value.trim();
    const email  = document.getElementById('campo_email').value.trim();
    const pass   = document.getElementById('campo_password').value;
    const rol    = document.getElementById('campo_rol').value;
    if (!nombre || !email) return mostrarMsgModal('Nombre y email son obligatorios');
    if (!id && !pass) return mostrarMsgModal('La contraseña es obligatoria para nuevos');

    const btn = document.getElementById('btnGuardar');
    btn.disabled = true; btn.textContent = 'Guardando...';

    const body = { nombre, email, rol };
    if (id) body.id = parseInt(id);
    if (pass) body.password = pass;

    const res  = await fetch('api/usuarios.php', {
        method: id ? 'PUT' : 'POST', headers: {'Content-Type':'application/json'},
        body: JSON.stringify(body)
    });
    const data = await res.json();
    btn.disabled = false; btn.textContent = 'Guardar';

    if (data.ok) { mostrarFeedbackUser('✅ ' + data.mensaje,'ok'); cerrarModal(); cargarUsuarios(); }
    else mostrarMsgModal(data.error || 'Error');
}

function pedirEliminar(id, nombre) {
    idParaEliminar = id;
    document.getElementById('textoEliminar').textContent = `Se eliminará a "${nombre}" permanentemente.`;
    document.getElementById('modalEliminar').classList.remove('hidden');
}
function cerrarEliminar() { document.getElementById('modalEliminar').classList.add('hidden'); idParaEliminar=null; }
async function ejecutarEliminar() {
    const res  = await fetch('api/usuarios.php',{ method:'DELETE', headers:{'Content-Type':'application/json'}, body:JSON.stringify({id:idParaEliminar}) });
    const data = await res.json();
    cerrarEliminar();
    if (data.ok) { mostrarFeedbackUser('✅ '+data.mensaje,'ok'); cargarUsuarios(); }
    else mostrarFeedbackUser(data.error||'Error','error');
}

function mostrarFeedbackUser(txt,tipo) {
    const el=document.getElementById('feedbackUser');
    el.textContent=txt; el.className=`mb-4 px-5 py-3 rounded-2xl text-sm font-medium text-center ${tipo==='ok'?'bg-green-100 text-green-700':'bg-red-100 text-red-700'}`;
    el.classList.remove('hidden'); setTimeout(()=>el.classList.add('hidden'),4000);
}
function mostrarMsgModal(txt) {
    const el=document.getElementById('mensajeModal');
    el.textContent=txt; el.className='mt-4 px-4 py-3 rounded-xl text-sm font-medium bg-red-100 text-red-700';
    el.classList.remove('hidden');
}
function ocultarMsgModal() { document.getElementById('mensajeModal').classList.add('hidden'); }

// ──────────────────────────────────────────────────
//  Sprint 2: Cargar y validar servicios desde admin
// ──────────────────────────────────────────────────
async function cargarServiciosAdmin(estado) {
    const lista = document.getElementById('listaServiciosAdmin');
    if (!lista) return;
    lista.innerHTML = '<p class="text-gray-400 text-sm text-center py-6">Cargando...</p>';

    const url = 'api/servicios.php' + (estado ? '?estado=' + estado : '');
    const res  = await fetch(url);
    const data = await res.json();

    if (!Array.isArray(data) || data.length === 0) {
        lista.innerHTML = '<p class="text-gray-400 text-sm text-center py-8">No hay servicios en este estado.</p>';
        return;
    }

    // Actualizar badge de pendientes en el tab
    const pendientes = data.filter(s => s.estado === 'Pendiente').length;
    const badge = document.getElementById('badgePendientes');
    if (badge) {
        if (pendientes > 0 && !estado) { badge.textContent = pendientes; badge.classList.remove('hidden'); }
        else badge.classList.add('hidden');
    }

    const BADGE = { 'Pendiente':'bg-amber-100 text-amber-700', 'Aprobado':'bg-green-100 text-green-700', 'Rechazado':'bg-red-100 text-red-700' };

    lista.innerHTML = data.map(s => `
        <div class="bg-white rounded-2xl border p-4 flex items-start justify-between gap-4">
            <div class="flex-1 min-w-0">
                <div class="flex items-center gap-2 flex-wrap mb-1">
                    <span class="${BADGE[s.estado]||'bg-gray-100 text-gray-600'} px-2 py-0.5 rounded-full text-xs font-semibold">${s.estado}</span>
                    <span class="text-xs text-gray-400">${s.categoria}</span>
                </div>
                <h4 class="font-bold text-sm truncate">${s.titulo}</h4>
                <p class="text-gray-500 text-xs">Por: ${s.nutricionista_nombre} · Bs. ${parseFloat(s.precio).toFixed(2)} · ${s.duracion_semanas} sem.</p>
                ${s.motivo_rechazo ? `<p class="text-red-500 text-xs mt-1">Motivo: ${s.motivo_rechazo}</p>` : ''}
            </div>
            <div class="flex gap-2 flex-shrink-0">
                ${s.estado !== 'Aprobado' ? `
                <button onclick="validarServicioAdmin(${s.id},'Aprobado')"
                        class="px-3 py-1.5 bg-green-500 text-white rounded-xl text-xs font-bold hover:bg-green-600">✅</button>` : ''}
                ${s.estado !== 'Rechazado' ? `
                <button onclick="pedirRechazoAdmin(${s.id},'${s.titulo.replace(/'/g,"\'")}  ')"
                        class="px-3 py-1.5 bg-red-500 text-white rounded-xl text-xs font-bold hover:bg-red-600">❌</button>` : ''}
            </div>
        </div>`).join('');
}

async function validarServicioAdmin(id, estado, motivo = '') {
    const res  = await fetch('api/servicios.php?accion=validar', {
        method: 'PUT', headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ id, estado, motivo })
    });
    const data = await res.json();
    const el = document.getElementById('feedbackSrv');
    if (data.ok) {
        el.textContent = '✅ ' + data.mensaje;
        el.className = 'mb-4 px-5 py-3 rounded-2xl text-sm font-medium text-center bg-green-100 text-green-700';
        el.classList.remove('hidden');
        setTimeout(() => el.classList.add('hidden'), 4000);
        cargarServiciosAdmin('');
        cargarStats();
    } else {
        el.textContent = '❌ ' + (data.error || 'Error al validar el servicio');
        el.className = 'mb-4 px-5 py-3 rounded-2xl text-sm font-medium text-center bg-red-100 text-red-700';
        el.classList.remove('hidden');
        setTimeout(() => el.classList.add('hidden'), 4000);
    }
}

// Modal de rechazo inline (reutiliza el modal de postulaciones)
let srvIdParaRechazar = null;
function pedirRechazoAdmin(id, titulo) {
    srvIdParaRechazar = id;
    // Abrir un prompt simple para el motivo
    const motivo = prompt(`Motivo de rechazo para:
"${titulo}"

(Obligatorio, mínimo 10 caracteres)`);
    if (motivo === null) return; // canceló
    if (!motivo || motivo.trim().length < 10) {
        alert('El motivo debe tener al menos 10 caracteres.');
        return;
    }
    validarServicioAdmin(id, 'Rechazado', motivo.trim());
}

async function logout() {
    if(!confirm('¿Cerrar sesión?')) return;
    await fetch('api/auth.php?accion=logout',{method:'POST'});
    window.location.href='login.php';
}

['modal','modalEliminar','modalPost'].forEach(id => {
    document.getElementById(id).addEventListener('click', e => {
        if(e.target.id===id) document.getElementById(id).classList.add('hidden');
    });
});
</script>
</body>
</html>

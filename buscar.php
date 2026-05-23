<?php
session_start();
if (empty($_SESSION['usuario'])) { header('Location: login.php'); exit; }
$usuario = $_SESSION['usuario'];
$rol     = $usuario['rol'];
$nombre  = $usuario['nombre'];

// Nutricionistas no buscan a otros nutricionistas, van a gestión de planes
if ($rol === 'Nutricionista') { header('Location: servicios.php'); exit; }
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>NutriSucre - Buscar Nutricionistas</title>
<script src="https://cdn.tailwindcss.com"></script>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet">
<style>
  body { font-family:'Inter',sans-serif; background:#f8fafb; }
  .material-symbols-outlined { font-variation-settings:'FILL' 0,'wght' 300; }
  .card { transition:transform .3s,box-shadow .3s; }
  .card:hover { transform:translateY(-6px); box-shadow:0 20px 25px -5px rgba(0,0,0,.1); }
  .star { cursor:pointer; font-size:26px; color:#d1d5db; transition:color .15s; }
  .star.activo,.star.hover { color:#f59e0b; }
  /* Calendario de disponibilidad */
  .slot { transition:all .2s; cursor:pointer; }
  .slot:hover { background:#22c55e; color:white; }
  .slot.ocupado { background:#fee2e2; color:#dc2626; cursor:not-allowed; pointer-events:none; }
  .slot.seleccionado { background:#22c55e; color:white; }
  /* Sello verificado */
  .sello-verificado { background:linear-gradient(135deg,#f0fdf4,#dcfce7); border:1.5px solid #22c55e; }
</style>
</head>
<body>

<?php $paginaActual = 'buscar'; require_once '_sidebar.php'; ?>

<main class="md:pl-64 p-6 max-w-7xl mx-auto">
  <div class="flex justify-between items-center mb-6">
    <h1 class="text-3xl font-black">Buscar Nutricionistas</h1>
    <div class="text-right hidden sm:block">
      <div class="font-semibold text-sm"><?= htmlspecialchars($nombre) ?></div>
      <div class="text-xs text-[#22c55e]"><?= htmlspecialchars($rol) ?></div>
    </div>
  </div>

  <!-- FILTROS AVANZADOS -->
  <div class="bg-white rounded-3xl p-5 shadow-sm border mb-6">
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
      <div class="md:col-span-2">
        <label class="block text-xs font-semibold text-gray-500 mb-1">Nombre o especialidad</label>
        <input id="f_nombre" type="text" placeholder="ej: Nutrición Deportiva, Diabetes..."
               class="w-full border rounded-xl px-4 py-2.5 text-sm focus:border-[#22c55e] outline-none"
               oninput="filtrarConDebounce()">
      </div>
      <div>
        <label class="block text-xs font-semibold text-gray-500 mb-1">Precio máx. (Bs.)</label>
        <input id="f_precio" type="number" min="0" placeholder="ej: 200"
               class="w-full border rounded-xl px-4 py-2.5 text-sm focus:border-[#22c55e] outline-none"
               oninput="filtrarConDebounce()">
      </div>
      <div>
        <label class="block text-xs font-semibold text-gray-500 mb-1">Calificación mínima</label>
        <select id="f_rating" class="w-full border rounded-xl px-4 py-2.5 text-sm focus:border-[#22c55e] outline-none" onchange="filtrar()">
          <option value="">Cualquiera</option>
          <option value="3">★★★ 3.0+</option>
          <option value="4">★★★★ 4.0+</option>
          <option value="4.5">★★★★½ 4.5+</option>
        </select>
      </div>
    </div>
    <!-- Filtro de modalidad -->
    <div class="flex gap-3 mt-4 flex-wrap">
      <span class="text-xs text-gray-500 self-center font-semibold">Modalidad:</span>
      <button onclick="filtrarModalidad('')"    id="mTodas"    class="chip-mod text-xs px-3 py-1 rounded-full border bg-[#22c55e] text-white border-[#22c55e] font-medium">Todas</button>
      <button onclick="filtrarModalidad('Virtual')"    id="mVirtual"    class="chip-mod text-xs px-3 py-1 rounded-full border hover:bg-[#22c55e] hover:text-white hover:border-[#22c55e] transition-colors font-medium">Virtual</button>
      <button onclick="filtrarModalidad('Presencial')" id="mPresencial" class="chip-mod text-xs px-3 py-1 rounded-full border hover:bg-[#22c55e] hover:text-white hover:border-[#22c55e] transition-colors font-medium">Presencial</button>
    </div>
    <!-- Chips especialidades -->
    <div class="flex gap-2 mt-3 flex-wrap">
      <span class="text-xs text-gray-500 self-center">Especialidades:</span>
      <?php foreach(['Nutrición Deportiva','Diabetes y Obesidad','Nutrición Infantil','Nutrición Clínica'] as $esp): ?>
      <button onclick="filtrarEspecialidad('<?= $esp ?>')"
              class="text-xs px-3 py-1 border rounded-full hover:bg-[#22c55e] hover:text-white hover:border-[#22c55e] transition-colors font-medium">
        <?= $esp ?>
      </button>
      <?php endforeach; ?>
      <button onclick="limpiarFiltros()" class="text-xs px-3 py-1 text-gray-400 hover:text-red-500 transition-colors">✕ Limpiar</button>
    </div>
  </div>

  <p id="contadorRes" class="text-sm text-gray-500 mb-4"></p>

  <!-- CARDS -->
  <div id="cardsContainer" class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
    <p class="text-gray-400 col-span-3 text-center py-12">Cargando nutricionistas verificados...</p>
  </div>
</main>

<!-- ═══════════════════════════════════════════════
     MODAL: Ver información completa del especialista
════════════════════════════════════════════════ -->
<div id="modalDetalle" class="hidden fixed inset-0 bg-black/70 flex items-center justify-center z-50 p-4">
  <div class="bg-white rounded-3xl w-full max-w-2xl max-h-[90vh] overflow-y-auto">
    <!-- Header verde -->
    <div class="bg-gradient-to-r from-[#22c55e] to-[#16a34a] p-6 rounded-t-3xl text-white relative">
      <button onclick="cerrarDetalle()" class="absolute top-4 right-4 bg-white/20 hover:bg-white/30 rounded-full p-1">
        <span class="material-symbols-outlined text-white">close</span>
      </button>
      <div class="flex items-center gap-4">
        <div id="det_avatar" class="w-20 h-20 bg-white/20 rounded-2xl flex items-center justify-center text-4xl">🥑</div>
        <div>
          <h2 id="det_nombre" class="text-2xl font-black"></h2>
          <p id="det_esp"     class="opacity-90 font-medium"></p>
          <div id="det_sello" class="sello-verificado hidden inline-flex items-center gap-1 mt-2 px-3 py-1 rounded-full text-xs font-bold text-[#16a34a]">
            <span class="material-symbols-outlined text-sm">verified</span> Profesional verificado
          </div>
        </div>
      </div>
    </div>

    <div class="p-6 space-y-5">

      <!-- Métricas rápidas -->
      <div class="grid grid-cols-3 gap-3">
        <div class="text-center bg-gray-50 rounded-2xl p-3">
          <p id="det_exp" class="text-2xl font-black text-[#22c55e]">—</p>
          <p class="text-xs text-gray-500">Años exp.</p>
        </div>
        <div class="text-center bg-gray-50 rounded-2xl p-3">
          <p id="det_pac" class="text-2xl font-black text-[#22c55e]">—</p>
          <p class="text-xs text-gray-500">Pacientes</p>
        </div>
        <div class="text-center bg-gray-50 rounded-2xl p-3">
          <p id="det_rating" class="text-2xl font-black text-amber-500">—</p>
          <p class="text-xs text-gray-500">Calificación</p>
        </div>
      </div>

      <!-- Biografía -->
      <div id="det_bio_bloque" class="hidden">
        <h3 class="font-bold text-sm text-gray-700 mb-2">Sobre el profesional</h3>
        <p id="det_bio" class="text-sm text-gray-600 leading-relaxed bg-gray-50 p-4 rounded-2xl"></p>
      </div>

      <!-- Info profesional -->
      <div class="grid grid-cols-2 gap-3 text-sm">
        <div class="bg-gray-50 rounded-2xl p-3">
          <p class="font-semibold text-gray-500 text-xs mb-1">UNIVERSIDAD</p>
          <p id="det_univ" class="font-medium">—</p>
        </div>
        <div class="bg-gray-50 rounded-2xl p-3">
          <p class="font-semibold text-gray-500 text-xs mb-1">TÍTULO</p>
          <p id="det_titulo" class="font-medium">—</p>
        </div>
        <div class="bg-gray-50 rounded-2xl p-3">
          <p class="font-semibold text-gray-500 text-xs mb-1">MODALIDAD</p>
          <p id="det_modalidad" class="font-medium">—</p>
        </div>
        <div class="bg-gray-50 rounded-2xl p-3">
          <p class="font-semibold text-gray-500 text-xs mb-1">IDIOMAS</p>
          <p id="det_idiomas" class="font-medium">—</p>
        </div>
        <div class="bg-gray-50 rounded-2xl p-3">
          <p class="font-semibold text-gray-500 text-xs mb-1">CONSULTA DESDE</p>
          <p id="det_precio" class="font-black text-[#22c55e] text-lg">—</p>
        </div>
        <div class="bg-gray-50 rounded-2xl p-3">
          <p class="font-semibold text-gray-500 text-xs mb-1">DURACIÓN</p>
          <p id="det_duracion" class="font-medium">—</p>
        </div>
      </div>

      <!-- Reseñas de pacientes -->
      <div>
        <h3 class="font-bold text-sm text-gray-700 mb-3">Opiniones de pacientes</h3>
        <div id="det_resenas" class="space-y-3 max-h-48 overflow-y-auto"></div>
      </div>

      <!-- Botón reservar desde el detalle -->
      <button id="det_btn_reservar"
              class="w-full py-4 bg-[#22c55e] text-white rounded-2xl font-bold hover:bg-[#16a34a] transition-colors">
        Reservar cita con este especialista
      </button>
    </div>
  </div>
</div>

<!-- ═══════════════════════════════════════════════
     MODAL: Agendar cita con calendario inteligente
════════════════════════════════════════════════ -->
<div id="modalCita" class="hidden fixed inset-0 bg-black/70 flex items-center justify-center z-[60] p-4">
  <div class="bg-white rounded-3xl w-full max-w-lg">
    <!-- Header -->
    <div class="bg-gradient-to-r from-[#22c55e] to-[#16a34a] p-6 rounded-t-3xl text-white">
      <div class="flex justify-between items-start">
        <div>
          <h3 id="cita_nombre" class="text-xl font-bold"></h3>
          <p id="cita_esp"    class="opacity-90 text-sm"></p>
          <p id="cita_precio" class="font-black text-lg mt-1"></p>
        </div>
        <button onclick="cerrarModalCita()" class="bg-white/20 hover:bg-white/30 rounded-full p-1">
          <span class="material-symbols-outlined text-white">close</span>
        </button>
      </div>
    </div>

    <div class="p-6 space-y-5">
      <!-- 1. Selector de fecha -->
      <div>
        <label class="block text-sm font-bold mb-2">1. Selecciona una fecha <span class="text-red-400">*</span></label>
        <input id="cita_fecha" type="date"
               class="w-full border rounded-2xl px-5 py-3 focus:border-[#22c55e] outline-none text-sm"
               onchange="cargarSlots()">
        <p class="text-xs text-gray-400 mt-1">Solo fechas futuras disponibles</p>
      </div>

      <!-- 2. Horarios disponibles (se llenan dinámicamente) -->
      <div id="bloqueHorarios" class="hidden">
        <label class="block text-sm font-bold mb-3">2. Horario disponible <span class="text-red-400">*</span></label>
        <div id="gridSlots" class="grid grid-cols-4 gap-2"></div>
        <p id="msgSlots" class="hidden text-sm text-gray-400 text-center py-3"></p>
      </div>

      <!-- Resumen de la selección -->
      <div id="resumenCita" class="hidden bg-green-50 border border-green-200 rounded-2xl p-4 text-sm">
        <p class="font-semibold text-[#22c55e]">✅ Cita seleccionada</p>
        <p id="resumenTexto" class="text-gray-600 mt-1"></p>
      </div>

      <div id="msgCita" class="hidden px-4 py-3 rounded-xl text-sm font-medium"></div>

      <div class="flex gap-3">
        <button onclick="cerrarModalCita()" class="flex-1 py-3 border rounded-2xl font-semibold text-sm hover:bg-gray-50">Cancelar</button>
        <button onclick="confirmarCita()" id="btnConfirmarCita"
                class="flex-1 py-3 bg-[#22c55e] text-white rounded-2xl font-bold text-sm hover:bg-[#16a34a] transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                disabled>
          Confirmar cita
        </button>
      </div>
    </div>
  </div>
</div>

<!-- MODAL: Reseña -->
<div id="modalResena" class="hidden fixed inset-0 bg-black/60 flex items-center justify-center z-50 p-4">
  <div class="bg-white rounded-3xl p-8 w-full max-w-md">
    <div class="flex justify-between items-center mb-4">
      <h3 class="text-xl font-bold">Dejar reseña</h3>
      <button onclick="cerrarResena()"><span class="material-symbols-outlined text-gray-400">close</span></button>
    </div>
    <p id="resenaNombre" class="text-[#22c55e] font-medium mb-4"></p>
    <div class="flex gap-1 mb-2" id="estrellas">
      <?php for($i=1;$i<=5;$i++): ?>
      <span class="star" onclick="setEstrella(<?=$i?>)" onmouseover="hoverEstrella(<?=$i?>)" onmouseout="unhoverEstrella()">★</span>
      <?php endfor; ?>
    </div>
    <p id="textoEstrellas" class="text-xs text-gray-400 mb-4">Selecciona una calificación</p>
    <textarea id="resenaComentario" rows="3" placeholder="¿Cómo fue tu experiencia? (opcional)"
              class="w-full border rounded-2xl px-4 py-3 focus:border-[#22c55e] outline-none resize-none text-sm"></textarea>
    <div id="msgResena" class="hidden mt-3 px-4 py-2 rounded-xl text-sm font-medium"></div>
    <div class="flex gap-3 mt-5">
      <button onclick="cerrarResena()" class="flex-1 py-3 border rounded-2xl font-semibold text-sm">Cancelar</button>
      <button onclick="enviarResena()" class="flex-1 py-3 bg-[#22c55e] text-white rounded-2xl font-bold text-sm">Publicar reseña</button>
    </div>
  </div>
</div>

<script>
// ──────────────────────────────────────────
//  Estado global
// ──────────────────────────────────────────
let todosLosNutri      = [];
let nutriSeleccionado  = null;
let horaSeleccionada   = null;
let calificacionActual = 0;
let modalidadFiltro    = '';
let debounceTimer      = null;

const TEXTOS_ESTRELLA = ['','Malo','Regular','Bueno','Muy bueno','Excelente'];

document.addEventListener('DOMContentLoaded', () => {
    // Fecha mínima = mañana
    const manana = new Date(); manana.setDate(manana.getDate() + 1);
    document.getElementById('cita_fecha').min = manana.toISOString().split('T')[0];
    cargarNutricionistas();
});

// ──────────────────────────────────────────
//  AJAX: cargar con filtros
// ──────────────────────────────────────────
async function cargarNutricionistas(params = {}) {
    const qs = new URLSearchParams();
    if (params.nombre)     qs.set('nombre',     params.nombre);
    if (params.precio_max) qs.set('precio_max', params.precio_max);
    if (params.rating_min) qs.set('rating_min', params.rating_min);
    if (params.modalidad)  qs.set('modalidad',  params.modalidad);

    const res  = await fetch('api/nutricionistas.php?' + qs.toString());
    const data = await res.json();
    todosLosNutri = Array.isArray(data) ? data : [];
    renderCards(todosLosNutri);
}

function filtrarConDebounce() { clearTimeout(debounceTimer); debounceTimer = setTimeout(filtrar, 350); }

function filtrar() {
    cargarNutricionistas({
        nombre:    document.getElementById('f_nombre').value.trim(),
        precio_max:document.getElementById('f_precio').value,
        rating_min:document.getElementById('f_rating').value,
        modalidad: modalidadFiltro
    });
}

function filtrarEspecialidad(esp) { document.getElementById('f_nombre').value = esp; filtrar(); }

function filtrarModalidad(mod) {
    modalidadFiltro = mod;
    document.querySelectorAll('.chip-mod').forEach(b => {
        b.className = 'chip-mod text-xs px-3 py-1 rounded-full border font-medium hover:bg-[#22c55e] hover:text-white hover:border-[#22c55e] transition-colors';
    });
    const activeId = mod === '' ? 'mTodas' : mod === 'Virtual' ? 'mVirtual' : 'mPresencial';
    const btn = document.getElementById(activeId);
    if (btn) { btn.className = 'chip-mod text-xs px-3 py-1 rounded-full border bg-[#22c55e] text-white border-[#22c55e] font-medium'; }
    filtrar();
}

function limpiarFiltros() {
    document.getElementById('f_nombre').value = '';
    document.getElementById('f_precio').value = '';
    document.getElementById('f_rating').value = '';
    modalidadFiltro = '';
    filtrarModalidad('');
}

// ──────────────────────────────────────────
//  Render cards con info profesional completa
// ──────────────────────────────────────────
function renderCards(lista) {
    const container = document.getElementById('cardsContainer');
    document.getElementById('contadorRes').textContent =
        lista.length > 0 ? `${lista.length} especialista${lista.length !== 1 ? 's' : ''} verificado${lista.length !== 1 ? 's' : ''} encontrado${lista.length !== 1 ? 's' : ''}` : '';

    if (lista.length === 0) {
        container.innerHTML = `
            <div class="col-span-3 text-center py-16">
                <span class="material-symbols-outlined text-5xl text-gray-300">search_off</span>
                <p class="text-gray-400 mt-3">No se encontraron especialistas con esos filtros.</p>
                <button onclick="limpiarFiltros()" class="mt-3 text-[#22c55e] text-sm font-medium hover:underline">Limpiar filtros</button>
            </div>`;
        return;
    }

    container.innerHTML = lista.map(n => {
        const estrellas = '★'.repeat(Math.round(n.rating || 5)) + '☆'.repeat(5 - Math.round(n.rating || 5));
        const modalBadge = n.modalidad === 'Ambas'
            ? '<span class="bg-purple-100 text-purple-700 px-2 py-0.5 rounded-full text-xs">Virtual y Presencial</span>'
            : n.modalidad === 'Virtual'
                ? '<span class="bg-blue-100 text-blue-700 px-2 py-0.5 rounded-full text-xs">Virtual</span>'
                : '<span class="bg-orange-100 text-orange-700 px-2 py-0.5 rounded-full text-xs">Presencial</span>';

        return `
        <div class="card bg-white rounded-3xl shadow-sm border overflow-hidden">
            <!-- Foto / Avatar -->
            <div class="bg-gradient-to-br from-green-50 to-emerald-100 p-5 flex items-center gap-4">
                <div class="w-16 h-16 bg-white rounded-2xl shadow-sm flex items-center justify-center text-3xl flex-shrink-0">
                    ${n.foto ? `<img src="${n.foto}" class="w-full h-full object-cover rounded-2xl">` : '🥑'}
                </div>
                <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-2 flex-wrap">
                        <h3 class="font-black text-lg leading-tight">${n.nombre}</h3>
                        <span title="Profesional verificado" class="text-[#22c55e]">
                            <span class="material-symbols-outlined text-base" style="font-variation-settings:'FILL' 1,'wght' 400">verified</span>
                        </span>
                    </div>
                    <p class="text-[#22c55e] font-semibold text-sm">${n.especialidad}</p>
                    <div class="flex items-center gap-1 mt-0.5">
                        <span class="text-amber-400 text-sm">${estrellas}</span>
                        <span class="text-xs text-gray-400">(${n.total_resenas || 0})</span>
                    </div>
                </div>
            </div>

            <!-- Detalles -->
            <div class="p-5">
                <div class="flex flex-wrap gap-2 mb-3">
                    ${modalBadge}
                    ${n.experiencia_años ? `<span class="bg-gray-100 text-gray-600 px-2 py-0.5 rounded-full text-xs">${n.experiencia_años} años exp.</span>` : ''}
                    ${n.pacientes_exit  ? `<span class="bg-gray-100 text-gray-600 px-2 py-0.5 rounded-full text-xs">+${n.pacientes_exit} pacientes</span>` : ''}
                </div>
                <div class="flex justify-between items-center mb-4">
                    <div>
                        <p class="text-xs text-gray-400">Consulta desde</p>
                        <p class="text-2xl font-black text-[#22c55e]">Bs. ${n.precio}</p>
                    </div>
                    <div class="text-right text-xs text-gray-400">
                        <p>${n.duracion_consulta || 60} min / consulta</p>
                    </div>
                </div>

                <!-- Botones principales -->
                <div class="flex gap-2">
                    <button onclick="abrirModalCita(${n.id})"
                            class="flex-1 py-3 bg-[#22c55e] text-white rounded-2xl font-bold text-sm hover:bg-[#16a34a] transition-colors">
                        Reservar cita
                    </button>
                    <button onclick="verDetalle(${n.id})"
                            class="py-3 px-4 border rounded-2xl text-sm font-medium hover:bg-gray-50 transition-colors" title="Ver información completa">
                        <span class="material-symbols-outlined text-xl text-gray-500">info</span>
                    </button>
                </div>
                <!-- Reseña -->
                <button onclick="abrirResenaPorId(${n.id})"
                        class="w-full mt-2 py-2 border rounded-xl text-xs font-medium text-gray-500 hover:bg-gray-50 transition-colors">
                    ★ Dejar reseña
                </button>
            </div>
        </div>`;
    }).join('');
}

// ──────────────────────────────────────────
//  Modal: Ver información completa
// ──────────────────────────────────────────
async function verDetalle(nutriId) {
    const res  = await fetch(`api/nutricionistas.php?id=${nutriId}`);
    const n    = await res.json();
    if (n.error) return;

    // Rellenar campos del modal
    document.getElementById('det_nombre').textContent   = n.nombre;
    document.getElementById('det_esp').textContent      = n.especialidad;
    document.getElementById('det_exp').textContent      = n.experiencia_años ? n.experiencia_años + ' años' : '—';
    document.getElementById('det_pac').textContent      = n.pacientes_exit ? '+' + n.pacientes_exit : '—';
    document.getElementById('det_rating').textContent   = n.rating || '5.0';
    document.getElementById('det_univ').textContent     = n.universidad || '—';
    document.getElementById('det_titulo').textContent   = n.titulo || '—';
    document.getElementById('det_modalidad').textContent= n.modalidad || '—';
    document.getElementById('det_idiomas').textContent  = n.idiomas || 'Español';
    document.getElementById('det_precio').textContent   = `Bs. ${n.precio}`;
    document.getElementById('det_duracion').textContent = `${n.duracion_consulta || 60} min`;

    if (n.biografia) {
        document.getElementById('det_bio').textContent = n.biografia;
        document.getElementById('det_bio_bloque').classList.remove('hidden');
    } else {
        document.getElementById('det_bio_bloque').classList.add('hidden');
    }

    // Sello verificado
    if (n.estado_verificacion === 'aprobado') {
        document.getElementById('det_sello').classList.remove('hidden');
    }

    // Reseñas
    const resRes   = await fetch(`api/resenas.php?nutricionista_id=${nutriId}`);
    const resenas  = await resRes.json();
    const listaRes = document.getElementById('det_resenas');
    if (Array.isArray(resenas) && resenas.length > 0) {
        listaRes.innerHTML = resenas.map(r => `
            <div class="bg-gray-50 rounded-2xl p-3">
                <div class="flex justify-between items-center mb-1">
                    <span class="font-semibold text-sm">${r.paciente}</span>
                    <span class="text-amber-400 text-sm">${'★'.repeat(r.calificacion)}${'☆'.repeat(5-r.calificacion)}</span>
                </div>
                ${r.comentario ? `<p class="text-gray-600 text-xs">${r.comentario}</p>` : ''}
            </div>`).join('');
    } else {
        listaRes.innerHTML = '<p class="text-gray-400 text-sm text-center py-3">Aún no hay reseñas</p>';
    }

    // Botón reservar conectado
    document.getElementById('det_btn_reservar').onclick = () => {
        cerrarDetalle();
        abrirModalCita(nutriId);
    };

    document.getElementById('modalDetalle').classList.remove('hidden');
}

function cerrarDetalle() { document.getElementById('modalDetalle').classList.add('hidden'); }

// ──────────────────────────────────────────
//  Modal: Agendar cita con CALENDARIO INTELIGENTE
// ──────────────────────────────────────────
async function abrirModalCita(nutriId) {
    // Buscar datos del nutricionista en cache local
    nutriSeleccionado = todosLosNutri.find(n => n.id === nutriId);

    // Si no está en cache (venimos del modal de detalle), pedirlo a la API
    if (!nutriSeleccionado) {
        const res = await fetch(`api/nutricionistas.php?id=${nutriId}`);
        nutriSeleccionado = await res.json();
    }

    document.getElementById('cita_nombre').textContent = nutriSeleccionado.nombre;
    document.getElementById('cita_esp').textContent    = nutriSeleccionado.especialidad;
    document.getElementById('cita_precio').textContent = `Bs. ${nutriSeleccionado.precio} / consulta`;

    // Reset estado
    horaSeleccionada = null;
    document.getElementById('cita_fecha').value = '';
    document.getElementById('bloqueHorarios').classList.add('hidden');
    document.getElementById('resumenCita').classList.add('hidden');
    document.getElementById('btnConfirmarCita').disabled = true;
    document.getElementById('msgCita').classList.add('hidden');

    document.getElementById('modalCita').classList.remove('hidden');
}

function cerrarModalCita() { document.getElementById('modalCita').classList.add('hidden'); nutriSeleccionado = null; }

// Cargar slots disponibles cuando el paciente elige una fecha
async function cargarSlots() {
    const fecha = document.getElementById('cita_fecha').value;
    if (!fecha || !nutriSeleccionado) return;

    const gridSlots   = document.getElementById('gridSlots');
    const msgSlots    = document.getElementById('msgSlots');
    const bloqueHoras = document.getElementById('bloqueHorarios');

    bloqueHoras.classList.remove('hidden');
    gridSlots.innerHTML = '<p class="col-span-4 text-center text-gray-400 text-sm py-3">Cargando horarios...</p>';
    msgSlots.classList.add('hidden');
    horaSeleccionada = null;
    document.getElementById('btnConfirmarCita').disabled = true;
    document.getElementById('resumenCita').classList.add('hidden');

    // AJAX: pedir horarios libres a la API
    const res  = await fetch(`api/postulaciones.php?accion=disponibilidad&nutri_id=${nutriSeleccionado.id}&fecha=${fecha}`);
    const data = await res.json();

    if (data.error || !data.slots) {
        gridSlots.innerHTML = '';
        msgSlots.textContent = data.error || 'No se encontraron horarios.';
        msgSlots.classList.remove('hidden');
        return;
    }

    if (data.slots.length === 0) {
        gridSlots.innerHTML = '';
        msgSlots.textContent = '😔 No hay horarios disponibles para esta fecha. Prueba otro día.';
        msgSlots.classList.remove('hidden');
        return;
    }

    // Renderizar slots como botones clicables
    gridSlots.innerHTML = data.slots.map(hora => `
        <button class="slot border rounded-xl py-2 text-sm font-medium text-center hover:bg-[#22c55e] hover:text-white hover:border-[#22c55e] transition-all"
                onclick="seleccionarSlot('${hora}', this)">
            ${hora}
        </button>
    `).join('');
}

function seleccionarSlot(hora, btn) {
    // Desmarcar slot anterior
    document.querySelectorAll('.slot.seleccionado').forEach(s => s.classList.remove('seleccionado'));
    btn.classList.add('seleccionado');
    horaSeleccionada = hora;

    // Mostrar resumen
    const fecha = document.getElementById('cita_fecha').value;
    const [y,m,d] = fecha.split('-');
    document.getElementById('resumenTexto').textContent =
        `${d}/${m}/${y} a las ${hora} con ${nutriSeleccionado.nombre} — Bs. ${nutriSeleccionado.precio}`;
    document.getElementById('resumenCita').classList.remove('hidden');
    document.getElementById('btnConfirmarCita').disabled = false;
}

async function confirmarCita() {
    const fecha = document.getElementById('cita_fecha').value;
    if (!fecha || !horaSeleccionada) return mostrarMsgCita('Selecciona una fecha y horario');

    const btn = document.getElementById('btnConfirmarCita');
    btn.disabled = true; btn.textContent = 'Guardando...';

    const res  = await fetch('api/citas.php', {
        method: 'POST', headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ nutricionista_id: nutriSeleccionado.id, fecha, hora: horaSeleccionada })
    });
    const data = await res.json();

    btn.disabled = false; btn.textContent = 'Confirmar cita';

    if (data.ok) {
        mostrarMsgCita('✅ ' + data.mensaje, 'ok');
        setTimeout(cerrarModalCita, 2000);
    } else {
        mostrarMsgCita(data.error || 'Error al agendar');
        // Recargar slots por si ese horario acaba de ser tomado por otro usuario
        cargarSlots();
    }
}

function mostrarMsgCita(txt, tipo = 'error') {
    const el = document.getElementById('msgCita');
    el.textContent = txt;
    el.className = `px-4 py-3 rounded-xl text-sm font-medium ${tipo === 'ok' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'}`;
    el.classList.remove('hidden');
}

// ──────────────────────────────────────────
//  Modal Reseña
// ──────────────────────────────────────────
function abrirResena(nutriId, nombre) {
    nutriSeleccionado = { ...nutriSeleccionado, id: nutriId, nombre };
    calificacionActual = 0;
    actualizarEstrellas(0);
    document.getElementById('resenaNombre').textContent   = nombre;
    document.getElementById('resenaComentario').value     = '';
    document.getElementById('textoEstrellas').textContent = 'Selecciona una calificación';
    document.getElementById('msgResena').classList.add('hidden');
    document.getElementById('modalResena').classList.remove('hidden');
}
function abrirResenaPorId(id) {
    const n = todosLosNutri.find(nutri => nutri.id === id);
    if (n) abrirResena(n.id, n.nombre);
}
function cerrarResena() { document.getElementById('modalResena').classList.add('hidden'); }

function setEstrella(n) {
    calificacionActual = n;
    actualizarEstrellas(n);
    document.getElementById('textoEstrellas').textContent = TEXTOS_ESTRELLA[n];
}
function hoverEstrella(n)  { actualizarEstrellas(n, true); }
function unhoverEstrella() { actualizarEstrellas(calificacionActual); }
function actualizarEstrellas(n, esHover = false) {
    document.querySelectorAll('#estrellas .star').forEach((s, i) => {
        s.classList.remove('activo','hover');
        if (i < n) s.classList.add(esHover ? 'hover' : 'activo');
    });
}

async function enviarResena() {
    if (!calificacionActual) return mostrarMsg('msgResena','Selecciona una calificación');
    const res  = await fetch('api/resenas.php', {
        method: 'POST', headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ nutricionista_id: nutriSeleccionado.id,
                               calificacion: calificacionActual,
                               comentario: document.getElementById('resenaComentario').value })
    });
    const data = await res.json();
    if (data.ok) { mostrarMsg('msgResena','✅ Reseña publicada','ok'); setTimeout(cerrarResena, 1500); filtrar(); }
    else mostrarMsg('msgResena', data.error || 'Error');
}

// ──────────────────────────────────────────
//  Helpers
// ──────────────────────────────────────────
function mostrarMsg(elId, txt, tipo = 'error') {
    const el = document.getElementById(elId);
    el.textContent = txt;
    el.className = `mt-3 px-4 py-2 rounded-xl text-sm font-medium ${tipo === 'ok' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'}`;
    el.classList.remove('hidden');
}

async function logout() {
    if (!confirm('¿Cerrar sesión?')) return;
    await fetch('api/auth.php?accion=logout', { method: 'POST' });
    window.location.href = 'login.php';
}

['modalCita','modalDetalle','modalResena'].forEach(id => {
    document.getElementById(id).addEventListener('click', e => {
        if (e.target.id === id) document.getElementById(id).classList.add('hidden');
    });
});
</script>
</body>
</html>

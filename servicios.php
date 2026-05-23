<?php
// ============================================================
//  servicios.php  —  Sprint 2: Gestión de Servicios
//  Nutricionista: crea, edita y elimina sus servicios
//  Paciente: ve los servicios aprobados disponibles
// ============================================================
session_start();
if (empty($_SESSION['usuario'])) { header('Location: login.php'); exit; }

$usuario = $_SESSION['usuario'];
$rol     = $usuario['rol'];
$nombre  = $usuario['nombre'];
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>NutriSucre - <?= $rol === 'Nutricionista' ? 'Mis Servicios' : 'Servicios Disponibles' ?></title>
<script src="https://cdn.tailwindcss.com"></script>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet">
<style>
  body { font-family:'Inter',sans-serif; background:#f8fafb; }
  .material-symbols-outlined { font-variation-settings:'FILL' 0,'wght' 300; }
  /* Badge de estado */
  .badge-Pendiente  { background:#fef3c7; color:#d97706; }
  .badge-Aprobado   { background:#dcfce7; color:#16a34a; }
  .badge-Rechazado  { background:#fee2e2; color:#dc2626; }
  /* Card hover */
  .card-servicio { transition:transform .25s, box-shadow .25s; }
  .card-servicio:hover { transform:translateY(-4px); box-shadow:0 12px 20px -5px rgba(0,0,0,.1); }
</style>
</head>
<body>

<?php $paginaActual = 'servicios'; require_once '_sidebar.php'; ?>

<!-- HEADER -->
<header class="flex justify-between items-center px-6 py-4 bg-white/80 backdrop-blur-xl border-b md:pl-72 sticky top-0 z-50">
  <div class="flex items-center gap-3">
    <button onclick="document.getElementById('sidebar').classList.toggle('hidden');document.getElementById('sidebar').classList.toggle('flex')" class="md:hidden">
      <span class="material-symbols-outlined">menu</span>
    </button>
    <h1 class="text-2xl font-bold">
      <?= $rol === 'Nutricionista' ? 'Mis Servicios' : ($rol === 'Administrador' ? 'Gestión de Servicios' : 'Servicios Disponibles') ?>
    </h1>
  </div>
  <div class="flex items-center gap-3">
    <?php if ($rol === 'Nutricionista'): ?>
    <button onclick="abrirModalCrear()"
            class="bg-[#22c55e] text-white px-5 py-2.5 rounded-2xl font-semibold flex items-center gap-2 hover:bg-[#16a34a] transition-colors text-sm">
      <span class="material-symbols-outlined text-xl">add</span>Nuevo servicio
    </button>
    <?php endif; ?>
    <div class="text-right hidden sm:block">
      <div class="font-semibold text-sm"><?= htmlspecialchars($nombre) ?></div>
      <div class="text-xs text-[#22c55e]"><?= htmlspecialchars($rol) ?></div>
    </div>
  </div>
</header>

<main class="md:pl-64 p-6 max-w-7xl mx-auto">

  <!-- Banner informativo para nutricionista -->
  <?php if ($rol === 'Nutricionista'): ?>
  <div class="bg-blue-50 border border-blue-200 rounded-2xl p-4 mb-6 flex gap-3 items-start">
    <span class="material-symbols-outlined text-blue-500 text-2xl flex-shrink-0 mt-0.5">info</span>
    <div class="text-sm text-blue-700">
      <strong>Flujo de publicación:</strong> Al crear o editar un servicio, quedará en estado
      <span class="font-bold">Pendiente</span> hasta que el administrador lo revise y apruebe.
      Solo los servicios <span class="font-bold">Aprobados</span> son visibles para los pacientes.
    </div>
  </div>
  <?php endif; ?>

  <!-- Filtros (para admin y paciente) -->
  <?php if ($rol !== 'Nutricionista'): ?>
  <div class="bg-white rounded-2xl p-4 shadow-sm border mb-6 flex items-center gap-4 flex-wrap">
    <?php if ($rol === 'Administrador'): ?>
    <select id="filtroEstado" onchange="cargarServicios()" class="border rounded-xl px-4 py-2 text-sm focus:border-[#22c55e] outline-none">
      <option value="">Todos los estados</option>
      <option value="Pendiente">⏳ Pendiente</option>
      <option value="Aprobado">✅ Aprobado</option>
      <option value="Rechazado">❌ Rechazado</option>
    </select>
    <?php endif; ?>
    <select id="filtroCategoria" onchange="cargarServicios()" class="border rounded-xl px-4 py-2 text-sm focus:border-[#22c55e] outline-none">
      <option value="">Todas las categorías</option>
      <?php foreach(['Pérdida de peso','Ganancia muscular','Control de diabetes','Nutrición deportiva','Nutrición infantil','Nutrición clínica','Nutrición geriátrica','Trastornos alimenticios','Embarazo y lactancia','Otro'] as $cat): ?>
      <option><?= $cat ?></option>
      <?php endforeach; ?>
    </select>
    <select id="filtroModalidad" onchange="cargarServicios()" class="border rounded-xl px-4 py-2 text-sm focus:border-[#22c55e] outline-none">
      <option value="">Cualquier modalidad</option>
      <option>Virtual</option><option>Presencial</option><option>Ambas</option>
    </select>
  </div>
  <?php endif; ?>

  <!-- Feedback global -->
  <div id="feedback" class="hidden mb-5 px-5 py-4 rounded-2xl text-sm font-medium text-center"></div>

  <!-- Stats (solo nutricionista) -->
  <?php if ($rol === 'Nutricionista'): ?>
  <div id="statsNutri" class="grid grid-cols-3 gap-4 mb-6"></div>
  <?php endif; ?>

  <!-- Grid de cards -->
  <div id="containerServicios" class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
    <div class="col-span-3 text-center py-12 text-gray-400">Cargando servicios...</div>
  </div>
</main>

<!-- ═══════════ MODAL: Crear / Editar servicio (HU-01 / HU-02) ═══════════ -->
<div id="modalServicio" class="hidden fixed inset-0 bg-black/60 flex items-center justify-center z-50 p-4">
  <div class="bg-white rounded-3xl w-full max-w-2xl max-h-[92vh] overflow-y-auto">
    <div class="bg-gradient-to-r from-[#22c55e] to-[#16a34a] p-6 rounded-t-3xl text-white flex justify-between items-center">
      <div>
        <h3 id="modalServicioTitulo" class="text-xl font-black">Nuevo Servicio</h3>
        <p class="text-white/80 text-sm mt-1">El servicio quedará en estado <strong>Pendiente</strong> hasta ser aprobado</p>
      </div>
      <button onclick="cerrarModalServicio()">
        <span class="material-symbols-outlined text-white">close</span>
      </button>
    </div>

    <div class="p-6 space-y-4">
      <input type="hidden" id="srv_id">

      <div>
        <label class="block text-sm font-semibold mb-1">Título del servicio <span class="text-red-400">*</span></label>
        <input id="srv_titulo" type="text" placeholder="ej: Plan de Control de Peso Intensivo"
               class="w-full border rounded-2xl px-4 py-3 focus:border-[#22c55e] outline-none text-sm">
      </div>

      <div>
        <label class="block text-sm font-semibold mb-1">Descripción completa <span class="text-red-400">*</span></label>
        <textarea id="srv_desc" rows="3" placeholder="Describe en qué consiste el servicio, a quién va dirigido y qué resultados puede esperar..."
                  class="w-full border rounded-2xl px-4 py-3 focus:border-[#22c55e] outline-none resize-none text-sm"></textarea>
      </div>

      <div class="grid grid-cols-2 gap-4">
        <div>
          <label class="block text-sm font-semibold mb-1">Categoría <span class="text-red-400">*</span></label>
          <select id="srv_categoria" class="w-full border rounded-2xl px-4 py-3 focus:border-[#22c55e] outline-none text-sm">
            <?php foreach(['Pérdida de peso','Ganancia muscular','Control de diabetes','Nutrición deportiva','Nutrición infantil','Nutrición clínica','Nutrición geriátrica','Trastornos alimenticios','Embarazo y lactancia','Otro'] as $cat): ?>
            <option><?= $cat ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div>
          <label class="block text-sm font-semibold mb-1">Modalidad <span class="text-red-400">*</span></label>
          <select id="srv_modalidad" class="w-full border rounded-2xl px-4 py-3 focus:border-[#22c55e] outline-none text-sm">
            <option>Virtual</option><option>Presencial</option><option>Ambas</option>
          </select>
        </div>
        <div>
          <label class="block text-sm font-semibold mb-1">Precio (Bs.) <span class="text-red-400">*</span></label>
          <input id="srv_precio" type="number" min="1" step="0.01" placeholder="ej: 350"
                 class="w-full border rounded-2xl px-4 py-3 focus:border-[#22c55e] outline-none text-sm">
        </div>
        <div>
          <label class="block text-sm font-semibold mb-1">Duración (semanas) <span class="text-red-400">*</span></label>
          <input id="srv_duracion" type="number" min="1" max="52" value="4"
                 class="w-full border rounded-2xl px-4 py-3 focus:border-[#22c55e] outline-none text-sm">
        </div>
      </div>

      <div>
        <label class="block text-sm font-semibold mb-1">¿Qué incluye el servicio?</label>
        <textarea id="srv_incluye" rows="2" placeholder="ej: 4 consultas virtuales, plan personalizado en PDF, seguimiento semanal..."
                  class="w-full border rounded-2xl px-4 py-3 focus:border-[#22c55e] outline-none resize-none text-sm"></textarea>
      </div>

      <!-- Aviso edición -->
      <div id="avisoEdicion" class="hidden bg-amber-50 border border-amber-200 rounded-xl p-3 text-xs text-amber-700">
        <strong>⚠ Importante:</strong> Al guardar los cambios, el servicio volverá a estado
        <strong>Pendiente</strong> y deberá ser aprobado nuevamente por el administrador.
      </div>

      <div id="msgModal" class="hidden px-4 py-3 rounded-xl text-sm font-medium"></div>

      <div class="flex gap-3 pt-2">
        <button onclick="cerrarModalServicio()" class="flex-1 py-3 border rounded-2xl font-semibold text-sm hover:bg-gray-50">Cancelar</button>
        <button onclick="guardarServicio()" id="btnGuardarSrv"
                class="flex-1 py-3 bg-[#22c55e] text-white rounded-2xl font-bold text-sm hover:bg-[#16a34a] transition-colors">
          Guardar servicio
        </button>
      </div>
    </div>
  </div>
</div>

<!-- ═══════════ MODAL: Validar (HU-03, solo Admin) ═══════════ -->
<div id="modalValidar" class="hidden fixed inset-0 bg-black/60 flex items-center justify-center z-50 p-4">
  <div class="bg-white rounded-3xl w-full max-w-md p-8">
    <div class="flex justify-between items-center mb-5">
      <h3 class="text-xl font-bold">Validar servicio</h3>
      <button onclick="cerrarModalValidar()"><span class="material-symbols-outlined text-gray-400">close</span></button>
    </div>
    <input type="hidden" id="val_id">
    <div id="val_resumen" class="bg-gray-50 rounded-2xl p-4 mb-4 text-sm space-y-1"></div>

    <div id="bloqueMotivo" class="hidden mb-4">
      <label class="block text-sm font-semibold mb-2">Motivo del rechazo <span class="text-red-400">*</span></label>
      <textarea id="val_motivo" rows="3" placeholder="Explica al nutricionista qué debe mejorar..."
                class="w-full border rounded-2xl px-4 py-3 focus:border-[#22c55e] outline-none resize-none text-sm"></textarea>
    </div>

    <div id="msgValidar" class="hidden mb-4 px-4 py-3 rounded-xl text-sm font-medium"></div>

    <div class="flex gap-3">
      <button onclick="cerrarModalValidar()" class="flex-1 py-3 border rounded-2xl font-semibold text-sm">Cancelar</button>
      <button onclick="ejecutarValidacion('Rechazado')"
              class="flex-1 py-3 bg-red-500 text-white rounded-2xl font-bold text-sm hover:bg-red-600">
        ❌ Rechazar
      </button>
      <button onclick="ejecutarValidacion('Aprobado')"
              class="flex-1 py-3 bg-[#22c55e] text-white rounded-2xl font-bold text-sm hover:bg-[#16a34a]">
        ✅ Aprobar
      </button>
    </div>
  </div>
</div>

<!-- ═══════════ MODAL: Confirmar eliminación ═══════════ -->
<div id="modalEliminar" class="hidden fixed inset-0 bg-black/60 flex items-center justify-center z-50 p-4">
  <div class="bg-white rounded-3xl p-8 w-full max-w-sm text-center">
    <span class="material-symbols-outlined text-red-400 text-5xl">delete</span>
    <h3 class="text-xl font-bold mt-3 mb-2">¿Eliminar servicio?</h3>
    <p id="textoEliminar" class="text-gray-500 text-sm mb-6"></p>
    <div class="flex gap-3">
      <button onclick="cerrarModalEliminar()" class="flex-1 py-3 border rounded-2xl font-semibold">Cancelar</button>
      <button onclick="ejecutarEliminar()" class="flex-1 py-3 bg-red-500 text-white rounded-2xl font-bold">Eliminar</button>
    </div>
  </div>
</div>

<script>
// ──────────────────────────────────────────────
//  Estado global
// ──────────────────────────────────────────────
const ROL = '<?= $rol ?>';
let idParaEliminar = null;
let todosLosServicios = [];

document.addEventListener('DOMContentLoaded', cargarServicios);

// ──────────────────────────────────────────────
//  AJAX: cargar servicios según rol y filtros
// ──────────────────────────────────────────────
async function cargarServicios() {
    const container = document.getElementById('containerServicios');
    container.innerHTML = '<div class="col-span-3 text-center py-8 text-gray-400">Cargando...</div>';

    let url = 'api/servicios.php';
    const params = new URLSearchParams();

    if (ROL === 'Administrador') {
        const estado = document.getElementById('filtroEstado')?.value || '';
        if (estado) params.set('estado', estado);
    } else if (ROL === 'Paciente') {
        params.set('publico', '1');
        const cat = document.getElementById('filtroCategoria')?.value || '';
        const mod = document.getElementById('filtroModalidad')?.value || '';
        if (cat) params.set('categoria', cat);
        if (mod) params.set('modalidad', mod);
    }

    if (params.toString()) url += '?' + params.toString();

    const res  = await fetch(url);
    const data = await res.json();

    if (!Array.isArray(data)) {
        container.innerHTML = `<div class="col-span-3 text-center py-8 text-red-400">${data.error || 'Error al cargar'}</div>`;
        return;
    }

    todosLosServicios = data;

    // Stats para nutricionista
    if (ROL === 'Nutricionista') {
        const pending  = todosLosServicios.filter(s => s.estado === 'Pendiente').length;
        const approved = todosLosServicios.filter(s => s.estado === 'Aprobado').length;
        const rejected = todosLosServicios.filter(s => s.estado === 'Rechazado').length;
        document.getElementById('statsNutri').innerHTML = [
            { lbl:'Total servicios', val:todosLosServicios.length,  color:'text-gray-800' },
            { lbl:'Aprobados',       val:approved,     color:'text-[#22c55e]' },
            { lbl:'Pendientes',      val:pending,      color:'text-amber-600' },
        ].map(s => `
            <div class="bg-white rounded-2xl p-4 shadow-sm border text-center">
                <p class="text-3xl font-black ${s.color}">${s.val}</p>
                <p class="text-xs text-gray-500 mt-1">${s.lbl}</p>
            </div>`).join('');
    }

    renderServicios(todosLosServicios);
}

// ──────────────────────────────────────────────
//  Render cards de servicios
// ──────────────────────────────────────────────
function renderServicios(lista) {
    const container = document.getElementById('containerServicios');

    if (lista.length === 0) {
        const msg = ROL === 'Nutricionista'
            ? 'Aún no tienes servicios registrados. ¡Crea tu primer servicio!'
            : 'No hay servicios disponibles con los filtros seleccionados.';
        container.innerHTML = `
            <div class="col-span-3 text-center py-16 bg-white rounded-3xl border">
                <span class="material-symbols-outlined text-5xl text-gray-300">medical_services</span>
                <p class="text-gray-400 mt-3 text-sm">${msg}</p>
                ${ROL === 'Nutricionista' ? '<button onclick="abrirModalCrear()" class="mt-4 bg-[#22c55e] text-white px-6 py-2.5 rounded-xl text-sm font-semibold hover:bg-[#16a34a]">+ Crear servicio</button>' : ''}
            </div>`;
        return;
    }

    container.innerHTML = lista.map(s => tarjetaServicio(s)).join('');
}

function tarjetaServicio(s) {
    const esNutri = ROL === 'Nutricionista';
    const esAdmin = ROL === 'Administrador';

    // Badge de estado con color según valor
    const badgeHTML = `<span class="badge-${s.estado} px-3 py-1 rounded-full text-xs font-semibold">${s.estado}</span>`;

    // Icono por categoría
    const iconosCat = {
        'Pérdida de peso': '⚖️', 'Ganancia muscular': '💪',
        'Control de diabetes': '🩸', 'Nutrición deportiva': '🏃',
        'Nutrición infantil': '👶', 'Nutrición clínica': '🏥',
        'Embarazo y lactancia': '🤰', 'Trastornos alimenticios': '🍃',
        'Otro': '🥗'
    };
    const icono = iconosCat[s.categoria] || '🥗';

    return `
    <div class="card-servicio bg-white rounded-3xl shadow-sm border overflow-hidden">
        <!-- Header de la card -->
        <div class="bg-gradient-to-br from-gray-50 to-gray-100 p-5 border-b">
            <div class="flex justify-between items-start">
                <span class="text-3xl">${icono}</span>
                ${badgeHTML}
            </div>
            <h3 class="font-black text-base mt-3 leading-tight">${s.titulo}</h3>
            <p class="text-[#22c55e] text-xs font-semibold mt-1">${s.categoria}</p>
            ${(esAdmin) ? `<p class="text-gray-400 text-xs mt-1">Por: ${s.nutricionista_nombre}</p>` : ''}
        </div>

        <!-- Cuerpo -->
        <div class="p-5">
            <p class="text-gray-600 text-xs leading-relaxed mb-4 line-clamp-2">${s.descripcion || ''}</p>

            <div class="flex flex-wrap gap-2 mb-4">
                <span class="bg-gray-100 text-gray-600 px-2 py-0.5 rounded-full text-xs">${s.modalidad}</span>
                <span class="bg-gray-100 text-gray-600 px-2 py-0.5 rounded-full text-xs">${s.duracion_semanas} semanas</span>
            </div>

            <div class="flex justify-between items-center mb-4">
                <div>
                    <p class="text-2xl font-black text-[#22c55e]">Bs. ${parseFloat(s.precio).toFixed(2)}</p>
                    <p class="text-xs text-gray-400">precio del servicio</p>
                </div>
            </div>

            ${s.incluye ? `
            <div class="bg-green-50 rounded-xl p-3 mb-4">
                <p class="text-xs font-semibold text-[#22c55e] mb-1">Incluye:</p>
                <p class="text-xs text-gray-600">${s.incluye}</p>
            </div>` : ''}

            ${s.estado === 'Rechazado' && s.motivo_rechazo ? `
            <div class="bg-red-50 border border-red-200 rounded-xl p-3 mb-4">
                <p class="text-xs font-semibold text-red-600 mb-1">Motivo de rechazo:</p>
                <p class="text-xs text-red-700">${s.motivo_rechazo}</p>
            </div>` : ''}

            <!-- Botones según rol -->
            <div class="flex gap-2 flex-wrap">
                ${esNutri ? `
                    <button onclick="abrirModalEditarPorId(${s.id})"
                            class="flex-1 py-2 border rounded-xl text-xs font-semibold hover:bg-gray-50 flex items-center justify-center gap-1">
                        <span class="material-symbols-outlined text-base">edit</span>Editar
                    </button>
                    <button onclick="pedirEliminarPorId(${s.id})"
                            class="py-2 px-3 border border-red-200 text-red-500 rounded-xl text-xs font-semibold hover:bg-red-50">
                        <span class="material-symbols-outlined text-base">delete</span>
                    </button>` : ''}
                ${esAdmin ? `
                    <button onclick="abrirModalValidarPorId(${s.id})"
                            class="flex-1 py-2 bg-[#22c55e] text-white rounded-xl text-xs font-bold hover:bg-[#16a34a] flex items-center justify-center gap-1">
                        <span class="material-symbols-outlined text-base">rate_review</span>Revisar
                    </button>
                    <button onclick="pedirEliminarPorId(${s.id})"
                            class="py-2 px-3 border border-red-200 text-red-500 rounded-xl text-xs font-semibold hover:bg-red-50">
                        <span class="material-symbols-outlined text-base">delete</span>
                    </button>` : ''}
                ${ROL === 'Paciente' ? `
                    <a href="buscar.php" class="flex-1 py-2 bg-[#22c55e] text-white rounded-xl text-xs font-bold hover:bg-[#16a34a] text-center">
                        Ver nutricionista →
                    </a>` : ''}
            </div>
        </div>
    </div>`;
}

// ──────────────────────────────────────────────
//  HU-01: Modal Crear
// ──────────────────────────────────────────────
function abrirModalCrear() {
    document.getElementById('srv_id').value      = '';
    document.getElementById('srv_titulo').value  = '';
    document.getElementById('srv_desc').value    = '';
    document.getElementById('srv_precio').value  = '';
    document.getElementById('srv_duracion').value= '4';
    document.getElementById('srv_incluye').value = '';
    document.getElementById('srv_categoria').value = 'Pérdida de peso';
    document.getElementById('srv_modalidad').value = 'Virtual';
    document.getElementById('modalServicioTitulo').textContent = 'Nuevo Servicio';
    document.getElementById('avisoEdicion').classList.add('hidden');
    document.getElementById('msgModal').classList.add('hidden');
    document.getElementById('modalServicio').classList.remove('hidden');
}

// ──────────────────────────────────────────────
//  HU-02: Modal Editar
// ──────────────────────────────────────────────
function abrirModalEditar(s) {
    document.getElementById('srv_id').value        = s.id;
    document.getElementById('srv_titulo').value    = s.titulo;
    document.getElementById('srv_desc').value      = s.descripcion || '';
    document.getElementById('srv_precio').value    = s.precio;
    document.getElementById('srv_duracion').value  = s.duracion_semanas;
    document.getElementById('srv_incluye').value   = s.incluye || '';
    document.getElementById('srv_categoria').value = s.categoria;
    document.getElementById('srv_modalidad').value = s.modalidad;
    document.getElementById('modalServicioTitulo').textContent = 'Editar Servicio';
    // Mostrar aviso de que vuelve a Pendiente
    document.getElementById('avisoEdicion').classList.remove('hidden');
    document.getElementById('msgModal').classList.add('hidden');
    document.getElementById('modalServicio').classList.remove('hidden');
}

function cerrarModalServicio() { document.getElementById('modalServicio').classList.add('hidden'); }

async function guardarServicio() {
    const id       = document.getElementById('srv_id').value;
    const titulo   = document.getElementById('srv_titulo').value.trim();
    const desc     = document.getElementById('srv_desc').value.trim();
    const precio   = document.getElementById('srv_precio').value;
    const duracion = document.getElementById('srv_duracion').value;
    const incluye  = document.getElementById('srv_incluye').value.trim();
    const categoria= document.getElementById('srv_categoria').value;
    const modalidad= document.getElementById('srv_modalidad').value;

    if (!titulo) return mostrarMsgModal('El título es obligatorio');
    if (!desc)   return mostrarMsgModal('La descripción es obligatoria');
    if (!precio || parseFloat(precio) <= 0) return mostrarMsgModal('El precio debe ser mayor a 0');

    const btn = document.getElementById('btnGuardarSrv');
    btn.disabled = true; btn.textContent = 'Guardando...';

    const body = { titulo, descripcion: desc, precio, duracion_semanas: duracion,
                   categoria, modalidad, incluye };
    if (id) body.id = parseInt(id);

    const res  = await fetch('api/servicios.php', {
        method:  id ? 'PUT' : 'POST',
        headers: { 'Content-Type': 'application/json' },
        body:    JSON.stringify(body)
    });
    const data = await res.json();

    btn.disabled = false; btn.textContent = 'Guardar servicio';

    if (data.ok) {
        cerrarModalServicio();
        mostrarFeedback(`✅ ${data.mensaje}`, 'ok');
        await cargarServicios();
    } else {
        mostrarMsgModal(data.error || 'Error al guardar');
    }
}

// ──────────────────────────────────────────────
//  HU-03: Modal Validar (Admin)
// ──────────────────────────────────────────────
function abrirModalValidar(s) {
    document.getElementById('val_id').value = s.id;
    document.getElementById('val_motivo').value = '';
    document.getElementById('bloqueMotivo').classList.add('hidden');
    document.getElementById('msgValidar').classList.add('hidden');
    document.getElementById('val_resumen').innerHTML = `
        <p class="font-bold text-sm">${s.titulo}</p>
        <p class="text-gray-500">Por: ${s.nutricionista_nombre}</p>
        <p class="text-gray-500">Categoría: ${s.categoria} · Bs. ${parseFloat(s.precio).toFixed(2)}</p>
        <p class="text-gray-500">Modalidad: ${s.modalidad} · ${s.duracion_semanas} semanas</p>
        <p class="text-gray-600 mt-2 text-xs">${s.descripcion || ''}</p>
    `;
    document.getElementById('modalValidar').classList.remove('hidden');
}
function cerrarModalValidar() { document.getElementById('modalValidar').classList.add('hidden'); }

async function ejecutarValidacion(estado) {
    const id     = document.getElementById('val_id').value;
    const motivo = document.getElementById('val_motivo').value.trim();

    // Si es rechazo, mostrar campo de motivo primero
    if (estado === 'Rechazado') {
        const bloque = document.getElementById('bloqueMotivo');
        bloque.classList.remove('hidden');
        if (!motivo) {
            document.getElementById('msgValidar').textContent = 'Debes indicar el motivo del rechazo';
            document.getElementById('msgValidar').className = 'mb-4 px-4 py-3 rounded-xl text-sm font-medium bg-red-100 text-red-700';
            document.getElementById('msgValidar').classList.remove('hidden');
            return;
        }
    }

    const res  = await fetch('api/servicios.php?accion=validar', {
        method:  'PUT',
        headers: { 'Content-Type': 'application/json' },
        body:    JSON.stringify({ id: parseInt(id), estado, motivo })
    });
    const data = await res.json();

    if (data.ok) {
        cerrarModalValidar();
        mostrarFeedback(`✅ ${data.mensaje}`, 'ok');
        await cargarServicios();
    } else {
        document.getElementById('msgValidar').textContent = data.error;
        document.getElementById('msgValidar').className = 'mb-4 px-4 py-3 rounded-xl text-sm font-medium bg-red-100 text-red-700';
        document.getElementById('msgValidar').classList.remove('hidden');
    }
}

// ──────────────────────────────────────────────
//  Eliminar
// ──────────────────────────────────────────────
function pedirEliminar(id, titulo) {
    idParaEliminar = id;
    document.getElementById('textoEliminar').textContent = `Se eliminará "${titulo}" permanentemente.`;
    document.getElementById('modalEliminar').classList.remove('hidden');
}
function cerrarModalEliminar() {
    document.getElementById('modalEliminar').classList.add('hidden');
    idParaEliminar = null;
}
async function ejecutarEliminar() {
    const res  = await fetch('api/servicios.php', {
        method:  'DELETE',
        headers: { 'Content-Type': 'application/json' },
        body:    JSON.stringify({ id: idParaEliminar })
    });
    const data = await res.json();
    cerrarModalEliminar();
    if (data.ok) { mostrarFeedback('✅ ' + data.mensaje, 'ok'); await cargarServicios(); }
    else mostrarFeedback(data.error || 'Error al eliminar', 'error');
}

// ──────────────────────────────────────────────
//  Helpers UI
// ──────────────────────────────────────────────
function mostrarFeedback(txt, tipo = 'ok') {
    const el = document.getElementById('feedback');
    el.textContent = txt;
    el.className = `mb-5 px-5 py-4 rounded-2xl text-sm font-medium text-center ${
        tipo === 'ok' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'
    }`;
    el.classList.remove('hidden');
    setTimeout(() => el.classList.add('hidden'), 5000);
}
function mostrarMsgModal(txt) {
    const el = document.getElementById('msgModal');
    el.textContent = txt;
    el.className = 'px-4 py-3 rounded-xl text-sm font-medium bg-red-100 text-red-700';
    el.classList.remove('hidden');
}

async function logout() {
    if (!confirm('¿Cerrar sesión?')) return;
    await fetch('api/auth.php?accion=logout', { method: 'POST' });
    window.location.href = 'login.php';
}

function abrirModalEditarPorId(id) {
    const s = todosLosServicios.find(srv => srv.id === id);
    if (s) abrirModalEditar(s);
}
function pedirEliminarPorId(id) {
    const s = todosLosServicios.find(srv => srv.id === id);
    if (s) pedirEliminar(s.id, s.titulo);
}
function abrirModalValidarPorId(id) {
    const s = todosLosServicios.find(srv => srv.id === id);
    if (s) abrirModalValidar(s);
}

['modalServicio','modalValidar','modalEliminar'].forEach(id => {
    document.getElementById(id).addEventListener('click', e => {
        if (e.target.id === id) document.getElementById(id).classList.add('hidden');
    });
});
</script>
</body>
</html>

<?php
// ============================================================
//  servicios.php  —  Sprint 2 y 3: Gestión de Servicios y Solicitudes
//  Nutricionista: gestiona sus servicios y responde solicitudes
//  Paciente: busca servicios, envía solicitudes y sigue sus estados
//  Administrador: valida servicios y visualiza solicitudes globales
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
<title>NutriSucre - <?= $rol === 'Nutricionista' ? 'Mis Servicios' : ($rol === 'Administrador' ? 'Gestión de Servicios' : 'Servicios y Solicitudes') ?></title>
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
  .badge-Aceptada   { background:#dcfce7; color:#16a34a; }
  .badge-Rechazada  { background:#fee2e2; color:#dc2626; }
  /* Card hover */
  .card-servicio, .card-solicitud { transition:transform .25s, box-shadow .25s; }
  .card-servicio:hover, .card-solicitud:hover { transform:translateY(-4px); box-shadow:0 12px 20px -5px rgba(0,0,0,.1); }
  /* Estilos para pestañas */
  .tab-btn { position: relative; transition: all 0.2s; }
  .tab-btn.activo { color: #22c55e; font-weight: 700; }
  .tab-btn.activo::after { content: ''; position: absolute; bottom: 0; left: 0; right: 0; height: 3px; background-color: #22c55e; border-radius: 9999px; }
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
      <?= $rol === 'Nutricionista' ? 'Gestión Profesional' : ($rol === 'Administrador' ? 'Consola de Administración' : 'Plataforma de Servicios') ?>
    </h1>
  </div>
  <div class="flex items-center gap-3">
    <?php if ($rol === 'Nutricionista'): ?>
    <button id="btnNuevoServicioHeader" onclick="abrirModalCrear()"
            class="bg-[#22c55e] text-white px-5 py-2.5 rounded-2xl font-semibold flex items-center gap-2 hover:bg-[#16a34a] transition-colors text-sm shadow-md shadow-green-100">
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

  <!-- SISTEMA DE PESTAÑAS (TABS) -->
  <div class="flex border-b mb-6 gap-6">
    <?php if ($rol === 'Paciente'): ?>
      <button onclick="cambiarTab('servicios')" id="tabServicios" class="tab-btn activo pb-3 text-sm font-semibold text-gray-500 hover:text-gray-800">
        🩺 Servicios Disponibles
      </button>
      <button onclick="cambiarTab('solicitudes')" id="tabSolicitudes" class="tab-btn pb-3 text-sm font-semibold text-gray-500 hover:text-gray-800">
        📨 Mis Solicitudes
      </button>
    <?php elseif ($rol === 'Nutricionista'): ?>
      <button onclick="cambiarTab('servicios')" id="tabServicios" class="tab-btn activo pb-3 text-sm font-semibold text-gray-500 hover:text-gray-800">
        💼 Mis Servicios
      </button>
      <button onclick="cambiarTab('solicitudes')" id="tabSolicitudes" class="tab-btn pb-3 text-sm font-semibold text-gray-500 hover:text-gray-800">
        📥 Solicitudes Recibidas
      </button>
    <?php else: ?>
      <button onclick="cambiarTab('servicios')" id="tabServicios" class="tab-btn activo pb-3 text-sm font-semibold text-gray-500 hover:text-gray-800">
        🔍 Validar Servicios
      </button>
      <button onclick="cambiarTab('solicitudes')" id="tabSolicitudes" class="tab-btn pb-3 text-sm font-semibold text-gray-500 hover:text-gray-800">
        🌐 Solicitudes Globales
      </button>
    <?php endif; ?>
  </div>

  <!-- Banner informativo para nutricionista -->
  <?php if ($rol === 'Nutricionista'): ?>
  <div id="bannerNutri" class="bg-blue-50 border border-blue-200 rounded-2xl p-4 mb-6 flex gap-3 items-start">
    <span class="material-symbols-outlined text-blue-500 text-2xl flex-shrink-0 mt-0.5">info</span>
    <div class="text-sm text-blue-700">
      <strong>Gestión Profesional:</strong> Crea y edita tus ofertas en la pestaña de <span class="font-bold">Mis Servicios</span> (requieren aprobación del Admin). En la pestaña <span class="font-bold">Solicitudes Recibidas</span>, evalúa los datos clínicos ingresados por los pacientes y decide si aceptas o rechazas sus solicitudes.
    </div>
  </div>
  <?php endif; ?>

  <!-- FEEDBACK GLOBAL -->
  <div id="feedback" class="hidden mb-5 px-5 py-4 rounded-2xl text-sm font-medium text-center"></div>

  <!-- ════════════ PESTAÑA: SERVICIOS ════════════ -->
  <section id="panelServicios">
    <!-- Filtros de búsqueda y ordenación (Paciente) -->
    <?php if ($rol === 'Paciente'): ?>
    <div class="bg-white rounded-3xl p-5 shadow-sm border mb-6 space-y-4">
      <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div class="md:col-span-2">
          <label class="block text-xs font-semibold text-gray-500 mb-1">Nombre o descripción del servicio</label>
          <div class="relative">
            <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-xl">search</span>
            <input id="f_buscar" type="text" placeholder="ej: Pérdida de peso, diabetes, Keto..."
                   class="w-full border rounded-xl pl-10 pr-4 py-2.5 text-sm focus:border-[#22c55e] outline-none"
                   oninput="filtrarServiciosConDebounce()">
          </div>
        </div>
        <div>
          <label class="block text-xs font-semibold text-gray-500 mb-1">Categoría</label>
          <select id="filtroCategoria" onchange="cargarServicios()" class="w-full border rounded-xl px-4 py-2.5 text-sm focus:border-[#22c55e] outline-none">
            <option value="">Todas las categorías</option>
            <?php foreach(['Pérdida de peso','Ganancia muscular','Control de diabetes','Nutrición deportiva','Nutrición infantil','Nutrición clínica','Nutrición geriátrica','Trastornos alimenticios','Embarazo y lactancia','Otro'] as $cat): ?>
            <option><?= $cat ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div>
          <label class="block text-xs font-semibold text-gray-500 mb-1">Modalidad</label>
          <select id="filtroModalidad" onchange="cargarServicios()" class="w-full border rounded-xl px-4 py-2.5 text-sm focus:border-[#22c55e] outline-none">
            <option value="">Cualquier modalidad</option>
            <option>Virtual</option><option>Presencial</option><option>Ambas</option>
          </select>
        </div>
      </div>
      <div class="flex flex-wrap justify-between items-center gap-3 pt-2 border-t">
        <div class="w-full md:w-64">
          <label class="block text-xs font-semibold text-gray-500 mb-1">Ordenar resultados</label>
          <select id="f_orden" onchange="cargarServicios()" class="w-full border rounded-xl px-4 py-2 text-sm focus:border-[#22c55e] outline-none bg-slate-50">
            <option value="recientes">📅 Más recientes</option>
            <option value="mas_utilizados">🔥 Más utilizados</option>
            <option value="mejor_calificados">⭐ Mejor calificados</option>
            <option value="precio_asc">💵 Precio: Menor a Mayor</option>
            <option value="precio_desc">💵 Precio: Mayor a Menor</option>
          </select>
        </div>
        <button onclick="limpiarFiltrosPaciente()" class="text-xs text-gray-400 hover:text-red-500 transition-colors font-medium self-end">✕ Limpiar todos los filtros</button>
      </div>
    </div>
    <?php endif; ?>

    <!-- Filtros Administrador -->
    <?php if ($rol === 'Administrador'): ?>
    <div class="bg-white rounded-2xl p-4 shadow-sm border mb-6 flex items-center gap-4 flex-wrap">
      <select id="filtroEstado" onchange="cargarServicios()" class="border rounded-xl px-4 py-2 text-sm focus:border-[#22c55e] outline-none">
        <option value="">Todos los estados</option>
        <option value="Pendiente">⏳ Pendiente</option>
        <option value="Aprobado">✅ Aprobado</option>
        <option value="Rechazado">❌ Rechazado</option>
      </select>
      <select id="filtroCategoriaAdmin" onchange="cargarServicios()" class="border rounded-xl px-4 py-2 text-sm focus:border-[#22c55e] outline-none">
        <option value="">Todas las categorías</option>
        <?php foreach(['Pérdida de peso','Ganancia muscular','Control de diabetes','Nutrición deportiva','Nutrición infantil','Nutrición clínica','Nutrición geriátrica','Trastornos alimenticios','Embarazo y lactancia','Otro'] as $cat): ?>
        <option><?= $cat ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <?php endif; ?>

    <!-- Stats (solo nutricionista) -->
    <?php if ($rol === 'Nutricionista'): ?>
    <div id="statsNutri" class="grid grid-cols-3 gap-4 mb-6"></div>
    <?php endif; ?>

    <!-- Grid de cards de servicios -->
    <div id="containerServicios" class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
      <div class="col-span-3 text-center py-12 text-gray-400">Cargando servicios...</div>
    </div>
  </section>

  <!-- ════════════ PESTAÑA: SOLICITUDES ════════════ -->
  <section id="panelSolicitudes" class="hidden">
    <!-- Buscador o estado de solicitudes (Filtro interactivo rápido) -->
    <div class="bg-white rounded-3xl p-5 shadow-sm border mb-6 flex justify-between items-center flex-wrap gap-4">
      <div>
        <h3 class="font-bold text-gray-800">Listado de solicitudes</h3>
        <p class="text-xs text-gray-400">Control y seguimiento del estado clínico y de aceptación</p>
      </div>
      <div class="flex gap-2">
        <select id="filtroEstadoSolicitud" onchange="renderSolicitudes(todasLasSolicitudes)" class="border rounded-xl px-4 py-2 text-sm focus:border-[#22c55e] outline-none">
          <option value="">Todos los estados</option>
          <option value="Pendiente">⏳ Pendientes</option>
          <option value="Aceptada">✅ Aceptadas</option>
          <option value="Rechazada">❌ Rechazadas</option>
        </select>
      </div>
    </div>

    <!-- Contenedor de solicitudes -->
    <div id="containerSolicitudes" class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
      <div class="col-span-3 text-center py-12 text-gray-400">Cargando solicitudes...</div>
    </div>
  </section>

</main>

<!-- ═══════════ MODAL: Crear / Editar servicio (Nutricionista) ═══════════ -->
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

<!-- ═══════════ MODAL: Solicitar Servicio (Paciente) ═══════════ -->
<div id="modalSolicitar" class="hidden fixed inset-0 bg-black/60 flex items-center justify-center z-50 p-4">
  <div class="bg-white rounded-3xl w-full max-w-lg max-h-[92vh] overflow-y-auto">
    <div class="bg-gradient-to-r from-[#22c55e] to-[#16a34a] p-6 rounded-t-3xl text-white flex justify-between items-center">
      <div>
        <h3 class="text-xl font-black">Solicitar Servicio</h3>
        <p class="text-white/80 text-sm mt-1">Ingresa tus datos para que el especialista los evalúe</p>
      </div>
      <button onclick="cerrarModalSolicitar()">
        <span class="material-symbols-outlined text-white">close</span>
      </button>
    </div>

    <div class="p-6 space-y-4">
      <input type="hidden" id="sol_servicio_id">
      
      <div class="bg-slate-50 border rounded-2xl p-4 text-sm space-y-1">
        <p class="font-bold text-gray-800" id="sol_info_titulo">—</p>
        <p class="text-gray-500" id="sol_info_categoria">—</p>
        <p class="text-gray-600 font-bold" id="sol_info_precio">—</p>
      </div>

      <div>
        <label class="block text-sm font-semibold mb-1 text-gray-700">Motivo de tu consulta <span class="text-red-400">*</span></label>
        <textarea id="sol_motivo" rows="3" placeholder="Explica brevemente tu necesidad o qué buscas lograr con este servicio..."
                  class="w-full border rounded-2xl px-4 py-3 focus:border-[#22c55e] outline-none resize-none text-sm"></textarea>
      </div>

      <div class="grid grid-cols-2 gap-4">
        <div>
          <label class="block text-sm font-semibold mb-1 text-gray-700">Peso actual (kg)</label>
          <input id="sol_peso" type="number" min="10" max="300" step="0.1" placeholder="ej: 72.5"
                 class="w-full border rounded-2xl px-4 py-3 focus:border-[#22c55e] outline-none text-sm">
        </div>
        <div>
          <label class="block text-sm font-semibold mb-1 text-gray-700">Altura actual (cm)</label>
          <input id="sol_altura" type="number" min="50" max="250" step="1" placeholder="ej: 165"
                 class="w-full border rounded-2xl px-4 py-3 focus:border-[#22c55e] outline-none text-sm">
        </div>
      </div>

      <div>
        <label class="block text-sm font-semibold mb-1 text-gray-700">Condiciones médicas u observaciones</label>
        <textarea id="sol_condiciones" rows="2" placeholder="Alergias, diabetes, hipertensión, intolerancias..."
                  class="w-full border rounded-2xl px-4 py-3 focus:border-[#22c55e] outline-none resize-none text-sm"></textarea>
      </div>

      <div id="msgModalSolicitar" class="hidden px-4 py-3 rounded-xl text-sm font-medium"></div>

      <div class="flex gap-3 pt-2">
        <button onclick="cerrarModalSolicitar()" class="flex-1 py-3 border rounded-2xl font-semibold text-sm hover:bg-gray-50">Cancelar</button>
        <button onclick="enviarSolicitud()" id="btnEnviarSolicitud"
                class="flex-1 py-3 bg-[#22c55e] text-white rounded-2xl font-bold text-sm hover:bg-[#16a34a] transition-colors shadow-md shadow-green-100">
          Enviar solicitud
        </button>
      </div>
    </div>
  </div>
</div>

<!-- ═══════════ MODAL: Responder Solicitud (Nutricionista) ═══════════ -->
<div id="modalResponderSolicitud" class="hidden fixed inset-0 bg-black/60 flex items-center justify-center z-50 p-4">
  <div class="bg-white rounded-3xl w-full max-w-md p-8">
    <div class="flex justify-between items-center mb-5">
      <h3 class="text-xl font-bold text-gray-800" id="res_titulo">Responder Solicitud</h3>
      <button onclick="cerrarModalResponder()"><span class="material-symbols-outlined text-gray-400">close</span></button>
    </div>
    
    <input type="hidden" id="res_solicitud_id">
    <input type="hidden" id="res_estado_nuevo">

    <div id="res_resumen" class="bg-slate-50 rounded-2xl p-4 mb-4 text-sm space-y-1">
      <!-- Se llena por JS -->
    </div>

    <div class="mb-4">
      <label class="block text-sm font-semibold mb-2 text-gray-700" id="lblRespuesta">Mensaje de respuesta / Feedback <span class="text-red-400" id="reqAsterisco">*</span></label>
      <textarea id="res_feedback" rows="3" placeholder="Ingresa indicaciones de inicio, recomendaciones o motivo del rechazo..."
                class="w-full border rounded-2xl px-4 py-3 focus:border-[#22c55e] outline-none resize-none text-sm"></textarea>
    </div>

    <div id="msgResponder" class="hidden mb-4 px-4 py-3 rounded-xl text-sm font-medium"></div>

    <div class="flex gap-3">
      <button onclick="cerrarModalResponder()" class="flex-1 py-3 border rounded-2xl font-semibold text-sm">Cancelar</button>
      <button onclick="ejecutarRespuesta()" id="btnConfirmarRespuesta"
              class="flex-1 py-3 text-white rounded-2xl font-bold text-sm transition-colors shadow-md">
        Confirmar
      </button>
    </div>
  </div>
</div>

<!-- ═══════════ MODAL: Validar Servicio (Admin) ═══════════ -->
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
let tabActual = 'servicios';
let idParaEliminar = null;
let todosLosServicios = [];
let todasLasSolicitudes = [];
let debounceTimer = null;

document.addEventListener('DOMContentLoaded', () => {
    cargarServicios();
    cargarSolicitudes();
});

// ──────────────────────────────────────────────
//  Cambiar de pestaña (Tab)
// ──────────────────────────────────────────────
function cambiarTab(tab) {
    tabActual = tab;
    
    // Actualizar botones de pestaña
    document.querySelectorAll('.tab-btn').forEach(btn => {
        btn.classList.remove('activo');
    });
    
    const activeTabBtn = tab === 'servicios' ? document.getElementById('tabServicios') : document.getElementById('tabSolicitudes');
    if (activeTabBtn) activeTabBtn.classList.add('activo');

    // Ocultar/Mostrar paneles
    document.getElementById('panelServicios').classList.toggle('hidden', tab !== 'servicios');
    document.getElementById('panelSolicitudes').classList.toggle('hidden', tab !== 'solicitudes');

    // Cargar información correspondiente
    if (tab === 'servicios') {
        cargarServicios();
        if (ROL === 'Nutricionista') {
            const btnH = document.getElementById('btnNuevoServicioHeader');
            if (btnH) btnH.classList.remove('hidden');
        }
    } else {
        cargarSolicitudes();
        if (ROL === 'Nutricionista') {
            const btnH = document.getElementById('btnNuevoServicioHeader');
            if (btnH) btnH.classList.add('hidden'); // Ocultar agregar servicio en pestaña solicitudes
        }
    }
}

// ──────────────────────────────────────────────
//  AJAX: Cargar Servicios
// ──────────────────────────────────────────────
async function cargarServicios() {
    const container = document.getElementById('containerServicios');
    container.innerHTML = '<div class="col-span-3 text-center py-8 text-gray-400">Cargando servicios...</div>';

    let url = 'api/servicios.php';
    const params = new URLSearchParams();

    if (ROL === 'Administrador') {
        const estado = document.getElementById('filtroEstado')?.value || '';
        const cat = document.getElementById('filtroCategoriaAdmin')?.value || '';
        if (estado) params.set('estado', estado);
        if (cat) params.set('categoria', cat);
    } else if (ROL === 'Paciente') {
        params.set('publico', '1');
        const cat = document.getElementById('filtroCategoria')?.value || '';
        const mod = document.getElementById('filtroModalidad')?.value || '';
        const buscar = document.getElementById('f_buscar')?.value.trim() || '';
        const orden = document.getElementById('f_orden')?.value || 'recientes';
        
        if (cat) params.set('categoria', cat);
        if (mod) params.set('modalidad', mod);
        if (buscar) params.set('buscar', buscar);
        if (orden) params.set('orden', orden);
    }

    if (params.toString()) url += '?' + params.toString();

    try {
        const res  = await fetch(url);
        const data = await res.json();

        if (!Array.isArray(data)) {
            container.innerHTML = `<div class="col-span-3 text-center py-8 text-red-400">${data.error || 'Error al cargar'}</div>`;
            return;
        }

        todosLosServicios = data;

        // Render stats si es nutricionista
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
    } catch(err) {
        container.innerHTML = '<div class="col-span-3 text-center py-8 text-red-400">Error en la llamada de red</div>';
    }
}

function filtrarServiciosConDebounce() {
    clearTimeout(debounceTimer);
    debounceTimer = setTimeout(cargarServicios, 350);
}

function limpiarFiltrosPaciente() {
    const b = document.getElementById('f_buscar');
    const c = document.getElementById('filtroCategoria');
    const m = document.getElementById('filtroModalidad');
    const o = document.getElementById('f_orden');
    
    if (b) b.value = '';
    if (c) c.value = '';
    if (m) m.value = '';
    if (o) o.value = 'recientes';
    
    cargarServicios();
}

// ──────────────────────────────────────────────
//  Render de tarjetas de servicios
// ──────────────────────────────────────────────
function renderServicios(lista) {
    const container = document.getElementById('containerServicios');

    if (lista.length === 0) {
        const msg = ROL === 'Nutricionista'
            ? 'Aún no tienes servicios registrados. ¡Crea tu primer servicio!'
            : 'No se encontraron servicios disponibles con los criterios seleccionados.';
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
    const esPac   = ROL === 'Paciente';

    const badgeHTML = `<span class="badge-${s.estado} px-3 py-1 rounded-full text-xs font-semibold">${s.estado}</span>`;

    const iconosCat = {
        'Pérdida de peso': '⚖️', 'Ganancia muscular': '💪',
        'Control de diabetes': '🩸', 'Nutrición deportiva': '🏃',
        'Nutrición infantil': '👶', 'Nutrición clínica': '🏥',
        'Embarazo y lactancia': '🤰', 'Trastornos alimenticios': '🍃',
        'Otro': '🥗'
    };
    const icono = iconosCat[s.categoria] || '🥗';

    return `
    <div class="card-servicio bg-white rounded-3xl shadow-sm border overflow-hidden flex flex-col h-full">
        <!-- Header -->
        <div class="bg-gradient-to-br from-gray-50 to-gray-100 p-5 border-b">
            <div class="flex justify-between items-start">
                <span class="text-3xl">${icono}</span>
                ${esNutri || esAdmin ? badgeHTML : ''}
                ${esPac && s.total_solicitudes > 0 ? `<span class="bg-green-100 text-green-700 px-2 py-0.5 rounded-full text-[10px] font-bold">🔥 Solicitado ${s.total_solicitudes} veces</span>` : ''}
            </div>
            <h3 class="font-black text-base mt-3 leading-tight text-gray-800">${s.titulo}</h3>
            <p class="text-[#22c55e] text-xs font-semibold mt-1">${s.categoria}</p>
            ${(esAdmin || esPac) ? `<p class="text-gray-400 text-xs mt-1 font-medium flex items-center gap-1"><span class="material-symbols-outlined text-sm">person</span> Por: ${s.nutricionista_nombre}</p>` : ''}
        </div>

        <!-- Cuerpo -->
        <div class="p-5 flex-1 flex flex-col justify-between">
            <div>
                <p class="text-gray-600 text-xs leading-relaxed mb-4 line-clamp-3">${s.descripcion || ''}</p>

                <div class="flex flex-wrap gap-2 mb-4">
                    <span class="bg-slate-100 text-slate-600 px-2 py-0.5 rounded-full text-xs font-medium">${s.modalidad}</span>
                    <span class="bg-slate-100 text-slate-600 px-2 py-0.5 rounded-full text-xs font-medium">${s.duracion_semanas} semanas</span>
                    ${esPac && s.nutricionista_rating ? `<span class="bg-amber-50 text-amber-600 px-2 py-0.5 rounded-full text-xs font-medium flex items-center gap-0.5">★ ${parseFloat(s.nutricionista_rating).toFixed(1)}</span>` : ''}
                </div>

                <div class="flex justify-between items-center mb-4">
                    <div>
                        <p class="text-2xl font-black text-[#22c55e]">Bs. ${parseFloat(s.precio).toFixed(2)}</p>
                        <p class="text-xs text-gray-400">Precio del servicio</p>
                    </div>
                </div>

                ${s.incluye ? `
                <div class="bg-green-50/50 rounded-xl p-3 mb-4 border border-green-100/50">
                    <p class="text-xs font-semibold text-[#22c55e] mb-1">Incluye:</p>
                    <p class="text-xs text-gray-600">${s.incluye}</p>
                </div>` : ''}

                ${s.estado === 'Rechazado' && s.motivo_rechazo ? `
                <div class="bg-red-50 border border-red-200 rounded-xl p-3 mb-4">
                    <p class="text-xs font-semibold text-red-600 mb-1">Motivo de rechazo:</p>
                    <p class="text-xs text-red-700">${s.motivo_rechazo}</p>
                </div>` : ''}
            </div>

            <!-- Botones -->
            <div class="flex gap-2 pt-3 border-t">
                ${esNutri ? `
                    <button onclick="abrirModalEditarPorId(${s.id})"
                            class="flex-1 py-2 border rounded-xl text-xs font-semibold hover:bg-gray-50 flex items-center justify-center gap-1 text-slate-700 transition-colors">
                        <span class="material-symbols-outlined text-base">edit</span>Editar
                    </button>
                    <button onclick="pedirEliminarPorId(${s.id})"
                            class="py-2 px-3 border border-red-200 text-red-500 rounded-xl text-xs font-semibold hover:bg-red-50 transition-colors">
                        <span class="material-symbols-outlined text-base">delete</span>
                    </button>` : ''}
                ${esAdmin ? `
                    <button onclick="abrirModalValidarPorId(${s.id})"
                            class="flex-1 py-2 bg-[#22c55e] text-white rounded-xl text-xs font-bold hover:bg-[#16a34a] flex items-center justify-center gap-1 shadow-md shadow-green-100 transition-colors">
                        <span class="material-symbols-outlined text-base">rate_review</span>Revisar
                    </button>
                    <button onclick="pedirEliminarPorId(${s.id})"
                            class="py-2 px-3 border border-red-200 text-red-500 rounded-xl text-xs font-semibold hover:bg-red-50 transition-colors">
                        <span class="material-symbols-outlined text-base">delete</span>
                    </button>` : ''}
                ${esPac ? `
                    <button onclick="abrirModalSolicitar(${s.id})"
                            class="flex-1 py-2 bg-[#22c55e] text-white rounded-xl text-xs font-bold hover:bg-[#16a34a] text-center shadow-md shadow-green-100 transition-colors">
                        📨 Solicitar Servicio
                    </button>
                    <a href="buscar.php" class="py-2 px-3 border rounded-xl text-xs font-semibold hover:bg-gray-50 text-center text-slate-600 transition-colors" title="Ver información del especialista">
                        <span class="material-symbols-outlined text-base align-middle">info</span>
                    </a>` : ''}
            </div>
        </div>
    </div>`;
}

// ──────────────────────────────────────────────
//  AJAX: Cargar Solicitudes
// ──────────────────────────────────────────────
async function cargarSolicitudes() {
    const container = document.getElementById('containerSolicitudes');
    container.innerHTML = '<div class="col-span-3 text-center py-8 text-gray-400">Cargando solicitudes...</div>';

    try {
        const res  = await fetch('api/solicitudes.php');
        const data = await res.json();

        if (!Array.isArray(data)) {
            container.innerHTML = `<div class="col-span-3 text-center py-8 text-red-400">${data.error || 'Error al cargar'}</div>`;
            return;
        }

        todasLasSolicitudes = data;
        renderSolicitudes(todasLasSolicitudes);
    } catch(err) {
        container.innerHTML = '<div class="col-span-3 text-center py-8 text-red-400">Error en la llamada de red</div>';
    }
}

// ──────────────────────────────────────────────
//  Render de solicitudes
// ──────────────────────────────────────────────
function renderSolicitudes(lista) {
    const container = document.getElementById('containerSolicitudes');
    const filtroEstado = document.getElementById('filtroEstadoSolicitud')?.value || '';

    let filtradas = lista;
    if (filtroEstado) {
        filtradas = lista.filter(r => r.estado === filtroEstado);
    }

    if (filtradas.length === 0) {
        container.innerHTML = `
            <div class="col-span-3 text-center py-16 bg-white rounded-3xl border">
                <span class="material-symbols-outlined text-5xl text-gray-300">mail</span>
                <p class="text-gray-400 mt-3 text-sm">No se encontraron solicitudes.</p>
            </div>`;
        return;
    }

    container.innerHTML = filtradas.map(r => tarjetaSolicitud(r)).join('');
}

function tarjetaSolicitud(r) {
    const esNutri = ROL === 'Nutricionista';
    const esAdmin = ROL === 'Administrador';
    const esPac   = ROL === 'Paciente';

    const badgeHTML = `<span class="badge-${r.estado} px-3 py-1 rounded-full text-xs font-semibold">${r.estado}</span>`;
    const fecha = new Date(r.created_at).toLocaleDateString('es-BO', {
        day: '2-digit', month: '2-digit', year: 'numeric', hour: '2-digit', minute: '2-digit'
    });

    return `
    <div class="card-solicitud bg-white rounded-3xl shadow-sm border overflow-hidden flex flex-col justify-between h-full">
        <!-- Header -->
        <div class="p-5 border-b bg-gradient-to-br from-slate-50 to-slate-100 flex flex-col gap-2">
            <div class="flex justify-between items-start">
                <span class="text-xs text-gray-400 font-semibold">${fecha}</span>
                ${badgeHTML}
            </div>
            <h4 class="font-bold text-gray-800 leading-tight">${r.servicio_titulo}</h4>
            <div class="text-xs text-[#22c55e] font-semibold">${r.servicio_categoria} · ${r.servicio_modalidad}</div>
            
            ${esNutri || esAdmin ? `
                <div class="mt-2 border-t pt-2 text-xs">
                    <p class="font-bold text-gray-700">Paciente: <span class="font-normal text-gray-600">${r.paciente_nombre}</span></p>
                    <p class="font-bold text-gray-700">Email: <span class="font-normal text-gray-600">${r.paciente_email}</span></p>
                </div>
            ` : `
                <div class="mt-2 border-t pt-2 text-xs">
                    <p class="font-bold text-gray-700">Nutricionista: <span class="font-normal text-gray-600">${r.nutricionista_nombre}</span></p>
                </div>
            `}
        </div>

        <!-- Detalles de Diagnóstico/Evaluación -->
        <div class="p-5 flex-1 space-y-4">
            <div>
                <p class="text-xs font-semibold text-gray-400 mb-1">Motivo de consulta:</p>
                <p class="text-gray-700 text-xs leading-relaxed bg-slate-50 p-3 rounded-xl border border-slate-100">${r.motivo_consulta}</p>
            </div>

            <!-- Datos corporales -->
            <div class="grid grid-cols-2 gap-2 text-xs">
                <div class="bg-slate-50/50 p-2 rounded-xl border border-slate-100/50 text-center">
                    <p class="text-gray-400 font-medium text-[10px]">PESO INICIAL</p>
                    <p class="font-bold text-gray-700 mt-0.5">${r.peso_actual ? r.peso_actual + ' kg' : '—'}</p>
                </div>
                <div class="bg-slate-50/50 p-2 rounded-xl border border-slate-100/50 text-center">
                    <p class="text-gray-400 font-medium text-[10px]">ALTURA</p>
                    <p class="font-bold text-gray-700 mt-0.5">${r.altura_actual ? r.altura_actual + ' cm' : '—'}</p>
                </div>
            </div>

            ${r.condiciones_medicas ? `
                <div>
                    <p class="text-xs font-semibold text-gray-400 mb-1">Condiciones médicas:</p>
                    <p class="text-gray-600 text-xs bg-red-50/50 text-red-800 p-3 rounded-xl border border-red-100/50 font-medium">${r.condiciones_medicas}</p>
                </div>
            ` : ''}

            <!-- Mensaje de respuesta del nutricionista -->
            ${r.respuesta_ofertante ? `
                <div class="border-t pt-3">
                    <p class="text-xs font-bold text-gray-700 mb-1">Mensaje del especialista:</p>
                    <p class="text-gray-600 text-xs bg-green-50/50 p-3 rounded-xl border border-green-100 italic leading-relaxed">"${r.respuesta_ofertante}"</p>
                </div>
            ` : ''}
        </div>

        <!-- Footer / Acciones -->
        <div class="p-5 border-t bg-slate-50/50 flex justify-between items-center text-xs">
            <div>
                <p class="text-gray-400">Precio al solicitar</p>
                <p class="font-black text-gray-700 text-sm">Bs. ${parseFloat(r.precio_historico).toFixed(2)}</p>
            </div>

            ${esNutri && r.estado === 'Pendiente' ? `
                <div class="flex gap-2">
                    <button onclick="abrirModalResponder(${r.id}, 'Rechazada', '${r.paciente_nombre}', '${r.servicio_titulo}')" 
                            class="px-3 py-2 border border-red-200 hover:bg-red-50 text-red-500 rounded-xl font-bold transition-colors">
                        Rechazar
                    </button>
                    <button onclick="abrirModalResponder(${r.id}, 'Aceptada', '${r.paciente_nombre}', '${r.servicio_titulo}')"
                            class="px-3 py-2 bg-[#22c55e] hover:bg-[#16a34a] text-white rounded-xl font-bold shadow-md shadow-green-100 transition-colors">
                        Aceptar
                    </button>
                </div>
            ` : ''}

            ${esAdmin && r.estado === 'Pendiente' ? `
                <span class="text-amber-500 font-semibold italic flex items-center gap-0.5"><span class="material-symbols-outlined text-sm">hourglass_empty</span> Esperando Nutricionista</span>
            ` : ''}
            
            ${r.estado !== 'Pendiente' ? `
                <span class="font-bold flex items-center gap-1 ${r.estado.startsWith('Acep') ? 'text-[#16a34a]' : 'text-red-500'}">
                    <span class="material-symbols-outlined text-sm">${r.estado.startsWith('Acep') ? 'check_circle' : 'cancel'}</span>
                    ${r.estado}
                </span>
            ` : ''}
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

    try {
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
    } catch(e) {
        btn.disabled = false; btn.textContent = 'Guardar servicio';
        mostrarMsgModal('Error de comunicación con el servidor');
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
        <p class="font-bold text-sm text-gray-800">${s.titulo}</p>
        <p class="text-gray-500 text-xs">Por: ${s.nutricionista_nombre}</p>
        <p class="text-gray-500 text-xs">Categoría: ${s.categoria} · Bs. ${parseFloat(s.precio).toFixed(2)}</p>
        <p class="text-gray-600 mt-2 text-xs bg-slate-50 p-2 rounded-xl">${s.descripcion || ''}</p>
    `;
    document.getElementById('modalValidar').classList.remove('hidden');
}
function cerrarModalValidar() { document.getElementById('modalValidar').classList.add('hidden'); }

async function ejecutarValidacion(estado) {
    const id     = document.getElementById('val_id').value;
    const motivo = document.getElementById('val_motivo').value.trim();

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

    try {
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
    } catch(e) {
        document.getElementById('msgValidar').textContent = 'Error de conexión';
        document.getElementById('msgValidar').className = 'mb-4 px-4 py-3 rounded-xl text-sm font-medium bg-red-100 text-red-700';
        document.getElementById('msgValidar').classList.remove('hidden');
    }
}

// ──────────────────────────────────────────────
//  Sprint 3: Modal Solicitar Servicio (Paciente)
// ──────────────────────────────────────────────
function abrirModalSolicitar(servicioId) {
    const s = todosLosServicios.find(srv => srv.id === servicioId);
    if (!s) return;

    document.getElementById('sol_servicio_id').value = s.id;
    document.getElementById('sol_info_titulo').textContent = s.titulo;
    document.getElementById('sol_info_categoria').textContent = s.categoria + ' · ' + s.modalidad + ' · ' + s.duracion_semanas + ' semanas';
    document.getElementById('sol_info_precio').textContent = 'Bs. ' + parseFloat(s.precio).toFixed(2);
    
    // Limpiar campos
    document.getElementById('sol_motivo').value = '';
    document.getElementById('sol_peso').value = '';
    document.getElementById('sol_altura').value = '';
    document.getElementById('sol_condiciones').value = '';
    
    document.getElementById('msgModalSolicitar').classList.add('hidden');
    document.getElementById('modalSolicitar').classList.remove('hidden');
}

function cerrarModalSolicitar() {
    document.getElementById('modalSolicitar').classList.add('hidden');
}

async function enviarSolicitud() {
    const servicioId      = document.getElementById('sol_servicio_id').value;
    const motivo          = document.getElementById('sol_motivo').value.trim();
    const peso            = document.getElementById('sol_peso').value;
    const altura          = document.getElementById('sol_altura').value;
    const condiciones_med = document.getElementById('sol_condiciones').value.trim();

    if (!motivo) {
        return mostrarMsgSolicitar('El motivo de la consulta es obligatorio para que el especialista pueda evaluar tu caso.');
    }

    const btn = document.getElementById('btnEnviarSolicitud');
    btn.disabled = true; btn.textContent = 'Enviando...';

    const body = {
        servicio_id: parseInt(servicioId),
        motivo_consulta: motivo,
        peso_actual: peso ? parseFloat(peso) : null,
        altura_actual: altura ? parseFloat(altura) : null,
        condiciones_medicas: condiciones_med
    };

    try {
        const res = await fetch('api/solicitudes.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(body)
        });
        const data = await res.json();
        
        btn.disabled = false; btn.textContent = 'Enviar solicitud';

        if (data.ok) {
            cerrarModalSolicitar();
            mostrarFeedback('✅ Solicitud registrada con éxito.', 'ok');
            cambiarTab('solicitudes'); // Cambiar a la pestaña de mis solicitudes
        } else {
            mostrarMsgSolicitar(data.error || 'Error al procesar la solicitud.');
        }
    } catch(e) {
        btn.disabled = false; btn.textContent = 'Enviar solicitud';
        mostrarMsgSolicitar('Error de conexión con el servidor.');
    }
}

function mostrarMsgSolicitar(txt) {
    const el = document.getElementById('msgModalSolicitar');
    el.textContent = txt;
    el.className = 'px-4 py-3 rounded-xl text-sm font-medium bg-red-100 text-red-700';
    el.classList.remove('hidden');
}

// ──────────────────────────────────────────────
//  Sprint 3: Responder Solicitud (Nutricionista)
// ──────────────────────────────────────────────
function abrirModalResponder(solicitudId, estadoNuevo, pacienteNombre, servicioTitulo) {
    document.getElementById('res_solicitud_id').value = solicitudId;
    document.getElementById('res_estado_nuevo').value = estadoNuevo;
    document.getElementById('res_feedback').value = '';
    
    // Títulos y estilos dinámicos
    const tituloModal = document.getElementById('res_titulo');
    const labelFeedback = document.getElementById('lblRespuesta');
    const reqAsterisco = document.getElementById('reqAsterisco');
    const btn = document.getElementById('btnConfirmarRespuesta');
    
    document.getElementById('res_resumen').innerHTML = `
        <p class="font-bold text-gray-800">${servicioTitulo}</p>
        <p class="text-gray-500 text-xs">Paciente: ${pacienteNombre}</p>
        <p class="text-gray-600 font-bold text-xs mt-1">Acción: <span class="${estadoNuevo === 'Aceptada' ? 'text-green-600' : 'text-red-600'}">${estadoNuevo === 'Aceptada' ? '✅ Aceptar Solicitud' : '❌ Rechazar Solicitud'}</span></p>
    `;

    if (estadoNuevo === 'Aceptada') {
        tituloModal.textContent = 'Aceptar Solicitud';
        labelFeedback.textContent = 'Indicaciones iniciales o bienvenida (opcional)';
        reqAsterisco.classList.add('hidden');
        btn.className = 'flex-1 py-3 bg-[#22c55e] hover:bg-[#16a34a] text-white rounded-2xl font-bold text-sm transition-colors shadow-md shadow-green-100';
    } else {
        tituloModal.textContent = 'Rechazar Solicitud';
        labelFeedback.textContent = 'Motivo del rechazo (obligatorio)';
        reqAsterisco.classList.remove('hidden');
        btn.className = 'flex-1 py-3 bg-red-500 hover:bg-red-600 text-white rounded-2xl font-bold text-sm transition-colors shadow-md shadow-red-100';
    }

    document.getElementById('msgResponder').classList.add('hidden');
    document.getElementById('modalResponderSolicitud').classList.remove('hidden');
}

function cerrarModalResponder() {
    document.getElementById('modalResponderSolicitud').classList.add('hidden');
}

async function ejecutarRespuesta() {
    const solicitudId = document.getElementById('res_solicitud_id').value;
    const estado      = document.getElementById('res_estado_nuevo').value;
    const feedback    = document.getElementById('res_feedback').value.trim();

    if (estado === 'Rechazada' && !feedback) {
        return mostrarMsgResponder('El motivo del rechazo es obligatorio para responder al paciente.');
    }

    const btn = document.getElementById('btnConfirmarRespuesta');
    btn.disabled = true; btn.textContent = 'Guardando...';

    const body = {
        id: parseInt(solicitudId),
        estado: estado,
        respuesta_ofertante: feedback
    };

    try {
        const res = await fetch('api/solicitudes.php?accion=responder', {
            method: 'PUT',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(body)
        });
        const data = await res.json();

        btn.disabled = false; btn.textContent = 'Confirmar';

        if (data.ok) {
            cerrarModalResponder();
            mostrarFeedback(`✅ Solicitud ${estado === 'Aceptada' ? 'aceptada' : 'rechazada'} correctamente.`, 'ok');
            await cargarSolicitudes();
        } else {
            mostrarMsgResponder(data.error || 'Error al procesar la respuesta.');
        }
    } catch(e) {
        btn.disabled = false; btn.textContent = 'Confirmar';
        mostrarMsgResponder('Error de conexión.');
    }
}

function mostrarMsgResponder(txt) {
    const el = document.getElementById('msgResponder');
    el.textContent = txt;
    el.className = 'px-4 py-3 rounded-xl text-sm font-medium bg-red-100 text-red-700';
    el.classList.remove('hidden');
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
    try {
        const res  = await fetch('api/servicios.php', {
            method:  'DELETE',
            headers: { 'Content-Type': 'application/json' },
            body:    JSON.stringify({ id: idParaEliminar })
        });
        const data = await res.json();
        cerrarModalEliminar();
        if (data.ok) { mostrarFeedback('✅ ' + data.mensaje, 'ok'); await cargarServicios(); }
        else mostrarFeedback(data.error || 'Error al eliminar', 'error');
    } catch(e) {
        cerrarModalEliminar();
        mostrarFeedback('Error de conexión al eliminar', 'error');
    }
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

// Cerrar modales clicando fuera
['modalServicio','modalValidar','modalEliminar','modalSolicitar','modalResponderSolicitud'].forEach(id => {
    const modal = document.getElementById(id);
    if (modal) {
        modal.addEventListener('click', e => {
            if (e.target.id === id) modal.classList.add('hidden');
        });
    }
});
</script>
</body>
</html>

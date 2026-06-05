<?php
session_start();
if (empty($_SESSION['usuario'])) { header('Location: login.php'); exit; }
$usuario = $_SESSION['usuario'];
$rol     = $usuario['rol'];
$nombre  = $usuario['nombre'];
$CATS = ['Pérdida de peso','Ganancia muscular','Control de diabetes','Nutrición deportiva','Nutrición infantil','Nutrición clínica','Nutrición geriátrica','Trastornos alimenticios','Embarazo y lactancia','Otro'];
?>
<!DOCTYPE html>
<html lang="es">
<head>
<title>NutriSucre · <?= $rol === 'Nutricionista' ? 'Mis Servicios' : ($rol === 'Administrador' ? 'Servicios' : 'Servicios') ?></title>
<?php require_once '_ios_head.php'; ?>
<style>
  .srv-card { background:white; border-radius:22px; border:1px solid var(--border); overflow:hidden; display:flex; flex-direction:column; transition: all .3s cubic-bezier(.34,1.2,.64,1); }
  .srv-card:hover { transform:translateY(-4px); box-shadow:var(--shadow-md); }
  .sol-card { background:white; border-radius:22px; border:1px solid var(--border); overflow:hidden; display:flex; flex-direction:column; }
  .badge-Pendiente  { background:#fef9c3; color:#854d0e; }
  .badge-Aprobado   { background:#dcfce7; color:#166534; }
  .badge-Rechazado  { background:#fee2e2; color:#991b1b; }
  .badge-Aceptada   { background:#dcfce7; color:#166534; }
  .badge-Rechazada  { background:#fee2e2; color:#991b1b; }
</style>
</head>
<body>
<?php $paginaActual = 'servicios'; require_once '_sidebar.php'; ?>

<header class="ios-header md:pl-64">
  <div class="flex items-center gap-3">
    <button onclick="toggleSidebar()" class="md:hidden ios-btn-icon"><span class="icon" style="font-size:20px">menu</span></button>
    <p class="font-black text-[18px]"><?= $rol==='Nutricionista'?'Gestión Profesional':($rol==='Administrador'?'Consola Admin':'Plataforma de Servicios') ?></p>
  </div>
  <div class="flex items-center gap-3">
    <?php if ($rol==='Nutricionista'): ?>
    <button id="btnNuevoSrvH" onclick="abrirModalCrear()" class="ios-btn text-[13px]" style="border-radius:12px;padding:10px 18px">
      <span class="icon" style="font-size:16px">add</span> Nuevo servicio
    </button>
    <?php endif; ?>
    <div class="text-right hidden sm:block">
      <p class="font-semibold text-[14px]"><?= htmlspecialchars($nombre) ?></p>
      <p class="text-[12px] text-[#22c55e] font-semibold"><?= htmlspecialchars($rol) ?></p>
    </div>
  </div>
</header>

<main class="md:pl-64 p-5 md:p-8 max-w-6xl mx-auto space-y-5">

  <!-- Tabs -->
  <div class="seg-control max-w-xs">
    <?php if ($rol==='Paciente'): ?>
    <button class="seg-btn active" id="tabServicios" onclick="cambiarTab('servicios')">Servicios</button>
    <button class="seg-btn" id="tabSolicitudes" onclick="cambiarTab('solicitudes')">Mis Solicitudes</button>
    <?php elseif ($rol==='Nutricionista'): ?>
    <button class="seg-btn active" id="tabServicios" onclick="cambiarTab('servicios')">Mis Servicios</button>
    <button class="seg-btn" id="tabSolicitudes" onclick="cambiarTab('solicitudes')">Solicitudes</button>
    <?php else: ?>
    <button class="seg-btn active" id="tabServicios" onclick="cambiarTab('servicios')">Validar</button>
    <button class="seg-btn" id="tabSolicitudes" onclick="cambiarTab('solicitudes')">Solicitudes</button>
    <?php endif; ?>
  </div>

  <!-- Feedback global (toast-like) -->
  <div id="feedback" class="hidden rounded-2xl px-5 py-4 text-[14px] font-semibold text-center"></div>

  <!-- ══ PANEL SERVICIOS ══ -->
  <section id="panelServicios">

    <!-- Filtros Paciente -->
    <?php if ($rol==='Paciente'): ?>
    <div class="bg-white rounded-[20px] border border-[var(--border)] p-5 space-y-4 mb-5">
      <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
        <div class="relative">
          <span class="icon absolute left-3.5 top-1/2 -translate-y-1/2 text-[#8e8e93]" style="font-size:18px">search</span>
          <input id="f_buscar" type="text" placeholder="Buscar servicio..." class="ios-input pl-10 text-[14px]" oninput="filtrarConDebounce()">
        </div>
        <select id="filtroCategoria" onchange="cargarServicios()" class="ios-input text-[14px]">
          <option value="">Todas las categorías</option>
          <?php foreach($CATS as $c): ?><option><?=$c?></option><?php endforeach; ?>
        </select>
        <select id="filtroModalidad" onchange="cargarServicios()" class="ios-input text-[14px]">
          <option value="">Cualquier modalidad</option>
          <option>Virtual</option><option>Presencial</option><option>Ambas</option>
        </select>
      </div>
      <div class="flex items-center gap-3 flex-wrap pt-1 border-t border-[rgba(0,0,0,0.05)]">
        <span class="text-[12px] font-semibold text-[#8e8e93]">Ordenar:</span>
        <select id="f_orden" onchange="cargarServicios()" class="ios-input text-[13px]" style="width:auto;padding:8px 14px">
          <option value="recientes">📅 Más recientes</option>
          <option value="mas_utilizados">🔥 Más solicitados</option>
          <option value="mejor_calificados">⭐ Mejor calificados</option>
          <option value="precio_asc">💚 Precio: menor a mayor</option>
          <option value="precio_desc">💛 Precio: mayor a menor</option>
        </select>
        <button onclick="limpiarFiltrosPac()" class="text-[12px] text-[#8e8e93] hover:text-red-500 font-medium transition-colors">✕ Limpiar</button>
      </div>
    </div>
    <?php endif; ?>

    <!-- Filtros Admin -->
    <?php if ($rol==='Administrador'): ?>
    <div class="flex gap-3 flex-wrap mb-5">
      <select id="filtroEstado" onchange="cargarServicios()" class="ios-input text-[14px]" style="width:auto">
        <option value="">Todos los estados</option>
        <option value="Pendiente">⏳ Pendientes</option>
        <option value="Aprobado">✅ Aprobados</option>
        <option value="Rechazado">❌ Rechazados</option>
      </select>
      <select id="filtroCategoriaAdmin" onchange="cargarServicios()" class="ios-input text-[14px]" style="width:auto">
        <option value="">Todas las categorías</option>
        <?php foreach($CATS as $c): ?><option><?=$c?></option><?php endforeach; ?>
      </select>
    </div>
    <?php endif; ?>

    <!-- Stats Nutricionista -->
    <?php if ($rol==='Nutricionista'): ?>
    <div id="statsNutri" class="grid grid-cols-3 gap-4 mb-5"></div>
    <?php endif; ?>

    <div id="containerServicios" class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-5">
      <div class="col-span-3 text-center py-12 text-[#8e8e93]">Cargando servicios...</div>
    </div>
  </section>

  <!-- ══ PANEL SOLICITUDES ══ -->
  <section id="panelSolicitudes" class="hidden space-y-4">
    <div class="flex justify-between items-center flex-wrap gap-3">
      <div>
        <p class="font-black text-[18px]">Solicitudes</p>
        <p class="text-[13px] text-[#8e8e93]">Control y seguimiento de solicitudes de servicios</p>
      </div>
      <select id="filtroEstadoSol" onchange="filtrarSolicitudes()" class="ios-input text-[14px]" style="width:auto">
        <option value="">Todos los estados</option>
        <option value="Pendiente">⏳ Pendientes</option>
        <option value="Aceptada">✅ Aceptadas</option>
        <option value="Rechazada">❌ Rechazadas</option>
      </select>
    </div>
    <div id="containerSolicitudes" class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-5">
      <div class="col-span-3 text-center py-12 text-[#8e8e93]">Cargando solicitudes...</div>
    </div>
  </section>
</main>

<!-- ══ MODAL: Crear/Editar Servicio ══ -->
<div id="modalServicio" class="ios-modal-bg" onclick="if(event.target===this)cerrarModalServicio()">
  <div class="ios-modal max-w-xl">
    <div class="flex justify-between items-center p-6 border-b border-[rgba(0,0,0,0.06)]">
      <div>
        <p id="modalSrvTitulo" class="font-black text-[20px]">Nuevo Servicio</p>
        <p class="text-[13px] text-[#8e8e93]">Quedará pendiente hasta aprobación del admin</p>
      </div>
      <button onclick="cerrarModalServicio()" class="ios-btn-icon"><span class="icon">close</span></button>
    </div>
    <div class="p-6 space-y-4">
      <input type="hidden" id="srv_id">
      <div>
        <label class="text-[13px] font-semibold text-[#48484a] pl-1 block mb-2">Título <span class="text-red-400">*</span></label>
        <input id="srv_titulo" type="text" placeholder="ej: Plan de Control de Peso Intensivo" class="ios-input">
      </div>
      <div>
        <label class="text-[13px] font-semibold text-[#48484a] pl-1 block mb-2">Descripción <span class="text-red-400">*</span></label>
        <textarea id="srv_desc" rows="3" placeholder="Describe el servicio, a quién va dirigido y qué resultados puede esperar..." class="ios-input resize-none" style="font-family:inherit"></textarea>
      </div>
      <div class="grid grid-cols-2 gap-4">
        <div>
          <label class="text-[13px] font-semibold text-[#48484a] pl-1 block mb-2">Categoría <span class="text-red-400">*</span></label>
          <select id="srv_categoria" class="ios-input">
            <?php foreach($CATS as $c): ?><option><?=$c?></option><?php endforeach; ?>
          </select>
        </div>
        <div>
          <label class="text-[13px] font-semibold text-[#48484a] pl-1 block mb-2">Modalidad <span class="text-red-400">*</span></label>
          <select id="srv_modalidad" class="ios-input">
            <option>Virtual</option><option>Presencial</option><option>Ambas</option>
          </select>
        </div>
        <div>
          <label class="text-[13px] font-semibold text-[#48484a] pl-1 block mb-2">Precio (Bs.) <span class="text-red-400">*</span></label>
          <input id="srv_precio" type="number" min="1" step="0.01" placeholder="ej: 350" class="ios-input">
        </div>
        <div>
          <label class="text-[13px] font-semibold text-[#48484a] pl-1 block mb-2">Duración (semanas) <span class="text-red-400">*</span></label>
          <input id="srv_duracion" type="number" min="1" max="52" value="4" class="ios-input">
        </div>
      </div>
      <div>
        <label class="text-[13px] font-semibold text-[#48484a] pl-1 block mb-2">¿Qué incluye?</label>
        <textarea id="srv_incluye" rows="2" placeholder="ej: 4 consultas, plan en PDF, seguimiento semanal..." class="ios-input resize-none" style="font-family:inherit"></textarea>
      </div>
      <div id="avisoEdicion" class="hidden bg-amber-50 border border-amber-200 rounded-2xl p-3 text-[13px] text-amber-700">
        ⚠ Al guardar cambios, el servicio volverá a estado <strong>Pendiente</strong>.
      </div>
      <div id="msgModalSrv" class="hidden rounded-2xl px-4 py-3 text-[13px] font-semibold"></div>
    </div>
    <div class="px-6 pb-6 flex gap-3">
      <button onclick="cerrarModalServicio()" class="ios-btn-ghost flex-1" style="border-radius:14px">Cancelar</button>
      <button onclick="guardarServicio()" id="btnGuardarSrv" class="ios-btn flex-1" style="border-radius:14px">Guardar servicio</button>
    </div>
  </div>
</div>

<!-- ══ MODAL: Solicitar Servicio (Paciente) ══ -->
<div id="modalSolicitar" class="ios-modal-bg" onclick="if(event.target===this)cerrarModalSolicitar()">
  <div class="ios-modal max-w-lg">
    <div class="flex justify-between items-center p-6 border-b border-[rgba(0,0,0,0.06)]">
      <div>
        <p class="font-black text-[20px]">Solicitar Servicio</p>
        <p class="text-[13px] text-[#8e8e93]">Datos para que el especialista evalúe tu caso</p>
      </div>
      <button onclick="cerrarModalSolicitar()" class="ios-btn-icon"><span class="icon">close</span></button>
    </div>
    <div class="p-6 space-y-4">
      <input type="hidden" id="sol_servicio_id">
      <div class="bg-[#f9f9fb] rounded-2xl p-4">
        <p id="sol_info_titulo" class="font-bold text-[15px]">—</p>
        <p id="sol_info_cat" class="text-[13px] text-[#8e8e93] mt-0.5">—</p>
        <p id="sol_info_precio" class="font-black text-[#22c55e] text-[18px] mt-2">—</p>
      </div>
      <div>
        <label class="text-[13px] font-semibold text-[#48484a] pl-1 block mb-2">Motivo de tu consulta <span class="text-red-400">*</span></label>
        <textarea id="sol_motivo" rows="3" placeholder="Explica qué buscas lograr con este servicio..." class="ios-input resize-none" style="font-family:inherit"></textarea>
      </div>
      <div class="grid grid-cols-2 gap-3">
        <div>
          <label class="text-[13px] font-semibold text-[#48484a] pl-1 block mb-2">Peso actual (kg)</label>
          <input id="sol_peso" type="number" min="10" max="300" step="0.1" placeholder="ej: 72.5" class="ios-input">
        </div>
        <div>
          <label class="text-[13px] font-semibold text-[#48484a] pl-1 block mb-2">Altura (cm)</label>
          <input id="sol_altura" type="number" min="50" max="250" placeholder="ej: 165" class="ios-input">
        </div>
      </div>
      <div>
        <label class="text-[13px] font-semibold text-[#48484a] pl-1 block mb-2">Condiciones médicas</label>
        <textarea id="sol_condiciones" rows="2" placeholder="Alergias, diabetes, intolerancias..." class="ios-input resize-none" style="font-family:inherit"></textarea>
      </div>
      <div id="msgModalSol" class="hidden rounded-2xl px-4 py-3 text-[13px] font-semibold"></div>
    </div>
    <div class="px-6 pb-6 flex gap-3">
      <button onclick="cerrarModalSolicitar()" class="ios-btn-ghost flex-1" style="border-radius:14px">Cancelar</button>
      <button onclick="enviarSolicitud()" id="btnEnviarSol" class="ios-btn flex-1" style="border-radius:14px">
        <span class="icon" style="font-size:18px">send</span> Enviar solicitud
      </button>
    </div>
  </div>
</div>

<!-- ══ MODAL: Responder Solicitud (Nutricionista) ══ -->
<div id="modalResponder" class="ios-modal-bg" onclick="if(event.target===this)cerrarModalResponder()">
  <div class="ios-modal max-w-md">
    <div class="flex justify-between items-center p-6 border-b border-[rgba(0,0,0,0.06)]">
      <p id="resTitulo" class="font-black text-[20px]">Responder Solicitud</p>
      <button onclick="cerrarModalResponder()" class="ios-btn-icon"><span class="icon">close</span></button>
    </div>
    <div class="p-6 space-y-4">
      <input type="hidden" id="res_sol_id">
      <input type="hidden" id="res_estado_nuevo">
      <div id="resResumen" class="bg-[#f9f9fb] rounded-2xl p-4 text-[13px] space-y-1"></div>
      <div>
        <label class="text-[13px] font-semibold text-[#48484a] pl-1 block mb-2" id="lblFeedback">Mensaje de respuesta</label>
        <textarea id="res_feedback" rows="3" placeholder="Indicaciones iniciales, recomendaciones o motivo del rechazo..." class="ios-input resize-none" style="font-family:inherit"></textarea>
      </div>
      <div id="msgResponder" class="hidden rounded-2xl px-4 py-3 text-[13px] font-semibold"></div>
    </div>
    <div class="px-6 pb-6 flex gap-3">
      <button onclick="cerrarModalResponder()" class="ios-btn-ghost flex-1" style="border-radius:14px">Cancelar</button>
      <button onclick="ejecutarRespuesta()" id="btnConfirmarResp" class="ios-btn flex-1" style="border-radius:14px">Confirmar</button>
    </div>
  </div>
</div>

<!-- ══ MODAL: Validar Servicio (Admin) ══ -->
<div id="modalValidar" class="ios-modal-bg" onclick="if(event.target===this)cerrarModalValidar()">
  <div class="ios-modal max-w-md">
    <div class="flex justify-between items-center p-6 border-b border-[rgba(0,0,0,0.06)]">
      <p class="font-black text-[20px]">Revisar Servicio</p>
      <button onclick="cerrarModalValidar()" class="ios-btn-icon"><span class="icon">close</span></button>
    </div>
    <div class="p-6 space-y-4">
      <input type="hidden" id="val_id">
      <div id="val_resumen" class="bg-[#f9f9fb] rounded-2xl p-4 text-[13px] space-y-1"></div>
      <div id="bloqueMotivo" class="hidden">
        <label class="text-[13px] font-semibold text-[#48484a] pl-1 block mb-2">Motivo del rechazo <span class="text-red-400">*</span></label>
        <textarea id="val_motivo" rows="3" placeholder="Explica al nutricionista qué debe mejorar..." class="ios-input resize-none" style="font-family:inherit"></textarea>
      </div>
      <div id="msgValidar" class="hidden rounded-2xl px-4 py-3 text-[13px] font-semibold"></div>
    </div>
    <div class="px-6 pb-6 flex gap-3">
      <button onclick="cerrarModalValidar()" class="ios-btn-ghost" style="border-radius:14px;flex:0 0 auto;padding:13px 20px">Cancelar</button>
      <button onclick="ejecutarValidacion('Rechazado')" class="flex-1 py-3 bg-red-500 text-white rounded-[14px] font-bold text-[14px] hover:bg-red-600 transition-colors">❌ Rechazar</button>
      <button onclick="ejecutarValidacion('Aprobado')"  class="flex-1 py-3 bg-[#22c55e] text-white rounded-[14px] font-bold text-[14px] hover:bg-[#16a34a] transition-colors">✅ Aprobar</button>
    </div>
  </div>
</div>

<!-- ══ MODAL: Eliminar ══ -->
<div id="modalEliminar" class="ios-modal-bg" onclick="if(event.target===this)cerrarModalEliminar()">
  <div class="ios-modal max-w-sm p-7 text-center">
    <span class="icon text-red-400" style="font-size:52px">delete_forever</span>
    <p class="font-black text-[20px] mt-3">¿Eliminar servicio?</p>
    <p id="textoEliminar" class="text-[14px] text-[#8e8e93] mt-2 mb-6"></p>
    <div class="flex gap-3">
      <button onclick="cerrarModalEliminar()" class="ios-btn-ghost flex-1" style="border-radius:14px">Cancelar</button>
      <button onclick="ejecutarEliminar()" class="flex-1 py-3 bg-red-500 text-white rounded-[14px] font-bold text-[14px] hover:bg-red-600 transition-colors">Eliminar</button>
    </div>
  </div>
</div>

<script>
const ROL = '<?= $rol ?>';
let tabActual='servicios', idEliminar=null, todosServicios=[], todasSolicitudes=[], debTimer=null;

document.addEventListener('DOMContentLoaded',()=>{ cargarServicios(); cargarSolicitudes(); });

function cambiarTab(tab) {
    tabActual = tab;
    document.getElementById('tabServicios').classList.toggle('active', tab==='servicios');
    document.getElementById('tabSolicitudes').classList.toggle('active', tab==='solicitudes');
    document.getElementById('panelServicios').classList.toggle('hidden', tab!=='servicios');
    document.getElementById('panelSolicitudes').classList.toggle('hidden', tab!=='solicitudes');
    const btnH = document.getElementById('btnNuevoSrvH');
    if (btnH) btnH.classList.toggle('hidden', tab!=='servicios');
    if (tab==='solicitudes') cargarSolicitudes();
    else cargarServicios();
}

// ── Servicios ──
async function cargarServicios() {
    const c = document.getElementById('containerServicios');
    c.innerHTML = '<div class="col-span-3 text-center py-12 text-[#8e8e93]">Cargando...</div>';
    let url = 'api/servicios.php', p = new URLSearchParams();
    if (ROL==='Administrador') {
        const e=document.getElementById('filtroEstado')?.value||'';
        const cat=document.getElementById('filtroCategoriaAdmin')?.value||'';
        if(e) p.set('estado',e); if(cat) p.set('categoria',cat);
    } else if (ROL==='Paciente') {
        p.set('publico','1');
        const cat=document.getElementById('filtroCategoria')?.value||'';
        const mod=document.getElementById('filtroModalidad')?.value||'';
        const b=document.getElementById('f_buscar')?.value.trim()||'';
        const ord=document.getElementById('f_orden')?.value||'recientes';
        if(cat) p.set('categoria',cat); if(mod) p.set('modalidad',mod);
        if(b) p.set('buscar',b); p.set('orden',ord);
    }
    if(p.toString()) url+='?'+p;
    try {
        const res=await fetch(url); const data=await res.json();
        if(!Array.isArray(data)) { c.innerHTML=`<div class="col-span-3 text-center py-12 text-red-400">${data.error||'Error'}</div>`; return; }
        todosServicios=data;
        if (ROL==='Nutricionista') {
            const pen=data.filter(s=>s.estado==='Pendiente').length;
            const apr=data.filter(s=>s.estado==='Aprobado').length;
            document.getElementById('statsNutri').innerHTML=[
                {l:'Total',v:data.length,c:'text-[#1c1c1e]'},
                {l:'Aprobados',v:apr,c:'text-[#22c55e]'},
                {l:'Pendientes',v:pen,c:'text-amber-500'},
            ].map(s=>`<div class="bg-white rounded-[18px] border border-[var(--border)] p-4 text-center">
                <p class="text-[28px] font-black ${s.c}">${s.v}</p>
                <p class="text-[12px] text-[#8e8e93] font-medium mt-0.5">${s.l}</p>
            </div>`).join('');
        }
        renderServicios(data);
    } catch(e) { c.innerHTML='<div class="col-span-3 text-center py-12 text-red-400">Error de red</div>'; }
}
function filtrarConDebounce() { clearTimeout(debTimer); debTimer=setTimeout(cargarServicios,350); }
function limpiarFiltrosPac() {
    ['f_buscar','filtroCategoria','filtroModalidad'].forEach(id=>{ const el=document.getElementById(id); if(el) el.value=''; });
    document.getElementById('f_orden').value='recientes';
    cargarServicios();
}
const CAT_ICON={'Pérdida de peso':'⚖️','Ganancia muscular':'💪','Control de diabetes':'🩸','Nutrición deportiva':'🏃','Nutrición infantil':'👶','Nutrición clínica':'🏥','Embarazo y lactancia':'🤰','Trastornos alimenticios':'🍃','Otro':'🥗'};
function renderServicios(lista) {
    const c=document.getElementById('containerServicios');
    if(!lista.length) {
        c.innerHTML=`<div class="col-span-3 text-center py-16 ios-card">
            <span class="icon text-[#d1d5db]" style="font-size:56px">medical_services</span>
            <p class="text-[#8e8e93] mt-3 text-[15px]">${ROL==='Nutricionista'?'Aún no tienes servicios. ¡Crea el primero!':'Sin servicios disponibles.'}</p>
            ${ROL==='Nutricionista'?'<button onclick="abrirModalCrear()" class="ios-btn mt-4 text-[13px]" style="border-radius:12px;padding:10px 20px">+ Crear servicio</button>':''}
        </div>`; return;
    }
    c.innerHTML=lista.map((s,i)=>tarjetaSrv(s,i)).join('');
}
function tarjetaSrv(s,i=0) {
    const esN=ROL==='Nutricionista', esA=ROL==='Administrador', esP=ROL==='Paciente';
    const ico=CAT_ICON[s.categoria]||'🥗';
    const badgeCls=`badge badge-${s.estado}`;
    return `<div class="srv-card fade-up" style="animation-delay:${i*0.05}s">
        <div class="bg-gradient-to-br from-[#f9f9fb] to-[#f2f2f7] p-5 border-b border-[rgba(0,0,0,0.05)]">
            <div class="flex justify-between items-start mb-3">
                <span class="text-3xl">${ico}</span>
                <div class="flex gap-2 items-center">
                    ${esP&&s.total_solicitudes>0?`<span class="badge badge-green">🔥 ${s.total_solicitudes}x</span>`:''}
                    ${esN||esA?`<span class="${badgeCls}">${s.estado}</span>`:''}
                </div>
            </div>
            <p class="font-black text-[16px] leading-tight">${s.titulo}</p>
            <p class="text-[#22c55e] text-[12px] font-semibold mt-1">${s.categoria}</p>
            ${esA||esP?`<p class="text-[11px] text-[#8e8e93] mt-1 flex items-center gap-1"><span class="icon" style="font-size:14px">person</span>${s.nutricionista_nombre}</p>`:''}
        </div>
        <div class="p-5 flex-1 flex flex-col justify-between">
            <div>
                <p class="text-[13px] text-[#48484a] leading-relaxed mb-3 line-clamp-3">${s.descripcion||''}</p>
                <div class="flex flex-wrap gap-1.5 mb-3">
                    <span class="badge badge-gray">${s.modalidad}</span>
                    <span class="badge badge-gray">${s.duracion_semanas} sem.</span>
                    ${esP&&s.nutricionista_rating?`<span class="badge badge-yellow">★ ${parseFloat(s.nutricionista_rating).toFixed(1)}</span>`:''}
                </div>
                <p class="text-[24px] font-black text-[#22c55e]">Bs. ${parseFloat(s.precio).toFixed(2)}</p>
                ${s.incluye?`<p class="text-[11px] text-[#8e8e93] mt-1 leading-relaxed"><span class="font-semibold text-[#22c55e]">Incluye:</span> ${s.incluye}</p>`:''}
                ${s.estado==='Rechazado'&&s.motivo_rechazo?`<div class="mt-3 bg-red-50 border border-red-100 rounded-2xl p-3"><p class="text-[11px] font-bold text-red-600 mb-1">Motivo rechazo:</p><p class="text-[11px] text-red-700">${s.motivo_rechazo}</p></div>`:''}
            </div>
            <div class="flex gap-2 pt-4 mt-4 border-t border-[rgba(0,0,0,0.05)]">
                ${esN?`
                    <button onclick="abrirModalEditarPorId(${s.id})" class="ios-btn-ghost flex-1 text-[13px]" style="border-radius:12px;padding:10px">
                        <span class="icon" style="font-size:16px">edit</span> Editar
                    </button>
                    <button onclick="pedirEliminarPorId(${s.id})" class="ios-btn-icon" style="border-color:#fecaca;color:#ef4444" title="Eliminar">
                        <span class="icon" style="font-size:18px">delete</span>
                    </button>`:''}
                ${esA?`
                    <button onclick="abrirModalValidarPorId(${s.id})" class="ios-btn flex-1 text-[13px]" style="border-radius:12px;padding:10px">
                        <span class="icon" style="font-size:16px">rate_review</span> Revisar
                    </button>
                    <button onclick="pedirEliminarPorId(${s.id})" class="ios-btn-icon" style="border-color:#fecaca;color:#ef4444">
                        <span class="icon" style="font-size:18px">delete</span>
                    </button>`:''}
                ${esP?`
                    <button onclick="abrirModalSolicitar(${s.id})" class="ios-btn flex-1 text-[13px]" style="border-radius:12px;padding:10px">
                        <span class="icon" style="font-size:16px">send</span> Solicitar
                    </button>`:''}
            </div>
        </div>
    </div>`;
}

// ── Solicitudes ──
async function cargarSolicitudes() {
    const c=document.getElementById('containerSolicitudes');
    c.innerHTML='<div class="col-span-3 text-center py-12 text-[#8e8e93]">Cargando...</div>';
    try {
        const res=await fetch('api/solicitudes.php'); const data=await res.json();
        if(!Array.isArray(data)) { c.innerHTML=`<div class="col-span-3 text-center py-12 text-red-400">${data.error||'Error'}</div>`; return; }
        todasSolicitudes=data; filtrarSolicitudes();
    } catch(e) { c.innerHTML='<div class="col-span-3 text-center py-12 text-red-400">Error de red</div>'; }
}
function filtrarSolicitudes() {
    const f=document.getElementById('filtroEstadoSol')?.value||'';
    const lista=f?todasSolicitudes.filter(r=>r.estado===f):todasSolicitudes;
    renderSolicitudes(lista);
}
function renderSolicitudes(lista) {
    const c=document.getElementById('containerSolicitudes');
    if(!lista.length) { c.innerHTML='<div class="col-span-3 text-center py-16 ios-card"><span class="icon text-[#d1d5db]" style="font-size:56px">inbox</span><p class="text-[#8e8e93] mt-3 text-[15px]">No hay solicitudes.</p></div>'; return; }
    c.innerHTML=lista.map((r,i)=>tarjetaSol(r,i)).join('');
}
function tarjetaSol(r,i=0) {
    const esN=ROL==='Nutricionista', esA=ROL==='Administrador', esP=ROL==='Paciente';
    const fecha=new Date(r.created_at).toLocaleDateString('es-BO',{day:'2-digit',month:'2-digit',year:'numeric'});
    const badgeCls=`badge badge-${r.estado}`;
    return `<div class="sol-card fade-up" style="animation-delay:${i*0.05}s">
        <div class="p-5 border-b border-[rgba(0,0,0,0.05)] bg-[#f9f9fb]">
            <div class="flex justify-between items-start mb-2">
                <span class="text-[11px] text-[#8e8e93] font-semibold">${fecha}</span>
                <span class="${badgeCls}">${r.estado}</span>
            </div>
            <p class="font-black text-[15px] leading-tight">${r.servicio_titulo}</p>
            <p class="text-[#22c55e] text-[12px] font-semibold mt-0.5">${r.servicio_categoria} · ${r.servicio_modalidad}</p>
            ${esN||esA?`<div class="mt-3 pt-3 border-t border-[rgba(0,0,0,0.06)] text-[12px]">
                <p class="font-bold text-[#48484a]">Paciente: <span class="font-normal">${r.paciente_nombre}</span></p>
                <p class="font-bold text-[#48484a]">Email: <span class="font-normal">${r.paciente_email}</span></p>
            </div>`:`<div class="mt-3 pt-3 border-t border-[rgba(0,0,0,0.06)] text-[12px]">
                <p class="font-bold text-[#48484a]">Nutricionista: <span class="font-normal">${r.nutricionista_nombre}</span></p>
            </div>`}
        </div>
        <div class="p-5 flex-1 space-y-3">
            <div>
                <p class="text-[11px] font-semibold text-[#8e8e93] mb-1 uppercase tracking-wide">Motivo</p>
                <p class="text-[13px] text-[#48484a] bg-[#f9f9fb] rounded-2xl p-3 leading-relaxed">${r.motivo_consulta}</p>
            </div>
            <div class="grid grid-cols-2 gap-2">
                <div class="bg-[#f9f9fb] rounded-xl p-3 text-center">
                    <p class="text-[10px] text-[#8e8e93] font-bold uppercase tracking-wide">Peso</p>
                    <p class="font-black text-[14px] mt-0.5">${r.peso_actual?r.peso_actual+' kg':'—'}</p>
                </div>
                <div class="bg-[#f9f9fb] rounded-xl p-3 text-center">
                    <p class="text-[10px] text-[#8e8e93] font-bold uppercase tracking-wide">Altura</p>
                    <p class="font-black text-[14px] mt-0.5">${r.altura_actual?r.altura_actual+' cm':'—'}</p>
                </div>
            </div>
            ${r.condiciones_medicas?`<div><p class="text-[11px] font-semibold text-[#8e8e93] mb-1 uppercase tracking-wide">Condiciones médicas</p><p class="text-[13px] bg-red-50 text-red-800 rounded-2xl p-3 font-medium">${r.condiciones_medicas}</p></div>`:''}
            ${r.respuesta_ofertante?`<div class="border-t border-[rgba(0,0,0,0.05)] pt-3"><p class="text-[11px] font-semibold text-[#8e8e93] mb-1 uppercase tracking-wide">Respuesta del especialista</p><p class="text-[13px] text-[#48484a] italic bg-green-50 border border-green-100 rounded-2xl p-3">"${r.respuesta_ofertante}"</p></div>`:''}
        </div>
        <div class="p-4 border-t border-[rgba(0,0,0,0.05)] flex justify-between items-center">
            <div>
                <p class="text-[11px] text-[#8e8e93] font-semibold">Precio al solicitar</p>
                <p class="font-black text-[#1c1c1e] text-[16px]">Bs. ${parseFloat(r.precio_historico).toFixed(2)}</p>
            </div>
            ${esN&&r.estado==='Pendiente'?`<div class="flex gap-2">
                <button onclick="abrirModalResponder(${r.id},'Rechazada','${r.paciente_nombre}','${r.servicio_titulo.replace(/'/g,"\\'")}' )" class="ios-btn-ghost text-red-500 text-[13px]" style="border-radius:12px;padding:9px 14px;border-color:#fecaca">Rechazar</button>
                <button onclick="abrirModalResponder(${r.id},'Aceptada','${r.paciente_nombre}','${r.servicio_titulo.replace(/'/g,"\\'")}' )" class="ios-btn text-[13px]" style="border-radius:12px;padding:9px 14px">Aceptar</button>
            </div>`:''}
            ${r.estado!=='Pendiente'?`<span class="badge ${r.estado==='Aceptada'?'badge-green':'badge-red'}">${r.estado}</span>`:''}
        </div>
    </div>`;
}

// ── Modal Crear/Editar Servicio ──
function abrirModalCrear() {
    ['srv_id','srv_titulo','srv_desc','srv_precio','srv_incluye'].forEach(id=>document.getElementById(id).value='');
    document.getElementById('srv_duracion').value='4';
    document.getElementById('srv_categoria').value='Pérdida de peso';
    document.getElementById('srv_modalidad').value='Virtual';
    document.getElementById('modalSrvTitulo').textContent='Nuevo Servicio';
    document.getElementById('avisoEdicion').classList.add('hidden');
    document.getElementById('msgModalSrv').classList.add('hidden');
    document.getElementById('modalServicio').classList.add('open');
}
function abrirModalEditar(s) {
    document.getElementById('srv_id').value=s.id;
    document.getElementById('srv_titulo').value=s.titulo;
    document.getElementById('srv_desc').value=s.descripcion||'';
    document.getElementById('srv_precio').value=s.precio;
    document.getElementById('srv_duracion').value=s.duracion_semanas;
    document.getElementById('srv_incluye').value=s.incluye||'';
    document.getElementById('srv_categoria').value=s.categoria;
    document.getElementById('srv_modalidad').value=s.modalidad;
    document.getElementById('modalSrvTitulo').textContent='Editar Servicio';
    document.getElementById('avisoEdicion').classList.remove('hidden');
    document.getElementById('msgModalSrv').classList.add('hidden');
    document.getElementById('modalServicio').classList.add('open');
}
function cerrarModalServicio() { document.getElementById('modalServicio').classList.remove('open'); }
async function guardarServicio() {
    const id=document.getElementById('srv_id').value;
    const titulo=document.getElementById('srv_titulo').value.trim();
    const desc=document.getElementById('srv_desc').value.trim();
    const precio=document.getElementById('srv_precio').value;
    if(!titulo) return mostrarMsgSrv('El título es obligatorio');
    if(!desc)   return mostrarMsgSrv('La descripción es obligatoria');
    if(!precio||parseFloat(precio)<=0) return mostrarMsgSrv('El precio debe ser mayor a 0');
    const btn=document.getElementById('btnGuardarSrv');
    btn.disabled=true; btn.textContent='Guardando...';
    const body={titulo,descripcion:desc,precio,duracion_semanas:document.getElementById('srv_duracion').value,
                 categoria:document.getElementById('srv_categoria').value,
                 modalidad:document.getElementById('srv_modalidad').value,
                 incluye:document.getElementById('srv_incluye').value.trim()};
    if(id) body.id=parseInt(id);
    try {
        const res=await fetch('api/servicios.php',{method:id?'PUT':'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify(body)});
        const data=await res.json();
        btn.disabled=false; btn.textContent='Guardar servicio';
        if(data.ok) { cerrarModalServicio(); mostrarFeedback('✅ '+data.mensaje,'ok'); await cargarServicios(); }
        else mostrarMsgSrv(data.error||'Error al guardar');
    } catch(e) { btn.disabled=false; btn.textContent='Guardar servicio'; mostrarMsgSrv('Error de conexión'); }
}
function mostrarMsgSrv(txt) { const el=document.getElementById('msgModalSrv'); el.textContent=txt; el.className='rounded-2xl px-4 py-3 text-[13px] font-semibold bg-red-50 text-red-700'; el.classList.remove('hidden'); }

// ── Modal Solicitar (Paciente) ──
function abrirModalSolicitar(id) {
    const s=todosServicios.find(x=>x.id===id); if(!s) return;
    document.getElementById('sol_servicio_id').value=s.id;
    document.getElementById('sol_info_titulo').textContent=s.titulo;
    document.getElementById('sol_info_cat').textContent=s.categoria+' · '+s.modalidad+' · '+s.duracion_semanas+' semanas';
    document.getElementById('sol_info_precio').textContent='Bs. '+parseFloat(s.precio).toFixed(2);
    ['sol_motivo','sol_peso','sol_altura','sol_condiciones'].forEach(id=>document.getElementById(id).value='');
    document.getElementById('msgModalSol').classList.add('hidden');
    document.getElementById('modalSolicitar').classList.add('open');
}
function cerrarModalSolicitar() { document.getElementById('modalSolicitar').classList.remove('open'); }
async function enviarSolicitud() {
    const motivo=document.getElementById('sol_motivo').value.trim();
    if(!motivo) return mostrarMsgSol('El motivo de la consulta es obligatorio.');
    const btn=document.getElementById('btnEnviarSol');
    btn.disabled=true; btn.textContent='Enviando...';
    try {
        const res=await fetch('api/solicitudes.php',{method:'POST',headers:{'Content-Type':'application/json'},
            body:JSON.stringify({servicio_id:parseInt(document.getElementById('sol_servicio_id').value),
                motivo_consulta:motivo,
                peso_actual:document.getElementById('sol_peso').value||null,
                altura_actual:document.getElementById('sol_altura').value||null,
                condiciones_medicas:document.getElementById('sol_condiciones').value.trim()})});
        const data=await res.json();
        btn.disabled=false; btn.innerHTML='<span class="icon" style="font-size:18px">send</span> Enviar solicitud';
        if(data.ok) { cerrarModalSolicitar(); showToast('✅ Solicitud enviada correctamente'); cambiarTab('solicitudes'); }
        else mostrarMsgSol(data.error||'Error al enviar');
    } catch(e) { btn.disabled=false; btn.textContent='Enviar solicitud'; mostrarMsgSol('Error de conexión'); }
}
function mostrarMsgSol(txt) { const el=document.getElementById('msgModalSol'); el.textContent=txt; el.className='rounded-2xl px-4 py-3 text-[13px] font-semibold bg-red-50 text-red-700'; el.classList.remove('hidden'); }

// ── Modal Responder (Nutricionista) ──
function abrirModalResponder(id,estado,paciente,titulo) {
    document.getElementById('res_sol_id').value=id;
    document.getElementById('res_estado_nuevo').value=estado;
    document.getElementById('res_feedback').value='';
    document.getElementById('resTitulo').textContent=estado==='Aceptada'?'Aceptar Solicitud':'Rechazar Solicitud';
    document.getElementById('lblFeedback').textContent=estado==='Aceptada'?'Bienvenida o indicaciones iniciales (opcional)':'Motivo del rechazo (obligatorio)';
    document.getElementById('resResumen').innerHTML=`<p class="font-bold">${titulo}</p><p class="text-[#8e8e93]">Paciente: ${paciente}</p><p class="font-bold mt-1 ${estado==='Aceptada'?'text-[#22c55e]':'text-red-500'}">${estado==='Aceptada'?'✅ Aceptar':'❌ Rechazar'}</p>`;
    const btn=document.getElementById('btnConfirmarResp');
    btn.style.background=estado==='Aceptada'?'#22c55e':'#ef4444';
    document.getElementById('msgResponder').classList.add('hidden');
    document.getElementById('modalResponder').classList.add('open');
}
function cerrarModalResponder() { document.getElementById('modalResponder').classList.remove('open'); }
async function ejecutarRespuesta() {
    const id=document.getElementById('res_sol_id').value;
    const estado=document.getElementById('res_estado_nuevo').value;
    const fb=document.getElementById('res_feedback').value.trim();
    if(estado==='Rechazada'&&!fb) return mostrarMsgResp('El motivo del rechazo es obligatorio.');
    const btn=document.getElementById('btnConfirmarResp');
    btn.disabled=true; btn.textContent='Guardando...';
    try {
        const res=await fetch('api/solicitudes.php?accion=responder',{method:'PUT',headers:{'Content-Type':'application/json'},
            body:JSON.stringify({id:parseInt(id),estado,respuesta_ofertante:fb})});
        const data=await res.json();
        btn.disabled=false; btn.textContent='Confirmar';
        if(data.ok) { cerrarModalResponder(); showToast(estado==='Aceptada'?'✅ Solicitud aceptada':'❌ Solicitud rechazada'); await cargarSolicitudes(); }
        else mostrarMsgResp(data.error||'Error');
    } catch(e) { btn.disabled=false; btn.textContent='Confirmar'; mostrarMsgResp('Error de conexión'); }
}
function mostrarMsgResp(txt) { const el=document.getElementById('msgResponder'); el.textContent=txt; el.className='rounded-2xl px-4 py-3 text-[13px] font-semibold bg-red-50 text-red-700'; el.classList.remove('hidden'); }

// ── Modal Validar (Admin) ──
function abrirModalValidar(s) {
    document.getElementById('val_id').value=s.id;
    document.getElementById('val_motivo').value='';
    document.getElementById('bloqueMotivo').classList.add('hidden');
    document.getElementById('msgValidar').classList.add('hidden');
    document.getElementById('val_resumen').innerHTML=`<p class="font-bold text-[15px]">${s.titulo}</p><p class="text-[#8e8e93] text-[12px]">Por: ${s.nutricionista_nombre}</p><p class="text-[12px] text-[#48484a]">${s.categoria} · Bs. ${parseFloat(s.precio).toFixed(2)}</p><p class="text-[13px] mt-2 text-[#48484a]">${s.descripcion||''}</p>`;
    document.getElementById('modalValidar').classList.add('open');
}
function cerrarModalValidar() { document.getElementById('modalValidar').classList.remove('open'); }
async function ejecutarValidacion(estado) {
    const id=document.getElementById('val_id').value;
    const motivo=document.getElementById('val_motivo').value.trim();
    if(estado==='Rechazado') {
        document.getElementById('bloqueMotivo').classList.remove('hidden');
        if(!motivo) return mostrarMsgVal('El motivo del rechazo es obligatorio.');
    }
    try {
        const res=await fetch('api/servicios.php?accion=validar',{method:'PUT',headers:{'Content-Type':'application/json'},
            body:JSON.stringify({id:parseInt(id),estado,motivo})});
        const data=await res.json();
        if(data.ok) { cerrarModalValidar(); showToast(estado==='Aprobado'?'✅ Servicio aprobado':'❌ Servicio rechazado'); await cargarServicios(); }
        else mostrarMsgVal(data.error||'Error');
    } catch(e) { mostrarMsgVal('Error de conexión'); }
}
function mostrarMsgVal(txt) { const el=document.getElementById('msgValidar'); el.textContent=txt; el.className='rounded-2xl px-4 py-3 text-[13px] font-semibold bg-red-50 text-red-700'; el.classList.remove('hidden'); }

// ── Modal Eliminar ──
function pedirEliminar(id,titulo) { idEliminar=id; document.getElementById('textoEliminar').textContent=`"${titulo}" será eliminado permanentemente.`; document.getElementById('modalEliminar').classList.add('open'); }
function cerrarModalEliminar() { document.getElementById('modalEliminar').classList.remove('open'); idEliminar=null; }
async function ejecutarEliminar() {
    try {
        const res=await fetch('api/servicios.php',{method:'DELETE',headers:{'Content-Type':'application/json'},body:JSON.stringify({id:idEliminar})});
        const data=await res.json();
        cerrarModalEliminar();
        if(data.ok) { showToast('🗑 Servicio eliminado'); await cargarServicios(); }
        else mostrarFeedback(data.error||'Error','error');
    } catch(e) { cerrarModalEliminar(); mostrarFeedback('Error de conexión','error'); }
}

// ── Helpers ──
function mostrarFeedback(txt,tipo='ok') {
    const el=document.getElementById('feedback');
    el.textContent=txt;
    el.className=`rounded-2xl px-5 py-4 text-[14px] font-semibold text-center ${tipo==='ok'?'bg-green-50 text-green-800 border border-green-200':'bg-red-50 text-red-800 border border-red-200'}`;
    el.classList.remove('hidden');
    setTimeout(()=>el.classList.add('hidden'),5000);
}
function abrirModalEditarPorId(id) { const s=todosServicios.find(x=>x.id===id); if(s) abrirModalEditar(s); }
function pedirEliminarPorId(id)    { const s=todosServicios.find(x=>x.id===id); if(s) pedirEliminar(s.id,s.titulo); }
function abrirModalValidarPorId(id){ const s=todosServicios.find(x=>x.id===id); if(s) abrirModalValidar(s); }
</script>
</body>
</html>

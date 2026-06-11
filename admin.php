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
<title>NutriSucre · Administración</title>
<?php require_once '_ios_head.php'; ?>
<style>
  .tab-panel { display:none; }
  .tab-panel.activo { display:block; }
  .row-user { display:flex; align-items:center; justify-content:space-between; padding:14px 20px; border-bottom:1px solid rgba(0,0,0,0.05); transition:background .15s; }
  .row-user:hover { background:#f9f9fb; }
  .row-user:last-child { border-bottom:none; }
</style>
</head>
<body>
<?php $paginaActual = 'admin'; require_once '_sidebar.php'; ?>

<header class="ios-header md:pl-64">
  <div class="flex items-center gap-3">
    <button onclick="toggleSidebar()" class="md:hidden ios-btn-icon"><span class="icon" style="font-size:20px">menu</span></button>
    <p class="font-black text-[18px]">Administración</p>
  </div>
  <div class="text-right hidden sm:block">
    <p class="font-semibold text-[14px]"><?= htmlspecialchars($nombre) ?></p>
    <p class="text-[12px] text-[#22c55e] font-semibold">Administrador</p>
  </div>
</header>

<main class="md:pl-64 p-5 md:p-8 max-w-6xl mx-auto space-y-5">

  <!-- Stats -->
  <div id="statsAdmin" class="grid grid-cols-2 md:grid-cols-4 gap-4"></div>

  <!-- Tabs -->
  <div class="seg-control max-w-sm">
    <button class="seg-btn active" id="tabPostulaciones" onclick="cambiarTab('Postulaciones')">Postulaciones</button>
    <button class="seg-btn" id="tabUsuarios"      onclick="cambiarTab('Usuarios')">Usuarios</button>
    <button class="seg-btn" id="tabServicios"     onclick="cambiarTab('Servicios')">
      Servicios <span id="badgePend" class="hidden bg-red-500 text-white text-[10px] rounded-full px-1.5 ml-0.5 font-bold"></span>
    </button>
  </div>

  <div id="feedbackGlobal" class="hidden rounded-2xl px-5 py-4 text-[14px] font-semibold text-center"></div>

  <!-- ══ POSTULACIONES ══ -->
  <div id="panelPostulaciones" class="tab-panel activo space-y-4">
    <div class="flex gap-2 flex-wrap">
      <button onclick="cargarPosts('')"          class="chip active">Todas</button>
      <button onclick="cargarPosts('pendiente')" class="chip">⏳ Pendientes</button>
      <button onclick="cargarPosts('aprobado')"  class="chip">✅ Aprobadas</button>
      <button onclick="cargarPosts('rechazado')" class="chip">❌ Rechazadas</button>
    </div>
    <div id="listaPosts" class="space-y-3"></div>
  </div>

  <!-- ══ USUARIOS ══ -->
  <div id="panelUsuarios" class="tab-panel space-y-4">
    <div class="bg-white rounded-[20px] border border-[var(--border)] p-4 flex items-center gap-3 flex-wrap">
      <div class="relative flex-1 min-w-[180px]">
        <span class="icon absolute left-3 top-1/2 -translate-y-1/2 text-[#8e8e93]" style="font-size:18px">search</span>
        <input id="buscarUser" type="text" placeholder="Buscar por nombre o email..." class="ios-input pl-9 text-[14px]" oninput="filtrarUsuarios()">
      </div>
      <select id="filtroRol" class="ios-input text-[14px]" style="width:auto" onchange="filtrarUsuarios()">
        <option value="">Todos los roles</option>
        <option>Paciente</option><option>Nutricionista</option><option>Administrador</option>
      </select>
      <button onclick="abrirModalUser()" class="ios-btn text-[13px]" style="border-radius:12px;padding:10px 16px">
        <span class="icon" style="font-size:16px">person_add</span> Nuevo
      </button>
    </div>
    <div class="ios-card overflow-hidden">
      <div id="listaUsuarios"></div>
      <p id="paginInfo" class="text-[12px] text-[#8e8e93] px-5 py-3 border-t border-[rgba(0,0,0,0.05)]"></p>
    </div>
  </div>

  <!-- ══ SERVICIOS ══ -->
  <div id="panelServicios" class="tab-panel space-y-4">
    <div class="flex gap-2 flex-wrap items-center justify-between">
      <div class="flex gap-2 flex-wrap">
        <button onclick="cargarSrvsAdmin('')"          class="chip active">Todos</button>
        <button onclick="cargarSrvsAdmin('Pendiente')" class="chip">⏳ Pendientes</button>
        <button onclick="cargarSrvsAdmin('Aprobado')"  class="chip">✅ Aprobados</button>
        <button onclick="cargarSrvsAdmin('Rechazado')" class="chip">❌ Rechazados</button>
      </div>
      <a href="servicios.php" class="text-[#22c55e] text-[13px] font-semibold flex items-center gap-1 hover:opacity-70">
        <span class="icon" style="font-size:16px">open_in_new</span> Vista completa
      </a>
    </div>
    <div id="listaSrvsAdmin" class="space-y-3"></div>
  </div>
</main>

<!-- ══ MODAL: Detalle Postulación ══ -->
<div id="modalPost" class="ios-modal-bg" onclick="if(event.target===this)cerrarModalPost()">
  <div class="ios-modal max-w-2xl">
    <div class="flex justify-between items-center p-6 border-b border-[rgba(0,0,0,0.06)]">
      <div>
        <p id="postNombreModal" class="font-black text-[20px]"></p>
        <p class="text-[13px] text-[#8e8e93]">Reporte de verificación profesional</p>
      </div>
      <button onclick="cerrarModalPost()" class="ios-btn-icon"><span class="icon">close</span></button>
    </div>
    <div id="postContenido" class="p-6 space-y-4 overflow-y-auto max-h-[70vh]"></div>
  </div>
</div>

<!-- ══ MODAL: Crear/Editar Usuario ══ -->
<div id="modalUser" class="ios-modal-bg" onclick="if(event.target===this)cerrarModalUser()">
  <div class="ios-modal max-w-md">
    <div class="flex justify-between items-center p-6 border-b border-[rgba(0,0,0,0.06)]">
      <p id="modalUserTitulo" class="font-black text-[20px]">Nuevo Usuario</p>
      <button onclick="cerrarModalUser()" class="ios-btn-icon"><span class="icon">close</span></button>
    </div>
    <div class="p-6 space-y-4">
      <input type="hidden" id="editId">
      <div>
        <label class="text-[13px] font-semibold text-[#48484a] pl-1 block mb-2">Nombre <span class="text-red-400">*</span></label>
        <input id="campo_nombre" type="text" class="ios-input">
      </div>
      <div>
        <label class="text-[13px] font-semibold text-[#48484a] pl-1 block mb-2">Email <span class="text-red-400">*</span></label>
        <input id="campo_email" type="email" class="ios-input">
      </div>
      <div>
        <label class="text-[13px] font-semibold text-[#48484a] pl-1 block mb-2">Contraseña <span id="labelPass" class="text-[#8e8e93] font-normal"></span></label>
        <input id="campo_pass" type="password" class="ios-input">
      </div>
      <div>
        <label class="text-[13px] font-semibold text-[#48484a] pl-1 block mb-2">Rol <span class="text-red-400">*</span></label>
        <select id="campo_rol" class="ios-input">
          <option>Paciente</option><option>Nutricionista</option><option>Administrador</option>
        </select>
      </div>
      <div>
        <label class="text-[13px] font-semibold text-[#48484a] pl-1 block mb-2">Carnet de Identidad (CI)</label>
        <input id="campo_ci" type="text" class="ios-input" placeholder="Ej: 1234567 CH">
      </div>
      <div>
        <label class="text-[13px] font-semibold text-[#48484a] pl-1 block mb-2">Celular</label>
        <input id="campo_celular" type="text" class="ios-input" placeholder="Ej: 71234567">
      </div>
      <div>
        <label class="text-[13px] font-semibold text-[#48484a] pl-1 block mb-2">Estado</label>
        <select id="campo_estado" class="ios-input">
          <option value="activo">Activo</option>
          <option value="bloqueado">Bloqueado</option>
        </select>
      </div>
      <div id="msgModalUser" class="hidden rounded-2xl px-4 py-3 text-[13px] font-semibold"></div>
    </div>
    <div class="px-6 pb-6 flex gap-3">
      <button onclick="cerrarModalUser()" class="ios-btn-ghost flex-1" style="border-radius:14px">Cancelar</button>
      <button onclick="guardarUsuario()" id="btnGuardarUser" class="ios-btn flex-1" style="border-radius:14px">Guardar</button>
    </div>
  </div>
</div>

<!-- ══ MODAL: Eliminar Usuario ══ -->
<div id="modalElimUser" class="ios-modal-bg" onclick="if(event.target===this)cerrarElimUser()">
  <div class="ios-modal max-w-sm p-7 text-center">
    <span class="icon text-red-400" style="font-size:52px">person_remove</span>
    <p class="font-black text-[20px] mt-3">¿Eliminar usuario?</p>
    <p id="textoElimUser" class="text-[14px] text-[#8e8e93] mt-2 mb-6"></p>
    <div class="flex gap-3">
      <button onclick="cerrarElimUser()" class="ios-btn-ghost flex-1" style="border-radius:14px">Cancelar</button>
      <button onclick="ejecutarElimUser()" class="flex-1 py-3 bg-red-500 text-white rounded-[14px] font-bold text-[14px]">Eliminar</button>
    </div>
  </div>
</div>

<script>
let todosUsers=[], todasPosts=[], idElimUser=null;
const ROL_BADGE={'Administrador':'badge badge-purple','Nutricionista':'badge badge-blue','Paciente':'badge badge-green'};

document.addEventListener('DOMContentLoaded',()=>{ cargarStats(); cargarPosts(''); cargarUsuarios(); cargarSrvsAdmin(''); });

function cambiarTab(name) {
    ['Postulaciones','Usuarios','Servicios'].forEach(t=>{
        document.getElementById('panel'+t).classList.remove('activo');
        document.getElementById('tab'+t).classList.remove('active');
    });
    document.getElementById('panel'+name).classList.add('activo');
    document.getElementById('tab'+name).classList.add('active');
}

async function cargarStats() {
    const [resU,resP]=await Promise.all([fetch('api/usuarios.php'),fetch('api/postulaciones.php')]);
    const [users,posts]=await Promise.all([resU.json(),resP.json()]);
    const pend=Array.isArray(posts)?posts.filter(p=>p.estado==='pendiente').length:0;
    const apro=Array.isArray(posts)?posts.filter(p=>p.estado==='aprobado').length:0;
    document.getElementById('statsAdmin').innerHTML=[
        {l:'Total usuarios',   v:Array.isArray(users)?users.length:0, c:'text-[#1c1c1e]'},
        {l:'Postulaciones',    v:Array.isArray(posts)?posts.length:0,  c:'text-blue-600'},
        {l:'Pendientes revisión',v:pend,  c:'text-amber-500'},
        {l:'Nutricionistas OK',v:apro,  c:'text-[#22c55e]'},
    ].map(s=>`<div class="bg-white rounded-[20px] border border-[var(--border)] p-5 text-center">
        <p class="text-[30px] font-black ${s.c}">${s.v}</p>
        <p class="text-[12px] text-[#8e8e93] font-medium mt-1">${s.l}</p>
    </div>`).join('');
}

// ── Postulaciones ──
async function cargarPosts(estado) {
    const url='api/postulaciones.php'+(estado?'?estado='+estado:'');
    const res=await fetch(url); const data=await res.json();
    todasPosts=Array.isArray(data)?data:[];
    renderPosts(todasPosts);
}
function renderPosts(lista) {
    const c=document.getElementById('listaPosts');
    if(!lista.length) { c.innerHTML='<div class="ios-card p-10 text-center text-[#8e8e93] text-[14px]">No hay postulaciones en este estado.</div>'; return; }
    c.innerHTML=lista.map(p=>{
        const ecls=p.estado==='aprobado'?'badge-green':p.estado==='rechazado'?'badge-red':'badge-yellow';
        const pcls=p.puntaje_tecnico>=70?'text-[#22c55e]':p.puntaje_tecnico>=40?'text-amber-500':'text-red-500';
        return `<div class="ios-card p-5">
            <div class="flex justify-between items-start gap-4 flex-wrap">
                <div class="flex-1">
                    <div class="flex items-center gap-2 mb-1">
                        <p class="font-black text-[17px]">${p.nombre}</p>
                        <span class="badge badge-${ecls==='badge-green'?'green':ecls==='badge-red'?'red':'yellow'}">${p.estado}</span>
                    </div>
                    <p class="text-[13px] text-[#8e8e93]">${p.email}</p>
                    <div class="flex gap-4 mt-2 text-[12px] text-[#8e8e93] flex-wrap">
                        <span>🎓 ${p.universidad||'—'}</span>
                        <span>📋 ${p.titulo_prof||'—'}</span>
                        <span>🔑 ${p.registro_prof||'—'}</span>
                    </div>
                    ${p.alertas?`<div class="mt-2 text-[12px] text-amber-600 bg-amber-50 border border-amber-100 px-3 py-2 rounded-xl">${p.alertas.split('\n').join(' · ')}</div>`:''}
                </div>
                <div class="text-center min-w-[80px]">
                    <p class="text-[11px] text-[#8e8e93] font-semibold">Puntaje</p>
                    <p class="text-[32px] font-black ${pcls}">${p.puntaje_tecnico}</p>
                    <p class="text-[11px] ${pcls} font-semibold">${p.puntaje_tecnico>=70?'✅ Alto':p.puntaje_tecnico>=40?'⚠ Medio':'❌ Bajo'}</p>
                </div>
            </div>
            <div class="flex gap-2 mt-4 pt-4 border-t border-[rgba(0,0,0,0.05)] flex-wrap">
                <button onclick="verReporte(${p.id})" class="ios-btn-ghost text-[13px]" style="border-radius:12px;padding:9px 16px">
                    <span class="icon" style="font-size:16px">description</span> Ver reporte
                </button>
                ${p.estado!=='aprobado'?`<button onclick="revisarPost(${p.id},'aprobado')" class="ios-btn text-[13px]" style="border-radius:12px;padding:9px 16px;background:#22c55e">✅ Aprobar</button>`:''}
                ${p.estado!=='rechazado'?`<button onclick="revisarPost(${p.id},'rechazado')" class="ios-btn text-[13px]" style="border-radius:12px;padding:9px 16px;background:#ef4444;box-shadow:none">❌ Rechazar</button>`:''}
            </div>
        </div>`;
    }).join('');
}
function verReporte(id) {
    const p=todasPosts.find(x=>x.id===id); if(!p) return;
    document.getElementById('postNombreModal').textContent=p.nombre;
    const pcls=p.puntaje_tecnico>=70?'#22c55e':p.puntaje_tecnico>=40?'#f59e0b':'#ef4444';
    const rec=p.puntaje_tecnico>=70?'✅ Recomendado':p.puntaje_tecnico>=40?'⚠ Requiere revisión':'❌ No recomendado';
    let esps=[];try{esps=JSON.parse(p.especialidades||'[]')}catch{}
    document.getElementById('postContenido').innerHTML=`
        <div class="grid grid-cols-2 gap-3">
            <div class="bg-[#f9f9fb] rounded-2xl p-4">
                <p class="text-[11px] text-[#8e8e93] font-bold uppercase tracking-wide">Universidad</p>
                <p class="font-semibold mt-1 text-[14px]">${p.universidad||'—'}</p>
            </div>
            <div class="bg-[#f9f9fb] rounded-2xl p-4">
                <p class="text-[11px] text-[#8e8e93] font-bold uppercase tracking-wide">Título</p>
                <p class="font-semibold mt-1 text-[14px]">${p.titulo_prof||'—'}</p>
            </div>
            <div class="bg-[#f9f9fb] rounded-2xl p-4">
                <p class="text-[11px] text-[#8e8e93] font-bold uppercase tracking-wide">Registro Profesional</p>
                <p class="font-semibold mt-1 text-[14px]">${p.registro_prof||'—'}</p>
            </div>
            <div class="bg-[#f9f9fb] rounded-2xl p-4">
                <p class="text-[11px] text-[#8e8e93] font-bold uppercase tracking-wide">Especialidades</p>
                <p class="font-semibold mt-1 text-[14px]">${esps.map(e=>e.nombre+(e.años?' ('+e.años+' años)':'')).join(', ')||'—'}</p>
            </div>
        </div>
        <div class="rounded-2xl p-5 border-2" style="border-color:${pcls}40;background:${pcls}10">
            <div class="flex items-center gap-4 mb-3">
                <p class="text-[44px] font-black leading-none" style="color:${pcls}">${p.puntaje_tecnico}</p>
                <div>
                    <p class="text-[13px] text-[#8e8e93]">de 100 puntos</p>
                    <p class="font-bold text-[15px]" style="color:${pcls}">${rec}</p>
                </div>
            </div>
            ${p.alertas?`<div class="bg-amber-50 border border-amber-200 rounded-xl p-3 text-[12px] text-amber-700">${p.alertas.split('\n').map(a=>`<p>• ${a}</p>`).join('')}</div>`:'<p class="text-[13px] text-[#22c55e] font-semibold">✅ Sin alertas detectadas</p>'}
        </div>
        <div class="space-y-3">
            <p class="font-bold text-[15px]">Respuestas técnicas</p>
            ${[['Obesidad + Diabetes tipo 2',p.resp_tecnica_1],['Indicadores nutricionales',p.resp_tecnica_2],['Diseño de plan alimenticio',p.resp_tecnica_3],['Desnutrición vs Malnutrición',p.resp_tecnica_4],['Baja adherencia al tratamiento',p.resp_tecnica_5]].map(([q,r],i)=>`
            <div class="bg-[#f9f9fb] rounded-2xl p-4">
                <p class="text-[11px] font-bold text-[#8e8e93] uppercase tracking-wide mb-2">${i+1}. ${q}</p>
                <p class="text-[13px] text-[#48484a] leading-relaxed">${r||'<span class="text-red-400 italic">Sin respuesta</span>'}</p>
            </div>`).join('')}
        </div>
        <div>
            <label class="text-[13px] font-semibold text-[#48484a] pl-1 block mb-2">Notas del administrador</label>
            <textarea id="notasAdmin_${p.id}" rows="2" placeholder="Observaciones internas..." class="ios-input resize-none" style="font-family:inherit">${p.notas_admin||''}</textarea>
        </div>
        <div class="flex gap-3">
            <button onclick="revisarPost(${p.id},'aprobado',true)" class="ios-btn flex-1 text-[13px]" style="border-radius:14px">✅ Aprobar</button>
            <button onclick="revisarPost(${p.id},'rechazado',true)" class="ios-btn flex-1 text-[13px]" style="border-radius:14px;background:#ef4444;box-shadow:none">❌ Rechazar</button>
        </div>`;
    document.getElementById('modalPost').classList.add('open');
}
function cerrarModalPost() { document.getElementById('modalPost').classList.remove('open'); }
async function revisarPost(id,estado,desdeModal=false) {
    const notas=document.getElementById(`notasAdmin_${id}`)?.value||'';
    const res=await fetch('api/postulaciones.php',{method:'PUT',headers:{'Content-Type':'application/json'},body:JSON.stringify({id,estado,notas_admin:notas})});
    const data=await res.json();
    if(data.ok) { mostrarFeedback('✅ '+data.mensaje,'ok'); if(desdeModal) cerrarModalPost(); cargarPosts(''); cargarStats(); }
    else mostrarFeedback(data.error||'Error','error');
}

// ── Usuarios ──
async function cargarUsuarios() {
    const res=await fetch('api/usuarios.php'); const data=await res.json();
    if(!data.error) { todosUsers=data; renderUsers(data); }
}
function filtrarUsuarios() {
    const t=document.getElementById('buscarUser').value.toLowerCase();
    const r=document.getElementById('filtroRol').value;
    renderUsers(todosUsers.filter(u=>(!t||u.nombre.toLowerCase().includes(t)||u.email.toLowerCase().includes(t))&&(!r||u.rol===r)));
}
function renderUsers(lista) {
    const c=document.getElementById('listaUsuarios');
    document.getElementById('paginInfo').textContent=`${lista.length} de ${todosUsers.length} usuarios`;
    if(!lista.length) { c.innerHTML='<div class="px-5 py-8 text-center text-[#8e8e93] text-[14px]">No se encontraron usuarios.</div>'; return; }
    c.innerHTML=lista.map(u=>{
        const fecha=new Date(u.created_at).toLocaleDateString('es-BO',{day:'2-digit',month:'short',year:'numeric'});
        const est = u.estado || 'activo';
        const estBadge = est === 'bloqueado' ? 'badge-red' : 'badge-green';
        const isBlocked = est === 'bloqueado';
        return `<div class="row-user">
            <div class="flex-1 min-w-0">
                <p class="font-semibold text-[14px] truncate">${u.nombre}</p>
                <p class="text-[12px] text-[#8e8e93] truncate">${u.email} ${u.ci ? '· CI: '+u.ci : ''} ${u.celular ? '· Cel: '+u.celular : ''} · ${fecha}</p>
            </div>
            <div class="flex items-center gap-3 ml-3">
                <span class="${ROL_BADGE[u.rol]||'badge badge-gray'}">${u.rol}</span>
                <span class="badge ${estBadge}">${est}</span>
                <button onclick="toggleEstadoUsuario(${u.id})" class="ios-btn-icon" title="${isBlocked ? 'Habilitar usuario' : 'Bloquear usuario'}" style="border-color:${isBlocked ? '#22c55e' : '#fecaca'}">
                    <span class="icon ${isBlocked ? 'text-[#22c55e]' : 'text-red-400'}">${isBlocked ? 'lock_open' : 'lock'}</span>
                </button>
                <button onclick="abrirEditarUser(${u.id})" class="ios-btn-icon" title="Editar">
                    <span class="icon text-[#22c55e]" style="font-size:18px">edit</span>
                </button>
                <button onclick="pedirElimUser(${u.id})" class="ios-btn-icon" title="Eliminar" style="border-color:#fecaca">
                    <span class="icon text-red-400" style="font-size:18px">delete</span>
                </button>
            </div>
        </div>`;
    }).join('');
}
function abrirModalUser() {
    document.getElementById('editId').value='';
    document.getElementById('modalUserTitulo').textContent='Nuevo Usuario';
    document.getElementById('campo_nombre').value='';
    document.getElementById('campo_email').value='';
    document.getElementById('campo_pass').value='';
    document.getElementById('campo_rol').value='Paciente';
    document.getElementById('campo_ci').value='';
    document.getElementById('campo_celular').value='';
    document.getElementById('campo_estado').value='activo';
    document.getElementById('labelPass').textContent='(obligatoria)';
    document.getElementById('msgModalUser').classList.add('hidden');
    document.getElementById('modalUser').classList.add('open');
}
function abrirEditarUser(id) {
    const u=todosUsers.find(x=>x.id===id); if(!u) return;
    document.getElementById('editId').value=u.id;
    document.getElementById('modalUserTitulo').textContent='Editar Usuario';
    document.getElementById('campo_nombre').value=u.nombre;
    document.getElementById('campo_email').value=u.email;
    document.getElementById('campo_pass').value='';
    document.getElementById('campo_rol').value=u.rol;
    document.getElementById('campo_ci').value=u.ci || '';
    document.getElementById('campo_celular').value=u.celular || '';
    document.getElementById('campo_estado').value=u.estado || 'activo';
    document.getElementById('labelPass').textContent='(vacío = no cambiar)';
    document.getElementById('msgModalUser').classList.add('hidden');
    document.getElementById('modalUser').classList.add('open');
}
function cerrarModalUser() { document.getElementById('modalUser').classList.remove('open'); }
async function toggleEstadoUsuario(id) {
    const u=todosUsers.find(x=>x.id===id); if(!u) return;
    const nuevoEstado = (u.estado || 'activo') === 'bloqueado' ? 'activo' : 'bloqueado';
    const body={id:u.id, nombre:u.nombre, email:u.email, rol:u.rol, ci:u.ci, celular:u.celular, estado:nuevoEstado};
    const res=await fetch('api/usuarios.php',{method:'PUT',headers:{'Content-Type':'application/json'},body:JSON.stringify(body)});
    const data=await res.json();
    if(data.ok) { mostrarFeedback('✅ Estado del usuario cambiado a '+nuevoEstado,'ok'); cargarUsuarios(); }
    else mostrarFeedback(data.error||'Error','error');
}
async function guardarUsuario() {
    const id=document.getElementById('editId').value;
    const nombre=document.getElementById('campo_nombre').value.trim();
    const email=document.getElementById('campo_email').value.trim();
    const pass=document.getElementById('campo_pass').value;
    const rol=document.getElementById('campo_rol').value;
    const ci=document.getElementById('campo_ci').value.trim();
    const celular=document.getElementById('campo_celular').value.trim();
    const estado=document.getElementById('campo_estado').value;
    
    if(!nombre||!email) return mostrarMsgUser('Nombre y email son obligatorios');
    if(!id&&!pass) return mostrarMsgUser('La contraseña es obligatoria para nuevos usuarios');
    const btn=document.getElementById('btnGuardarUser');
    btn.disabled=true; btn.textContent='Guardando...';
    const body={nombre,email,rol,ci,celular,estado}; if(id) body.id=parseInt(id); if(pass) body.password=pass;
    const res=await fetch('api/usuarios.php',{method:id?'PUT':'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify(body)});
    const data=await res.json();
    btn.disabled=false; btn.textContent='Guardar';
    if(data.ok) { mostrarFeedback('✅ '+data.mensaje,'ok'); cerrarModalUser(); cargarUsuarios(); cargarStats(); }
    else mostrarMsgUser(data.error||'Error');
}
function mostrarMsgUser(txt) { const el=document.getElementById('msgModalUser'); el.textContent=txt; el.className='rounded-2xl px-4 py-3 text-[13px] font-semibold bg-red-50 text-red-700'; el.classList.remove('hidden'); }
function pedirElimUser(id) { const u=todosUsers.find(x=>x.id===id); if(!u) return; idElimUser=id; document.getElementById('textoElimUser').textContent=`Se eliminará a "${u.nombre}" permanentemente.`; document.getElementById('modalElimUser').classList.add('open'); }
function cerrarElimUser() { document.getElementById('modalElimUser').classList.remove('open'); idElimUser=null; }
async function ejecutarElimUser() {
    const res=await fetch('api/usuarios.php',{method:'DELETE',headers:{'Content-Type':'application/json'},body:JSON.stringify({id:idElimUser})});
    const data=await res.json();
    cerrarElimUser();
    if(data.ok) { mostrarFeedback('✅ '+data.mensaje,'ok'); cargarUsuarios(); cargarStats(); }
    else mostrarFeedback(data.error||'Error','error');
}

// ── Servicios Admin ──
async function cargarSrvsAdmin(estado) {
    const c=document.getElementById('listaSrvsAdmin');
    c.innerHTML='<p class="text-center text-[#8e8e93] text-[14px] py-8">Cargando...</p>';
    const url='api/servicios.php'+(estado?'?estado='+estado:'');
    const res=await fetch(url); const data=await res.json();
    if(!Array.isArray(data)||!data.length) { c.innerHTML='<div class="ios-card p-10 text-center text-[#8e8e93] text-[14px]">No hay servicios en este estado.</div>'; return; }
    const pend=data.filter(s=>s.estado==='Pendiente').length;
    const badge=document.getElementById('badgePend');
    if(pend>0&&!estado) { badge.textContent=pend; badge.classList.remove('hidden'); } else badge.classList.add('hidden');
    const BCLS={'Pendiente':'badge-yellow','Aprobado':'badge-green','Rechazado':'badge-red'};
    c.innerHTML=data.map(s=>`<div class="ios-card p-4 flex items-start justify-between gap-4">
        <div class="flex-1 min-w-0">
            <div class="flex items-center gap-2 mb-1 flex-wrap">
                <span class="badge badge-${BCLS[s.estado]?.replace('badge-','')}">${s.estado}</span>
                <span class="text-[12px] text-[#8e8e93]">${s.categoria}</span>
            </div>
            <p class="font-bold text-[15px] truncate">${s.titulo}</p>
            <p class="text-[12px] text-[#8e8e93]">Por: ${s.nutricionista_nombre} · Bs. ${parseFloat(s.precio).toFixed(2)} · ${s.duracion_semanas} sem.</p>
            ${s.motivo_rechazo?`<p class="text-red-500 text-[12px] mt-1">Motivo: ${s.motivo_rechazo}</p>`:''}
        </div>
        <div class="flex gap-2 flex-shrink-0">
            ${s.estado!=='Aprobado'?`<button onclick="validarSrvAdmin(${s.id},'Aprobado')" class="ios-btn text-[12px]" style="border-radius:10px;padding:8px 14px">✅ Aprobar</button>`:''}
            ${s.estado!=='Rechazado'?`<button onclick="pedirRechazoAdmin(${s.id},'${s.titulo.replace(/'/g,"\\'")}' )" class="ios-btn text-[12px]" style="border-radius:10px;padding:8px 14px;background:#ef4444;box-shadow:none">❌ Rechazar</button>`:''}
        </div>
    </div>`).join('');
}
async function validarSrvAdmin(id,estado,motivo='') {
    const res=await fetch('api/servicios.php?accion=validar',{method:'PUT',headers:{'Content-Type':'application/json'},body:JSON.stringify({id,estado,motivo})});
    const data=await res.json();
    if(data.ok) { mostrarFeedback('✅ '+data.mensaje,'ok'); cargarSrvsAdmin(''); cargarStats(); }
    else mostrarFeedback(data.error||'Error','error');
}
function pedirRechazoAdmin(id,titulo) {
    const m=prompt(`Motivo de rechazo para:\n"${titulo}"\n\n(mínimo 10 caracteres)`);
    if(m===null) return;
    if(!m||m.trim().length<10) { alert('El motivo debe tener al menos 10 caracteres.'); return; }
    validarSrvAdmin(id,'Rechazado',m.trim());
}

// ── Feedback ──
function mostrarFeedback(txt,tipo='ok') {
    const el=document.getElementById('feedbackGlobal');
    el.textContent=txt;
    el.className=`rounded-2xl px-5 py-4 text-[14px] font-semibold text-center ${tipo==='ok'?'bg-green-50 text-green-800 border border-green-200':'bg-red-50 text-red-800 border border-red-200'}`;
    el.classList.remove('hidden');
    showToast(txt);
    setTimeout(()=>el.classList.add('hidden'),5000);
}
}
</script>
</body>
</html>

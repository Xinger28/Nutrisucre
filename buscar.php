<?php
session_start();
if (empty($_SESSION['usuario'])) { header('Location: login.php'); exit; }
$usuario = $_SESSION['usuario'];
$rol     = $usuario['rol'];
$nombre  = $usuario['nombre'];
if ($rol === 'Nutricionista') { header('Location: servicios.php'); exit; }
?>
<!DOCTYPE html>
<html lang="es">
<head>
<title>NutriSucre · Buscar</title>
<?php require_once '_ios_head.php'; ?>
<style>
  .nutri-card { background:white; border-radius:24px; border:1px solid var(--border); overflow:hidden; transition: all .3s cubic-bezier(.34,1.2,.64,1); }
  .nutri-card:hover { transform:translateY(-5px); box-shadow:0 16px 40px rgba(0,0,0,0.10); }
  .star-row { color:#f59e0b; font-size:14px; letter-spacing:1px; }
  .star-interactive { font-size:28px; cursor:pointer; color:#d1d5db; transition:color .15s; }
  .star-interactive.on { color:#f59e0b; }
  .slot-btn { padding:8px 12px; border-radius:10px; border:1.5px solid var(--border); font-size:13px; font-weight:600; cursor:pointer; transition:all .2s ease; background:white; }
  .slot-btn:hover { border-color:var(--green); color:var(--green-dark); }
  .slot-btn.sel { background:var(--green); color:white; border-color:var(--green); }
  .filter-bar { background:rgba(255,255,255,0.8); backdrop-filter:blur(20px); -webkit-backdrop-filter:blur(20px); border-radius:20px; border:1px solid var(--border); }
</style>
</head>
<body>

<?php $paginaActual = 'buscar'; require_once '_sidebar.php'; ?>

<header class="ios-header md:pl-64">
  <div class="flex items-center gap-3">
    <button onclick="toggleSidebar()" class="md:hidden ios-btn-icon"><span class="icon" style="font-size:20px">menu</span></button>
    <p class="font-black text-[18px]">Buscar Nutricionistas</p>
  </div>
  <div class="text-right hidden sm:block">
    <p class="font-semibold text-[14px]"><?= htmlspecialchars($nombre) ?></p>
    <p class="text-[12px] text-[#22c55e] font-semibold"><?= htmlspecialchars($rol) ?></p>
  </div>
</header>

<main class="md:pl-64 p-5 md:p-8 max-w-6xl mx-auto space-y-5">

  <!-- Filtros -->
  <div class="filter-bar p-5 space-y-4">
    <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
      <div class="md:col-span-1 relative">
        <span class="icon absolute left-3.5 top-1/2 -translate-y-1/2 text-[#8e8e93]" style="font-size:18px">search</span>
        <input id="f_nombre" type="text" placeholder="Nombre o especialidad..."
               class="ios-input pl-10 text-[14px]" oninput="filtrarConDebounce()">
      </div>
      <div class="relative">
        <span class="icon absolute left-3.5 top-1/2 -translate-y-1/2 text-[#8e8e93]" style="font-size:18px">payments</span>
        <input id="f_precio" type="number" min="0" placeholder="Precio máx. (Bs.)"
               class="ios-input pl-10 text-[14px]" oninput="filtrarConDebounce()">
      </div>
      <div>
        <select id="f_rating" class="ios-input text-[14px]" onchange="filtrar()">
          <option value="">⭐ Cualquier calificación</option>
          <option value="3">⭐⭐⭐ 3.0+</option>
          <option value="4">⭐⭐⭐⭐ 4.0+</option>
          <option value="4.5">⭐⭐⭐⭐⭐ 4.5+</option>
        </select>
      </div>
    </div>
    <!-- Chips modalidad -->
    <div class="flex gap-2 flex-wrap items-center">
      <span class="text-[12px] font-semibold text-[#8e8e93] mr-1">Modalidad:</span>
      <button onclick="filtrarModalidad('')"          id="mTodas"     class="chip active">Todas</button>
      <button onclick="filtrarModalidad('Virtual')"   id="mVirtual"   class="chip">Virtual</button>
      <button onclick="filtrarModalidad('Presencial')" id="mPresencial" class="chip">Presencial</button>
      <div class="w-px h-4 bg-[#e5e5ea] mx-1"></div>
      <span class="text-[12px] font-semibold text-[#8e8e93] mr-1">Especialidades:</span>
      <?php foreach(['Nutrición Deportiva','Diabetes y Obesidad','Nutrición Infantil','Nutrición Clínica'] as $esp): ?>
      <button onclick="filtrarEsp('<?= $esp ?>')" class="chip text-[12px]"><?= $esp ?></button>
      <?php endforeach; ?>
      <button onclick="limpiarFiltros()" class="text-[12px] text-[#8e8e93] hover:text-red-500 transition-colors ml-1 font-medium">✕ Limpiar</button>
    </div>
  </div>

  <p id="contador" class="text-[13px] text-[#8e8e93] font-medium pl-1"></p>

  <!-- Cards grid -->
  <div id="grid" class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-5">
    <div class="col-span-3 text-center py-16 text-[#8e8e93]">Cargando especialistas...</div>
  </div>
</main>

<!-- ═══ MODAL DETALLE ═══ -->
<div id="modalDetalle" class="ios-modal-bg" onclick="if(event.target===this)cerrarDetalle()">
  <div class="ios-modal p-0 max-w-lg">
    <div class="flex justify-between items-center p-6 border-b border-[rgba(0,0,0,0.06)]">
      <div>
        <p id="det_nombre" class="font-black text-[20px]"></p>
        <p id="det_esp" class="text-[#22c55e] font-semibold text-[14px]"></p>
      </div>
      <button onclick="cerrarDetalle()" class="ios-btn-icon"><span class="icon">close</span></button>
    </div>
    <div class="p-6 space-y-4 overflow-y-auto max-h-[65vh]">
      <div id="det_sello" class="hidden flex items-center gap-2 bg-green-50 border border-green-200 rounded-2xl p-3">
        <span class="icon icon-fill text-[#22c55e]">verified</span>
        <p class="text-[13px] font-semibold text-green-800">Profesional verificado por NutriSucre</p>
      </div>
      <div class="grid grid-cols-2 gap-3">
        <div class="bg-[#f9f9fb] rounded-2xl p-4">
          <p class="text-[11px] text-[#8e8e93] font-semibold uppercase tracking-wide">Experiencia</p>
          <p id="det_exp" class="font-black text-[18px] mt-1">—</p>
        </div>
        <div class="bg-[#f9f9fb] rounded-2xl p-4">
          <p class="text-[11px] text-[#8e8e93] font-semibold uppercase tracking-wide">Calificación</p>
          <p id="det_rating" class="font-black text-[18px] mt-1 text-amber-500">—</p>
        </div>
        <div class="bg-[#f9f9fb] rounded-2xl p-4">
          <p class="text-[11px] text-[#8e8e93] font-semibold uppercase tracking-wide">Precio</p>
          <p id="det_precio" class="font-black text-[18px] mt-1 text-[#22c55e]">—</p>
        </div>
        <div class="bg-[#f9f9fb] rounded-2xl p-4">
          <p class="text-[11px] text-[#8e8e93] font-semibold uppercase tracking-wide">Duración</p>
          <p id="det_duracion" class="font-black text-[18px] mt-1">—</p>
        </div>
      </div>
      <div id="det_bio_bloque" class="hidden">
        <p class="text-[12px] font-semibold text-[#8e8e93] uppercase tracking-wide mb-2">Sobre mí</p>
        <p id="det_bio" class="text-[14px] text-[#48484a] leading-relaxed bg-[#f9f9fb] rounded-2xl p-4"></p>
      </div>
      <div class="grid grid-cols-2 gap-3 text-[13px]">
        <div><p class="text-[#8e8e93] font-semibold">Universidad</p><p id="det_univ" class="font-semibold mt-0.5">—</p></div>
        <div><p class="text-[#8e8e93] font-semibold">Título</p><p id="det_titulo" class="font-semibold mt-0.5">—</p></div>
        <div><p class="text-[#8e8e93] font-semibold">Modalidad</p><p id="det_modalidad" class="font-semibold mt-0.5">—</p></div>
        <div><p class="text-[#8e8e93] font-semibold">Idiomas</p><p id="det_idiomas" class="font-semibold mt-0.5">—</p></div>
      </div>
      <div>
        <p class="text-[12px] font-semibold text-[#8e8e93] uppercase tracking-wide mb-3">Reseñas de pacientes</p>
        <div id="det_resenas" class="space-y-2"></div>
      </div>
    </div>
    <div class="p-5 border-t border-[rgba(0,0,0,0.06)] flex gap-3">
      <button id="det_btn_resena" class="ios-btn-ghost flex-1 text-[14px]" style="border-radius:14px">
        <span class="icon" style="font-size:18px">star</span> Dejar reseña
      </button>
      <button id="det_btn_reservar" class="ios-btn flex-1 text-[14px]" style="border-radius:14px">
        <span class="icon" style="font-size:18px">event</span> Reservar cita
      </button>
    </div>
  </div>
</div>

<!-- ═══ MODAL CITA ═══ -->
<div id="modalCita" class="ios-modal-bg" onclick="if(event.target===this)cerrarModalCita()">
  <div class="ios-modal p-0 max-w-md">
    <div class="flex justify-between items-center p-6 border-b border-[rgba(0,0,0,0.06)]">
      <div>
        <p class="font-black text-[18px]">Agendar Cita</p>
        <p id="cita_nombre" class="text-[#22c55e] font-semibold text-[13px]"></p>
      </div>
      <button onclick="cerrarModalCita()" class="ios-btn-icon"><span class="icon">close</span></button>
    </div>
    <div class="p-6 space-y-4">
      <div class="bg-[#f9f9fb] rounded-2xl p-4 flex justify-between items-center">
        <div>
          <p class="text-[12px] text-[#8e8e93] font-semibold">Especialidad</p>
          <p id="cita_esp" class="font-bold text-[14px] mt-0.5">—</p>
        </div>
        <div class="text-right">
          <p class="text-[12px] text-[#8e8e93] font-semibold">Costo</p>
          <p id="cita_precio" class="font-black text-[16px] text-[#22c55e] mt-0.5">—</p>
        </div>
      </div>
      <div>
        <label class="text-[13px] font-semibold text-[#48484a] pl-1 block mb-2">Fecha de la consulta</label>
        <input id="cita_fecha" type="date" class="ios-input" onchange="cargarSlots()">
      </div>
      <div id="bloqueHorarios" class="hidden space-y-3">
        <p class="text-[13px] font-semibold text-[#48484a] pl-1">Horarios disponibles</p>
        <div id="gridSlots" class="grid grid-cols-4 gap-2"></div>
        <p id="msgSlots" class="hidden text-[13px] text-[#8e8e93] text-center py-3"></p>
      </div>
      <div id="resumenCita" class="hidden bg-green-50 border border-green-200 rounded-2xl p-4">
        <div class="flex items-center gap-2 mb-1">
          <span class="icon icon-fill text-[#22c55e]" style="font-size:18px">check_circle</span>
          <p class="text-[13px] font-bold text-green-800">Resumen de tu cita</p>
        </div>
        <p id="resumenTexto" class="text-[13px] text-green-700"></p>
      </div>
      <div id="msgCita" class="hidden rounded-2xl px-4 py-3 text-[13px] font-semibold"></div>
    </div>
    <div class="px-6 pb-6">
      <button onclick="confirmarCita()" id="btnConfirmarCita" disabled
              class="ios-btn w-full opacity-50" style="border-radius:14px">
        <span class="icon" style="font-size:18px">event_available</span> Confirmar cita
      </button>
    </div>
  </div>
</div>

<!-- ═══ MODAL RESEÑA ═══ -->
<div id="modalResena" class="ios-modal-bg" onclick="if(event.target===this)cerrarResena()">
  <div class="ios-modal p-6 max-w-sm">
    <div class="flex justify-between items-center mb-5">
      <p class="font-black text-[18px]">Dejar reseña</p>
      <button onclick="cerrarResena()" class="ios-btn-icon"><span class="icon">close</span></button>
    </div>
    <p class="text-[14px] text-[#48484a] mb-4">Tu reseña para <strong id="resenaNombre"></strong></p>
    <div class="flex justify-center gap-2 mb-2" id="estrellas">
      <?php for($i=1;$i<=5;$i++): ?>
      <span class="star-interactive" onclick="setEstrella(<?=$i?>)" onmouseover="hoverEstrella(<?=$i?>)" onmouseout="unhoverEstrella()">★</span>
      <?php endfor; ?>
    </div>
    <p id="textoEstrellas" class="text-center text-[13px] text-[#8e8e93] mb-4">Selecciona una calificación</p>
    <textarea id="resenaComentario" rows="3" placeholder="¿Qué te pareció la consulta? (opcional)"
              class="ios-input resize-none mb-4" style="font-family:inherit"></textarea>
    <div id="msgResena" class="hidden rounded-2xl px-4 py-3 text-[13px] font-semibold mb-4"></div>
    <button onclick="enviarResena()" class="ios-btn w-full" style="border-radius:14px">
      <span class="icon" style="font-size:18px">star</span> Publicar reseña
    </button>
  </div>
</div>

<script>
let todosLosNutri = [], nutriSel = null, horaSel = null, calActual = 0, modFiltro = '', debTimer = null;
const TEXTOS_STAR = ['','Malo','Regular','Bueno','Muy bueno','Excelente'];

document.addEventListener('DOMContentLoaded', () => {
    const man = new Date(); man.setDate(man.getDate()+1);
    document.getElementById('cita_fecha').min = man.toISOString().split('T')[0];
    cargarNutri();
});

async function cargarNutri(p = {}) {
    const qs = new URLSearchParams();
    if (p.nombre)     qs.set('nombre',     p.nombre);
    if (p.precio_max) qs.set('precio_max', p.precio_max);
    if (p.rating_min) qs.set('rating_min', p.rating_min);
    if (p.modalidad)  qs.set('modalidad',  p.modalidad);
    const res  = await fetch('api/nutricionistas.php?' + qs);
    const data = await res.json();
    todosLosNutri = Array.isArray(data) ? data : [];
    renderCards(todosLosNutri);
}
function filtrarConDebounce() { clearTimeout(debTimer); debTimer = setTimeout(filtrar, 350); }
function filtrar() {
    cargarNutri({ nombre: document.getElementById('f_nombre').value.trim(),
                  precio_max: document.getElementById('f_precio').value,
                  rating_min: document.getElementById('f_rating').value,
                  modalidad: modFiltro });
}
function filtrarModalidad(m) {
    modFiltro = m;
    document.querySelectorAll('#mTodas,#mVirtual,#mPresencial').forEach(b => b.classList.remove('active'));
    const id = m === '' ? 'mTodas' : m === 'Virtual' ? 'mVirtual' : 'mPresencial';
    document.getElementById(id).classList.add('active');
    filtrar();
}
function filtrarEsp(e) { document.getElementById('f_nombre').value = e; filtrar(); }
function limpiarFiltros() {
    document.getElementById('f_nombre').value = '';
    document.getElementById('f_precio').value = '';
    document.getElementById('f_rating').value = '';
    filtrarModalidad('');
}

function renderCards(lista) {
    const g = document.getElementById('grid');
    document.getElementById('contador').textContent = lista.length > 0
        ? `${lista.length} especialista${lista.length !== 1 ? 's' : ''} encontrado${lista.length !== 1 ? 's' : ''}`
        : '';
    if (lista.length === 0) {
        g.innerHTML = `<div class="col-span-3 text-center py-16">
            <span class="icon text-[#d1d5db]" style="font-size:64px">person_search</span>
            <p class="text-[#8e8e93] mt-3 text-[15px]">No se encontraron especialistas.</p>
            <button onclick="limpiarFiltros()" class="ios-btn mt-4 text-[13px]" style="border-radius:12px;padding:10px 20px">Limpiar filtros</button>
        </div>`;
        return;
    }
    g.innerHTML = lista.map((n,i) => {
        const stars = Math.round(n.rating || 5);
        const starStr = '★'.repeat(stars) + '☆'.repeat(5-stars);
        const modBadge = n.modalidad === 'Ambas' ? 'badge-purple' : n.modalidad === 'Virtual' ? 'badge-blue' : 'badge-yellow';
        const modLabel = n.modalidad === 'Ambas' ? 'Virtual · Presencial' : n.modalidad;
        return `<div class="nutri-card fade-up" style="animation-delay:${i*0.05}s">
            <div class="bg-gradient-to-br from-[#f0fdf4] to-[#dcfce7] p-5 flex items-center gap-4">
                <div class="w-14 h-14 bg-white rounded-[18px] shadow-sm flex items-center justify-center text-3xl flex-shrink-0">
                    ${n.foto ? `<img src="${n.foto}" class="w-full h-full object-cover rounded-[18px]">` : '🥑'}
                </div>
                <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-1.5">
                        <p class="font-black text-[16px] truncate">${n.nombre}</p>
                        <span class="icon icon-fill text-[#22c55e]" style="font-size:16px">verified</span>
                    </div>
                    <p class="text-[#22c55e] font-semibold text-[13px] truncate">${n.especialidad}</p>
                    <p class="text-amber-400 text-[13px] mt-0.5">${starStr} <span class="text-[#8e8e93] text-[11px]">(${n.total_resenas || 0})</span></p>
                </div>
            </div>
            <div class="p-5">
                <div class="flex flex-wrap gap-1.5 mb-4">
                    <span class="badge ${modBadge}">${modLabel}</span>
                    ${n.experiencia_años ? `<span class="badge badge-gray">${n.experiencia_años} años exp.</span>` : ''}
                    ${n.pacientes_exit  ? `<span class="badge badge-gray">+${n.pacientes_exit} pacientes</span>` : ''}
                </div>
                <div class="flex items-end justify-between mb-4">
                    <div>
                        <p class="text-[11px] text-[#8e8e93] font-semibold">Consulta desde</p>
                        <p class="text-[26px] font-black text-[#22c55e] leading-tight">Bs. ${n.precio}</p>
                    </div>
                    <p class="text-[12px] text-[#8e8e93] font-medium">${n.duracion_consulta || 60} min</p>
                </div>
                <div class="flex gap-2">
                    <button onclick="abrirModalCita(${n.id})" class="ios-btn flex-1 text-[13px]" style="border-radius:12px;padding:12px 16px">
                        <span class="icon" style="font-size:16px">event</span> Reservar
                    </button>
                    <button onclick="verDetalle(${n.id})" class="ios-btn-icon" title="Ver perfil completo">
                        <span class="icon" style="font-size:18px">info</span>
                    </button>
                    <button onclick="abrirResenaPorId(${n.id})" class="ios-btn-icon" title="Dejar reseña">
                        <span class="icon" style="font-size:18px">star</span>
                    </button>
                </div>
            </div>
        </div>`;
    }).join('');
}

async function verDetalle(id) {
    const res = await fetch(`api/nutricionistas.php?id=${id}`);
    const n   = await res.json();
    if (n.error) return;
    document.getElementById('det_nombre').textContent    = n.nombre;
    document.getElementById('det_esp').textContent       = n.especialidad;
    document.getElementById('det_exp').textContent       = n.experiencia_años ? n.experiencia_años + ' años' : '—';
    document.getElementById('det_pac').textContent       = n.pacientes_exit ? '+' + n.pacientes_exit : '—';
    document.getElementById('det_rating').textContent    = (n.rating || '5.0') + ' ★';
    document.getElementById('det_univ').textContent      = n.universidad || '—';
    document.getElementById('det_titulo').textContent    = n.titulo || '—';
    document.getElementById('det_modalidad').textContent = n.modalidad || '—';
    document.getElementById('det_idiomas').textContent   = n.idiomas || 'Español';
    document.getElementById('det_precio').textContent    = `Bs. ${n.precio}`;
    document.getElementById('det_duracion').textContent  = `${n.duracion_consulta || 60} min`;
    const bioBlq = document.getElementById('det_bio_bloque');
    if (n.biografia) { document.getElementById('det_bio').textContent = n.biografia; bioBlq.classList.remove('hidden'); }
    else bioBlq.classList.add('hidden');
    document.getElementById('det_sello').classList.toggle('hidden', n.estado_verificacion !== 'aprobado');
    const resRes = await fetch(`api/resenas.php?nutricionista_id=${id}`);
    const resenas = await resRes.json();
    const lr = document.getElementById('det_resenas');
    if (Array.isArray(resenas) && resenas.length > 0) {
        lr.innerHTML = resenas.map(r => `<div class="bg-[#f9f9fb] rounded-2xl p-3">
            <div class="flex justify-between mb-1">
                <span class="font-semibold text-[13px]">${r.paciente}</span>
                <span class="text-amber-400 text-[12px]">${'★'.repeat(r.calificacion)}${'☆'.repeat(5-r.calificacion)}</span>
            </div>
            ${r.comentario ? `<p class="text-[#48484a] text-[12px]">${r.comentario}</p>` : ''}
        </div>`).join('');
    } else { lr.innerHTML = '<p class="text-[#8e8e93] text-[13px] text-center py-4">Aún no hay reseñas.</p>'; }
    document.getElementById('det_btn_reservar').onclick = () => { cerrarDetalle(); abrirModalCita(id); };
    document.getElementById('det_btn_resena').onclick   = () => { cerrarDetalle(); abrirResenaPorId(id); };
    document.getElementById('modalDetalle').classList.add('open');
}
function cerrarDetalle() { document.getElementById('modalDetalle').classList.remove('open'); }

async function abrirModalCita(id) {
    nutriSel = todosLosNutri.find(n => n.id === id);
    if (!nutriSel) { const r = await fetch(`api/nutricionistas.php?id=${id}`); nutriSel = await r.json(); }
    document.getElementById('cita_nombre').textContent  = nutriSel.nombre;
    document.getElementById('cita_esp').textContent     = nutriSel.especialidad;
    document.getElementById('cita_precio').textContent  = `Bs. ${nutriSel.precio} / consulta`;
    horaSel = null;
    document.getElementById('cita_fecha').value = '';
    document.getElementById('bloqueHorarios').classList.add('hidden');
    document.getElementById('resumenCita').classList.add('hidden');
    document.getElementById('btnConfirmarCita').disabled = true;
    document.getElementById('btnConfirmarCita').style.opacity = '0.5';
    document.getElementById('msgCita').classList.add('hidden');
    document.getElementById('modalCita').classList.add('open');
}
function cerrarModalCita() { document.getElementById('modalCita').classList.remove('open'); nutriSel = null; }

async function cargarSlots() {
    const fecha = document.getElementById('cita_fecha').value;
    if (!fecha || !nutriSel) return;
    const grid = document.getElementById('gridSlots'), msgS = document.getElementById('msgSlots');
    document.getElementById('bloqueHorarios').classList.remove('hidden');
    grid.innerHTML = '<div class="col-span-4 text-center text-[#8e8e93] text-[13px] py-3">Cargando horarios...</div>';
    msgS.classList.add('hidden');
    horaSel = null;
    document.getElementById('btnConfirmarCita').disabled = true;
    document.getElementById('btnConfirmarCita').style.opacity = '0.5';
    document.getElementById('resumenCita').classList.add('hidden');
    const res  = await fetch(`api/postulaciones.php?accion=disponibilidad&nutri_id=${nutriSel.id}&fecha=${fecha}`);
    const data = await res.json();
    if (data.error || !data.slots || data.slots.length === 0) {
        grid.innerHTML = '';
        msgS.textContent = data.error || '😔 No hay horarios disponibles para esta fecha.';
        msgS.classList.remove('hidden');
        return;
    }
    grid.innerHTML = data.slots.map(h => `<button class="slot-btn" onclick="seleccionarSlot('${h}',this)">${h}</button>`).join('');
}
function seleccionarSlot(hora, btn) {
    document.querySelectorAll('.slot-btn').forEach(b => b.classList.remove('sel'));
    btn.classList.add('sel');
    horaSel = hora;
    const [y,m,d] = document.getElementById('cita_fecha').value.split('-');
    document.getElementById('resumenTexto').textContent = `${d}/${m}/${y} a las ${hora} · ${nutriSel.nombre} · Bs. ${nutriSel.precio}`;
    document.getElementById('resumenCita').classList.remove('hidden');
    const btn2 = document.getElementById('btnConfirmarCita');
    btn2.disabled = false; btn2.style.opacity = '1';
}
async function confirmarCita() {
    const fecha = document.getElementById('cita_fecha').value;
    if (!fecha || !horaSel) return mostrarMsgCita('Selecciona fecha y horario','error');
    const btn = document.getElementById('btnConfirmarCita');
    btn.disabled = true; btn.textContent = 'Guardando...';
    const res  = await fetch('api/citas.php', {
        method:'POST', headers:{'Content-Type':'application/json'},
        body: JSON.stringify({ nutricionista_id: nutriSel.id, fecha, hora: horaSel })
    });
    const data = await res.json();
    btn.disabled = false; btn.innerHTML = '<span class="icon" style="font-size:18px">event_available</span> Confirmar cita';
    if (data.ok) {
        mostrarMsgCita('✅ ' + data.mensaje, 'ok');
        showToast('✅ Cita agendada correctamente');
        setTimeout(cerrarModalCita, 2000);
    } else { mostrarMsgCita(data.error || 'Error al agendar','error'); cargarSlots(); }
}
function mostrarMsgCita(txt, tipo) {
    const el = document.getElementById('msgCita');
    el.textContent = txt;
    el.className = `rounded-2xl px-4 py-3 text-[13px] font-semibold ${tipo==='ok'?'bg-green-50 text-green-800':'bg-red-50 text-red-700'}`;
    el.classList.remove('hidden');
}

function abrirResena(id, nm) {
    nutriSel = { id, nombre: nm };
    calActual = 0; actualizarEstrellas(0);
    document.getElementById('resenaNombre').textContent   = nm;
    document.getElementById('resenaComentario').value     = '';
    document.getElementById('textoEstrellas').textContent = 'Selecciona una calificación';
    document.getElementById('msgResena').classList.add('hidden');
    document.getElementById('modalResena').classList.add('open');
}
function abrirResenaPorId(id) { const n = todosLosNutri.find(x => x.id === id); if(n) abrirResena(n.id, n.nombre); }
function cerrarResena() { document.getElementById('modalResena').classList.remove('open'); }
function setEstrella(n) { calActual = n; actualizarEstrellas(n); document.getElementById('textoEstrellas').textContent = TEXTOS_STAR[n]; }
function hoverEstrella(n)  { actualizarEstrellas(n, true); }
function unhoverEstrella() { actualizarEstrellas(calActual); }
function actualizarEstrellas(n, hover=false) {
    document.querySelectorAll('#estrellas .star-interactive').forEach((s,i) => {
        s.classList.toggle('on', i < n);
    });
}
async function enviarResena() {
    if (!calActual) return mostrarMsgR('Selecciona una calificación','error');
    const res  = await fetch('api/resenas.php', {
        method:'POST', headers:{'Content-Type':'application/json'},
        body: JSON.stringify({ nutricionista_id: nutriSel.id, calificacion: calActual,
                               comentario: document.getElementById('resenaComentario').value })
    });
    const data = await res.json();
    if (data.ok) { mostrarMsgR('✅ Reseña publicada','ok'); showToast('⭐ Reseña publicada'); setTimeout(cerrarResena, 1500); filtrar(); }
    else mostrarMsgR(data.error || 'Error al enviar','error');
}
function mostrarMsgR(txt, tipo) {
    const el = document.getElementById('msgResena');
    el.textContent = txt;
    el.className = `rounded-2xl px-4 py-3 text-[13px] font-semibold ${tipo==='ok'?'bg-green-50 text-green-800':'bg-red-50 text-red-700'}`;
    el.classList.remove('hidden');
}
</script>
</body>
</html>

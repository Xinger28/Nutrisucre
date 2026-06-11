<?php
// ============================================================
//  planes.php  —  Planes nutricionales
//  Paciente: ve sus planes activos
//  Nutricionista/Admin: puede crear y gestionar planes
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
<title>NutriSucre · Planes Nutricionales</title>
<?php require_once '_ios_head.php'; ?>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<style>
  .macro-bar { height:7px; border-radius:4px; transition:width .6s ease; }
</style>
<body>

<?php $paginaActual = 'planes'; require_once '_sidebar.php'; ?>

<!-- ======= HEADER ======= -->
<header class="ios-header md:pl-64">
  <div class="flex items-center gap-3">
    <button onclick="toggleSidebar()" class="md:hidden ios-btn-icon"><span class="icon" style="font-size:20px">menu</span></button>
    <p class="font-black text-[18px]"><?= in_array($rol,['Nutricionista','Administrador'])?'Gestión de Planes':'Mis Planes Nutricionales' ?></p>
  </div>
  <div class="flex items-center gap-3">
    <?php if (in_array($rol,['Nutricionista','Administrador'])): ?>
    <button onclick="abrirModalPlan()" class="ios-btn text-[13px]" style="border-radius:12px;padding:10px 18px">
      <span class="icon" style="font-size:16px">add</span> Nuevo plan
    </button>
    <?php endif; ?>
    <div class="text-right hidden sm:block">
      <p class="font-semibold text-[14px]"><?= htmlspecialchars($nombre) ?></p>
      <p class="text-[12px] text-[#22c55e] font-semibold"><?= htmlspecialchars($rol) ?></p>
    </div>
  </div>
</header>

<!-- ======= CONTENIDO ======= -->
<main class="md:pl-64 p-5 md:p-8 max-w-6xl mx-auto space-y-5">

  <!-- Filtro por paciente (solo para nutricionista/admin) -->
  <?php if (in_array($rol, ['Nutricionista','Administrador'])): ?>
  <div class="bg-white rounded-[20px] border border-[var(--border)] p-4 flex items-center gap-4">
    <label class="text-sm font-semibold text-gray-600 whitespace-nowrap">Ver planes de paciente:</label>
    <select id="selectPaciente" onchange="cambiarPaciente(this.value)"
            class="ios-input text-[14px] flex-1">
      <option value="">Cargando pacientes...</option>
    </select>
  </div>
  <?php endif; ?>

  <!-- Grid de tarjetas de planes -->
  <div id="containerPlanes" class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    <div class="col-span-2 text-center py-12 text-gray-400">Cargando planes...</div>
  </div>
</main>

<!-- ======= MODAL: Crear plan (solo nutricionista/admin) ======= -->
<?php if (in_array($rol, ['Nutricionista','Administrador'])): ?>
<div id="modalPlan" class="ios-modal-bg">
  <div class="bg-white rounded-3xl w-full max-w-2xl p-8 max-h-[90vh] overflow-y-auto">
    <div class="flex justify-between items-center mb-6">
      <h3 class="text-2xl font-bold">Nuevo Plan Nutricional</h3>
      <button onclick="cerrarModalPlan()">
        <span class="icon" style="font-size:20px">close</span>
      </button>
    </div>

    <div class="space-y-4">
      <div>
        <label class="block text-sm font-semibold mb-1">Paciente *</label>
        <select id="plan_paciente" class="ios-input">
          <option value="">Selecciona un paciente</option>
        </select>
      </div>
      <div>
        <label class="block text-sm font-semibold mb-1">Título del plan *</label>
        <input id="plan_titulo" type="text" placeholder="ej: Plan de descenso de peso - Fase 1"
               class="ios-input">
      </div>
      <div>
        <label class="block text-sm font-semibold mb-1">Descripción</label>
        <textarea id="plan_desc" rows="3" placeholder="Objetivos, indicaciones generales..."
                  class="w-full border rounded-2xl px-4 py-3 focus:border-[#22c55e] outline-none resize-none"></textarea>
      </div>

      <!-- Macronutrientes en grid -->
      <div>
        <p class="text-sm font-semibold mb-2">Macronutrientes objetivo (dejar vacío si no aplica)</p>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
          <div>
            <label class="block text-xs text-gray-500 mb-1">Calorías (kcal)</label>
            <input id="plan_kcal" type="number" min="0" placeholder="1600"
                   class="w-full border rounded-xl px-3 py-2 text-sm focus:border-[#22c55e] outline-none">
          </div>
          <div>
            <label class="block text-xs text-gray-500 mb-1">Proteínas (g)</label>
            <input id="plan_prot" type="number" min="0" placeholder="120"
                   class="w-full border rounded-xl px-3 py-2 text-sm focus:border-[#22c55e] outline-none">
          </div>
          <div>
            <label class="block text-xs text-gray-500 mb-1">Carbohidratos (g)</label>
            <input id="plan_carb" type="number" min="0" placeholder="150"
                   class="w-full border rounded-xl px-3 py-2 text-sm focus:border-[#22c55e] outline-none">
          </div>
          <div>
            <label class="block text-xs text-gray-500 mb-1">Grasas (g)</label>
            <input id="plan_gras" type="number" min="0" placeholder="45"
                   class="w-full border rounded-xl px-3 py-2 text-sm focus:border-[#22c55e] outline-none">
          </div>
        </div>
      </div>

      <div class="grid grid-cols-2 gap-4">
        <div>
          <label class="block text-sm font-semibold mb-1">Fecha de inicio</label>
          <input id="plan_fecha" type="date" class="ios-input">
        </div>
        <div>
          <label class="block text-sm font-semibold mb-1">Duración (semanas)</label>
          <input id="plan_dur" type="number" min="1" max="52" value="4"
                 class="ios-input">
        </div>
      </div>
    </div>

    <div id="msgPlan" class="hidden mt-4 px-4 py-3 rounded-xl text-sm font-medium"></div>

    <div class="flex gap-3 mt-6">
      <button onclick="cerrarModalPlan()" class="flex-1 py-3 border rounded-2xl font-semibold hover:bg-gray-50">Cancelar</button>
      <button onclick="crearPlan()" id="btnCrearPlan"
              class="flex-1 py-3 bg-[#22c55e] text-white rounded-2xl font-bold hover:bg-[#16a34a] transition-colors">
        Crear plan
      </button>
    </div>
  </div>
</div>
<?php endif; ?>

<!-- ======= MODAL: Ver detalle del plan ======= -->
<div id="modalDetalle" class="ios-modal-bg">
  <div class="bg-white rounded-3xl w-full max-w-lg p-8">
    <div class="flex justify-between items-center mb-4">
      <h3 id="detalleTitulo" class="text-xl font-bold"></h3>
      <button onclick="cerrarDetalle()">
        <span class="icon" style="font-size:20px">close</span>
      </button>
    </div>
    <div id="detalleContenido" class="space-y-3 text-sm text-gray-700"></div>
    <button onclick="cerrarDetalle()" class="mt-6 w-full bg-[#22c55e] text-white py-3 rounded-2xl font-semibold">Cerrar</button>
  </div>
</div>

<!-- ======= JAVASCRIPT ======= -->
<script>
// Rol del usuario actual (viene del PHP, lo usamos en JS)
const ROL = '<?= $rol ?>';
let pacienteIdActual = <?= $usuario['id'] ?>;
let todosLosPlanes = [];

document.addEventListener('DOMContentLoaded', () => {
    document.getElementById('plan_fecha') && (document.getElementById('plan_fecha').value = new Date().toISOString().split('T')[0]);

    if (ROL === 'Paciente') {
        // Los pacientes solo ven sus propios planes
        cargarPlanes(pacienteIdActual);
    } else {
        // Nutricionistas/Admin primero cargan la lista de pacientes
        cargarPacientes();
    }
});

// ──────────────────────────────────────────────
//  Cargar pacientes para el select (AJAX)
// ──────────────────────────────────────────────
async function cargarPacientes() {
    try {
        const res  = await fetch('api/usuarios.php');
        const data = await res.json();

        if (data.error) {
            document.getElementById('containerPlanes').innerHTML = `<div class="col-span-2 text-center py-12 text-red-400">Error: ${data.error}</div>`;
            return;
        }

        // Filtrar solo pacientes
        const pacientes = data.filter(u => u.rol === 'Paciente');

        const sel = document.getElementById('selectPaciente');
        const selModal = document.getElementById('plan_paciente');

        const opcs = pacientes.map(p => `<option value="${p.id}">${p.nombre}</option>`).join('');
        if (sel) sel.innerHTML = '<option value="">— Selecciona paciente —</option>' + opcs;
        if (selModal) selModal.innerHTML = '<option value="">Selecciona un paciente</option>' + opcs;

        // Cargar planes del primer paciente automaticamente
        if (pacientes.length > 0) {
            if (sel) sel.value = pacientes[0].id;
            pacienteIdActual = pacientes[0].id;
            cargarPlanes(pacientes[0].id);
        } else {
            document.getElementById('containerPlanes').innerHTML =
                '<div class="col-span-2 text-center py-12 text-gray-400">No hay pacientes registrados aún.</div>';
        }
    } catch(e) {
        console.error(e);
        document.getElementById('containerPlanes').innerHTML = '<div class="col-span-2 text-center py-12 text-red-400">Error de conexión al cargar pacientes.</div>';
    }
}

function cambiarPaciente(id) {
    if (!id) return;
    pacienteIdActual = parseInt(id);
    cargarPlanes(pacienteIdActual);
}

// ──────────────────────────────────────────────
//  Cargar planes del paciente (AJAX)
// ──────────────────────────────────────────────
async function cargarPlanes(pacienteId) {
    const container = document.getElementById('containerPlanes');
    container.innerHTML = '<div class="col-span-2 text-center py-8 text-gray-400">Cargando...</div>';

    try {
        const res  = await fetch(`api/planes.php?paciente_id=${pacienteId}`);
        const data = await res.json();

        if (data.error) {
            container.innerHTML = `<div class="col-span-2 text-center py-12 text-red-400">${data.error}</div>`;
            return;
        }

        todosLosPlanes = Array.isArray(data) ? data : [];

        if (todosLosPlanes.length === 0) {
            container.innerHTML = `
                <div class="col-span-2 text-center py-16">
                    <span class="icon text-5xl text-gray-300">restaurant_menu</span>
                    <p class="text-gray-400 mt-3">Este paciente aún no tiene planes asignados.</p>
                </div>`;
            return;
        }

        container.innerHTML = todosLosPlanes.map(p => tarjetaPlan(p)).join('');
    } catch(e) {
        console.error(e);
        container.innerHTML = '<div class="col-span-2 text-center py-12 text-red-400">Error de conexión al cargar planes.</div>';
    }
}

// ──────────────────────────────────────────────
//  HTML de la tarjeta de plan
// ──────────────────────────────────────────────
function tarjetaPlan(p) {
    const estadoColor = {
        'activo':     'bg-green-100 text-green-700',
        'finalizado': 'bg-gray-100 text-gray-600',
        'pausado':    'bg-yellow-100 text-yellow-700',
    };

    // Calcular barras de macros proporcionales (si existen)
    const totalMacros = (p.proteinas || 0) * 4 + (p.carbohidratos || 0) * 4 + (p.grasas || 0) * 9;
    const barras = totalMacros > 0 ? `
        <div class="mt-4 space-y-2">
            <p class="text-xs font-semibold text-gray-500 mb-2">DISTRIBUCIÓN DE MACROS</p>
            ${p.proteinas ? `
            <div class="flex items-center gap-2 text-xs">
                <span class="w-20 text-gray-500">Proteínas</span>
                <div class="flex-1 bg-gray-100 rounded-full h-2">
                    <div class="macro-bar bg-blue-500" style="width:${Math.round(p.proteinas*4/totalMacros*100)}%"></div>
                </div>
                <span class="w-12 text-right font-medium">${p.proteinas}g</span>
            </div>` : ''}
            ${p.carbohidratos ? `
            <div class="flex items-center gap-2 text-xs">
                <span class="w-20 text-gray-500">Carbos</span>
                <div class="flex-1 bg-gray-100 rounded-full h-2">
                    <div class="macro-bar bg-amber-500" style="width:${Math.round(p.carbohidratos*4/totalMacros*100)}%"></div>
                </div>
                <span class="w-12 text-right font-medium">${p.carbohidratos}g</span>
            </div>` : ''}
            ${p.grasas ? `
            <div class="flex items-center gap-2 text-xs">
                <span class="w-20 text-gray-500">Grasas</span>
                <div class="flex-1 bg-gray-100 rounded-full h-2">
                    <div class="macro-bar bg-red-400" style="width:${Math.round(p.grasas*9/totalMacros*100)}%"></div>
                </div>
                <span class="w-12 text-right font-medium">${p.grasas}g</span>
            </div>` : ''}
        </div>` : '';

    return `
        <div class="bg-white rounded-3xl shadow-sm border p-6">
            <div class="flex justify-between items-start mb-3">
                <div class="flex-1 mr-3">
                    <h3 class="font-bold text-lg leading-tight">${p.titulo}</h3>
                    <p class="text-[#22c55e] text-sm mt-1">Por: ${p.nutricionista}</p>
                </div>
                <span class="px-3 py-1 rounded-full text-xs font-medium whitespace-nowrap ${estadoColor[p.estado] || 'bg-gray-100 text-gray-600'}">
                    ${p.estado}
                </span>
            </div>

            ${p.descripcion ? `<p class="text-gray-600 text-sm mb-3 leading-relaxed">${p.descripcion}</p>` : ''}

            <div class="flex gap-4 text-sm text-gray-500 mb-3 flex-wrap">
                <span>📅 Inicio: ${p.fecha_inicio}</span>
                <span>⏱ ${p.duracion_semanas} semanas</span>
                ${p.calorias ? `<span>🔥 ${p.calorias} kcal/día</span>` : ''}
            </div>

            ${barras}

            <div class="flex gap-2 mt-4">
                <button onclick='descargarPDF(${p.id})' class="py-2 border rounded-xl text-xs font-medium hover:bg-gray-50 transition-colors flex items-center justify-center gap-1" title="Descargar PDF"><span class="icon text-base">download</span>PDF</button>
                <button onclick="verDetallePorId(${p.id})"
                        class="flex-1 py-2 border rounded-xl text-sm font-medium hover:bg-gray-50 transition-colors">
                    Ver detalle
                </button>
                ${ROL !== 'Paciente' ? `
                <select onchange="cambiarEstado(${p.id}, this.value)"
                        class="py-2 px-3 border rounded-xl text-sm focus:border-[#22c55e] outline-none">
                    <option value="activo"     ${p.estado==='activo'     ?'selected':''}>Activo</option>
                    <option value="pausado"    ${p.estado==='pausado'    ?'selected':''}>Pausado</option>
                    <option value="finalizado" ${p.estado==='finalizado' ?'selected':''}>Finalizado</option>
                </select>` : ''}
            </div>
        </div>
    `;
}

// ──────────────────────────────────────────────
//  Crear plan (AJAX POST)
// ──────────────────────────────────────────────
function abrirModalPlan()  { document.getElementById('modalPlan').classList.add('open'); }
function cerrarModalPlan() { document.getElementById('modalPlan').classList.remove('open'); }

async function crearPlan() {
    const paciente = document.getElementById('plan_paciente').value;
    const titulo   = document.getElementById('plan_titulo').value.trim();

    if (!paciente || !titulo) {
        return mostrarMsgPlan('Paciente y título son obligatorios');
    }

    const btn = document.getElementById('btnCrearPlan');
    btn.disabled = true; btn.textContent = 'Creando...';

    const res = await fetch('api/planes.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
            paciente_id:      parseInt(paciente),
            titulo,
            descripcion:      document.getElementById('plan_desc').value,
            calorias:         document.getElementById('plan_kcal').value,
            proteinas:        document.getElementById('plan_prot').value,
            carbohidratos:    document.getElementById('plan_carb').value,
            grasas:           document.getElementById('plan_gras').value,
            duracion_semanas: document.getElementById('plan_dur').value,
            fecha_inicio:     document.getElementById('plan_fecha').value,
        })
    });
    const data = await res.json();

    btn.disabled = false; btn.textContent = 'Crear plan';

    if (data.ok) {
        cerrarModalPlan();
        // Actualizar la vista con el nuevo plan
        cargarPlanes(parseInt(paciente));
        // Cambiar el select de paciente si aplica
        const sel = document.getElementById('selectPaciente');
        if (sel) { sel.value = paciente; pacienteIdActual = parseInt(paciente); }
    } else {
        mostrarMsgPlan(data.error || 'Error al crear el plan');
    }
}

function mostrarMsgPlan(txt) {
    const el = document.getElementById('msgPlan');
    el.textContent = txt;
    el.className = 'mt-4 px-4 py-3 rounded-xl text-sm font-medium bg-red-100 text-red-700';
    el.classList.add('open');
}

// Cambiar estado del plan (activo / pausado / finalizado)
async function cambiarEstado(id, estado) {
    await fetch('api/planes.php', {
        method: 'PUT',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ id, estado })
    });
    // Refrescar sin recargar la pagina completa
    cargarPlanes(pacienteIdActual);
}

// ──────────────────────────────────────────────
//  Modal detalle del plan
// ──────────────────────────────────────────────
function verDetalle(p) {
    document.getElementById('detalleTitulo').textContent = p.titulo;
    document.getElementById('detalleContenido').innerHTML = `
        <div class="bg-gray-50 p-4 rounded-xl space-y-1">
            <p><span class="font-semibold">Nutricionista:</span> ${p.nutricionista}</p>
            <p><span class="font-semibold">Inicio:</span> ${p.fecha_inicio}</p>
            <p><span class="font-semibold">Duración:</span> ${p.duracion_semanas} semanas</p>
            <p><span class="font-semibold">Estado:</span> ${p.estado}</p>
        </div>
        ${p.descripcion ? `<div class="bg-green-50 p-4 rounded-xl"><p class="font-semibold text-[#22c55e] mb-1">Descripción</p><p>${p.descripcion}</p></div>` : ''}
        ${p.calorias ? `
        <div class="bg-amber-50 p-4 rounded-xl">
            <p class="font-semibold text-amber-700 mb-2">Objetivos calóricos</p>
            <div class="grid grid-cols-2 gap-2 text-xs">
                <div class="bg-white p-2 rounded-lg text-center"><p class="text-gray-500">Calorías</p><p class="font-bold text-lg">${p.calorias}</p><p class="text-gray-400">kcal/día</p></div>
                ${p.proteinas ? `<div class="bg-white p-2 rounded-lg text-center"><p class="text-gray-500">Proteínas</p><p class="font-bold text-lg">${p.proteinas}g</p></div>` : ''}
                ${p.carbohidratos ? `<div class="bg-white p-2 rounded-lg text-center"><p class="text-gray-500">Carbohidratos</p><p class="font-bold text-lg">${p.carbohidratos}g</p></div>` : ''}
                ${p.grasas ? `<div class="bg-white p-2 rounded-lg text-center"><p class="text-gray-500">Grasas</p><p class="font-bold text-lg">${p.grasas}g</p></div>` : ''}
            </div>
        </div>` : ''}
    `;
    document.getElementById('modalDetalle').classList.add('open');
}
function verDetallePorId(planId) {
    const p = todosLosPlanes.find(pl => pl.id === planId);
    if (p) verDetalle(p);
}
function cerrarDetalle() { document.getElementById('modalDetalle').classList.remove('open'); }




// ──────────────────────────────────────────────
//  Descargar PDF del plan con jsPDF (client-side)
// ──────────────────────────────────────────────
async function descargarPDF(planId) {
    // 1. Pedir datos del plan a la API PHP
    const res  = await fetch(\`api/pdf_plan.php?id=\${planId}\`);
    const plan = await res.json();
    if (plan.error) return alert('Error: ' + plan.error);

    // 2. Crear el documento PDF con jsPDF
    const { jsPDF } = window.jspdf;
    const doc = new jsPDF({ unit: 'mm', format: 'a4' });

    // Colores
    const VERDE  = [34, 197, 94];
    const OSCURO = [30, 30, 30];
    const GRIS   = [120, 120, 120];

    let y = 20; // cursor vertical

    // ── Encabezado con fondo verde ──
    doc.setFillColor(...VERDE);
    doc.rect(0, 0, 210, 35, 'F');
    doc.setTextColor(255, 255, 255);
    doc.setFont('helvetica', 'bold');
    doc.setFontSize(20);
    doc.text('NutriSucre', 15, 15);
    doc.setFontSize(10);
    doc.setFont('helvetica', 'normal');
    doc.text('Plan Nutricional Personalizado', 15, 23);
    doc.text('Chuquisaca, Bolivia', 15, 29);
    y = 45;

    // ── Titulo del plan ──
    doc.setTextColor(...OSCURO);
    doc.setFont('helvetica', 'bold');
    doc.setFontSize(16);
    doc.text(plan.titulo, 15, y);
    y += 8;

    // Badge de estado
    const estadoColor = plan.estado === 'activo' ? [34,197,94] : [150,150,150];
    doc.setFillColor(...estadoColor);
    doc.roundedRect(15, y, 30, 7, 2, 2, 'F');
    doc.setTextColor(255,255,255);
    doc.setFontSize(8);
    doc.text(plan.estado.toUpperCase(), 17, y + 4.5);
    y += 14;

    // ── Info del profesional y paciente ──
    doc.setTextColor(...GRIS);
    doc.setFont('helvetica', 'normal');
    doc.setFontSize(10);
    doc.text(\`Nutricionista: \${plan.nutricionista} (\${plan.especialidad})\`, 15, y); y += 6;
    doc.text(\`Paciente: \${plan.paciente}\`, 15, y); y += 6;
    doc.text(\`Fecha de inicio: \${plan.fecha_inicio}  |  Duración: \${plan.duracion_semanas} semanas\`, 15, y); y += 10;

    // Línea separadora
    doc.setDrawColor(...VERDE);
    doc.setLineWidth(0.5);
    doc.line(15, y, 195, y);
    y += 8;

    // ── Descripcion ──
    if (plan.descripcion) {
        doc.setFont('helvetica', 'bold');
        doc.setFontSize(12);
        doc.setTextColor(...OSCURO);
        doc.text('Descripción del plan', 15, y); y += 6;
        doc.setFont('helvetica', 'normal');
        doc.setFontSize(10);
        doc.setTextColor(...GRIS);
        // splitTextToSize ajusta el texto al ancho de la pagina
        const lineas = doc.splitTextToSize(plan.descripcion, 180);
        doc.text(lineas, 15, y);
        y += lineas.length * 5 + 8;
    }

    // ── Tabla de macros (si existen) ──
    if (plan.calorias || plan.proteinas || plan.carbohidratos || plan.grasas) {
        doc.setFont('helvetica', 'bold');
        doc.setFontSize(12);
        doc.setTextColor(...OSCURO);
        doc.text('Objetivos nutricionales diarios', 15, y); y += 8;

        const macros = [
            { etiqueta: 'Calorías',       valor: plan.calorias      ? \`\${plan.calorias} kcal\`  : '—', color: [245,158,11] },
            { etiqueta: 'Proteínas',      valor: plan.proteinas     ? \`\${plan.proteinas} g\`    : '—', color: [59,130,246] },
            { etiqueta: 'Carbohidratos',  valor: plan.carbohidratos ? \`\${plan.carbohidratos} g\`: '—', color: [234,179,8] },
            { etiqueta: 'Grasas',         valor: plan.grasas        ? \`\${plan.grasas} g\`       : '—', color: [239,68,68] },
        ];

        const colW = 43;
        macros.forEach((m, i) => {
            const x = 15 + i * colW;
            doc.setFillColor(...m.color);
            doc.roundedRect(x, y, colW - 3, 20, 3, 3, 'F');
            doc.setTextColor(255,255,255);
            doc.setFont('helvetica', 'bold');
            doc.setFontSize(14);
            doc.text(m.valor, x + (colW-3)/2, y + 11, { align: 'center' });
            doc.setFontSize(8);
            doc.setFont('helvetica', 'normal');
            doc.text(m.etiqueta, x + (colW-3)/2, y + 17, { align: 'center' });
        });
        y += 30;
    }

    // ── Pie de pagina ──
    doc.setDrawColor(...VERDE);
    doc.line(15, 275, 195, 275);
    doc.setFontSize(8);
    doc.setTextColor(...GRIS);
    doc.setFont('helvetica', 'italic');
    doc.text('Documento generado por NutriSucre — nutrisucre.bo', 15, 281);
    doc.text(\`Fecha de emisión: \${new Date().toLocaleDateString('es-BO')}\`, 195, 281, { align: 'right' });

    // 3. Descargar el archivo
    const nombreArchivo = \`plan_\${plan.titulo.replace(/[^a-zA-Z0-9]/g,'_').toLowerCase()}.pdf\`;
    doc.save(nombreArchivo);
}

// Cerrar modales al click fuera
['modalPlan','modalDetalle'].forEach(id => {
    document.getElementById(id)?.addEventListener('click', e => {
        if (e.target.id === id) document.getElementById(id).classList.remove('open');
    });
});
</script>
</body>
</html>

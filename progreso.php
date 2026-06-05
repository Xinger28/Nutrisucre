<?php
// ============================================================
//  progreso.php  —  Seguimiento de progreso del paciente
//  Usa AJAX (fetch) + Chart.js para el grafico
// ============================================================
session_start();
if (empty($_SESSION['usuario'])) { header('Location: login.php'); exit; }

$usuario = $_SESSION['usuario'];
$rol     = $usuario['rol'];
$nombre  = $usuario['nombre'];

// Solo pacientes y administradores pueden ver el progreso
if ($rol === 'Nutricionista') { header('Location: servicios.php'); exit; }
?>
<!DOCTYPE html>
<html lang="es">
<head>
<title>NutriSucre · Mi Progreso</title>
<?php require_once '_ios_head.php'; ?>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<style>
  .btn-metrica { transition:all .2s; border:1.5px solid var(--border); padding:7px 16px; border-radius:50px; font-size:13px; font-weight:600; cursor:pointer; background:white; color:var(--text2); }
  .btn-metrica.activo { background:var(--green); color:white; border-color:var(--green); box-shadow:0 4px 12px rgba(34,197,94,0.3); }
</style>
<body>

<!-- ======= SIDEBAR (igual en todas las paginas) ======= -->
<?php $paginaActual = 'progreso'; require_once '_sidebar.php'; ?>

<!-- ======= HEADER ======= -->
<header class="ios-header md:pl-64">
  <div class="flex items-center gap-3">
    <p class="font-black text-[18px]">Mi Progreso</p>
  </div>
  <div class="flex items-center gap-3">
    <button onclick="abrirModalRegistro()" class="ios-btn text-[13px]" style="border-radius:12px;padding:10px 18px">
      <span class="icon" style="font-size:16px">add</span> Nuevo registro
    </button>
    <div class="text-right hidden sm:block">
      <p class="font-semibold text-[14px]"><?= htmlspecialchars($nombre) ?></p>
      <p class="text-[12px] text-[#22c55e] font-semibold"><?= htmlspecialchars($rol) ?></p>
    </div>
  </div>
</header>

<!-- ======= CONTENIDO PRINCIPAL ======= -->
<main class="md:pl-64 p-5 md:p-8 max-w-6xl mx-auto space-y-5">

  <!-- Stats resumen (se llenan con JS) -->
  <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
    <div class="bg-white rounded-[20px] border border-[var(--border)] p-5">
      <p class="text-xs text-gray-500 mb-1">Peso inicial</p>
      <p id="statPesoInicio" class="text-2xl font-black text-gray-800">—</p>
      <p class="text-xs text-gray-400">kg</p>
    </div>
    <div class="bg-white rounded-[20px] border border-[var(--border)] p-5">
      <p class="text-xs text-gray-500 mb-1">Peso actual</p>
      <p id="statPesoActual" class="text-2xl font-black text-[#22c55e]">—</p>
      <p class="text-xs text-gray-400">kg</p>
    </div>
    <div class="bg-white rounded-[20px] border border-[var(--border)] p-5">
      <p class="text-xs text-gray-500 mb-1">Diferencia total</p>
      <p id="statDiferencia" class="text-2xl font-black">—</p>
      <p class="text-xs text-gray-400">kg desde el inicio</p>
    </div>
    <div class="bg-white rounded-[20px] border border-[var(--border)] p-5">
      <p class="text-xs text-gray-500 mb-1">Registros</p>
      <p id="statRegistros" class="text-2xl font-black text-gray-800">0</p>
      <p class="text-xs text-gray-400">entradas totales</p>
    </div>
  </div>

  <!-- Grafico + selector de metrica -->
  <div class="ios-card p-6">
    <div class="flex flex-wrap justify-between items-center mb-6 gap-3">
      <h2 class="text-xl font-bold">Evolución de métricas</h2>
      <!-- Selector de metrica: JS escucha el click y redibuja el Chart.js -->
      <div class="flex gap-2 flex-wrap">
        <button class="btn-metrica activo px-4 py-2 rounded-xl text-sm font-medium border" onclick="cambiarMetrica('peso', this)">Peso (kg)</button>
        <button class="btn-metrica px-4 py-2 rounded-xl text-sm font-medium border" onclick="cambiarMetrica('cintura', this)">Cintura (cm)</button>
        <button class="btn-metrica px-4 py-2 rounded-xl text-sm font-medium border" onclick="cambiarMetrica('cadera', this)">Cadera (cm)</button>
        <button class="btn-metrica px-4 py-2 rounded-xl text-sm font-medium border" onclick="cambiarMetrica('grasa', this)">% Grasa</button>
      </div>
    </div>
    <!-- Canvas del grafico — Chart.js necesita width en el contenedor, no en canvas -->
    <div style="position:relative; height:300px; width:100%">
      <canvas id="grafico" role="img" aria-label="Grafico de progreso de medidas corporales"></canvas>
    </div>
    <p id="sinDatos" class="hidden text-center text-gray-400 py-12">No hay datos suficientes para mostrar el gráfico. ¡Registra tu primer progreso!</p>
  </div>

  <!-- Tabla de historial -->
  <div class="ios-card overflow-hidden">
    <div class="flex justify-between items-center px-6 py-5 border-b border-[rgba(0,0,0,0.05)]">
      <h2 class="text-xl font-bold">Historial de registros</h2>
      <span id="totalRegistros" class="text-sm text-gray-400"></span>
    </div>
    <div class="overflow-x-auto">
      <table class="w-full">
        <thead class="bg-gray-50 text-sm text-gray-600">
          <tr>
            <th class="px-6 py-4 text-left font-semibold">Fecha</th>
            <th class="px-6 py-4 text-left font-semibold">Peso (kg)</th>
            <th class="px-6 py-4 text-left font-semibold">Cintura (cm)</th>
            <th class="px-6 py-4 text-left font-semibold">Cadera (cm)</th>
            <th class="px-6 py-4 text-left font-semibold">% Grasa</th>
            <th class="px-6 py-4 text-left font-semibold">Nota</th>
            <th class="px-6 py-4 text-right font-semibold">Acción</th>
          </tr>
        </thead>
        <tbody id="tablaHistorial" class="divide-y text-sm">
          <tr><td colspan="7" class="px-6 py-8 text-gray-400 text-center">Cargando historial...</td></tr>
        </tbody>
      </table>
    </div>
  </div>
</main>

<!-- ======= MODAL: Nuevo registro ======= -->
<div id="modalRegistro" class="ios-modal-bg">
  <div class="ios-modal max-w-lg p-7">
    <div class="flex justify-between items-center mb-6">
      <h3 class="text-2xl font-bold">Nuevo registro de progreso</h3>
      <button onclick="cerrarModalRegistro()">
        <span class="icon" style="font-size:20px">close</span>
      </button>
    </div>

    <div class="space-y-4">
      <div>
        <label class="block text-sm font-semibold mb-1">Fecha *</label>
        <input id="reg_fecha" type="date" class="ios-input">
      </div>
      <!-- Grid de medidas: 2 columnas -->
      <div class="grid grid-cols-2 gap-4">
        <div>
          <label class="block text-sm font-semibold mb-1">Peso (kg)</label>
          <input id="reg_peso" type="number" step="0.1" min="20" max="300" placeholder="ej: 72.5"
                 class="ios-input">
        </div>
        <div>
          <label class="block text-sm font-semibold mb-1">Cintura (cm)</label>
          <input id="reg_cintura" type="number" step="0.1" min="40" max="200" placeholder="ej: 85.0"
                 class="ios-input">
        </div>
        <div>
          <label class="block text-sm font-semibold mb-1">Cadera (cm)</label>
          <input id="reg_cadera" type="number" step="0.1" min="40" max="250" placeholder="ej: 98.0"
                 class="ios-input">
        </div>
        <div>
          <label class="block text-sm font-semibold mb-1">% Grasa corporal</label>
          <input id="reg_grasa" type="number" step="0.1" min="1" max="60" placeholder="ej: 25.0"
                 class="ios-input">
        </div>
      </div>
      <div>
        <label class="block text-sm font-semibold mb-1">Nota (opcional)</label>
        <textarea id="reg_nota" rows="2" placeholder="Ej: me siento con más energía..."
                  class="ios-input resize-none" style="font-family:inherit"></textarea>
      </div>
    </div>

    <!-- Mensaje de error/exito dentro del modal -->
    <div id="msgModal" class="hidden mt-4 px-4 py-3 rounded-xl text-sm font-medium"></div>

    <div class="flex gap-3 mt-6">
      <button onclick="cerrarModalRegistro()" class="ios-btn-ghost flex-1" style="border-radius:14px">Cancelar</button>
      <button onclick="guardarRegistro()" id="btnGuardar"
              class="ios-btn flex-1" style="border-radius:14px">
        Guardar
      </button>
    </div>
  </div>
</div>

<!-- ======= MODAL: Confirmar eliminacion ======= -->
<div id="modalEliminar" class="ios-modal-bg">
  <div class="ios-modal max-w-sm p-7 text-center">
    <span class="icon text-red-400" style="font-size:52px">delete</span>
    <h3 class="text-xl font-bold mt-3 mb-2">¿Eliminar este registro?</h3>
    <p class="text-gray-500 text-sm mb-6">Esta acción no se puede deshacer.</p>
    <div class="flex gap-3">
      <button onclick="cerrarModalEliminar()" class="ios-btn-ghost flex-1" style="border-radius:14px">Cancelar</button>
      <button onclick="ejecutarEliminar()" class="flex-1 py-3 bg-red-500 text-white rounded-[14px] font-bold hover:bg-red-600 transition-colors">Eliminar</button>
    </div>
  </div>
</div>

<!-- ======= JAVASCRIPT ======= -->
<script>
// ──────────────────────────────────────────────
//  Estado global del modulo
// ──────────────────────────────────────────────
let datosGrafico  = [];          // array completo de registros de la API
let metricaActual = 'peso';      // metrica que muestra el Chart.js
let chartInstancia = null;       // instancia de Chart.js (para destruir antes de redibujar)
let idParaEliminar = null;       // id del registro a eliminar

// Colores por metrica (hardcoded porque Chart.js no lee CSS vars)
const COLORES = {
    peso:    { linea: '#22c55e', relleno: 'rgba(34,197,94,0.08)' },
    cintura: { linea: '#3b82f6', relleno: 'rgba(59,130,246,0.08)' },
    cadera:  { linea: '#a855f7', relleno: 'rgba(168,85,247,0.08)' },
    grasa:   { linea: '#f59e0b', relleno: 'rgba(245,158,11,0.08)' },
};

// ──────────────────────────────────────────────
//  Al cargar la pagina, pedir datos a la API con AJAX (fetch)
// ──────────────────────────────────────────────
document.addEventListener('DOMContentLoaded', () => {
    // Poner fecha de hoy en el formulario por defecto
    document.getElementById('reg_fecha').value = new Date().toISOString().split('T')[0];
    cargarProgreso();
});

async function cargarProgreso() {
    // fetch es la forma moderna de AJAX en JavaScript
    const res  = await fetch('api/seguimiento.php');
    const data = await res.json();

    if (data.error) {
        console.error('Error API:', data.error);
        return;
    }

    datosGrafico = data;
    actualizarStats(data);
    dibujarGrafico(data, metricaActual);
    renderTabla(data);
}

// ──────────────────────────────────────────────
//  Stats resumen (primer y último registro)
// ──────────────────────────────────────────────
function actualizarStats(data) {
    document.getElementById('statRegistros').textContent = data.length;

    // Filtrar solo los registros que tienen dato de peso
    const conPeso = data.filter(r => r.peso !== null);

    if (conPeso.length === 0) return;

    const inicio  = parseFloat(conPeso[0].peso);
    const actual  = parseFloat(conPeso[conPeso.length - 1].peso);
    const diff    = (actual - inicio).toFixed(1);
    const diffNum = parseFloat(diff);

    document.getElementById('statPesoInicio').textContent  = inicio.toFixed(1);
    document.getElementById('statPesoActual').textContent  = actual.toFixed(1);

    const el = document.getElementById('statDiferencia');
    el.textContent = (diffNum > 0 ? '+' : '') + diff;
    el.className   = 'text-2xl font-black ' + (diffNum < 0 ? 'text-[#22c55e]' : diffNum > 0 ? 'text-red-500' : 'text-gray-800');
}

// ──────────────────────────────────────────────
//  Chart.js — Dibujar/Redibujar grafico
// ──────────────────────────────────────────────
function dibujarGrafico(data, metrica) {
    // Filtrar registros que tienen dato para la metrica elegida
    const filtrados = data.filter(r => r[metrica] !== null && r[metrica] !== '');
    const canvas    = document.getElementById('grafico');
    const sinDatos  = document.getElementById('sinDatos');

    if (filtrados.length < 2) {
        // Ocultar canvas y mostrar mensaje
        canvas.style.display = 'none';
        sinDatos.classList.remove('hidden');
        return;
    }

    canvas.style.display = 'block';
    sinDatos.classList.add('hidden');

    // Si ya existia un chart, destruirlo antes de crear uno nuevo
    // (Chart.js lanza error si intentas redibujar el mismo canvas)
    if (chartInstancia) {
        chartInstancia.destroy();
        chartInstancia = null;
    }

    const etiquetas = filtrados.map(r => {
        // Formatear fecha DD/MM para que se vea mas limpio en el eje X
        const [y, m, d] = r.fecha.split('-');
        return `${d}/${m}`;
    });
    const valores = filtrados.map(r => parseFloat(r[metrica]));
    const color   = COLORES[metrica];

    const ctx = canvas.getContext('2d');
    chartInstancia = new Chart(ctx, {
        type: 'line',
        data: {
            labels: etiquetas,
            datasets: [{
                label: metrica.charAt(0).toUpperCase() + metrica.slice(1),
                data: valores,
                borderColor: color.linea,
                backgroundColor: color.relleno,
                borderWidth: 2.5,
                fill: true,
                tension: 0.35,          // linea suavizada
                pointRadius: 5,
                pointBackgroundColor: '#fff',
                pointBorderColor: color.linea,
                pointBorderWidth: 2,
                pointHoverRadius: 7,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },     // la leyenda la hacemos con los botones
                tooltip: {
                    callbacks: {
                        // Personalizar el tooltip que aparece al hacer hover
                        label: ctx => ` ${ctx.parsed.y} ${metrica === 'grasa' ? '%' : metrica === 'peso' ? 'kg' : 'cm'}`
                    }
                }
            },
            scales: {
                x: {
                    grid: { color: 'rgba(0,0,0,0.04)' },
                    ticks: { font: { family: 'Inter', size: 11 } }
                },
                y: {
                    grid: { color: 'rgba(0,0,0,0.04)' },
                    ticks: { font: { family: 'Inter', size: 11 } }
                }
            }
        }
    });
}

// Cambia la metrica activa al hacer click en un boton
function cambiarMetrica(metrica, btn) {
    metricaActual = metrica;
    // Quitar clase activo de todos los botones y aplicarla al clickeado
    document.querySelectorAll('.btn-metrica').forEach(b => b.classList.remove('activo'));
    btn.classList.add('activo');
    dibujarGrafico(datosGrafico, metrica);
}

// ──────────────────────────────────────────────
//  Tabla de historial (DOM manipulation)
// ──────────────────────────────────────────────
function renderTabla(data) {
    const tbody = document.getElementById('tablaHistorial');
    document.getElementById('totalRegistros').textContent = `${data.length} registro${data.length !== 1 ? 's' : ''}`;

    if (data.length === 0) {
        tbody.innerHTML = '<tr><td colspan="7" class="px-6 py-10 text-gray-400 text-center">No hay registros aún. ¡Agrega tu primer progreso!</td></tr>';
        return;
    }

    // Invertir para mostrar los mas recientes primero
    const invertido = [...data].reverse();

    tbody.innerHTML = invertido.map(r => {
        const fecha = r.fecha.split('-').reverse().join('/');
        return `
            <tr class="hover:bg-gray-50 transition-colors">
                <td class="px-6 py-4 font-medium">${fecha}</td>
                <td class="px-6 py-4">${r.peso    ?? '<span class="text-gray-300">—</span>'}</td>
                <td class="px-6 py-4">${r.cintura ?? '<span class="text-gray-300">—</span>'}</td>
                <td class="px-6 py-4">${r.cadera  ?? '<span class="text-gray-300">—</span>'}</td>
                <td class="px-6 py-4">${r.grasa   ?? '<span class="text-gray-300">—</span>'}</td>
                <td class="px-6 py-4 text-gray-500 text-xs max-w-[180px] truncate">${r.nota ?? ''}</td>
                <td class="px-6 py-4 text-right">
                    <button onclick="pedirEliminar(${r.id})"
                            class="text-red-400 hover:text-red-600 p-1 transition-colors" title="Eliminar">
                        <span class="icon" style="font-size:20px">delete</span>
                    </button>
                </td>
            </tr>
        `;
    }).join('');
}

// ──────────────────────────────────────────────
//  Modal nuevo registro
// ──────────────────────────────────────────────
function abrirModalRegistro() {
    ocultarMsg();
    document.getElementById('modalRegistro').classList.add('open');
}
function cerrarModalRegistro() {
    document.getElementById('modalRegistro').classList.remove('open');
}

async function guardarRegistro() {
    const fecha   = document.getElementById('reg_fecha').value;
    const peso    = document.getElementById('reg_peso').value;
    const cintura = document.getElementById('reg_cintura').value;
    const cadera  = document.getElementById('reg_cadera').value;
    const grasa   = document.getElementById('reg_grasa').value;
    const nota    = document.getElementById('reg_nota').value;

    if (!fecha) return mostrarMsg('Selecciona una fecha');
    if (!peso && !cintura && !cadera && !grasa) {
        return mostrarMsg('Ingresa al menos una medida');
    }

    const btn = document.getElementById('btnGuardar');
    btn.disabled = true; btn.textContent = 'Guardando...';

    // Fetch POST a la API PHP
    const res  = await fetch('api/seguimiento.php', {
        method:  'POST',
        headers: { 'Content-Type': 'application/json' },
        body:    JSON.stringify({ fecha, peso, cintura, cadera, grasa, nota })
    });
    const data = await res.json();

    btn.disabled = false; btn.textContent = 'Guardar';

    if (data.ok) {
        cerrarModalRegistro();
        // Limpiar campos
        ['reg_peso','reg_cintura','reg_cadera','reg_grasa','reg_nota'].forEach(id => {
            document.getElementById(id).value = '';
        });
        // Recargar datos y redibujar TODO (tabla + grafico + stats)
        await cargarProgreso();
    } else {
        mostrarMsg(data.error || 'Error al guardar');
    }
}

// ──────────────────────────────────────────────
//  Eliminar registro
// ──────────────────────────────────────────────
function pedirEliminar(id) {
    idParaEliminar = id;
    document.getElementById('modalEliminar').classList.add('open');
}
function cerrarModalEliminar() {
    document.getElementById('modalEliminar').classList.remove('open');
    idParaEliminar = null;
}
async function ejecutarEliminar() {
    const res  = await fetch('api/seguimiento.php', {
        method:  'DELETE',
        headers: { 'Content-Type': 'application/json' },
        body:    JSON.stringify({ id: idParaEliminar })
    });
    const data = await res.json();
    cerrarModalEliminar();
    if (data.ok) await cargarProgreso();
}

// ──────────────────────────────────────────────
//  Helpers de mensajes y cierre de modales
// ──────────────────────────────────────────────
function mostrarMsg(txt, tipo = 'error') {
    const el = document.getElementById('msgModal');
    el.textContent = txt;
    el.className = `mt-4 px-4 py-3 rounded-xl text-sm font-medium ${
        tipo === 'ok' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'
    }`;
    el.classList.remove('hidden');
}
function ocultarMsg() { document.getElementById('msgModal').classList.add('hidden'); }



// Cerrar modales al hacer click fuera
['modalRegistro','modalEliminar'].forEach(id => {
    document.getElementById(id).addEventListener('click', e => {
        if (e.target.id === id) document.getElementById(id).classList.remove('open');
    });
});
</script>
</body>
</html>

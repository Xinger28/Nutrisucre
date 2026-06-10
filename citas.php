<?php
// ============================================================
//  citas.php  —  Gestión de Citas (Pacientes, Nutricionistas, Admin)
// ============================================================
session_start();
if (empty($_SESSION['usuario'])) { header('Location: login.php'); exit; }
$usuario = $_SESSION['usuario'];
$rol = $usuario['rol'];
?>
<!DOCTYPE html>
<html lang="es">
<head>
<title>NutriSucre · Mis Citas</title>
<?php require_once '_ios_head.php'; ?>
<style>
  body { background: #f2f2f7; }
</style>
</head>
<body>

<div class="flex">
  <!-- Sidebar -->
  <?php $paginaActual = 'citas'; require_once '_sidebar.php'; ?>

  <!-- Main Content -->
  <div class="flex-1 min-h-screen md:pl-64">
    
    <!-- Header -->
    <header class="ios-header">
      <div class="flex items-center gap-3">
        <button onclick="toggleSidebar()" class="md:hidden ios-btn-icon"><span class="icon" style="font-size:20px">menu</span></button>
        <p class="font-black text-[18px]">
          <?php 
            if ($rol === 'Paciente') echo 'Mis Citas y Reservas';
            elseif ($rol === 'Nutricionista') echo 'Solicitudes de Citas Recibidas';
            else echo 'Auditoría Global de Citas';
          ?>
        </p>
      </div>
      <div class="text-right hidden sm:block">
        <p class="font-semibold text-[14px]"><?= htmlspecialchars($usuario['nombre']) ?></p>
        <p class="text-[12px] text-[#22c55e] font-semibold"><?= htmlspecialchars($rol) ?></p>
      </div>
    </header>

    <main class="max-w-4xl mx-auto p-5 md:p-8 space-y-6">
      
      <!-- Feedback status -->
      <div id="feedbackCitas" class="hidden rounded-2xl px-5 py-4 text-[14px] font-semibold text-center"></div>

      <!-- Filtros de Estado -->
      <div class="flex gap-2 flex-wrap items-center justify-between">
        <div class="flex gap-2 flex-wrap" id="filtrosCitas">
          <button onclick="filtrarCitas('')" class="chip active" id="chip_all">Todas</button>
          <button onclick="filtrarCitas('pendiente_confirmacion')" class="chip" id="chip_pend">⏳ Pendientes de Pago</button>
          <button onclick="filtrarCitas('confirmada')" class="chip" id="chip_conf">✅ Confirmadas</button>
          <button onclick="filtrarCitas('rechazada')" class="chip" id="chip_rech">❌ Rechazadas</button>
          <button onclick="filtrarCitas('cancelada')" class="chip" id="chip_canc">🚫 Canceladas</button>
        </div>
        
        <?php if ($rol === 'Paciente'): ?>
          <a href="index.php" class="ios-btn py-2.5 px-5 text-xs">Agendar Especialista</a>
        <?php endif; ?>
      </div>

      <!-- Listado de Citas -->
      <div id="listaCitas" class="space-y-4">
        <!-- Render dinámico por JS -->
      </div>

    </main>
  </div>
</div>

<!-- ══ MODAL: Ver Comprobante ══ -->
<div id="modalComprobante" class="ios-modal-bg" onclick="if(event.target===this)cerrarModalComprobante()">
  <div class="ios-modal max-w-lg p-5">
    <div class="flex justify-between items-center mb-4">
      <p class="font-bold text-[18px]">Comprobante de Pago</p>
      <button onclick="cerrarModalComprobante()" class="ios-btn-icon"><span class="icon">close</span></button>
    </div>
    <div class="text-center">
      <img id="imgComprobante" src="" class="max-h-[60vh] mx-auto rounded-xl object-contain border">
      <iframe id="pdfComprobante" src="" class="w-full h-[60vh] rounded-xl border hidden"></iframe>
      <p id="txtNoComprobante" class="text-gray-500 py-8 hidden">No hay comprobante cargado para esta cita.</p>
    </div>
  </div>
</div>

<script>
let citasOriginales = [];
let rolUsuario = '<?= $rol ?>';

document.addEventListener('DOMContentLoaded', () => {
    cargarCitas();
});

async function cargarCitas() {
    const list = document.getElementById('listaCitas');
    list.innerHTML = '<p class="text-center text-[#8e8e93] text-[14px] py-12">Cargando tus citas...</p>';
    
    try {
        const res = await fetch('api/citas.php');
        const data = await res.json();
        if (data.error) {
            list.innerHTML = `<div class="ios-card p-12 text-center text-red-500 text-sm">Error: ${data.error}</div>`;
            return;
        }
        
        citasOriginales = Array.isArray(data) ? data : [];
        renderCitas(citasOriginales);
    } catch(e) {
        list.innerHTML = '<div class="ios-card p-12 text-center text-red-500 text-sm">Error de conexión al cargar citas.</div>';
    }
}

function renderCitas(lista) {
    const list = document.getElementById('listaCitas');
    if (!lista.length) {
        list.innerHTML = '<div class="ios-card p-12 text-center text-gray-500 text-sm">No se encontraron citas en este estado.</div>';
        return;
    }

    const EST_BADGE = {
        'pendiente_confirmacion': 'badge-yellow',
        'pendiente': 'badge-yellow',
        'confirmada': 'badge-green',
        'rechazada': 'badge-red',
        'cancelada': 'badge-gray'
    };
    const EST_TXT = {
        'pendiente_confirmacion': 'Pendiente de Pago',
        'pendiente': 'Pendiente',
        'confirmada': 'Confirmada',
        'rechazada': 'Rechazada',
        'cancelada': 'Cancelada'
    };

    list.innerHTML = lista.map(c => {
        const fechaStr = new Date(c.fecha + 'T00:00:00').toLocaleDateString('es-BO', { day: '2-digit', month: 'short', year: 'numeric' });
        const horaStr = c.hora;
        
        // Comprobante button
        const comprobanteBtn = c.comprobante_pago 
            ? `<button onclick="abrirModalComprobante('${c.comprobante_pago}')" class="badge badge-blue cursor-pointer flex items-center gap-1">
                 <span class="icon" style="font-size:12px">receipt</span> Ver Pago (${c.metodo_pago})
               </button>`
            : '<span class="text-xs text-gray-400">Sin comprobante</span>';

        let subDetalles = '';
        let cardAcciones = '';

        if (rolUsuario === 'Paciente') {
            const nutriContacto = c.nutricionista_whatsapp 
                ? `<a href="https://wa.me/591${c.nutricionista_whatsapp}" target="_blank" class="text-green-600 font-bold hover:underline flex items-center gap-0.5 mt-1">
                     <span class="icon" style="font-size:14px">chat</span> WhatsApp: ${c.nutricionista_whatsapp}
                   </a>`
                : '';
                
            subDetalles = `
                <div class="border-t pt-3 flex justify-between items-center text-xs text-gray-500 flex-wrap gap-2">
                    <div>
                        <p>Especialista: <strong class="text-gray-700">${c.nutricionista}</strong> (${c.especialidad})</p>
                        ${nutriContacto}
                        ${c.motivo_rechazo ? `<p class="text-red-500 mt-2 font-semibold">Motivo rechazo: ${c.motivo_rechazo}</p>` : ''}
                    </div>
                    <div>
                        <p>Método de Pago: <strong class="text-gray-700">${c.metodo_pago || '—'}</strong></p>
                        <div class="mt-1">${comprobanteBtn}</div>
                    </div>
                </div>`;
            
            // Paciente puede cancelar si está pendiente
            if (c.estado === 'pendiente_confirmacion' || c.estado === 'pendiente') {
                cardAcciones = `
                    <div class="flex justify-end pt-2 border-t border-[rgba(0,0,0,0.03)]">
                        <button onclick="responderCita(${c.id}, 'cancelada')" class="ios-btn-ghost py-1.5 px-3 text-[11px]" style="border-radius:8px;color:#ef4444;border-color:#fca5a5">Cancelar Reserva</button>
                    </div>`;
            }
        } 
        
        else if (rolUsuario === 'Nutricionista') {
            const pacContacto = c.paciente_celular 
                ? `<a href="https://wa.me/591${c.paciente_celular}" target="_blank" class="text-green-600 font-bold hover:underline flex items-center gap-0.5 mt-1">
                     <span class="icon" style="font-size:14px">chat</span> WhatsApp: ${c.paciente_celular}
                   </a>`
                : '';
                
            subDetalles = `
                <div class="border-t pt-3 flex justify-between items-center text-xs text-gray-500 flex-wrap gap-2">
                    <div>
                        <p>Paciente: <strong class="text-gray-700">${c.paciente}</strong> (${c.paciente_email})</p>
                        ${pacContacto}
                        ${c.motivo_rechazo ? `<p class="text-red-500 mt-2 font-semibold">Tu motivo de rechazo: ${c.motivo_rechazo}</p>` : ''}
                    </div>
                    <div>
                        <p>Método de Pago del Paciente: <strong class="text-gray-700">${c.metodo_pago || '—'}</strong></p>
                        <div class="mt-1 flex justify-end">${comprobanteBtn}</div>
                    </div>
                </div>`;
            
            // Nutricionista puede aceptar o rechazar solicitudes pendientes
            if (c.estado === 'pendiente_confirmacion' || c.estado === 'pendiente') {
                cardAcciones = `
                    <div class="flex gap-2 justify-end pt-2 border-t border-[rgba(0,0,0,0.03)] flex-wrap">
                        <button onclick="responderCita(${c.id}, 'confirmada')" class="ios-btn py-1.5 px-4 text-[11px]" style="border-radius:8px;background:#22c55e">Aceptar y Confirmar</button>
                        <button onclick="responderCita(${c.id}, 'rechazada')" class="ios-btn py-1.5 px-4 text-[11px]" style="border-radius:8px;background:#ef4444;box-shadow:none">Rechazar Solicitud</button>
                    </div>`;
            }
        } 
        
        else {
            // Rol: Administrador
            subDetalles = `
                <div class="border-t pt-3 flex justify-between items-center text-xs text-gray-500 flex-wrap gap-2">
                    <div>
                        <p>Paciente: <strong class="text-gray-700">${c.paciente}</strong> (Cel: ${c.paciente_celular || '—'})</p>
                        <p>Especialista: <strong class="text-gray-700">${c.nutricionista}</strong> (${c.especialidad})</p>
                        ${c.motivo_rechazo ? `<p class="text-red-500 mt-2 font-semibold">Motivo rechazo: ${c.motivo_rechazo}</p>` : ''}
                    </div>
                    <div>
                        <p>Pago: <strong class="text-gray-700">${c.metodo_pago || '—'}</strong></p>
                        <div class="mt-1">${comprobanteBtn}</div>
                    </div>
                </div>`;
            
            // Admin puede confirmar o rechazar cualquier cita en auditoría
            if (c.estado === 'pendiente_confirmacion' || c.estado === 'pendiente') {
                cardAcciones = `
                    <div class="flex gap-2 justify-end pt-2 border-t border-[rgba(0,0,0,0.03)] flex-wrap">
                        <button onclick="responderCita(${c.id}, 'confirmada')" class="ios-btn py-1.5 px-3 text-[11px]" style="border-radius:8px;background:#22c55e">Aprobar Pago</button>
                        <button onclick="responderCita(${c.id}, 'rechazada')" class="ios-btn py-1.5 px-3 text-[11px]" style="border-radius:8px;background:#ef4444;box-shadow:none">Rechazar Pago</button>
                    </div>`;
            } else if (c.estado === 'confirmada') {
                cardAcciones = `
                    <div class="flex justify-end pt-2 border-t border-[rgba(0,0,0,0.03)]">
                        <button onclick="responderCita(${c.id}, 'cancelada')" class="ios-btn-ghost py-1.5 px-3 text-[11px]" style="border-radius:8px;color:#ef4444;border-color:#fca5a5">Cancelar Cita</button>
                    </div>`;
            }
        }

        return `<div class="ios-card bg-white p-5 space-y-4">
            <div class="flex justify-between items-start flex-wrap gap-2">
                <div>
                    <span class="badge ${EST_BADGE[c.estado]}">${EST_TXT[c.estado]}</span>
                    <h3 class="font-black text-[17px] mt-1.5">${c.servicio_titulo || 'Consulta General'}</h3>
                    <p class="text-xs text-gray-400 mt-0.5">Fecha: ${fechaStr} · Hora: ${horaStr} · Precio: Bs. ${parseFloat(c.precio).toFixed(2)}</p>
                </div>
            </div>
            
            ${subDetalles}
            ${cardAcciones}
        </div>`;
    }).join('');
}

function filtrarCitas(estado) {
    // Toggles active chip
    const chips = ['all', 'pend', 'conf', 'rech', 'canc'];
    chips.forEach(ch => {
        const btn = document.getElementById('chip_' + ch);
        if (btn) btn.classList.remove('active');
    });
    
    const activeChip = estado === '' ? 'all' : estado === 'pendiente_confirmacion' ? 'pend' : estado === 'confirmada' ? 'conf' : estado === 'rechazada' ? 'rech' : 'canc';
    const btnActive = document.getElementById('chip_' + activeChip);
    if (btnActive) btnActive.classList.add('active');

    if (estado === '') {
        renderCitas(citasOriginales);
    } else {
        const filtradas = citasOriginales.filter(c => c.estado === estado);
        renderCitas(filtradas);
    }
}

async function responderCita(id, estado) {
    let motivo = '';
    if (estado === 'rechazada') {
        motivo = prompt('Ingresa el motivo del rechazo del pago/reserva (mínimo 5 caracteres):');
        if (motivo === null) return;
        if (motivo.trim().length < 5) {
            alert('El motivo de rechazo debe tener al menos 5 caracteres.');
            return;
        }
    } else if (estado === 'cancelada') {
        const conf = confirm('¿Estás seguro de que deseas cancelar esta cita/reserva?');
        if (!conf) return;
    }
    
    try {
        const res = await fetch('api/citas.php?accion=responder', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id, estado, motivo_rechazo: motivo })
        });
        const data = await res.json();
        if (data.ok) {
            mostrarFeedback('✅ Cita actualizada correctamente.', 'ok');
            cargarCitas();
        } else {
            mostrarFeedback(data.error || 'Error al actualizar cita', 'error');
        }
    } catch (e) {
        mostrarFeedback('Error al procesar la solicitud.', 'error');
    }
}

function abrirModalComprobante(url) {
    const img = document.getElementById('imgComprobante');
    const pdf = document.getElementById('pdfComprobante');
    const none = document.getElementById('txtNoComprobante');
    
    img.classList.add('hidden');
    pdf.classList.add('hidden');
    none.classList.add('hidden');
    
    if (!url) {
        none.classList.remove('hidden');
    } else if (url.toLowerCase().endsWith('.pdf')) {
        pdf.src = url;
        pdf.classList.remove('hidden');
    } else {
        img.src = url;
        img.classList.remove('hidden');
    }
    document.getElementById('modalComprobante').classList.add('open');
}

function cerrarModalComprobante() {
    document.getElementById('modalComprobante').classList.remove('open');
}

function mostrarFeedback(txt, tipo) {
    const el = document.getElementById('feedbackCitas');
    el.textContent = txt;
    el.className = `rounded-2xl px-5 py-4 text-[14px] font-semibold text-center ${tipo==='ok'?'bg-green-50 text-green-800 border border-green-200':'bg-red-50 text-red-800 border border-red-200'}`;
    el.classList.remove('hidden');
    showToast(txt);
    setTimeout(() => el.classList.add('hidden'), 5000);
}
</script>

</body>
</html>

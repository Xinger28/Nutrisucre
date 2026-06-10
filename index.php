<?php
// ============================================================
//  index.php  —  Página Principal Unificada
//  Pública: accesible para visitantes, pacientes y profesionales
// ============================================================
session_start();
$usuarioLogueado = $_SESSION['usuario'] ?? null;
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>NutriSucre — Especialistas en Nutrición</title>
<?php require_once '_ios_head.php'; ?>
<style>
  body { background: #f8fafc; }
  .hero-bg {
    background: linear-gradient(135deg, #22c55e 0%, #16a34a 100%);
  }
  .glass-nav {
    background: rgba(255, 255, 255, 0.85);
    backdrop-filter: blur(20px);
    -webkit-backdrop-filter: blur(20px);
    border-bottom: 1px solid rgba(0, 0, 0, 0.05);
  }
  .slot-btn {
    border: 1.5px solid transparent;
    transition: all 0.2s ease;
  }
  .slot-btn.disponible { background: #dcfce7; color: #16a34a; border-color: #bbf7d0; cursor: pointer; }
  .slot-btn.disponible:hover { background: #16a34a; color: white; border-color: #16a34a; transform: scale(1.05); }
  .slot-btn.disponible.selected { background: #15803d; color: white; border-color: #15803d; box-shadow: 0 4px 12px rgba(22,163,74,0.3); }
  .slot-btn.pendiente { background: #fef9c3; color: #854d0e; border-color: #fef08a; cursor: not-allowed; opacity: 0.8; }
  .slot-btn.ocupado { background: #fee2e2; color: #991b1b; border-color: #fecaca; cursor: not-allowed; opacity: 0.8; }
</style>
</head>
<body class="min-h-screen flex flex-col pb-12">

  <!-- Navbar -->
  <nav class="glass-nav fixed top-0 left-0 right-0 z-50 px-6 py-4 flex items-center justify-between">
    <div class="flex items-center gap-3">
      <div class="w-9 h-9 bg-gradient-to-br from-[#22c55e] to-[#16a34a] rounded-xl flex items-center justify-center shadow-lg">
        <span class="icon icon-fill text-white text-lg" style="font-size:18px">nutrition</span>
      </div>
      <span class="text-xl font-black tracking-tight text-[#1c1c1e]">NutriSucre</span>
    </div>
    
    <div class="flex items-center gap-4">
      <a href="#especialistas" class="text-sm font-semibold text-gray-600 hover:text-green-600 transition-colors">Especialistas</a>
      <a href="#testimonios" class="text-sm font-semibold text-gray-600 hover:text-green-600 transition-colors hidden sm:block">Opiniones</a>
      
      <?php if ($usuarioLogueado): ?>
        <a href="dashboard.php" class="ios-btn py-2 px-4 text-xs">Panel Control</a>
      <?php else: ?>
        <a href="login.php" class="ios-btn-ghost py-2 px-4 text-xs">Iniciar Sesión</a>
        <a href="login.php?tab=register" class="ios-btn py-2 px-4 text-xs">Registrarse</a>
      <?php endif; ?>
    </div>
  </nav>

  <!-- Hero Section -->
  <header class="hero-bg text-white pt-28 pb-16 px-6 text-center relative overflow-hidden">
    <div class="max-w-3xl mx-auto space-y-6 relative z-10">
      <span class="bg-white/20 text-white text-xs font-bold uppercase tracking-widest px-3 py-1.5 rounded-full">Chuquisaca · Bolivia</span>
      <h1 class="text-4xl md:text-5xl font-black tracking-tight leading-tight">Encuentra a tu Especialista en Nutrición</h1>
      <p class="text-green-50 text-base md:text-lg max-w-xl mx-auto leading-relaxed">Reserva consultas profesionales virtuales o presenciales en Sucre. Tu salud en manos certificadas.</p>
      
      <!-- Buscador principal -->
      <div class="bg-white rounded-3xl p-3 shadow-2xl max-w-lg mx-auto flex items-center gap-2 mt-8 border">
        <span class="icon text-gray-400 pl-3">search</span>
        <input type="text" id="mainSearch" placeholder="Buscar por nombre o especialidad..." class="w-full text-gray-800 outline-none text-sm py-2">
        <button onclick="realizarBusqueda()" class="ios-btn py-2 px-6 text-xs flex-shrink-0">Buscar</button>
      </div>
    </div>
    <div class="absolute inset-0 bg-white/5 opacity-50 pointer-events-none" style="background-image: radial-gradient(circle at 20% 30%, rgba(255,255,255,0.15) 1px, transparent 1px); background-size: 20px 20px;"></div>
  </header>

  <!-- Filtros y Listado de Especialistas -->
  <main class="max-w-6xl mx-auto px-6 py-10 flex-1 w-full grid grid-cols-1 lg:grid-cols-4 gap-8">
    
    <!-- Filtros Lateral (Desktop) -->
    <aside class="space-y-6 lg:col-span-1">
      <div class="bg-white p-6 rounded-3xl border shadow-sm space-y-5">
        <h3 class="font-bold text-lg text-gray-800 border-b pb-2">Filtros de Búsqueda</h3>
        
        <!-- Especialidad -->
        <div class="space-y-2">
          <label class="text-[12px] font-bold text-gray-500 uppercase">Especialidad</label>
          <select id="filtroEspecialidad" class="ios-input text-xs" onchange="aplicarFiltros()">
            <option value="">Todas</option>
            <option>Nutrición clínica</option>
            <option>Nutrición deportiva</option>
            <option>Nutrición pediátrica</option>
            <option>Diabetes</option>
            <option>Obesidad y metabolismo</option>
          </select>
        </div>

        <!-- Rango de Precios -->
        <div class="space-y-2">
          <label class="text-[12px] font-bold text-gray-500 uppercase">Precio Máximo (Bs.)</label>
          <input type="number" id="filtroPrecioMax" placeholder="Ej: 150" class="ios-input text-xs" oninput="aplicarFiltros()">
        </div>

        <!-- Modalidad -->
        <div class="space-y-2">
          <label class="text-[12px] font-bold text-gray-500 uppercase">Modalidad</label>
          <select id="filtroModalidad" class="ios-input text-xs" onchange="aplicarFiltros()">
            <option value="">Todas</option>
            <option>Virtual</option>
            <option>Presencial</option>
          </select>
        </div>

        <!-- Calificación Mínima -->
        <div class="space-y-2">
          <label class="text-[12px] font-bold text-gray-500 uppercase">Calificación Mínima</label>
          <select id="filtroRating" class="ios-input text-xs" onchange="aplicarFiltros()">
            <option value="">Cualquiera</option>
            <option value="5">⭐⭐⭐⭐⭐ (5.0)</option>
            <option value="4">⭐⭐⭐⭐ (4.0 o más)</option>
            <option value="3">⭐⭐⭐ (3.0 o más)</option>
          </select>
        </div>
        
        <button onclick="limpiarFiltros()" class="ios-btn-ghost w-full py-2 text-xs">Limpiar filtros</button>
      </div>
    </aside>

    <!-- Lista de Especialistas -->
    <section id="especialistas" class="lg:col-span-3 space-y-6">
      <div class="flex items-center justify-between">
        <h2 class="text-2xl font-bold tracking-tight text-gray-800">Nutricionistas Disponibles</h2>
        <p id="totalResultados" class="text-xs text-gray-500 font-semibold"></p>
      </div>
      
      <div id="gridEspecialistas" class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- Renderizado dinámico por JS -->
      </div>
    </section>

  </main>

  <!-- Sección Calificaciones & Testimonios -->
  <section id="testimonios" class="bg-gray-100 py-16 px-6">
    <div class="max-w-5xl mx-auto space-y-10">
      <div class="text-center space-y-2">
        <h2 class="text-3xl font-bold tracking-tight text-gray-800">Calificaciones y Opiniones</h2>
        <p class="text-gray-500 max-w-md mx-auto text-sm">Opiniones reales de pacientes que completaron sus planes de alimentación con nuestros especialistas.</p>
      </div>
      
      <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="bg-white p-6 rounded-3xl shadow-sm border space-y-4">
          <div class="flex text-amber-500 gap-0.5"><span class="icon">star</span><span class="icon">star</span><span class="icon">star</span><span class="icon">star</span><span class="icon">star</span></div>
          <p class="text-sm text-gray-600 italic">"Excelente la atención de la Lic. Ana. Logré bajar 8 kg en 2 meses comiendo sano y sin pasar hambre. Recomiendo la modalidad virtual."</p>
          <p class="font-bold text-xs text-gray-700">— Carlos M. (Paciente de Nutrición Deportiva)</p>
        </div>
        <div class="bg-white p-6 rounded-3xl shadow-sm border space-y-4">
          <div class="flex text-amber-500 gap-0.5"><span class="icon">star</span><span class="icon">star</span><span class="icon">star</span><span class="icon">star</span><span class="icon">star</span></div>
          <p class="text-sm text-gray-600 italic">"Muy profesional el Dr. Javier. Su plan personalizado me ayudó a controlar mis niveles de glucosa y diabetes tipo 2 de forma natural."</p>
          <p class="font-bold text-xs text-gray-700">— María Luisa R. (Paciente con Diabetes)</p>
        </div>
        <div class="bg-white p-6 rounded-3xl shadow-sm border space-y-4">
          <div class="flex text-amber-500 gap-0.5"><span class="icon">star</span><span class="icon">star</span><span class="icon">star</span><span class="icon">star</span><span class="icon">star</span></div>
          <p class="text-sm text-gray-600 italic">"Los mejores especialistas de Sucre están aquí. Es muy fácil reservar la cita, subir el comprobante de pago y agendar en el calendario virtual."</p>
          <p class="font-bold text-xs text-gray-700">— Claudia S. (Paciente de Nutrición Clínica)</p>
        </div>
      </div>
    </div>
  </section>

  <!-- Banner unirse al equipo -->
  <section class="max-w-4xl mx-auto my-12 px-6">
    <div class="bg-gradient-to-br from-green-50 to-emerald-50 border border-green-200 rounded-3xl p-8 flex flex-col md:flex-row items-center justify-between gap-6">
      <div class="space-y-2 text-center md:text-left">
        <h3 class="text-xl font-black text-green-900">¿Eres un profesional y deseas unirte a nuestro equipo?</h3>
        <p class="text-green-700 text-xs md:text-sm max-w-lg leading-relaxed">Forma parte de NutriSucre, gestiona tu agenda, recibe pacientes de todo el país y obtén tu verificación profesional con nosotros.</p>
      </div>
      <button onclick="irRegistroProfesional()" class="ios-btn py-3 px-6 text-xs whitespace-nowrap">Comenzar postulación</button>
    </div>
  </section>

  <!-- ══ MODAL: Perfil Nutricionista Público ══ -->
  <div id="modalPerfil" class="ios-modal-bg" onclick="if(event.target===this)cerrarModalPerfil()">
    <div class="ios-modal max-w-2xl">
      <div class="flex justify-between items-center p-6 border-b border-[rgba(0,0,0,0.06)]">
        <div>
          <p id="detNombre" class="font-black text-[20px]"></p>
          <p id="detEsp" class="text-[13px] text-green-600 font-semibold"></p>
        </div>
        <button onclick="cerrarModalPerfil()" class="ios-btn-icon"><span class="icon">close</span></button>
      </div>
      
      <div class="p-6 space-y-6 overflow-y-auto max-h-[75vh]">
        <!-- Datos de contacto y precio -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
          <div class="text-center p-4 bg-gray-50 border rounded-2xl">
            <span class="icon text-gray-400">payments</span>
            <p class="text-[11px] text-gray-500 font-semibold uppercase mt-1">Precio Consulta</p>
            <p id="detPrecio" class="font-black text-lg text-gray-800 mt-1"></p>
          </div>
          <div class="text-center p-4 bg-gray-50 border rounded-2xl">
            <span class="icon text-gray-400">grade</span>
            <p class="text-[11px] text-gray-500 font-semibold uppercase mt-1">Calificación</p>
            <p id="detRating" class="font-black text-lg text-amber-500 mt-1"></p>
          </div>
          <div class="text-center p-4 bg-gray-50 border rounded-2xl">
            <span class="icon text-gray-400">forum</span>
            <p class="text-[11px] text-gray-500 font-semibold uppercase mt-1">Contacto Directo</p>
            <p id="detContacto" class="font-semibold text-xs text-green-700 mt-2 flex items-center justify-center gap-1"></p>
          </div>
        </div>
        
        <!-- Descripción -->
        <div>
          <h4 class="font-bold text-[14px] text-gray-700 uppercase tracking-wider mb-2">Acerca del Especialista</h4>
          <p id="detDesc" class="text-sm text-gray-600 leading-relaxed bg-green-50/50 p-4 border rounded-2xl"></p>
        </div>

        <!-- Información Académica -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div>
            <h4 class="font-bold text-[14px] text-gray-700 uppercase tracking-wider mb-2">Formación</h4>
            <p id="detUniversidad" class="text-xs text-gray-600"></p>
          </div>
          <div>
            <h4 class="font-bold text-[14px] text-gray-700 uppercase tracking-wider mb-2">Licencia Profesional</h4>
            <p id="detLicencia" class="text-xs text-gray-600"></p>
          </div>
        </div>

        <!-- CALENDARIO PÚBLICO -->
        <div class="border-t pt-5">
          <h4 class="font-black text-lg text-gray-800 mb-3 flex items-center gap-2">
            <span class="icon text-green-600">calendar_month</span> Agenda de Citas Disponibles
          </h4>
          <p class="text-xs text-gray-500 mb-4">Selecciona una fecha para visualizar las horas y agenda tu consulta. Código de colores: 
            <span class="badge badge-green">Disponible</span>
            <span class="badge badge-yellow">Pendiente</span>
            <span class="badge badge-red">Ocupado</span>
          </p>
          
          <!-- Selector de Fecha -->
          <div class="flex items-center gap-3 mb-5 max-w-xs">
            <input type="date" id="calendarDate" class="ios-input py-2 text-xs" onchange="cargarHorasCalendario()">
          </div>
          
          <!-- Horas -->
          <div id="slotsGrid" class="grid grid-cols-3 sm:grid-cols-4 gap-2">
            <!-- Cargado dinámicamente por JS -->
          </div>
          <p id="msgSinSlots" class="text-center text-xs text-gray-500 py-6 hidden">El nutricionista no tiene disponibilidad registrada para este día.</p>
        </div>
      </div>
      
      <div class="p-6 border-t bg-gray-50 flex items-center justify-between gap-4">
        <div>
          <p class="text-xs text-gray-500 font-semibold">Hora seleccionada</p>
          <p id="txtSlotSel" class="font-black text-sm text-[#1c1c1e]">Ninguna</p>
        </div>
        <button id="btnReservarCita" onclick="iniciarReserva()" class="ios-btn py-3 px-8 text-xs" disabled>Reservar Turno</button>
      </div>
    </div>
  </div>

  <!-- ══ MODAL: Proceso de Checkout y Pago ══ -->
  <div id="modalCheckout" class="ios-modal-bg" onclick="if(event.target===this)cerrarModalCheckout()">
    <div class="ios-modal max-w-lg">
      <div class="flex justify-between items-center p-6 border-b border-[rgba(0,0,0,0.06)]">
        <div>
          <p class="font-black text-[18px]">Confirmar Reserva y Pago</p>
          <p class="text-[12px] text-gray-500">Completa tu información clínica y sube tu comprobante de pago</p>
        </div>
        <button onclick="cerrarModalCheckout()" class="ios-btn-icon"><span class="icon">close</span></button>
      </div>
      
      <div class="p-6 space-y-5 overflow-y-auto max-h-[70vh]">
        <!-- Resumen -->
        <div class="p-4 bg-green-50 border border-green-200 rounded-2xl text-xs space-y-1">
          <p class="text-green-800 font-semibold">Resumen de la Reserva:</p>
          <p id="chkResumenCita" class="text-green-700"></p>
          <p id="chkPrecio" class="text-green-900 font-bold text-sm mt-1"></p>
        </div>

        <!-- Selección de Servicio Profesional -->
        <div class="space-y-1">
          <label class="text-[13px] font-semibold text-[#48484a] pl-1">Selecciona el Servicio Profesional</label>
          <select id="chkServicioSelect" class="ios-input mt-1" onchange="actualizarPrecioServicio()">
            <!-- Servicios cargados dinámicamente -->
          </select>
        </div>

        <!-- Datos Obligatorios del Paciente (CI y Celular) -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div class="space-y-1">
            <label class="text-[13px] font-semibold text-[#48484a] pl-1">C.I. / DNI del Paciente <span class="text-red-500">*</span></label>
            <input id="chkCI" type="text" class="ios-input" placeholder="Ej: 1234567 CH" value="<?= htmlspecialchars($usuarioLogueado['ci'] ?? '') ?>">
          </div>
          <div class="space-y-1">
            <label class="text-[13px] font-semibold text-[#48484a] pl-1">Celular de Contacto <span class="text-red-500">*</span></label>
            <input id="chkCelular" type="text" class="ios-input" placeholder="Ej: 71234567" value="<?= htmlspecialchars($usuarioLogueado['celular'] ?? '') ?>">
          </div>
        </div>

        <!-- Opciones de Cobro del Especialista -->
        <div class="border-t pt-4">
          <label class="text-[13px] font-bold text-gray-700 uppercase tracking-wider block mb-3">Información de Pago del Profesional</label>
          
          <div class="space-y-3" id="datosCobroProfesional">
            <!-- Datos bancarios, QR, etc. cargados dinámicamente -->
          </div>
        </div>

        <!-- Formulario de Pago del Paciente -->
        <div class="border-t pt-4 space-y-4">
          <label class="text-[13px] font-bold text-gray-700 uppercase tracking-wider block">Tu Pago Realizado</label>
          
          <div class="space-y-2">
            <label class="text-[12px] text-gray-500 font-semibold block">Método de Pago Utilizado <span class="text-red-500">*</span></label>
            <select id="chkMetodoPago" class="ios-input">
              <option value="">Selecciona...</option>
              <option value="QR">Pago por QR</option>
              <option value="Transferencia">Transferencia Bancaria</option>
              <option value="Deposito">Depósito Directo</option>
            </select>
          </div>
          
          <div class="space-y-2">
            <label class="text-[12px] text-gray-500 font-semibold block">Subir Comprobante de Pago <span class="text-xs text-gray-400 font-normal">(Opcional)</span></label>
            <input type="file" id="fileComprobante" accept="image/*,application/pdf" class="hidden" onchange="uploadComprobante()">
            <div class="flex items-center gap-4">
              <button type="button" onclick="document.getElementById('fileComprobante').click()" class="ios-btn-ghost py-2 px-4 text-xs">Subir archivo</button>
              <span id="fileNameComprobante" class="text-xs text-gray-500">Ningún archivo seleccionado</span>
              <input type="hidden" id="chkComprobanteUrl" value="">
            </div>
            <p class="text-[10px] text-gray-400">Se aceptan imágenes (PNG, JPG) o PDF (máx. 5MB). Al reservarlo, el especialista validará el depósito.</p>
          </div>
        </div>
        
        <div id="msgCheckout" class="hidden rounded-2xl px-4 py-3 text-[13px] font-semibold text-center"></div>
      </div>
      
      <div class="p-6 border-t flex gap-3">
        <button onclick="cerrarModalCheckout()" class="ios-btn-ghost flex-1">Cancelar</button>
        <button onclick="confirmarReservaCita()" id="btnConfirmarReserva" class="ios-btn flex-1">Confirmar Reserva</button>
      </div>
    </div>
  </div>

  <script>
  let todosNutris = [];
  let nutriSeleccionado = null;
  let slotSeleccionado = null;
  let usuarioAutenticado = <?= $usuarioLogueado ? 'true' : 'false' ?>;
  let serviciosNutri = [];

  // Al cargar, inicializar buscador y lista de profesionales
  document.addEventListener('DOMContentLoaded', () => {
      // Establecer fecha por defecto para calendario (mañana)
      const mañana = new Date();
      mañana.setDate(mañana.getDate() + 1);
      document.getElementById('calendarDate').value = mañana.toISOString().split('T')[0];
      document.getElementById('calendarDate').min = mañana.toISOString().split('T')[0];

      cargarEspecialistas();
  });

  async function cargarEspecialistas() {
      const res = await fetch('api/nutricionistas.php');
      const data = await res.json();
      todosNutris = Array.isArray(data) ? data : [];
      renderEspecialistas(todosNutris);
  }

  function renderEspecialistas(lista) {
      const g = document.getElementById('gridEspecialistas');
      document.getElementById('totalResultados').textContent = `${lista.length} profesional(es) encontrado(s)`;
      
      if (!lista.length) {
          g.innerHTML = '<div class="col-span-full ios-card p-12 text-center text-gray-500 text-sm">No se encontraron nutricionistas aprobados con los filtros aplicados.</div>';
          return;
      }

      g.innerHTML = lista.map(n => {
          const rating = parseFloat(n.rating || 5).toFixed(1);
          return `<div class="ios-card bg-white p-5 flex flex-col justify-between space-y-4">
              <div class="flex gap-4">
                  <img src="${n.foto || 'uploads/fotos/default.jpg'}" class="w-16 h-16 rounded-2xl object-cover border">
                  <div class="flex-1 min-w-0">
                      <p class="font-bold text-[16px] text-gray-800 truncate">${n.nombre}</p>
                      <p class="text-xs text-green-600 font-semibold truncate">${n.especialidad}</p>
                      <p class="text-xs text-gray-500 mt-1 flex items-center gap-1">
                          <span class="icon text-amber-500 text-sm" style="font-size:14px">grade</span> 
                          <strong>${rating}</strong> (${n.total_resenas || 0} reviews)
                      </p>
                  </div>
              </div>
              
              <div class="border-t pt-3 flex items-center justify-between text-xs text-gray-500">
                  <div>
                      <p>Precio base:</p>
                      <p class="font-bold text-gray-800 text-sm">Bs. ${parseFloat(n.precio).toFixed(2)}</p>
                  </div>
                  <div>
                      <p>Consulta:</p>
                      <p class="font-semibold text-gray-700">${n.duracion_consulta || 60} min · ${n.modalidad}</p>
                  </div>
              </div>
              
              <button onclick="verPerfilNutricionista(${n.id})" class="ios-btn-ghost w-full py-2.5 text-xs">Ver perfil y reservar</button>
          </div>`;
      }).join('');
  }

  function realizarBusqueda() {
      const q = document.getElementById('mainSearch').value.toLowerCase().trim();
      const filtrados = todosNutris.filter(n => 
          n.nombre.toLowerCase().includes(q) || 
          n.especialidad.toLowerCase().includes(q)
      );
      renderEspecialistas(filtrados);
  }

  function aplicarFiltros() {
      const esp = document.getElementById('filtroEspecialidad').value;
      const mod = document.getElementById('filtroModalidad').value;
      const prMax = parseFloat(document.getElementById('filtroPrecioMax').value) || null;
      const rat = parseFloat(document.getElementById('filtroRating').value) || null;

      let filtrados = todosNutris;

      if (esp) {
          filtrados = filtrados.filter(n => n.especialidad.toLowerCase().includes(esp.toLowerCase()));
      }
      if (mod) {
          filtrados = filtrados.filter(n => n.modalidad === mod || n.modalidad === 'Ambas');
      }
      if (prMax !== null) {
          filtrados = filtrados.filter(n => parseFloat(n.precio) <= prMax);
      }
      if (rat !== null) {
          filtrados = filtrados.filter(n => parseFloat(n.rating) >= rat);
      }

      renderEspecialistas(filtrados);
  }

  function limpiarFiltros() {
      document.getElementById('filtroEspecialidad').value = '';
      document.getElementById('filtroPrecioMax').value = '';
      document.getElementById('filtroModalidad').value = '';
      document.getElementById('filtroRating').value = '';
      renderEspecialistas(todosNutris);
  }

  // ── Perfil Nutricionista Modal ──
  async function verPerfilNutricionista(id) {
      const res = await fetch(`api/nutricionistas.php?id=${id}`);
      const n = await res.json();
      if (n.error) return alert(n.error);

      nutriSeleccionado = n;
      slotSeleccionado = null;

      document.getElementById('detNombre').textContent = n.nombre;
      document.getElementById('detEsp').textContent = n.especialidad;
      document.getElementById('detPrecio').textContent = `Bs. ${parseFloat(n.precio).toFixed(2)}`;
      document.getElementById('detRating').innerHTML = `<span class="icon" style="font-size:16px">grade</span> ${parseFloat(n.rating || 5.0).toFixed(1)}`;
      
      const whatsappLink = n.whatsapp 
          ? `<a href="https://wa.me/591${n.whatsapp}" target="_blank" class="flex items-center gap-1 text-green-600 font-bold hover:underline"><span class="icon" style="font-size:16px">chat</span> WhatsApp</a>` 
          : 'No registrado';
      document.getElementById('detContacto').innerHTML = whatsappLink;
      
      document.getElementById('detDesc').textContent = n.descripcion_serv || 'El especialista no ha cargado una descripción todavía.';
      document.getElementById('detUniversidad').innerHTML = `<strong>Univ:</strong> ${n.universidad || '—'}<br><strong>Título:</strong> ${n.titulo || '—'}`;
      document.getElementById('detLicencia').innerHTML = `<strong>Reg. Prof:</strong> ${n.registro_prof || '—'}<br><strong>Vence:</strong> ${n.licencia_vence || '—'}`;

      document.getElementById('txtSlotSel').textContent = 'Ninguna';
      document.getElementById('btnReservarCita').disabled = true;

      // Cargar disponibilidad
      cargarHorasCalendario();

      document.getElementById('modalPerfil').classList.add('open');
  }

  function cerrarModalPerfil() {
      document.getElementById('modalPerfil').classList.remove('open');
  }

  async function cargarHorasCalendario() {
      if (!nutriSeleccionado) return;
      const fecha = document.getElementById('calendarDate').value;
      const grid = document.getElementById('slotsGrid');
      const msg = document.getElementById('msgSinSlots');

      grid.innerHTML = '';
      msg.classList.add('hidden');

      try {
          const res = await fetch(`api/postulaciones.php?accion=disponibilidad&nutri_id=${nutriSeleccionado.id}&fecha=${fecha}`);
          const data = await res.json();
          const slots = data.slots || [];

          if (!slots.length) {
              msg.classList.remove('hidden');
              return;
          }

          grid.innerHTML = slots.map(sl => {
              const est = sl.estado; // disponible | pendiente | ocupado
              const click = est === 'disponible' ? `onclick="seleccionarSlot('${sl.hora}', this)"` : '';
              return `<button class="slot-btn text-xs font-semibold py-2.5 px-3 rounded-xl border text-center ${est}" ${click}>
                  ${sl.hora}
              </button>`;
          }).join('');
      } catch (e) {
          grid.innerHTML = '<p class="col-span-full text-center text-xs text-red-500">Error al cargar la disponibilidad.</p>';
      }
  }

  function seleccionarSlot(hora, btn) {
      slotSeleccionado = hora;
      document.getElementById('txtSlotSel').textContent = `${document.getElementById('calendarDate').value} a las ${hora}`;
      
      // Resaltar
      document.querySelectorAll('.slot-btn.disponible').forEach(b => b.classList.remove('selected'));
      btn.classList.add('selected');
      
      document.getElementById('btnReservarCita').disabled = false;
  }

  // ── Reserva e Inicio de Checkout ──
  function iniciarReserva() {
      if (!usuarioAutenticado) {
          alert('Debes iniciar sesión o registrarte como Paciente en la plataforma para poder agendar una cita.');
          window.location.href = 'login.php';
          return;
      }
      
      cerrarModalPerfil();
      
      // Cargar datos en Checkout
      const fecha = document.getElementById('calendarDate').value;
      document.getElementById('chkResumenCita').innerHTML = `Especialista: <strong>${nutriSeleccionado.nombre}</strong><br>Fecha: <strong>${fecha}</strong> · Hora: <strong>${slotSeleccionado}</strong>`;
      document.getElementById('chkPrecio').textContent = `Precio Cita: Bs. ${parseFloat(nutriSeleccionado.precio).toFixed(2)}`;
      
      // Cargar cobro profesional
      const cobro = document.getElementById('datosCobroProfesional');
      cobro.innerHTML = '';
      
      let metodosHTML = '';
      if (nutriSeleccionado.pago_qr_habilitado && nutriSeleccionado.qr_code) {
          metodosHTML += `<div class="bg-white p-4 border rounded-2xl flex items-start gap-4">
              <img src="${nutriSeleccionado.qr_code}" class="w-24 h-24 rounded-lg object-contain border">
              <div>
                  <p class="font-bold text-xs text-gray-800">1. Escanea el código QR de Pago</p>
                  <p class="text-[10px] text-gray-500 mt-1">Descarga o escanea la imagen desde tu app bancaria móvil para realizar el pago correspondiente.</p>
              </div>
          </div>`;
      }
      if (nutriSeleccionado.pago_transferencia_habilitado) {
          metodosHTML += `<div class="bg-white p-4 border rounded-2xl space-y-1.5 text-xs text-gray-700">
              <p class="font-bold text-gray-800">2. Transferencia Bancaria Directa</p>
              <p><strong>Banco:</strong> ${nutriSeleccionado.banco || '—'}</p>
              <p><strong>Número Cuenta:</strong> ${nutriSeleccionado.nro_cuenta || '—'}</p>
              <p><strong>Titular:</strong> ${nutriSeleccionado.titular_cuenta || '—'}</p>
              ${nutriSeleccionado.datos_transferencia_adicional ? `<p class="text-[10px] text-gray-500 bg-gray-50 p-2 rounded-lg border mt-1 font-mono">${nutriSeleccionado.datos_transferencia_adicional}</p>` : ''}
          </div>`;
      }
      if (nutriSeleccionado.pago_deposito_habilitado) {
          metodosHTML += `<div class="bg-white p-4 border rounded-2xl text-xs text-gray-700">
              <p class="font-bold text-gray-800">3. Depósito Bancario</p>
              <p>Realiza tu depósito directo en taquilla o corresponsal autorizado a nombre del titular de la cuenta.</p>
          </div>`;
      }
      
      if (!metodosHTML) {
          metodosHTML = '<p class="text-xs text-amber-600 bg-amber-50 border p-3 rounded-xl">El profesional no ha habilitado métodos de pago en línea todavía. Ponte en contacto directamente para definir el pago.</p>';
      }
      cobro.innerHTML = metodosHTML;

      // Cargar lista de servicios adicionales del profesional para que el paciente los asocie opcionalmente
      cargarServiciosCheckout(nutriSeleccionado.usuario_id);

      document.getElementById('modalCheckout').classList.add('open');
  }

  function cerrarModalCheckout() {
      document.getElementById('modalCheckout').classList.remove('open');
  }

  async function cargarServiciosCheckout(nutriUsuarioId) {
      const select = document.getElementById('chkServicioSelect');
      select.innerHTML = '<option value="">Cargando servicios...</option>';
      
      try {
          const res = await fetch(`api/servicios.php?nutri_id=${nutriUsuarioId}`);
          const data = await res.json();
          serviciosNutri = Array.isArray(data) ? data.filter(s => s.estado === 'Aprobado') : [];
          
          let options = '<option value="">Consulta General (Por defecto)</option>';
          options += serviciosNutri.map(s => `<option value="${s.id}">Plan: ${s.titulo} (Bs. ${parseFloat(s.precio).toFixed(2)})</option>`).join('');
          select.innerHTML = options;
      } catch (e) {
          select.innerHTML = '<option value="">Consulta General (Por defecto)</option>';
      }
  }

  function actualizarPrecioServicio() {
      const select = document.getElementById('chkServicioSelect');
      const val = select.value;
      if (!val) {
          document.getElementById('chkPrecio').textContent = `Precio Cita: Bs. ${parseFloat(nutriSeleccionado.precio).toFixed(2)}`;
      } else {
          const srv = serviciosNutri.find(s => s.id == val);
          if (srv) {
              document.getElementById('chkPrecio').textContent = `Precio Plan: Bs. ${parseFloat(srv.precio).toFixed(2)}`;
          }
      }
  }

  async function uploadComprobante() {
      const input = document.getElementById('fileComprobante');
      const name = document.getElementById('fileNameComprobante');
      const urlHidden = document.getElementById('chkComprobanteUrl');
      
      if (!input.files.length) return;
      name.textContent = 'Subiendo comprobante...';
      
      const formData = new FormData();
      formData.append('archivo', input.files[0]);
      formData.append('tipo', 'comprobantes');
      
      try {
          const res = await fetch('api/upload.php', {
              method: 'POST',
              body: formData
          });
          const data = await res.json();
          if (data.ok) {
              name.textContent = '✅ ' + input.files[0].name;
              urlHidden.value = data.url;
          } else {
              name.textContent = '❌ Error al subir comprobante';
              alert('Error: ' + (data.error || 'Carga fallida'));
          }
      } catch (e) {
          name.textContent = '❌ Error en la carga';
      }
  }

  async function confirmarReservaCita() {
      const ci = document.getElementById('chkCI').value.trim();
      const celular = document.getElementById('chkCelular').value.trim();
      const metodoPago = document.getElementById('chkMetodoPago').value;
      const comprobante = document.getElementById('chkComprobanteUrl').value;
      const servicioId = document.getElementById('chkServicioSelect').value;
      const fecha = document.getElementById('calendarDate').value;
      
      if (!ci || !celular || !metodoPago) {
          mostrarMsgCheckout('Completa el CI, celular y selecciona tu método de pago.', 'error');
          return;
      }

      const btn = document.getElementById('btnConfirmarReserva');
      btn.disabled = true; btn.textContent = 'Enviando...';

      const payload = {
          nutricionista_id: nutriSeleccionado.id,
          fecha: fecha,
          hora: slotSeleccionado,
          servicio_id: servicioId ? parseInt(servicioId) : null,
          metodo_pago: metodoPago,
          comprobante_pago: comprobante
      };

      try {
          const res = await fetch('api/citas.php', {
              method: 'POST',
              headers: { 'Content-Type': 'application/json' },
              body: JSON.stringify(payload)
          });
          const data = await res.json();
          
          btn.disabled = false; btn.textContent = 'Confirmar Reserva';
          
          if (data.ok) {
              alert('✅ ' + data.mensaje);
              cerrarModalCheckout();
              window.location.href = 'citas.php';
          } else {
              mostrarMsgCheckout(data.error || 'No se pudo agendar la cita', 'error');
          }
      } catch (e) {
          btn.disabled = false; btn.textContent = 'Confirmar Reserva';
          mostrarMsgCheckout('Error de red. Intenta nuevamente.', 'error');
      }
  }

  function mostrarMsgCheckout(txt, tipo) {
      const el = document.getElementById('msgCheckout');
      el.textContent = txt;
      el.className = `rounded-2xl px-4 py-3 text-[13px] font-semibold text-center ${tipo === 'ok' ? 'bg-green-100 text-green-800' : 'bg-red-50 text-red-700'}`;
      el.classList.remove('hidden');
      setTimeout(() => el.classList.add('hidden'), 5000);
  }

  function irRegistroProfesional() {
      if (usuarioAutenticado) {
          window.location.href = 'registro_nutricionista.php';
      } else {
          window.location.href = 'login.php';
      }
  }
  </script>
</body>
</html>

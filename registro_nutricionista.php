<?php
// ============================================================
//  registro_nutricionista.php  —  Formulario de postulación
//  Solo accesible para usuarios con rol Nutricionista
// ============================================================
session_start();
if (empty($_SESSION['usuario']))                       { header('Location: login.php'); exit; }
if ($_SESSION['usuario']['rol'] !== 'Nutricionista')   { header('Location: dashboard.php'); exit; }
$usuario = $_SESSION['usuario'];
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>NutriSucre - Verificación Profesional</title>
<script src="https://cdn.tailwindcss.com"></script>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet">
<style>
  body { font-family: 'Inter', sans-serif; background: #f8fafb; }
  .material-symbols-outlined { font-variation-settings: 'FILL' 0, 'wght' 300; }
  /* Paso activo en el stepper */
  .step { transition: all .3s; }
  .step.activo .step-num { background: #22c55e; color: white; }
  .step.completado .step-num { background: #16a34a; color: white; }
  .step.completado .step-num::after { content:'✓'; }
  .step-num { width:32px; height:32px; border-radius:50%; border:2px solid #d1d5db;
              display:flex; align-items:center; justify-content:center; font-size:13px; font-weight:700; }
  /* Secciones del formulario */
  .seccion { display: none; }
  .seccion.activa { display: block; }
  .input-field { width:100%; border:1px solid #e5e7eb; border-radius:12px; padding:12px 16px;
                 font-size:14px; outline:none; transition:border-color .2s; }
  .input-field:focus { border-color:#22c55e; }
  .label { display:block; font-size:13px; font-weight:600; color:#374151; margin-bottom:6px; }
  .req { color:#ef4444; }
</style>
</head>
<body>

<!-- Header simple -->
<header class="bg-white border-b px-6 py-4 flex items-center justify-between sticky top-0 z-50">
  <div class="flex items-center gap-3">
    <span class="material-symbols-outlined text-[#22c55e] text-3xl">nutrition</span>
    <span class="font-black text-xl text-[#22c55e]">NutriSucre</span>
    <span class="text-gray-300 mx-2">|</span>
    <span class="text-gray-600 font-medium text-sm">Verificación Profesional</span>
  </div>
  <a href="dashboard.php" class="text-sm text-gray-500 hover:text-gray-700 flex items-center gap-1">
    <span class="material-symbols-outlined text-lg">arrow_back</span> Volver
  </a>
</header>

<main class="max-w-3xl mx-auto p-6">

  <!-- Banner informativo -->
  <div class="bg-amber-50 border border-amber-200 rounded-2xl p-5 mb-8 flex gap-4">
    <span class="material-symbols-outlined text-amber-500 text-3xl flex-shrink-0 mt-0.5">verified_user</span>
    <div>
      <p class="font-bold text-amber-800">Verificación profesional requerida</p>
      <p class="text-amber-700 text-sm mt-1">Para ofrecer servicios en NutriSucre debes completar este formulario. Tu perfil quedará en estado <strong>PENDIENTE</strong> hasta que un administrador revise y apruebe tu documentación.</p>
    </div>
  </div>

  <!-- Stepper de pasos -->
  <div class="flex items-center justify-between mb-10 relative">
    <div class="absolute top-4 left-0 right-0 h-0.5 bg-gray-200 z-0"></div>
    <?php
    $pasos = ['Datos personales','Formación académica','Licencia','Especialidades','Experiencia','Servicios','Evaluación técnica'];
    foreach ($pasos as $i => $paso):
    ?>
    <div class="step flex flex-col items-center gap-1 z-10 <?= $i === 0 ? 'activo' : '' ?>" id="step<?= $i ?>">
      <div class="step-num bg-white"><?= $i + 1 ?></div>
      <span class="text-xs text-gray-500 hidden md:block text-center w-16"><?= $paso ?></span>
    </div>
    <?php endforeach; ?>
  </div>

  <!-- Titulo de sección actual -->
  <div class="bg-white rounded-3xl shadow-sm border p-8 mb-6">
    <div id="tituloSeccion" class="flex items-center gap-3 mb-6">
      <span id="iconoSeccion" class="material-symbols-outlined text-3xl text-[#22c55e]">person</span>
      <div>
        <h2 id="textoSeccion" class="text-2xl font-bold">Información Personal</h2>
        <p id="descSeccion" class="text-gray-500 text-sm">Datos básicos de identificación profesional</p>
      </div>
    </div>

    <!-- ══════ SECCIÓN 0: Datos personales ══════ -->
    <div class="seccion activa" id="sec0">
      <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
        <div class="md:col-span-2">
          <label class="label">Nombre completo <span class="req">*</span></label>
          <input class="input-field" id="p_nombre" type="text" value="<?= htmlspecialchars($usuario['nombre']) ?>" readonly>
        </div>
        <div>
          <label class="label">C.I. / DNI / Pasaporte <span class="req">*</span></label>
          <input class="input-field" id="p_ci" type="text" placeholder="Ej: 12345678">
        </div>
        <div>
          <label class="label">Fecha de nacimiento</label>
          <input class="input-field" id="p_nacimiento" type="date">
        </div>
        <div>
          <label class="label">Sexo</label>
          <select class="input-field" id="p_sexo">
            <option value="">Prefiero no decir</option>
            <option>Masculino</option><option>Femenino</option>
          </select>
        </div>
        <div>
          <label class="label">País <span class="req">*</span></label>
          <input class="input-field" id="p_pais" type="text" value="Bolivia">
        </div>
        <div>
          <label class="label">Ciudad <span class="req">*</span></label>
          <input class="input-field" id="p_ciudad" type="text" placeholder="Ej: Sucre">
        </div>
        <div>
          <label class="label">Dirección profesional</label>
          <input class="input-field" id="p_direccion" type="text" placeholder="Ej: Av. Venezuela #245">
        </div>
        <div>
          <label class="label">Teléfono / Celular <span class="req">*</span></label>
          <input class="input-field" id="p_telefono" type="tel" placeholder="+591 7XXXXXXX">
        </div>
      </div>
    </div>

    <!-- ══════ SECCIÓN 1: Formación académica ══════ -->
    <div class="seccion" id="sec1">
      <div class="bg-blue-50 border border-blue-200 rounded-xl p-4 mb-5 text-sm text-blue-700">
        <strong>Carreras aceptadas:</strong> Nutrición, Nutrición Clínica, Nutrición y Dietética.<br>
        <strong>No aceptadas:</strong> Coaching nutricional, Entrenador fitness, Biología, Enfermería.
      </div>
      <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
        <div class="md:col-span-2">
          <label class="label">Universidad <span class="req">*</span></label>
          <input class="input-field" id="f_universidad" type="text" placeholder="Ej: USFX - Universidad Mayor de San Francisco Xavier">
        </div>
        <div class="md:col-span-2">
          <label class="label">Carrera cursada <span class="req">*</span></label>
          <input class="input-field" id="f_carrera" type="text" placeholder="Ej: Licenciatura en Nutrición y Dietética">
        </div>
        <div>
          <label class="label">Año de egreso</label>
          <input class="input-field" id="f_egreso" type="number" min="1970" max="2030" placeholder="Ej: 2018">
        </div>
        <div>
          <label class="label">Año de titulación</label>
          <input class="input-field" id="f_titulacion" type="number" min="1970" max="2030" placeholder="Ej: 2019">
        </div>
        <div class="md:col-span-2">
          <label class="label">Título profesional obtenido <span class="req">*</span></label>
          <input class="input-field" id="f_titulo" type="text" placeholder="Ej: Licenciada en Nutrición y Dietética">
        </div>
        <div class="md:col-span-2">
          <label class="label">Subir título profesional</label>
          <input class="input-field" id="f_doc_titulo" type="file" accept=".pdf,.jpg,.jpeg,.png">
          <p class="text-xs text-gray-400 mt-1">PDF o imagen. El equipo de NutriSucre verificará el documento.</p>
        </div>
      </div>
    </div>

    <!-- ══════ SECCIÓN 2: Licencia ══════ -->
    <div class="seccion" id="sec2">
      <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
        <div class="md:col-span-2">
          <label class="label">Número de registro profesional <span class="req">*</span></label>
          <input class="input-field" id="l_registro" type="text" placeholder="Ej: NUT-2019-0456">
        </div>
        <div class="md:col-span-2">
          <label class="label">Institución reguladora <span class="req">*</span></label>
          <input class="input-field" id="l_institucion" type="text" placeholder="Ej: Ministerio de Salud de Bolivia">
        </div>
        <div>
          <label class="label">Fecha de emisión</label>
          <input class="input-field" id="l_inicio" type="date">
        </div>
        <div>
          <label class="label">Fecha de vencimiento</label>
          <input class="input-field" id="l_vence" type="date">
        </div>
        <div class="md:col-span-2">
          <label class="label">Certificado de habilitación vigente</label>
          <input class="input-field" id="l_doc" type="file" accept=".pdf,.jpg,.jpeg,.png">
        </div>
      </div>
    </div>

    <!-- ══════ SECCIÓN 3: Especialidades ══════ -->
    <div class="seccion" id="sec3">
      <p class="text-sm text-gray-600 mb-4">Selecciona tus especialidades y completa la información de cada una.</p>
      <div class="grid grid-cols-2 md:grid-cols-3 gap-3 mb-6" id="gridEspecialidades">
        <?php
        $esps = ['Nutrición clínica','Nutrición deportiva','Nutrición pediátrica',
                 'Nutrición hospitalaria','Nutrición geriátrica','Nutrición oncológica',
                 'Obesidad y metabolismo','Diabetes','Nutrición renal',
                 'Nutrición para embarazo','Trastornos alimenticios','Otras'];
        foreach ($esps as $e):
        ?>
        <label class="flex items-start gap-2 p-3 border rounded-xl cursor-pointer hover:border-[#22c55e] hover:bg-green-50 transition-all text-sm">
          <input type="checkbox" class="chk-esp mt-0.5" value="<?= $e ?>">
          <span><?= $e ?></span>
        </label>
        <?php endforeach; ?>
      </div>
      <div id="detallesEsp" class="space-y-4"></div>
    </div>

    <!-- ══════ SECCIÓN 4: Experiencia laboral ══════ -->
    <div class="seccion" id="sec4">
      <div id="listaExp" class="space-y-5"></div>
      <button onclick="agregarExp()"
              class="mt-4 flex items-center gap-2 text-[#22c55e] font-semibold text-sm hover:underline">
        <span class="material-symbols-outlined text-xl">add_circle</span> Agregar experiencia laboral
      </button>
    </div>

    <!-- ══════ SECCIÓN 5: Servicios ══════ -->
    <div class="seccion" id="sec5">
      <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
        <div>
          <label class="label">Tipo de consulta <span class="req">*</span></label>
          <input class="input-field" id="s_tipo" type="text" placeholder="Ej: Consulta nutricional inicial">
        </div>
        <div>
          <label class="label">Precio (Bs.) <span class="req">*</span></label>
          <input class="input-field" id="s_precio" type="number" min="0" placeholder="Ej: 150">
        </div>
        <div>
          <label class="label">Duración de consulta <span class="req">*</span></label>
          <select class="input-field" id="s_duracion">
            <option value="30">30 minutos</option>
            <option value="45">45 minutos</option>
            <option value="60" selected>1 hora</option>
            <option value="90">1 hora 30 min</option>
          </select>
        </div>
        <div>
          <label class="label">Modalidad <span class="req">*</span></label>
          <select class="input-field" id="s_modalidad">
            <option>Virtual</option><option>Presencial</option><option>Ambas</option>
          </select>
        </div>
        <div>
          <label class="label">Idiomas</label>
          <input class="input-field" id="s_idiomas" type="text" value="Español" placeholder="Ej: Español, Inglés">
        </div>
        <div>
          <label class="label">Máx. pacientes por día</label>
          <input class="input-field" id="s_max_pac" type="number" min="1" max="20" value="8">
        </div>
        <div class="md:col-span-2">
          <label class="label">Descripción profesional</label>
          <textarea class="input-field" id="s_desc" rows="3" placeholder="Describe tu enfoque, metodología y lo que ofreces a tus pacientes..."></textarea>
        </div>
        <!-- Horarios por día -->
        <div class="md:col-span-2">
          <label class="label">Días y horarios de atención</label>
          <div class="space-y-2" id="horariosGrid">
            <?php $dias = ['Lunes','Martes','Miércoles','Jueves','Viernes','Sábado','Domingo']; ?>
            <?php foreach ($dias as $i => $dia): ?>
            <div class="flex items-center gap-3 text-sm">
              <label class="flex items-center gap-2 w-28">
                <input type="checkbox" class="chk-dia" value="<?= $i ?>" <?= $i < 5 ? 'checked' : '' ?>>
                <span><?= $dia ?></span>
              </label>
              <input type="time" class="input-field w-28 py-2" data-dia="<?= $i ?>" data-tipo="inicio" value="09:00">
              <span class="text-gray-400">a</span>
              <input type="time" class="input-field w-28 py-2" data-dia="<?= $i ?>" data-tipo="fin" value="17:00">
            </div>
            <?php endforeach; ?>
          </div>
        </div>
      </div>
    </div>

    <!-- ══════ SECCIÓN 6: Evaluación técnica ══════ -->
    <div class="seccion" id="sec6">
      <div class="bg-amber-50 border border-amber-200 rounded-xl p-4 mb-6 text-sm text-amber-700">
        <strong>Evaluación técnica obligatoria</strong> — Sus respuestas serán analizadas para verificar su conocimiento profesional. Responda con la mayor precisión y extensión posible.
      </div>
      <div class="space-y-6">
        <?php
        $preguntas = [
            '¿Ante un paciente con obesidad y diabetes tipo 2, qué priorizaría en su intervención nutricional? Explique su razonamiento clínico.',
            '¿Qué indicadores utiliza para evaluar el estado nutricional de un adulto? Mencione al menos 5 y explique cada uno.',
            '¿Cómo diseña un plan alimenticio personalizado? Describa su metodología paso a paso.',
            'Explique la diferencia entre desnutrición y malnutrición, con ejemplos clínicos de cada concepto.',
            '¿Cómo maneja pacientes con baja adherencia al tratamiento nutricional? Describa estrategias concretas.',
        ];
        foreach ($preguntas as $i => $pregunta):
        ?>
        <div>
          <label class="label"><?= $i+1 ?>. <?= $pregunta ?> <span class="req">*</span></label>
          <textarea class="input-field" id="t_resp<?= $i+1 ?>" rows="4"
                    placeholder="Escriba su respuesta aquí (mínimo 3 líneas)..."></textarea>
          <p class="text-xs text-gray-400 mt-1" id="cnt_resp<?= $i+1 ?>">0 caracteres</p>
        </div>
        <?php endforeach; ?>
      </div>

      <!-- Declaración legal -->
      <div class="mt-8 bg-gray-50 rounded-2xl p-5 border">
        <label class="flex items-start gap-3 cursor-pointer">
          <input type="checkbox" id="chk_legal" class="mt-1 w-4 h-4 accent-[#22c55e]">
          <span class="text-sm text-gray-700 leading-relaxed">
            <strong>Declaración legal:</strong> Declaro que toda la información proporcionada es verdadera y verificable. 
            Entiendo que la falsificación documental o información falsa resultará en el 
            <strong>rechazo permanente del perfil</strong> y posibles acciones legales.
          </span>
        </label>
      </div>
    </div>

  </div><!-- /card -->

  <!-- Botones de navegación -->
  <div class="flex justify-between items-center">
    <button id="btnAnterior" onclick="irSeccion(-1)" class="hidden flex items-center gap-2 px-6 py-3 border rounded-2xl font-semibold text-sm hover:bg-gray-50">
      <span class="material-symbols-outlined">arrow_back</span> Anterior
    </button>
    <div></div>
    <button id="btnSiguiente" onclick="irSeccion(1)"
            class="flex items-center gap-2 px-8 py-3 bg-[#22c55e] text-white rounded-2xl font-bold text-sm hover:bg-[#16a34a] transition-colors">
      Siguiente <span class="material-symbols-outlined">arrow_forward</span>
    </button>
    <button id="btnEnviar" onclick="enviarPostulacion()" class="hidden flex items-center gap-2 px-8 py-3 bg-[#22c55e] text-white rounded-2xl font-bold text-sm hover:bg-[#16a34a] transition-colors">
      <span class="material-symbols-outlined">send</span> Enviar postulación
    </button>
  </div>

  <!-- Feedback final -->
  <div id="msgFinal" class="hidden mt-6 px-6 py-5 rounded-2xl text-sm font-medium text-center"></div>
</main>

<script>
// ─────────────────────────────────────────
//  Estado del formulario multi-paso
// ─────────────────────────────────────────
let seccionActual = 0;
const TOTAL_SECCIONES = 7;

const META_SECCIONES = [
    { titulo: 'Información Personal',     desc: 'Datos básicos de identificación',        icono: 'person' },
    { titulo: 'Formación Académica',       desc: 'Estudios universitarios y titulación',   icono: 'school' },
    { titulo: 'Licencia Profesional',      desc: 'Habilitación y registro oficial',        icono: 'badge' },
    { titulo: 'Especialidades',            desc: 'Áreas de práctica profesional',          icono: 'medical_services' },
    { titulo: 'Experiencia Laboral',       desc: 'Historial de trabajo profesional',       icono: 'work_history' },
    { titulo: 'Servicios',                 desc: 'Modalidad, precio y disponibilidad',     icono: 'calendar_month' },
    { titulo: 'Evaluación Técnica',        desc: 'Preguntas de conocimiento profesional',  icono: 'quiz' },
];

// Contadores de caracteres en respuestas técnicas
for (let i = 1; i <= 5; i++) {
    document.getElementById('t_resp' + i).addEventListener('input', function() {
        document.getElementById('cnt_resp' + i).textContent = this.value.length + ' caracteres';
    });
}

// Actualizar la UI de la sección
function actualizarUI() {
    // Ocultar todas las secciones y mostrar la actual
    document.querySelectorAll('.seccion').forEach((s, i) => {
        s.classList.toggle('activa', i === seccionActual);
    });

    // Actualizar stepper
    document.querySelectorAll('.step').forEach((s, i) => {
        s.classList.remove('activo','completado');
        if (i === seccionActual) s.classList.add('activo');
        if (i < seccionActual)   s.classList.add('completado');
    });

    // Actualizar título de sección
    const meta = META_SECCIONES[seccionActual];
    document.getElementById('textoSeccion').textContent = meta.titulo;
    document.getElementById('descSeccion').textContent  = meta.desc;
    document.getElementById('iconoSeccion').textContent = meta.icono;

    // Botones de navegación
    document.getElementById('btnAnterior').classList.toggle('hidden', seccionActual === 0);
    document.getElementById('btnSiguiente').classList.toggle('hidden', seccionActual === TOTAL_SECCIONES - 1);
    document.getElementById('btnEnviar').classList.toggle('hidden', seccionActual !== TOTAL_SECCIONES - 1);
}

function irSeccion(delta) {
    if (!validarSeccionActual()) return;
    seccionActual = Math.max(0, Math.min(TOTAL_SECCIONES - 1, seccionActual + delta));
    actualizarUI();
    window.scrollTo({ top: 0, behavior: 'smooth' });
}

// Validación por sección
function validarSeccionActual() {
    if (seccionActual === 0) {
        if (!document.getElementById('p_ci').value.trim())      { alerta('El CI/DNI es obligatorio'); return false; }
        if (!document.getElementById('p_ciudad').value.trim())  { alerta('La ciudad es obligatoria'); return false; }
        if (!document.getElementById('p_telefono').value.trim()){ alerta('El teléfono es obligatorio'); return false; }
    }
    if (seccionActual === 1) {
        if (!document.getElementById('f_universidad').value.trim()) { alerta('La universidad es obligatoria'); return false; }
        if (!document.getElementById('f_carrera').value.trim())     { alerta('La carrera es obligatoria'); return false; }
        if (!document.getElementById('f_titulo').value.trim())      { alerta('El título es obligatorio'); return false; }
    }
    if (seccionActual === 2) {
        if (!document.getElementById('l_registro').value.trim())   { alerta('El número de registro es obligatorio'); return false; }
        if (!document.getElementById('l_institucion').value.trim()){ alerta('La institución reguladora es obligatoria'); return false; }
    }
    if (seccionActual === 5) {
        if (!document.getElementById('s_tipo').value.trim())  { alerta('El tipo de consulta es obligatorio'); return false; }
        if (!document.getElementById('s_precio').value)       { alerta('El precio es obligatorio'); return false; }
    }
    if (seccionActual === 6) {
        if (!document.getElementById('chk_legal').checked) { alerta('Debes aceptar la declaración legal'); return false; }
        for (let i = 1; i <= 5; i++) {
            const v = document.getElementById('t_resp' + i).value.trim();
            if (v.length < 30) { alerta('La respuesta ' + i + ' es demasiado corta (mínimo 30 caracteres)'); return false; }
        }
    }
    return true;
}

function alerta(msg) {
    const el = document.getElementById('msgFinal');
    el.textContent = '⚠ ' + msg;
    el.className = 'mt-6 px-6 py-4 rounded-2xl text-sm font-medium text-center bg-red-100 text-red-700';
    el.classList.remove('hidden');
    setTimeout(() => el.classList.add('hidden'), 4000);
}

// ─────────────────────────────────────────
//  Especialidades — checkboxes dinámicos
// ─────────────────────────────────────────
document.querySelectorAll('.chk-esp').forEach(chk => {
    chk.addEventListener('change', actualizarDetallesEsp);
});

function actualizarDetallesEsp() {
    const seleccionadas = Array.from(document.querySelectorAll('.chk-esp:checked')).map(c => c.value);
    const contenedor    = document.getElementById('detallesEsp');
    contenedor.innerHTML = seleccionadas.map(esp => `
        <div class="bg-gray-50 rounded-2xl p-4 border">
            <p class="font-semibold text-sm mb-3 text-[#22c55e]">${esp}</p>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="label">Años de experiencia</label>
                    <input class="input-field" type="number" min="0" max="50"
                           data-esp="${esp}" data-campo="años" placeholder="Ej: 3">
                </div>
                <div>
                    <label class="label">Certificaciones / Diplomados</label>
                    <input class="input-field" type="text"
                           data-esp="${esp}" data-campo="certs" placeholder="Ej: Diplomado ALANPE 2022">
                </div>
            </div>
        </div>
    `).join('');
}

// ─────────────────────────────────────────
//  Experiencia laboral dinámica
// ─────────────────────────────────────────
let expCount = 0;
function agregarExp() {
    const id = expCount++;
    const div = document.createElement('div');
    div.className = 'bg-gray-50 rounded-2xl p-5 border relative';
    div.id = 'exp_' + id;
    div.innerHTML = `
        <button onclick="document.getElementById('exp_${id}').remove()"
                class="absolute top-3 right-3 text-gray-400 hover:text-red-500">
            <span class="material-symbols-outlined text-xl">delete</span>
        </button>
        <p class="font-semibold text-sm mb-4 text-[#22c55e]">Experiencia ${id + 1}</p>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div><label class="label">Institución</label>
                 <input class="input-field exp-campo" data-id="${id}" data-c="institucion" type="text" placeholder="Hospital / Clínica / Consultorio"></div>
            <div><label class="label">Cargo</label>
                 <input class="input-field exp-campo" data-id="${id}" data-c="cargo" type="text" placeholder="Ej: Nutricionista clínica"></div>
            <div><label class="label">Fecha inicio</label>
                 <input class="input-field exp-campo" data-id="${id}" data-c="inicio" type="month"></div>
            <div><label class="label">Fecha fin (vacío = actual)</label>
                 <input class="input-field exp-campo" data-id="${id}" data-c="fin" type="month"></div>
            <div class="md:col-span-2"><label class="label">Funciones principales</label>
                 <textarea class="input-field exp-campo" data-id="${id}" data-c="funciones" rows="2" placeholder="Descripción de funciones realizadas..."></textarea></div>
        </div>
    `;
    document.getElementById('listaExp').appendChild(div);
}
// Agregar una entrada vacía de inicio
agregarExp();

// ─────────────────────────────────────────
//  Enviar postulación
// ─────────────────────────────────────────
async function enviarPostulacion() {
    if (!validarSeccionActual()) return;

    const btn = document.getElementById('btnEnviar');
    btn.disabled = true; btn.textContent = 'Enviando...';

    // Recolectar especialidades con sus detalles
    const especialidades = Array.from(document.querySelectorAll('.chk-esp:checked')).map(chk => {
        const esp = chk.value;
        return {
            nombre: esp,
            años:   document.querySelector(`[data-esp="${esp}"][data-campo="años"]`)?.value || '',
            certs:  document.querySelector(`[data-esp="${esp}"][data-campo="certs"]`)?.value || '',
        };
    });

    // Recolectar experiencia laboral
    const experiencia = [];
    document.querySelectorAll('[data-id]').forEach(el => {
        const id = el.dataset.id;
        if (!experiencia[id]) experiencia[id] = {};
        experiencia[id][el.dataset.c] = el.value;
    });

    // Recolectar horarios
    const horarios = [];
    document.querySelectorAll('.chk-dia:checked').forEach(chk => {
        const dia = chk.value;
        const inicio = document.querySelector(`[data-dia="${dia}"][data-tipo="inicio"]`)?.value || '09:00';
        const fin    = document.querySelector(`[data-dia="${dia}"][data-tipo="fin"]`)?.value || '17:00';
        horarios.push({ dia: parseInt(dia), inicio, fin });
    });

    const payload = {
        ci: document.getElementById('p_ci').value,
        fecha_nacimiento: document.getElementById('p_nacimiento').value,
        sexo: document.getElementById('p_sexo').value,
        pais: document.getElementById('p_pais').value,
        ciudad: document.getElementById('p_ciudad').value,
        direccion_prof: document.getElementById('p_direccion').value,
        telefono: document.getElementById('p_telefono').value,
        universidad: document.getElementById('f_universidad').value,
        carrera: document.getElementById('f_carrera').value,
        anio_egreso: document.getElementById('f_egreso').value,
        anio_titulacion: document.getElementById('f_titulacion').value,
        titulo_prof: document.getElementById('f_titulo').value,
        registro_prof: document.getElementById('l_registro').value,
        institucion_reg: document.getElementById('l_institucion').value,
        licencia_inicio: document.getElementById('l_inicio').value,
        licencia_vence: document.getElementById('l_vence').value,
        especialidades,
        experiencia: experiencia.filter(Boolean),
        tipo_consulta: document.getElementById('s_tipo').value,
        precio: document.getElementById('s_precio').value,
        duracion_consulta: document.getElementById('s_duracion').value,
        modalidad: document.getElementById('s_modalidad').value,
        descripcion_serv: document.getElementById('s_desc').value,
        idiomas: document.getElementById('s_idiomas').value,
        horarios,
        max_pacientes_dia: document.getElementById('s_max_pac').value,
        resp_tecnica_1: document.getElementById('t_resp1').value,
        resp_tecnica_2: document.getElementById('t_resp2').value,
        resp_tecnica_3: document.getElementById('t_resp3').value,
        resp_tecnica_4: document.getElementById('t_resp4').value,
        resp_tecnica_5: document.getElementById('t_resp5').value,
    };

    const res  = await fetch('api/postulaciones.php', {
        method: 'POST', headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(payload)
    });
    const data = await res.json();

    btn.disabled = false; btn.textContent = 'Enviar postulación';

    if (data.ok) {
        const el = document.getElementById('msgFinal');
        el.innerHTML = `
            <div class="text-center">
                <span class="material-symbols-outlined text-5xl text-[#22c55e]">check_circle</span>
                <h3 class="text-xl font-bold mt-3">¡Postulación enviada correctamente!</h3>
                <p class="text-gray-600 mt-2">Tu puntaje técnico: <strong>${data.puntaje}/100</strong></p>
                <p class="text-gray-500 text-sm mt-2">Un administrador revisará tu documentación. Te notificaremos por email cuando tu perfil sea aprobado.</p>
                <a href="dashboard.php" class="inline-block mt-5 px-8 py-3 bg-[#22c55e] text-white rounded-2xl font-bold">Ir al dashboard</a>
            </div>`;
        el.className = 'mt-6 px-6 py-8 rounded-2xl bg-green-50 border border-green-200';
        el.classList.remove('hidden');
        document.querySelector('.flex.justify-between').classList.add('hidden');
    } else {
        alerta(data.error || 'Error al enviar la postulación');
    }
}

// Init
actualizarUI();
</script>
</body>
</html>

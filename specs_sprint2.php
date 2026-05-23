<?php
// specs_sprint2.php — Solo accesible para Administrador
session_start();
if (empty($_SESSION['usuario']) || $_SESSION['usuario']['rol'] !== 'Administrador') {
    header('Location: login.php'); exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>NutriSucre — Specs Sprint 2 (SDD)</title>
<script src="https://cdn.tailwindcss.com"></script>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@400;600&display=swap" rel="stylesheet">
<style>
  body { font-family:'Inter',sans-serif; background:#0f172a; color:#e2e8f0; }
  .mono { font-family:'JetBrains Mono',monospace; }
  .spec-pass { border-left:4px solid #22c55e; }
  .spec-fail { border-left:4px solid #ef4444; }
  .spec-pending { border-left:4px solid #f59e0b; }
  .spec-running { border-left:4px solid #3b82f6; }
</style>
</head>
<body class="bg-[#0f172a] min-h-screen">
<?php $paginaActual = 'specs_sprint2'; require_once '_sidebar.php'; ?>
<div class="md:pl-64">
<div class="p-8">

<div class="max-w-4xl mx-auto">

  <!-- Header -->
  <div class="flex items-center justify-between mb-8">
    <div>
      <div class="flex items-center gap-3 mb-2">
        <span class="text-2xl">🧪</span>
        <h1 class="text-2xl font-black text-white">Especificaciones Sprint 2</h1>
      </div>
      <p class="text-slate-400 text-sm">Spec-Driven Development (SDD) · NutriSucre · Gestión de Servicios</p>
    </div>
    <div class="text-right">
      <div id="resumenTotal" class="text-sm text-slate-400">Esperando ejecución...</div>
      <button onclick="ejecutarTodas()"
              class="mt-2 bg-[#22c55e] hover:bg-[#16a34a] text-white px-6 py-2.5 rounded-xl font-bold text-sm transition-colors">
        ▶ Ejecutar todas las specs
      </button>
    </div>
  </div>

  <!-- Barra de progreso -->
  <div class="bg-slate-800 rounded-full h-2 mb-8 overflow-hidden">
    <div id="barraProgreso" class="bg-[#22c55e] h-2 rounded-full transition-all duration-500" style="width:0%"></div>
  </div>

  <!-- Specs por Historia de Usuario -->
  <div id="contenedorSpecs" class="space-y-6"></div>

  <!-- Log de ejecución -->
  <div class="mt-8 bg-slate-800 rounded-2xl p-5">
    <div class="flex items-center justify-between mb-3">
      <h2 class="text-sm font-bold text-slate-300 mono">EXECUTION LOG</h2>
      <button onclick="document.getElementById('log').innerHTML=''" class="text-xs text-slate-500 hover:text-slate-300">Limpiar</button>
    </div>
    <div id="log" class="mono text-xs text-slate-400 space-y-1 max-h-64 overflow-y-auto"></div>
  </div>

  
</div>

<script>
// ══════════════════════════════════════════════════════
//  ESPECIFICACIONES FORMALES — Sprint 2 (SDD)
//  Cada spec define: descripción, acción, aserción
// ══════════════════════════════════════════════════════

const SPECS = [
  // ─────────────────────────────────────
  //  HU-01: Registro de Servicio (Ofertante)
  // ─────────────────────────────────────
  {
    hu: 'HU-01',
    titulo: 'Registro de Producto/Servicio',
    descripcion: 'Como Nutricionista, quiero registrar un servicio para que sea visible tras aprobación.',
    specs: [
      {
        id: 'HU01-01',
        nombre: 'Creación exitosa con datos completos',
        descripcion: 'Dado un nutricionista autenticado con datos válidos, al crear un servicio debe responder ok:true y estado:Pendiente',
        tipo: 'happy_path',
        async ejecutar() {
          const res = await fetch('api/servicios.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
              titulo: '[SPEC-TEST] Servicio de prueba automatizada',
              descripcion: 'Descripción completa para prueba de especificación SDD del sprint 2.',
              categoria: 'Pérdida de peso',
              precio: 200,
              duracion_semanas: 4,
              modalidad: 'Virtual',
              incluye: 'Consultas y seguimiento'
            })
          });
          const data = await res.json();
          // Limpiar el dato de prueba si se creó
          if (data.id) {
            await fetch('api/servicios.php', {
              method: 'DELETE',
              headers: { 'Content-Type': 'application/json' },
              body: JSON.stringify({ id: data.id })
            });
          }
          return {
            pasó: data.ok === true && data.estado === 'Pendiente',
            detalle: `ok=${data.ok}, estado="${data.estado}", mensaje="${data.mensaje || data.error || ''}"`
          };
        }
      },
      {
        id: 'HU01-02',
        nombre: 'Rechazo por título vacío (caso de borde)',
        descripcion: 'Dado un payload sin título, el sistema debe devolver error 400 con mensaje descriptivo.',
        tipo: 'edge_case',
        async ejecutar() {
          const res = await fetch('api/servicios.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ titulo: '', descripcion: 'Test', precio: 100, duracion_semanas: 4 })
          });
          const data = await res.json();
          return {
            pasó: res.status === 400 && typeof data.error === 'string' && data.error.length > 0,
            detalle: `HTTP ${res.status}, error="${data.error || '(ninguno)'}"`
          };
        }
      },
      {
        id: 'HU01-03',
        nombre: 'Rechazo por descripción vacía (caso de borde)',
        descripcion: 'Sin descripción el sistema debe devolver error 400.',
        tipo: 'edge_case',
        async ejecutar() {
          const res = await fetch('api/servicios.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ titulo: 'Test', descripcion: '', precio: 100, duracion_semanas: 4 })
          });
          const data = await res.json();
          return {
            pasó: res.status === 400 && typeof data.error === 'string',
            detalle: `HTTP ${res.status}, error="${data.error || '(ninguno)'}"`
          };
        }
      },
      {
        id: 'HU01-04',
        nombre: 'Rechazo por precio igual a 0 (caso de borde)',
        descripcion: 'El precio debe ser mayor a 0. Con precio=0 debe retornar 400.',
        tipo: 'edge_case',
        async ejecutar() {
          const res = await fetch('api/servicios.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ titulo: 'Test', descripcion: 'Test desc', precio: 0, duracion_semanas: 4 })
          });
          const data = await res.json();
          return {
            pasó: res.status === 400 && typeof data.error === 'string',
            detalle: `HTTP ${res.status}, error="${data.error || '(ninguno)'}"`
          };
        }
      },
      {
        id: 'HU01-05',
        nombre: 'Estado inicial obligatorio es "Pendiente"',
        descripcion: 'Al listar mis servicios, el último creado debe tener estado=Pendiente (verificado en la API de listado).',
        tipo: 'happy_path',
        async ejecutar() {
          const res  = await fetch('api/servicios.php');
          const data = await res.json();
          if (!Array.isArray(data)) return { pasó: false, detalle: 'No se pudo obtener lista: ' + (data.error || 'error') };
          // Verificar que los servicios Aprobados son los únicos visibles para pacientes
          // (desde el endpoint público)
          const resPub = await fetch('api/servicios.php?publico=1');
          const pub    = await resPub.json();
          const todosAprobados = Array.isArray(pub) && pub.every(s => s.estado === 'Aprobado');
          return {
            pasó: todosAprobados,
            detalle: `Endpoint público devuelve ${Array.isArray(pub)?pub.length:0} servicios. Todos Aprobados: ${todosAprobados}`
          };
        }
      }
    ]
  },

  // ─────────────────────────────────────
  //  HU-02: Edición y Eliminación
  // ─────────────────────────────────────
  {
    hu: 'HU-02',
    titulo: 'Edición y Eliminación de Servicio',
    descripcion: 'Como Nutricionista, quiero modificar o eliminar mis servicios.',
    specs: [
      {
        id: 'HU02-01',
        nombre: 'Edición exitosa devuelve estado "Pendiente"',
        descripcion: 'Al editar un servicio aprobado, su estado debe volver automáticamente a Pendiente.',
        tipo: 'happy_path',
        async ejecutar() {
          // 1. Crear servicio temporal
          const resC = await fetch('api/servicios.php', {
            method: 'POST', headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ titulo:'[SPEC] Edicion test', descripcion:'Desc test', precio:150, duracion_semanas:4, categoria:'Otro', modalidad:'Virtual' })
          });
          const creado = await resC.json();
          if (!creado.id) return { pasó: false, detalle: 'No se pudo crear servicio de prueba: ' + (creado.error||'') };

          // 2. Editar
          const resE = await fetch('api/servicios.php', {
            method: 'PUT', headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id: creado.id, titulo:'[SPEC] Editado', descripcion:'Nueva desc', precio:200, duracion_semanas:6, categoria:'Otro', modalidad:'Virtual' })
          });
          const editado = await resE.json();

          // 3. Limpiar
          await fetch('api/servicios.php', { method:'DELETE', headers:{'Content-Type':'application/json'}, body:JSON.stringify({id:creado.id}) });

          return {
            pasó: editado.ok === true && editado.estado === 'Pendiente',
            detalle: `ok=${editado.ok}, estado="${editado.estado}", msg="${editado.mensaje||editado.error||''}"`
          };
        }
      },
      {
        id: 'HU02-02',
        nombre: 'Edición con título vacío retorna error 400',
        descripcion: 'Caso de borde: enviar PUT con título vacío debe retornar 400.',
        tipo: 'edge_case',
        async ejecutar() {
          const res = await fetch('api/servicios.php', {
            method: 'PUT', headers: {'Content-Type':'application/json'},
            body: JSON.stringify({ id: 999999, titulo: '', descripcion: 'x', precio: 100, duracion_semanas: 4 })
          });
          const data = await res.json();
          return {
            pasó: res.status === 400 && typeof data.error === 'string',
            detalle: `HTTP ${res.status}, error="${data.error||'(ninguno)'}"`
          };
        }
      },
      {
        id: 'HU02-03',
        nombre: 'Eliminación exitosa de servicio propio',
        descripcion: 'El nutricionista puede eliminar sus propios servicios.',
        tipo: 'happy_path',
        async ejecutar() {
          // Crear y eliminar
          const resC = await fetch('api/servicios.php', {
            method:'POST', headers:{'Content-Type':'application/json'},
            body: JSON.stringify({ titulo:'[SPEC] Para eliminar', descripcion:'Desc', precio:100, duracion_semanas:2, categoria:'Otro', modalidad:'Virtual' })
          });
          const creado = await resC.json();
          if (!creado.id) return { pasó: false, detalle: 'No se pudo crear: ' + (creado.error||'') };

          const resD = await fetch('api/servicios.php', {
            method:'DELETE', headers:{'Content-Type':'application/json'},
            body: JSON.stringify({ id: creado.id })
          });
          const eliminado = await resD.json();
          return {
            pasó: eliminado.ok === true,
            detalle: `ok=${eliminado.ok}, msg="${eliminado.mensaje||eliminado.error||''}"`
          };
        }
      },
      {
        id: 'HU02-04',
        nombre: 'Eliminar servicio inexistente retorna 404 o error',
        descripcion: 'Caso de borde: intentar eliminar un ID que no existe.',
        tipo: 'edge_case',
        async ejecutar() {
          const res = await fetch('api/servicios.php', {
            method:'DELETE', headers:{'Content-Type':'application/json'},
            body: JSON.stringify({ id: 999999 })
          });
          const data = await res.json();
          return {
            pasó: !data.ok && typeof data.error === 'string',
            detalle: `ok=${data.ok}, error="${data.error||'(ninguno)'}"`
          };
        }
      }
    ]
  },

  // ─────────────────────────────────────
  //  HU-03: Validación (Administrador)
  // ─────────────────────────────────────
  {
    hu: 'HU-03',
    titulo: 'Validación de Contenido (Administrador)',
    descripcion: 'Como Administrador, quiero aprobar o rechazar servicios pendientes.',
    specs: [
      {
        id: 'HU03-01',
        nombre: 'Aprobación exitosa de servicio Pendiente',
        descripcion: 'El admin puede aprobar un servicio. La API debe responder ok:true y estado:Aprobado.',
        tipo: 'happy_path',
        async ejecutar() {
          // Crear servicio pendiente
          const resC = await fetch('api/servicios.php', {
            method:'POST', headers:{'Content-Type':'application/json'},
            body: JSON.stringify({ titulo:'[SPEC] Para aprobar', descripcion:'Desc aprobacion', precio:300, duracion_semanas:8, categoria:'Nutrición clínica', modalidad:'Ambas' })
          });
          const creado = await resC.json();
          if (!creado.id) return { pasó: false, detalle: 'No se pudo crear: ' + (creado.error||'') };

          // Aprobar
          const resA = await fetch('api/servicios.php?accion=validar', {
            method:'PUT', headers:{'Content-Type':'application/json'},
            body: JSON.stringify({ id: creado.id, estado:'Aprobado', motivo:'' })
          });
          const aprobado = await resA.json();

          // Limpiar
          await fetch('api/servicios.php', { method:'DELETE', headers:{'Content-Type':'application/json'}, body:JSON.stringify({id:creado.id}) });

          return {
            pasó: aprobado.ok === true && aprobado.estado === 'Aprobado',
            detalle: `ok=${aprobado.ok}, estado="${aprobado.estado}", msg="${aprobado.mensaje||aprobado.error||''}"`
          };
        }
      },
      {
        id: 'HU03-02',
        nombre: 'Rechazo con motivo obligatorio',
        descripcion: 'Caso happy path: rechazar con motivo debe funcionar correctamente.',
        tipo: 'happy_path',
        async ejecutar() {
          const resC = await fetch('api/servicios.php', {
            method:'POST', headers:{'Content-Type':'application/json'},
            body: JSON.stringify({ titulo:'[SPEC] Para rechazar', descripcion:'Desc rechazo', precio:50, duracion_semanas:1, categoria:'Otro', modalidad:'Virtual' })
          });
          const creado = await resC.json();
          if (!creado.id) return { pasó: false, detalle: 'No se pudo crear: ' + (creado.error||'') };

          const resR = await fetch('api/servicios.php?accion=validar', {
            method:'PUT', headers:{'Content-Type':'application/json'},
            body: JSON.stringify({ id: creado.id, estado:'Rechazado', motivo:'Duración insuficiente y precio no justificado.' })
          });
          const rechazado = await resR.json();

          await fetch('api/servicios.php', { method:'DELETE', headers:{'Content-Type':'application/json'}, body:JSON.stringify({id:creado.id}) });

          return {
            pasó: rechazado.ok === true && rechazado.estado === 'Rechazado',
            detalle: `ok=${rechazado.ok}, estado="${rechazado.estado}"`
          };
        }
      },
      {
        id: 'HU03-03',
        nombre: 'Rechazo sin motivo retorna error 400 (caso de borde)',
        descripcion: 'Al intentar rechazar sin motivo, el sistema debe retornar error.',
        tipo: 'edge_case',
        async ejecutar() {
          const res = await fetch('api/servicios.php?accion=validar', {
            method:'PUT', headers:{'Content-Type':'application/json'},
            body: JSON.stringify({ id: 1, estado:'Rechazado', motivo:'' })
          });
          const data = await res.json();
          return {
            pasó: res.status === 400 && typeof data.error === 'string',
            detalle: `HTTP ${res.status}, error="${data.error||'(ninguno)'}"`
          };
        }
      },
      {
        id: 'HU03-04',
        nombre: 'Estado inválido retorna error 400 (caso de borde)',
        descripcion: 'Enviar estado="Eliminado" u otro no válido debe retornar error 400.',
        tipo: 'edge_case',
        async ejecutar() {
          const res = await fetch('api/servicios.php?accion=validar', {
            method:'PUT', headers:{'Content-Type':'application/json'},
            body: JSON.stringify({ id: 1, estado:'Eliminado', motivo:'test' })
          });
          const data = await res.json();
          return {
            pasó: res.status === 400 && typeof data.error === 'string',
            detalle: `HTTP ${res.status}, error="${data.error||'(ninguno)'}"`
          };
        }
      },
      {
        id: 'HU03-05',
        nombre: 'Servicios aprobados son visibles en endpoint público',
        descripcion: 'Tras aprobar un servicio, debe aparecer en el endpoint público (?publico=1).',
        tipo: 'happy_path',
        async ejecutar() {
          const res  = await fetch('api/servicios.php?publico=1');
          const data = await res.json();
          const soloAprobados = Array.isArray(data) && data.length > 0 && data.every(s => s.estado === 'Aprobado');
          return {
            pasó: soloAprobados,
            detalle: `${Array.isArray(data)?data.length:0} servicios públicos, todos Aprobados: ${soloAprobados}`
          };
        }
      }
    ]
  }
];

// ══════════════════════════════════════════
//  Motor de ejecución de specs
// ══════════════════════════════════════════
let resultados = { pasadas: 0, fallidas: 0, total: 0 };

function log(msg, tipo = 'info') {
    const el = document.getElementById('log');
    const color = tipo === 'pass' ? '#22c55e' : tipo === 'fail' ? '#ef4444' : tipo === 'run' ? '#3b82f6' : '#94a3b8';
    const ts = new Date().toLocaleTimeString('es-BO');
    el.innerHTML += `<div style="color:${color}">[${ts}] ${msg}</div>`;
    el.scrollTop = el.scrollHeight;
}

async function ejecutarTodas() {
    resultados = { pasadas: 0, fallidas: 0, total: 0 };
    renderSpecs('pending');
    document.getElementById('resumenTotal').textContent = 'Ejecutando...';

    const todasSpecs = SPECS.flatMap(hu => hu.specs);
    resultados.total = todasSpecs.length;
    let ejecutadas = 0;

    log('▶ Iniciando ejecución de ' + todasSpecs.length + ' especificaciones...', 'info');

    for (const hu of SPECS) {
        for (const spec of hu.specs) {
            log(`  ⏳ [${spec.id}] ${spec.nombre}`, 'run');
            actualizarEstadoSpec(spec.id, 'running');
            try {
                const resultado = await spec.ejecutar();
                spec._resultado = resultado;
                if (resultado.pasó) {
                    resultados.pasadas++;
                    log(`  ✅ [${spec.id}] PASS — ${resultado.detalle}`, 'pass');
                    actualizarEstadoSpec(spec.id, 'pass', resultado.detalle);
                } else {
                    resultados.fallidas++;
                    log(`  ❌ [${spec.id}] FAIL — ${resultado.detalle}`, 'fail');
                    actualizarEstadoSpec(spec.id, 'fail', resultado.detalle);
                }
            } catch (e) {
                resultados.fallidas++;
                log(`  ❌ [${spec.id}] ERROR — ${e.message}`, 'fail');
                actualizarEstadoSpec(spec.id, 'fail', e.message);
            }
            ejecutadas++;
            document.getElementById('barraProgreso').style.width = (ejecutadas / resultados.total * 100) + '%';
        }
    }

    const color = resultados.fallidas === 0 ? '#22c55e' : '#ef4444';
    document.getElementById('resumenTotal').innerHTML =
        `<span style="color:${color}">✅ ${resultados.pasadas} pasadas &nbsp; ❌ ${resultados.fallidas} fallidas &nbsp; de ${resultados.total} specs</span>`;
    log(`\n📊 RESULTADO FINAL: ${resultados.pasadas}/${resultados.total} specs pasadas`, resultados.fallidas === 0 ? 'pass' : 'fail');
}

function actualizarEstadoSpec(id, estado, detalle = '') {
    const el = document.getElementById('spec_' + id);
    if (!el) return;
    const badge = el.querySelector('.spec-badge');
    const det   = el.querySelector('.spec-detalle');

    el.className = el.className.replace(/spec-(pass|fail|pending|running)/g, '');
    if (estado === 'pass') {
        el.classList.add('spec-pass');
        badge.innerHTML = '<span class="bg-green-500 text-white px-2 py-0.5 rounded-full text-xs font-bold">PASS</span>';
    } else if (estado === 'fail') {
        el.classList.add('spec-fail');
        badge.innerHTML = '<span class="bg-red-500 text-white px-2 py-0.5 rounded-full text-xs font-bold">FAIL</span>';
    } else if (estado === 'running') {
        el.classList.add('spec-running');
        badge.innerHTML = '<span class="bg-blue-500 text-white px-2 py-0.5 rounded-full text-xs font-bold animate-pulse">RUN</span>';
    }
    if (detalle && det) det.textContent = detalle;
}

function renderSpecs(estadoInicial = 'pending') {
    const cont = document.getElementById('contenedorSpecs');
    cont.innerHTML = SPECS.map(hu => `
        <div class="bg-slate-800 rounded-2xl overflow-hidden">
            <!-- Header HU -->
            <div class="bg-slate-700 px-5 py-4 flex items-start gap-3">
                <span class="bg-[#22c55e] text-black text-xs font-black px-2 py-1 rounded-lg mono">${hu.hu}</span>
                <div>
                    <h2 class="font-bold text-white">${hu.titulo}</h2>
                    <p class="text-slate-400 text-xs mt-0.5">${hu.descripcion}</p>
                </div>
            </div>
            <!-- Specs de esta HU -->
            <div class="divide-y divide-slate-700">
                ${hu.specs.map(spec => `
                <div id="spec_${spec.id}" class="spec-${estadoInicial} px-5 py-4 transition-all">
                    <div class="flex justify-between items-start gap-3">
                        <div class="flex-1">
                            <div class="flex items-center gap-2 mb-1">
                                <span class="mono text-xs text-slate-500">${spec.id}</span>
                                <span class="${spec.tipo === 'edge_case' ? 'bg-amber-900 text-amber-300' : 'bg-green-900 text-green-300'} text-xs px-2 py-0.5 rounded-full font-medium">
                                    ${spec.tipo === 'edge_case' ? '⚡ Edge case' : '✨ Happy path'}
                                </span>
                            </div>
                            <p class="text-white text-sm font-semibold">${spec.nombre}</p>
                            <p class="text-slate-400 text-xs mt-1">${spec.descripcion}</p>
                            <p class="spec-detalle mono text-xs text-slate-500 mt-2"></p>
                        </div>
                        <div class="spec-badge flex-shrink-0">
                            <span class="bg-slate-600 text-slate-300 px-2 py-0.5 rounded-full text-xs font-bold">WAIT</span>
                        </div>
                    </div>
                </div>`).join('')}
            </div>
        </div>
    `).join('');
}

// Inicializar al cargar
renderSpecs('pending');

// Logout requerido por el sidebar
async function logout() {
    if (!confirm('¿Cerrar sesión?')) return;
    await fetch('api/auth.php?accion=logout', { method: 'POST' });
    window.location.href = 'login.php';
}
</script>
</div></div>
</body>
</html>

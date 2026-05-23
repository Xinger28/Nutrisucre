<?php
session_start();
if (!empty($_SESSION['usuario'])) { header('Location: dashboard.php'); exit; }
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>NutriSucre - Ingreso</title>
<script src="https://cdn.tailwindcss.com"></script>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet">
<style>
  body { font-family: 'Inter', sans-serif; }
  .material-symbols-outlined { font-variation-settings: 'FILL' 0, 'wght' 300; }
  /* Tabs de login/registro */
  .tab-btn { transition: all .2s; }
  .tab-btn.activo { background: white; color: #22c55e; box-shadow: 0 1px 4px rgba(0,0,0,.1); }
</style>
</head>
<body class="bg-gradient-to-br from-[#f0fdf4] via-white to-[#f0fdf4] min-h-screen flex items-center justify-center p-4">

<div class="fixed inset-0 overflow-hidden pointer-events-none">
  <div class="absolute -top-40 -right-40 w-96 h-96 bg-[#22c55e]/10 rounded-full blur-3xl"></div>
  <div class="absolute -bottom-40 -left-40 w-96 h-96 bg-[#22c55e]/10 rounded-full blur-3xl"></div>
</div>

<main class="relative z-10 w-full max-w-md">

  <!-- Logo -->
  <div class="text-center mb-8">
    <div class="inline-flex items-center gap-3 mb-5">
      <div class="w-16 h-16 bg-white rounded-2xl shadow-xl flex items-center justify-center">
        <span class="material-symbols-outlined text-[#22c55e] text-4xl">nutrition</span>
      </div>
      <span class="text-4xl font-black tracking-tight">NutriSucre</span>
    </div>
    <!-- Mensaje principal (SIN selector de rol en el login) -->
    <h1 class="text-2xl font-bold text-gray-800">Bienvenido a tu bienestar nutricional</h1>
    <p class="text-gray-500 mt-2 text-sm leading-relaxed">
      ¿Quieres mejorar tu salud con ayuda de especialistas profesionales?<br>
      Inicia sesión y comienza hoy tu cambio.
    </p>
  </div>

  <div class="bg-white rounded-3xl shadow-2xl overflow-hidden">

    <!-- Tabs: Iniciar sesión / Crear cuenta -->
    <div class="flex p-2 bg-gray-50 gap-1">
      <button onclick="mostrarTab('login')" id="tabLogin"
              class="tab-btn activo flex-1 py-4 rounded-2xl font-semibold text-sm">
        Iniciar sesión
      </button>
      <button onclick="mostrarTab('register')" id="tabRegister"
              class="tab-btn flex-1 py-4 rounded-2xl font-semibold text-sm text-gray-500">
        Crear cuenta
      </button>
    </div>

    <!-- Mensaje global de feedback -->
    <div id="msgGlobal" class="hidden mx-6 mt-4 px-4 py-3 rounded-2xl text-sm font-medium"></div>

    <!-- ════════════ FORM LOGIN ════════════ -->
    <div id="formLogin" class="p-8 space-y-5">
      <div>
        <label class="block text-sm font-semibold mb-2 text-gray-700">Correo electrónico</label>
        <div class="relative">
          <span class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 text-xl">mail</span>
          <input id="login_email" type="email" placeholder="tu@correo.com"
                 class="w-full pl-11 pr-4 py-4 border rounded-2xl focus:border-[#22c55e] outline-none text-sm">
        </div>
      </div>
      <div>
        <label class="block text-sm font-semibold mb-2 text-gray-700">Contraseña</label>
        <div class="relative">
          <span class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 text-xl">lock</span>
          <input id="login_pass" type="password" placeholder="••••••••"
                 class="w-full pl-11 pr-12 py-4 border rounded-2xl focus:border-[#22c55e] outline-none text-sm">
          <button type="button" onclick="togglePass('login_pass',this)"
                  class="absolute right-4 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600">
            <span class="material-symbols-outlined text-xl">visibility</span>
          </button>
        </div>
      </div>
      <button onclick="login()"
              class="w-full py-4 bg-[#22c55e] text-white font-bold text-base rounded-2xl hover:bg-[#16a34a] active:scale-95 transition-all shadow-lg shadow-green-200">
        Iniciar sesión
      </button>
      <p class="text-center text-xs text-gray-400">Demo: luis@nutrisucre.bo / 123456</p>
    </div>

    <!-- ════════════ FORM REGISTRO ════════════ -->
    <div id="formRegister" class="p-8 space-y-4 hidden">
      <div>
        <label class="block text-sm font-semibold mb-2 text-gray-700">Nombre completo</label>
        <div class="relative">
          <span class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 text-xl">person</span>
          <input id="reg_nombre" type="text" placeholder="Ej: Ana Beltrán"
                 class="w-full pl-11 pr-4 py-4 border rounded-2xl focus:border-[#22c55e] outline-none text-sm">
        </div>
      </div>
      <div>
        <label class="block text-sm font-semibold mb-2 text-gray-700">Correo electrónico</label>
        <div class="relative">
          <span class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 text-xl">mail</span>
          <input id="reg_email" type="email" placeholder="tu@correo.com"
                 class="w-full pl-11 pr-4 py-4 border rounded-2xl focus:border-[#22c55e] outline-none text-sm">
        </div>
      </div>
      <div>
        <label class="block text-sm font-semibold mb-2 text-gray-700">Contraseña</label>
        <div class="relative">
          <span class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 text-xl">lock</span>
          <input id="reg_pass" type="password" placeholder="Mínimo 6 caracteres"
                 class="w-full pl-11 pr-12 py-4 border rounded-2xl focus:border-[#22c55e] outline-none text-sm">
          <button type="button" onclick="togglePass('reg_pass',this)"
                  class="absolute right-4 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600">
            <span class="material-symbols-outlined text-xl">visibility</span>
          </button>
        </div>
      </div>

      <!-- Tipo de cuenta: el selector está en el REGISTRO, no en el login -->
      <div>
        <label class="block text-sm font-semibold mb-2 text-gray-700">Tipo de cuenta</label>
        <div class="grid grid-cols-2 gap-3">
          <label id="cardPaciente" onclick="selectTipo('Paciente')"
                 class="tipo-card flex flex-col items-center gap-2 p-4 border-2 border-[#22c55e] bg-[#f0fdf4] rounded-2xl cursor-pointer transition-all">
            <span class="material-symbols-outlined text-3xl text-[#22c55e]">person</span>
            <span class="font-semibold text-sm text-[#22c55e]">Paciente</span>
            <span class="text-xs text-gray-400 text-center">Busca nutricionistas y agenda citas</span>
          </label>
          <label id="cardNutri" onclick="selectTipo('Nutricionista')"
                 class="tipo-card flex flex-col items-center gap-2 p-4 border-2 border-gray-200 rounded-2xl cursor-pointer transition-all hover:border-[#22c55e]">
            <span class="material-symbols-outlined text-3xl text-gray-400">medical_services</span>
            <span class="font-semibold text-sm text-gray-600">Nutricionista</span>
            <span class="text-xs text-gray-400 text-center">Ofrece servicios profesionales</span>
          </label>
        </div>
        <input type="hidden" id="reg_rol" value="Paciente">
      </div>

      <button onclick="registrar()"
              class="w-full py-4 bg-[#22c55e] text-white font-bold text-base rounded-2xl hover:bg-[#16a34a] active:scale-95 transition-all shadow-lg shadow-green-200">
        Crear cuenta
      </button>
      <p class="text-center text-xs text-gray-400">Si eres nutricionista, después de registrarte deberás completar tu formulario de verificación profesional.</p>
    </div>
  </div>

  <p class="text-center text-xs text-gray-400 mt-6">
    Chuquisaca, Bolivia · Plataforma 100% virtual
  </p>
</main>

<script>
let tipoSeleccionado = 'Paciente';

function mostrarTab(tab) {
    const isLogin = tab === 'login';
    document.getElementById('formLogin').classList.toggle('hidden', !isLogin);
    document.getElementById('formRegister').classList.toggle('hidden', isLogin);
    document.getElementById('tabLogin').classList.toggle('activo', isLogin);
    document.getElementById('tabRegister').classList.toggle('activo', !isLogin);
    document.getElementById('tabLogin').classList.toggle('text-gray-500', !isLogin);
    document.getElementById('tabRegister').classList.toggle('text-gray-500', isLogin);
    ocultarMsg();
}

function selectTipo(tipo) {
    tipoSeleccionado = tipo;
    document.getElementById('reg_rol').value = tipo;
    const cards = document.querySelectorAll('.tipo-card');
    cards.forEach(c => {
        c.classList.remove('border-[#22c55e]','bg-[#f0fdf4]');
        c.classList.add('border-gray-200');
        c.querySelectorAll('.material-symbols-outlined').forEach(i => { i.classList.remove('text-[#22c55e]'); i.classList.add('text-gray-400'); });
        c.querySelectorAll('.font-semibold').forEach(s => { s.classList.remove('text-[#22c55e]'); s.classList.add('text-gray-600'); });
    });
    const activo = tipo === 'Paciente' ? document.getElementById('cardPaciente') : document.getElementById('cardNutri');
    activo.classList.add('border-[#22c55e]','bg-[#f0fdf4]');
    activo.classList.remove('border-gray-200');
    activo.querySelectorAll('.material-symbols-outlined').forEach(i => { i.classList.add('text-[#22c55e]'); i.classList.remove('text-gray-400'); });
    activo.querySelectorAll('.font-semibold').forEach(s => { s.classList.add('text-[#22c55e]'); s.classList.remove('text-gray-600'); });
}

function togglePass(inputId, btn) {
    const input = document.getElementById(inputId);
    const esPassword = input.type === 'password';
    input.type = esPassword ? 'text' : 'password';
    btn.querySelector('.material-symbols-outlined').textContent = esPassword ? 'visibility_off' : 'visibility';
}

async function login() {
    const email = document.getElementById('login_email').value.trim();
    const pass  = document.getElementById('login_pass').value;
    if (!email || !pass) return mostrarMsg('Completa todos los campos', 'error');

    const res  = await fetch('api/auth.php?accion=login', {
        method: 'POST', headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ identificador: email, password: pass })
    });
    const data = await res.json();
    if (data.ok) {
        window.location.href = 'dashboard.php';
    } else {
        mostrarMsg(data.error || 'Credenciales incorrectas', 'error');
    }
}

async function registrar() {
    const nombre = document.getElementById('reg_nombre').value.trim();
    const email  = document.getElementById('reg_email').value.trim();
    const pass   = document.getElementById('reg_pass').value;
    const rol    = document.getElementById('reg_rol').value;

    if (!nombre || !email || !pass) return mostrarMsg('Completa todos los campos', 'error');
    if (pass.length < 6) return mostrarMsg('La contraseña debe tener al menos 6 caracteres', 'error');

    const res  = await fetch('api/auth.php?accion=register', {
        method: 'POST', headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ nombre, email, password: pass, rol })
    });
    const data = await res.json();

    if (data.ok) {
        mostrarMsg('✅ Cuenta creada. Ahora inicia sesión.', 'ok');
        document.getElementById('login_email').value = email;
        setTimeout(() => mostrarTab('login'), 1800);
    } else {
        mostrarMsg(data.error || 'Error al registrar', 'error');
    }
}

function mostrarMsg(txt, tipo = 'error') {
    const el = document.getElementById('msgGlobal');
    el.textContent = txt;
    el.className = `mx-6 mt-4 px-4 py-3 rounded-2xl text-sm font-medium ${
        tipo === 'ok' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'
    }`;
    el.classList.remove('hidden');
}
function ocultarMsg() { document.getElementById('msgGlobal').classList.add('hidden'); }

document.addEventListener('keydown', e => {
    if (e.key !== 'Enter') return;
    const loginVisible = !document.getElementById('formLogin').classList.contains('hidden');
    loginVisible ? login() : registrar();
});
</script>
</body>
</html>

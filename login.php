<?php
session_start();
if (!empty($_SESSION['usuario'])) { header('Location: dashboard.php'); exit; }
?>
<!DOCTYPE html>
<html lang="es">
<head>
<title>NutriSucre</title>
<?php require_once '_ios_head.php'; ?>
<style>
  body { background: linear-gradient(145deg, #f0fdf4 0%, #ffffff 50%, #f0f9ff 100%); }
  .glass-card {
    background: rgba(255,255,255,0.75);
    backdrop-filter: saturate(180%) blur(30px);
    -webkit-backdrop-filter: saturate(180%) blur(30px);
    border: 1px solid rgba(255,255,255,0.8);
    border-radius: 28px;
    box-shadow: 0 25px 80px rgba(0,0,0,0.10), 0 0 0 1px rgba(255,255,255,0.5) inset;
  }
  .floating-orb {
    position: fixed; border-radius: 50%; filter: blur(80px); pointer-events: none; z-index: 0;
  }
  .tipo-card { border: 2px solid var(--border); border-radius: 18px; padding: 16px; cursor: pointer; transition: all .2s cubic-bezier(.34,1.56,.64,1); text-align: center; }
  .tipo-card.sel { border-color: var(--green); background: var(--green-soft); }
  .tipo-card.sel .tipo-icon { color: var(--green-dark); }
  .tipo-card.sel .tipo-label { color: var(--green-dark); }
  .tipo-icon { font-size: 30px; color: var(--text3); transition: color .2s; display: block; margin-bottom: 4px; }
  .tipo-label { font-weight: 700; font-size: 14px; color: var(--text2); transition: color .2s; }
  .tipo-sub { font-size: 11px; color: var(--text3); margin-top: 2px; }
</style>
</head>
<body class="min-h-screen flex items-center justify-center p-4">

<div class="floating-orb w-[600px] h-[600px] bg-green-200/40 -top-48 -right-48"></div>
<div class="floating-orb w-[400px] h-[400px] bg-emerald-100/60 -bottom-24 -left-24"></div>

<main class="relative z-10 w-full max-w-[420px]" style="animation: fadeUp .5s ease">

  <!-- Logo -->
  <div class="text-center mb-8">
    <div class="inline-flex items-center gap-3 mb-4">
      <div class="w-14 h-14 bg-gradient-to-br from-[#22c55e] to-[#16a34a] rounded-[18px] flex items-center justify-center shadow-xl shadow-green-200">
        <span class="icon icon-fill text-white" style="font-size:28px">nutrition</span>
      </div>
      <span class="text-[32px] font-black tracking-tight text-[#1c1c1e]">NutriSucre</span>
    </div>
    <p class="text-[15px] text-[#8e8e93] leading-relaxed">Tu plataforma de nutrición personalizada<br>en Sucre, Bolivia</p>
  </div>

  <div class="glass-card overflow-hidden">
    <!-- Segmented control tabs -->
    <div class="p-4 border-b border-[rgba(0,0,0,0.06)]">
      <div class="seg-control">
        <button class="seg-btn active" id="tabLogin" onclick="mostrarTab('login')">Iniciar sesión</button>
        <button class="seg-btn" id="tabRegister" onclick="mostrarTab('register')">Crear cuenta</button>
      </div>
    </div>

    <!-- Mensaje global -->
    <div id="msgGlobal" class="hidden mx-5 mt-4 px-4 py-3 rounded-2xl text-sm font-semibold"></div>

    <!-- ═══ LOGIN ═══ -->
    <div id="formLogin" class="p-6 space-y-4">
      <div class="space-y-1">
        <label class="text-[13px] font-semibold text-[#48484a] pl-1">Correo electrónico</label>
        <div class="relative">
          <span class="icon absolute left-3.5 top-1/2 -translate-y-1/2 text-[#8e8e93]" style="font-size:20px">mail</span>
          <input id="login_email" type="email" placeholder="tu@correo.com"
                 class="ios-input pl-11" autocomplete="email">
        </div>
      </div>
      <div class="space-y-1">
        <label class="text-[13px] font-semibold text-[#48484a] pl-1">Contraseña</label>
        <div class="relative">
          <span class="icon absolute left-3.5 top-1/2 -translate-y-1/2 text-[#8e8e93]" style="font-size:20px">lock</span>
          <input id="login_pass" type="password" placeholder="••••••••"
                 class="ios-input pl-11 pr-12" autocomplete="current-password">
          <button type="button" onclick="togglePass('login_pass',this)"
                  class="absolute right-3.5 top-1/2 -translate-y-1/2 text-[#8e8e93] hover:text-[#48484a] transition-colors">
            <span class="icon" style="font-size:20px">visibility</span>
          </button>
        </div>
      </div>
      <button onclick="login()" id="btnLogin"
              class="ios-btn w-full mt-2" style="border-radius:14px; padding:15px">
        Iniciar sesión
      </button>
      <p class="text-center text-[12px] text-[#8e8e93] pt-1">
        Demo: <span class="font-semibold text-[#48484a]">luis@nutrisucre.bo</span> / 123456
      </p>
    </div>

    <!-- ═══ REGISTRO ═══ -->
    <div id="formRegister" class="p-6 space-y-4 hidden">
      <div class="space-y-1">
        <label class="text-[13px] font-semibold text-[#48484a] pl-1">Nombre completo</label>
        <div class="relative">
          <span class="icon absolute left-3.5 top-1/2 -translate-y-1/2 text-[#8e8e93]" style="font-size:20px">person</span>
          <input id="reg_nombre" type="text" placeholder="Ej: Ana Beltrán" class="ios-input pl-11" autocomplete="name">
        </div>
      </div>
      <div class="space-y-1">
        <label class="text-[13px] font-semibold text-[#48484a] pl-1">Correo electrónico</label>
        <div class="relative">
          <span class="icon absolute left-3.5 top-1/2 -translate-y-1/2 text-[#8e8e93]" style="font-size:20px">mail</span>
          <input id="reg_email" type="email" placeholder="tu@correo.com" class="ios-input pl-11" autocomplete="email">
        </div>
      </div>
      <div class="space-y-1">
        <label class="text-[13px] font-semibold text-[#48484a] pl-1">Contraseña</label>
        <div class="relative">
          <span class="icon absolute left-3.5 top-1/2 -translate-y-1/2 text-[#8e8e93]" style="font-size:20px">lock</span>
          <input id="reg_pass" type="password" placeholder="Mínimo 6 caracteres" class="ios-input pl-11 pr-12" autocomplete="new-password">
          <button type="button" onclick="togglePass('reg_pass',this)"
                  class="absolute right-3.5 top-1/2 -translate-y-1/2 text-[#8e8e93] hover:text-[#48484a] transition-colors">
            <span class="icon" style="font-size:20px">visibility</span>
          </button>
        </div>
      </div>
      <!-- Campos específicos de Paciente -->
      <div id="patient_fields" class="space-y-4">
        <div class="space-y-1">
          <label class="text-[13px] font-semibold text-[#48484a] pl-1">Carnet de Identidad (CI) <span class="text-red-500">*</span></label>
          <div class="relative">
            <span class="icon absolute left-3.5 top-1/2 -translate-y-1/2 text-[#8e8e93]" style="font-size:20px">badge</span>
            <input id="reg_ci" type="text" placeholder="Ej: 1234567 CH" class="ios-input pl-11">
          </div>
        </div>
        <div class="space-y-1">
          <label class="text-[13px] font-semibold text-[#48484a] pl-1">Número de Celular <span class="text-red-500">*</span></label>
          <div class="relative">
            <span class="icon absolute left-3.5 top-1/2 -translate-y-1/2 text-[#8e8e93]" style="font-size:20px">call</span>
            <input id="reg_celular" type="text" placeholder="Ej: 71234567" class="ios-input pl-11">
          </div>
        </div>
      </div>
      <div class="space-y-2">
        <label class="text-[13px] font-semibold text-[#48484a] pl-1">Tipo de cuenta</label>
        <div class="grid grid-cols-2 gap-3">
          <div class="tipo-card sel" id="cardPaciente" onclick="selectTipo('Paciente')">
            <span class="icon tipo-icon">person</span>
            <p class="tipo-label">Paciente</p>
            <p class="tipo-sub">Busca especialistas</p>
          </div>
          <div class="tipo-card" id="cardNutri" onclick="selectTipo('Nutricionista')">
            <span class="icon tipo-icon">medical_services</span>
            <p class="tipo-label">Nutricionista</p>
            <p class="tipo-sub">Ofrece servicios</p>
          </div>
        </div>
        <input type="hidden" id="reg_rol" value="Paciente">
      </div>
      <button onclick="registrar()" id="btnReg"
              class="ios-btn w-full" style="border-radius:14px; padding:15px">
        Crear cuenta
      </button>
      <p class="text-center text-[11px] text-[#8e8e93]">Si eres nutricionista, completarás tu verificación profesional después del registro.</p>
    </div>
  </div>

  <p class="text-center text-[12px] text-[#8e8e93] mt-5">Plataforma 100% segura · Chuquisaca, Bolivia</p>
</main>

<script>
function mostrarTab(tab) {
    const isLogin = tab === 'login';
    document.getElementById('formLogin').classList.toggle('hidden', !isLogin);
    document.getElementById('formRegister').classList.toggle('hidden', isLogin);
    document.getElementById('tabLogin').classList.toggle('active', isLogin);
    document.getElementById('tabRegister').classList.toggle('active', !isLogin);
    ocultarMsg();
}
function selectTipo(tipo) {
    document.getElementById('reg_rol').value = tipo;
    document.getElementById('cardPaciente').classList.toggle('sel', tipo === 'Paciente');
    document.getElementById('cardNutri').classList.toggle('sel', tipo === 'Nutricionista');
    
    // Mostrar u ocultar campos de paciente
    const patientFields = document.getElementById('patient_fields');
    if (tipo === 'Paciente') {
        patientFields.classList.remove('hidden');
    } else {
        patientFields.classList.add('hidden');
    }
}
function togglePass(id, btn) {
    const input = document.getElementById(id);
    const show = input.type === 'password';
    input.type = show ? 'text' : 'password';
    btn.querySelector('.icon').textContent = show ? 'visibility_off' : 'visibility';
}
function setLoading(btnId, loading) {
    const btn = document.getElementById(btnId);
    btn.disabled = loading;
    btn.style.opacity = loading ? '0.7' : '1';
}
async function login() {
    const email = document.getElementById('login_email').value.trim();
    const pass  = document.getElementById('login_pass').value;
    if (!email || !pass) return mostrarMsg('Completa todos los campos', 'error');
    setLoading('btnLogin', true);
    try {
        const res  = await fetch('api/auth.php?accion=login', {
            method: 'POST', headers: {'Content-Type':'application/json'},
            body: JSON.stringify({ identificador: email, password: pass })
        });
        const data = await res.json();
        if (data.ok) { window.location.href = 'dashboard.php'; }
        else { mostrarMsg(data.error || 'Credenciales incorrectas', 'error'); setLoading('btnLogin', false); }
    } catch(e) { mostrarMsg('Error de conexión. Intenta de nuevo.', 'error'); setLoading('btnLogin', false); }
}
async function registrar() {
    const nombre = document.getElementById('reg_nombre').value.trim();
    const email  = document.getElementById('reg_email').value.trim();
    const pass   = document.getElementById('reg_pass').value;
    const rol    = document.getElementById('reg_rol').value;
    const ci     = document.getElementById('reg_ci').value.trim();
    const celular = document.getElementById('reg_celular').value.trim();

    if (!nombre || !email || !pass) return mostrarMsg('Completa todos los campos', 'error');
    if (rol === 'Paciente' && (!ci || !celular)) {
        return mostrarMsg('CI y celular son obligatorios para pacientes', 'error');
    }
    if (pass.length < 6) return mostrarMsg('La contraseña debe tener al menos 6 caracteres', 'error');
    setLoading('btnReg', true);
    try {
        const res  = await fetch('api/auth.php?accion=register', {
            method: 'POST', headers: {'Content-Type':'application/json'},
            body: JSON.stringify({ nombre, email, password: pass, rol, ci: rol === 'Paciente' ? ci : '', celular: rol === 'Paciente' ? celular : '' })
        });
        const data = await res.json();
        if (data.ok) {
            mostrarMsg('✅ Cuenta creada. Ahora inicia sesión.', 'ok');
            document.getElementById('login_email').value = email;
            setTimeout(() => mostrarTab('login'), 1800);
        } else { mostrarMsg(data.error || 'Error al registrar', 'error'); }
    } catch(e) { mostrarMsg('Error de conexión. Intenta de nuevo.', 'error'); }
    setLoading('btnReg', false);
}
function mostrarMsg(txt, tipo) {
    const el = document.getElementById('msgGlobal');
    el.textContent = txt;
    el.className = `mx-5 mt-4 px-4 py-3 rounded-2xl text-sm font-semibold ${tipo === 'ok' ? 'bg-green-100 text-green-800' : 'bg-red-50 text-red-700'}`;
    el.classList.remove('hidden');
    setTimeout(() => el.classList.add('hidden'), 5000);
}
function ocultarMsg() { document.getElementById('msgGlobal').classList.add('hidden'); }
document.addEventListener('keydown', e => {
    if (e.key !== 'Enter') return;
    !document.getElementById('formLogin').classList.contains('hidden') ? login() : registrar();
});
</script>
</body>
</html>

<?php
// ============================================================
//  _ios_head.php — Bloque <head> compartido + CSS global iOS
//  Uso: require_once '_ios_head.php'; (dentro de <head>)
// ============================================================
?>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
<script src="https://cdn.tailwindcss.com"></script>
<link href="https://fonts.googleapis.com/css2?family=SF+Pro+Display:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Rounded:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200&display=swap" rel="stylesheet">
<style>
  :root {
    --green:      #22c55e;
    --green-dark: #16a34a;
    --green-soft: #dcfce7;
    --green-xs:   rgba(34,197,94,0.08);
    --bg:         #f2f2f7;
    --surface:    #ffffff;
    --surface2:   #f9f9fb;
    --border:     rgba(0,0,0,0.08);
    --text:       #1c1c1e;
    --text2:      #48484a;
    --text3:      #8e8e93;
    --radius-xl:  22px;
    --radius-2xl: 28px;
    --shadow-sm:  0 2px 12px rgba(0,0,0,0.06);
    --shadow-md:  0 8px 30px rgba(0,0,0,0.10);
    --shadow-lg:  0 20px 60px rgba(0,0,0,0.15);
    --blur:       saturate(180%) blur(20px);
  }
  * { -webkit-font-smoothing: antialiased; box-sizing: border-box; }
  body {
    font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, sans-serif;
    background: var(--bg);
    color: var(--text);
    min-height: 100vh;
  }
  /* ── iOS-style cards ── */
  .ios-card {
    background: var(--surface);
    border-radius: var(--radius-2xl);
    box-shadow: var(--shadow-sm);
    border: 1px solid var(--border);
    transition: transform .25s cubic-bezier(.34,1.56,.64,1), box-shadow .25s ease;
  }
  .ios-card:hover { transform: translateY(-3px); box-shadow: var(--shadow-md); }
  /* ── iOS-style buttons ── */
  .ios-btn {
    display: inline-flex; align-items: center; justify-content: center; gap: 6px;
    padding: 14px 24px;
    background: var(--green);
    color: white;
    font-weight: 700;
    font-size: 15px;
    border-radius: 50px;
    border: none; cursor: pointer;
    transition: all .2s cubic-bezier(.34,1.56,.64,1);
    box-shadow: 0 4px 15px rgba(34,197,94,0.35);
    letter-spacing: -0.2px;
  }
  .ios-btn:hover { background: var(--green-dark); transform: scale(1.03); box-shadow: 0 6px 20px rgba(34,197,94,0.45); }
  .ios-btn:active { transform: scale(0.97); }
  .ios-btn-ghost {
    display: inline-flex; align-items: center; justify-content: center; gap: 6px;
    padding: 13px 22px;
    background: var(--bg);
    color: var(--text);
    font-weight: 600; font-size: 15px;
    border-radius: 50px;
    border: 1.5px solid var(--border);
    cursor: pointer;
    transition: all .2s ease;
  }
  .ios-btn-ghost:hover { background: #e8e8ed; border-color: #c7c7cc; }
  .ios-btn-icon {
    width: 44px; height: 44px;
    background: var(--bg);
    border-radius: 50%;
    border: 1.5px solid var(--border);
    display: inline-flex; align-items: center; justify-content: center;
    cursor: pointer; transition: all .2s ease;
  }
  .ios-btn-icon:hover { background: #e8e8ed; }
  /* ── iOS input ── */
  .ios-input {
    width: 100%;
    background: var(--bg);
    border: 1.5px solid transparent;
    border-radius: 14px;
    padding: 13px 16px;
    font-size: 15px;
    font-family: inherit;
    color: var(--text);
    outline: none;
    transition: all .2s ease;
  }
  .ios-input:focus { background: white; border-color: var(--green); box-shadow: 0 0 0 4px rgba(34,197,94,0.12); }
  .ios-input::placeholder { color: var(--text3); }
  /* ── Badge ── */
  .badge { display: inline-flex; align-items: center; gap: 4px; padding: 4px 10px; border-radius: 50px; font-size: 12px; font-weight: 600; }
  .badge-green  { background: var(--green-soft); color: var(--green-dark); }
  .badge-yellow { background: #fef9c3; color: #854d0e; }
  .badge-red    { background: #fee2e2; color: #991b1b; }
  .badge-blue   { background: #dbeafe; color: #1e40af; }
  .badge-purple { background: #f3e8ff; color: #6b21a8; }
  .badge-gray   { background: #f3f4f6; color: #374151; }
  /* ── Material Icons Rounded ── */
  .icon { font-family: 'Material Symbols Rounded'; font-style: normal; font-size: 22px; line-height: 1;
          letter-spacing: normal; text-transform: none; display: inline-block; white-space: nowrap;
          word-wrap: normal; direction: ltr; font-variation-settings: 'FILL' 0,'wght' 400,'GRAD' 0,'opsz' 24; }
  .icon-fill { font-variation-settings: 'FILL' 1,'wght' 400,'GRAD' 0,'opsz' 24; }
  /* ── Sidebar iOS ── */
  #sidebar {
    background: rgba(255,255,255,0.85);
    backdrop-filter: var(--blur);
    -webkit-backdrop-filter: var(--blur);
    border-right: 1px solid var(--border);
  }
  .nav-item {
    display: flex; align-items: center; gap: 12px;
    padding: 11px 14px; border-radius: 14px;
    font-size: 14px; font-weight: 500; color: var(--text2);
    text-decoration: none; transition: all .18s ease; cursor: pointer;
  }
  .nav-item:hover { background: var(--bg); color: var(--text); }
  .nav-item.active {
    background: var(--green-soft);
    color: var(--green-dark);
    font-weight: 700;
    box-shadow: inset 0 0 0 1px rgba(34,197,94,0.2);
  }
  .nav-item .icon { font-size: 20px; }
  /* ── Header iOS ── */
  .ios-header {
    position: sticky; top: 0; z-index: 50;
    background: rgba(255,255,255,0.8);
    backdrop-filter: var(--blur);
    -webkit-backdrop-filter: var(--blur);
    border-bottom: 1px solid var(--border);
    padding: 12px 24px;
    display: flex; align-items: center; justify-content: space-between;
  }
  /* ── Modal iOS ── */
  .ios-modal-bg {
    position: fixed; inset: 0;
    background: rgba(0,0,0,0.5);
    backdrop-filter: blur(8px);
    -webkit-backdrop-filter: blur(8px);
    display: flex; align-items: center; justify-content: center;
    z-index: 200; padding: 16px;
    opacity: 0; pointer-events: none;
    transition: opacity .25s ease;
  }
  .ios-modal-bg.open { opacity: 1; pointer-events: all; }
  .ios-modal {
    background: white;
    border-radius: var(--radius-2xl);
    width: 100%; max-width: 500px;
    box-shadow: var(--shadow-lg);
    transform: translateY(30px) scale(0.97);
    transition: transform .3s cubic-bezier(.34,1.2,.64,1);
    max-height: 90vh; overflow-y: auto;
  }
  .ios-modal-bg.open .ios-modal { transform: translateY(0) scale(1); }
  /* ── Segmented control iOS ── */
  .seg-control { background: #e5e5ea; border-radius: 10px; padding: 3px; display: flex; gap: 2px; }
  .seg-btn { flex: 1; padding: 7px 12px; border-radius: 8px; font-size: 13px; font-weight: 600; border: none; cursor: pointer; background: transparent; color: var(--text2); transition: all .2s ease; text-align: center; }
  .seg-btn.active { background: white; color: var(--text); box-shadow: 0 1px 4px rgba(0,0,0,0.15); }
  /* ── Pill chip ── */
  .chip { display: inline-flex; align-items: center; gap: 4px; padding: 6px 14px; border-radius: 50px; font-size: 13px; font-weight: 600; border: 1.5px solid var(--border); cursor: pointer; transition: all .18s ease; background: white; color: var(--text2); }
  .chip:hover { border-color: var(--green); color: var(--green-dark); }
  .chip.active { background: var(--green); color: white; border-color: var(--green); }
  /* ── Scrollbar ── */
  ::-webkit-scrollbar { width: 5px; height: 5px; }
  ::-webkit-scrollbar-track { background: transparent; }
  ::-webkit-scrollbar-thumb { background: #c7c7cc; border-radius: 3px; }
  /* ── Toast ── */
  #toast {
    position: fixed; bottom: 30px; left: 50%; transform: translateX(-50%) translateY(80px);
    background: rgba(28,28,30,0.95); color: white;
    padding: 12px 20px; border-radius: 50px; font-size: 14px; font-weight: 600;
    z-index: 999; backdrop-filter: blur(10px);
    transition: transform .4s cubic-bezier(.34,1.56,.64,1), opacity .4s ease;
    opacity: 0; white-space: nowrap;
  }
  #toast.show { transform: translateX(-50%) translateY(0); opacity: 1; }
  /* ── Anim ── */
  @keyframes fadeUp { from { opacity:0; transform:translateY(16px); } to { opacity:1; transform:translateY(0); } }
  .fade-up { animation: fadeUp .4s ease both; }
  @keyframes scaleIn { from { opacity:0; transform:scale(0.95); } to { opacity:1; transform:scale(1); } }
  .scale-in { animation: scaleIn .3s ease both; }
</style>

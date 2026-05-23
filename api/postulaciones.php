<?php
// ============================================================
//  api/postulaciones.php
//  POST ?accion=enviar  → paciente/nutri envía postulación
//  GET                  → admin lista postulaciones
//  PUT ?accion=revisar  → admin aprueba/rechaza
//  GET ?accion=disponibilidad&nutri_id=X&fecha=Y → horas libres
// ============================================================
session_start();
require_once __DIR__ . '/../config.php';

$metodo = $_SERVER['REQUEST_METHOD'];
$accion = $_GET['accion'] ?? '';
$body   = json_decode(file_get_contents('php://input'), true) ?? [];

if ($metodo === 'GET' && $accion === 'disponibilidad') { horasDisponibles(); exit; }

match($metodo) {
    'GET' => listar(),
    'POST'=> enviar($body),
    'PUT' => revisar($body),
    default => responderJSON(['error' => 'Metodo no permitido'], 405)
};

// ─── Horas disponibles de un nutricionista en una fecha ───────
function horasDisponibles(): void {
    requireAuth();
    $db      = getDB();
    $nutriId = intval($_GET['nutri_id'] ?? 0);
    $fecha   = $_GET['fecha'] ?? '';

    if (!$nutriId || !$fecha) responderJSON(['error' => 'nutri_id y fecha requeridos'], 400);

    // Día de la semana de la fecha pedida (0=Lun ... 6=Dom)
    $diaSemana = (int)date('N', strtotime($fecha)) - 1;  // N devuelve 1=Lun..7=Dom

    // Obtener bloques de disponibilidad del nutricionista ese dia
    $stmt = $db->prepare("
        SELECT hora_inicio, hora_fin FROM disponibilidad
        WHERE nutricionista_id = ? AND dia_semana = ?
    ");
    $stmt->execute([$nutriId, $diaSemana]);
    $bloques = $stmt->fetchAll();

    // Obtener la duracion de consulta
    $nStmt = $db->prepare("SELECT duracion_consulta FROM nutricionistas WHERE id = ?");
    $nStmt->execute([$nutriId]);
    $nutri = $nStmt->fetch();
    $duracion = $nutri['duracion_consulta'] ?? 60;

    // Obtener horas ya reservadas ese día
    $resStmt = $db->prepare("
        SELECT TIME_FORMAT(hora,'%H:%i') AS hora
        FROM citas
        WHERE nutricionista_id = ? AND fecha = ? AND estado != 'cancelada'
    ");
    $resStmt->execute([$nutriId, $fecha]);
    $reservadas = array_column($resStmt->fetchAll(), 'hora');

    // Generar slots disponibles
    $slots = [];
    foreach ($bloques as $bloque) {
        $cursor = strtotime($bloque['hora_inicio']);
        $fin    = strtotime($bloque['hora_fin']);
        while ($cursor + $duracion * 60 <= $fin) {
            $hora = date('H:i', $cursor);
            if (!in_array($hora, $reservadas)) {
                $slots[] = $hora;
            }
            $cursor += $duracion * 60;
        }
    }

    responderJSON(['slots' => $slots, 'duracion' => $duracion]);
}

// ─── Listar postulaciones (admin) ─────────────────────────────
function listar(): void {
    requireAdmin();
    $db    = getDB();
    $estado = $_GET['estado'] ?? '';

    $where = $estado ? "WHERE p.estado = '$estado'" : '';

    $stmt = $db->query("
        SELECT p.id, p.estado, p.puntaje_tecnico, p.alertas, p.created_at,
               u.nombre, u.email,
               p.universidad, p.titulo_prof, p.registro_prof,
               p.especialidades, p.experiencia, p.licencia_vence,
               p.resp_tecnica_1, p.resp_tecnica_2, p.resp_tecnica_3,
               p.resp_tecnica_4, p.resp_tecnica_5, p.notas_admin
        FROM postulaciones p
        JOIN usuarios u ON u.id = p.usuario_id
        $where
        ORDER BY p.created_at DESC
    ");
    responderJSON($stmt->fetchAll());
}

// ─── Enviar postulación ───────────────────────────────────────
function enviar(array $body): void {
    $usuario = requireAuth();

    // Verificar que no haya postulación previa pendiente/aprobada
    $db    = getDB();
    $check = $db->prepare("SELECT id, estado FROM postulaciones WHERE usuario_id = ? ORDER BY created_at DESC LIMIT 1");
    $check->execute([$usuario['id']]);
    $prev  = $check->fetch();
    if ($prev && in_array($prev['estado'], ['pendiente','aprobado'])) {
        responderJSON(['error' => 'Ya tienes una postulación ' . $prev['estado']], 409);
    }

    // Validaciones básicas obligatorias
    $campos = ['universidad','carrera','titulo_prof','registro_prof'];
    foreach ($campos as $c) {
        if (empty($body[$c])) {
            responderJSON(['error' => "El campo '$c' es obligatorio"], 400);
        }
    }

    // Validar carrera: debe ser de nutrición
    $carrera    = strtolower($body['carrera'] ?? '');
    $invalidas  = ['coaching','fitness','entrenador','biología','enfermería','medicina general'];
    foreach ($invalidas as $inv) {
        if (str_contains($carrera, $inv)) {
            responderJSON(['error' => "La carrera '$carrera' no es compatible con la plataforma. Solo nutricionistas certificados."], 422);
        }
    }
    $validas = ['nutrici','dietética','dietetica','alimentaci'];
    $esValida = false;
    foreach ($validas as $v) { if (str_contains($carrera, $v)) { $esValida = true; break; } }
    if (!$esValida) {
        responderJSON(['error' => 'La carrera debe ser de Nutrición, Nutrición Clínica o Nutrición y Dietética'], 422);
    }

    // Detectar vencimiento de licencia
    $alertas = [];
    if (!empty($body['licencia_vence'])) {
        if ($body['licencia_vence'] < date('Y-m-d')) {
            $alertas[] = '⚠ Licencia profesional VENCIDA';
        }
    } else {
        $alertas[] = '⚠ No se proporcionó fecha de vencimiento de licencia';
    }

    // Puntaje técnico básico (análisis de longitud/coherencia de respuestas)
    $puntaje = calcularPuntajeTecnico($body);
    if ($puntaje < 40) $alertas[] = '⚠ Respuestas técnicas con puntaje bajo (' . $puntaje . '/100)';

    $stmt = $db->prepare("
        INSERT INTO postulaciones
          (usuario_id, ci, fecha_nacimiento, sexo, pais, ciudad, direccion_prof, telefono,
           universidad, carrera, anio_egreso, anio_titulacion, titulo_prof,
           registro_prof, institucion_reg, licencia_inicio, licencia_vence,
           especialidades, experiencia,
           tipo_consulta, precio, duracion_consulta, modalidad, descripcion_serv,
           idiomas, horarios, max_pacientes_dia,
           resp_tecnica_1, resp_tecnica_2, resp_tecnica_3, resp_tecnica_4, resp_tecnica_5,
           puntaje_tecnico, alertas, estado)
        VALUES
          (?,?,?,?,?,?,?,?,
           ?,?,?,?,?,
           ?,?,?,?,
           ?,?,
           ?,?,?,?,?,
           ?,?,?,
           ?,?,?,?,?,
           ?,?,'pendiente')
    ");

    $stmt->execute([
        $usuario['id'],
        $body['ci'] ?? null, $body['fecha_nacimiento'] ?? null,
        $body['sexo'] ?? null, $body['pais'] ?? 'Bolivia',
        $body['ciudad'] ?? null, $body['direccion_prof'] ?? null, $body['telefono'] ?? null,
        $body['universidad'], $body['carrera'],
        intval($body['anio_egreso'] ?? 0) ?: null,
        intval($body['anio_titulacion'] ?? 0) ?: null,
        $body['titulo_prof'],
        $body['registro_prof'], $body['institucion_reg'] ?? null,
        $body['licencia_inicio'] ?? null, $body['licencia_vence'] ?? null,
        json_encode($body['especialidades'] ?? []),
        json_encode($body['experiencia'] ?? []),
        $body['tipo_consulta'] ?? 'Consulta nutricional',
        floatval($body['precio'] ?? 120),
        intval($body['duracion_consulta'] ?? 60),
        $body['modalidad'] ?? 'Virtual',
        $body['descripcion_serv'] ?? null,
        $body['idiomas'] ?? 'Español',
        json_encode($body['horarios'] ?? []),
        intval($body['max_pacientes_dia'] ?? 8),
        $body['resp_tecnica_1'] ?? null, $body['resp_tecnica_2'] ?? null,
        $body['resp_tecnica_3'] ?? null, $body['resp_tecnica_4'] ?? null,
        $body['resp_tecnica_5'] ?? null,
        $puntaje,
        implode("\n", $alertas) ?: null,
    ]);

    // Actualizar rol del usuario a Nutricionista
    $db->prepare("UPDATE usuarios SET rol='Nutricionista' WHERE id=?")->execute([$usuario['id']]);

    responderJSON(['ok' => true, 'mensaje' => 'Postulación enviada. Estará disponible tras revisión administrativa.', 'puntaje' => $puntaje]);
}

// ─── Revisar (admin aprueba/rechaza) ─────────────────────────
function revisar(array $body): void {
    requireAdmin();
    $db     = getDB();
    $id     = intval($body['id'] ?? 0);
    $estado = $body['estado'] ?? '';
    $notas  = $body['notas_admin'] ?? '';

    if (!$id || !in_array($estado, ['aprobado','rechazado','pendiente'])) {
        responderJSON(['error' => 'ID y estado válido son obligatorios'], 400);
    }

    // Actualizar postulación
    $stmt = $db->prepare("UPDATE postulaciones SET estado=?, notas_admin=? WHERE id=?");
    $stmt->execute([$estado, $notas, $id]);

    // Si aprobado: crear/actualizar el perfil del nutricionista
    if ($estado === 'aprobado') {
        $post = $db->prepare("SELECT * FROM postulaciones WHERE id=?");
        $post->execute([$id]);
        $p    = $post->fetch();

        // ¿Ya tiene registro en nutricionistas?
        $existe = $db->prepare("SELECT id FROM nutricionistas WHERE usuario_id=?");
        $existe->execute([$p['usuario_id']]);
        $nExiste = $existe->fetch();

        $especialidades = json_decode($p['especialidades'], true);
        $espPrincipal   = is_array($especialidades) && count($especialidades) > 0
            ? $especialidades[0]['nombre'] ?? 'Nutrición General'
            : 'Nutrición General';

        if ($nExiste) {
            $upd = $db->prepare("
                UPDATE nutricionistas SET
                    especialidad=?, universidad=?, titulo=?,
                    anio_egreso=?, anio_titulacion=?,
                    registro_prof=?, institucion_reg=?,
                    licencia_inicio=?, licencia_vence=?,
                    modalidad=?, idiomas=?,
                    duracion_consulta=?, max_pacientes_dia=?,
                    precio=?, estado_verificacion='aprobado', puntaje_tecnico=?,
                    descripcion_serv=?
                WHERE usuario_id=?
            ");
        } else {
            $upd = $db->prepare("
                INSERT INTO nutricionistas
                  (usuario_id, especialidad, universidad, titulo,
                   anio_egreso, anio_titulacion,
                   registro_prof, institucion_reg,
                   licencia_inicio, licencia_vence,
                   modalidad, idiomas,
                   duracion_consulta, max_pacientes_dia,
                   precio, rating, estado_verificacion, puntaje_tecnico,
                   descripcion_serv)
                VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,5.0,'aprobado',?,?)
            ");
        }

        $params = [
            $espPrincipal, $p['universidad'], $p['titulo_prof'],
            $p['anio_egreso'], $p['anio_titulacion'],
            $p['registro_prof'], $p['institucion_reg'],
            $p['licencia_inicio'], $p['licencia_vence'],
            $p['modalidad'], $p['idiomas'],
            $p['duracion_consulta'], $p['max_pacientes_dia'],
            $p['precio'], $p['puntaje_tecnico'],
            $p['descripcion_serv']
        ];
        if ($nExiste) $params[] = $p['usuario_id'];
        else           array_unshift($params, $p['usuario_id']);

        $upd->execute($params);
    }

    responderJSON(['ok' => true, 'mensaje' => "Postulación marcada como $estado"]);
}

// ─── Puntaje técnico simple (longitud + palabras clave) ───────
function calcularPuntajeTecnico(array $body): int {
    $claves = ['calorías','proteína','carbohidrato','glucosa','imc','talla','peso',
               'intervención','plan','evaluación','adherencia','seguimiento',
               'desnutrición','malnutrición','metabolismo','dietética','kcal'];
    $puntaje = 0;
    for ($i = 1; $i <= 5; $i++) {
        $resp = strtolower($body["resp_tecnica_$i"] ?? '');
        if (strlen($resp) > 50)  $puntaje += 8;   // respuesta larga
        if (strlen($resp) > 120) $puntaje += 4;   // respuesta muy larga
        foreach ($claves as $clave) {
            if (str_contains($resp, $clave)) { $puntaje += 2; break; }
        }
    }
    return min($puntaje, 100);
}

<?php
session_start();
if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.html");
    exit();
}

$db = new SQLite3(__DIR__ . '/fitly.db');
$uid = $_SESSION['usuario_id'];

/* ==========================
   GUARDAR HÁBITO DEL DÍA
========================== */
if (isset($_POST['habito'])) {

    $h = $_POST['habito'];
    $pts = $_POST['puntos_h'];
    $fecha = date("Y-m-d");

    // evita duplicar el mismo hábito el mismo día
    $existe = $db->querySingle(
        "SELECT COUNT(*) FROM habitos 
         WHERE usuario_id=$uid AND nombre='$h' AND fecha='$fecha'"
    );

    if ($existe == 0) {
        $stmt = $db->prepare(
            "INSERT INTO habitos (usuario_id,nombre,puntos,fecha)
             VALUES (:u,:n,:p,:f)"
        );
        $stmt->bindValue(':u', $uid);
        $stmt->bindValue(':n', $h);
        $stmt->bindValue(':p', $pts);
        $stmt->bindValue(':f', $fecha);
        $stmt->execute();
    }
}

/* ==========================
   AGREGAR META
========================== */
if(isset($_POST['nueva_meta'])){
    $t = $_POST['titulo'];
    $d = $_POST['descripcion'];

    $stmt = $db->prepare("INSERT INTO metas (usuario_id,titulo,descripcion,progreso,completado) 
                          VALUES (:u,:t,:d,0,0)");
    $stmt->bindValue(':u',$uid);
    $stmt->bindValue(':t',$t);
    $stmt->bindValue(':d',$d);
    $stmt->execute();
}

/* ==========================
   EDITAR META
========================== */
if(isset($_POST['editar_meta'])){
    $stmt = $db->prepare("UPDATE metas SET titulo=:t, descripcion=:d 
                          WHERE id=:id AND usuario_id=:u");
    $stmt->bindValue(':t', $_POST['titulo_edit']);
    $stmt->bindValue(':d', $_POST['desc_edit']);
    $stmt->bindValue(':id', $_POST['id_edit']);
    $stmt->bindValue(':u', $uid);
    $stmt->execute();
}

/* ==========================
   ACTUALIZAR PROGRESO
========================== */
if(isset($_POST['act_progreso'])){
    $stmt = $db->prepare("UPDATE metas SET progreso=:p 
                          WHERE id=:id AND usuario_id=:u AND completado=0");
    $stmt->bindValue(':p', $_POST['progreso']);
    $stmt->bindValue(':id', $_POST['id']);
    $stmt->bindValue(':u', $uid);
    $stmt->execute();
}

/* ==========================
   COMPLETAR META
========================== */
if(isset($_GET['completar'])){
    $db->exec("UPDATE metas SET completado=1, progreso=100 
               WHERE id=".$_GET['completar']." AND usuario_id=$uid");
}

/* ==========================
   ELIMINAR META
========================== */
if(isset($_GET['eliminar'])){
    $db->exec("DELETE FROM metas WHERE id=".$_GET['eliminar']." AND usuario_id=$uid");
}

/* ==========================
   OBTENER METAS
========================== */
$res = $db->query("SELECT * FROM metas WHERE usuario_id=$uid ORDER BY id DESC");

/* ======================================
   CONSULTAR HABITOS COMPLETADOS HOY
====================================== */
$hoy = date("Y-m-d");
$done = $db->query("SELECT nombre FROM habitos WHERE usuario_id=$uid AND fecha='$hoy'");
$completadosHoy = [];
while($d = $done->fetchArray()) $completadosHoy[] = $d['nombre'];
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Metas Fitly</title>
<link rel="stylesheet" href="estilos.css">
<link rel="stylesheet" href="estilos2.css">

<style>
/* ===== MISMO ESTILO QUE DASHBOARD, PROGRESO Y NUTRICIÓN ===== */
:root {
    --verde-claro: #7cb518;
    --verde-oliva: #5e5d02;
    --verde-menta: #c7e9b0;
    --gris-suave: #f5f7f0;
    --lavanda: #a998ab;
    --morado: #410057;
    --blanco: #ffffff;
    --texto-oscuro: #2d3e2b;
}

* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

body {
    background: linear-gradient(135deg, #f0f7e8 0%, #e8f0e0 100%);
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
}

/* Header superior */
body > h2 {
    background: linear-gradient(135deg, var(--verde-oliva), #3a5a0e);
    color: white;
    padding: 20px 30px;
    margin: 0;
    font-size: 24px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.08);
    display: inline-block;
    width: 100%;
    border-bottom: 3px solid var(--verde-menta);
}

/* Título dentro del main */
.main-fitly > h2 {
    background: none;
    color: var(--verde-oliva);
    padding: 0;
    margin: 0 0 20px 0;
    box-shadow: none;
    border: none;
    font-size: 32px;
    display: flex;
    align-items: center;
    gap: 10px;
}

.main-fitly > h2:first-of-type::before {
    content: "🎯";
    font-size: 35px;
}

.main-fitly > h2:last-of-type::before {
    content: "✅";
    font-size: 35px;
}

.cerrar-sesion {
    position: absolute;
    top: 22px;
    right: 30px;
    background: rgba(255,255,255,0.2);
    backdrop-filter: blur(8px);
    color: white;
    padding: 8px 20px;
    border-radius: 30px;
    text-decoration: none;
    font-weight: 500;
    transition: all 0.3s ease;
    border: 1px solid rgba(255,255,255,0.3);
    z-index: 100;
}

.cerrar-sesion:hover {
    background: var(--verde-oliva);
    transform: scale(1.03);
    border-color: var(--verde-menta);
}

/* Dashboard container */
.dashboard-container {
    display: flex;
    min-height: calc(100vh - 70px);
}

/* Sidebar */
.sidebar-fitly {
    width: 260px;
    background: linear-gradient(180deg, #2d3e2b 0%, #1f2e1d 100%);
    padding: 30px 20px;
    box-shadow: 4px 0 20px rgba(0,0,0,0.08);
}

.sidebar-fitly h2 {
    background: transparent;
    padding: 0 0 20px 0;
    font-size: 28px;
    text-align: center;
    border-bottom: 2px solid var(--verde-menta);
    margin-bottom: 25px;
    box-shadow: none;
    color: white;
    display: block;
}

.sidebar-fitly a {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 12px 18px;
    margin: 8px 0;
    color: #c8ddb5;
    text-decoration: none;
    border-radius: 12px;
    transition: all 0.3s;
    font-weight: 500;
}

.sidebar-fitly a::before {
    content: "✨";
    font-size: 18px;
}

.sidebar-fitly a:nth-child(3)::before { content: "🏠"; }
.sidebar-fitly a:nth-child(4)::before { content: "📊"; }
.sidebar-fitly a:nth-child(5)::before { content: "🥗"; }
.sidebar-fitly a:nth-child(6)::before { content: "🎯"; }
.sidebar-fitly a:nth-child(7)::before { content: "🚪"; }

.sidebar-fitly a:hover {
    background: rgba(124, 181, 24, 0.25);
    color: white;
    transform: translateX(5px);
}

.sidebar-fitly a.active {
    background: linear-gradient(90deg, var(--verde-oliva), #3a6b0f);
    color: white;
    box-shadow: 0 4px 12px rgba(94, 93, 2, 0.3);
}

/* Main content */
.main-fitly {
    flex: 1;
    padding: 30px;
    overflow-y: auto;
}

/* --- FORMULARIO NUEVA META --- */
.form-meta {
    background: var(--blanco);
    padding: 20px;
    border-radius: 24px;
    display: flex;
    gap: 12px;
    align-items: center;
    margin-bottom: 25px;
    box-shadow: 0 5px 15px rgba(0,0,0,0.05);
    border: 1px solid rgba(124, 181, 24, 0.2);
}

.form-meta input {
    flex: 1;
    padding: 12px 15px;
    border-radius: 14px;
    border: 2px solid #e0e8d8;
    font-size: 15px;
    transition: all 0.3s;
}

.form-meta input:focus {
    outline: none;
    border-color: var(--verde-claro);
    box-shadow: 0 0 0 3px rgba(124, 181, 24, 0.1);
}

.form-meta button {
    background: linear-gradient(135deg, var(--verde-oliva), #4a6b0c);
    color: white;
    padding: 12px 24px;
    border: none;
    border-radius: 14px;
    cursor: pointer;
    font-size: 15px;
    font-weight: bold;
    transition: all 0.3s;
}

.form-meta button:hover {
    transform: scale(1.02);
    box-shadow: 0 5px 15px rgba(94, 93, 2, 0.3);
}

hr {
    margin: 25px 0;
    border: none;
    height: 2px;
    background: linear-gradient(90deg, var(--verde-menta), transparent);
}

/* --- TARJETAS DE META --- */
.meta-card {
    background: var(--blanco);
    padding: 25px;
    border-radius: 24px;
    margin-bottom: 25px;
    box-shadow: 0 5px 20px rgba(0,0,0,0.05);
    transition: all 0.3s ease;
    border: 1px solid rgba(124, 181, 24, 0.2);
}

.meta-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 15px 30px rgba(94, 93, 2, 0.1);
    border-color: var(--verde-claro);
}

.meta-card.completa {
    opacity: 0.7;
    background: linear-gradient(135deg, #f5f7f0, var(--blanco));
}

.meta-title {
    font-size: 22px;
    font-weight: 700;
    color: var(--verde-oliva);
}

.meta-desc {
    margin-top: 5px;
    margin-bottom: 18px;
    color: #667c5e;
}

/* Barra de progreso */
.meta-bar {
    height: 12px;
    background: #e8f0e0;
    border-radius: 20px;
    margin-top: 15px;
    overflow: hidden;
}

.meta-fill {
    height: 12px;
    border-radius: 20px;
    background: linear-gradient(90deg, var(--verde-oliva), var(--verde-claro));
    transition: width 0.3s ease;
}

/* Rango personalizado */
.input-progreso {
    width: 100%;
    appearance: none;
    height: 8px;
    border-radius: 10px;
    background: #e0e8d8;
    margin-bottom: 8px;
}

.input-progreso::-webkit-slider-thumb {
    -webkit-appearance: none;
    height: 18px;
    width: 18px;
    background: var(--verde-claro);
    border-radius: 50%;
    cursor: pointer;
    border: 2px solid white;
    box-shadow: 0 0 5px rgba(0,0,0,0.2);
}

.input-progreso::-webkit-slider-thumb:hover {
    transform: scale(1.2);
    background: var(--verde-oliva);
}

/* Botón guardar */
.btn-guardar {
    background: var(--verde-oliva);
    color: white;
    padding: 8px 16px;
    border: none;
    border-radius: 12px;
    font-size: 14px;
    cursor: pointer;
    margin-top: 8px;
    transition: all 0.3s;
}

.btn-guardar:hover {
    background: var(--verde-claro);
    transform: scale(1.02);
}

/* Acciones de meta */
.meta-actions {
    margin-top: 15px;
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
}

.meta-actions button,
.meta-actions a {
    padding: 8px 16px;
    border-radius: 12px;
    text-decoration: none;
    font-size: 13px;
    font-weight: 500;
    transition: all 0.3s;
    cursor: pointer;
    border: none;
}

.meta-actions .editar { 
    background: var(--morado); 
    color: white; 
}
.meta-actions .editar:hover { 
    background: #6a2a7a; 
    transform: scale(1.02);
}

.meta-actions .completar { 
    background: var(--verde-oliva); 
    color: white; 
}
.meta-actions .completar:hover { 
    background: #4a6b0c; 
    transform: scale(1.02);
}

.meta-actions .eliminar { 
    background: #c76e00; 
    color: white; 
}
.meta-actions .eliminar:hover { 
    background: #a85a00; 
    transform: scale(1.02);
}

/* Hábitos */
.habito {
    background: var(--gris-suave);
    padding: 15px 18px;
    border-radius: 18px;
    margin-bottom: 12px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    transition: all 0.2s;
    border: 1px solid rgba(124, 181, 24, 0.15);
}

.habito:hover {
    transform: translateX(5px);
    border-color: var(--verde-claro);
}

.habito button {
    background: linear-gradient(135deg, var(--verde-oliva), #4a6b0c);
    border: none;
    padding: 8px 18px;
    border-radius: 30px;
    color: white;
    font-weight: bold;
    cursor: pointer;
    transition: all 0.3s;
}

.habito button:hover:not(:disabled) {
    transform: scale(1.05);
    box-shadow: 0 3px 10px rgba(94, 93, 2, 0.3);
}

.habito button:disabled {
    background: var(--verde-claro);
    cursor: not-allowed;
}

/* Puntos box */
.puntos-box {
    background: linear-gradient(135deg, #e8f5e9, #d4ecd0);
    padding: 20px;
    border-radius: 20px;
    text-align: center;
    font-size: 22px;
    font-weight: bold;
    color: var(--verde-oliva);
    margin-bottom: 25px;
    border: 1px solid var(--verde-menta);
}

/* Modal */
#modalEditar {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    backdrop-filter: blur(5px);
    background: rgba(0,0,0,0.6);
    padding-top: 80px;
    z-index: 1000;
}

.modal-inner {
    background: var(--blanco);
    width: 90%;
    max-width: 450px;
    margin: auto;
    padding: 25px;
    border-radius: 24px;
    box-shadow: 0 20px 40px rgba(0,0,0,0.2);
}

.modal-inner h3 {
    color: var(--verde-oliva);
    margin-bottom: 20px;
    font-size: 24px;
}

.modal-inner label {
    display: block;
    margin: 15px 0 5px;
    color: var(--texto-oscuro);
    font-weight: 500;
}

.modal-inner input {
    width: 100%;
    padding: 12px;
    border-radius: 12px;
    border: 2px solid #e0e8d8;
    transition: all 0.3s;
}

.modal-inner input:focus {
    outline: none;
    border-color: var(--verde-claro);
}

.modal-inner button {
    margin-top: 20px;
    width: 100%;
}

/* === RECOMPENSAS === */
.recompensas-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
    gap: 20px;
    margin-top: 20px;
}

.recompensa-card {
    background: var(--blanco);
    border-radius: 20px;
    overflow: hidden;
    box-shadow: 0 5px 15px rgba(0,0,0,0.05);
    transition: all 0.3s ease;
    opacity: 0.6;
    filter: grayscale(0.2);
    border: 1px solid rgba(124, 181, 24, 0.15);
}

.recompensa-card.desbloqueada {
    opacity: 1;
    filter: grayscale(0);
    box-shadow: 0 8px 25px rgba(124, 181, 24, 0.2);
    border: 2px solid var(--verde-claro);
}

.recompensa-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 15px 35px rgba(0,0,0,0.1);
}

.recompensa-img {
    width: 100%;
    height: 140px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 48px;
}

.recompensa-info {
    padding: 15px;
}

.recompensa-info h4 {
    margin: 0 0 8px 0;
    color: var(--verde-oliva);
    font-size: 18px;
}

.recompensa-info p {
    margin: 0;
    color: #667c5e;
    font-size: 13px;
}

.recompensa-puntos {
    display: inline-block;
    background: var(--verde-oliva);
    color: white;
    padding: 4px 12px;
    border-radius: 20px;
    font-size: 12px;
    margin-top: 10px;
}

/* Responsive */
@media (max-width: 768px) {
    .dashboard-container {
        flex-direction: column;
    }
    
    .sidebar-fitly {
        width: 100%;
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        padding: 15px;
    }
    
    .sidebar-fitly a {
        flex: 1;
        min-width: 100px;
        justify-content: center;
    }
    
    .sidebar-fitly h2 {
        width: 100%;
        text-align: center;
    }
    
    .cerrar-sesion {
        position: static;
        display: inline-block;
        margin: 15px;
    }
    
    body > h2 {
        text-align: center;
        padding: 15px;
    }
    
    .main-fitly {
        padding: 20px;
    }
    
    .form-meta {
        flex-direction: column;
    }
    
    .form-meta button {
        width: 100%;
    }
    
    .meta-actions {
        flex-direction: column;
    }
    
    .meta-actions button,
    .meta-actions a {
        text-align: center;
    }
}
</style>
</head>

<body>

<h2> FITLY  Metas</h2>
<a href="logout.php" class="cerrar-sesion">🚪 Cerrar sesión</a>

<div class="dashboard-container">

<div class="sidebar-fitly">
    <h2>FITLY</h2>
    <a href="dashboard.php">Inicio</a>
    <a href="progreso_fitly.php">Progreso</a>
    <a href="nutrición_fitly.html">Nutrición</a>
    <a href="metas_fitly.php" class="active">Metas</a>
    <a href="logout.php">Cerrar sesión</a>
</div>

<div class="main-fitly">

<h2>Metas personales</h2>

<!-- FORMULARIO NUEVA META -->
<form method="POST" class="form-meta">
    <input name="titulo" placeholder="🎯 Ej: Bajar 5kg" required>
    <input name="descripcion" placeholder="📝 Notas o detalles...">
    <button name="nueva_meta">+ Agregar meta</button>
</form>

<hr>

<!-- METAS -->
<?php while($m = $res->fetchArray()): ?>
<div class="meta-card <?= $m['completado'] ? 'completa':'' ?>">

    <div class="meta-title"><?= $m['titulo'] ?></div>
    <div class="meta-desc"><?= $m['descripcion'] ?></div>

    <?php if(!$m['completado']): ?>
    <form method="POST">
        <input type="hidden" name="id" value="<?= $m['id'] ?>">

        <input type="range" 
               class="input-progreso"
               name="progreso" 
               value="<?= $m['progreso'] ?>"
               oninput="this.nextElementSibling.value=this.value">

        <output style="font-weight:bold; color:var(--verde-oliva);">
            <?= $m['progreso'] ?>
        </output> %

        <button class="btn-guardar" name="act_progreso">📌 Guardar progreso</button>
    </form>
    <?php else: ?>
        <p style="color: var(--verde-claro);"><b>✔ Meta completada - ¡Felicidades!</b></p>
    <?php endif; ?>

    <div class="meta-bar">
        <div class="meta-fill" style="width: <?= $m['progreso'] ?>%"></div>
    </div>

    <div class="meta-actions">
        <button class="editar" onclick="editarMeta(<?= $m['id'] ?>,'<?= htmlspecialchars($m['titulo']) ?>','<?= htmlspecialchars($m['descripcion']) ?>')">
            ✏ Editar
        </button>
        <a class="completar" href="?completar=<?= $m['id'] ?>">✔ Completar</a>
        <a class="eliminar" href="?eliminar=<?= $m['id'] ?>" onclick="return confirm('¿Eliminar esta meta?')">❌ Eliminar</a>
    </div>

</div>
<?php endwhile; ?>
   
<!-- MODAL EDITAR -->
<div id="modalEditar">
    <div class="modal-inner">
        <h3>✏ Editar meta</h3>

        <form method="POST">
            <input type="hidden" id="id_edit" name="id_edit">

            <label>Título:</label>
            <input id="titulo_edit" name="titulo_edit">

            <label>Descripción:</label>
            <input id="desc_edit" name="desc_edit">

            <button name="editar_meta" class="btn-guardar">💾 Guardar cambios</button>
        </form>

        <button onclick="document.getElementById('modalEditar').style.display='none'" 
                style="background:#c76e00; margin-top:10px;" 
                class="btn-guardar">❌ Cerrar</button>
    </div>
</div>

<hr>

<h2>Hábitos diarios</h2>

<?php
$puntosTotales = $db->querySingle("
    SELECT COALESCE(SUM(puntos),0)
    FROM habitos
    WHERE usuario_id=$uid
");
?>

<div class="puntos-box">⭐ Puntos acumulados: <span id="puntos"><?= $puntosTotales ?></span> ⭐</div>

<?php
$habitos = [
    ["id"=>1, "texto"=>"💧 Tomar 2L de agua", "puntos"=>5],
    ["id"=>2, "texto"=>"🚶 Caminar 3000 pasos", "puntos"=>10],
    ["id"=>3, "texto"=>"🏃 Ejercicio del día", "puntos"=>15],
    ["id"=>4, "texto"=>"🥗 Comer saludable", "puntos"=>10]
];

foreach($habitos as $h): ?>
<div class="habito">
    <span style="font-size: 16px;"><?= $h["texto"] ?></span>

    <?php if(in_array($h["texto"], $completadosHoy)): ?>
        <button disabled style="background: var(--verde-claro);">✅ Completado</button>
    <?php else: ?>
        <form method="POST" style="margin:0;">
            <input type="hidden" name="habito" value="<?= $h['texto'] ?>">
            <input type="hidden" name="puntos_h" value="<?= $h['puntos'] ?>">
            <button>+<?= $h["puntos"] ?> pts</button>
        </form>
    <?php endif; ?>
</div>
<?php endforeach; ?>

<h2>🎁 Recompensas desbloqueadas</h2>
<div class="recompensas-grid">

<?php
$recompensas = [
    [
        "pts"=>30, 
        "titulo"=>"Smoothie verde energético", 
        "desc"=>"Batido de espinacas, piña y jengibre - ¡Energía natural!",
        "emoji"=>"🥤",
        "color"=>"#c8e6c9"
    ],
    [
        "pts"=>60, 
        "titulo"=>"Snack saludable", 
        "desc"=>"Manzana con crema de cacahuate y canela",
        "emoji"=>"🍎",
        "color"=>"#ffccbc"
    ],
    [
        "pts"=>100, 
        "titulo"=>"Rutina especial cardio", 
        "desc"=>"Cardio Fitly de 10 min - Quema calorías extra",
        "emoji"=>"🔥",
        "color"=>"#ffab91"
    ],
    [
        "pts"=>150, 
        "titulo"=>"Desayuno fitness", 
        "desc"=>"Avena con proteína y frutos rojos",
        "emoji"=>"🥣",
        "color"=>"#b39ddb"
    ],
    [
        "pts"=>200, 
        "titulo"=>"Playlist motivacional", 
        "desc"=>"Lista de canciones para entrenar con ritmo",
        "emoji"=>"🎵",
        "color"=>"#80cbc4"
    ],
    [
        "pts"=>300, 
        "titulo"=>"Plancha challenge", 
        "desc"=>"Rutina de 5 min de plancha abdominal",
        "emoji"=>"💪",
        "color"=>"#ff8a65"
    ]
];

foreach($recompensas as $r):
    $desbloqueada = ($puntosTotales >= $r["pts"]);
    $clase = $desbloqueada ? "desbloqueada" : "";
?>

<div class="recompensa-card <?= $clase ?>">
    <div class="recompensa-img" style="background: <?= $r['color'] ?>;">
        <?= $desbloqueada ? $r['emoji'] : '🔒' ?>
    </div>
    <div class="recompensa-info">
        <h4><?= $r['titulo'] ?></h4>
        <p><?= $r['desc'] ?></p>
        <span class="recompensa-puntos">🎯 <?= $r['pts'] ?> pts</span>
        <?php if(!$desbloqueada): ?>
            <p style="font-size: 11px; color: #999; margin-top: 8px;">
                ⭐ Te faltan <?= $r['pts'] - $puntosTotales ?> puntos
            </p>
        <?php else: ?>
            <p style="font-size: 12px; color: var(--verde-claro); margin-top: 8px;">
                ✨ ¡Desbloqueada! ✨
            </p>
        <?php endif; ?>
    </div>
</div>

<?php endforeach; ?>

</div>

<!-- Mensaje de motivación -->
<?php if($puntosTotales < 30): ?>
    <div style="text-align: center; padding: 30px; background: var(--gris-suave); border-radius: 20px; margin-top: 20px; border: 1px solid var(--verde-menta);">
        🌟 <strong>¡Sigue así!</strong> Completa hábitos diarios para desbloquear recompensas.
    </div>
<?php endif; ?>

</div>
</div>

<script>
function editarMeta(id,t,d){
    document.getElementById("modalEditar").style.display="flex";
    document.getElementById("id_edit").value=id;
    document.getElementById("titulo_edit").value=t;
    document.getElementById("desc_edit").value=d;
}

// Cerrar modal haciendo clic fuera
document.getElementById('modalEditar').addEventListener('click', function(e) {
    if (e.target === this) {
        this.style.display = 'none';
    }
});
</script>

</body>
</html>
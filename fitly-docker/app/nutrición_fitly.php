<?php
session_start();
if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.html");
    exit();
}

$db = new SQLite3(__DIR__ . '/fitly.db');
$usuario_id = $_SESSION['usuario_id'];

// Obtener el IMC más reciente del usuario
$stmt = $db->prepare("SELECT imc FROM imc WHERE usuario_id = :u ORDER BY fecha DESC LIMIT 1");
$stmt->bindValue(':u', $usuario_id, SQLITE3_INTEGER);
$result = $stmt->execute();
$row = $result->fetchArray(SQLITE3_ASSOC);

$imc = $row ? $row['imc'] : null;

// Determinar estado y recomendación
if ($imc === null) {
    $estado = "Sin datos";
    $recomendacion = "📝 Aún no has registrado tu IMC. Ve a la sección de Progreso para calcularlo.";
    $color_estado = "#888";
} elseif ($imc < 18.5) {
    $estado = "Bajo peso";
    $recomendacion = "🥑 Necesitas aumentar tu ingesta calórica de forma saludable. Prioriza alimentos ricos en nutrientes como aguacate, frutos secos, proteínas magras y carbohidratos complejos. Consulta a un nutricionista para un plan personalizado.";
    $color_estado = "#ffa726";
} elseif ($imc < 24.9) {
    $estado = "Normal";
    $recomendacion = "🌿 ¡Felicidades! Tu IMC está en el rango saludable. Sigue manteniendo una alimentación balanceada, rica en frutas, verduras, proteínas y grasas saludables. Continúa con tus hábitos positivos.";
    $color_estado = "#7cb518";
} elseif ($imc < 29.9) {
    $estado = "Sobrepeso 🟡";
    $recomendacion = "🍽️ Te recomendamos reducir el consumo de azúcares y grasas saturadas. Aumenta tu consumo de verduras y proteínas magras. Realiza actividad física regularmente y controla las porciones.";
    $color_estado = "#f9a825";
} elseif ($imc < 34.9) {
    $estado = "Obesidad grado I 🟠";
    $recomendacion = "🔥 Es momento de hacer cambios significativos. Prioriza alimentos integrales, reduce los carbohidratos refinados y aumenta el consumo de fibra. Consulta a un profesional para un plan de alimentación estructurado.";
    $color_estado = "#fb8c00";
} else {
    $estado = "Obesidad grado II/III 🔴";
    $recomendacion = "⚠️ Te recomendamos buscar asesoría profesional. Un nutricionista puede ayudarte a establecer un plan de alimentación adecuado. Recuerda que cada pequeño cambio cuenta y tu salud es lo más importante.";
    $color_estado = "#d32f2f";
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Nutrición - FITLY</title>

<link rel="stylesheet" href="estilos.css">
<link rel="stylesheet" href="estilos2.css">

<style>
/* ===== TODOS TUS ESTILOS EXISTENTES ===== */
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
h2:first-of-type {
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

.main-fitly h2 {
    background: none;
    color: var(--verde-oliva);
    padding: 0;
    margin: 0 0 10px 0;
    box-shadow: none;
    border: none;
    font-size: 28px;
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

.dashboard-container {
    display: flex;
    min-height: calc(100vh - 70px);
}

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

.main-fitly {
    flex: 1;
    padding: 30px;
    overflow-y: auto;
}

.nutricion-container {
    padding: 30px;
}

.nutricion-container h2 {
    font-size: 32px;
    font-weight: bold;
    background: linear-gradient(135deg, var(--verde-oliva), var(--verde-claro));
    -webkit-background-clip: text;
    background-clip: text;
    color: transparent;
    margin-bottom: 10px;
    display: flex;
    align-items: center;
    gap: 10px;
}

.nutricion-container h2::before {
    content: "🥗";
    font-size: 35px;
    background: none;
    -webkit-background-clip: unset;
    background-clip: unset;
    color: var(--verde-oliva);
}

.nutricion-container > p {
    color: #667c5e;
    margin-bottom: 30px;
    font-size: 16px;
}

/* TARJETA DE RECOMENDACIÓN PERSONALIZADA */
.recomendacion-card {
    background: var(--blanco);
    border-radius: 24px;
    padding: 25px;
    margin-bottom: 30px;
    box-shadow: 0 5px 20px rgba(0,0,0,0.05);
    border: 2px solid <?= $color_estado ?>;
    transition: all 0.3s ease;
}

.recomendacion-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 15px 30px rgba(94, 93, 2, 0.1);
}

.recomendacion-card .estado-imc {
    font-size: 24px;
    font-weight: bold;
    color: <?= $color_estado ?>;
    margin-bottom: 8px;
}

.recomendacion-card .imc-valor {
    font-size: 18px;
    color: var(--texto-oscuro);
    margin-bottom: 10px;
}

.recomendacion-card .recomendacion-texto {
    font-size: 16px;
    color: #444;
    line-height: 1.6;
    background: var(--gris-suave);
    padding: 15px;
    border-radius: 16px;
    border-left: 4px solid <?= $color_estado ?>;
}

/* NUEVA TARJETA DE RECOMENDACIÓN DE COMIDAS SEGÚN IMC */
.comidas-recomendadas {
    background: var(--blanco);
    border-radius: 24px;
    padding: 25px;
    margin-bottom: 30px;
    box-shadow: 0 5px 20px rgba(0,0,0,0.05);
    border: 1px solid rgba(124, 181, 24, 0.2);
    transition: all 0.3s ease;
}

.comidas-recomendadas:hover {
    transform: translateY(-3px);
    box-shadow: 0 15px 30px rgba(94, 93, 2, 0.1);
}

.comidas-recomendadas h3 {
    color: var(--verde-oliva);
    font-size: 22px;
    margin-bottom: 15px;
    display: flex;
    align-items: center;
    gap: 10px;
    border-left: 4px solid var(--verde-claro);
    padding-left: 15px;
}

.comidas-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 15px;
    margin-top: 10px;
}

.comida-item {
    background: var(--gris-suave);
    padding: 15px;
    border-radius: 16px;
    text-align: center;
    transition: all 0.3s;
    border: 1px solid rgba(124, 181, 24, 0.1);
}

.comida-item:hover {
    transform: scale(1.03);
    border-color: var(--verde-claro);
}

.comida-item .emoji {
    font-size: 32px;
    display: block;
    margin-bottom: 8px;
}

.comida-item .nombre {
    font-weight: bold;
    color: var(--verde-oliva);
    font-size: 14px;
}

.comida-item .desc {
    font-size: 12px;
    color: #666;
    margin-top: 4px;
}

/* ESTILOS EXISTENTES PARA NUTRI-CARD */
.nutri-card {
    background: var(--blanco);
    border-radius: 24px;
    padding: 25px;
    margin-bottom: 25px;
    box-shadow: 0 5px 20px rgba(0,0,0,0.05);
    transition: all 0.3s ease;
    border: 1px solid rgba(124, 181, 24, 0.2);
}

.nutri-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 15px 30px rgba(94, 93, 2, 0.1);
    border-color: var(--verde-claro);
}

.nutri-card h3 {
    color: var(--verde-oliva);
    font-size: 22px;
    margin-bottom: 15px;
    display: flex;
    align-items: center;
    gap: 10px;
    border-left: 4px solid var(--verde-claro);
    padding-left: 15px;
}

.btn-nutri {
    background: linear-gradient(135deg, var(--verde-oliva), #4a6b0c);
    color: white;
    border: none;
    padding: 10px 20px;
    border-radius: 14px;
    margin-top: 10px;
    cursor: pointer;
    font-weight: bold;
    transition: all 0.3s ease;
    font-size: 14px;
}

.btn-nutri:hover {
    transform: scale(1.02);
    box-shadow: 0 5px 15px rgba(94, 93, 2, 0.3);
    background: linear-gradient(135deg, #6b8a0a, var(--verde-oliva));
}

.nutri-content {
    display: none;
    margin-top: 20px;
    padding-top: 15px;
    border-top: 2px dashed var(--verde-menta);
    animation: fadeIn 0.3s ease;
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(-10px); }
    to { opacity: 1; transform: translateY(0); }
}

.nutri-content ul {
    padding-left: 25px;
    margin-bottom: 15px;
}

.nutri-content li {
    margin: 8px 0;
    color: var(--texto-oscuro);
    font-size: 15px;
}

.nutri-content li::marker {
    color: var(--verde-claro);
}

.nutri-content p {
    color: #667c5e;
    margin: 10px 0;
    font-style: italic;
}

.receta {
    background: var(--gris-suave);
    padding: 15px;
    border-radius: 16px;
    margin-top: 12px;
    transition: all 0.2s;
    border-left: 3px solid var(--verde-claro);
}

.receta:hover {
    background: #edf5e5;
    transform: translateX(5px);
}

.receta b {
    color: var(--verde-oliva);
    font-size: 16px;
    display: block;
    margin-bottom: 5px;
}

.receta p {
    color: #556b4a;
    margin: 0;
    font-style: normal;
}

.nutri-img {
    width: 100%;
    max-height: 250px;
    object-fit: cover;
    border-radius: 20px;
    margin-top: 15px;
    box-shadow: 0 5px 15px rgba(0,0,0,0.1);
    transition: all 0.3s;
}

.nutri-img:hover {
    transform: scale(1.01);
    box-shadow: 0 8px 25px rgba(94, 93, 2, 0.15);
}

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
    
    h2:first-of-type {
        text-align: center;
        padding: 15px;
    }
    
    .main-fitly {
        padding: 20px;
    }
    
    .nutri-card h3 {
        font-size: 18px;
    }
    
    .comidas-grid {
        grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
    }
}
</style>

</head>

<body>

<h2>✨ FITLY ✨ Nutrición</h2>
<a href="logout.php" class="cerrar-sesion">🚪 Cerrar sesión</a>

<div class="dashboard-container">

    <!-- SIDEBAR -->
    <div class="sidebar-fitly">
        <h2>FITLY</h2>
        <a href="dashboard.php">Inicio</a>
        <a href="progreso_fitly.php">Progreso</a>
        <a href="nutrición_fitly.php" class="active">Nutrición</a>
        <a href="metas_fitly.php">Metas personales</a>
        <a href="logout.php">Cerrar sesión</a>
    </div>

    <!-- CONTENIDO -->
    <div class="main-fitly nutricion-container">

        <h2>Nutrición Inteligente</h2>
        <p>🌿 Recomendaciones personalizadas según tu IMC para mejorar tu alimentación 💚</p>

        <!-- ===== NUEVA TARJETA: RECOMENDACIÓN PERSONALIZADA ===== -->
        <div class="recomendacion-card">
            <div class="estado-imc">📊 Tu IMC: <span style="color:<?= $color_estado ?>;"><?= $imc ?? 'Sin registro' ?></span></div>
            <div class="imc-valor">Estado: <strong style="color:<?= $color_estado ?>;"><?= $estado ?></strong></div>
            <div class="recomendacion-texto">
                💡 <strong>Recomendación nutricional:</strong><br>
                <?= $recomendacion ?>
            </div>
        </div>

        <!-- ===== NUEVA TARJETA: COMIDAS RECOMENDADAS SEGÚN IMC ===== -->
        <div class="comidas-recomendadas">
            <h3>🍽️ Comidas recomendadas para ti</h3>
            <div class="comidas-grid">

            <?php
            // Definir comidas según estado de IMC
            $comidas = [];

            if ($imc === null) {
                $comidas = [
                    ["emoji" => "📝", "nombre" => "Registra tu IMC", "desc" => "Ve a Progreso para calcularlo"],
                ];
            } elseif ($imc < 18.5) {
                $comidas = [
                    ["emoji" => "🥑", "nombre" => "Aguacate", "desc" => "Grasas saludables y calorías"],
                    ["emoji" => "🥜", "nombre" => "Frutos secos", "desc" => "Alta densidad calórica"],
                    ["emoji" => "🍗", "nombre" => "Pollo", "desc" => "Proteína magra"],
                    ["emoji" => "🍚", "nombre" => "Arroz integral", "desc" => "Carbohidratos complejos"],
                    ["emoji" => "🧀", "nombre" => "Queso", "desc" => "Calcio y proteína"],
                    ["emoji" => "🥛", "nombre" => "Leche entera", "desc" => "Calorías y nutrientes"],
                ];
            } elseif ($imc < 24.9) {
                $comidas = [
                    ["emoji" => "🥗", "nombre" => "Ensalada", "desc" => "Vitaminas y fibra"],
                    ["emoji" => "🐟", "nombre" => "Pescado", "desc" => "Omega 3"],
                    ["emoji" => "🍎", "nombre" => "Manzana", "desc" => "Fibra y antioxidantes"],
                    ["emoji" => "🥬", "nombre" => "Verduras", "desc" => "Nutrientes esenciales"],
                    ["emoji" => "🥑", "nombre" => "Aguacate", "desc" => "Grasas saludables"],
                    ["emoji" => "🫘", "nombre" => "Legumbres", "desc" => "Proteína vegetal"],
                ];
            } elseif ($imc < 29.9) {
                $comidas = [
                    ["emoji" => "🥬", "nombre" => "Verduras", "desc" => "Bajo en calorías"],
                    ["emoji" => "🐟", "nombre" => "Pescado", "desc" => "Proteína magra"],
                    ["emoji" => "🫘", "nombre" => "Legumbres", "desc" => "Saciedad y fibra"],
                    ["emoji" => "🍚", "nombre" => "Quinoa", "desc" => "Proteína completa"],
                    ["emoji" => "🥑", "nombre" => "Aguacate", "desc" => "Grasas saludables"],
                    ["emoji" => "🍋", "nombre" => "Limón", "desc" => "Desintoxicante"],
                ];
            } elseif ($imc < 34.9) {
                $comidas = [
                    ["emoji" => "🥬", "nombre" => "Espinacas", "desc" => "Bajo en calorías"],
                    ["emoji" => "🐟", "nombre" => "Salmón", "desc" => "Omega 3"],
                    ["emoji" => "🫘", "nombre" => "Frijoles", "desc" => "Fibra y proteína"],
                    ["emoji" => "🍚", "nombre" => "Arroz integral", "desc" => "Carbohidrato complejo"],
                    ["emoji" => "🥦", "nombre" => "Brócoli", "desc" => "Vitaminas"],
                    ["emoji" => "🥗", "nombre" => "Ensalada", "desc" => "Saciedad"],
                ];
            } else {
                $comidas = [
                    ["emoji" => "🥬", "nombre" => "Verduras", "desc" => "Prioritarias en cada comida"],
                    ["emoji" => "🐟", "nombre" => "Pescado", "desc" => "Proteína magra"],
                    ["emoji" => "🫘", "nombre" => "Legumbres", "desc" => "Fuente de fibra"],
                    ["emoji" => "🥑", "nombre" => "Aguacate", "desc" => "Grasas saludables (moderado)"],
                    ["emoji" => "🍋", "nombre" => "Limón", "desc" => "Ayuda a la digestión"],
                    ["emoji" => "🌿", "nombre" => "Té verde", "desc" => "Antioxidante"],
                ];
            }

            foreach($comidas as $c):
            ?>
                <div class="comida-item">
                    <span class="emoji"><?= $c['emoji'] ?></span>
                    <span class="nombre"><?= $c['nombre'] ?></span>
                    <span class="desc"><?= $c['desc'] ?></span>
                </div>
            <?php endforeach; ?>

            </div>
        </div>

        <!-- ALIMENTACIÓN BALANCEADA -->
        <div class="nutri-card">
            <h3>🥑 Alimentación Balanceada</h3>
            <button class="btn-nutri" onclick="toggleNutri('bal')">📖 Ver más</button>

            <div id="bal" class="nutri-content">
                <ul>
                    <li>🍗 Proteínas: pollo, huevo, pescado, legumbres</li>
                    <li>🌾 Carbohidratos: arroz integral, avena, papa, quinoa</li>
                    <li>🥑 Grasas saludables: aguacate, nueces, aceite de oliva</li>
                </ul>

                <p>💡 <b>Tip:</b> combina siempre los 3 grupos para obtener energía y nutrición balanceada.</p>

                <img class="nutri-img" src="https://images.unsplash.com/photo-1490645935967-10de6ba17061" alt="Comida saludable">
            </div>
        </div>

        <!-- PORCIONES -->
        <div class="nutri-card">
            <h3>🍽️ Porciones y combinaciones</h3>
            <button class="btn-nutri" onclick="toggleNutri('porciones')">📖 Ver más</button>

            <div id="porciones" class="nutri-content">
                <ul>
                    <li>🥬 50% verduras y vegetales</li>
                    <li>🍗 25% proteína magra</li>
                    <li>🌾 25% carbohidratos complejos</li>
                </ul>

                <p>💡 El plato balanceado es la clave. Evita excesos y escucha las señales de tu cuerpo.</p>

                <img class="nutri-img" src="https://images.unsplash.com/photo-1546069901-eacef0df6022" alt="Plato balanceado">
            </div>
        </div>

        <!-- HIDRATACIÓN -->
        <div class="nutri-card">
            <h3>💧 Hidratación Inteligente</h3>
            <button class="btn-nutri" onclick="toggleNutri('agua')">📖 Ver más</button>

            <div id="agua" class="nutri-content">
                <ul>
                    <li>🚰 2 a 2.5 litros de agua al día</li>
                    <li>🏃‍♂️ Más si haces ejercicio (añade 500ml por hora)</li>
                    <li>🥤 Evita refrescos azucarados y jugos procesados</li>
                </ul>

                <p>💡 <b>Tip rápido:</b> toma un vaso de agua al despertar para activar tu metabolismo 💚</p>

                <img class="nutri-img" src="https://images.unsplash.com/photo-1502741338009-cac2772e18bc" alt="Agua y hidratación">
            </div>
        </div>

        <!-- RECETAS -->
        <div class="nutri-card">
            <h3>🥗 Recetas Saludables</h3>
            <button class="btn-nutri" onclick="toggleNutri('recetas')">👨‍🍳 Ver recetas</button>

            <div id="recetas" class="nutri-content">

                <div class="receta">
                    <b>🥗 Ensalada FITLY</b>
                    <p>Lechuga fresca + pollo a la plancha + aguacate + limón y un toque de sal</p>
                </div>

                <div class="receta">
                    <b>🌾 Avena energética</b>
                    <p>Avena cocida + plátano en rodajas + miel + nueces picadas</p>
                </div>

                <div class="receta">
                    <b>🍳 Desayuno rápido</b>
                    <p>Pan integral + huevo revuelto + aguacate + fruta de temporada</p>
                </div>

                <div class="receta">
                    <b>🥤 Smoothie verde</b>
                    <p>Espinacas + piña + jengibre + agua de coco - ¡refrescante y nutritivo!</p>
                </div>

            </div>
        </div>

    </div>

</div>

<script>
function toggleNutri(id) {
    let section = document.getElementById(id);

    if (section.style.display === "block") {
        section.style.display = "none";
    } else {
        section.style.display = "block";
    }
}
</script>

</body>
</html>
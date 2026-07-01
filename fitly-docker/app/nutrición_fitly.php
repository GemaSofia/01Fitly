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

/* ===== TARJETA DE COMIDAS RECOMENDADAS ===== */
.comidas-recomendadas {
    background: var(--blanco);
    border-radius: 24px;
    padding: 30px;
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
    font-size: 24px;
    margin-bottom: 20px;
    display: flex;
    align-items: center;
    gap: 12px;
    border-left: 4px solid var(--verde-claro);
    padding-left: 18px;
}

/* Grid de comidas */
.comidas-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 25px;
}

/* ===== ESTILO COMO EN LA IMAGEN (Recetas paso a paso) ===== */
.comida-item {
    background: var(--blanco);
    border-radius: 20px;
    overflow: hidden;
    transition: all 0.3s ease;
    border: 2px solid #e8f0e0;
    cursor: pointer;
    box-shadow: 0 2px 8px rgba(0,0,0,0.04);
}

.comida-item:hover {
    transform: translateY(-6px);
    box-shadow: 0 12px 30px rgba(94, 93, 2, 0.12);
    border-color: var(--verde-claro);
}

/* Imagen grande como en la imagen */
.comida-img {
    width: 100%;
    height: 220px;
    overflow: hidden;
    background: #f0f5eb;
}

.comida-img img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: 0.5s ease;
}

.comida-item:hover .comida-img img {
    transform: scale(1.08);
}

/* Información debajo de la imagen */
.comida-info {
    padding: 18px 20px 14px 20px;
    text-align: left;
}

.comida-info .nombre {
    font-weight: 700;
    color: var(--verde-oliva);
    font-size: 19px;
    display: block;
}

.comida-info .desc {
    font-size: 14px;
    color: #6f7d67;
    display: block;
    margin-top: 2px;
}

.comida-info .click-hint {
    font-size: 13px;
    color: var(--verde-claro);
    display: block;
    margin-top: 6px;
    font-weight: 500;
}

/* ===== RECETAS DESPLEGABLES (ESTILO PASO A PASO COMO EN LA IMAGEN) ===== */
.recetas-ocultas {
    display: none;
    padding: 0 20px 20px 20px;
    background: var(--gris-suave);
    border-top: 2px dashed var(--verde-menta);
    animation: slideDown 0.4s ease;
}

@keyframes slideDown {
    from {
        opacity: 0;
        transform: translateY(-15px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.receta-item {
    background: var(--blanco);
    border-radius: 16px;
    padding: 18px 20px;
    margin-top: 14px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.04);
    border-left: 4px solid var(--verde-claro);
    transition: 0.2s;
}

.receta-item:hover {
    transform: translateX(5px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.08);
}

.receta-item:first-child {
    margin-top: 18px;
}

.receta-item .receta-titulo {
    font-weight: 700;
    color: var(--verde-oliva);
    font-size: 17px;
    display: block;
    margin-bottom: 6px;
}

.receta-item .receta-detalle {
    font-size: 14px;
    color: #44553a;
    line-height: 1.8;
    margin: 0;
}

/* Pequeño indicador de "paso a paso" */
.receta-item .paso-label {
    display: inline-block;
    font-size: 11px;
    background: var(--verde-claro);
    color: white;
    padding: 2px 10px;
    border-radius: 20px;
    margin-bottom: 6px;
    font-weight: 600;
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
    
    .comidas-grid {
        grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
        gap: 15px;
    }
    
    .comida-img {
        height: 160px;
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

        <!-- ===== TARJETA: RECOMENDACIÓN PERSONALIZADA ===== -->
        <div class="recomendacion-card">
            <div class="estado-imc">📊 Tu IMC: <span style="color:<?= $color_estado ?>;"><?= $imc ?? 'Sin registro' ?></span></div>
            <div class="imc-valor">Estado: <strong style="color:<?= $color_estado ?>;"><?= $estado ?></strong></div>
            <div class="recomendacion-texto">
                💡 <strong>Recomendación nutricional:</strong><br>
                <?= $recomendacion ?>
            </div>
        </div>

        <!-- ===== TARJETA: COMIDAS RECOMENDADAS ===== -->
        <div class="comidas-recomendadas">
            <h3>🍽️ Comidas recomendadas para ti</h3>
            <div class="comidas-grid">

            <?php
            // ============================================================
            // 📌 AQUÍ PUEDES CAMBIAR LAS IMÁGENES
            // ============================================================
            // Las imágenes están en la carpeta: img/comidas/
            // Ejemplo: "img/comidas/aguacate.jpg"
            // ============================================================

            $comidas = [];

            if ($imc === null) {
                $comidas = [
                    [
                        "nombre" => "Registra tu IMC",
                        "desc" => "Ve a Progreso para calcularlo",
                        "img" => "img/comidas/registro.jpg",
                        "recetas" => []
                    ],
                ];
            } elseif ($imc < 18.5) {
                $comidas = [
                    [
                        "nombre" => "Aguacate",
                        "desc" => "Grasas saludables y calorías",
                        "img" => "img/comidas/aguacate.jpg",
                        "recetas" => [
                            ["titulo" => "🥑 Tostada de aguacate con huevo", "detalle" => "1. Tuesta una rebanada de pan integral.\n2. Machaca medio aguacate con limón y sal.\n3. Coloca el aguacate sobre el pan.\n4. Añade un huevo pochado o frito.\n5. ¡Disfruta!"],
                            ["titulo" => "🥑 Guacamole casero", "detalle" => "1. Machaca 2 aguacates maduros.\n2. Agrega jugo de limón, cebolla picada, tomate, cilantro y sal.\n3. Mezcla todos los ingredientes.\n4. Sirve con totopos de maíz o tiras de zanahoria y pepino."],
                            ["titulo" => "🥑 Ensalada de aguacate y pollo", "detalle" => "1. Desmenuza pechuga de pollo cocida.\n2. Corta aguacate en cubos.\n3. Mezcla con lechuga, tomate cherry.\n4. Adereza con aceite de oliva y limón."]
                        ]
                    ],
                    [
                        "nombre" => "Frutos secos",
                        "desc" => "Alta densidad calórica",
                        "img" => "img/comidas/frutos-secos.jpg",
                        "recetas" => [
                            ["titulo" => "🥜 Mix de nueces y almendras", "detalle" => "1. Mezcla nueces, almendras, avellanas y pasas.\n2. Toma un puñado como snack entre comidas.\n3. Ideal para aumentar tu ingesta calórica de forma saludable."],
                            ["titulo" => "🥜 Leche de almendras casera", "detalle" => "1. Remoja 1 taza de almendras en agua por 8 horas.\n2. Licúa con 3 tazas de agua.\n3. Cuela la mezcla.\n4. Endulza con miel o dátiles al gusto."]
                        ]
                    ],
                    [
                        "nombre" => "Pollo",
                        "desc" => "Proteína magra",
                        "img" => "img/comidas/pollo.jpg",
                        "recetas" => [
                            ["titulo" => "🍗 Pechuga a la plancha", "detalle" => "1. Adoba la pechuga con ajo, limón, sal y pimienta.\n2. Cocina a la plancha por 5-7 minutos por lado.\n3. Sirve con arroz integral y verduras al vapor."],
                            ["titulo" => "🍗 Pollo al horno con verduras", "detalle" => "1. Coloca piezas de pollo en una bandeja.\n2. Añade papas, zanahorias y cebolla.\n3. Hornea a 200°C por 40 minutos.\n4. Baña con jugo de limón y hierbas."]
                        ]
                    ],
                    [
                        "nombre" => "Arroz integral",
                        "desc" => "Carbohidratos complejos",
                        "img" => "img/comidas/arroz-integral.jpg",
                        "recetas" => [
                            ["titulo" => "🍚 Arroz integral con verduras", "detalle" => "1. Sofríe cebolla, zanahoria y pimiento.\n2. Añade arroz integral cocido.\n3. Saltea y agrega salsa de soja.\n4. Decora con cilantro fresco."],
                            ["titulo" => "🍚 Bowl de arroz y pollo", "detalle" => "1. Coloca una base de arroz integral.\n2. Añade tiras de pollo a la plancha.\n3. Agrega aguacate y edamame.\n4. Adereza con aceite de sésamo y limón."]
                        ]
                    ]
                ];
            } elseif ($imc < 24.9) {
                $comidas = [
                    [
                        "nombre" => "Ensalada",
                        "desc" => "Vitaminas y fibra",
                        "img" => "img/comidas/ensalada.jpg",
                        "recetas" => [
                            ["titulo" => "🥗 Ensalada César", "detalle" => "1. Mezcla lechuga romana, pollo a la plancha, crutones y queso parmesano.\n2. Prepara salsa César con yogur, ajo, anchoas, limón y mostaza.\n3. Adereza y sirve."],
                            ["titulo" => "🥗 Ensalada de quinoa", "detalle" => "1. Cocina la quinoa y deja enfriar.\n2. Mezcla con pepino, tomate, perejil y cebolla roja.\n3. Aliña con aceite de oliva y limón."]
                        ]
                    ],
                    [
                        "nombre" => "Pescado",
                        "desc" => "Omega 3",
                        "img" => "img/comidas/pescado.jpg",
                        "recetas" => [
                            ["titulo" => "🐟 Salmón al horno", "detalle" => "1. Coloca el salmón en una bandeja con rodajas de limón, eneldo y sal.\n2. Hornea a 180°C por 15-20 minutos.\n3. Sirve con espárragos al vapor."],
                            ["titulo" => "🐟 Pescado a la plancha", "detalle" => "1. Marina el pescado con ajo, perejil y limón.\n2. Cocina por 3-4 minutos cada lado.\n3. Acompaña con ensalada fresca."]
                        ]
                    ],
                    [
                        "nombre" => "Manzana",
                        "desc" => "Fibra y antioxidantes",
                        "img" => "img/comidas/manzana.jpg",
                        "recetas" => [
                            ["titulo" => "🍎 Manzana con canela", "detalle" => "1. Corta una manzana en gajos.\n2. Espolvorea canela.\n3. Hornea por 10 minutos.\n4. Sirve caliente."],
                            ["titulo" => "🍎 Compota de manzana", "detalle" => "1. Pela y corta manzanas.\n2. Cocina con un poco de agua y canela.\n3. Tritura hasta obtener una textura suave.\n4. Sirve caliente o fría."]
                        ]
                    ],
                    [
                        "nombre" => "Verduras",
                        "desc" => "Nutrientes esenciales",
                        "img" => "img/comidas/verduras.jpg",
                        "recetas" => [
                            ["titulo" => "🥬 Verduras al vapor", "detalle" => "1. Cocina brócoli, zanahoria y calabaza al vapor por 10 minutos.\n2. Rocía con aceite de oliva y sal.\n3. Sirve como acompañamiento."],
                            ["titulo" => "🥬 Salteado de verduras", "detalle" => "1. Saltea pimiento, cebolla, zanahoria y champiñones.\n2. Añade salsa de soja y jengibre.\n3. Cocina por 5 minutos."]
                        ]
                    ]
                ];
            } elseif ($imc < 29.9) {
                $comidas = [
                    [
                        "nombre" => "Verduras",
                        "desc" => "Bajo en calorías",
                        "img" => "img/comidas/verduras.jpg",
                        "recetas" => [
                            ["titulo" => "🥬 Verduras al vapor", "detalle" => "1. Cocina brócoli, espárragos y zanahoria al vapor.\n2. Añade un toque de sal y limón."],
                            ["titulo" => "🥬 Crema de verduras", "detalle" => "1. Cocina calabaza, zanahoria y apio.\n2. Licua con un poco de caldo de verduras.\n3. Sirve caliente."]
                        ]
                    ],
                    [
                        "nombre" => "Pescado",
                        "desc" => "Proteína magra",
                        "img" => "img/comidas/pescado.jpg",
                        "recetas" => [
                            ["titulo" => "🐟 Salmón a la plancha", "detalle" => "1. Cocina el salmón con piel por 4-5 minutos por lado.\n2. Sirve con espinacas salteadas."],
                            ["titulo" => "🐟 Pescado con verduras", "detalle" => "1. Hornea el pescado con tomate, cebolla y pimiento.\n2. Añade hierbas provenzales.\n3. Hornea por 20 minutos."]
                        ]
                    ],
                    [
                        "nombre" => "Legumbres",
                        "desc" => "Saciedad y fibra",
                        "img" => "img/comidas/legumbres.jpg",
                        "recetas" => [
                            ["titulo" => "🫘 Lentejas guisadas", "detalle" => "1. Sofríe cebolla, zanahoria y apio.\n2. Añade lentejas, agua y laurel.\n3. Cocina hasta que estén tiernas."],
                            ["titulo" => "🫘 Garbanzos con espinacas", "detalle" => "1. Saltea garbanzos cocidos con espinacas, ajo y comino.\n2. Cocina por 5 minutos.\n3. Sirve caliente."]
                        ]
                    ],
                    [
                        "nombre" => "Quinoa",
                        "desc" => "Proteína completa",
                        "img" => "img/comidas/quinoa.jpg",
                        "recetas" => [
                            ["titulo" => "🍚 Quinoa con verduras", "detalle" => "1. Cocina quinoa.\n2. Mézclala con pimientos, zanahoria, cebolla y perejil.\n3. Aliña con aceite de oliva y limón."],
                            ["titulo" => "🍚 Ensalada de quinoa", "detalle" => "1. Mezcla quinoa fría con tomate cherry, pepino, aceitunas y queso feta.\n2. Adereza con vinagreta."]
                        ]
                    ]
                ];
            } elseif ($imc < 34.9) {
                $comidas = [
                    [
                        "nombre" => "Espinacas",
                        "desc" => "Bajo en calorías",
                        "img" => "img/comidas/espinacas.jpg",
                        "recetas" => [
                            ["titulo" => "🥬 Espinacas salteadas", "detalle" => "1. Saltea espinacas con ajo en una sartén.\n2. Cocina hasta que reduzcan.\n3. Añade sal y un chorrito de limón."],
                            ["titulo" => "🥬 Ensalada de espinacas", "detalle" => "1. Mezcla espinacas frescas con fresas, nueces y queso feta.\n2. Adereza con vinagreta balsámica."]
                        ]
                    ],
                    [
                        "nombre" => "Salmón",
                        "desc" => "Omega 3",
                        "img" => "img/comidas/salmon.jpg",
                        "recetas" => [
                            ["titulo" => "🐟 Salmón a la plancha", "detalle" => "1. Cocina el salmón con piel por 4-5 minutos por lado.\n2. Sirve con espinacas salteadas."],
                            ["titulo" => "🐟 Salmón con verduras", "detalle" => "1. Hornea salmón con espárragos y tomates cherry.\n2. Rocía con aceite de oliva y hierbas."]
                        ]
                    ],
                    [
                        "nombre" => "Frijoles",
                        "desc" => "Fibra y proteína",
                        "img" => "img/comidas/frijoles.jpg",
                        "recetas" => [
                            ["titulo" => "🫘 Frijoles negros con arroz", "detalle" => "1. Sirve frijoles negros cocidos sobre arroz integral.\n2. Añade cebolla, cilantro y limón."],
                            ["titulo" => "🫘 Sopa de frijoles", "detalle" => "1. Cocina frijoles con cebolla, ajo, comino y una pizca de chile.\n2. Licúa parcialmente para darle cremosidad."]
                        ]
                    ],
                    [
                        "nombre" => "Brócoli",
                        "desc" => "Vitaminas",
                        "img" => "img/comidas/brocoli.jpg",
                        "recetas" => [
                            ["titulo" => "🥦 Brócoli al vapor", "detalle" => "1. Cocina brócoli al vapor por 8-10 minutos.\n2. Sirve con sal, limón y aceite de oliva."],
                            ["titulo" => "🥦 Brócoli salteado", "detalle" => "1. Saltea brócoli con ajo y champiñones.\n2. Añade un toque de salsa de soja."]
                        ]
                    ]
                ];
            } else {
                $comidas = [
                    [
                        "nombre" => "Verduras",
                        "desc" => "Prioritarias en cada comida",
                        "img" => "img/comidas/verduras.jpg",
                        "recetas" => [
                            ["titulo" => "🥬 Verduras al vapor", "detalle" => "1. Cocina brócoli, coliflor y zanahoria al vapor.\n2. Añade limón y sal."],
                            ["titulo" => "🥬 Salteado de verduras", "detalle" => "1. Saltea pimiento, calabacín, cebolla y champiñones.\n2. Añade salsa de soja baja en sodio."]
                        ]
                    ],
                    [
                        "nombre" => "Pescado",
                        "desc" => "Proteína magra",
                        "img" => "img/comidas/pescado.jpg",
                        "recetas" => [
                            ["titulo" => "🐟 Pescado a la plancha", "detalle" => "1. Cocina el pescado con limón y hierbas.\n2. Acompaña con ensalada de verduras."],
                            ["titulo" => "🐟 Pescado al horno", "detalle" => "1. Hornea pescado con tomate, cebolla y pimiento.\n2. Sirve con verduras asadas."]
                        ]
                    ],
                    [
                        "nombre" => "Legumbres",
                        "desc" => "Fuente de fibra",
                        "img" => "img/comidas/legumbres.jpg",
                        "recetas" => [
                            ["titulo" => "🫘 Lentejas guisadas", "detalle" => "1. Cocina lentejas con cebolla, zanahoria y apio.\n2. Añade comino y laurel."],
                            ["titulo" => "🫘 Garbanzos con espinacas", "detalle" => "1. Saltea garbanzos cocidos con espinacas, ajo y comino.\n2. Acompaña con arroz integral."]
                        ]
                    ],
                    [
                        "nombre" => "Limón",
                        "desc" => "Ayuda a la digestión",
                        "img" => "img/comidas/limon.jpg",
                        "recetas" => [
                            ["titulo" => "🍋 Agua de limón", "detalle" => "1. Exprime medio limón en un vaso con agua tibia.\n2. Bebe en ayunas para activar tu metabolismo."],
                            ["titulo" => "🍋 Limonada natural", "detalle" => "1. Mezcla jugo de limón con agua.\n2. Endulza con miel o stevia.\n3. Sirve con hielo y menta."]
                        ]
                    ]
                ];
            }

            foreach($comidas as $c):
            ?>
                <div class="comida-item" onclick="toggleRecetas(this)">
                    <div class="comida-img">
                        <img src="<?= $c['img'] ?>" alt="<?= $c['nombre'] ?>" loading="lazy">
                    </div>
                    <div class="comida-info">
                        <span class="nombre"><?= $c['nombre'] ?></span>
                        <span class="desc"><?= $c['desc'] ?></span>
                        <span class="click-hint">👆 Haz clic para ver recetas paso a paso</span>
                    </div>
                    <?php if(!empty($c['recetas'])): ?>
                        <div class="recetas-ocultas">
                            <?php foreach($c['recetas'] as $receta): ?>
                                <div class="receta-item">
                                    <span class="receta-titulo"><?= $receta['titulo'] ?></span>
                                    <p class="receta-detalle"><?= nl2br($receta['detalle']) ?></p>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>

            </div>
        </div>

    </div>

</div>



<script>
// Función para desplegar/ocultar recetas al hacer clic en una comida
function toggleRecetas(element) {
    const recetasDiv = element.querySelector('.recetas-ocultas');
    if (recetasDiv) {
        if (recetasDiv.style.display === 'none' || recetasDiv.style.display === '') {
            recetasDiv.style.display = 'block';
        } else {
            recetasDiv.style.display = 'none';
        }
    }
}
</script>

</body>
</html>
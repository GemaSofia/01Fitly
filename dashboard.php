<?php
session_start();
if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.html");
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>FITLY Dashboard</title>

<link rel="stylesheet" href="estilos.css">
<link rel="stylesheet" href="estilos2.css">

<style>
/* ===== PALETA CLARA + VERDES SUTILES ===== */
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
h2 {
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

.bienvenida-fitly {
    background: linear-gradient(135deg, #7cb518, #5e5d02);
    color: white;
    padding: 25px 30px;
    border-radius: 24px;
    font-size: 28px;
    font-weight: bold;
    margin-bottom: 30px;
    text-align: center;
    box-shadow: 0 8px 20px rgba(124, 181, 24, 0.2);
    position: relative;
    overflow: hidden;
}

.bienvenida-fitly::before {
    content: "💪";
    font-size: 50px;
    position: absolute;
    right: 20px;
    top: 50%;
    transform: translateY(-50%);
    opacity: 0.15;
}

.bienvenida-fitly::after {
    content: "🌟";
    font-size: 40px;
    position: absolute;
    left: 20px;
    top: 50%;
    transform: translateY(-50%);
    opacity: 0.15;
}

/* Grid de cards */
.cards-fitly {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
    gap: 25px;
}

.card-fitly {
    background: var(--blanco);
    border-radius: 24px;
    padding: 25px;
    box-shadow: 0 5px 20px rgba(0,0,0,0.05);
    transition: all 0.3s ease;
    border: 1px solid rgba(124, 181, 24, 0.2);
}

.card-fitly:hover {
    transform: translateY(-5px);
    box-shadow: 0 15px 30px rgba(94, 93, 2, 0.1);
    border-color: var(--verde-claro);
}

.card-fitly h3 {
    color: var(--verde-oliva);
    font-size: 20px;
    margin-bottom: 18px;
    display: flex;
    align-items: center;
    gap: 10px;
    border-left: 4px solid var(--verde-claro);
    padding-left: 15px;
}

/* Inputs */
.imc-input {
    width: 100%;
    padding: 12px 15px;
    margin: 8px 0;
    border: 2px solid #e0e8d8;
    border-radius: 14px;
    font-size: 14px;
    transition: all 0.3s;
    background: #fefefe;
}

.imc-input:focus {
    outline: none;
    border-color: var(--verde-claro);
    box-shadow: 0 0 0 3px rgba(124, 181, 24, 0.1);
}

.btn-fitly {
    background: linear-gradient(135deg, var(--verde-oliva), #4a6b0c);
    color: white;
    border: none;
    padding: 12px 20px;
    border-radius: 14px;
    font-weight: bold;
    cursor: pointer;
    transition: all 0.3s;
    width: 100%;
    margin-top: 10px;
    font-size: 14px;
}

.btn-fitly:hover {
    transform: scale(1.02);
    box-shadow: 0 5px 15px rgba(94, 93, 2, 0.3);
    background: linear-gradient(135deg, #6b8a0a, var(--verde-oliva));
}

/* Resultado IMC */
#resultadoIMC {
    font-size: 24px;
    font-weight: bold;
    text-align: center;
    margin: 15px 0 5px;
    color: var(--verde-oliva);
}

.estado-imc {
    text-align: center;
    padding: 8px;
    border-radius: 20px;
    font-weight: bold;
}

/* Racha */
.racha-box {
    text-align: center;
    background: linear-gradient(135deg, #fafff2, var(--blanco));
}

.racha-num {
    font-size: 64px;
    font-weight: bold;
    background: linear-gradient(135deg, var(--verde-oliva), var(--verde-claro));
    -webkit-background-clip: text;
    background-clip: text;
    color: transparent;
    margin: 15px 0;
}

/* Frase motivacional */
.frase-dia {
    font-size: 17px;
    line-height: 1.5;
    color: #44553a;
    font-style: italic;
    background: var(--gris-suave);
    padding: 16px;
    border-radius: 18px;
    border-left: 4px solid var(--verde-claro);
}

/* Logros */
#lista-logros {
    margin-top: 15px;
}

.logro-item {
    padding: 12px 15px;
    border-radius: 14px;
    margin-bottom: 10px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    transition: all 0.3s;
}

.logro-lock {
    background: #f0f2ec;
    opacity: 0.7;
    color: #88957d;
}

.logro-ok {
    background: linear-gradient(90deg, #e8f5e6, #d4ecd0);
    border-left: 5px solid var(--verde-claro);
    font-weight: bold;
    color: #2d5a1e;
}

/* Botón historial */
.btn-historial {
    background: var(--verde-claro);
    color: white;
    padding: 10px 16px;
    border-radius: 14px;
    border: none;
    cursor: pointer;
    margin-top: 15px;
    font-weight: bold;
    transition: 0.3s;
    width: 100%;
}

.btn-historial:hover {
    background: var(--verde-oliva);
    transform: scale(1.02);
}

#historialIMC {
    margin-top: 15px;
    display: none;
    background: var(--gris-suave);
    border-radius: 18px;
    padding: 12px;
}

.historial-list li {
    background: white;
    padding: 10px 12px;
    border-radius: 12px;
    margin-bottom: 8px;
    list-style: none;
    border-left: 3px solid var(--verde-claro);
    font-size: 14px;
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
    
    .cards-fitly {
        grid-template-columns: 1fr;
    }
    
    .cerrar-sesion {
        position: static;
        display: inline-block;
        margin: 15px;
    }
    
    h2 {
        text-align: center;
        padding: 15px;
    }
}
</style>

</head>

<body>

<h2> FITLY  Bienvenido, <?php echo $_SESSION['usuario_nombre']; ?> 👋</h2>
<a href="logout.php" class="cerrar-sesion">🚪 Cerrar sesión</a>

<div class="dashboard-container">

<div class="sidebar-fitly">
    <h2>FITLY</h2>
    <a href="dashboard.php">Inicio</a>
    <a href="progreso_fitly.php">Progreso</a>
    <a href="nutrición_fitly.html">Nutrición</a>
    <a href="metas_fitly.php">Metas personales</a>
    <a href="logout.php">Cerrar sesión</a>
</div>

<div class="main-fitly">

    <div class="bienvenida-fitly">
        Tu bienestar empieza hoy 🌱
    </div>

    <div class="cards-fitly">

        <div class="card-fitly">
            <h3>📊 IMC Rápido</h3>
            <input class="imc-input" id="peso" placeholder="⚖️ Peso (kg)">
            <input class="imc-input" id="altura" placeholder="📏 Estatura (m)">
            <button class="btn-fitly" onclick="calcularIMC()">Calcular IMC</button>
            <p id="resultadoIMC"></p>
            <p id="estadoIMC" class="estado-imc"></p>

            <button id="btnHistorial" class="btn-historial">📜 Ver historial IMC</button>
            <div id="historialIMC"></div>
        </div>

        <div class="card-fitly racha-box">
            <h3>🔥 Racha Saludable</h3>
            <div class="racha-num" id="racha">0</div>
            <button class="btn-fitly" onclick="sumarRacha()">✅ ¡Hoy cumplí!</button>
        </div>

        <div class="card-fitly">
            <h3>💭 Motivación diaria</h3>
            <p class="frase-dia" id="frase">Cargando...</p>
        </div>

        <div class="card-fitly">
            <h3>🏆 Logros</h3>
            <div id="lista-logros"></div>
        </div>

    </div>

</div>

</div>

<script src="dashboard.js"></script>

</body>
</html>
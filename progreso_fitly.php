<?php
session_start();
if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.html");
    exit();
}

$db = new SQLite3(__DIR__ . '/fitly.db');
$usuario_id = $_SESSION['usuario_id'];

$stmt = $db->prepare("SELECT * FROM imc WHERE usuario_id = :u ORDER BY fecha ASC");
$stmt->bindValue(':u', $usuario_id, SQLITE3_INTEGER);
$res = $stmt->execute();

$fechas = [];
$valores = [];

while ($row = $res->fetchArray(SQLITE3_ASSOC)) {
    $fechas[] = $row['fecha'];
    $valores[] = $row['imc'];
}

$promedio = count($valores) ? round(array_sum($valores)/count($valores),2) : 0;
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Progreso Fitly</title>

<link rel="stylesheet" href="estilos.css">
<link rel="stylesheet" href="estilos2.css">

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<style>
/* ===== MISMO ESTILO QUE DASHBOARD ===== */
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

.titulo {
    font-size: 32px;
    font-weight: bold;
    background: linear-gradient(135deg, var(--verde-oliva), var(--verde-claro));
    -webkit-background-clip: text;
    background-clip: text;
    color: transparent;
    margin-bottom: 25px;
    display: flex;
    align-items: center;
    gap: 10px;
}

.titulo::before {
    content: "📈";
    font-size: 35px;
    background: none;
    -webkit-background-clip: unset;
    background-clip: unset;
    color: var(--verde-oliva);
}

.grid-progreso {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 25px;
    margin-bottom: 30px;
}

.card-progreso {
    background: var(--blanco);
    border-radius: 24px;
    padding: 25px;
    box-shadow: 0 5px 20px rgba(0,0,0,0.05);
    transition: all 0.3s ease;
    border: 1px solid rgba(124, 181, 24, 0.2);
}

.card-progreso:hover {
    transform: translateY(-5px);
    box-shadow: 0 15px 30px rgba(94, 93, 2, 0.1);
    border-color: var(--verde-claro);
}

.card-progreso h3 {
    color: var(--verde-oliva);
    font-size: 18px;
    margin-bottom: 15px;
    display: flex;
    align-items: center;
    gap: 8px;
    border-left: 3px solid var(--verde-claro);
    padding-left: 12px;
}

.numero {
    font-size: 42px;
    font-weight: bold;
    background: linear-gradient(135deg, var(--verde-oliva), var(--verde-claro));
    -webkit-background-clip: text;
    background-clip: text;
    color: transparent;
    margin: 10px 0;
}

.barra {
    width: 100%;
    height: 12px;
    background: #e8f0e0;
    border-radius: 20px;
    margin-top: 15px;
    overflow: hidden;
}

.barra-progreso {
    height: 12px;
    background: linear-gradient(90deg, var(--verde-oliva), var(--verde-claro));
    border-radius: 20px;
    transition: width 0.5s ease;
}

.mensaje {
    padding: 16px;
    border-radius: 16px;
    background: var(--gris-suave);
    color: var(--texto-oscuro);
    font-weight: 500;
    text-align: center;
    border-left: 4px solid var(--verde-claro);
}

.lista {
    max-height: 200px;
    overflow-y: auto;
    list-style: none;
}

.lista li {
    padding: 10px 12px;
    border-bottom: 1px solid #e8e8e8;
    font-size: 14px;
    color: #44553a;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.lista li:last-child {
    border-bottom: none;
}

.lista li::before {
    content: "📅";
    margin-right: 8px;
    opacity: 0.7;
}

/* Contenedor de la gráfica */
.chart-container {
    background: var(--blanco);
    border-radius: 24px;
    padding: 25px;
    box-shadow: 0 5px 20px rgba(0,0,0,0.05);
    border: 1px solid rgba(124, 181, 24, 0.2);
    transition: all 0.3s ease;
    margin-top: 25px;
}

.chart-container:hover {
    transform: translateY(-3px);
    box-shadow: 0 15px 30px rgba(94, 93, 2, 0.1);
    border-color: var(--verde-claro);
}

.chart-container h3 {
    color: var(--verde-oliva);
    font-size: 20px;
    margin-bottom: 20px;
    display: flex;
    align-items: center;
    gap: 10px;
    border-left: 4px solid var(--verde-claro);
    padding-left: 15px;
}

.chart-container h3::before {
    content: "📊";
    font-size: 22px;
}

canvas {
    max-height: 400px;
    width: 100%;
}

/* Scroll personalizado para la lista */
.lista::-webkit-scrollbar {
    width: 6px;
}

.lista::-webkit-scrollbar-track {
    background: #e8f0e0;
    border-radius: 10px;
}

.lista::-webkit-scrollbar-thumb {
    background: var(--verde-claro);
    border-radius: 10px;
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
    
    h2 {
        text-align: center;
        padding: 15px;
    }
    
    .main-fitly {
        padding: 20px;
    }
    
    .titulo {
        font-size: 26px;
    }
}
</style>
</head>

<body>

<h2> FITLY  Progreso</h2>
<a href="logout.php" class="cerrar-sesion">🚪 Cerrar sesión</a>

<div class="dashboard-container">

<div class="sidebar-fitly">
    <h2>FITLY</h2>
    <a href="dashboard.php">Inicio</a>
    <a href="progreso_fitly.php" class="active">Progreso</a>
    <a href="nutrición_fitly.html">Nutrición</a>
    <a href="metas_fitly.php">Metas personales</a>
    <a href="logout.php">Cerrar sesión</a>
</div>

<div class="main-fitly">

<div class="titulo">Tu Progreso</div>

<div class="grid-progreso">

<div class="card-progreso">
    <h3>📊 Promedio IMC</h3>
    <div class="numero"><?= $promedio ? $promedio : "--" ?></div>

    <?php if($promedio): ?>
    <div class="barra">
        <div class="barra-progreso" style="width: <?= min(100,$promedio*3) ?>%"></div>
    </div>
    <p style="font-size: 12px; color: #88957d; margin-top: 8px;">Escala referencial</p>
    <?php endif; ?>
</div>

<div class="card-progreso">
    <h3>💡 Estado</h3>

    <div class="mensaje">
        <?php
        if(count($valores)>1){
            $inicio = $valores[0];
            $fin = end($valores);

            if($fin < $inicio){
                echo "🎉 ¡Felicidades! Vas mejorando, sigue así 💪";
            }elseif($fin > $inicio){
                echo "🟡 Tu IMC subió, cuida tu alimentación 🥗";
            }else{
                echo "💙 Te mantienes estable, ¡sigue constante! 🌟";
            }
        } else {
            echo "📝 Agrega más datos para ver tu progreso";
        }
        ?>
    </div>
</div>

<div class="card-progreso">
    <h3>📋 Historial</h3>
    <ul class="lista">
        <?php if(empty($valores)): ?>
            <li>No hay datos registrados</li>
        <?php else: ?>
            <?php foreach($valores as $i => $v): ?>
                <li><?= $fechas[$i] ?> → <strong>IMC: <?= $v ?></strong></li>
            <?php endforeach; ?>
        <?php endif; ?>
    </ul>
</div>

</div>

<!-- GRÁFICA -->
<div class="chart-container">
    <h3>Evolución del IMC</h3>
    <canvas id="graficaIMC"></canvas>
</div>

</div>
</div>

<script>
const ctx = document.getElementById('graficaIMC').getContext('2d');

const gradient = ctx.createLinearGradient(0,0,0,300);
gradient.addColorStop(0,"rgba(124, 181, 24, 0.3)");
gradient.addColorStop(1,"rgba(199, 233, 176, 0.05)");

new Chart(ctx, {
    type: 'line',
    data: {
        labels: <?= json_encode($fechas) ?>,
        datasets: [{
            label: 'IMC',
            data: <?= json_encode($valores) ?>,
            fill: true,
            backgroundColor: gradient,
            borderColor: "#7cb518",
            pointBackgroundColor: "#5e5d02",
            pointBorderColor: "#ffffff",
            pointBorderWidth: 2,
            pointRadius: 6,
            pointHoverRadius: 8,
            tension: 0.4,
            borderWidth: 3
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: true,
        plugins: { 
            legend: { 
                display: false 
            },
            tooltip: {
                backgroundColor: "#2d3e2b",
                titleColor: "#c7e9b0",
                bodyColor: "#ffffff",
                cornerRadius: 10
            }
        },
        scales:{
            x: { 
                ticks: { color: '#5e5d02', font: { weight: 'bold' } },
                grid: { color: '#e0e8d8' }
            },
            y: { 
                ticks: { color: '#410057' },
                grid: { color: '#e0e8d8' }
            }
        }
    }
});
</script>

</body>
</html>
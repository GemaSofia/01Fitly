// ==========================
// FRASE MOTIVACIONAL (API)
// ==========================
async function cargarFrase() {
    try {
        const res = await fetch("https://api.quotable.io/random");
        const data = await res.json();
        document.getElementById("frase").innerText = data.content;
    } catch {
        document.getElementById("frase").innerText = "Sigue adelante, tú puedes 💚";
    }
}

// ==========================
// IMC COMPLETO
// ==========================
async function calcularIMC() {
    let peso = parseFloat(document.getElementById("peso").value);
    let altura = parseFloat(document.getElementById("altura").value);

    if (!peso || !altura) {
        document.getElementById("resultadoIMC").innerText = "Completa los campos";
        return;
    }
    fetch("php/get_imc.php")
    .then(r => r.json())
    .then(data => {

        if (data.length == 1) {
            fetch("php/set_logro.php", {
                method: "POST",
                headers: { "Content-Type": "application/x-www-form-urlencoded" },
                body: "logro=Primer IMC"
            }).then(() => cargarLogros());
        }

        if (data.length == 5) {
            fetch("php/set_logro.php", {
                method: "POST",
                headers: { "Content-Type": "application/x-www-form-urlencoded" },
                body: "logro=IMC x5"
            }).then(() => cargarLogros());
        }

    });

    let imc = (peso / (altura * altura)).toFixed(2);
    document.getElementById("resultadoIMC").innerText = "IMC: " + imc;

    let estado = "";
    if (imc < 18.5) estado = "Bajo peso";
    else if (imc < 24.9) estado = "Normal";
    else if (imc < 29.9) estado = "Sobrepeso 🟡";
    else if (imc < 34.9) estado = "Obesidad grado I 🟠";
    else estado = "Obesidad grado II/III 🔴";

    document.getElementById("estadoIMC").innerText = "Estado: " + estado;

    await fetch("php/guardar_imc.php", {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: `peso=${peso}&altura=${altura}&imc=${imc}`
    });

    cargarHistorialIMC(); 
}

// ==========================
// HISTORIAL IMC EN DASHBOARD
// ==========================
async function cargarHistorialIMC() {
    const res = await fetch("php/get_imc.php");
    const data = await res.json();

    const box = document.getElementById("historialIMC");
    let html = "<h4>Historial IMC</h4>";

    if (data.length === 0) {
        html += "<p>No hay registros aún</p>";
    } else {
        html += "<ul class='historial-list'>";
        data.forEach(item => {
            html += `
                <li>
                    <b>${item.fecha}</b> — 
                    ${item.peso} kg, ${item.altura} m → IMC: ${item.imc}
                </li>`;
        });
        html += "</ul>";
    }

    box.innerHTML = html;
}

// ==========================
// BOTÓN DE HISTORIAL IMC
// ==========================
document.addEventListener("DOMContentLoaded", () => {
    const btn = document.getElementById("btnHistorial");
    const box = document.getElementById("historialIMC");

    btn.addEventListener("click", async () => {
        if (box.style.display === "none" || box.style.display === "") {
            await cargarHistorialIMC();
            box.style.display = "block";
            btn.innerText = "Ocultar historial";
        } else {
            box.style.display = "none";
            btn.innerText = "Ver historial IMC";
        }
    });
});

// ==========================
// RACHA BD
// ==========================
async function cargarRacha() {
    const res = await fetch("php/get_racha.php");
    const data = await res.json();
    document.getElementById("racha").innerText = data.racha;
}

async function sumarRacha() {
    const res = await fetch("php/sumar_racha.php");
    const nueva = await res.text();

    document.getElementById("racha").innerText = nueva;

    if (nueva == 7) {
        fetch("php/set_logro.php", {
            method: "POST",
            headers: { "Content-Type": "application/x-www-form-urlencoded" },
            body: "logro=7 días"
        }).then(() => cargarLogros());
    }

    if (nueva == 30) {
        fetch("php/set_logro.php", {
            method: "POST",
            headers: { "Content-Type": "application/x-www-form-urlencoded" },
            body: "logro=30 días"
        }).then(() => cargarLogros());
    }

}

// ==========================
// INICIO
// ==========================
window.onload = function () {
    cargarFrase();
    cargarRacha();
    cargarLogros();

    // ==========================
// LOGROS
// ==========================
async function cargarLogros() {
    const res = await fetch("php/get_logros.php");
    const data = await res.json();

    let html = "";

    data.forEach(l => {
        html += `
            <div class="logro-item ${l.desbloqueado == 1 ? 'logro-ok' : 'logro-lock'}">
                ${l.logro}
                ${l.desbloqueado == 1 ? '✅' : '🔒'}
            </div>
        `;
    });

    document.getElementById("lista-logros").innerHTML = html;
}
};

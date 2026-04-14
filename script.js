document.getElementById("formIMC")?.addEventListener("submit", function(e){
    e.preventDefault();

    
    let peso = parseFloat(document.getElementById("peso").value);
    let estatura = parseFloat(document.getElementById("estatura").value) / 100;

    let imc = (peso / (estatura * estatura)).toFixed(2);

    let clasificacion = "";
    if (imc < 18.5) clasificacion = "Bajo peso";
    else if (imc < 24.9) clasificacion = "Peso saludable";
    else if (imc < 29.9) clasificacion = "Sobrepeso";
    else clasificacion = "Obesidad";

    document.getElementById("resultado").innerHTML = `
        <h3>Tu IMC es: ${imc}</h3>
        <p>Clasificación: <strong>${clasificacion}</strong></p>
        <p><small>Esta información es general y no sustituye asesoría profesional.</small></p>
    `;
});

// ===============================
// HISTORIAL IMC
// ===============================

if (window.location.pathname.includes("historial_imc.html")) {
    fetch("php/get_historial_imc.php")
        .then(res => res.json())
        .then(datos => mostrarHistorial(datos));
}

function mostrarHistorial(datos) {

    if (datos.length === 0) {
        document.getElementById("ultimoIMC").innerHTML =
            "<p>Aún no tienes registros IMC.</p>";
        return;
    }

    // 📌 Último IMC
    let ultimo = datos[0];

    let estado = "";
    if (ultimo.imc < 18.5) estado = "Bajo peso";
    else if (ultimo.imc < 25) estado = "Normal";
    else if (ultimo.imc < 30) estado = "Sobrepeso";
    else estado = "Obesidad";

    document.getElementById("ultimoIMC").innerHTML = `
        <h3>Último IMC</h3>
        <p><strong>${ultimo.imc}</strong></p>
        <p>${estado}</p>
        <small>${ultimo.fecha}</small>
    `;

    // 📌 Tabla
    let html = "";
    datos.forEach(d => {
        let e = "";
        if (d.imc < 18.5) e = "Bajo peso";
        else if (d.imc < 25) e = "Normal";
        else if (d.imc < 30) e = "Sobrepeso";
        else e = "Obesidad";

        html += `
            <tr>
                <td>${d.fecha}</td>
                <td>${d.peso}</td>
                <td>${d.altura}</td>
                <td>${d.imc}</td>
                <td>${e}</td>
            </tr>
        `;
    });

    document.getElementById("tablaIMC").innerHTML = html;

    // 📌 GRÁFICA
    let ctx = document.getElementById("graficaIMC");
    new Chart(ctx, {
        type: "line",
        data: {
            labels: datos.map(d => d.fecha),
            datasets: [{
                label: "IMC",
                data: datos.map(d => d.imc),
                borderWidth: 2
            }]
        }
    });
}
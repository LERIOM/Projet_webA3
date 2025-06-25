
// Initialisation de la carte Plotly
document.addEventListener('DOMContentLoaded', () => {
    const vesselNames = ["Liberty One", "Ocean Spirit", "Sea Explorer"];
    const vesselLats = [24.5, 25.2, 26.0];
    const vesselLons = [-89.5, -88.0, -87.5];

    // Trace des bateaux
    const trace = {
    type: 'scattergeo',
    locationmode: 'USA-states',
    lon: vesselLons,
    lat: vesselLats,
    text: vesselNames.map((n,i) => `${n}`),
    mode: 'markers',
    marker: {
        size: 14,
        symbol: 'circle',
        color: 'blue'
    },
    hoverinfo: 'text'
    };

    // Layout centré sur le Golfe
    const layout = {
    geo: {
        scope: 'north america',
        projection: { type: 'albers usa' },
        lonaxis: { range: [-95, -82] },
        lataxis: { range: [22, 30] },
        showland: true,
        landcolor: 'rgb(217, 217, 217)',
        subunitwidth: 1,
        countrywidth: 1,
        subunitcolor: 'rgb(255,255,255)',
        countrycolor: 'rgb(255,255,255)'
    },
    margin: { l:0, r:0, t:0, b:0 }
    };

    Plotly.newPlot('plotly-map', [trace], layout);

    // Gestion des clics sur les points
    const mapDiv = document.getElementById('plotly-map');
    mapDiv.on('plotly_click', data => {
    const point = data.points[0];
    const name = point.text;
    document.getElementById("vesselNameDisplay").textContent = name;
    document.getElementById("vesselModalLabel").textContent = `Bateau : ${name}`;
    new bootstrap.Modal(document.getElementById('vesselModal')).show();
    });

    // Remplissage du tableau avec liens
    const tbody = document.getElementById("vessel-tbody");
    vesselNames.forEach(name => {
    const tr = document.createElement("tr");
    const td = document.createElement("td");
    const link = document.createElement("a");
    link.href = "#";
    link.textContent = name;
    link.className = "text-primary text-decoration-underline";
    link.setAttribute("data-bs-toggle", "modal");
    link.setAttribute("data-bs-target", "#vesselModal");
    link.onclick = () => {
        document.getElementById("vesselNameDisplay").textContent = name;
        document.getElementById("vesselModalLabel").textContent = `Bateau : ${name}`;
    };
    td.appendChild(link);
    tr.appendChild(td);
    tbody.appendChild(tr);
    });
});

// Initialisation de la carte Plotly
document.addEventListener('DOMContentLoaded', () => {
    // Dynamically load vessel data
    ajaxRequest('GET', '/php/request.php/getAllVesselsPos', function(response) {
      if (!response.error) {
        // Récupère le tableau de navires depuis la réponse et log
        const vessels = response.data || response;
        // Stocke les données globalement pour recolorisation par cluster
        window.vessels = vessels;
        // Récupère les clusters pour chaque point
        const vesselClusters = vessels.map(v => parseInt(v.cluster_kmeans, 10));
        console.log('Données des navires récupérées avec succès:', vessels);
        // Génère les tableaux pour Plotly à partir de chaque objet
        const vesselNames = vessels.map(v => v.vessel_name);
        const vesselLats = vessels.map(v => parseFloat(v.lat));
        const vesselLons = vessels.map(v => parseFloat(v.lon));

        const trace = {
          type: 'scattergeo',
          locationmode: 'USA-states',
          lon: vesselLons,
          lat: vesselLats,
          text: vesselNames,
          mode: 'markers',
          marker: {
            size: 5,
            symbol: 'circle',
            color: 'blue'
          },
          hoverinfo: 'text'
        };

        const layout = {
          margin: { t: 0, b: 0, l: 0, r: 0 },
          geo: {
            projection: { type: 'mercator' },
            showland: true,
            landcolor: 'rgb(230,230,230)',
            showcountries: true,
            lataxis: { range: [18, 31] },
            lonaxis: { range: [-105, -74] }
          }
        };

        Plotly.newPlot('plotly-map', [trace], layout);

        // Fonction pour colorer les points selon leur cluster (0-4)
        window.applyClusterColors = function() {
          const clusterColors = ['red', 'green', 'blue', 'orange', 'purple'];
          // Génère un tableau de couleurs basé sur cluster_kmeans
          const colors = window.vessels.map(v => {
            const c = parseInt(v.cluster_kmeans, 10);
            return clusterColors[c] || 'gray';
          });
          // Met à jour la couleur des marqueurs sur la carte
          Plotly.restyle('plotly-map', 'marker.color', [colors]);
        };

        // Attach click handler after plotting
        const mapDiv = document.getElementById('plotly-map');
        mapDiv.on('plotly_click', data => {
          const point = data.points[0];
          const name = point.text;
          getInfoByName(name);
          document.getElementById("vesselNameDisplay").textContent = name;
          document.getElementById("vesselModalLabel").textContent = `Bateau : ${name}`;
          new bootstrap.Modal(document.getElementById('vesselModal')).show();
        });

      } else {
        console.error('Erreur lors de la récupération dynamique des navires:', response);
      }
    });
});

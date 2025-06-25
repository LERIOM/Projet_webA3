function getTabSearch(){
    var id = getCookie("nautica-cookie");
    ajaxRequest('GET','/php/request.php/tabSearch?id_user='+id, function(responses){

        if(!responses.error){

          let content = document.getElementById("tabSearch");
          content.innerHTML=""
          // console.log(responses);
          for(let response of responses){
            // console.log(response);
            content.innerHTML+=`<tr> 
                  <td>`+response.duration+`</td>
                  <td>`+response.depth+`</td>
                  <td>
                  <button type="button" class="btn btn-light" onclick="displayAddDive(`+response.id_dive+`)" data-bs-toggle="modal" data-bs-target="#exampleModal"> Ajouter a mes plongées</button>
                </td>
                <td><span class="close" onclick="deleteDive(` + response.id_dive + `)">&times;</span></td>
            </tr>`;
          };
        }
        else{
            let content = document.getElementById("tabSearch");
            content.innerHTML=""
        }
    });
}


function test(){
    ajaxRequest('GET','/php/request.php/test', function(responses){
        if(!responses.error){
            console.log(responses);
        }
        else{
            console.log(responses);
        }
    });
}

function getAllBoats(){
    ajaxRequest('GET','/php/request.php/boatAll', function(responses){
        if(!responses.error){
            console.log(responses);
        }
        else{
            console.log(responses);
        }
    });
}

function getBoatMmsi(mmsi){
    ajaxRequest('GET','/php/request.php/boatMmsi?mmsi='+mmsi, function(responses){
        if(!responses.error){
            console.log(responses);
        }
        else{
            console.log(responses);
        }
    });
}

function getCluster(){
    ajaxRequest('GET','/php/request.php/predictCluster?cog='+cog+'&sog='+sog+'&lat='+lat+'&lon='+lon, function(responses){
        if(!responses.error){
            console.log(responses);
        }
        else{
            console.log(responses);
        }
    });
}

function getPredictTrajectory( cog, sog, lat, lon, delta, heading,length, draft){
    ajaxRequest('GET','/php/request.php/predictTrajectory?&cog='+cog+'&sog='+sog+'&lat='+lat+'&lon='+lon+'&delta='+delta+'&heading='+heading+'&length='+length+'&draft='+draft, function(responses){
        const prediction = Array.isArray(responses) ? responses[0] : null;
        if (prediction) {
            console.log('Latitude :', prediction.lat);
        } else {
            console.error('Réponse inattendue:', responses);
        }
    });
}

/**
 * Envoie le texte de l'utilisateur au chatbot via l'API OpenAI et affiche la réponse.
 * @param {string} promptText - Le texte saisi par l'utilisateur.
 */
function sendChat(promptText) {
  appendMessage(promptText, 'user');
  fetch('/php/request.php/chat', {
    method: 'POST',
    headers: {'Content-Type': 'application/json'},
    body: JSON.stringify({ prompt: promptText })
  })
  .then(response => response.json())
  .then(data => {
    appendMessage(data.answer, 'bot');
  })
  .catch(error => {
    console.error('Erreur chat API:', error);
  });
}

/**
 * Ajoute un message à la zone de chat.
 * @param {string} text - Le texte du message.
 * @param {string} sender - 'user' ou 'bot' pour appliquer un style.
 */
function appendMessage(text, sender) {
    const messagesEl = document.getElementById('messages');
    if (!messagesEl) return;
    const div = document.createElement('div');
    div.className = 'msg ' + sender;
    div.textContent = text;
    messagesEl.appendChild(div);
    messagesEl.scrollTop = messagesEl.scrollHeight;
}
function clearChat() {
    const messagesEl = document.getElementById('messages');
    if (messagesEl) {
        messagesEl.innerHTML = '';
    }
}
function GetTabVessselsName() {
  ajaxRequest('GET', '/php/request.php/vesselname', function(raw) {
    // 1) JSON.parse si nécessaire
    let data;
    try {
      data = (typeof raw === 'string') ? JSON.parse(raw) : raw;
    } catch (e) {
      console.error('Erreur JSON.parse :', e, raw);
      return;
    }

    // 2) on s'assure d'avoir bien un array d'objets
    if (!Array.isArray(data)) {
      console.error('Format inattendu, attendu un array :', data);
      return;
    }

    // 3) on extrait juste les noms
    let vesselNames = data.map(item => item.vessel_name);

    // 4) padding pour que count % 4 === 0
    const reste = vesselNames.length % 4;
    if (reste !== 0) {
      for (let i = 0; i < 4 - reste; i++) {
        vesselNames.push('');  // cellules vides
      }
    }

    // 5) build du tableau
    const tbody = document.getElementById('vessel-tbody');
    tbody.innerHTML = '';

    for (let i = 0; i < vesselNames.length; i += 4) {
      const tr = document.createElement('tr');

      for (let j = 0; j < 4; j++) {
        const name = vesselNames[i + j];
        const td = document.createElement('td');

        if (name) {
          // Création du lien cliquable pour ouvrir le modal
          const link = document.createElement('a');
          link.href = '#';
          link.className = 'text-primary text-decoration-underline';
          link.textContent = name;
          link.setAttribute('data-bs-toggle', 'modal');
          link.setAttribute('data-bs-target', '#vesselModal');
          link.addEventListener('click', () => {
            document.getElementById('vesselNameDisplay').textContent = name;
            document.getElementById('vesselModalLabel')
                    .textContent = `Bateau : ${name}`;
            getInfoByName(name);
          });

          td.appendChild(link);
        } else {
          // cellule de remplissage
          td.innerHTML = '&nbsp;';
        }

        tr.appendChild(td);
      }

      tbody.appendChild(tr);
    }
  });
}


function getInfoByName(name){
    getTabByName(name);
    ajaxRequest('GET', '/php/request.php/vesselInfo?name=' + encodeURIComponent(name), function(raw) {
        const response = raw[0];
        if (!response.error) {
            const infoDiv = document.getElementById('vesselNameDisplay');
            infoDiv.innerHTML = `
                <p><strong>MMSI:</strong> ${response.mmsi} <strong>IMO:</strong> ${response.imo} <strong>Type:</strong> ${response.type} <strong>Longueur:</strong> ${response.length} m <strong>Tirant d'eau:</strong> ${response.draft} m</p>
            `;
        } else {
            console.error('Erreur lors de la récupération des infos du bateau:', response);
        }
    });
}

function getTabByName(name) {
    ajaxRequest('GET', '/php/request.php/vesselTab?name=' + encodeURIComponent(name), function(raw) {
        const tbody = document.getElementById('vesselModalTbody');
        tbody.innerHTML = ''; // Réinitialiser le contenu
        if (Array.isArray(raw) && raw.length > 0) {
            raw.forEach(item => {
                const tr = document.createElement('tr');
                tr.innerHTML = `
                    <td>${item.date}</td>
                    <td>${item.status}</td>
                    <td>${item.cog}</td>
                    <td>${item.sog}</td>
                    <td>${item.lat}</td>
                    <td>${item.lon}</td>
                   <td>${item.heading}</td>
                `;
                tbody.appendChild(tr);
            });
        } else {
            tbody.innerHTML = '<tr><td colspan="5">Aucune donnée disponible</td></tr>';
        }
    });
}

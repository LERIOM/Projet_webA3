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
  clearPrompt();
  appendMessage(promptText, 'user');
  fetch('/php/request.php/chat', {
    method: 'POST',
    headers: {'Content-Type': 'application/json'},
    body: JSON.stringify({ prompt: promptText })
  })
  .then(response => response.json())
  .then(data => {
  let parsed;
  try {
    parsed = JSON.parse(data.answer);
  } catch {
    parsed = null;
  }

  if (Array.isArray(parsed) && parsed.every(item => typeof item === 'object')) {
    renderTable(parsed);
  } else {
    appendMessage(data.answer, 'bot');
  }
})
}
/**
 * Vide le champ de saisie du prompt.
 */
function clearPrompt() {
    const promptEl = document.getElementById('prompt');
    if (promptEl) {
        promptEl.value = '';
    }
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
    ajaxRequest('GET', '/php/request.php/positionTab?name=' + encodeURIComponent(name), function(raw) {
        const tbody = document.getElementById('vesselModalTbody');
        tbody.innerHTML = ''; // Réinitialiser le contenu
        if (Array.isArray(raw) && raw.length > 0) {
            raw.forEach(item => {
                const tr = document.createElement('tr');
                tr.innerHTML = `
                    <td>${item.base_date_time}</td>
                    <td>${item.status_description}</td>
                    <td>${item.lat}</td>
                    <td>${item.lon}</td>
                    <td>${item.cog}</td>
                    <td>${item.sog}</td>
                   <td>${item.heading}</td>
                   <td><input type="radio" name="selectMMSI" value="${item.id_position}" onclick="console.log(item.id_position)"></td>`;
                tbody.appendChild(tr);
            });
        } else {
            tbody.innerHTML = '<tr><td colspan="5">Aucune donnée disponible</td></tr>';
        }
    });
}


function renderTable(rows) {
    const messagesEl = document.getElementById('messages');
    // Crée la table et ses éléments
    const table = document.createElement('table');
    table.style.width = '100%';
    table.style.borderCollapse = 'collapse';
    table.style.margin = '10px 0';

    // En-têtes dynamiques
    const thead = document.createElement('thead');
    const headerRow = document.createElement('tr');
    const cols = Object.keys(rows[0]);
    cols.forEach(col => {
        const th = document.createElement('th');
        th.textContent = col;
        th.style.border = '1px solid #ccc';
        th.style.padding = '4px';
        th.style.background = '#f0f0f0';
        headerRow.appendChild(th);
    });
    thead.appendChild(headerRow);
    table.appendChild(thead);

    // Corps du tableau
    const tbody = document.createElement('tbody');
    rows.forEach(obj => {
        const tr = document.createElement('tr');
        cols.forEach(col => {
            const td = document.createElement('td');
            td.textContent = obj[col] ?? '';
            td.style.border = '1px solid #ccc';
            td.style.padding = '4px';
            tr.appendChild(td);
        });
        tbody.appendChild(tr);
    });
    table.appendChild(tbody);

    // Wrapper pour conserver le style "bot"
    const wrapper = document.createElement('div');
    wrapper.className = 'msg bot';
    wrapper.appendChild(table);
    messagesEl.appendChild(wrapper);
    messagesEl.scrollTop = messagesEl.scrollHeight;
}

 function putNewBoat(event) {
    event.preventDefault();


    const formData = new FormData(this);
    const mmsi = formData.get('mmsi');
    const timestamp = formData.get('timestamp');
    const lat     = parseFloat(formData.get('lat'));
    const lon     = parseFloat(formData.get('lon'));
    const sog     = parseFloat(formData.get('sog'));
    const cog     = parseFloat(formData.get('cog'));
    const heading = parseFloat(formData.get('heading'));
    const name = formData.get('vessel_name');
    const status = formData.get('status');
    const length  = parseFloat(formData.get('length'));
    const width   = parseFloat(formData.get('width'));
    const draft   = parseFloat(formData.get('draft'));

    console.log('Données du bateau à ajouter :', {
        mmsi,
        timestamp,
        lat,
        lon,
        sog,
        cog,
        heading,
        name,
        status,
        length,
        width,
        draft
    });
    let data = "mmsi=" + mmsi + "&timestamp=" + timestamp + "&lat=" + lat + "&lon=" + lon + "&sog=" + sog + "&cog=" + cog + "&heading=" + heading + "&name=" + name + "&status=" + status + "&length=" + length + "&width=" + width + "&draft=" + draft;

    ajaxRequest('POST', '/php/request.php/boat', function(response) {
        if (!response.error) {
            console.log('Bateau ajouté avec succès:', response);
        } else {
            console.error('Erreur lors de l\'ajout du bateau:', response);
        }
    }, data);

    const ajoutModal = bootstrap.Modal.getInstance(document.getElementById('ajoutPointModal'));
    ajoutModal.hide();
    this.reset();
}

 function getSelectedMMSI() {
    const radios = document.getElementsByName('selectMMSI');
    for (let radio of radios) {
      if (radio.checked) {
        return radio.value;
      }
    }
    alert("Veuillez sélectionner un bateau.");
    return null;
  }

  function predictType() {
    const mmsi = getSelectedMMSI();
    if (mmsi) {
      console.log("Prédiction du type pour le MMSI :", mmsi);
    }
  }

  function predictTrajectoire() {
    const mmsi = getSelectedMMSI();
    if (mmsi) {
      console.log("Prédiction de la trajectoire pour le MMSI :", mmsi);
    }
  }
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
  // Exemple de données : remplacez par votre liste réelle
  const vesselNames = [
    'Titanic', 'Queen Mary', 'USS Enterprise', 'Black Pearl',
    'HMS Victory', 'Santa Maria', 'Endeavour', 'Kon-Tiki',
    'USS Constitution', 'Bismarck', 'USS Missouri'
    // … etc.
  ];

  const tbody = document.getElementById('vessel-tbody');
  let currentRow = null;

  vesselNames.forEach((name, idx) => {
    // À chaque multiple de 4, on crée une nouvelle ligne
    if (idx % 4 === 0) {
      currentRow = document.createElement('tr');
      tbody.appendChild(currentRow);
    }
    // On crée la cellule, on lui donne le texte et on l'ajoute à la ligne
    const td = document.createElement('td');
    td.textContent = name;
    currentRow.appendChild(td);
  });

  // Si la dernière ligne est incomplète (moins de 4), on la complète avec des cellules vides
  const missing = vesselNames.length % 4;
  if (missing !== 0) {
    for (let i = missing; i < 4; i++) {
      const emptyTd = document.createElement('td');
      emptyTd.innerHTML = '&nbsp;'; // espace insécable
      currentRow.appendChild(emptyTd);
    }
  }
}
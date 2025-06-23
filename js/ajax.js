function ajaxRequest(type, url, callback, data = null)
{
  let xhr;
  // Create XML HTTP request.
  xhr = new XMLHttpRequest();
  if (type == 'GET' && data != null)
    url += '?' + data;
  xhr.open(type, url);
  xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');

  // Add the onload function.
  xhr.onload = () =>
  {

    console.log(xhr.responseText);
    switch (xhr.status)
    {
      case 200:
      case 201:
        callback(JSON.parse(xhr.responseText));
        break;
      case 404:
        alert("Erreur 404: Page non trouvée");
        break;
      case 500:
        alert("Erreur 500: Erreur interne du serveur");
        break;
      default:
        alert("Erreur: " + xhr.status);
        break;
    }
  };

  // Send XML HTTP request.
  xhr.send(data);
}
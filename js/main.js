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



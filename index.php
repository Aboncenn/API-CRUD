<?php
?>
<!doctype html>
<html lang="fr">
<head>
  <meta charset="utf-8">
  <title>EPSI</title>
  <link rel="stylesheet" href="https://unpkg.com/leaflet@1.3.4/dist/leaflet.css"
  integrity="sha512-puBpdR0798OZvTTbP4A8Ix/l+A4dHDD0DGqYW6RQ+9jxkRFclaxxQb/SJAWZfWAkuyeQUytO7+7N4QKrDh+drA=="
  crossorigin=""/>
  <script src="https://code.jquery.com/jquery-3.3.1.min.js" integrity="sha256-FgpCb/KJQlLNfOu91ta32o/NMZxltwRo8QtmkMRdAu8=" crossorigin="anonymous"></script>
  <script src="https://unpkg.com/leaflet@1.3.4/dist/leaflet.js" integrity="sha512-nMMmRyTVoLYqjP9hrbed9S+FzjZHW5gY1TWCHA5ckwXZBadntCNs8kEqAWdrb9O7rxbCaA4lKTIWjDXZxflOcA==" crossorigin=""></script>
  <style type="text/css">
    #carte {
      min-height: 90vh;
    }
  </style>
</head>
<body>
  <div id="body">
    <h1>Hopitaux de Grenoble</h1>
    <h3>Créer un lieu</h3>
    <div>
      <br>Nom du lieu:
      <input type="text" id="name_create" name="name" required><br>
      <br>Adresse:
      <input type="text" id="adress_create" name="adress" required>
      <br>Code postal:
      <input type="number" id="postal_create" name="code_postal" required>
      <br>Ville:
      <input type="text" id="city_create" name="ville" required>
      <br>
      <select id="id_category" >
        <option required>---- SELECTIONNEZ ----</option>
      </select>
    </div>
    <button type="submit" id='submit_create' value="Submit">Submit</button>
    <div>
      <h3>Créer une catégorie</h3>
      <div>
        <br>Nom de la catégorie
        <input type="text" id="cat_create" name="name" required><br>
      </div>
      <button type="submit" id='submit_cat' value="Submit">Submit</button>
    </div>
    <div id="carte"></div>
  </div>
</body>
</html>
<script>
  $(document).ready(function() {
    if (navigator.geolocation) {
      navigator.geolocation.getCurrentPosition(function(position) {

        // Les variables importantes
        var apikey = "5d00e249171228470d8e265d6259735bfebcd18efd9f463bfe72cab07d7221da"; // key API
        var latitude = position.coords.latitude;
        var longitude = position.coords.longitude;

        // AFFICHAGE DE LA CARTE
        var map = L.map('carte').setView([latitude, longitude], 15);
        var osmUrl = 'http://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png';
        var osmAttrib = 'Map data © <a href="http://openstreetmap.org">OpenStreetMap</a> contributors';
        var france = new L.TileLayer(osmUrl, {
          minZoom: 2,
          maxZoom: 15,
          attribution: osmAttrib
        });
        var user = L.marker([latitude, longitude], {icon: User_icon}).addTo(map);
        map.addLayer(france);

        // AFFICHAGE DES CATEGORIES

        var category = $.ajax({
          "async": true,
          "crossDomain": true,
          "url": "https://api.cours-webservice.arrobe.fr/v2/category",
          "method": "GET",
          "headers": {
            'apikey': apikey,
          },
          success: function(data) {
            data.category.forEach(function(value) {
              var input = document.createElement("option");
              var title = document.createTextNode(value.name);
              input.setAttribute("value", value.id);
              input.appendChild(title);
              document.getElementById("id_category").appendChild(input);
            });
          },
          error: function(jqXHR, textStatus, errorThrown) {
            alert("Il y à un soucis quelque part !");
            console.log('jqXHR:');
            console.log(jqXHR);
            console.log('textStatus:');
            console.log(textStatus);
            console.log('errorThrown:');
            console.log(errorThrown);
          },
          dataType: "json"
        });


        // AFFICHAGE DES HOPITAUX
        var all_places = $.ajax({
          "async": true,
          "crossDomain": true,
          "url": "https://api.cours-webservice.arrobe.fr/v2/places/"+latitude +"/"+ longitude + "/10",
          "method": "GET",
          "headers": {
            'apikey': apikey,
          },
          success: function(data) {
          data.results.forEach(function(value) {
              var marker = L.marker([value.latitude, value.longitude]).addTo(map);

              var content_pop =" <br>" + value.name + ", situé  "+ value.address + " </b><br> "+ value.postal_code +" " + value.city +
                                "<br> <b>Modifier ce lieu</b>" +
                                "<br> <div><input type='hidden' id='id_update' value=" + value.id + " required>" +
                                "<br> Nom du lieu: <input type='text' id='name_update' name='name' required>" +
                                "<br> Adresse:<input type='text' id='adress_update' name='adress' required>" +
                                "<br> Code postal:<input type='number' id='postal_update' name='code_postal' required>" +
                                "<br> Ville:<input type='text' id='city_update' name='ville' required>" +
                                "<br> <select id='id_category1' required><option id='id_category_update' value ="+value.category_id + ">"+ value.name +"</option></select></div>"+
                                "<button type='submit' id='submit_update' class='open_button' value='Submit'>Submit</button> <button type='submit' id='submit_delete' class='close_button' value='Delete'>Delete</button> ";

              marker.bindPopup(content_pop).openPopup();
            });
            },
            error: function(jqXHR, textStatus, errorThrown) {
              alert("Il y à un soucis quelque part !");
              console.log('jqXHR:');
              console.log(jqXHR);
              console.log('textStatus:');
              console.log(textStatus);
              console.log('errorThrown:');
              console.log(errorThrown);
            },
          dataType: "json",
        });

        // Fonction création des éléments
        $("#submit_create").click(function() {
          // Récupération de tous les éléments
          var name_create = document.getElementById('name_create').value;
          var adress_create = document.getElementById('adress_create').value;
          var postal_create = document.getElementById('postal_create').value;
          var city_create = document.getElementById('city_create').value;
          var id_category = document.getElementById('id_category_update').value;

          // Création du fichier d'ajout d'élément
          var data = {
            "category_id": id_category,
            "name": name_create,
            "address": adress_create,
            "postal_code": postal_create,
            "city": city_create
          };
          data = JSON.stringify(data);

          // Envoi du nouveau lieu
          var post = $.ajax({
            "async": true,
            "crossDomain": true,
            "url": "https://api.cours-webservice.arrobe.fr/v2/places",
            "method": "POST",
            "headers": {
                'apikey': apikey,
            },
            success: function(data) {
              alert("votre ajout à bien été pris en compte ! Veuillez recharger la page svp");
              var newOne = L.marker([data.latitude, data.longitude], {icon: Newplace}).addTo(map);
              newOne.bindPopup("<b>" + data.name +", situé au " + data.address + "</b><br>"+ data.postal_code + data.city).openPopup();

            },
            error: function(jqXHR, textStatus, errorThrown) {
              alert("Il y à un soucis quelque part !");
              console.log('jqXHR:');
              console.log(jqXHR);
              console.log('textStatus:');
              console.log(textStatus);
              console.log('errorThrown:');
              console.log(errorThrown);
            },
            data: data,
            dataType: "json"
          });
        });

        // Fonction d'update d'élément
        $("div").on("click", '.open_button', function () {
          // Récupération de tous les éléments
          var id_update = document.getElementById('id_update').value;
          var name_update = document.getElementById('name_update').value;
          var adress_update = document.getElementById('adress_update').value;
          var postal_update = document.getElementById('postal_update').value;
          var city_update = document.getElementById('city_update').value;
          var id_category = document.getElementById('id_category_update').value;
          // Création du fichier d'update d'élément
          var data = {
            "category_id": id_category,
          	"name": name_update,
          	"address": adress_update,
          	"postal_code": postal_update,
          };
          data = JSON.stringify(data);
          // Ajax Update
          var path = $.ajax({
            "async": true,
            "crossDomain": true,
            "url": "https://api.cours-webservice.arrobe.fr/v2/places/" + id_update,
            "method": "PATCH",
            "headers": {
                'apikey': apikey,
            },
            success: function(data) {
              alert("votre modification à bien été pris en compte ! Veuillez recharger svp");
            },
            error: function(jqXHR, textStatus, errorThrown) {
              alert("Il y à un soucis quelque part !");
              console.log('jqXHR:');
              console.log(jqXHR);
              console.log('textStatus:');
              console.log(textStatus);
              console.log('errorThrown:');
              console.log(errorThrown);
            },
            data: data,
            dataType: "json",
          });

        });

        // Alert delete button
        $("div").on("click", '.close_button', function () {
          var alert = confirm("Etes vous sur de vouloir supprimer cet élément ?");
          if(alert == true){
            var alert = confirm("C'est fait. Rechargez la page svp");
            // Récupération de tous les éléments
            var id_delete = document.getElementById('id_update').value;
            // Ajax Delete
            var path = $.ajax({
              "async": true,
              "crossDomain": true,
              "url": "https://api.cours-webservice.arrobe.fr/v2/places/" + id_delete,
              "method": "DELETE",
              "headers": {
                  'apikey': apikey,
              },
              success: function(data) {
                alert("Vous avez supprimé cet élément");
              },
              error: function(jqXHR, textStatus, errorThrown) {
                alert("Il y à un soucis quelque part !");
                console.log('jqXHR:');
                console.log(jqXHR);
                console.log('textStatus:');
                console.log(textStatus);
                console.log('errorThrown:');
                console.log(errorThrown);
              },
              dataType: "json"
            });
          }
        });

        // Fonction création des catégories
        $("#submit_cat").click(function() {
          // Récupération de tous les éléments
          var name_create = document.getElementById('cat_create').value;

          // Création du fichier d'ajout d'élément
          var data = {
            "name": name_create,
          };
          data = JSON.stringify(data);

          // Envoi du nouveau lieu
          var post = $.ajax({
            "async": true,
            "crossDomain": true,
            "url": "https://api.cours-webservice.arrobe.fr/v2/category",
            "method": "POST",
            "headers": {
                'apikey': apikey,
            },
            success: function(data) {
              alert("Vous avez créer une nouvelle catégorie ! Veuillez recharger la page svp");
            },
            error: function(jqXHR, textStatus, errorThrown) {
              alert("Il y à un soucis quelque part !");
              console.log('jqXHR:');
              console.log(jqXHR);
              console.log('textStatus:');
              console.log(textStatus);
              console.log('errorThrown:');
              console.log(errorThrown);
            },
            data: data,
            dataType: "json"
          });
        });

      });

    } else
      alert("Vous êtes sous IE ou quoi");
  });

  // DU JOLI
  var Newplace = L.icon({
        iconUrl: 'newPlace.png',

        iconSize:     [38, 40], // size of the icon
        iconAnchor:   [22, 94], // point of the icon which will correspond to marker's location
        shadowAnchor: [4, 62],  // the same for the shadow
        popupAnchor:  [-3, -76] // point from which the popup should open relative to the iconAnchor
    });

    var User_icon = L.icon({
        iconUrl: 'icon_user.png',

        iconSize:     [38, 40], // size of the icon
        iconAnchor:   [22, 94], // point of the icon which will correspond to marker's location
        shadowAnchor: [4, 62],  // the same for the shadow
        popupAnchor:  [-3, -76] // point from which the popup should open relative to the iconAnchor
    });
</script>
<?php

  $codesource = file_get_contents('https://www.google.fr/search?q=web+scraping');
echo $codesource;
?>

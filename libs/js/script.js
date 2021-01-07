function getCountryFlag(cc) {
  if (cc.length !== 2)
      return cc;
  function risl(chr) {
      return String.fromCodePoint(0x1F1E6 - 65 + chr.toUpperCase().charCodeAt(0));
  }
  return risl(cc[0]) + risl(cc[1]);
}

function arrayToStr(array,head){
  var outPut = ""
  array.forEach(element => outPut =  outPut + head + String(element));
  return outPut;
}

function arrayToSpans(array,head){
  var outPut = ""
  array.forEach(element => outPut =  outPut + "<span>" + head + String(element) + "</span>" );
  return outPut;
}

function getWeatherIcon(code){

  var codeTrimed = code.substring(0, 2);

  switch(codeTrimed) {
    case "01":
      return "☀️"
    case "02":
      return "⛅"
    case "03":
      return "☁️"
    case "04":
      return "☁️"
    case "09":
      return "🌧️"
    case "10":
      return "🌦️"
    case "11":
      return "⛈️"
    case "13":
      return "❄️"
    case "50":
      return "🌫️"
    default:
      return codeTrimed
  } 
}

function convertMillion(population){
  if(population > 100000){
    return (parseFloat(population)/1000000).toFixed(2) + "M" 
  }else{
    if(population > 1000){
      return (parseFloat(population)/1000).toFixed(2) + "K" 
    }else{
      return population
    }
  }
}

function feedCountryList(){

  var list;

  $( "#countriesDropDown" ).change(function() { loadNewMap( JSON.parse(this.value).codea2, JSON.parse(this.value).index); }); 

  $.ajax({
    url: "libs/php/getCountriesList.php",
    type: 'GET',
    dataType: 'json',
    success: function(result) {  
      var listSorted = result.sort((a, b) => (a.name > b.name) ? 1 : -1)
      listSorted.forEach(element => {
  
        var data = JSON.stringify({codea2:element.iso_a2, index:element.index});

        $('#countriesDropDown').append($('<option>', {value: data , class:"dropdown-item", text:element.name + ' ' + getCountryFlag(element.iso_a2)}));
        
      });
      list = result;

      
    },
    error: function(jqXHR, textStatus, errorThrown) {
        //console.log("Ajax error")
    }
  });

  return list;
}


function onEachFeature(feature, layer) {

  var popupContent = "<h6>" + feature.properties.name + "</h6><p>Population: " + feature.properties.population + "</p>";

  if (feature.properties && feature.properties.popupContent) {
    popupContent += feature.properties.popupContent;
  }

  layer.bindPopup(popupContent);
}


function showResult(result){

  //console.log("entramos en showResult")
  //Clean all divs
  $("#mainInfoBody").html("");
  $("#demogrBody").html("");
  $("#currencyList").html("");
  $("#exchangeGroups").html("");
  $("#weatherBody").html("");
  $("#imagesBody").html("");
  $("#imagesBody").scrollTop(0);


  $(".nameCountryTitle").html(result.nameBorder);

  $("#selectCountryFeedBack").html(result.nameBorder);

  //$(".flag-title").attr("src", result.flagImage)


  $("#mainInfoBody").append('<div class="rowElem"><div class="legendModalElem">Capital</div><div class="dataModalElem">' + result.capital + '</div></div>');
  $("#mainInfoBody").append('<div class="rowElem"><div class="legendModalElem">Region</div><div class="dataModalElem">' + result.region + '</div></div>');
  $("#mainInfoBody").append('<div class="rowElem"><div class="legendModalElem">Subregion</div><div class="dataModalElem">' + result.subregion + '</div></div>');
  $("#mainInfoBody").append('<div class="rowElem"><div class="legendModalElem">Area</div><div class="dataModalElem">' + convertMillion(result.area) + ' km<sup>2</sup></div></div>');
  
  //meter muchos span?
  $("#mainInfoBody").append('<div class="rowElem"><div class="legendModalElem">Time zones</div><div class="dataModalElem">' + arrayToSpans(result.timezones," ") + '</div></div>');
  $("#mainInfoBody").append('<div class="rowElem"><div class="legendModalElem">Calling codes</div><div class="dataModalElem">' + arrayToStr(result.callingCodes," +") + '</div></div>');
  $("#mainInfoBody").append('<div class="rowElem"><div class="legendModalElem">Borders Share</div><div class="dataModalElem">' + arrayToStr(result.bordersShare," ") + '</div></div>');
 
  $("#demogrBody").append('<div class="rowElem"><div class="legendModalElem">Population</div><div class="dataModalElem">' + convertMillion(result.population) + '</div></div>');
  $("#demogrBody").append('<div class="rowElem"><div class="legendModalElem">Demonym</div><div class="dataModalElem">' + result.demonym + '</div></div>');
  $("#demogrBody").append('<div class="rowElem"><div class="legendModalElem">Languages</div><div class="dataModalElem">' + arrayToStr(result.languages," ") + '</div></div>');
  if(result.regionalBlocs.length > 0){
  $("#demogrBody").append('<div class="rowElem"><div class="legendModalElem">Regional Bloq</div><div class="dataModalElem">' + arrayToStr(result.regionalBlocs," ") + '</div></div>');
  }
  $("#demogrBody").append('<div class="rowElem"><div class="legendModalElem"><a href="https://en.wikipedia.org/wiki/Gini_coefficient">GINI</a></div><div class="dataModalElem">' + result.gini + '</div></div>');
  
  $("#weatherBody").append('<div class="rowElem weatherDescr"><div class="legendModalElem">' + result.weatherDescr + '</div><div class="dataModalElem">' + getWeatherIcon(result.icon) + '</div></div>');
  $("#weatherBody").append('<div class="rowElem"><div class="legendModalElem">Temp</div><div class="dataModalElem">' + result.temp + '°C</div></div>');
  $("#weatherBody").append('<div class="rowElem"><div class="legendModalElem">Feels like</div><div class="dataModalElem">' + result.feels_like + '°C</div></div>');
  $("#weatherBody").append('<div class="rowElem"><div class="legendModalElem">T Min</div><div class="dataModalElem">' + result.temp_min + '°C</div></div>');
  $("#weatherBody").append('<div class="rowElem"><div class="legendModalElem">T Max</div><div class="dataModalElem">' + result.temp_max + '°C</div></div>');
  $("#weatherBody").append('<div class="rowElem"><div class="legendModalElem">Pressure</div><div class="dataModalElem">' + result.pressure + ' mbar</div></div>');
  $("#weatherBody").append('<div class="rowElem"><div class="legendModalElem">Humidity</div><div class="dataModalElem">' + result.humidity + '%</div></div>');

  //Fisrt image the flag

  $("#imagesBody").append('<img class="rounded mx-auto d-block imageSpare" src=' + result.flagImage + '>')
  
  result.currency.forEach(element => {
    $("#currencyList").append('<div class="rowElem"><div class="legendModalElem">'+ element.code + ' ' + (element.symbol || "")  + '</div><div class="dataModalElem">' + element.name + '</div></div>')
  });
  

if(result.exchangeRates != "NoInfo"){
  $("#exchangeGroups").append('<hr/>')
  $("#exchangeGroups").append( '<div class=""><div class="legendModalElem">Exchange rates</div></div>')

 for (const property in result.exchangeRates) {
  if(result.exchangeRates[property]!="NoInfo"){
    var stringExchange = "";
    for (const currency in result.exchangeRates[property]) {
      stringExchange = stringExchange + ' ' + currency + ' ' + result.exchangeRates[property][currency] + ' ';
      //console.log(currency + ' ' + result.exchangeRates[property][currency]);
    }
    $("#exchangeGroups").append('<div class="rowElem"><div class="legendModalElem">'+ property + '</div><div class="dataModalElem">' + stringExchange + '</div></div>')
  }
}
}

//console.log(`${property}: ${object[property]}`);



result.images.forEach(element => {
  $("#imagesBody").append('<img class="rounded mx-auto d-block imageSpare" src=' + element + '>')
});


//No flag in header

//$(".modal-header").css("background-image",  "url('" + result.flagImage + "')");

//console.log(result.cities);

}



function findCountryCode(map,lat,lng){

  //console.log("Estamos en findCountryCode")

  $.ajax({
    url: "libs/php/getCodeLocation.php",
    type: 'GET',
    dataType: 'json',
    data: {
      lat: lat,
      lng: lng,
    },
    // we dont need it beforeSend: function(){},
    complete:function(){
      //console.log("Estamos en complete")
    },
    success: function(result) {  
      loadCountryAjax(map, result.data.codeA2 , result.data.index )
    },
    error: function(jqXHR, textStatus, errorThrown) {
        //console.log("Ajax error")
    }
  });


}






function loadCountryAjax(map, code, index){

  //es un nuevo pais asi que necestio su codigo y su indice

  //codeA2 bordIndex

  //console.log("Estamos en loadCountry")

  //console.log(code+ " " + index)
  

  $.ajax({
    url: "libs/php/getInfo.php",
    type: 'GET',
    dataType: 'json',
    data: {
      codeA2: code,
      bordIndex: index,
    },
    beforeSend: function(){
      //console.log("Estamos en beforeSend")

      map.spin(true);
      //$("#map").hide();


     },
    complete:function(){ //after success and error callbacks are executed
      //console.log("Estamos en complete")
    },
    success: function(result) {  
      $("#map").show();
      map.spin(false);
      //console.log("Estamos en success")
      showResult(result)

      //layer management

      map.eachLayer(function (layer) {
        
        if((layer.options.id) && (layer.options.id=="countryBounds")){
          map.removeLayer(layer);
        }

        if((layer.options.id) && (layer.options.id=="countryCities")){
          map.removeLayer(layer);
        }

      });

      console.log(result.citiesGeoJOSN)

      
      /*
var markersOptions ={
  'id': 'countryCities',
  //'type': 'symbol',
  //'source': 'points',
  'layout': {
    //'icon-image': 'custom-marker',
    'text-field': ['get', 'name'],
    'text-font': [
      'Open Sans Semibold',
      'Arial Unicode MS Bold'
    ],
      'text-offset': [0, 1.25],
      'text-anchor': 'top'
    }
  };*/
    
      //var countrycities = new L.geoJSON(result.citiesGeoJOSN, markersOptions).addTo(map);

      var citiIcon = L.icon({
        iconUrl: './icons/marker-icon.png',
        iconSize: [32, 37],
        iconAnchor: [16, 37],
        popupAnchor: [0, -28]
      });

      var countrycities = L.geoJSON(result.citiesGeoJOSN, {

        'id': 'countryCities',

        pointToLayer: function (feature, latlng) {
          return L.marker(latlng, {icon: citiIcon});
        },
    
        onEachFeature: onEachFeature
      }).addTo(map);


      var countryBounds = L.geoJSON(result.borderGeo, {id: "countryBounds"}).addTo(map);
      map.flyToBounds(countryBounds.getBounds(),{duration: 2});

    },
    error: function(jqXHR, textStatus, errorThrown) {
        //console.log("Ajax error")
    }


  });


}

//declaremap
var mymap = L.map('map',{
  minZoom: 1,
  maxZoom: 20
}).setView([55.669, 37.692], 1)



//for the dropdown selection
function loadNewMap(codeAlpha2,indexCountry){
  loadCountryAjax(mymap,codeAlpha2,indexCountry)
}


$( document ).ready(function() {

  var countriesList = feedCountryList();

  L.tileLayer('https://api.mapbox.com/styles/v1/{id}/tiles/{z}/{x}/{y}?access_token={accessToken}', {
    attribution: 'Map data &copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors, Imagery © <a href="https://www.mapbox.com/">Mapbox</a>',
    minZoom: 2,
    maxZoom: 18,
    id: 'mapbox/streets-v11',
    tileSize: 512,
    zoomOffset: -1,
    accessToken: 'pk.eyJ1IjoibWFwYm94IiwiYSI6ImNpejY4NXVycTA2emYycXBndHRqcmZ3N3gifQ.rJcFIG214AriISLbB6B5aw'
}).addTo(mymap);


  //loadCountryAjax(mymap,"PA", 19)


  
  if (navigator.geolocation) {
      navigator.geolocation.getCurrentPosition(goToPosition);
  
  } else {
      $('#map').html("NO INFO ABOUT THIS PARTICULAR PLACE");
  }

  function goToPosition(position){
    findCountryCode(mymap, position.coords.latitude, position.coords.longitude)
  }



  //L.easyButton('<img src="https://pdxcyclesafetymap.neocities.org/images/blackSkull.svg" style="width:16px">'

  L.easyButton( '<img src="./icons/info.svg" style="width:16px">', 
  function(){
    $("#mainInfo").modal();
  }
  ).addTo(mymap);

  L.easyButton( '<img src="./icons/demogr.svg" style="width:16px">', 
  function(){
    $("#demogr").modal();       
  }
  ).addTo(mymap);

  L.easyButton( '<img src="./icons/weather.svg" style="width:16px">', 
  function(){
    $("#weather").modal();
  }
  ).addTo(mymap);

  L.easyButton( '<img src="./icons/currency.svg" style="width:16px">', 
  function(){
    $("#money").modal();
  }
  ).addTo(mymap);

  L.easyButton( '<img src="./icons/images.svg" style="width:16px">', 
  function(){
    $("#images").modal();
  }
  ).addTo(mymap);

})

<?php

//

$keyOpenCage = "fe2723162ce84c1fbd05aaee7e378b8c";
$keyOpenWeather = "ce03983a8cf0ae7af0871cb332cd7c31";
$keyImages = "19545147-7a8005508a3f71df5e184cc01";


//https://api.opencagedata.com/geocode/v1/json?q=US&key=fe2723162ce84c1fbd05aaee7e378b8c

//weather at the capital: api.openweathermap.org/data/2.5/weather?q=London,uk&appid={API key}

$countryCode = $_REQUEST['codeA2'];

$borderIndex = $_REQUEST['bordIndex'];

  //get borders

  $countryBorders = file_get_contents("../countryBorders.geo.json");

  $countryBordersArray = json_decode($countryBorders);

  $output['borderGeo'] = $countryBordersArray->features[$borderIndex]->geometry;
  $output['nameBorder'] = $countryBordersArray->features[$borderIndex]->properties->name;

//Rest country

$urlRestCountries = "https://restcountries.eu/rest/v2/alpha?codes=". $countryCode;

$sessionRestCountries = curl_init();

curl_setopt($sessionRestCountries, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($sessionRestCountries, CURLOPT_RETURNTRANSFER, true);	
curl_setopt($sessionRestCountries, CURLOPT_URL, $urlRestCountries);
curl_setopt($sessionRestCountries, CURLOPT_FAILONERROR, true); // Required for HTTP error codes to be reported via our call to curl_error($ch)

$resultRestCountries = curl_exec($sessionRestCountries);

$resultRestCountriesArray = json_decode($resultRestCountries,true);

if(curl_errno($sessionRestCountries)){

  $output['error'] = 'Request Error Rest Countries:' . curl_error($sessionRestCountries);

}else{

  $output['nameRest'] = $resultRestCountriesArray[0]['name'];
  $output['callingCodes'] = $resultRestCountriesArray[0]['callingCodes'];
  $output['capital'] = $resultRestCountriesArray[0]['capital'];
  $output['altSpellings'] = $resultRestCountriesArray[0]['altSpellings'];

  $output['region'] = $resultRestCountriesArray[0]['region'];
  $output['subregion'] = $resultRestCountriesArray[0]['subregion'];
  $output['population'] = $resultRestCountriesArray[0]['population'];


  $output['demonym'] = $resultRestCountriesArray[0]['demonym'];
  $output['area'] = $resultRestCountriesArray[0]['area'];
  $output['gini'] = $resultRestCountriesArray[0]['gini']  ?? 'NoGini';

  //$output['timezones'] = $resultRestCountriesArray[0]['timezones'];

  $output['timezones'] = [];

  foreach($resultRestCountriesArray[0]['timezones'] as $time){

    if(strlen($time)>4){
      array_push($output['timezones'], substr($time, 3));
    }
  }

  $output['bordersShare'] = $resultRestCountriesArray[0]['borders'];

  $output['currency'] = $resultRestCountriesArray[0]['currencies'];
  $output['languages'] = [];

  foreach($resultRestCountriesArray[0]['languages'] as $lang){
    array_push($output['languages'],$lang['name']);
    //echo json_encode($lang['name']);
  }

  $output['flagImage'] = $resultRestCountriesArray[0]['flag'];
  $output['regionalBlocs'] = $resultRestCountriesArray[0]['regionalBlocs'];

  $output['regionalBlocs'] = [];

  foreach($resultRestCountriesArray[0]['regionalBlocs'] as $bloc){
    array_push($output['regionalBlocs'],$bloc['name']);
  }

}



if(isset($output['nameBorder']) && isset($output['capital'])){

  $urlOpenWeather = "api.openweathermap.org/data/2.5/weather?&units=metric&q=". $output['capital'] .",". $countryCode ."&appid=".$keyOpenWeather;

  $sessionOpenWeather = curl_init();

  curl_setopt($sessionOpenWeather, CURLOPT_SSL_VERIFYPEER, false);
  curl_setopt($sessionOpenWeather, CURLOPT_RETURNTRANSFER, true);	
  curl_setopt($sessionOpenWeather, CURLOPT_URL, $urlOpenWeather);
  curl_setopt($sessionOpenWeather, CURLOPT_FAILONERROR, true); // Required for HTTP error codes to be reported via our call to curl_error($ch)

  $resultOpenWeather = curl_exec($sessionOpenWeather);

  $resultOpenWeatherArray = json_decode($resultOpenWeather,true);

  if(curl_errno($sessionOpenWeather)){

    $output['error'] = 'Request Error Open Weather:' . curl_error($sessionOpenWeather);

  }else{

    $output['weatherDescr'] = $resultOpenWeatherArray['weather'][0]['description'];
    $output['icon'] = $resultOpenWeatherArray['weather'][0]['icon'];
    $output['temp'] = $resultOpenWeatherArray['main']['temp'];
    $output['feels_like'] = $resultOpenWeatherArray['main']['feels_like'];  
    $output['temp_min'] = $resultOpenWeatherArray['main']['temp_min'];
    $output['temp_max'] = $resultOpenWeatherArray['main']['temp_max'];
    $output['pressure'] = $resultOpenWeatherArray['main']['pressure'];  
    $output['humidity'] = $resultOpenWeatherArray['main']['humidity'];
  }

  curl_close($sessionOpenWeather);


}else{
  $output['error'] = 'Request Error lack of name or capital:' . curl_error($sessionRestCountries);
}


//currencies

if((isset($output['currency'])) && (count($output['currency']) > 0)){

  $output['exchangeRates'] = [];

  $exchangeOff = true;

  foreach ($output['currency'] as $value) {

    $urlExchangeRate = "https://api.exchangeratesapi.io/latest?base=".$value['code'];

    //echo "URL exchange: " . $urlExchangeRate . "<br>";

      $sessionExchangeRate = curl_init();

      curl_setopt($sessionExchangeRate, CURLOPT_SSL_VERIFYPEER, false);
      curl_setopt($sessionExchangeRate, CURLOPT_RETURNTRANSFER, true);	
      curl_setopt($sessionExchangeRate, CURLOPT_URL, $urlExchangeRate);
      curl_setopt($sessionExchangeRate, CURLOPT_FAILONERROR, true); // Required for HTTP error codes to be reported via our call to curl_error($ch)

      $resultExchangeRate = curl_exec($sessionExchangeRate);

      $resultExchangeRateArray = json_decode($resultExchangeRate,true);

      if(curl_errno($sessionExchangeRate)){
      
        $output['exchangeRates'][$value['code']]= "NoInfo";
        //echo 'Request Error Exchange Rate:' . curl_error($sessionExchangeRate);

      }else{ //$object->newPropery = 'value';

        $exchangeOff = false;
        $output['exchangeRates'][$value['code']]=[];
        if($value['code'] != "USD" ){
        $output['exchangeRates'][$value['code']]["USD"] = round($resultExchangeRateArray['rates']["USD"], 3);
        }

        if($value['code'] != "EUR" ){
          if (isset($resultExchangeRateArray['rates']["EUR"])){
            $output['exchangeRates'][$value['code']]["EUR"] = round($resultExchangeRateArray['rates']["EUR"], 3);
          }else{
            $output['exchangeRates'][$value['code']]["EUR"] = 1;
          }
        }

        if($value['code'] != "GBP" ){
        $output['exchangeRates'][$value['code']]["GBP"] = round($resultExchangeRateArray['rates']["GBP"], 3 );
        }
      }

    curl_close($sessionExchangeRate);
    }
    
    if($exchangeOff){
      $output['exchangeRates']="NoInfo";
    }

  }



  //images
  $imageThemes = array("+art+culture","&category=people","&category=travel","&category=animals","&category=food","&category=fashion");

  $sessionImages = curl_init();

  curl_setopt($sessionImages, CURLOPT_RETURNTRANSFER, true);

  $output['images'] = [];
  $imagesIds =[];

  foreach($imageThemes as $theme){

    $imagesURL = "https://pixabay.com/api/?key=". $keyImages . "&per_page=3&q=" . $output['nameBorder'] . $theme;
    curl_setopt($sessionImages, CURLOPT_URL, $imagesURL);

    $resultImages = curl_exec($sessionImages);
    $resultImagesArray = json_decode($resultImages,true);

    //echo $resultImages;

    //CUidado cuando no viene ninguna imagen y si pillamos varias de las que vienen?
    //echo $resultImages. "<br>";

    $hits = 3;
    //there are better operators
    if($resultImagesArray["total"]<3){
      $hits = $resultImagesArray["total"];
    }

    for ($x = 0; $x < $hits; $x++) {
      $newImg = $resultImagesArray["hits"][$x]["webformatURL"];
      $newImgID = $resultImagesArray["hits"][$x]["id"];
      if (!in_array($newImgID, $imagesIds))
      //mirar que tengan distinto ID
      {
        array_push($output['images'],$newImg);
        array_push($imagesIds,$newImgID);
      }
    } 

    //echo '<img src="'.  $resultImagesArray["hits"][$x]["webformatURL"] .'" width="100" height="100">';
  }

  curl_close($sessionImages);

  //https://public.opendatasoft.com/api/records/1.0/search/?dataset=geonames-all-cities-with-a-population-1000&q=&rows=12&sort=population&refine.country_code=US

  $urlCities = "https://public.opendatasoft.com/api/records/1.0/search/?dataset=geonames-all-cities-with-a-population-1000&q=&rows=12&sort=population&refine.country_code=". $countryCode;

  $sessionCities = curl_init();

  curl_setopt($sessionCities, CURLOPT_SSL_VERIFYPEER, false);
  curl_setopt($sessionCities, CURLOPT_RETURNTRANSFER, true);	
  curl_setopt($sessionCities, CURLOPT_URL, $urlCities);
  curl_setopt($sessionCities, CURLOPT_FAILONERROR, true); // Required for HTTP error codes to be reported via our call to curl_error($ch)

  $resultCities = curl_exec($sessionCities);

  $resultCitiesArray = json_decode($resultCities,true);

  if(curl_errno($sessionCities)){

    $output['error'] = 'Request Error Rest Countries:' . curl_error($sessionCities);

  }else{

    $cityList = $resultCitiesArray["records"];

    $outputCityGeoJSONList = [];

    foreach($cityList as $city){

      $newGeoJSON = (object) [
        "type" => "Feature"];

      $newGeoJSON->geometry = $city["geometry"];
      $newGeoJSON->properties["name"] = $city["fields"]["name"];
      $newGeoJSON->properties["population"]  = $city["fields"]["population"];

      array_push($outputCityGeoJSONList, $newGeoJSON);
      //echo gettype($newGeoJSON);

    }

    $output['citiesGeoJOSN'] = $outputCityGeoJSONList;
    //echo json_encode($outputCityGeoJSONList);

}

echo json_encode($output);

?>
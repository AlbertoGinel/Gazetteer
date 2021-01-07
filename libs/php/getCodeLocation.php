<?php

$keyOpenCage = "fe2723162ce84c1fbd05aaee7e378b8c";

//I just want the ISO code of the country, if I cnt have it Ill

$urlOpenCage = "https://api.opencagedata.com/geocode/v1/json?q=" . $_REQUEST['lat'] . "%2C" . $_REQUEST['lng'] . "&key=" . $keyOpenCage . "&language=en&pretty=1";

$sessionOpenCage = curl_init();

curl_setopt($sessionOpenCage, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($sessionOpenCage, CURLOPT_RETURNTRANSFER, true);	
curl_setopt($sessionOpenCage, CURLOPT_URL, $urlOpenCage);
curl_setopt($sessionOpenCage, CURLOPT_FAILONERROR, true); // Required for HTTP error codes to be reported via our call to curl_error($ch)

$resultOpenCage = curl_exec($sessionOpenCage);

$resultOpenCageArray = json_decode($resultOpenCage,true);

$output['status']['code'] = "200";

//I need the index from location






if (in_array("ISO_3166-1_alpha-2",  array_keys($resultOpenCageArray['results'][0]['components']))) {
  $output['status']['name'] = "userLocationOk";
  $output['data']['codeA2'] = $resultOpenCageArray['results'][0]['components']['ISO_3166-1_alpha-2'];
  $output['data']['codeA3'] = $resultOpenCageArray['results'][0]['components']['ISO_3166-1_alpha-3'];
  $output['data']['lat'] = $_REQUEST['lat'];
  $output['data']['lng'] = $_REQUEST['lng'];

  //get borderIndex in the geoJson

  $countryBorders = file_get_contents("../countryBorders.geo.json");

  $countryBordersArray = json_decode($countryBorders);

  foreach($countryBordersArray->features as $key => $feature) {

      if ($output['data']['codeA2'] == $feature->properties->iso_a2) {
          $output['data']['index'] = $key;
          break;
      }
  }

}else{
  //defaut location in London
  $output['status']['name'] = "userFailed";
  $output['data']['codeA2'] = "GB";
  $output['data']['codeA3'] = "GBR";
  $output['data']['lat'] = "51.50718904769748";
  $output['data']['lng'] = "-0.07544754315766225";
  $output['data']['index'] = "145";
}

curl_close($sessionOpenCage);


//London 51.50718904769748, -0.07544754315766225

echo json_encode($output);

?>
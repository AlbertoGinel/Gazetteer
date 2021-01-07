<?php

//Im getting the index so I dont have to look for it later

$countryBorders = file_get_contents("../countryBorders.geo.json");

$countryBordersArray = json_decode($countryBorders);

$output = [];

foreach($countryBordersArray->features as $key => $feature) {

    $elem["name"] = $feature->properties->name;
    $elem["iso_a2"] = $feature->properties->iso_a2;
    $elem["iso_a3"] = $feature->properties->iso_a3;
    $elem["index"] = $key;

    $output[$key] = $elem;    
}

//return just a list o countries for the dropdown
echo json_encode($output);

//use the index to retrieve the borders echo json_encode($countryBordersArray->features[49]);

?>
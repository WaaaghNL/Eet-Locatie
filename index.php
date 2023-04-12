<?php
// Initialize cURL
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'https://www.waaagh.nl/api.php?format=json&action=query&titles=Restaurants_ToDo&prop=revisions&rvprop=content');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

// Execute cURL request
$get = curl_exec($ch);

// Check for cURL errors
if(curl_errno($ch)){
    // Handle error
    die("Oh no! The url was not valid!");
}

// Close cURL
curl_close($ch);


$json = json_decode($get, true);

$json = $json["query"]["pages"][812]["revisions"][0]["*"];

$json = str_ireplace('Naast een lijst van goede restaurantjes heb ik natuurlijk ook nog een ToDo lijst met aanraders van derden. [https://eetlocatie.waaagh.nl/ Laat de dino je helpen om een keuze te maken!]',null,$json);
$json = trim($json);

$array = preg_replace('/(===.+?)+(===)/i', null, $json); //Verwijder Tabel
$array = preg_replace('/(==.+?)+(==)/i', null, $array); //Verwijder Tabel
$array = explode("*", $array);
foreach ($array as $key => $value){
    $data = trim($array[$key]);
    if(!empty($data)){
        $output[$key] = $data;
    }
}
shuffle($output); //Random output
$count = rand(1, count($output));

$url = $output[$count];
$name = $url;
$name = str_replace("https://", "", $name);
$name = str_replace("http://", "", $name);
$name = str_replace("www.", "", $name);
if(substr($name, -1) == '/') {
    $name = substr($name, 0, -1);
}

$homepage_url =  "//{$_SERVER['HTTP_HOST']}{$_SERVER['REQUEST_URI']}";
$escaped_url = htmlspecialchars( $homepage_url, ENT_QUOTES, 'UTF-8' );
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Waaagh.nl - Eet Locatie Generator</title>
    <link rel="shortcut icon" href="favicon.ico">
    <link rel="stylesheet" type="text/css" href="style.css" >
</head>
<body>

<div id="header">
	<div id="header-content"></div>
</div>
<div id="main">
    <div id="main-content">
        <div id="password-panel">
            <div id="password-panel-message">Wij gaan eten bij...</div>
        	<div id="password"><a id="password-input" href="<?=$output[$count];?>"><?=$name;?></a></div>
        	<div id="password-panel-reset">
        	    <a class="gradient-button gradient-button-TEST" href="<?=$escaped_url;?>">Andere Locatie</a>
        	</div>
        	
        </div>
    
        <div id="about-passwords" class="content">
        	<h2>Over de Eet Locatie Generator</h2>
        	<h3>Wat is de ELG?</h3>
        	<p>De eet locatie generator is een project van Waaagh.nl en haalt links uit het overzicht van restaurants die de Smullertjes nog willen uit proberen. Deze lijst wordt door de dino in een bak met lootjes gegooid en daarna trekt hij er een uit.</p>
        
        	<h3>Waar kan ik de hele lijst zien?</h3>
        	<p>De hele lijst die in de ELG gaat is te vinden op de volgende webpagina: <a href="https://www.waaagh.nl/Restaurants_ToDo" target="_blank">Waaagh.nl - Restaurants TODO</a></p>
        </div>
    </div>
</div>

<div id="footer">
  <p>
	<a href="https://www.waaagh.nl">  &copy; <?=date('Y');?> Waaagh.nl All right reserved</a> | <a href="https://github.com/WaaaghNL/Eet-Locatie">Github Page</a>
  </p>
</div>

</body>
</html>

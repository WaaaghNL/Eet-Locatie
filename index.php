<?php
//Config
$wikiURL = 'https://www.waaagh.nl/api.php?format=json&action=query&titles=Restaurants_ToDo&prop=revisions&rvprop=content';

// Initialize cURL
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $wikiURL);
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

if(count($json["query"]["pages"]) != 1){
    die('To many pages to chose from! check the url!');
}

foreach ($json["query"]["pages"] AS $key => $value){
    $json = $json["query"]["pages"][$key]["revisions"][0]["*"];
}


$json = preg_split("/\r\n|\n|\r/", $json);
$output = array();
foreach($json as $line){
    $line = trim($line);
    if(strpos($line, '*') === 0) { //Show only lines staring with *
        $line = str_replace("*", "", $line); //Remove star
        $line = trim($line); //Remove spaces and linebreaks                
                
        //Clean up for title
        $cleanup = str_replace("https://", "", $line);
        $cleanup = str_replace("http://", "", $cleanup);
        $cleanup = str_replace("www.", "", $cleanup);
                
        $cleanup = explode("/", $cleanup); //Remove everything after and including the first /
        
		$return = array();
        $return['title'] = $cleanup[0];                
        $return['url'] = $line;
        
        $output[] = $return;
    }
}

shuffle($output); //Random output

$homepage_url =  "//{$_SERVER['HTTP_HOST']}";
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
<div id="container">
    <div id="header">
        <div id="header-content"></div>
    </div>

    <div id="main">
        <div id="main-content">
            <div id="speech-panel">
                <div id="speech-panel-message">Wij gaan eten bij...</div>
                <div id="speech"><a id="speech-input" target="_blank" href="<?=$output[0]['url'];?>"><?=$output[0]['title'];?></a></div>
                <div id="speech-panel-reset">
                    <a class="gradient-button gradient-button-TEST" href="<?=$escaped_url;?>">Andere Locatie</a>
                </div>
            </div>
            <div class="content">
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
            <a href="https://www.waaagh.nl">  &copy; 2023 - <?=date('Y');?> Waaagh.nl All right reserved</a> | <a href="https://github.com/WaaaghNL/Eet-Locatie">Github Page</a>
        </p>
    </div>
</div>
</body>
</html>

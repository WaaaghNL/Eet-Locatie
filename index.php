<?php
//Config
$wikiURL = 'https://www.waaagh.nl/api.php?format=json&action=query&titles=Restaurants_ToDo&prop=revisions&rvprop=content';
$debug = false;

$cacheInMinutes = 2;
$cacheFile = 'cache.json';

//Don't edit below!
//Don't edit below!
if($debug or isset($_GET['debug'])){
    $debugMode=true;
}

if($debugMode){
    echo '<h1 style="font-size: 2em;color: red; font-weight: bold;">Debugging mode active!</h1>';
}

if(isset($_GET['force'])){
    //Force a location
    $location=array();
    $location['title']='Waaagh.nl';
    $location['url']='https://waaagh.nl';
    
    if($debugMode){
        echo '<h2 style="font-size: 1.5em;color: darkorange; font-weight: bold;">Forcing a location</h2>';
        echo "<pre>";
        print_r($location);
        echo "</pre><hr />";
    }
}
else{
    //Caching Module
    $cacheFile = getcwd().'/'.$cacheFile;
    if (file_exists($cacheFile) && (filemtime($cacheFile) > (time() - 60 * $cacheInMinutes )) && !isset($_GET['noCache'])) {
        //Get data from cache
        $file = file_get_contents($cacheFile);
        $location = json_decode($file, true);
       
        if($debugMode){
            echo '<h2 style="font-size: 1.5em;color: darkorange; font-weight: bold;">Using Cache</h2>';
            echo "<pre>";
            print_r($location);
            echo "</pre><hr />";
        }
    }
    else {
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
        
        if($debugMode){
            echo '<h2 style="font-size: 1.5em;color: darkorange; font-weight: bold;">Getting data from website</h2>';
            echo "<pre>";
            print_r($json);
            echo "</pre><hr />";
        }        
        //------------------------------------------------------------------------------
		
        if(count($json["query"]["pages"]) != 1){
            die('To many pages to chose from! check the url!');
        }
        
        foreach ($json["query"]["pages"] AS $key => $value){
            $json = $json["query"]["pages"][$key]["revisions"][0]["*"];
        }
        
        if($debugMode){
            echo '<h2 style="font-size: 1.5em;color: darkorange; font-weight: bold;">Select only content</h2>';
            echo "<pre>";
            print_r($json);
            echo "</pre><hr />";
        }
        //------------------------------------------------------------------------------
		
        $array = preg_split("/\r\n|\n|\r/", $json);
        
        if($debugMode){
            echo '<h2 style="font-size: 1.5em;color: darkorange; font-weight: bold;">Split JSON into array</h2>';
            echo "<pre>";
            print_r($array);
            echo "</pre><hr />";
        }        
        //------------------------------------------------------------------------------
		
        $returnLocations = array();
        foreach($array as $line){
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
                
                $returnLocations[] = $return;
            }
        }
        
        if($debugMode){
            echo '<h2 style="font-size: 1.5em;color: darkorange; font-weight: bold;">Clean Up Locations</h2>';
            echo "<pre>";
            print_r($returnLocations);
            echo "</pre><hr />";
        }
        //------------------------------------------------------------------------------
		
        shuffle($returnLocations); //Random output
        
        if($debugMode){
            echo '<h2 style="font-size: 1.5em;color: darkorange; font-weight: bold;">Shuffel Locations</h2>';
            echo "<pre>";
            print_r($returnLocations);
            echo "</pre><hr />";
        }
        //------------------------------------------------------------------------------
		
        if(count($returnLocations)==0){
            $location = array("title"=>"Not found, check wiki url!", "url" => $wikiURL);
        }
        else{
            $location = $returnLocations[0];
        }        
        if($debugMode){
            echo '<h2 style="font-size: 1.5em;color: darkorange; font-weight: bold;">Select the first location</h2>';
            echo "<pre>";
            print_r($location);
            echo "</pre><hr />";
        }
        //------------------------------------------------------------------------------
		
        $json = json_encode($location);
        file_put_contents($cacheFile, $json, LOCK_EX);
        if($debugMode){
            $file = file_get_contents($cacheFile);
            $location = json_decode($file, true);
            echo '<h2 style="font-size: 1.5em;color: darkorange; font-weight: bold;">Check if cache is set</h2>';
            echo "<pre>";
            print_r($location);
            echo "</pre><hr />";
        }
		//------------------------------------------------------------------------------
    }
}
     
//------------------------------------------------------------------------------
$homepage_url =  "//{$_SERVER['HTTP_HOST']}";
$escaped_url = htmlspecialchars( $homepage_url, ENT_QUOTES, 'UTF-8' );
?>
<!DOCTYPE html>
<html lang="nl">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1" />
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
                    	<div id="speech"><a id="speech-input" target="_blank" href="<?=$location['url'];?>"><?=$location['title'];?></a></div>
                    	<div id="speech-panel-reset">
                    	    <?php
                    	    if(isset($_GET['noCache'])){
                    	        ?>
                    	        <a class="gradient-button gradient-button-TEST" href="<?=$escaped_url;?>?noCache">Andere Locatie</a>
                    	        <?php
                    	    }
                    	    ?>
                    	</div>
                    </div>
                    <div class="content">
                    	<h2>Over de Eet Locatie Generator</h2>
                    	<h3>Wat is de ELG?</h3>
                    	<p>De eet locatie generator is een project van Waaagh.nl en haalt links uit het overzicht van restaurants die de Smullertjes nog willen uit proberen. Deze lijst wordt door de dino in een bak met lootjes gegooid en daarna trekt hij er een uit.</p>
                    	<h3>Waar kan ik de hele lijst zien?</h3>
                    	<p>De hele lijst die in de ELG gaat is te vinden op de volgende webpagina: <a href="https://www.waaagh.nl/Restaurants_ToDo" target="_blank">Waaagh.nl - Restaurants TODO</a></p>
            
                        <h3>Ik krijg niets nieuws!</h3>
                        <?php
                        $timer = ((filemtime($cacheFile) - (time() - 60 * $cacheInMinutes )) > 0) ? (filemtime($cacheFile) - (time() - 60 * $cacheInMinutes )) : 120;
                        ?>
                        <p>Onze dino is behoorlijk standvast, als hij iets aanraad gaat hij er ook voor. Het duurt nog <?=$timer;?> secoden voor je hem kan overtuigen voor een nieuw adresje! <a href="<?=$escaped_url;?>?noCache" style="color:#f5f5f5;">Over rule de dino</a></p>
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
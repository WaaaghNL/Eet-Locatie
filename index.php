<?php
//Config
$MarkdownFile['todo']     = 'https://raw.githubusercontent.com/WaaaghNL/Restaurants/main/ToDo.md';
$MarkdownFile['aanrader'] = 'https://raw.githubusercontent.com/WaaaghNL/Restaurants/main/Aanraders.md';
$debug = false;

$cacheInMinutes = 5;
$cacheFile = 'cache-todo.json';

//Don't edit below!
//Don't edit below!
//Don't edit below!
//Don't edit below!
//Don't edit below!
if($debug or isset($_GET['debug'])){
  $debugMode=true;
}
else{
  $debugMode=false;
}

if($debugMode){
  echo '<h1 style="font-size: 2em;color: red; font-weight: bold;">Debugging mode active!</h1>';
}

function cleanup($url){
  $cleanup_https = str_replace("https://", "", $url);
  $cleanup_http = str_replace("http://", "", $cleanup_https);
  $cleanup_www = str_replace("www.", "", $cleanup_http);

  $cleanup_slash = explode("/", $cleanup_www); //Remove everything after and including the first /

  $return = array();
  $return['title'] = $cleanup_slash[0];
  $return['url'] = $url;

  return $return;
}

function random_location($MarkdownFile, $debug=false){
  $ch = curl_init();
  curl_setopt($ch, CURLOPT_URL, $MarkdownFile);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

  // Execute cURL request
  $get = curl_exec($ch);

  // Check for cURL errors
  if(curl_errno($ch)){
    die("Oh no! The url was not valid!");
  }

  // Close cURL
  curl_close($ch);

  if($debugMode){
    echo '<h2 style="font-size: 1.5em;color: darkorange; font-weight: bold;">Getting data from website</h2>';
    echo "<pre>";
    print_r($get);
    echo "</pre><hr />";
  }

  $array = preg_split("/\r\n|\n|\r/", $get);

  if($debugMode){
    echo '<h2 style="font-size: 1.5em;color: darkorange; font-weight: bold;">Split JSON into array</h2>';
    echo "<pre>";
    print_r($array);
    echo "</pre><hr />";
  }

  $returnLocations = array();
  foreach($array as $line){
    $line = trim($line);
    if(strpos($line, '*') === 0) { //Show only lines staring with *
      $line = str_replace("*", "", $line); //Remove star
      $line = trim($line); //Remove spaces and linebreaks

      //Clean up for title
      $return = cleanup($line);

      $returnLocations[] = $return;
    }
  }

  if($debugMode){
    echo '<h2 style="font-size: 1.5em;color: darkorange; font-weight: bold;">Clean Up Locations</h2>';
    echo "<pre>";
    print_r($returnLocations);
    echo "</pre><hr />";
  }

  shuffle($returnLocations); //Random output

  if($debugMode){
    echo '<h2 style="font-size: 1.5em;color: darkorange; font-weight: bold;">Shuffel Locations</h2>';
    echo "<pre>";
    print_r($returnLocations);
    echo "</pre><hr />";
  }


  if(count($returnLocations)==0){
    $location = array("title"=>"Not found, check url!", "url" => $MarkdownFile);
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

  return $location;
}

if(isset($_GET['type']) AND $_GET['type'] == "force" AND isset($_GET['url'])){
  //Force a location
  $location=cleanup($_GET['url']);

  if($debugMode){
    echo '<h2 style="font-size: 1.5em;color: darkorange; font-weight: bold;">Forcing a location</h2>';
    echo "<pre>";
    print_r($location);
    echo "</pre><hr />";
  }
}
elseif(isset($_GET['type']) AND $_GET['type'] == "aanrader"){
  $MarkdownFile = $MarkdownFile['aanrader'];
  $location = random_location($MarkdownFile, $debug);
}
elseif(isset($_GET['type']) AND $_GET['type'] == "todo"){
  $MarkdownFile = $MarkdownFile['todo'];

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
    $location = random_location($MarkdownFile, $debug);

    $json = json_encode($location);
    file_put_contents($cacheFile, $json, LOCK_EX);
    if($debugMode){
      $file = file_get_contents($cacheFile);
      $location_file = json_decode($file, true);
      echo '<h2 style="font-size: 1.5em;color: darkorange; font-weight: bold;">Check if cache is set</h2>';
      echo "<pre>";
      print_r($location_file);
      echo "</pre><hr />";
    }
  }
}
else{
  //Niks gekozen
}
$homepage_url =  "//{$_SERVER['HTTP_HOST']}";
$escaped_url = htmlspecialchars( $homepage_url, ENT_QUOTES, 'UTF-8' );

if($debugMode){
  echo "Eindresultaat: href=".htmlspecialchars($location['url'], ENT_QUOTES, 'UTF-8'). "Title= ".htmlspecialchars($location['title'], ENT_QUOTES, 'UTF-8');
}
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Waaagh.nl - Eet Locatie Generator</title>
    <link rel="shortcut icon" href="./assets/favicon.ico">
    <link rel="stylesheet" type="text/css" href="./assets/reset-min.css">
    <link rel="stylesheet" type="text/css" href="./assets/style.css" >
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@200..1000&display=swap" rel="stylesheet">
  </head>
  <body>
    <div class="container">
      <main id="main">
        <div id="password-panel" class="hero">
          <div class="hero__image object-fit-cover">
            <img src="./assets/dino.png" alt="dino" width="400" height="400" loading="eager" fetchpriority="high">
          </div>
          <div class="hero__content" id="heroСontent">
            <?php
            if(isset($_GET['type'])){
              ?>
              <div id="password-panel-message">Wij gaan eten bij...</div>
              <div id="password" class="password">
                <div class="password-input__wrapper">
                  <span class="password-input">
                    <a href="<?=htmlspecialchars($location['url'], ENT_QUOTES, 'UTF-8');?>" target="_blank"><?=htmlspecialchars($location['title'], ENT_QUOTES, 'UTF-8');?></a>
                  </span>
                </div>
              </div>
              <?
            }
            else{
              ?>
              <div id="password-panel-message">Wat wil je proberen?!</div>
              <?php
            }
            ?>
            <ul class="hero__btns-group">
              <?php
              if(isset($_GET['type']) AND $_GET['type'] == "aanrader"){
                ?>
                <li class="simple">
                  <a href="<?=$escaped_url;?>/" target="_self" class="btn-primary" id="simple-button">
                    Ga terug
                  </a>
                </li>
                <li class="strong">
                  <a href="<?=$escaped_url;?>/?type=aanrader" target="_self" class="btn-secondary" id="strong-button">
                    Nee, liever een andere
                  </a>
                </li>
                <?
              }
              elseif(isset($_GET['type']) AND $_GET['type'] == "todo"){
                ?>
                <li class="simple">
                  <a href="<?=$escaped_url;?>/" target="_self" class="btn-primary" id="simple-button">
                    Ga terug
                  </a>
                </li>
                <?php
                if(isset($_GET['noCache'])){
                  ?>
                  <li class="strong">
                    <a href="<?=$escaped_url;?>/?type=todo&noCache" target="_self" class="btn-secondary" id="strong-button">
                      Kies een andere
                    </a>
                  </li>
                  <?
                }
              }
              else{
                ?>
                <li class="simple">
                  <a href="<?=$escaped_url;?>/?type=aanrader" target="_self" class="btn-primary" id="simple-button">
                    Een aanrader!
                  </a>
                </li>
                <li class="strong">
                  <a href="<?=$escaped_url;?>/?type=todo" target="_self" class="btn-secondary" id="strong-button">
                    een todo restaurant!
                  </a>
                </li>
                <?
              }
              ?>
            </ul>
          </div>
        </div>

        <section id="about-passwords" class="about-passwords content">
          <div class="about-passwords__wrapper">
            <h4 class="about-passwords__title">Over de Eet Locatie Generator</h4>
            <ul class="about-passwords__list">
              <li class="about-passwords__item about-passwords-item">
                <h5 class="about-passwords-item__title">Wat is de ELG?</h5>
                <p class="about-passwords-item__text">
                  De eet locatie generator is een project van Waaagh.nl en haalt links uit het overzicht van restaurants die de Smullertjes nog willen uit proberen. Deze lijst wordt door de dino in een bak met lootjes gegooid en daarna trekt hij er een uit.
                </p>
              </li>
              <li class="about-passwords__item about-passwords-item">
                <h5 class="about-passwords-item__title">Waar kan ik de hele lijst zien?</h5>
                <p class="about-passwords-item__text">
                  De hele lijst die in de ELG gaat is te vinden op de volgende webpagina: <a href="https://github.com/WaaaghNL/Restaurants" target="_blank">Github: WaaaghNL/Restaurants</a>
                </p>
              </li>
              <li class="about-passwords__item about-passwords-item">
                <h5 class="about-passwords-item__title">Ik krijg niets nieuws!</h5>
                <p class="about-passwords-item__text">
                  <?php
                  $timer = ((filemtime($cacheFile) - (time() - 60 * $cacheInMinutes )) > 0) ? (filemtime($cacheFile) - (time() - 60 * $cacheInMinutes )) : 120;
                  ?>
                  Onze dino is behoorlijk standvast, als hij iets aanraad gaat hij er ook voor. Het duurt nog <?=$timer;?> secoden voor je hem kan overtuigen voor een nieuw adresje! Over rule de dino <a href="<?=$escaped_url;?>/?type=todo&noCache" style="color:#fdfdfd;">Over rule de dino</a>
                </p>
              </li>
            </ul>
          </div>
        </section>
      </main>
      <div id="footer" class="footer">
        <a href="https://www.waaagh.nl">  &copy; 2023 - <?=date('Y');?> Waaagh.nl All right reserved</a> | <a href="https://github.com/WaaaghNL/Eet-Locatie">Github Page</a>
      </div>
    </div>
  </body>
</html>
<?php
require __DIR__ . '/vendor/autoload.php';

// Load environment variables from .env
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

$weather = "";
$error = "";
$apiKey = $_ENV['API_KEY']; 

//Checking if the variable '$city' has a city name 
$city = isset($_GET['city']) ? $_GET['city'] : 'Jaipur'; 
$city = urlencode($city);

// Getting Weather Data
$url = "https://api.openweathermap.org/data/2.5/weather?q=$city&appid=$apiKey&units=metric";
$response = file_get_contents($url);
$data = json_decode($response, true);


 // Decoding the response
    $timestamp     = $data['dt'];
    $sunrise       = $data['sys']['sunrise'];
    $sunset        = $data['sys']['sunset'];
    $timezoneOffset = $data['timezone'];
    $localTime = gmdate("g:i A", time() + $timezoneOffset);
    // Determining day or night
    $isDay = ($timestamp >= $sunrise && $timestamp <= $sunset);
    

    if ($data["cod"] == 200) {
      $lat = $data['coord']['lat'];
      $lon = $data['coord']['lon'];
    
      $forecastUrl = "https://api.openweathermap.org/data/2.5/forecast?q=$city&appid=$apiKey&units=metric";
      $forecastResponse = file_get_contents($forecastUrl);
      $forecastData = json_decode($forecastResponse, true);
      $hourly = $forecastData['list']; // 3-hour intervals

      $aqi_url = "https://api.openweathermap.org/data/2.5/air_pollution?lat=$lat&lon=$lon&appid=$apiKey";
      $aqi_response = file_get_contents($aqi_url);
      $aqi_data = json_decode($aqi_response, true);

      // Step 4: Air Quality Index Interpretation
      $aqi = $aqi_data['list'][0]['main']['aqi'];
      switch ($aqi) {   
        case 1: $airStatus = "Good"; break;
        case 2: $airStatus = "Fair"; break;
        case 3: $airStatus = "Moderate"; break;
        case 4: $airStatus = "Poor"; break;
        case 5: $airStatus = "Very Poor"; break;
        default: $airStatus = "Unknown";
      }

      $weather =  $data["name"]  ;
      $data["main"]["temp"]  . $data["main"]["humidity"] ;
      $celcius = intval($data["main"]["temp"]);   
      $city = $data["name"];

      $windSpeed = $data['wind']['speed'];
      $humidity = $data['main']['humidity'];
      $AirPressure = $data['main']['pressure'];
      $countryNames = [
        "IN" => "India",
        "US" => "United States",
        "GB" => "United Kingdom",
        "FR" => "France",
        "DE" => "Germany",
        "AU" => "Australia",
        "CA" => "Canada",
        "CN" => "China",
        "JP" => "Japan",
        "BR" => "Brazil",
        "ZA" => "South Africa",
        "AE"=>"UAE",
        "EG"=>"Egypt",
        "Pk"=>"Pakistan"
];

    $countryCode = $data["sys"]["country"];
    $countryName = $countryNames[$countryCode] ?? $countryCode;

    
    } 
    else {
        $error = "City not found. Please try again.";
    }
  
    $grouped = [];

    if (isset($forecastData['list']) && is_array($forecastData['list'])){
        foreach ($forecastData['list'] as $entry) {
           $date = date('Y-m-d', strtotime($entry['dt_txt']));
           // We'll take the 12:00 PM entry for each day (or closest)
           $time = date('H:i', strtotime($entry['dt_txt']));
           if ($time === "12:00") {
              $grouped[$date] = $entry;
           }
        }
    }
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>PHP Weather App</title>
    <link rel='stylesheet' href='style.css' />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.0/css/all.min.css"
        integrity="sha512-DxV+EoADOkOygM4IR9yXP8Sb2qwgidEmeqAEmDKIOfPRQZOWbXCzLC6vjbZyy0vPisbH2SyW27+ddLVCN+OMzQ=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />
</head>

<body>
    <div class="hero-bg <?php echo $isDay ? 'day' : 'night'; ?>">
        <nav>
            <h1 class='logo <?php echo $isDay ? 'logo-day' : 'logo-night'; ?>'>TensorWind</h1>
            <p class="Date"><?php echo date("d M Y")?></p>
            <form method="get" class='search-bar'>
                <input type="text" name="city" placeholder="Enter your city.." required>
                <button type="submit"><i class='fa-solid fa-magnifying-glass'></i></button>
            </form>
        </nav>

        <div class='hero-content'>
            <div class="hero-middle">
                <div class="content-left">
                    <?php
                        if ($weather) {
                           echo
                            "<div class='weather-box a'>
                                <p class='celc'>$celcius <span class='degree'>°</span><span class='C'>C</span></p>
                                <div class='weather-box-section2'>
                                    <p class='city'><i class='fa-solid fa-location-dot'></i> $city, $countryName  ($localTime)</p>
                                </div>
                            </div>";
            
           
                        } 
                        else if ($error) {
                            echo "<p style='color:red;'>$error</p>";
                        }
                    ?>
                </div>
                <div class="content-right">
                    <?php
                       if ($weather) {
                            $mainWeather = strtolower($data["weather"][0]["main"]);
                            $weatherType = "Unknown";
    

                           if ($mainWeather === "clear") {
                                $weatherType = "<img class='cloud' src='/assets/sun.png'/>";
                                $mainWeather=' Mostly Clear';
                            } 
                            elseif ($mainWeather === "clouds") {
                                $weatherType = "<img class='cloud' src='/assets/cloud.jpg'/>";
                                $mainWeather='Mostly Cloudy';
   
                            } 
                            elseif ($mainWeather === "rain") {
                                $weatherType = "<img class='cloud' src='/assets/rain.png'/>";
                                $mainWeather=' Rainy';
                            }

                            echo "
                                <div class='just-for-column'>
                                    <div id='weather-type'>
                                        <p id='wSym'>$weatherType</p>
                                        <p id='wInfo'>$mainWeather</p>
                                    </div>
                                    <div>
                                        <p class='air-quality'><i class='fa-solid fa-feather'></i> Air Quality : $airStatus</p>
                                    </div>
                                </div>";
      
                        }
                    ?>
                    <div class="tabs">
                        <div class="tab1 t">
                            <p class='tab-headers'><i class="fa-solid fa-temperature-high"></i> Humidity</p>
                            <span class="humidity h"><?php echo $humidity ?> %</span>
                        </div>
                        <div class="tab2 t">
                            <p class='tab-headers'><i class="fa-solid fa-wind"></i> Wind Speed</p>
                            <span class="wind-speed h"><?php echo $windSpeed ?> m/h</span>
                        </div>
                        <div class="tab3 t">
                            <p class='tab-headers'><i class="fas fa-gauge"></i> Air Pressure</p>
                            <span class="air-pressure h"><?php echo $AirPressure; ?> hPa</span>
                        </div>
                    </div>
                </div>
            </div>
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1440 320">
                <path fill="#d3deff" fill-opacity="1"
                    d="M0,256L48,234.7C96,213,192,171,288,133.3C384,96,480,64,576,90.7C672,117,768,203,864,229.3C960,256,1056,224,1152,213.3C1248,203,1344,213,1392,218.7L1440,224L1440,320L1392,320C1344,320,1248,320,1152,320C1056,320,960,320,864,320C768,320,672,320,576,320C480,320,384,320,288,320C192,320,96,320,48,320L0,320Z">
                </path>
            </svg>
        </div>
    </div>
    <div class="container">
        <h2 id='forecast-header'>Hourly Forecast</h2>
        <?php
           // Show next 12 hours
            echo "<div class='grid-section'>";
            for ($i = 0; $i < 8; $i++) {
                if ($i % 4 == 0) {
                    if ($i != 0){
                        echo "</div>";     // Close previous row
                    } 
                    echo "<div class='row'>"; // Start new row
                }

                $hourData = $hourly[$i];
                $time = date("g A", strtotime($hourData['dt_txt']));
                $temp = round($hourData['main']['temp']);
                $icon = $hourData['weather'][0]['icon'];
                $iconUrl = "https://openweathermap.org/img/wn/$icon@4x.png";

                echo 
                "<div class='hour-card'>
                    <p class='hourly-time'>$time</p>
                    <div class='icon'>
                        <img class='weather-icon' src='$iconUrl' alt='icon'>
                    </div>
                    <p id='card-temp'>$temp °C</p>
                </div>";
            }
            echo "</div>"; // Close last row
            echo "</div>"; // Close grid-section
        ?>

        <div class='five-day'>
            <h2 id='forecast-header'>5-Day Forecast</h2>
            <div class="grid-section">
                <div class="row top-row">
                    <?php
                        $count = 0;
                        foreach ($grouped as $date => $entry) {
                            $temp = round($entry['main']['temp']);
                            $desc = ucwords($entry['weather'][0]['description']);
                            $icon = $entry['weather'][0]['icon'];
                            $iconUrl = "https://openweathermap.org/img/wn/$icon@2x.png";

                            // Display first 3 cards in top row
                            if ($count < 3) {
                                echo "  <div class='hour-card'>
                                            <p class='hourly-time'>" . date("D, M j", strtotime($date)) . "</p>
                                            <div class='icon'>
                                               <img class='weather-icon' src='$iconUrl' alt='$desc'>
                                            </div>
                                            <p id='card-temp'>$temp °C</p>
                                            <p id='review' style='font-size: 0.9rem;'>$desc</p>
                                        </div>";
                            }
                            $count++;
                        }
                    ?>
                </div>

                <div class="row bottom-row">
                    <?php
                        $count = 0;
                        foreach (array_slice($grouped, 3, 2) as $date => $entry) {
                            $temp = round($entry['main']['temp']);
                            $desc = ucwords($entry['weather'][0]['description']);
                            $icon = $entry['weather'][0]['icon'];
                            $iconUrl = "https://openweathermap.org/img/wn/$icon@2x.png";

                            echo "  <div class='hour-card'>
                                        <p class='hourly-time'>" . date("D, M j", strtotime($date)) . "</p>
                                        <div class='icon'>
                                             <img class='weather-icon' src='$iconUrl' alt='$desc'>
                                        </div>
                                        <p id='card-temp'>$temp °C</p>
                                        <p id='review' style='font-size: 0.9rem;'>$desc</p>
                                    </div>";
                            $count++;
                        }
                    ?>
                </div>
            </div>
        </div>
        <!--------------------------------- Container ends ------------------------------------------>
        <footer>
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1440 320">
                <path fill="#d3deff" fill-opacity="1"
                    d="M0,224L80,234.7C160,245,320,267,480,245.3C640,224,800,160,960,154.7C1120,149,1280,203,1360,229.3L1440,256L1440,0L1360,0C1280,0,1120,0,960,0C800,0,640,0,480,0C320,0,160,0,80,0L0,0Z">
                </path>
            </svg>

            <div class="middle-div-footer">
                <div class="team ft">
                    <h4>Quick Links</h4>

                    <p><i class="fa-brands fa-linkedin"></i> LinkedIn</p>
                    <p><i class="fa-brands fa-github"></i> GitHub</p>
                    <p></p>
                </div>
                <div class="tools ft">
                    <h4>Services</h4>
                    <p>HTML</p>
                    <p>CSS</p>
                    <p>PHP</p>
                    <p>OpenWeather.API</p>
                    <p>FontAwesome</p>
                    <p>GetWaves.io</p>
                </div>
            </div>
            <p id='copy'>&copy; Copyright 2025-26 | All Rights Reserved</p>
        </footer>
</body>
<script src='script.js'></script>

</html>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Weather Checker</title>
    <?php include 'db.php'; ?>
    <style>
    .weather-details {
        background-color: rgba(255, 255, 255, 0.8); /* Light background for contrast */
        padding: 15px;
        border-radius: 8px;
        margin-top: 20px;
        box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.2);
    }

    .weather-details h2 {
        font-size: 24px;
        color: #007BFF; /* Blue color for the city name */
    }

    .weather-details p {
        font-size: 18px;
        margin: 5px 0;
        color: #333; /* Darker text for readability */
    }


    body {
    background-image: url('weather.png'); /* Ensure correct image path */
    background-size: cover;
    background-position: center;
    font-family: Arial, sans-serif;
    color: black;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    min-height: 100vh;
    margin: 0;
}

h1 {
    text-align: center;
    margin-bottom: 20px;
}

form {
    background: rgba(0, 0, 0, 0.8); /* Darker background with transparency */
    padding: 20px;
    border-radius: 10px;
    width: 350px;
    text-align: center;
    color: white;
    box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.3);
}

label {
    font-size: 16px;
    display: block;
    margin-bottom: 10px;
}

input[type="text"] {
    width: 90%;
    padding: 12px;
    border: 2px solid white;
    border-radius: 5px;
    margin-bottom: 15px;
    outline: none;
    background: transparent;
    color: white;
    font-size: 16px;
    text-align: center;
}

input[type="text"]::placeholder {
    color: #ddd;
}

button {
    width: 100%;
    padding: 12px;
    border: none;
    border-radius: 5px;
    background-color: #007BFF;
    color: white;
    font-size: 16px;
    cursor: pointer;
    transition: background 0.3s ease;
}

button:hover {
    background-color: #0056b3;
}

/* Responsive Design */
@media (max-width: 400px) {
    form {
        width: 90%;
    }
}
</style>

</head>
<body>
    <h1>Check Weather Here!</h1>
    <form method="POST">
        <label for="city">Enter City Name:</label>
        <input type="text" id="city" name="city" required>
        <button type="submit">Check Weather</button>
    </form>

    <?php
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $city = mysqli_real_escape_string($conn, $_POST['city']);
        $apiKey = '2775969f2e1199b7522f99842f9aa0d3'; // Replace with a valid API key
        $url = "http://api.openweathermap.org/data/2.5/weather?q=" . urlencode($city) . "&appid={$apiKey}&units=metric";

        $response = @file_get_contents($url); // Suppress warnings
        if ($response === FALSE) {
            echo "<p>Error fetching weather data. Please try again.</p>";
            error_log("API URL: {$url}"); // Log the API URL for debugging
        } else {
            $data = json_decode($response, true);
            if (isset($data['weather']) && $data['cod'] == 200) {
                // Process and display weather data
                $temperature = $data['main']['temp'];
                $weatherDescription = $data['weather'][0]['description'];
                $humidity = $data['main']['humidity'];
                $windSpeed = $data['wind']['speed'];
                $rain = $data['rain']['1h'] ?? 0; // Assign rain data, default to 0

                // Insert weather details into the database
                $stmt = $conn->prepare("INSERT INTO weather_data (city, temperature, description, humidity, wind_speed, chances_of_rain, created_at) 
                                        VALUES (?, ?, ?, ?, ?, ?, NOW())");
                $stmt->bind_param("sdsddd", $city, $temperature, $weatherDescription, $humidity, $windSpeed, $rain);
                
                if ($stmt->execute()) {
                    echo "<p>Weather details of {$city} have been saved .</p>";
                } else {
                    echo "<p>Error saving weather details to the database.</p>";
                }
                $stmt->close();

                // Display the weather details
                echo "<div class='weather-details'>";
                echo "<h2>Weather in {$city}</h2>";
                echo "<p>Temperature: {$temperature} °C</p>";
                echo "<p>Condition: {$weatherDescription}</p>";
                echo "<p>Humidity: {$humidity}%</p>";
                echo "<p>Wind Speed: {$windSpeed} m/s</p>";
                $chanceOfRainPercentage = ($rain > 0) ? ($rain * 100 / 1) : 0; // Assuming 1mm as a standard for percentage
                echo "<p>Chance of Rain: {$chanceOfRainPercentage}%</p>";

                echo "</div>";

            } else {
                echo "<p>City not found or API returned an error. Please check the name and try again.</p>";
            }
        }
    }
    ?>
</body>
</html>

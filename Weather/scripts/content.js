chrome.runtime.sendMessage({ action: 'fetchWeather', city: 'London' }, response => {
    if (response.success) {
        console.log('Weather data:', response.data);
    } else {
        console.error('Error fetching weather data:', response.error);
    }
});

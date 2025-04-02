chrome.runtime.onMessage.addListener((message, sender, sendResponse) => {
    if (message.action === 'fetchWeather') {
        fetchWeatherData(message.city)
            .then(data => {
                sendResponse({ success: true, data });
            })
            .catch(error => {
                sendResponse({ success: false, error: error.message });
            });
        return true; // Indicates that the response will be sent asynchronously
    }
});

function fetchWeatherData(city) {
    const apiKey = '2775969f2e1199b7522f99842f9aa0d3';
    const url = `http://api.openweathermap.org/data/2.5/weather?q=${encodeURIComponent(city)}&appid=${apiKey}&units=metric`;
    return fetch(url)
        .then(response => response.json());
}

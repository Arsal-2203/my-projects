// ==== CONFIG ====
const API_KEY = "2775969f2e1199b7522f99842f9aa0d3"; // <- replace me
let units = "metric"; // "metric" (°C) or "imperial" (°F)
const dom = {
  search: document.getElementById("search"),
  unit: document.getElementById("unit"),
  loc: document.getElementById("loc"),
  city: document.getElementById("city"),
  time: document.getElementById("time"),
  icon: document.getElementById("icon"),
  temp: document.getElementById("temp"),
  desc: document.getElementById("desc"),
  feels: document.getElementById("feels"),
  wind: document.getElementById("wind"),
  hum: document.getElementById("hum"),
  forecast: document.getElementById("forecast"),
};

// ==== HELPERS ====
const deg = (n) => `${Math.round(n)}°`;
const windUnit = () => (units === "metric" ? "m/s" : "mph");
const emojiFor = (id, isNight=false) => {
  // OpenWeather condition id mapping (very compact)
  if (id >= 200 && id < 300) return "⛈️";
  if (id >= 300 && id < 400) return "🌦️";
  if (id >= 500 && id < 600) return "🌧️";
  if (id >= 600 && id < 700) return "❄️";
  if (id >= 700 && id < 800) return "🌫️";
  if (id === 800) return isNight ? "🌕" : "☀️";
  if (id > 800) return isNight ? "☁️" : "⛅";
  return "🌤️";
};
const fmtTime = (ts, tzOffsetSec) => {
  const d = new Date((ts + tzOffsetSec) * 1000);
  return d.toUTCString().split(" ")[4].slice(0,5); // HH:MM
};
const dayName = (ts, tzOff) => {
  const d = new Date((ts + tzOff) * 1000);
  return d.toUTCString().split(",")[0]; // Mon, Tue...
};

const debounce = (fn, ms=400) => {
  let t; return (...args)=>{ clearTimeout(t); t=setTimeout(()=>fn(...args),ms); };
};

// ==== API CALLS ====
async function fetchCurrent(city) {
  const url = `https://api.openweathermap.org/data/2.5/weather?q=${encodeURIComponent(city)}&appid=${API_KEY}&units=${units}`;
  const res = await fetch(url);
  if (!res.ok) throw new Error("City not found");
  return res.json();
}
async function fetchForecast(city) {
  const url = `https://api.openweathermap.org/data/2.5/forecast?q=${encodeURIComponent(city)}&appid=${API_KEY}&units=${units}`;
  const res = await fetch(url);
  if (!res.ok) throw new Error("Forecast not found");
  return res.json();
}
async function fetchCurrentByCoords(lat, lon) {
  const u = `https://api.openweathermap.org/data/2.5/weather?lat=${lat}&lon=${lon}&appid=${API_KEY}&units=${units}`;
  const r = await fetch(u); if (!r.ok) throw new Error("Loc error"); return r.json();
}
async function fetchForecastByCoords(lat, lon) {
  const u = `https://api.openweathermap.org/data/2.5/forecast?lat=${lat}&lon=${lon}&appid=${API_KEY}&units=${units}`;
  const r = await fetch(u); if (!r.ok) throw new Error("Loc forecast error"); return r.json();
}

// ==== RENDERERS ====
function renderCurrent(data){
  const { name, sys, weather, main, wind, dt, timezone } = data;
  const code = weather?.[0]?.id || 800;
  const isNight = (dt + timezone)/3600 % 24 < 6 || (dt + timezone)/3600 % 24 > 18;

  dom.city.textContent = `${name}, ${sys.country}`;
  dom.time.textContent = fmtTime(dt, timezone);
  dom.icon.textContent = emojiFor(code, isNight);
  dom.temp.textContent = deg(main.temp);
  dom.desc.textContent = weather?.[0]?.description?.replace(/\b\w/g, c=>c.toUpperCase()) || "—";
  dom.feels.textContent = `Feels like ${deg(main.feels_like)}`;
  dom.wind.textContent = `Wind ${Math.round(wind.speed)} ${windUnit()}`;
  dom.hum.textContent = `Humidity ${main.humidity}%`;

  // background tint by temperature
  const warm = Math.min(Math.max((main.temp - (units==="metric"?0:32)) / (units==="metric"?35:63), 0), 1);
  document.documentElement.style.setProperty('--bg1', `hsl(${200 - warm*60}, 95%, 75%)`);
  document.documentElement.style.setProperty('--bg2', `hsl(${220 - warm*60}, 85%, 60%)`);
}
function renderForecast(data){
  const { list, city } = data;
  // pick one slot per day around 12:00 local
  const byDay = {};
  list.forEach(x=>{
    const day = dayName(x.dt, city.timezone);
    const hour = parseInt(fmtTime(x.dt, city.timezone).split(":")[0],10);
    if (!byDay[day] || Math.abs(hour-12) < Math.abs(parseInt(fmtTime(byDay[day].dt, city.timezone).split(":")[0],10)-12)) {
      byDay[day] = x;
    }
  });
  const days = Object.keys(byDay).slice(0,5);
  dom.forecast.innerHTML = days.map(d=>{
    const x = byDay[d];
    const code = x.weather?.[0]?.id || 800;
    return `
      <div class="forecast-item">
        <div class="f-day">${d}</div>
        <div class="f-emoji">${emojiFor(code)}</div>
        <div class="muted">${x.weather?.[0]?.main || ""}</div>
        <div class="f-temp">${deg(x.main.temp_min)} / ${deg(x.main.temp_max)}</div>
      </div>
    `;
  }).join("");
}

// ==== CONTROLLERS ====
async function loadByCity(city){
  try{
    dom.city.textContent = "Loading…"; dom.time.textContent="—"; dom.temp.textContent="--°"; dom.desc.textContent="—";
    const [cur, f5] = await Promise.all([fetchCurrent(city), fetchForecast(city)]);
    renderCurrent(cur); renderForecast(f5);
    localStorage.setItem("last_city", city);
  }catch(e){
    dom.city.textContent = "Not found"; dom.desc.textContent = "Try another city";
    console.error(e);
  }
}
async function loadByLocation(){
  if (!navigator.geolocation){ return alert("Geolocation not supported."); }
  navigator.geolocation.getCurrentPosition(async pos=>{
    try{
      const { latitude:lat, longitude:lon } = pos.coords;
      const [cur, f5] = await Promise.all([fetchCurrentByCoords(lat,lon), fetchForecastByCoords(lat,lon)]);
      renderCurrent(cur); renderForecast(f5);
      localStorage.setItem("last_city", cur.name);
    }catch(e){ console.error(e); alert("Couldn’t load location weather."); }
  }, ()=> alert("Location permission denied."));
}

// search (debounced)
dom.search.addEventListener("input", debounce((e)=>{
  const q = e.target.value.trim();
  if (q.length >= 2) loadByCity(q);
}, 500));

// unit toggle
dom.unit.addEventListener("click", ()=>{
  units = units === "metric" ? "imperial" : "metric";
  dom.unit.textContent = units === "metric" ? "°C" : "°F";
  const last = localStorage.getItem("last_city");
  if (last) loadByCity(last);
});

// location button
dom.loc.addEventListener("click", loadByLocation);

// boot
(function init(){
  const last = localStorage.getItem("last_city") || "Karachi";
  loadByCity(last);
})();

<?php /* /htdocs/crypto-app/alerts.php */ ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Crypto App • Alerts</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="styles.css" />
</head>
<body class="bg-gray-50 text-gray-900">
  <header class="max-w-6xl mx-auto px-4 py-6 flex items-center justify-between">
    <h1 class="text-2xl sm:text-3xl font-extrabold">🔔 Price Alerts</h1>
    <nav class="flex gap-2">
      <a class="btn" href="index.php">Home</a>
      <a class="btn" href="dashboard.php">Dashboard</a>
      <a class="btn" href="portfolio.php">Portfolio</a>
    </nav>
  </header>

  <main class="max-w-6xl mx-auto px-4 pb-16">
    <div class="bg-white shadow-soft rounded-2xl p-6 mb-6">
      <h2 class="text-lg font-semibold mb-4">Create Alert</h2>
      <div class="grid sm:grid-cols-5 gap-3">
        <input id="aName" class="input sm:col-span-2" placeholder="Coin (name search)" />
        <div id="aSuggest" class="suggestions hidden sm:col-span-2"></div>

        <input id="aId" class="input" placeholder="Coin ID (auto)" readonly />
        <select id="aCond" class="input">
          <option value="gte">Price ≥</option>
          <option value="lte">Price ≤</option>
        </select>
        <input id="aPrice" class="input" type="number" step="0.01" placeholder="Target USD" />
        <button id="aAdd" class="btn-primary">Add Alert</button>
      </div>
      <p class="text-xs text-gray-500 mt-2">Tip: Allow notifications when prompted so alerts can pop even if tab is in background.</p>
    </div>

    <div class="bg-white shadow-soft rounded-2xl p-6">
      <h2 class="text-lg font-semibold mb-4">Active Alerts</h2>
      <div class="overflow-auto">
        <table class="w-full text-sm">
          <thead class="text-left bg-gray-100">
            <tr>
              <th class="th">Coin</th>
              <th class="th">Condition</th>
              <th class="th">Target</th>
              <th class="th">Last Price</th>
              <th class="th">Status</th>
              <th class="th">Action</th>
            </tr>
          </thead>
          <tbody id="aBody"></tbody>
        </table>
      </div>
    </div>
  </main>

  <audio id="beep">
    <source src="data:audio/wav;base64,UklGRiQAAABXQVZFZm10IBAAAAABAAEAESsAACJWAAACABYAAAACAAACAAA=" type="audio/wav">
  </audio>

  <script src="script.js"></script>
  <script>
    // Notifications
    if (Notification && Notification.permission !== 'granted') {
      Notification.requestPermission();
    }

    // Autocomplete for alerts
    let coins = [];
    (async () => { coins = await getCoinList(); })();

    const aName = document.getElementById('aName');
    const aId = document.getElementById('aId');
    const aBox = document.getElementById('aSuggest');
    aName.addEventListener('input', () => {
      const q = aName.value.trim().toLowerCase();
      aBox.innerHTML = '';
      if (q.length < 2) { aBox.classList.add('hidden'); return; }
      const list = coins.filter(c => c.name.toLowerCase().includes(q)).slice(0, 10);
      if (!list.length) { aBox.classList.add('hidden'); return; }
      list.forEach(c => {
        const d = document.createElement('div');
        d.className = 'suggestion';
        d.textContent = `${c.name} (${c.symbol.toUpperCase()})`;
        d.onclick = () => { aName.value = c.name; aId.value = c.id; aBox.classList.add('hidden'); };
        aBox.appendChild(d);
      });
      aBox.classList.remove('hidden');
    });
    document.addEventListener('click', e => { if (!aName.contains(e.target)) aBox.classList.add('hidden'); });

    const AKEY = 'alerts_v1';
    function getAlerts(){ return JSON.parse(localStorage.getItem(AKEY) || '[]'); }
    function setAlerts(v){ localStorage.setItem(AKEY, JSON.stringify(v)); }

    document.getElementById('aAdd').onclick = () => {
      const id = aId.value.trim(), name = aName.value.trim();
      const cond = document.getElementById('aCond').value;
      const price = parseFloat(document.getElementById('aPrice').value);
      if (!id || !name || !price) return alert('Complete all fields.');
      const arr = getAlerts();
      arr.push({ id, name, cond, price, last: null, fired: false });
      setAlerts(arr);
      aId.value=''; aName.value=''; document.getElementById('aPrice').value='';
      renderAlerts();
    };

    async function renderAlerts(){
      const body = document.getElementById('aBody');
      const arr = getAlerts();
      if (!arr.length){ body.innerHTML = '<tr><td class="py-6 text-gray-500" colspan="6">No alerts yet.</td></tr>'; return; }

      // fetch all prices in one call
      const ids = arr.map(a => a.id).join(',');
      const prices = await fetchSimplePrices(ids);

      body.innerHTML = arr.map((a,i) => {
        const last = prices[a.id]?.usd || null;
        a.last = last;
        return `
          <tr>
            <td class="td font-medium">${a.name}</td>
            <td class="td">${a.cond === 'gte' ? '≥' : '≤'}</td>
            <td class="td">${formatUSD(a.price)}</td>
            <td class="td">${last == null ? '—' : formatUSD(last)}</td>
            <td class="td" id="status-${i}">Checking…</td>
            <td class="td"><button class="btn-danger text-xs" onclick="delAlert(${i})">Delete</button></td>
          </tr>
        `;
      }).join('');
      setAlerts(arr); // persist last

      // evaluate conditions + notify
      arr.forEach((a,i) => {
        const met = a.last != null && ((a.cond==='gte' && a.last >= a.price) || (a.cond==='lte' && a.last <= a.price));
        const cell = document.getElementById(`status-${i}`);
        if (met && !a.fired){
          cell.innerHTML = '<span class="text-green-600 font-semibold">Triggered</span>';
          a.fired = true;
          setAlerts(arr);
          try { new Notification(`${a.name} alert`, { body: `Price ${a.cond==='gte'?'≥':'≤'} ${formatUSD(a.price)} (now ${formatUSD(a.last)})` }); } catch {}
          document.getElementById('beep').play().catch(()=>{});
        } else {
          cell.innerHTML = met ? '<span class="text-green-600">Met</span>' : '<span class="text-gray-500">Waiting</span>';
        }
      });
    }
    window.delAlert = i => { const arr = getAlerts(); arr.splice(i,1); setAlerts(arr); renderAlerts(); };

    renderAlerts();
    setInterval(renderAlerts, 30000);
  </script>
</body>
</html>

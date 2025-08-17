<?php /* /htdocs/crypto-app/portfolio.php */ ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Crypto App • Portfolio</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="styles.css" />
</head>
<body class="bg-gray-50 text-gray-900">
  <header class="max-w-6xl mx-auto px-4 py-6 flex items-center justify-between">
    <h1 class="text-2xl sm:text-3xl font-extrabold">🧳 Portfolio</h1>
    <nav class="flex gap-2">
      <a class="btn" href="index.php">Home</a>
      <a class="btn" href="dashboard.php">Dashboard</a>
      <a class="btn" href="alerts.php">Alerts</a>
    </nav>
  </header>

  <main class="max-w-6xl mx-auto px-4 pb-16">
    <div class="bg-white shadow-soft rounded-2xl p-6 mb-6">
      <h2 class="text-lg font-semibold mb-4">Add Coin</h2>
      <div class="grid sm:grid-cols-4 gap-3 relative">
        <div class="sm:col-span-2 relative">
          <input id="coinName" class="input" placeholder="Search coin name..." autocomplete="off" />
          <div id="suggestions" class="suggestions hidden"></div>
        </div>
        <input id="coinId" class="input" placeholder="CoinGecko ID (auto)" readonly />
        <input id="qty" class="input" placeholder="Quantity" type="number" step="0.00000001" />
        <input id="buy" class="input" placeholder="Buy Price (USD)" type="number" step="0.01" />
        <button id="add" class="btn-primary sm:col-span-1">Add</button>
      </div>
    </div>

    <div class="bg-white shadow-soft rounded-2xl p-6">
      <div class="flex items-center justify-between mb-4">
        <h2 class="text-lg font-semibold">Your Holdings</h2>
        <div class="text-sm text-gray-600">Total Value: <span id="total-value">$0.00</span></div>
      </div>

      <div class="overflow-auto">
        <table class="w-full text-sm">
          <thead class="text-left bg-gray-100">
            <tr>
              <th class="th">Coin</th>
              <th class="th">Qty</th>
              <th class="th">Buy Price</th>
              <th class="th">Current</th>
              <th class="th">Value</th>
              <th class="th">P/L</th>
              <th class="th">7d</th>
              <th class="th">Action</th>
            </tr>
          </thead>
          <tbody id="pf-body">
            <!-- Rows via JS -->
          </tbody>
        </table>
      </div>
    </div>
  </main>

  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <script src="script.js"></script>
  <script>
    // Autocomplete
    let coinList = [];
    (async () => { coinList = await getCoinList(); })();

    const nameInput = document.getElementById('coinName');
    const idInput = document.getElementById('coinId');
    const box = document.getElementById('suggestions');

    nameInput.addEventListener('input', () => {
      const q = nameInput.value.trim().toLowerCase();
      box.innerHTML = '';
      if (q.length < 2) { box.classList.add('hidden'); return; }
      const matches = coinList.filter(c => c.name.toLowerCase().includes(q)).slice(0, 10);
      if (!matches.length) { box.classList.add('hidden'); return; }
      matches.forEach(c => {
        const div = document.createElement('div');
        div.className = 'suggestion';
        div.textContent = `${c.name} (${c.symbol.toUpperCase()})`;
        div.onclick = () => {
          nameInput.value = c.name;
          idInput.value = c.id;
          box.classList.add('hidden');
        };
        box.appendChild(div);
      });
      box.classList.remove('hidden');
    });
    document.addEventListener('click', (e) => {
      if (!nameInput.contains(e.target)) box.classList.add('hidden');
    });

    // Portfolio storage (localStorage)
    const KEY = 'portfolio_v1';
    function getPF() { return JSON.parse(localStorage.getItem(KEY) || '[]'); }
    function setPF(v) { localStorage.setItem(KEY, JSON.stringify(v)); }

    document.getElementById('add').onclick = () => {
      const id = idInput.value.trim();
      const name = nameInput.value.trim();
      const qty = parseFloat(document.getElementById('qty').value);
      const buy = parseFloat(document.getElementById('buy').value);
      if (!id || !name || !qty || !buy) return alert('Fill all fields (and pick a coin from list).');
      const pf = getPF();
      pf.push({ id, name, qty, buy });
      setPF(pf);
      idInput.value = ''; nameInput.value = ''; document.getElementById('qty').value = ''; document.getElementById('buy').value='';
      renderPF();
    };

    async function renderPF() {
      const body = document.getElementById('pf-body');
      const pf = getPF();
      if (!pf.length) { body.innerHTML = '<tr><td class="py-6 text-gray-500" colspan="8">No holdings yet. Add your first coin above.</td></tr>'; document.getElementById('total-value').textContent = '$0.00'; return; }

      // Fetch current prices in one go
      const ids = pf.map(x => x.id).join(',');
      const prices = await fetchSimplePrices(ids);
      body.innerHTML = skeletonRows(pf.length);

      let total = 0;
      const rows = await Promise.all(pf.map(async (item, idx) => {
        const price = prices[item.id]?.usd || 0;
        const value = price * item.qty;
        total += value;
        const pnl = (price - item.buy) * item.qty;
        const spark = await fetchSparkline(item.id);
        return `
          <tr>
            <td class="td font-medium">${item.name}</td>
            <td class="td">${formatAmount(item.qty)}</td>
            <td class="td">${formatUSD(item.buy)}</td>
            <td class="td">${formatUSD(price)}</td>
            <td class="td">${formatUSD(value)}</td>
            <td class="td font-semibold ${pnl >= 0 ? 'text-green-600' : 'text-red-600'}">${formatUSD(pnl)}</td>
            <td class="td"><canvas id="spark-${idx}" width="120" height="36"></canvas></td>
            <td class="td">
              <button class="btn-danger text-xs" onclick="delRow(${idx})">Delete</button>
            </td>
          </tr>
        `;
      }));

      body.innerHTML = rows.join('');
      document.getElementById('total-value').textContent = formatUSD(total);

      // Draw sparks
      const pfNow = getPF();
      for (let i = 0; i < pfNow.length; i++) {
        const spark = await fetchSparkline(pfNow[i].id);
        drawMiniSpark(`spark-${i}`, spark);
      }
    }

    function delRow(i) {
      const pf = getPF();
      pf.splice(i, 1);
      setPF(pf);
      renderPF();
    }

    renderPF();
    setInterval(renderPF, 15000);
  </script>
</body>
</html>

<?php /* /htdocs/crypto-app/dashboard.php */ ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Crypto App • Dashboard</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="styles.css" />
</head>
<body class="bg-gray-50 text-gray-900">
  <header class="max-w-6xl mx-auto px-4 py-6 flex items-center justify-between">
    <h1 class="text-2xl sm:text-3xl font-extrabold">📊 Market Dashboard</h1>
    <nav class="flex gap-2">
      <a class="btn" href="index.php">Home</a>
      <a class="btn" href="portfolio.php">Portfolio</a>
      <a class="btn" href="alerts.php">Alerts</a>
    </nav>
  </header>

  <main class="max-w-6xl mx-auto px-4 pb-16">
    <div class="bg-white shadow-soft rounded-2xl p-6">
      <div class="flex flex-col sm:flex-row gap-3 sm:items-center sm:justify-between mb-4">
        <div class="flex gap-2">
          <input id="search" class="input" placeholder="Search coin..." />
          <select id="rows" class="input">
            <option value="25">25 rows</option>
            <option value="50">50 rows</option>
            <option value="100">100 rows</option>
          </select>
        </div>
        <span id="dash-updated" class="text-xs text-gray-500"></span>
      </div>

      <div class="overflow-auto">
        <table class="w-full text-sm">
          <thead class="text-left bg-gray-100">
            <tr>
              <th class="th">#</th>
              <th class="th">Coin</th>
              <th class="th">Price</th>
              <th class="th">24h</th>
              <th class="th">Market Cap</th>
              <th class="th">7d</th>
            </tr>
          </thead>
          <tbody id="dash-body">
            <!-- Rows via JS -->
          </tbody>
        </table>
      </div>
    </div>
  </main>

  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <script src="script.js"></script>
  <script>
    let allCoins = [];
    async function loadDash() {
      const per = parseInt(document.getElementById('rows').value, 10);
      document.getElementById('dash-body').innerHTML = skeletonRows(per);
      allCoins = await fetchMarketPage({ per_page: per, sparkline: true });
      renderDash();
      document.getElementById('dash-updated').textContent =
        'Updated: ' + new Date().toLocaleTimeString();
    }

    function renderDash() {
      const q = document.getElementById('search').value.trim().toLowerCase();
      const rows = (q ? allCoins.filter(c =>
        c.name.toLowerCase().includes(q) || c.symbol.toLowerCase().includes(q)
      ) : allCoins).map(c => marketRow(c)).join('');
      const body = document.getElementById('dash-body');
      body.innerHTML = rows;
      // Draw mini 7d charts
      (q ? allCoins.filter(c =>
        c.name.toLowerCase().includes(q) || c.symbol.toLowerCase().includes(q)
      ) : allCoins).forEach(c => drawMiniSpark(`spark-${c.id}`, c.sparkline_in_7d?.price || []));
    }

    document.getElementById('search').addEventListener('input', renderDash);
    document.getElementById('rows').addEventListener('change', loadDash);

    loadDash();
    setInterval(loadDash, 20000);
  </script>
</body>
</html>

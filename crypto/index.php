<?php /* /htdocs/crypto-app/index.php */ ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Crypto App • Home</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="styles.css" />
</head>
<body class="bg-gray-50 text-gray-900">
  <header class="max-w-6xl mx-auto px-4 py-6 flex items-center justify-between">
    <h1 class="text-2xl sm:text-3xl font-extrabold">🪙 Crypto Tracker</h1>
    <nav class="flex gap-2">
      <a class="btn" href="dashboard.php">Dashboard</a>
      <a class="btn" href="portfolio.php">Portfolio</a>
      <a class="btn" href="alerts.php">Alerts</a>
    </nav>
  </header>

  <main class="max-w-6xl mx-auto px-4 pb-16">
    <section class="rounded-2xl p-8 bg-white shadow-soft">
      <h2 class="text-xl font-bold mb-2">Welcome, legend 👋</h2>
      <p class="text-gray-600 mb-6">
      Your market. Your way. Always live, always clear.
      </p>
      <div class="flex flex-wrap gap-3">
        <a href="dashboard.php" class="btn-primary">Open Market Dashboard</a>
        <a href="portfolio.php" class="btn-outline">Build Your Portfolio</a>
        <a href="alerts.php" class="btn-outline">Set Price Alerts</a>
      </div>
    </section>

    <section class="mt-10">
      <div class="flex items-center justify-between mb-3">
        <h3 class="text-lg font-semibold">Top 10 Coins (live)</h3>
        <span id="last-updated" class="text-xs text-gray-500"></span>
      </div>

      <div id="top-coins" class="grid sm:grid-cols-2 lg:grid-cols-5 gap-4">
        <!-- Cards injected by JS -->
      </div>
    </section>
  </main>

  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <script src="script.js"></script>
  <script>
    // Home: show top 10 coins
    async function renderHome() {
      const container = document.getElementById('top-coins');
      container.innerHTML = skeletonCards(10);
      const list = await fetchMarketPage({ per_page: 10 });
      container.innerHTML = list.map(c => coinCard(c)).join('');
      document.getElementById('last-updated').textContent =
        'Updated: ' + new Date().toLocaleTimeString();
    }
    renderHome();
    setInterval(renderHome, 15000);
  </script>
</body>
</html>

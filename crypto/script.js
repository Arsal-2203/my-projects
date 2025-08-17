/* /htdocs/crypto-app/script.js */

// ---------- Utilities ----------
function formatUSD(n) {
  if (n == null || isNaN(n)) return '$0.00';
  return n >= 1 ? `$${n.toLocaleString(undefined, { maximumFractionDigits: 2 })}`
                : `$${n.toFixed(6)}`;
}
function formatAmount(n) {
  if (!n && n !== 0) return '0';
  return n >= 1 ? n.toLocaleString(undefined, { maximumFractionDigits: 6 })
                : n.toFixed(8);
}
function pctBadge(v) {
  const cls = v >= 0 ? 'text-green-700 bg-green-100' : 'text-red-700 bg-red-100';
  const sign = v > 0 ? '+' : '';
  return `<span class="px-2 py-0.5 rounded-full text-xs ${cls}">${sign}${v?.toFixed(2)}%</span>`;
}

// ---------- API ----------
async function fetchMarketPage({ per_page = 25, sparkline = true } = {}) {
  const url = `https://api.coingecko.com/api/v3/coins/markets?vs_currency=usd&order=market_cap_desc&per_page=${per_page}&page=1&sparkline=${sparkline ? 'true':'false'}&price_change_percentage=24h`;
  const res = await fetch(url);
  return await res.json();
}
async function fetchSimplePrices(idsCsv) {
  if (!idsCsv) return {};
  const url = `https://api.coingecko.com/api/v3/simple/price?ids=${encodeURIComponent(idsCsv)}&vs_currencies=usd`;
  const res = await fetch(url);
  return await res.json();
}
async function fetchSparkline(id) {
  const url = `https://api.coingecko.com/api/v3/coins/${encodeURIComponent(id)}/market_chart?vs_currency=usd&days=7`;
  const res = await fetch(url);
  const data = await res.json();
  return (data.prices || []).map(p => p[1]);
}
async function getCoinList() {
  const cacheKey = 'cg_coin_list_v1';
  const cached = sessionStorage.getItem(cacheKey);
  if (cached) return JSON.parse(cached);
  const res = await fetch('https://api.coingecko.com/api/v3/coins/list');
  const list = await res.json();
  sessionStorage.setItem(cacheKey, JSON.stringify(list));
  return list;
}

// ---------- UI generators ----------
function coinCard(c) {
  const up = (c.price_change_percentage_24h || 0) >= 0;
  return `
    <div class="p-4 rounded-xl bg-white shadow-soft flex flex-col gap-2">
      <div class="flex items-center justify-between">
        <div class="font-semibold">${c.name}</div>
        <div class="text-xs text-gray-500">${c.symbol.toUpperCase()}</div>
      </div>
      <div class="text-lg font-bold">${formatUSD(c.current_price)}</div>
      <div>${pctBadge(c.price_change_percentage_24h || 0)}</div>
    </div>
  `;
}
function marketRow(c) {
  const change = c.price_change_percentage_24h;
  return `
    <tr class="border-b last:border-0">
      <td class="td">${c.market_cap_rank}</td>
      <td class="td font-medium">${c.name} <span class="text-xs text-gray-500">(${c.symbol.toUpperCase()})</span></td>
      <td class="td">${formatUSD(c.current_price)}</td>
      <td class="td">${pctBadge(change)}</td>
      <td class="td">$${(c.market_cap || 0).toLocaleString()}</td>
      <td class="td"><canvas id="spark-${c.id}" width="120" height="36"></canvas></td>
    </tr>
  `;
}
function skeletonCards(n=10){
  return Array.from({length:n}).map(()=>`
    <div class="p-4 rounded-xl bg-white shadow-soft animate-pulse h-28"></div>
  `).join('');
}
function skeletonRows(n=10){
  return Array.from({length:n}).map(()=>`
    <tr class="border-b">
      <td class="td"><div class="skeleton h-3 w-6"></div></td>
      <td class="td"><div class="skeleton h-3 w-32"></div></td>
      <td class="td"><div class="skeleton h-3 w-16"></div></td>
      <td class="td"><div class="skeleton h-3 w-12"></div></td>
      <td class="td"><div class="skeleton h-3 w-20"></div></td>
      <td class="td"><div class="skeleton h-3 w-24"></div></td>
      <td class="td"><div class="skeleton h-3 w-24"></div></td>
      <td class="td"><div class="skeleton h-6 w-14"></div></td>
    </tr>
  `).join('');
}

// ---------- Charts ----------
function drawMiniSpark(canvasId, series){
  const el = document.getElementById(canvasId);
  if (!el || !series || !series.length) return;
  const rising = series[series.length-1] >= series[0];
  new Chart(el, {
    type: 'line',
    data: { labels: series.map((_,i)=>i), datasets: [{ data: series, borderWidth: 2, pointRadius: 0, fill: false, tension: 0.3, borderColor: rising ? 'green' : 'red' }] },
    options: { responsive: false, plugins:{legend:{display:false}}, scales:{x:{display:false},y:{display:false}} }
  });
}

<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width,initial-scale=1.0"/>
  <title>📊 Thống Kê Lượt Truy Cập | GiáVàng.vn</title>
  <link rel="preconnect" href="https://fonts.googleapis.com"/>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=JetBrains+Mono:wght@400;600&display=swap" rel="stylesheet"/>
  <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
  <style>
    *,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
    :root{--bg:#07090f;--bg2:#0d1018;--bg3:#131724;--border:rgba(255,255,255,0.07);--border2:rgba(255,255,255,0.12);
      --gold:#f5c518;--text:#e4e8f2;--text2:#c4cad8;--muted:#6e778c;--muted2:#909ab2;
      --green:#22c97a;--red:#f55252;--blue:#4f7af8;--purple:#a78bfa;--radius:14px;--radius-sm:8px}
    html{scroll-behavior:smooth}
    body{font-family:'Inter',sans-serif;background:var(--bg);color:var(--text);min-height:100vh;-webkit-font-smoothing:antialiased}
    main{max-width:1100px;margin:0 auto;padding:32px 24px 80px}
    @media(max-width:640px){
      main{padding:12px 0 60px}
      .back-link{padding:0 12px}
      .page-title{padding:0 12px}
      .page-sub{padding:0 12px}
      .cards{padding:0 12px}
      .chart-wrap{border-radius:0;border-left:none;border-right:none}
      .tbl-wrap{border-radius:0;border-left:none;border-right:none}
    }

    .page-title{font-size:28px;font-weight:800;margin-bottom:6px}
    .page-sub{font-size:13px;color:var(--muted);margin-bottom:28px}

    /* Overview Cards */
    .cards{display:grid;grid-template-columns:repeat(auto-fill,minmax(200px,1fr));gap:14px;margin-bottom:28px}
    .card{background:var(--bg2);border:1px solid var(--border);border-radius:var(--radius);padding:20px}
    .card-label{font-size:11px;font-weight:700;color:var(--muted);text-transform:uppercase;letter-spacing:.06em;margin-bottom:8px}
    .card-val{font-size:28px;font-weight:800;font-family:'JetBrains Mono',monospace}
    .card-sub{font-size:12px;color:var(--muted);margin-top:4px}
    .card-green .card-val{color:var(--green)}
    .card-blue .card-val{color:var(--blue)}
    .card-purple .card-val{color:var(--purple)}
    .card-gold .card-val{color:var(--gold)}
    .card-red .card-val{color:var(--red)}

    /* Chart */
    .chart-wrap{background:var(--bg2);border:1px solid var(--border);border-radius:var(--radius);padding:24px;margin-bottom:28px}
    .chart-title{font-size:16px;font-weight:700;margin-bottom:16px}
    .chart-box{height:280px}

    /* Tables */
    .tbl-wrap{background:var(--bg2);border:1px solid var(--border);border-radius:var(--radius);overflow:hidden;margin-bottom:28px}
    .tbl-head{padding:16px 20px;font-size:16px;font-weight:700;border-bottom:1px solid var(--border)}
    table{width:100%;border-collapse:collapse}
    th{padding:10px 16px;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:var(--muted);background:var(--bg3);text-align:left;border-bottom:1px solid var(--border)}
    td{padding:10px 16px;font-size:13px;border-bottom:1px solid var(--border);color:var(--text2)}
    tr:last-child td{border-bottom:none}
    tr:hover{background:rgba(255,255,255,0.02)}
    .mono{font-family:'JetBrains Mono',monospace;font-size:12px}
    .url-link{color:var(--blue);text-decoration:none}
    .url-link:hover{text-decoration:underline}
    .bar{height:6px;border-radius:3px;background:var(--bg3);position:relative;min-width:60px}
    .bar-fill{position:absolute;top:0;left:0;height:100%;border-radius:3px;background:linear-gradient(90deg,var(--blue),var(--purple))}
    .back-link{font-size:13px;color:var(--blue);text-decoration:none;margin-bottom:20px;display:inline-block}
    .back-link:hover{text-decoration:underline}

    @media(max-width:640px){
      .cards{grid-template-columns:1fr 1fr}
      .card-val{font-size:22px}
      table th,table td{padding:8px 10px;font-size:12px}
    }
  </style>
</head>
<body>
<main>
  <a href="/" class="back-link">← Trang chủ</a>
  <h1 class="page-title">📊 Thống Kê Lượt Truy Cập</h1>
  <p class="page-sub">Dữ liệu tự động thu thập · Cập nhật mỗi lần load trang · Không tính bot</p>

  {{-- OVERVIEW --}}
  <div class="cards">
    <div class="card card-green">
      <div class="card-label">Hôm nay</div>
      <div class="card-val">{{ number_format($stats['today_views']) }}</div>
      <div class="card-sub">{{ number_format($stats['today_unique']) }} unique IP</div>
    </div>
    <div class="card card-blue">
      <div class="card-label">Hôm qua</div>
      <div class="card-val">{{ number_format($stats['yesterday_views']) }}</div>
      <div class="card-sub">{{ number_format($stats['yesterday_unique']) }} unique IP</div>
    </div>
    <div class="card card-purple">
      <div class="card-label">7 ngày qua</div>
      <div class="card-val">{{ number_format($stats['week_views']) }}</div>
      <div class="card-sub">{{ number_format($stats['week_unique']) }} unique IP</div>
    </div>
    <div class="card card-gold">
      <div class="card-label">30 ngày qua</div>
      <div class="card-val">{{ number_format($stats['month_views']) }}</div>
      <div class="card-sub">{{ number_format($stats['month_unique']) }} unique IP</div>
    </div>
    <div class="card">
      <div class="card-label">Tổng cộng</div>
      <div class="card-val">{{ number_format($stats['total_views']) }}</div>
      <div class="card-sub">{{ number_format($stats['total_unique']) }} unique IP</div>
    </div>
    <div class="card card-red">
      <div class="card-label">Bot / Crawler</div>
      <div class="card-val">{{ number_format($stats['bot_views']) }}</div>
      <div class="card-sub">Tự động loại khỏi thống kê</div>
    </div>
  </div>

  {{-- CHART --}}
  <div class="chart-wrap">
    <div class="chart-title">📈 Lượt truy cập 30 ngày qua</div>
    <div class="chart-box"><canvas id="daily-chart"></canvas></div>
  </div>

  {{-- TOP PAGES --}}
  <div class="tbl-wrap">
    <div class="tbl-head">🏆 Top trang được xem nhiều nhất (30 ngày)</div>
    <table>
      <thead><tr><th>#</th><th>URL</th><th>Lượt xem</th><th>Unique IP</th><th>Tỷ lệ</th></tr></thead>
      <tbody>
        @php $maxViews = $topPages->max('views') ?: 1; @endphp
        @foreach($topPages as $i => $page)
        <tr>
          <td>{{ $i + 1 }}</td>
          <td><a href="{{ $page->url }}" class="url-link" target="_blank">{{ $page->url }}</a></td>
          <td class="mono">{{ number_format($page->views) }}</td>
          <td class="mono">{{ number_format($page->unique_ips) }}</td>
          <td>
            <div class="bar"><div class="bar-fill" style="width:{{ round($page->views / $maxViews * 100) }}%"></div></div>
          </td>
        </tr>
        @endforeach
      </tbody>
    </table>
  </div>

  {{-- RECENT --}}
  <div class="tbl-wrap">
    <div class="tbl-head">🕐 50 lượt truy cập gần nhất</div>
    <table>
      <thead><tr><th>Thời gian</th><th>URL</th><th>IP</th><th>Referer</th></tr></thead>
      <tbody>
        @foreach($recent as $r)
        <tr>
          <td class="mono">{{ $r->created_at->format('H:i:s d/m') }}</td>
          <td><a href="{{ $r->url }}" class="url-link" target="_blank">{{ Str::limit($r->url, 35) }}</a></td>
          <td class="mono">{{ $r->ip }}</td>
          <td style="color:var(--muted);font-size:11px">{{ Str::limit($r->referer, 30) ?: '—' }}</td>
        </tr>
        @endforeach
      </tbody>
    </table>
  </div>
</main>

<script>
(function(){
  var labels = @json($dailyViews->pluck('date'));
  var views  = @json($dailyViews->pluck('views'));
  var uniq   = @json($dailyViews->pluck('unique_ips'));

  new Chart(document.getElementById('daily-chart'), {
    type: 'line',
    data: {
      labels: labels,
      datasets: [
        {
          label: 'Lượt xem',
          data: views,
          borderColor: '#4f7af8',
          backgroundColor: 'rgba(79,122,248,0.08)',
          borderWidth: 2,
          fill: true,
          tension: 0.35,
          pointRadius: 3,
          pointHoverRadius: 6,
        },
        {
          label: 'Unique IP',
          data: uniq,
          borderColor: '#a78bfa',
          backgroundColor: 'rgba(167,139,250,0.06)',
          borderWidth: 2,
          fill: true,
          tension: 0.35,
          pointRadius: 3,
          pointHoverRadius: 6,
        }
      ]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: {
        legend: { position: 'top', labels: { color: '#909ab2', font: { size: 12 } } },
      },
      scales: {
        x: { ticks: { color: '#6e778c', font: { size: 10 } }, grid: { color: 'rgba(255,255,255,0.04)' } },
        y: { ticks: { color: '#6e778c', font: { size: 11 } }, grid: { color: 'rgba(255,255,255,0.06)' }, beginAtZero: true }
      }
    }
  });
})();
</script>
</body>
</html>

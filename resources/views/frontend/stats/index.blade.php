@extends('frontend.partials.layout', ['activePage' => '', 'maxWidth' => '1100px'])

@section('title', '📊 Thống Kê Lượt Truy Cập | GiáVàng.vn')

@push('head-scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
@endpush

@push('styles')
<link rel="stylesheet" href="/frontend/css/stats.css"/>
@endpush

@section('content')
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
@endsection

@push('scripts')
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
@endpush

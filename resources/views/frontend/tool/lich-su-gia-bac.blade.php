@extends('frontend.partials.layout', ['activePage' => 'lichsu', 'maxWidth' => '1000px'])

@section('title', 'Lịch Sử Giá Bạc Hôm Nay & 30/60/90 Ngày – Biểu Đồ Trực Quan | GiáVàng.vn')

@section('meta')
  <meta name="description" content="Xem lịch sử giá bạc mua vào bán ra theo ngày và trong ngày."/>
  <link rel="canonical" href="{{ url('/lich-su-gia-bac') }}"/>
@endsection

@section('bg-glow')
<div class="bg-glow" style="position:fixed;inset:0;pointer-events:none;z-index:0;
  background:radial-gradient(ellipse 60% 40% at 20% 0%,rgba(167,139,250,0.06) 0%,transparent 60%),radial-gradient(ellipse 50% 35% at 80% 90%,rgba(79,122,248,0.05) 0%,transparent 60%)"></div>
@endsection

@push('head-scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
@endpush

@push('styles')
<link rel="stylesheet" href="/frontend/css/tool-lichsu.css"/>
@endpush

@section('content')
<div class="breadcrumb">
    <a href="/">Trang chủ</a><span>›</span><span>Lịch Sử Giá Bạc</span>
  </div>

  <div class="page-head">
    <div class="page-head-row">
      <div class="page-head-icon">📈</div>
      <h1>Lịch Sử Giá Bạc</h1>
    </div>
    <p class="sub">Biểu đồ lịch sử giá bạc mua vào bán ra theo ngày · Chọn thương hiệu và khoảng thời gian</p>
  </div>

  {{-- CONTROL BAR --}}
  <div class="ctrl-bar">
    <span class="ctrl-label">Thương hiệu:</span>
    @foreach($brands as $key => $brand)
    <button class="brand-btn {{ $loop->first ? 'active' : '' }}" data-brand="{{ $key }}" data-api="{{ $brand['api'] }}">{{ $brand['name'] }}</button>
    @endforeach
  </div>
  <div class="ctrl-bar">
    <span class="ctrl-label">Đơn vị:</span>
    <button class="ctrl-btn active" data-unit="KG">KG</button>
    <button class="ctrl-btn" data-unit="LUONG">Lượng</button>
    <span style="margin:0 8px;color:var(--border2)">|</span>
    <span class="ctrl-label">Kỳ:</span>
    <button class="ctrl-btn" data-days="1">1 Ngày</button>
    <button class="ctrl-btn" data-days="7">7 Ngày</button>
    <button class="ctrl-btn active" data-days="30">30 Ngày</button>
    <button class="ctrl-btn" data-days="60">60 Ngày</button>
    <button class="ctrl-btn" data-days="90">90 Ngày</button>
    <button class="ctrl-btn" data-days="365">1 Năm</button>
  </div>

  {{-- STATS --}}
  <div class="stats-row">
    <div class="stat-card">
      <div class="stat-label">Giá bán ra hiện tại</div>
      <div class="stat-val" id="stat-sell-now">–</div>
      <div class="stat-sub" id="stat-unit-lbl">VND/đvị</div>
    </div>
    <div class="stat-card">
      <div class="stat-label" id="stat-high-label">Cao nhất kỳ</div>
      <div class="stat-val stat-up" id="stat-high">–</div>
      <div class="stat-sub" id="stat-high-date"></div>
    </div>
    <div class="stat-card">
      <div class="stat-label" id="stat-low-label">Thấp nhất kỳ</div>
      <div class="stat-val stat-down" id="stat-low">–</div>
      <div class="stat-sub" id="stat-low-date"></div>
    </div>
    <div class="stat-card">
      <div class="stat-label">Thay đổi kỳ</div>
      <div class="stat-val" id="stat-change">–</div>
      <div class="stat-sub" id="stat-change-pct"></div>
    </div>
  </div>

  {{-- CHART --}}
  <div class="chart-card">
    <div class="chart-header">
      <div>
        <div class="chart-title" id="chart-title">Lịch sử giá bán ra</div>
        <div class="chart-subtitle" id="chart-sub">Đang tải...</div>
      </div>
      <span class="chart-unit-lbl" id="chart-unit-lbl"></span>
    </div>
    <div class="chart-body">
      <div class="chart-loading" id="chart-loading"><div class="spinner"></div> Đang tải biểu đồ...</div>
      <canvas id="histChart" style="display:none"></canvas>
    </div>
  </div>

  {{-- DATA TABLE --}}
  <div class="data-wrap">
    <div class="data-wrap-head">
      <h3>📅 Bảng Dữ Liệu Chi Tiết</h3>
      <span class="data-count" id="data-count"></span>
    </div>
    <div style="overflow-x:auto;max-height:360px;overflow-y:auto">
      <table class="data-tbl" id="data-tbl">
        <thead>
          <tr>
            <th>Ngày</th>
            <th>Mua vào</th>
            <th>Bán ra</th>
            <th>Chênh lệch</th>
            <th>Thay đổi bán ra</th>
          </tr>
        </thead>
        <tbody id="data-tbody"><tr><td colspan="5" style="text-align:center;color:var(--muted);padding:20px">Đang tải...</td></tr></tbody>
      </table>
    </div>
  </div>

  {{-- SEO INFO --}}
  <div class="info-card">
    <h2>📖 Về Dữ Liệu Lịch Sử Giá Bạc</h2>
    <p>Dữ liệu lịch sử giá bạc được thu thập tự động từ các thương hiệu uy tín: Phú Quý, Ancarat, DOJI và Kim Ngân Phúc. Mỗi điểm dữ liệu đại diện cho giá chốt cuối ngày hoặc theo giờ (kỳ 1 ngày).</p>
    <p>Nhà đầu tư có thể sử dụng biểu đồ lịch sử để phân tích xu hướng giá trong ngày hoặc theo kỳ dài hạn, từ đó đưa ra quyết định đầu tư phù hợp.</p>
  </div>
@endsection

@push('scripts')
<script src="/frontend/js/tool-lichsu.js"></script>
@endpush

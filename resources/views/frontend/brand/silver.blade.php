@extends('frontend.partials.layout', ['activePage' => $brand['key'], 'maxWidth' => '960px'])

@section('title', $brand['title'])

@section('meta')
  <meta name="description" content="{{ $brand['description'] }}"/>
  <link rel="canonical" href="{{ url('/' . $brand['slug']) }}"/>
  <meta property="og:type" content="website"/>
  <meta property="og:title" content="{{ $brand['title'] }}"/>
  <meta property="og:description" content="{{ $brand['description'] }}"/>
  <meta property="og:url" content="{{ url('/' . $brand['slug']) }}"/>
  <meta property="og:site_name" content="GiáVàng.vn"/>
  <script type="application/ld+json">
  {
    "@context": "https://schema.org",
    "@type": "FAQPage",
    "mainEntity": [
      @foreach($brand['faqs'] as $i => $faq)
      {
        "@type": "Question",
        "name": "{{ $faq['q'] }}",
        "acceptedAnswer": {
          "@type": "Answer",
          "text": "{{ $faq['a'] }}"
        }
      }{{ !$loop->last ? ',' : '' }}
      @endforeach
    ]
  }
  </script>
  <script type="application/ld+json">
  {
    "@context": "https://schema.org",
    "@type": "WebPage",
    "name": "{{ $brand['title'] }}",
    "description": "{{ $brand['description'] }}",
    "url": "{{ url('/' . $brand['slug']) }}",
    "publisher": {
      "@type": "Organization",
      "name": "GiáVàng.vn",
      "url": "{{ url('/') }}"
    }
  }
  </script>
@endsection

@section('bg-glow')
<div class="bg-glow" style="position:fixed;inset:0;pointer-events:none;z-index:0;
  background:radial-gradient(ellipse 60% 40% at 20% 0%,rgba(79,122,248,0.07) 0%,transparent 60%),radial-gradient(ellipse 50% 35% at 80% 90%,rgba(176,190,197,0.05) 0%,transparent 60%)"></div>
@endsection

@push('head-scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
@endpush

@push('styles')
<link rel="stylesheet" href="/frontend/css/brand.css"/>
@endpush

@section('content')
{{-- BREADCRUMB --}}
  <div class="breadcrumb">
    <a href="/">Trang chủ</a>
    <span class="sep">›</span>
    <span>{{ $brand['name'] }}</span>
  </div>

  {{-- PAGE HEADER --}}
  <div class="page-head">
    <div class="page-head-row">
      <div class="brand-icon">{{ $brand['icon'] }}</div>
      <h1>Giá Bạc {{ $brand['name_short'] }} 999 Hôm Nay – {{ now()->format('d/m/Y') }}</h1>
    </div>
    <p class="sub">Cập nhật real-time · Nguồn: {{ $brand['name_short'] }} chính thức · GiáVàng.vn</p>
  </div>

  {{-- SSR PRICE TABLE — Google đọc được --}}
  <div class="price-table-wrap">
    <div class="price-table-head">
      <h2>📊 Bảng Giá Bạc {{ $brand['name_short'] }} 999 – {{ now()->format('d/m/Y H:i') }}</h2>
      <span class="updated-label" id="tbl-updated">
        @if(!empty($prices))
          @php $firstPrice = collect($prices)->first(); @endphp
          Cập nhật: {{ $firstPrice->recorded_at ? $firstPrice->recorded_at->format('H:i d/m/Y') : 'N/A' }}
        @else
          Đang cập nhật...
        @endif
      </span>
    </div>

    <table class="price-tbl">
      <thead>
        <tr>
          <th>Đơn vị</th>
          <th>Mua vào (VNĐ)</th>
          <th>Bán ra (VNĐ)</th>
          <th>Chênh lệch</th>
          <th>Cập nhật</th>
        </tr>
      </thead>
      <tbody id="ssr-tbody">
        @forelse($prices as $unit => $row)
        <tr>
          <td class="td-unit">{{ $brand['unit_labels'][$unit] ?? $unit }}</td>
          <td class="td-price td-buy"  id="ssr-buy-{{ $unit }}">{{ number_format($row->buy_price) }}</td>
          <td class="td-price td-sell" id="ssr-sell-{{ $unit }}">{{ number_format($row->sell_price) }}</td>
          <td class="td-spread"        id="ssr-spread-{{ $unit }}">{{ number_format($row->sell_price - $row->buy_price) }}</td>
          <td class="td-time">{{ $row->recorded_at ? $row->recorded_at->format('H:i d/m') : '–' }}</td>
        </tr>
        @empty
        <tr><td colspan="5" style="text-align:center;padding:20px;color:var(--muted)">Đang tải dữ liệu...</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>

  {{-- CHART --}}
  <div class="chart-section">
    <div class="chart-bar">
      <span class="chart-bar-title">Lịch sử giá bán ra</span>
      @foreach($brand['chart_unit_options'] as $opt)
      <button class="unit-btn {{ $opt['active'] ? 'active' : '' }}"
              data-unit="{{ $opt['unit'] }}" data-mult="{{ $opt['mult'] }}">
        {{ $opt['label'] }}
      </button>
      @endforeach
      <span style="margin-left:auto"></span>
      <button class="prd-btn" data-days="1">1D</button>
      <button class="prd-btn active" data-days="7">7D</button>
      <button class="prd-btn" data-days="30">1M</button>
      <button class="prd-btn" data-days="90">3M</button>
      <button class="prd-btn" data-days="365">1Y</button>
    </div>
    <div class="chart-canvas-wrap">
      <div class="chart-loading" id="chart-loading">
        <div class="spinner"></div> Đang tải biểu đồ...
      </div>
      <canvas id="brandChart" style="display:none"></canvas>
    </div>
  </div>

  {{-- ABOUT BRAND --}}
  <div class="about-section">
    <h2>🏦 Về {{ $brand['name_short'] }}</h2>
    <p>{{ $brand['about'] }}</p>
  </div>

  {{-- FAQ --}}
  <section class="faq-section">
    <h2>❓ Câu hỏi thường gặp về giá bạc {{ $brand['name_short'] }}</h2>
    @foreach($brand['faqs'] as $faq)
    <div class="faq-item">
      <div class="faq-q">
        <span>{{ $faq['q'] }}</span>
        <span class="faq-caret">▼</span>
      </div>
      <div class="faq-a">{{ $faq['a'] }}</div>
    </div>
    @endforeach
  </section>

  {{-- RELATED BRANDS --}}
  <div class="related-section">
    <h2>Xem giá bạc thương hiệu khác</h2>
    <div class="related-grid">
      @if($brand['key'] !== 'phuquy')
      <a href="/gia-bac-phu-quy" class="related-card">
        <div class="related-icon" style="background:linear-gradient(135deg,#b0bec5,#546e7a)">🥈</div>
        <div><div class="related-name">Phú Quý 999</div><div class="related-sub">giá bạc phú quý</div></div>
      </a>
      @endif
      @if($brand['key'] !== 'ancarat')
      <a href="/gia-bac-ancarat" class="related-card">
        <div class="related-icon" style="background:linear-gradient(135deg,#06b6d4,#0284c7)">🏅</div>
        <div><div class="related-name">Ancarat 999</div><div class="related-sub">giá bạc ancarat</div></div>
      </a>
      @endif
      @if($brand['key'] !== 'doji')
      <a href="/gia-bac-doji" class="related-card">
        <div class="related-icon" style="background:linear-gradient(135deg,#dc2626,#991b1b)">🔴</div>
        <div><div class="related-name">DOJI 99.9</div><div class="related-sub">giá bạc doji</div></div>
      </a>
      @endif
      @if($brand['key'] !== 'kimnganphuc')
      <a href="/gia-bac-kim-ngan-phuc" class="related-card">
        <div class="related-icon" style="background:linear-gradient(135deg,#a78bfa,#7c3aed);font-size:10px;font-weight:900;color:#fff">KNP</div>
        <div><div class="related-name">Kim Ngân Phúc 999</div><div class="related-sub">giá bạc kim ngân phúc</div></div>
      </a>
      @endif
    </div>
  </div>
@endsection

@push('scripts')
<script src="/frontend/js/brand.js"></script>
@endpush

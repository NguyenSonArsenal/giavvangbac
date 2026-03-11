@extends('frontend.partials.layout', ['activePage' => 'sosanh', 'maxWidth' => '1000px'])

@section('title', 'So Sánh Giá Bạc Các Thương Hiệu Hôm Nay | GiáVàng.vn')

@section('meta')
  <meta name="description" content="So sánh giá bạc mua vào bán ra của Phú Quý, Ancarat, DOJI, Kim Ngân Phúc."/>
  <link rel="canonical" href="{{ url('/so-sanh-gia-bac') }}"/>
@endsection

@section('bg-glow')
<div class="bg-glow" style="position:fixed;inset:0;pointer-events:none;z-index:0;
  background:radial-gradient(ellipse 60% 40% at 20% 0%,rgba(79,122,248,0.07) 0%,transparent 60%),radial-gradient(ellipse 50% 35% at 80% 90%,rgba(34,201,122,0.05) 0%,transparent 60%)"></div>
@endsection

@push('styles')
<link rel="stylesheet" href="/frontend/css/tool-sosanh.css"/>
@endpush

@section('content')
<div class="breadcrumb">
    <a href="/">Trang chủ</a><span>›</span><span>So Sánh Giá Bạc</span>
  </div>

  <div class="page-head">
    <div class="page-head-row">
      <div class="page-head-icon">📊</div>
      <h1>So Sánh Giá Bạc Hôm Nay – {{ now()->format('d/m/Y') }}</h1>
    </div>
    <p class="sub">Bảng so sánh giá bạc mua vào bán ra của tất cả thương hiệu · Cập nhật real-time</p>
  </div>

  {{-- FILTER BAR --}}
  <div class="filter-bar">
    <span class="filter-label">Đơn vị:</span>
    <button class="unit-tab active" data-unit="LUONG" onclick="filterUnit(this,'LUONG')">Lượng</button>
    <button class="unit-tab" data-unit="KG" onclick="filterUnit(this,'KG')">Kilogram</button>
    <div class="live-badge"><span class="live-dot"></span> Đang cập nhật</div>
  </div>

  {{-- COMPARISON TABLE --}}
  <div class="cmp-wrap">
    <table class="cmp-table" id="cmp-table">
      <thead>
        <tr>
          <th>Thương hiệu / Đơn vị</th>
          <th>Giá Mua Vào ↑</th>
          <th>Giá Bán Ra ↓</th>
          <th>Chênh Lệch</th>
          <th>Cập nhật</th>
        </tr>
      </thead>
      <tbody id="cmp-tbody">
        @foreach($rows as $row)
        <tr class="cmp-row" data-unit="{{ $row['unit'] }}" data-brand="{{ $row['brand_key'] }}"
            style="{{ $row['unit'] === 'LUONG' ? '' : 'display:none' }}">
          <td data-label="Thương hiệu">
            <div class="cmp-brand-cell">
              <div class="cmp-brand-icon" style="background:{{ $brands[$row['brand_key']]['gradient'] }};color:{{ strlen($row['brand_icon']) > 2 ? '#fff' : 'inherit' }};font-size:{{ strlen($row['brand_icon']) > 2 ? '9px' : '15px' }}">{{ $row['brand_icon'] }}</div>
              <div>
                <div class="cmp-brand-name"><a href="/gia-bac-{{ str_replace(['phuquy','ancarat','doji','kimnganphuc'],['phu-quy','ancarat','doji','kim-ngan-phuc'],$row['brand_key']) }}" style="color:inherit;text-decoration:none;border-bottom:1px solid rgba(255,255,255,0.1)">{{ $row['brand_name'] }}</a></div>
                <div class="cmp-unit-badge">{{ $row['unit_label'] }}</div>
              </div>
            </div>
          </td>
          <td data-label="Mua vào">
            <span class="cmp-price cmp-buy" id="cmp-buy-{{ $row['brand_key'] }}-{{ $row['unit'] }}">{{ number_format($row['buy']) }}</span>
          </td>
          <td data-label="Bán ra">
            <span class="cmp-price cmp-sell" id="cmp-sell-{{ $row['brand_key'] }}-{{ $row['unit'] }}">{{ number_format($row['sell']) }}</span>
          </td>
          <td data-label="Chênh lệch">
            <span class="cmp-spread" id="cmp-spread-{{ $row['brand_key'] }}-{{ $row['unit'] }}">{{ number_format($row['spread']) }}</span>
          </td>
          <td data-label="Cập nhật">
            <span class="cmp-time" id="cmp-time-{{ $row['brand_key'] }}">{{ $row['updated_at'] ?? '–' }}</span>
          </td>
        </tr>
        @endforeach
      </tbody>
    </table>
  </div>

  {{-- SEO INFO --}}
  <div class="info-card">
    <h2>📌 Hướng Dẫn Đọc Bảng So Sánh</h2>
    <p><strong>Giá mua vào</strong>: Giá thương hiệu mua bạc từ bạn (bạn bán bạc và nhận tiền). Thường thấp hơn giá bán ra.</p>
    <p><strong>Giá bán ra</strong>: Giá thương hiệu bán bạc cho bạn (bạn mua bạc và trả tiền). Thường cao hơn giá mua vào.</p>
    <p><strong>Chênh lệch (Spread)</strong>: Hiệu số giữa giá bán ra và giá mua vào. Spread thấp hơn nghĩa là chi phí giao dịch thấp hơn — có lợi hơn cho nhà đầu tư.</p>
    <p>Bảng giá được cập nhật tự động real-time trong giờ giao dịch từ các thương hiệu bạc uy tín tại Việt Nam.</p>
  </div>
@endsection

@push('scripts')
<script src="/frontend/js/tool-sosanh.js"></script>
@endpush

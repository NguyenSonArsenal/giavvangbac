@extends('frontend.partials.layout', ['activePage' => 'quydoi', 'maxWidth' => '900px'])

@section('title', 'Quy Đổi Giá Bạc – Tính Toán KG, Lượng, Chỉ, Gram | GiáVàng.vn')

@section('meta')
  <meta name="description" content="Công cụ quy đổi giá bạc trực tuyến."/>
  <link rel="canonical" href="{{ url('/quy-doi-bac') }}"/>
@endsection

@section('bg-glow')
<div class="bg-glow" style="position:fixed;inset:0;pointer-events:none;z-index:0;
  background:radial-gradient(ellipse 60% 40% at 20% 0%,rgba(79,122,248,0.07) 0%,transparent 60%),radial-gradient(ellipse 50% 35% at 80% 90%,rgba(176,190,197,0.05) 0%,transparent 60%)"></div>
@endsection

@push('styles')
<link rel="stylesheet" href="/frontend/css/tool-quydoi.css"/>
@endpush

@section('content')
<div class="breadcrumb">
    <a href="/">Trang chủ</a><span>›</span><span>Quy Đổi Giá Bạc</span>
  </div>

  <div class="page-head">
    <div class="page-head-row">
      <div class="page-head-icon">⚖️</div>
      <h1>Quy Đổi Giá Bạc</h1>
    </div>
    <p class="sub">Nhập số lượng, chọn đơn vị và thương hiệu để tính tổng tiền mua vào / bán ra ngay lập tức.</p>
  </div>

  {{-- CALCULATOR --}}
  <div class="calc-card">
    <h2>⚖️ Máy Tính Quy Đổi</h2>

    <div class="calc-grid">
      <div class="form-group">
        <label>Số lượng</label>
        <input type="number" id="qty-input" value="1" min="0.001" step="any" placeholder="Nhập số lượng..."/>
      </div>
      <div class="form-group">
        <label>Đơn vị</label>
        <select id="unit-select">
          <option value="LUONG" data-gram="37.5">Lượng (37.5g)</option>
          <option value="CHI"   data-gram="3.75">Chỉ (3.75g)</option>
          <option value="KG"    data-gram="1000">Kilogram (1000g)</option>
          <option value="GRAM"  data-gram="1">Gram (1g)</option>
        </select>
      </div>
    </div>

    {{-- Brand selector --}}
    <div style="margin-bottom:14px">
      <label style="font-size:11px;font-weight:700;color:var(--muted);text-transform:uppercase;letter-spacing:.06em;display:block;margin-bottom:8px">Thương hiệu</label>
      <div class="brand-grid">
        @foreach($brands as $key => $brand)
        <button class="brand-btn {{ $loop->first ? 'active' : '' }}" data-brand="{{ $key }}"
                onclick="selectBrand(this,'{{ $key }}')"
                style="{{ $loop->first ? 'border-color:'. $brand['color'] .';background:rgba(176,190,197,0.08)' : '' }}">
          <div class="brand-btn-icon" style="background:{{ $brand['gradient'] }};color:{{ strlen($brand['icon']) > 2 ? '#fff' : 'inherit' }};font-size:{{ strlen($brand['icon']) > 2 ? '9px' : '14px' }}">{{ $brand['icon'] }}</div>
          <div class="brand-btn-text">{{ $brand['name'] }}</div>
        </button>
        @endforeach
      </div>
    </div>

    {{-- Result --}}
    <div class="result-wrap">
      <div class="result-title">Kết quả tính toán</div>
      <div class="result-grid">
        <div class="result-box">
          <div class="result-box-label">💸 Giá mua vào (bạn bán)</div>
          <div class="result-buy" id="result-buy">–</div>
          <div class="result-sub" id="result-buy-sub"></div>
        </div>
        <div class="result-box">
          <div class="result-box-label">🏷️ Giá bán ra (bạn mua)</div>
          <div class="result-sell" id="result-sell">–</div>
          <div class="result-sub" id="result-sell-sub"></div>
        </div>
      </div>
      <div class="result-note" id="result-note">Đang tải giá từ thị trường...</div>
    </div>
  </div>

  {{-- Unit conversion table --}}
  <div class="info-card">
    <h2>📐 Bảng Quy Đổi Đơn Vị Bạc</h2>
    <p>Các đơn vị đo lường phổ biến khi giao dịch bạc tại Việt Nam:</p>
    <table class="convert-table">
      <thead><tr><th>Đơn vị</th><th>Quy đổi sang Gram</th><th>Ghi chú</th></tr></thead>
      <tbody>
        <tr><td>1 Lượng</td><td>37.5 gram</td><td>Đơn vị phổ biến nhất tại VN</td></tr>
        <tr><td>1 Chỉ</td><td>3.75 gram</td><td>= 1/10 lượng</td></tr>
        <tr><td>1 Kilogram (KG)</td><td>1,000 gram</td><td>= 26.67 lượng</td></tr>
        <tr><td>1 Tael (Lạng TQ)</td><td>50 gram</td><td>Thị trường quốc tế</td></tr>
        <tr><td>1 Troy Oz (XAG)</td><td>31.1 gram</td><td>Chuẩn quốc tế</td></tr>
      </tbody>
    </table>
  </div>

  {{-- SEO text --}}
  <div class="info-card">
    <h2>💡 Hướng Dẫn Quy Đổi Giá Bạc</h2>
    <p>Công cụ quy đổi giá bạc của GiáVàng.vn giúp bạn tính toán nhanh chóng giá trị mua vào và bán ra của bạc theo nhiều đơn vị khác nhau. Giá được lấy trực tiếp từ các thương hiệu bạc uy tín như Phú Quý, Ancarat, DOJI và Kim Ngân Phúc.</p>
    <p>Lưu ý: Giá hiển thị là giá tham khảo, cập nhật real-time. Hãy xác nhận giá chính thức từ thương hiệu trước khi giao dịch.</p>
  </div>
@endsection

@push('scripts')
<script src="/frontend/js/tool-quydoi.js"></script>
@endpush

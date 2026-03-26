@extends('frontend.partials.layout', ['activePage' => 'article', 'maxWidth' => '1200px'])

@section('title', 'Danh Mục Bài Viết | GiáVàng.vn')

@section('meta')
<meta name="description" content="Danh mục bài viết về vàng, bạc, kiến thức đầu tư và thị trường tài chính tại GiáVàng.vn"/>
<link rel="canonical" href="{{ url('/danh-muc') }}"/>
<meta property="og:type" content="website"/>
<meta property="og:title" content="Danh Mục Bài Viết | GiáVàng.vn"/>
<meta property="og:description" content="Danh mục bài viết về vàng, bạc, kiến thức đầu tư và thị trường tài chính."/>
<meta property="og:url" content="{{ url('/danh-muc') }}"/>
@endsection

@push('styles')
<style>
/* ── Page Header ── */
.cat-page-header { margin-bottom: 36px }
.cat-page-header h1 {
  font-size: 28px; font-weight: 800; margin: 0 0 6px;
  background: linear-gradient(90deg, #4f7af8, #a78bfa);
  -webkit-background-clip: text; background-clip: text; -webkit-text-fill-color: transparent;
}
.cat-page-header p { color: var(--muted2); font-size: 15px; margin: 0 }

/* ── Grid ── */
.cat-grid {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 20px;
}
@media (max-width: 900px) { .cat-grid { grid-template-columns: repeat(2, 1fr) } }
@media (max-width: 520px) { .cat-grid { grid-template-columns: 1fr } }

/* ── Card ── */
.cat-card {
  background: var(--bg2);
  border: 1px solid var(--border);
  border-radius: 14px;
  overflow: hidden;
  text-decoration: none;
  display: flex; flex-direction: column;
  transition: border-color .25s, transform .25s, box-shadow .25s;
}
.cat-card:hover {
  border-color: rgba(79,122,248,0.4);
  transform: translateY(-4px);
  box-shadow: 0 12px 32px rgba(79,122,248,0.1);
  text-decoration: none;
}

/* Thumbnail */
.cat-card__thumb {
  height: 140px;
  position: relative; overflow: hidden;
}
.cat-card__thumb img {
  width: 100%; height: 100%; object-fit: cover;
  transition: transform .3s;
}
.cat-card:hover .cat-card__thumb img { transform: scale(1.06); }
.cat-card__thumb-placeholder {
  width: 100%; height: 100%;
  display: flex; align-items: center; justify-content: center;
  font-size: 42px;
}

/* Body */
.cat-card__body {
  padding: 18px 20px 20px; flex: 1;
  display: flex; flex-direction: column;
}
.cat-card__name {
  font-size: 17px; font-weight: 800;
  color: var(--text); margin: 0 0 6px;
}
.cat-card:hover .cat-card__name { color: var(--blue); }
.cat-card__desc {
  font-size: 13px; color: var(--muted2);
  line-height: 1.6; margin: 0 0 14px; flex: 1;
  display: -webkit-box; -webkit-line-clamp: 2;
  -webkit-box-orient: vertical; overflow: hidden;
}
.cat-card__footer {
  display: flex; align-items: center; justify-content: space-between;
  padding-top: 12px; border-top: 1px solid var(--border);
}
.cat-card__count {
  font-size: 12px; font-weight: 600;
  color: var(--muted);
  display: flex; align-items: center; gap: 5px;
}
.cat-card__count strong {
  color: var(--blue); font-size: 16px;
}
.cat-card__arrow {
  font-size: 13px; font-weight: 600;
  color: var(--blue); display: flex; align-items: center; gap: 4px;
}
.cat-card__arrow span { transition: transform .2s; }
.cat-card:hover .cat-card__arrow span { transform: translateX(4px); }

/* Empty */
.cat-empty {
  text-align: center; color: var(--muted);
  padding: 80px 0; font-size: 16px;
  grid-column: 1 / -1;
}
</style>
@endpush

@php
  $gradients = [
    'linear-gradient(135deg, #4f7af8 0%, #6d28d9 100%)',
    'linear-gradient(135deg, #f5c518 0%, #c8820a 100%)',
    'linear-gradient(135deg, #22c97a 0%, #059669 100%)',
    'linear-gradient(135deg, #a78bfa 0%, #7c3aed 100%)',
    'linear-gradient(135deg, #f97316 0%, #dc2626 100%)',
    'linear-gradient(135deg, #06b6d4 0%, #0284c7 100%)',
    'linear-gradient(135deg, #ec4899 0%, #be185d 100%)',
    'linear-gradient(135deg, #b0bec5 0%, #546e7a 100%)',
  ];
  $icons = ['📂','📊','💰','📈','🥇','🏦','📰','💎'];
@endphp

@section('content')
  <div class="cat-page-header">
    <h1>📂 Danh Mục Bài Viết</h1>
    <p>Khám phá các chủ đề về vàng, bạc và thị trường tài chính</p>
  </div>

  @if($categories->count() > 0)
    <div class="cat-grid">
      @foreach($categories as $i => $category)
        <a href="{{ route('fe.category.show', $category->slug) }}" class="cat-card">
          <div class="cat-card__thumb"
               style="background:{{ $gradients[$i % count($gradients)] }}">
            @if($category->thumbnail)
              <img src="{{ asset('storage/' . $category->thumbnail) }}" alt="{{ $category->name }}" loading="lazy">
            @else
              <div class="cat-card__thumb-placeholder">{{ $icons[$i % count($icons)] }}</div>
            @endif
          </div>
          <div class="cat-card__body">
            <h2 class="cat-card__name">{{ $category->name }}</h2>
            @if($category->description)
              <p class="cat-card__desc">{{ $category->description }}</p>
            @endif
            <div class="cat-card__footer">
              <div class="cat-card__count">
                <strong>{{ $category->posts_count }}</strong> bài viết
              </div>
              <div class="cat-card__arrow">
                Xem thêm <span>→</span>
              </div>
            </div>
          </div>
        </a>
      @endforeach
    </div>
  @else
    <div class="cat-grid">
      <div class="cat-empty">
        Chưa có danh mục nào. Hãy quay lại sau nhé! 🙏
      </div>
    </div>
  @endif
@endsection

@extends('backend.layout.main')

@push('style')
<style>
  .post-filter-bar {
    background: linear-gradient(135deg, #f8f9ff 0%, #eef1f8 100%);
    border: 1px solid #e2e6f0;
    border-radius: 10px;
    padding: 16px 20px;
    margin-bottom: 20px;
    display: flex;
    align-items: center;
    gap: 12px;
    flex-wrap: wrap;
  }
  .post-filter-bar label {
    font-weight: 600; color: #4a5568; margin: 0; font-size: 13px; letter-spacing: 0.3px;
  }
  .post-filter-bar select {
    border-radius: 8px; border: 1px solid #d1d9e6;
    padding: 6px 32px 6px 12px; font-size: 13px;
    background-color: #fff; width: auto; transition: border-color .2s;
  }
  .post-filter-bar select:focus { border-color: #667eea; box-shadow: 0 0 0 3px rgba(102,126,234,.15); }
  .post-filter-bar .btn { font-size: 13px; border-radius: 8px; padding: 6px 16px; }

  .post-page-header {
    display: flex; align-items: center; justify-content: space-between; margin-bottom: 6px;
  }
  .post-page-header h5 { font-weight: 700; font-size: 17px; color: #2d3748; margin: 0; }

  .post-card {
    border: none; border-radius: 14px;
    box-shadow: 0 2px 12px rgba(0,0,0,.06); overflow: hidden;
  }
  .post-card .card-body { padding: 24px; }

  .post-table { border-collapse: separate; border-spacing: 0; }
  .post-table thead th {
    background: #f7f8fc; color: #4a5568;
    font-weight: 600; font-size: 12.5px;
    text-transform: uppercase; letter-spacing: 0.5px;
    padding: 12px 14px; border: none;
    border-bottom: 2px solid #e2e6f0; white-space: nowrap;
  }
  .post-table tbody td {
    padding: 12px 14px; vertical-align: middle;
    font-size: 13.5px; border-bottom: 1px solid #f0f2f5; color: #4a5568;
  }
  .post-table tbody tr { transition: background .15s; }
  .post-table tbody tr:hover { background: #f7f8fc; }

  .post-thumb {
    width: 48px; height: 48px; object-fit: cover;
    border-radius: 8px; border: 2px solid #edf0f7;
  }
  .post-thumb-empty {
    width: 48px; height: 48px; border-radius: 8px;
    background: #f0f2f5; display: flex; align-items: center;
    justify-content: center; color: #cbd5e0; font-size: 18px;
  }

  .badge-active {
    background: linear-gradient(135deg, #48bb78 0%, #38a169 100%);
    color: #fff; padding: 4px 12px; border-radius: 20px;
    font-size: 11.5px; font-weight: 600;
  }
  .badge-inactive {
    background: linear-gradient(135deg, #fc8181 0%, #e53e3e 100%);
    color: #fff; padding: 4px 12px; border-radius: 20px;
    font-size: 11.5px; font-weight: 600;
  }
  .badge-featured {
    background: linear-gradient(135deg, #f6ad55 0%, #ed8936 100%);
    color: #fff; padding: 4px 10px; border-radius: 20px;
    font-size: 11px; font-weight: 600;
  }

  .post-actions { display: flex; gap: 6px; }
  .post-actions .btn {
    border-radius: 7px; font-size: 12px; padding: 5px 12px;
    font-weight: 500; border: none;
    transition: transform .15s, box-shadow .15s;
  }
  .post-actions .btn:hover { transform: translateY(-1px); box-shadow: 0 3px 8px rgba(0,0,0,.12); }
  .btn-edit { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: #fff; }
  .btn-edit:hover { color: #fff; }
  .btn-del { background: linear-gradient(135deg, #fc8181 0%, #e53e3e 100%); color: #fff; }
  .btn-del:hover { color: #fff; }

  .btn-add-new {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: #fff; border: none; border-radius: 8px;
    padding: 7px 20px; font-size: 13px; font-weight: 600;
    transition: transform .15s, box-shadow .15s;
  }
  .btn-add-new:hover { color: #fff; transform: translateY(-1px); box-shadow: 0 4px 14px rgba(102,126,234,.35); }

  .post-slug { color: #a0aec0; font-size: 12.5px; font-family: 'Consolas', monospace; }
  .post-cat-badge {
    background: #edf2f7; color: #4a5568; padding: 3px 10px;
    border-radius: 6px; font-size: 12px; font-weight: 500;
  }
</style>
@endpush

@section('content')
  <div class="content-page">
    <div class="page-breadcrumb">
      <div class="row">
        <div class="col-12 d-flex no-block align-items-center">
          <h4 class="page-title">Bài viết</h4>
        </div>
      </div>
    </div>
    <div class="container-fluid">
      <div class="row">
        <div class="col-12">
          <div class="card post-card">
            <div class="card-body">
              @include('backend.layout.structures._notification')

              <div class="post-page-header">
                <h5>📝 Danh sách bài viết</h5>
                <a href="{{ backendRoute('post.create') }}" class="btn btn-add-new">
                  <i class="mdi mdi-plus"></i> Thêm mới
                </a>
              </div>

              {{-- Bộ lọc --}}
              <form method="GET" action="{{ backendRoute('post.index') }}" class="post-filter-bar">
                <label><i class="mdi mdi-filter-outline"></i> Trạng thái:</label>
                <select name="status">
                  <option value="">Tất cả</option>
                  <option value="1" {{ request('status') === '1' ? 'selected' : '' }}>Active</option>
                  <option value="-1" {{ request('status') === '-1' ? 'selected' : '' }}>Inactive</option>
                </select>

                <label>Danh mục:</label>
                <select name="category_id">
                  <option value="">Tất cả</option>
                  @foreach($categories as $cat)
                    <option value="{{ $cat->id }}" {{ request('category_id') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                  @endforeach
                </select>

                <button type="submit" class="btn btn-primary btn-sm"><i class="mdi mdi-magnify"></i> Lọc</button>
                <a href="{{ backendRoute('post.index') }}" class="btn btn-outline-secondary btn-sm"><i class="mdi mdi-refresh"></i> Reset</a>
              </form>

              <div class="dataTables_wrapper dt-bootstrap4">
                <table class="table post-table" role="grid">
                  <thead>
                  <tr>
                    <th>STT</th>
                    <th>Ảnh</th>
                    <th>Tiêu đề</th>
                    <th>Danh mục</th>
                    <th>Trạng thái</th>
                    <th>Nổi bật</th>
                    <th>Lượt xem</th>
                    <th>Ngày tạo</th>
                    <th>Hành động</th>
                  </tr>
                  </thead>
                  <tbody>
                  @foreach($data as $key => $entity)
                    <tr>
                      <td>{{ getSTTBackend($data, $key) }}</td>
                      <td>
                        @if($entity->thumbnail)
                          <img src="{{ asset('storage/' . $entity->thumbnail) }}" alt="{{ $entity->title }}" class="post-thumb">
                        @else
                          <div class="post-thumb-empty"><i class="mdi mdi-image-off"></i></div>
                        @endif
                      </td>
                      <td>
                        <strong>{{ Str::limit($entity->title, 50) }}</strong>
                        <div class="post-slug">{{ $entity->slug }}</div>
                      </td>
                      <td>
                        @if($entity->category)
                          <span class="post-cat-badge">{{ $entity->category->name }}</span>
                        @else
                          <span class="text-muted">—</span>
                        @endif
                      </td>
                      <td>
                        @if($entity->status == 1)
                          <span class="badge-active">Active</span>
                        @else
                          <span class="badge-inactive">Inactive</span>
                        @endif
                      </td>
                      <td>
                        @if($entity->is_featured)
                          <span class="badge-featured">⭐ Nổi bật</span>
                        @else
                          —
                        @endif
                      </td>
                      <td>{{ number_format($entity->view_count) }}</td>
                      <td>{{ $entity->created_at->format('d/m/Y H:i') }}</td>
                      <td>
                        <div class="post-actions">
                          <a href="{{ backendRoute('post.edit', ['post' => $entity->id]) }}">
                            <button type="button" class="btn btn-edit"><i class="mdi mdi-pencil"></i> Sửa</button>
                          </a>
                          <form action="{{ backendRoute('post.destroy', ['post' => $entity->id]) }}" method="post">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-del" onclick="return confirm('Xoá bài viết này?')">
                              <i class="mdi mdi-delete"></i> Xóa
                            </button>
                          </form>
                        </div>
                      </td>
                    </tr>
                  @endforeach
                  </tbody>
                </table>

                {{ $data->appends(request()->all())->links('backend.layout.structures._pagination')}}
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
@stop

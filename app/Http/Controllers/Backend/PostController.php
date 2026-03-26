<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class PostController extends Controller
{
    public function index(Request $request)
    {
        $query = Post::with('category')->orderBy('id', 'desc');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        $data = $query->paginate(getConstant('BACKEND_PAGINATE'));
        $categories = Category::orderBy('name')->get();

        return view('backend.post.index', compact('data', 'categories'));
    }

    public function create()
    {
        $categories = Category::where('status', 1)->orderBy('name')->get();
        return view('backend.post.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'title'     => 'required|string|max:255',
            'content'   => 'required|string',
            'thumbnail' => 'nullable|image|max:2048',
        ]);

        try {
            $post = new Post();
            $post->fill($request->only([
                'title', 'slug', 'excerpt', 'content',
                'category_id', 'meta_title', 'meta_description',
                'is_featured', 'status',
            ]));

            // Checkbox is_featured: nếu không check thì = false
            $post->is_featured = $request->has('is_featured') ? 1 : 0;

            if ($request->hasFile('thumbnail')) {
                $path = $request->file('thumbnail')->store('posts', 'public');
                $post->thumbnail = $path;
            }

            $post->save();

            return redirect()->route(backendRouteName('post.index'))
                ->with('notification_success', "Thêm bài viết [{$post->title}] thành công");
        } catch (\Exception $e) {
            Log::error($e);
            return redirect()->back()->with('notification_error', 'Đã có lỗi xảy ra');
        }
    }

    public function edit($id)
    {
        try {
            $data = Post::findOrFail($id);
            $categories = Category::where('status', 1)->orderBy('name')->get();
            return view('backend.post.edit', compact('data', 'categories'));
        } catch (\Exception $e) {
            Log::error($e);
            return redirect()->back()->with('notification_error', 'Không tìm thấy bài viết');
        }
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'title'     => 'required|string|max:255',
            'content'   => 'required|string',
            'thumbnail' => 'nullable|image|max:2048',
        ]);

        try {
            $data = Post::findOrFail($id);
            $data->fill($request->only([
                'title', 'slug', 'excerpt', 'content',
                'category_id', 'meta_title', 'meta_description',
                'status',
            ]));

            $data->is_featured = $request->has('is_featured') ? 1 : 0;

            if ($request->hasFile('thumbnail')) {
                if ($data->thumbnail) {
                    Storage::disk('public')->delete($data->thumbnail);
                }
                $path = $request->file('thumbnail')->store('posts', 'public');
                $data->thumbnail = $path;
            }

            $data->save();

            return redirect()->route(backendRouteName('post.index'))
                ->with('notification_success', "Cập nhật bài viết [{$data->title}] thành công");
        } catch (\Exception $e) {
            Log::error($e);
            return redirect()->back()->with('notification_error', 'Đã có lỗi xảy ra');
        }
    }

    public function destroy($id)
    {
        try {
            $post = Post::findOrFail($id);
            if ($post->thumbnail) {
                Storage::disk('public')->delete($post->thumbnail);
            }
            $post->delete();
            return redirect()->back()->with('notification_success', 'Xóa bài viết thành công');
        } catch (\Exception $e) {
            return redirect()->back()->with('notification_error', 'Đã có lỗi xảy ra');
        }
    }

    public function upload(Request $request)
    {
        $request->validate([
            'file' => ['required', 'image', 'max:5120'],
        ]);

        $path = $request->file('file')->store('posts', 'public');

        return response()->json([
            'location' => asset('storage/' . $path),
        ]);
    }
}

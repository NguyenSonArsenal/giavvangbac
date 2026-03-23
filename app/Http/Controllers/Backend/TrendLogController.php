<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\SilverTrendLog;
use Illuminate\Http\Request;

class TrendLogController extends Controller
{
    public function index()
    {
        $logs = SilverTrendLog::orderByDesc('created_at')->paginate(20);

        return view('backend.trend-log.index', compact('logs'));
    }

    /**
     * AJAX: Toggle đánh giá đúng/sai
     */
    public function toggleAccuracy(Request $request, $id)
    {
        $log = SilverTrendLog::findOrFail($id);
        $log->is_accurate = $request->input('is_accurate');
        $log->admin_note  = $request->input('admin_note', $log->admin_note);
        $log->save();

        return response()->json(['success' => true, 'is_accurate' => $log->is_accurate]);
    }
}

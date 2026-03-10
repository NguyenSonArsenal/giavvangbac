<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\SilverPriceHistory;

class ToolController extends Controller
{
    private $brands = [
        'phuquy'     => ['name' => 'Phú Quý 999',        'api' => 'silver',      'color' => '#b0bec5', 'gradient' => 'linear-gradient(135deg,#b0bec5,#546e7a)', 'icon' => '🥈'],
        'ancarat'    => ['name' => 'Ancarat 999',         'api' => 'ancarat',     'color' => '#06b6d4', 'gradient' => 'linear-gradient(135deg,#06b6d4,#0284c7)', 'icon' => '🏅'],
        'doji'       => ['name' => 'DOJI 99.9',           'api' => 'doji',        'color' => '#f87171', 'gradient' => 'linear-gradient(135deg,#dc2626,#991b1b)', 'icon' => '🔴'],
        'kimnganphuc'=> ['name' => 'Kim Ngân Phúc 999',  'api' => 'kimnganphuc', 'color' => '#a78bfa', 'gradient' => 'linear-gradient(135deg,#a78bfa,#7c3aed)', 'icon' => 'KNP'],
    ];

    /** GET /quy-doi-bac */
    public function quyDoi()
    {
        // SSR: lấy giá hiện tại của tất cả brand để pre-fill calculator
        $prices = [];
        foreach ($this->brands as $key => $brand) {
            $source = $key;
            $units  = ($key === 'doji') ? ['LUONG'] : ['KG', 'LUONG'];
            foreach ($units as $unit) {
                $row = SilverPriceHistory::where('source', $source)
                    ->where('unit', $unit)
                    ->orderByDesc('recorded_at')
                    ->first();
                if ($row) {
                    $prices[$key][$unit] = ['buy' => $row->buy_price, 'sell' => $row->sell_price];
                }
            }
        }

        return view('frontend.tool.quy-doi-bac', [
            'brands' => $this->brands,
            'prices' => $prices,
        ]);
    }

    /** GET /so-sanh-gia-bac */
    public function soSanh()
    {
        // SSR: build comparison table data
        $rows = [];
        foreach ($this->brands as $key => $brand) {
            $units = ($key === 'doji') ? ['LUONG'] : ['KG', 'LUONG'];
            foreach ($units as $unit) {
                $row = SilverPriceHistory::where('source', $key)
                    ->where('unit', $unit)
                    ->orderByDesc('recorded_at')
                    ->first();
                if ($row) {
                    $rows[] = [
                        'brand_key'   => $key,
                        'brand_name'  => $brand['name'],
                        'brand_color' => $brand['color'],
                        'brand_icon'  => $brand['icon'],
                        'brand_api'   => $brand['api'],
                        'unit'        => $unit,
                        'unit_label'  => $unit === 'KG' ? 'KG' : '1 Lượng',
                        'buy'         => $row->buy_price,
                        'sell'        => $row->sell_price,
                        'spread'      => $row->sell_price - $row->buy_price,
                        'updated_at'  => $row->recorded_at ? $row->recorded_at->format('H:i d/m') : null,
                    ];
                }
            }
        }

        return view('frontend.tool.so-sanh-gia-bac', [
            'brands' => $this->brands,
            'rows'   => $rows,
        ]);
    }

    /** GET /lich-su-gia-bac */
    public function lichSu()
    {
        return view('frontend.tool.lich-su-gia-bac', [
            'brands' => $this->brands,
        ]);
    }
}

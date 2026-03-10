<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;

class ArticleController extends Controller
{
    /** GET /bac-999-la-gi */
    public function bac999LaGi()
    {
        return view('frontend.article.bac-999-la-gi');
    }

    /** GET /nen-mua-bac-o-dau */
    public function nenMuaBacODau()
    {
        return view('frontend.article.nen-mua-bac-o-dau');
    }

    /** GET /bac-co-phai-kenh-dau-tu-tot */
    public function dauTuBac()
    {
        return view('frontend.article.bac-co-phai-kenh-dau-tu-tot');
    }
}

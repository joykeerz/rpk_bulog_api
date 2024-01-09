<?php

namespace App\Http\Controllers;

use App\Models\PosProfile;
use Illuminate\Http\Request;

class PosProfileController extends Controller
{
    public function getUserProfile()
    {
        $profile = PosProfile::where('user_id', 4)->first();

        if (empty($profile)) {
            return response()->json([
                'error' => "there's no data yet"
            ], 404);
        };

        return response()->json($profile, 200);
    }

}

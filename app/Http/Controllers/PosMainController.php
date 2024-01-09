<?php

namespace App\Http\Controllers;

use App\Models\PosEmployee;
use Illuminate\Http\Request;

class PosMainController extends Controller
{
    //
    public function getUserEmployee()
    {
        $employee = PosEmployee::where('profile_id', 1)->first();

        if (empty($employee)) {
            return response()->json([
                'error' => "there's no data yet"
            ], 404);
        };

        return response()->json($employee, 200);
    }
}

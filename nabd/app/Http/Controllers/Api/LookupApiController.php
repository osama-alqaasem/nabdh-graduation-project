<?php

namespace App\Http\Controllers\Api;

use App\Models\Symptom;
use App\Models\Test;

class LookupApiController extends ApiController
{
    public function symptoms() { return response()->json(['data' => Symptom::orderBy('name')->get(['id','name'])]); }
    public function tests() { return response()->json(['data' => Test::orderBy('name')->get(['id','name'])]); }
}

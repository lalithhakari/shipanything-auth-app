<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;

class TestController extends Controller
{
    public function test()
    {
        Cache::put('test_cache', 'This is a test value');
        Redis::set('test_redis', 'This is a test value');

        $dbRow = DB::table('test')->first();

        return response()->json([
            'cacheTest' => Cache::get('test_cache'),
            'redisTest' => Redis::get('test_cache'),
            'dbTest' => $dbRow

        ]);
    }
}

<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

use App\Models\UserFeed;

class UserController extends Controller
{
    //
    public function feed(Request $request)
    {
        $userId = auth()->id();
        $feed = UserFeed::where('userId', $userId)->get();
        if (count($feed) > 0) {
            $feed = $feed[0];
            Log::channel('stderr')->info($feed);
            return response()->json([
                'categories' => array_filter(explode(",", $feed['categories'])),
                'sources' => array_filter(explode(",", $feed['sources'])),
                'author' => $feed['author']
            ]);
        }
        return response()->json([]);
    }

    public function saveFeed(Request $request)
    {
        $userId = auth()->id();

        Log::channel('stderr')->info("===update:" . $userId);
        $categories = $request->input('categories', "");
        $sources = $request->input('sources', "");
        $author = $request->input('author', "");

        Log::channel('stderr')->info($categories);
        Log::channel('stderr')->info($sources);
        Log::channel('stderr')->info($author);

        UserFeed::updateOrCreate(
        [
            'userId' => $userId,
        ],
        [
            'categories' => $categories,
            'sources' => $sources,
            'author' => $author
        ]
        );

        return response()->json([
            'categories' => array_filter(explode(",", $categories)),
            'sources' => array_filter(explode(",", $sources)),
            'author' => $author
        ]);
    }
}

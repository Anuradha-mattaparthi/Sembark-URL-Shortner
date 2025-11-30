<?php

namespace App\Http\Controllers;

use App\Jobs\RecordShortUrlClick;
use App\Models\ShortUrl;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class ShortUrlController extends Controller
{
    // Store a new short URL

    public function store(Request $request)
    {
        $data = $request->validate([
            'long_url' => ['required', 'url', 'max:2000'],
        ]);

        $user = Auth::user();

        try {
            $code = $this->generateUniqueCode();

            $short = ShortUrl::create([
                'user_id'    => $user?->id,
                'company_id' => $user?->company_id ?? null,
                'long_url'   => $data['long_url'],
                'short_code' => $code,
                'hits'       => 0,
            ]);

            $domain = rtrim(env('SHORT_DOMAIN', config('app.url')), '/');
            $full = $domain . '/r/' . $short->short_code;

            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'short_url'  => $full,
                    'short_code' => $short->short_code,
                    'company_id' => $short->company_id,
                    'long_url'   => $short->long_url,
                    'hits'       => $short->hits,
                    'created_at' => $short->created_at,
                ], 201);
            }

            return back()->with('success', 'Short URL created')->with('short_url', $full);
        } catch (\Throwable $e) {
            Log::error('ShortUrl store error: ' . $e->getMessage(), ['exception' => $e]);

            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'message' => 'Server error: ' . $e->getMessage()
                ], 500);
            }

            return back()->withErrors('Could not create short URL');
        }
    }

    //Redirect short code -> long URL (public route).

    public function redirect($code, Request $request)
{
    $short = ShortUrl::where('short_code', $code)->first()
        ?: ShortUrl::where('short_code', Str::lower($code))->first();

    if (! $short) {
        abort(404, 'Short URL not found');
    }

    // Atomic increment
    try {
        $short->increment('hits');
    } catch (\Throwable $e) {
        Log::warning("Failed incrementing hits for short_code {$code}: " . $e->getMessage());
    }

    // Dispatch job to record click asynchronously
    RecordShortUrlClick::dispatch(
        $short->id,
        $short->company_id,
        $short->user_id,
        $request->ip(),
        $request->userAgent(),
        $request->headers->get('referer'),
        null // optional country, fill if you do geo lookup
    );

    return redirect()->away($short->long_url);
}

   //Generate a unique short code.

    protected function generateUniqueCode($length = 6)
    {
        do {
            $code = Str::lower(Str::random($length));
        } while (ShortUrl::where('short_code', $code)->exists());

        return $code;
    }
}

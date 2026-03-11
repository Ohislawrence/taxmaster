<?php

namespace App\Http\Controllers\PublicApi;

use App\Http\Controllers\Controller;
use App\Services\PublicAiAgentService;
use Illuminate\Http\Request;

class PublicAiController extends Controller
{
    /**
     * Send message to AI (public, guest access)
     */
    public function sendMessage(Request $request)
    {
        return response()->json([
            'error' => 'AI chat is not available for guests.'
        ], 403);
    }
}

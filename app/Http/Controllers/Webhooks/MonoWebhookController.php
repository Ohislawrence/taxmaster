<?php

namespace App\Http\Controllers\Webhooks;

use App\Http\Controllers\Controller;
use App\Services\MonoIntegrationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class MonoWebhookController extends Controller
{
    /**
     * Handle Mono webhook events
     */
    public function handle(Request $request, MonoIntegrationService $monoService)
    {
        $payload = $request->getContent();
        $signature = $request->header('mono-signature') ?? $request->header('x-mono-signature');

        if (!$signature) {
            Log::warning('Mono webhook signature missing');
            return response()->json(['message' => 'Signature missing'], 401);
        }

        if (!$monoService->verifyWebhookSignature($payload, $signature)) {
            Log::warning('Mono webhook signature invalid');
            return response()->json(['message' => 'Invalid signature'], 401);
        }

        $event = $request->json()->all();
        if (!$event) {
            Log::warning('Mono webhook payload invalid');
            return response()->json(['message' => 'Invalid payload'], 400);
        }

        $monoService->handleWebhook($event);

        return response()->json(['status' => 'ok']);
    }
}

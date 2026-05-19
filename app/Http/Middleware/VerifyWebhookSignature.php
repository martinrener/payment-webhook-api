<?php

namespace App\Http\Middleware;
use Closure;
use Illuminate\Http\Request;

class VerifyWebhookSignature
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        // Verify the webhook signature here
        // You can use the secret key provided by the webhook provider to verify the signature

        $signature = $request->header('X-Webhook-Signature');
        $payload = $request->getContent();
        $secret = config('services.webhook_secret');

        if (!$this->isValidSignature($signature, $payload, $secret)) {
            return response()->json(['error' => 'Invalid signature'], 401);
        }

        return $next($request);
    }

    private function isValidSignature($signature, $payload, $secret)
    {
        // Implement your signature verification logic here
        // This is just a placeholder and should be replaced with actual logic

        $expectedSignature = hash_hmac('sha256', $payload, $secret);

        return hash_equals($expectedSignature, $signature);
    }
}
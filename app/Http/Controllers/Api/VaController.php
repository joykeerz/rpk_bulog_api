<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;

class VaController extends Controller
{
    public function getToken()
    {
        $client_id = "GVotGizvjghnt1wusGNdG8DE9QpmpiTf";
        // $timestamp = now()->format('Y-m-d\TH:i:s.v\Z');
        $timestamp = now()->format('Y-m-d\TH:i:s.vP'); /// ini ISO8601
        // $timestamp = trim(now(), '"');
        // $timestamp = 1718269573;
        // $timestamp = '2024-06-13T09:06:13+0000';
        $string_to_sign = $client_id . '|' . $timestamp;

        // Load your RSA private key from storage or environment
        $private_key = env('VA_PRIVATE_KEY');

        // Load the private key
        $private_key_resource = openssl_pkey_get_private($private_key);

        if (!$private_key_resource) {
            return response()->json(['error' => 'Failed to load private key'], 500);
        }

        // Sign the string using SHA256 with RSA
        openssl_sign($string_to_sign, $signature, $private_key_resource, OPENSSL_ALGO_SHA256);

        // Encode the signature in base64
        $x_signature = base64_encode($signature);

        $headers = [
            'X-CLIENT-KEY' => $client_id,
            'X-TIMESTAMP' => $timestamp,
            'X-SIGNATURE' => $x_signature,
            'Content-Type' => 'application/json',
        ];

        $body = [
            'grantType' => 'client_credentials'
        ];

        $response = Http::withHeaders($headers)->post('https://sandbox.partner.api.bri.co.id/snap/v1.0/access-token/b2b', $body);

        return response()->json($response->json(), $response->status());
    }

    public function generateSignature()
    {
        $client_id = 'GVotGizvjghnt1wusGNdG8DE9QpmpiTf';
        $timestamp = now()->format('Y-m-d\TH:i:s.v\Z');
        $timestamp2 = now()->format('Y-m-d\TH:i:s.vP');
        $timestamp3 = trim(now(), '"');

        $string_to_sign = $client_id . '|' . $timestamp;

        // Load your RSA private key from storage or environment
        $private_key = env('VA_PRIVATE_KEY');

        // Load the private key
        $private_key_resource = openssl_pkey_get_private($private_key);

        if (!$private_key_resource) {
            return response()->json(['error' => 'Failed to load private key'], 500);
        }

        // Sign the string using SHA256 with RSA
        openssl_sign($string_to_sign, $signature, $private_key_resource, OPENSSL_ALGO_SHA256);

        // Encode the signature in base64
        $x_signature = base64_encode($signature);

        return response()->json([
            'X-CLIENT-KEY' => $client_id,
            'X-TIMESTAMP' => $timestamp,
            'X-TIMESTAMP_2' => $timestamp2,
            'X-TIMESTAMP_3' => $timestamp3,
            'X-SIGNATURE' => $x_signature,
        ]);
    }

    public function getTokenOld()
    {
        $client_id = "GVotGizvjghnt1wusGNdG8DE9QpmpiTf";
        $client_secret = "6pNOtn6H79DKZZ7c";
    }
}

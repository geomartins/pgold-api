<?php

namespace App\Http\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class APICallsService
{

    public static function makeAPICall($full_url, $data, $method, $headerArray= ['Content-Type: application/json'], $bearer_token='', $basic_auth=[], $attachment = []){
        try{
            if($method == 'POST'){
                if(!empty($bearer_token)){
                    if(count($attachment) < 1){
                        $response = Http::withHeaders($headerArray)->withToken($bearer_token)->withoutVerifying()->post($full_url, $data);
                        return json_decode($response->body(), true);
                    }else{
                        $response = Http::withHeaders($headerArray)
                        ->attach(
                            $attachment['field'] ?? "",      // The form field name (e.g., 'image')
                            $attachment['file'] ?? "",       // The file content (binary data)
                            $attachment['filename'] ?? ""   // The file name (e.g., 'image.jpeg')
                        )
                        ->withToken($bearer_token)->withoutVerifying()->post($full_url, $data);

                        return json_decode($response->body(), true);
                    }

                }else if(!empty($basic_auth)){
                    $response = Http::withBasicAuth($basic_auth['username'], $basic_auth['password'])->withHeaders($headerArray)->withoutVerifying()->post($full_url, $data);
                    return json_decode($response->body(), true);
                }
                    $response = Http::withHeaders($headerArray)->withoutVerifying()->post($full_url, $data);
                    // Log::info(json_decode($response, true));
                    return json_decode($response->body(), true);
            }


            else if($method == 'GET'){
                if(!empty($bearer_token)){
                    if(empty($data)){
                        $response = Http::withToken($bearer_token)->withHeaders($headerArray)->withoutVerifying()->get($full_url);
                    }else{
                        $response = Http::withToken($bearer_token)->withHeaders($headerArray)->withoutVerifying()->get($full_url, $data);
                    }

                    return json_decode($response->body(), true);
                }else if(!empty($basic_auth)){
                    $response = Http::withBasicAuth($basic_auth['username'],$basic_auth['password'])->withHeaders($headerArray)->withoutVerifying()->get($full_url);
                    return json_decode($response->body(), true);
                }else if(array_key_exists('X-API-KEY', $headerArray)){
                    $response = Http::withHeaders($headerArray)->withoutVerifying()->get($full_url,$data);
                    return json_decode($response->body(), true);
                }
                $response = Http::withHeaders($headerArray)->withoutVerifying()->get($full_url);
                return json_decode($response->body(), true);
            }

            else if ($method == 'PUT') {
                if (!empty($bearer_token)) {
                    if (count($attachment) < 1) {
                        $response = Http::withHeaders($headerArray)
                            ->withToken($bearer_token)
                            ->withoutVerifying()
                            ->put($full_url, $data);
                        return json_decode($response->body(), true);
                    } else {
                        $response = Http::withHeaders($headerArray)
                            ->attach(
                                $attachment['field'] ?? "",
                                $attachment['file'] ?? "",
                                $attachment['filename'] ?? ""
                            )
                            ->withToken($bearer_token)
                            ->withoutVerifying()
                            ->put($full_url, $data);

                        return json_decode($response->body(), true);
                    }
                } else if (!empty($basic_auth)) {
                    $response = Http::withBasicAuth(
                            $basic_auth['username'],
                            $basic_auth['password']
                        )
                        ->withHeaders($headerArray)
                        ->withoutVerifying()
                        ->put($full_url, $data);
                    return json_decode($response->body(), true);
                }

                $response = Http::withHeaders($headerArray)
                    ->withoutVerifying()
                    ->put($full_url, $data);
                return json_decode($response->body(), true);
            }
            
        }
        catch(\Exception $e){
            Log::error("API call to ". $full_url." failed!");
            return false;
        }
    }

}

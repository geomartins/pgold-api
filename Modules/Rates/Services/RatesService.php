<?php

namespace Modules\Rates\Services;

use Illuminate\Support\Facades\Log;
use App\Http\Services\APICallsService;

class RatesService
{

    private function getHeaders(): array
    {
        return [
            'Content-Type' => 'application/json'
        ];
    }

    public function getGiftCardRates(): array
    {
        try {

            // Step 1: Prepare data 
            $fullUrl = config('app.sandbox_base_url') . "/api/guest/giftcards";
            $headerArray = $this->getHeaders();

            // Step2: Make Api call
            $call = APICallsService::makeAPICall($fullUrl, [], 'GET', $headerArray);

            //Step 3: Returns error if failed
            if (!$call) {
                Log::error('Giftcards api call failed ', ['response' => $call]);
                return [false, "unable to make API call to fetch Giftcards  list"];
            }
            if (!isset($call["all_giftcards"])) {
                Log::error('Giftcard list failed response', ['response' => $call]);
                return [false, "unable to fetch Giftcard list at this time"];
            }

            // Step 4: Return data if successful
            return [true, $call["all_giftcards"]];
        } catch (\Exception $e) {
            Log::error($e);
            return [false, $e->getMessage()];
        }
    }

    public function getCryptoRates(): array
    {
        try {

            // Step 1: Prepare data 
            $fullUrl = config('app.sandbox_base_url') . "/api/guest/cryptocurrencies";
            $headerArray = $this->getHeaders();

            // Step2: Make Api call
            $call = APICallsService::makeAPICall($fullUrl, [], 'GET', $headerArray);

            //Step 3: Returns error if failed
            if (!$call) {
                Log::error('Crypto api call failed ', ['response' => $call]);
                return [false, "unable to make API call to fetch Crypto list"];
            }
            if (!isset($call["data"])) {
                Log::error('Crypto list failed response', ['response' => $call]);
                return [false, "unable to fetch Crypto list at this time"];
            }

            // Step 4: Return data if successful
            return [true, $call["data"]];
        } catch (\Exception $e) {
            Log::error($e);
            return [false, $e->getMessage()];
        }
    }
}

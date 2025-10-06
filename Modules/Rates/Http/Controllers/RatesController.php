<?php

namespace Modules\Rates\Http\Controllers;

use Illuminate\Routing\Controller;
use Modules\Rates\Services\RatesService;

class RatesController extends Controller
{
    private RatesService $ratesService;

    public function __construct(RatesService $ratesService)
    {
        $this->ratesService = $ratesService;
    }

    /**
     * Get Gift Card Rates
     */
    public function giftCards()
    {
        [$status, $data] = $this->ratesService->getGiftCardRates();

        return response()->json([
            'status' => $status,
            'data' => $status ? $data : null,
            'message' => $status ? 'Gift Card rates fetched successfully' : $data
        ], $status ? 200 : 400);
    }

    /**
     * Get Crypto Rates
     */
    public function crypto()
    {
        [$status, $data] = $this->ratesService->getCryptoRates();

        return response()->json([
            'status' => $status,
            'data' => $status ? $data : null,
            'message' => $status ? 'Crypto rates fetched successfully' : $data
        ], $status ? 200 : 400);
    }
}

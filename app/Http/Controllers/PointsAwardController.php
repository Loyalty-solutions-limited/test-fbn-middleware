<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class PointsAwardController extends Controller
{
    protected $url;

    public function __construct()
    {
        
        $this->url = config('externalservices.NON_FINANCIAL_TRANSACTION');
    }


    public function award(Request $request)
    {
        $validated = $request->validate([
            'pointValue'         => ['required', 'numeric'],
            'points'             => ['required', 'numeric'],
            'amount'             => ['required', 'numeric'],
            'transactionDate'    => ['required'],
            'transactionId'      => ['required'],
            'membershipId'       => ['required'],
            'transactionChannel' => ['nullable'],
            'sourceAccount'      => ['nullable'] ,
        ]);

        $response = json_decode($this->makeCurl($this->url, 'POST', json_encode($validated)), true);

        if (is_array($response) && isset($response['responseCode']) && $response['responseCode'] === '00') {
            return response()->json([
                'responseCode'    => '00',
                'responseMessage' => $response['responseMessage'] ?? 'Successful',
            ]);
        }

        return response()->json([
            'responseCode'    => is_array($response) ? ($response['responseCode'] ?? '99') : '99',
            'responseMessage' => is_array($response) ? ($response['responseMessage'] ?? 'Point award failed') : 'Point award failed',
        ]);
    }

    public function makeCurl($url, $action, $data)
    {
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => $action,
            CURLOPT_POSTFIELDS => $data,
            CURLOPT_HTTPHEADER => array(
                'Accept: application/json',
                'AppId: ' . config('externalservices.POINTS_TO_CASH_APPID'),
                'AppKey: ' . config('externalservices.POINTS_TO_CASH_APPKEY'),
                'Content-Type: application/json',
            ),
        ));

        $response = curl_exec($curl);
        curl_close($curl);

        return $response;
    }
}

<?php

namespace Dizatech\RayanpayIpg;

use Exception;
use stdClass;

class RayanpayIpg
{
    protected $merchant_id;
    protected $http_client;

    public function __construct($args = [])
    {
        $this->merchant_id = $args['merchantId'];
        $this->http_client = new \GuzzleHttp\Client();
    }

    public function getToken($amount, $redirect_address)
    {
        $result = new stdClass();
        try {
            $response = $this->http_client->request(
                'POST',
                'https://pms.rayanpay.com/api/v2/ipg/paymentrequest',
                [
                    'headers'           => [
                        'Content-Type'  => 'application/json'
                    ],
                    'body'              => json_encode([
                        'merchantID'    => $this->merchant_id,
                        'amount'        => $amount,
                        'callbackURL'   => $redirect_address
                    ])
                ]
            );
            if ($response->getStatusCode() == 200) {
                $body = $response->getBody();
                $contents = $body->getContents();
                $contents = json_decode($contents);

                if (
                    isset($contents->status) &&
                    $contents->status == 100 &&
                    isset($contents->authority)
                ) {
                    $result->status = 'success';
                    $result->token = $contents->authority;
                } else {
                    $message = 'خطا در اتصال به درگاه پرداخت!';
                    if (isset($contents->status)) {
                        $message .= " کد خطا: {$contents->status}";
                    }
                    $result->status = 'error';
                    $result->message = $message;
                }
            } else {
                $result->status = 'error';
                $result->message = 'خطا در اتصال به درگاه پرداخت!';
            }
        } catch (Exception $exception) {
            $result->status = 'error';
            $result->message = 'خطا در اتصال به درگاه پرداخت!';
        }

        return $result;
    }

    public function verifyRequest($amount, $token)
    {
        $result = new stdClass();

        try {
            $response = $this->http_client->request(
                'POST',
                'https://pms.rayanpay.com/api/v2/ipg/paymentVerification',
                [
                    'json'   => [
                        'merchantID'    => $this->merchant_id,
                        'amount'        => $amount,
                        'authority'     => $token
                    ]
                ]
            );

            if ($response->getStatusCode() == 200) {
                $body = $response->getBody();
                $contents = $body->getContents();
                $contents = json_decode($contents);

                if (isset($contents->status) && $contents->status == 100) {
                    $result->status = 'success';
                    $result->ref_id = $contents->refID;
                } else {
                    $message = 'خطا در تایید پرداخت!';
                    if (isset($contents->status)) {
                        $message .= " کد خطا: {$contents->status}";
                    }
                    $result->status = 'error';
                    $result->message = $message;
                }
            } else {
                $result->status = 'error';
                $result->message = 'خطا در تایید تراکنش!';
            }
        } catch (Exception $exception) {
            $result->status = 'error';
            $result->message = 'خطا در تایید تراکنش!';
        }

        return $result;
    }
}

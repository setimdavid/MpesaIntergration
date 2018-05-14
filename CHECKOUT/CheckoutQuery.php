<?php
include_once '..' . DIRECTORY_SEPARATOR . 'config.php';
include_once '..' . DIRECTORY_SEPARATOR . 'Functions.php';

class MPESACHECKOUTQuery
{

    private $configs;

    private $functions;

    private $CHECKOUTQUERYURL;

    public function __construct()
    {
        $this->configs = Configs::$configs;
        $this->functions = new Functions(__CLASS__);

        $this->loadConfig();
    }

    public function init()
    {
        $dataStream = file_get_contents('php://input');
        $this->functions->writeLog('CHECKOUT', 'requests', __LINE__, 'Request From channel :- ' . $dataStream);
        if ($dataStream === null) {
            $error = array(
                'STATUS' => '99',
                'STATUSDESCRIPTION' => 'Request cannot be empty.'
            );
            $this->functions->writeLog('CHECKOUT', 'error', __LINE__, 'Empty request');
            return json_encode($error);
        } else {

            $validated = $this->validateRequest($dataStream);
            if ($validated['STATUS'] !== '00') {
                $toChannel = json_encode($this->functions->removeSensitiveData(array_merge($validated, json_decode($dataStream, true))));
            } else {
                $authentication = $this->functions->getAuthToken($validated);
                if ($authentication['STATUS'] === '00') {
                    $b2cheader = array(
                        'Content-type: application/json',
                        'Authorization: Bearer ' . $authentication['STATUSDESCRIPTION']
                    );
                    $toMpesa = $this->prepareSafCheckoutJson($validated);
                    $this->functions->writeLog('CHECKOUT', 'requests', __LINE__, 'JSON to MPESA :- ' . $toMpesa);
                    $respo = $this->functions->curlPostToURL($this->CHECKOUTQUERYURL, $toMpesa, $b2cheader);
                    $this->functions->writeLog('CHECKOUT', 'requests', __LINE__, 'JSON from MPESA :- ' . $respo);
                    $toChannel = $this->functions->processResponse(json_decode($respo, true), $validated);
                } else {
                    $toChannel = $authentication;
                }
                $toChannel = json_encode($this->functions->removeSensitiveData($toChannel));
            }
            $this->functions->writeLog('CHECKOUT', 'requests', __LINE__, 'Response to channel :- ' . $toChannel);
            return $toChannel;
        }
    }

    private function prepareSafCheckoutJson($data)
    {
        $t = time();
        $timestamp = date('Ymdhis', $t); // yyyymmddhhiiss
        $shortcode = isset($data['SHORTCODE']) === true ? $data['SHORTCODE'] : '';
        $passkey = isset($data['PASSKEY']) === true ? $data['PASSKEY'] : '';
        $safjson = array(
            'BusinessShortCode' => $shortcode,
            'Password' => base64_encode($shortcode . $passkey . $timestamp),
            'Timestamp' => $timestamp,
            'CheckoutRequestID' => isset($data['TRANSACTIONID']) === true ? $data['TRANSACTIONID'] : ''
        );
        return json_encode($safjson);
    }

    private function validateRequest($data)
    {
        $jsondata = json_decode($data, true);
        if ($jsondata === null) {
            $response = array(
                'STATUS' => '99',
                'STATUSDESCRIPTION' => 'INVALID JSON REQUEST ',
                'ORIGINAL_REQUEST' => $data
            );
        } else if (! array_key_exists('TRANSACTIONID', $jsondata) || empty($jsondata['TRANSACTIONID'])) {
            $response = array(
                'STATUS' => '55',
                'STATUSDESCRIPTION' => '(TRANSACTIONID) in request cannot be empty.',
                'ORIGINAL_REQUEST' => $jsondata
            );
        } else if (! array_key_exists('PASSKEY', $jsondata) || empty($jsondata['PASSKEY'])) {
            $response = array(
                'STATUS' => '55',
                'STATUSDESCRIPTION' => '(PASSKEY) in request cannot be empty.',
                'ORIGINAL_REQUEST' => $jsondata
            );
        } else if (! array_key_exists('SHORTCODE', $jsondata) || empty($jsondata['SHORTCODE'])) {
            $response = array(
                'STATUS' => '55',
                'STATUSDESCRIPTION' => '(SHORTCODE) in request cannot be empty.',
                'ORIGINAL_REQUEST' => $jsondata
            );
        } else if (! array_key_exists('CONSUMERKEY', $jsondata) || empty($jsondata['CONSUMERKEY'])) {
            $response = array(
                'STATUS' => '55',
                'STATUSDESCRIPTION' => '(CONSUMERKEY) in request cannot be empty.',
                'ORIGINAL_REQUEST' => $jsondata
            );
        } else if (! array_key_exists('CONSUMERSECRET', $jsondata) || empty($jsondata['CONSUMERSECRET'])) {
            $response = array(
                'STATUS' => '55',
                'STATUSDESCRIPTION' => '(CONSUMERSECRET) in request cannot be empty.',
                'ORIGINAL_REQUEST' => $jsondata
            );
        } else {
            $response = array();
            $response = $jsondata;
            $response['STATUS'] = '00';
        }
        if ($response['STATUS'] !== '00') {
            $temp = $this->functions->removeSensitiveData($response['ORIGINAL_REQUEST']);
            $response['ORIGINAL_REQUEST'] = $temp;
        }
        return $response;
    }

    public function loadConfig()
    {
        $this->CHECKOUTQUERYURL = $this->configs['CHECKOUT']['CHECKOUTQUERYURL'];
    }
}

header('Content-type: application/json; charset=utf-8');
$checkoutquery = new MPESACHECKOUTQuery();
echo $checkoutquery->init();

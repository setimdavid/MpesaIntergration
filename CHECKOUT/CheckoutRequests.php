<?php
include_once '..' . DIRECTORY_SEPARATOR . 'config.php';
include_once '..' . DIRECTORY_SEPARATOR . 'Functions.php';

class MPESACHECKOUTRequest
{

    private $configs;

    private $functions;

    private $MPESACHECKOUTURL;

    private $RESULTURL;

    const ACCOUNT = 'ACCOUNT';

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
                $combined = $this->functions->removeSensitiveData(array_merge($validated, json_decode($dataStream, true)));  
				$combined['ORIGINAL_RESPONSE']='{}';
				return json_encode($combined);
			} else {
                $authentication = $this->functions->getAuthToken($validated);
                if ($authentication['STATUS'] === '00') {
                    $b2cheader = array(
                        'Content-type: application/json',
                        'Authorization: Bearer ' . $authentication['STATUSDESCRIPTION']
                    );
                    $toMpesa = $this->prepareSafaricomRequest($validated);
                    $this->functions->writeLog('CHECKOUT', 'requests', __LINE__, 'JSON to MPESA :- ' . $toMpesa);
                    $respo = $this->functions->curlPostToURL($this->MPESACHECKOUTURL, $toMpesa, $b2cheader);
                    $this->functions->writeLog('CHECKOUT', 'requests', __LINE__, 'JSON from MPESA :- ' . $respo);
                    $toChannel = $this->functions->processResponse(json_decode($respo, true), $validated);
                } else {
                    $toChannel = $authentication;
                }
                return json_encode($this->functions->removeSensitiveData($toChannel));
            }
        }
    }

    private function prepareSafaricomRequest($data)
    {
        $t = time();
        $timestamp = date('Ymdhis', $t); // yyyymmddhhiiss
        $shortcode = isset($data['SHORTCODE']) === true ? $data['SHORTCODE'] : '';
        $passkey = isset($data['PASSKEY']) === true ? $data['PASSKEY'] : '';
        $account = isset($data[$this::ACCOUNT]) === true ? $data[$this::ACCOUNT] : '';
        $msisdn = isset($data['MSISDN']) === true ? $data['MSISDN'] : '';
        $safjson = array(
            'BusinessShortCode' => $shortcode,
            'Password' => base64_encode($shortcode . $passkey . $timestamp),
            'Timestamp' => $timestamp,
            'TransactionType' => 'CustomerPayBillOnline',
            'Amount' => isset($data['AMOUNT']) === true ? $data['AMOUNT'] : '',
            'PartyA' => $account, // The entity sending the funds
            'PartyB' => $shortcode,
            'PhoneNumber' => $msisdn, // The MSISDN sending the funds
            'CallBackURL' => $this->RESULTURL,
            'AccountReference' => isset($data['TRANSACTIONID']) === true ? $data['TRANSACTIONID'] : '',
            'TransactionDesc' => isset($data['NARRATION']) === true ? $data['NARRATION'] : 'CHECKOUT FOR ' . $shortcode
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
        } else if (! array_key_exists('AMOUNT', $jsondata) || empty($jsondata['AMOUNT'])) {
            $response = array(
                'STATUS' => '55',
                'STATUSDESCRIPTION' => '(AMOUNT) in request cannot be empty.',
                'ORIGINAL_REQUEST' => $jsondata
            );
        } else if (! array_key_exists('TRANSACTIONID', $jsondata) || empty($jsondata['TRANSACTIONID'])) {
            $response = array(
                'STATUS' => '55',
                'STATUSDESCRIPTION' => '(TRANSACTIONID) in request cannot be empty.',
                'ORIGINAL_REQUEST' => $jsondata
            );
        } else if (! array_key_exists('NARRATION', $jsondata) || empty($jsondata['NARRATION'])) {
            $response = array(
                'STATUS' => '55',
                'STATUSDESCRIPTION' => '(NARRATION) in request cannot be empty.',
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
        } else if (! array_key_exists('CONSUMERSECRET', $jsondata) || empty($jsondata['CONSUMERSECRET'])) {
            $response = array(
                'STATUS' => '55',
                'STATUSDESCRIPTION' => '(CONSUMERSECRET) in request cannot be empty.',
                'ORIGINAL_REQUEST' => $jsondata
            );
        } else if (! array_key_exists('CONSUMERKEY', $jsondata) || empty($jsondata['CONSUMERKEY'])) {
            $response = array(
                'STATUS' => '55',
                'STATUSDESCRIPTION' => '(CONSUMERKEY) in request cannot be empty.',
                'ORIGINAL_REQUEST' => $jsondata
            );
        } else if (! array_key_exists('MSISDN', $jsondata) || empty($jsondata['MSISDN'])) {
            $response = array(
                'STATUS' => '55',
                'STATUSDESCRIPTION' => '(MSISDN) in request cannot be empty.',
                'ORIGINAL_REQUEST' => $jsondata
            );
        } else if (! array_key_exists($this::ACCOUNT, $jsondata) || empty($jsondata[$this::ACCOUNT])) {
            $response = array(
                'STATUS' => '55',
                'STATUSDESCRIPTION' => '(ACCOUNT) in request cannot be empty.',
                'ORIGINAL_REQUEST' => $jsondata
            );
        } else if (strlen(trim($jsondata[$this::ACCOUNT])) !== 12) {
            $response = array(
                'STATUS' => '55',
                'STATUSDESCRIPTION' => '(ACCOUNT) in request has invalid lenght.',
                'ORIGINAL_REQUEST' => $jsondata
            );
        } else if (! strncmp($jsondata[$this::ACCOUNT], '254', 3) === 0) {
            $response = array(
                'STATUS' => '55',
                'STATUSDESCRIPTION' => '(ACCOUNT) in request has invalid format.start with 254',
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
        $this->MPESACHECKOUTURL = $this->configs['CHECKOUT']['CHECKOUTREQUESTURL'];
        $this->RESULTURL = $this->configs['CHECKOUT']['RESULTURL'];
    }
}

header('Content-type: application/json; charset=utf-8');

$checkoutrequest = new MPESACHECKOUTRequest();
echo $checkoutrequest->init();
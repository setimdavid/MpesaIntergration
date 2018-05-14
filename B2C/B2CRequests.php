<?php
include_once '..' . DIRECTORY_SEPARATOR . 'config.php';
include_once '..' . DIRECTORY_SEPARATOR . 'Functions.php';

class MpesaB2CRequests
{

    private $configs;

    private $functions;

    private $MPESAB2CSERVICEURL;

    private $QUEUETIMEOUTURL;

    private $RESULTURL;

    private $OCCATION;

    private $SEC;

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
        $this->functions->writeLog('B2C', 'requests', __LINE__, 'Request From channel :- ' . $dataStream);
        if ($dataStream == null) {
            $error = array(
                'STATUS' => '99',
                'STATUSDESCRIPTION' => 'Request cannot be empty.'
            );
            $this->functions->writeLog('B2C', 'error', __LINE__, 'Empty request');
            return json_encode($error);
        } else {

            $validated = $this->validateRequest($dataStream);
            if ($validated['STATUS'] !== '00') {
                $toChannel = json_encode(array_merge($validated, json_decode($dataStream, true)));
            } else {
                $authentication = $this->functions->getAuthToken($validated);
                if ($authentication['STATUS'] === '00') {
                    $b2cheader = array(
                        'Content-type: application/json',
                        'Authorization: Bearer ' . $authentication['STATUSDESCRIPTION']
                    );
                    $toMpesa = $this->prepareSafcomB2CJson($validated);
                    $this->functions->writeLog('B2C', 'requests', __LINE__, 'JSON to MPESA :- ' . $toMpesa);
                    $respo = $this->functions->curlPostToURL($this->MPESAB2CSERVICEURL, $toMpesa, $b2cheader);
                    $this->functions->writeLog('B2C', 'requests', __LINE__, 'JSON from MPESA :- ' . $respo);
                    $toChannel = $this->processB2CResponse(json_decode($respo, true), $validated);
                } else {
                    $toChannel = json_encode($authentication);
                }
            }
            $this->functions->writeLog('B2C', 'requests', __LINE__, 'Response to channel :- ' . $toChannel);
            return $toChannel;
        }
    }

    public function processB2CResponse($response, $request)
    {
        if (array_key_exists('ResponseCode', $response) && (string) $response['ResponseCode'] === '0') {
            // Success
            $result = array(
                'STATUS' => '00',
                'RESPONSECODE' => $response['ResponseCode'],
                'STATUSDESCRIPTION' => $response['ResponseDescription']
            );
        } else if (array_key_exists('errorCode', $response) === true) {
            // Failed
            $result = array(
                'STATUS' => '99',
                'RESPONSECODE' => $response['errorCode'],
                'STATUSDESCRIPTION' => $response['errorMessage']
            );
        } else if (array_key_exists('STATUS', $response) === true) {
            $result = array(
                'RESPONSECODE' => '57'
            );
        } else {
            $result = array(
                'STATUS' => '55',
                'RESPONSECODE' => '55',
                'STATUSDESCRIPTION' => 'Unknow transaction state'
            );
        }
        $result['ORIGINAL_RESPONSE'] = $response;
        return json_encode(array_merge($request, $result));
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
        } else if (! array_key_exists(MpesaB2CRequests::ACCOUNT, $jsondata) || empty($jsondata[$this::ACCOUNT])) {
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
        } else if (strncmp($jsondata[$this::ACCOUNT], '254', 3) !== 0) {
            $response = array(
                'STATUS' => '55',
                'STATUSDESCRIPTION' => '(ACCOUNT) in request has invalid format.start with 254',
                'ORIGINAL_REQUEST' => $jsondata
            );
        } else if (! array_key_exists('SHORTCODE', $jsondata) || empty($jsondata['SHORTCODE'])) {
            $response = array(
                'STATUS' => '55',
                'STATUSDESCRIPTION' => '(SHORTCODE) in request cannot be empty.',
                'ORIGINAL_REQUEST' => $jsondata
            );
        } else if (! array_key_exists('COMMANDID', $jsondata) || empty($jsondata['COMMANDID'])) {
            $response = array(
                'STATUS' => '55',
                'STATUSDESCRIPTION' => '(COMMANDID) in request cannot be empty.',
                'ORIGINAL_REQUEST' => $jsondata
            );
        } else if (! array_key_exists('INITIATORNAME', $jsondata) || empty($jsondata['INITIATORNAME'])) {
            $response = array(
                'STATUS' => '55',
                'STATUSDESCRIPTION' => '(INITIATORNAME) in request cannot be empty.',
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
        } else if (! array_key_exists('SECURITYCREDENTIAL', $jsondata) || empty($jsondata['SECURITYCREDENTIAL'])) {
            $response = array(
                'STATUS' => '55',
                'STATUSDESCRIPTION' => '(SECURITYCREDENTIAL) in request cannot be empty.',
                'ORIGINAL_REQUEST' => $jsondata
            );
        } else {
            $response = array();
            $response = $jsondata;
            $response['STATUS'] = '00';
        }
        return $response;
    }

    private function prepareSafcomB2CJson($data)
    {
        $safjson = array(
            'InitiatorName' => isset($data['INITIATORNAME']) === true ? $data['INITIATORNAME'] : '',

            'SecurityCredential' => $this->functions->publicB2Cencrypt(isset($data['SECURITYCREDENTIAL']) === true ? $data['SECURITYCREDENTIAL'] : ''),

            'CommandID' => isset($data['COMMANDID']) === true ? $data['COMMANDID'] : '',
            'Amount' => isset($data['AMOUNT']) === true ? $data['AMOUNT'] : '',
            'PartyA' => isset($data['SHORTCODE']) === true ? $data['SHORTCODE'] : '',
            'PartyB' => isset($data[$this::ACCOUNT]) === true ? $data[$this::ACCOUNT] : '',
            'Remarks' => isset($data['NARRATION']) === true ? $data['NARRATION'] : 'B2C transfer to : ' . $data['ACCOUNT'],
            'QueueTimeOutURL' => $this->QUEUETIMEOUTURL,
            'ResultURL' => $this->RESULTURL,
            'Occassion' => $this->OCCATION

        );

        return json_encode($safjson);
    }

    public function loadConfig()
    {
        $this->MPESAB2CSERVICEURL = $this->configs['B2C']['MPESAB2CSERVICEURL'];
        $this->RESULTURL = $this->configs['B2C']['RESULTURL'];
        $this->QUEUETIMEOUTURL = $this->configs['B2C']['QUEUETIMEOUTURL'];
    }
}

header('Content-type: application/json; charset=utf-8');

$b2c = new MpesaB2CRequests();
echo $b2c->init();
<?php
include_once '..' . DIRECTORY_SEPARATOR . 'config.php';
include_once '..' . DIRECTORY_SEPARATOR . 'Functions.php';

class MpesaQueryRequests
{

    private $configs;

    private $functions;

    private $INITIATOR;

    private $SECURITYCREDENTIALS;

    private $COMMANDID;

    private $SHORTCODE;

    private $RESULTURL;

    private $QUEUETIMEOUTURL;

    private $MPESAQUERYSERVICEURL;

    public function __construct()
    {
        $this->configs = Configs::$configs;
        $this->loadConfig();
        $this->functions = new Functions(__CLASS__);
    }

    public function init()
    {
        $dataStream = file_get_contents('php://input');
        $this->functions->writeLog('QUERY', 'requests', __LINE__, 'Request From channel :- ' . $dataStream);
        if ($dataStream === null) {
            $error = array(
                'STATUS' => 99,
                'STATUSDESCRIPTION' => 'Request cannot be empty.'
            );
            $this->functions->writeLog('QUERY', 'error', __LINE__, 'Empty request');
            return json_encode($error);
        } else {
            $toChannel = '';
            $validated = $this->validateRequest($dataStream);

            if ($validated['STATUS'] !== '00') {
                $toChannel = json_encode(array_merge($validated, json_decode($dataStream, true)));
            } else {
                $authentication = $this->functions->getAuthToken();
                if ($authentication['STATUS'] === '00') {
                    $queryheader = array(
                        'Content-type: application/json',
                        'Authorization: Bearer ' . $authentication['STATUSDESCRIPTION']
                    );
                    $toMpesa = $this->prepareQueryJsn($validated);
                    $this->functions->writeLog('QUERY', 'requests', __LINE__, 'JSON to MPESA :- ' . $toMpesa);
                    $respo = $this->functions->curlPostToURL($this->MPESAQUERYSERVICEURL, $toMpesa, $queryheader);
                    $this->functions->writeLog('QUERY', 'requests', __LINE__, 'JSON from MPESA :- ' . $respo);
                } else {
                    $toChannel = json_encode($authentication);
                }
            }
            $this->functions->writeLog('QUERY', 'requests', __LINE__, 'Response to channel :- ' . $toChannel);
            return $toChannel;
        }
    }

    private function prepareQueryJsn($data)
    {
        $safJson = array(
            'Initiator' => $this->INITIATOR,
            'SecurityCredential' => $this->SECURITYCREDENTIALS,
            'CommandID' => $this->COMMANDID,
            'TransactionID' => isset($data['TRANSACTIONID']) === true ? $data['TRANSACTIONID'] : '',
            'PartyA' => $this->SHORTCODE,
            'IdentifierType' => '4', // 1 – MSISDN, 2 – Till Number, 4 – Organization short code
            'ResultURL' => $this->RESULTURL,
            'QueueTimeOutURL' => $this->QUEUETIMEOUTURL,
            'Remarks' => 'Checking status for Transaction ' . (isset($data['ORIGINALCONVERSATIONID']) === true ? $data['ORIGINALCONVERSATIONID'] : ''),
            'Occasion' => ' ',
            'OriginatorConversationID' => isset($data['ORIGINALCONVERSATIONID']) === true ? $data['ORIGINALCONVERSATIONID'] : ''
        );

        return json_encode($safJson);
    }

    private function validateRequest($data)
    {
        $jsondata = json_decode($data, true);
        if ($jsondata === null) {
            $response = array(
                'STATUS' => '99',
                'STATUSDESCRIPTION' => 'INVALID JSON REQUEST '
            );
        } else if ((! array_key_exists('ORIGINALCONVERSATIONID', $jsondata) || empty($jsondata['ORIGINALCONVERSATIONID'])) && (! array_key_exists('TRANSACTIONID', $jsondata) || empty($jsondata['TRANSACTIONID']))) {
            $response = array(
                'STATUS' => '55',
                'STATUSDESCRIPTION' => '(ORIGINALCONVERSATIONID) and (ORIGINALCONVERSATIONID) in request cannot be empty.',
                'ORIGINAL_REQUEST' => $jsondata
            );
        } else {
            $response = array();
            $response = $jsondata;
            $response['STATUS'] = '00';
        }
        return $response;
    }

    public function loadConfig()
    {
        $this->MPESAQUERYSERVICEURL = $this->configs['QUERY']['MPESAB2CSERVICEURL'];

        $this->INITIATOR = $this->configs['GENERAL']['INITIATORNAME'];

        // $encycreds = $this->functions->public_encrypt($this->configs['B2C']['SECURITYCREDENTIAL']);
        // $this->SECURITYCREDENTIAL = $encycreds;
        $this->SECURITYCREDENTIALS = $this->configs['GENERAL']['ENCSECURITYCREDENTIALS'];

        $this->COMMANDID = $this->configs['QUERY']['COMMANDID'];

        $this->SHORTCODE = $this->configs['QUERY']['SHORTCODE'];

        $this->RESULTURL = $this->configs['QUERY']['RESULTURL'];

        $this->QUEUETIMEOUTURL = $this->configs['QUERY']['QUEUETIMEOUTURL'];
    }
}

$query = new MpesaQueryRequests();
echo $query->init();


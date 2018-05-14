<?php
include_once '..' . DIRECTORY_SEPARATOR . 'config.php';
include_once '..' . DIRECTORY_SEPARATOR . 'Functions.php';

class PaybillBalanceEnq
{

    private $configs;

    private $functions;

    private $INITIATOR;

    private $SECURITYCREDENTIALS;

    private $COMMANDID;

    private $SHORTCODE;

    private $RESULTURL;

    private $QUEUETIMEOUTURL;

    private $PAYBILLBALSERVICEURL;

    public function __construct()
    {
        $this->configs = Configs::$configs;
        $this->loadConfig();
        $this->functions = new Functions(__CLASS__);
    }

    public function init()
    {
        $dataStream = file_get_contents('php://input');
        $this->functions->writeLog('BAL', 'requests', __LINE__, 'Request From channel :- ' . $dataStream);
        if ($dataStream == null) {
            $error = array(
                'STATUS' => '99',
                'STATUSDESCRIPTION' => 'Request cannot be empty.'
            );
            $this->functions->writeLog('BAL', 'error', __LINE__, 'Empty request');
            return json_encode($error);
        } else if ($dataStream === 'ShortcodeBalance') {
            $authentication = $this->functions->getAuthToken();
            if ($authentication['STATUS'] === '00') {
                $balheader = array(
                    'Content-type: application/json',
                    'Authorization: Bearer ' . $authentication['STATUSDESCRIPTION']
                );
                $toMpesa = $this->prepareSadcomBALJson();
                $this->functions->writeLog('BAL', 'requests', __LINE__, 'JSON to MPESA :- ' . $toMpesa);
                $respo = $this->functions->curlPostToURL($this->PAYBILLBALSERVICEURL, $toMpesa, $balheader);
                $this->functions->writeLog('BAL', 'requests', __LINE__, 'JSON from MPESA :- ' . $respo);
                $toChannel = $respo;
            } else {
                $toChannel = json_encode($authentication);
            }

            $this->functions->writeLog('BAL', 'requests', __LINE__, 'Response to channel :- ' . $toChannel);

            return $toChannel;
        } else {
            $error = array(
                'STATUS' => '99',
                'STATUSDESCRIPTION' => 'Unrecognized request.'
            );
            $this->functions->writeLog('BAL', 'error', __LINE__, 'Empty request');
            return json_encode($error);
        }
    }

    private function prepareSadcomBALJson()
    {
        $safjson = array(
            'Initiator' => $this->INITIATOR,
            'SecurityCredential' => $this->SECURITYCREDENTIALS,
            'CommandID' => $this->COMMANDID,
            'PartyA' => $this->SHORTCODE,
            'IdentifierType' => '4',
            'Remarks' => 'Checking utility balance',
            'QueueTimeOutURL' => $this->QUEUETIMEOUTURL,
            'ResultURL' => $this->RESULTURL
        );

        return json_encode($safjson);
    }

    private function loadConfig()
    {
        $this->PAYBILLBALSERVICEURL = $this->configs['BALANCE']['PAYBILLBALSERVICEURL'];
        $this->QUEUETIMEOUTURL = $this->configs['BALANCE']['QUEUETIMEOUTURL'];
        $this->RESULTURL = $this->configs['BALANCE']['RESULTURL'];
        $this->SHORTCODE = $this->configs['BALANCE']['SHORTCODE'];
        $this->COMMANDID = $this->configs['BALANCE']['COMMANDID'];
        $this->INITIATOR = $this->configs['GENERAL']['INITIATORNAME'];
        $this->SECURITYCREDENTIALS = $this->configs['GENERAL']['ENCSECURITYCREDENTIALS'];
    }
}

$bal = new PaybillBalanceEnq();
echo $bal->init();


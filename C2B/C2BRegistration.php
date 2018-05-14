<?php
include_once '..' . DIRECTORY_SEPARATOR . 'config.php';
include_once '..' . DIRECTORY_SEPARATOR . 'Functions.php';

class C2BRegistration
{

    private $configs;

    private $MPESAC2BREGISTRATIONURL;

    private $VALIDATIONURL;

    private $CONFIRMATIONURL;

    private $SHORTCODE;

    public function __construct()
    {
        $this->configs = Configs::$configs;
        $this->loadConfig();
        $this->functions = new Functions(__CLASS__);
    }

    public function init()
    {
        $dataStream = file_get_contents('php://input');
        $this->functions->writeLog('C2B', 'registration', __LINE__, 'Request From channel :- ' . $dataStream);
        if ($dataStream === null) {
            $error = array(
                'STATUS' => '99',
                'STATUSDESCRIPTION' => 'Request cannot be empty.'
            );
            $this->functions->writeLog('C2B', 'error', __LINE__, 'Empty request');
            return json_encode($error);
        } else if (json_decode($dataStream) === null) {
            $error = array(
                'STATUS' => '99',
                'STATUSDESCRIPTION' => 'Non JSON Request'
            );
            $this->functions->writeLog('C2B', 'error', __LINE__, 'Invalid request');
            return json_encode($error);
        } else {
            $jsondata = json_decode($dataStream);
            $authentication = $this->functions->getAuthToken($jsondata);
            if ($authentication['STATUS'] === '00') {
                $b2cheader = array(
                    'Content-type: application/json',
                    'Authorization: Bearer ' . $authentication['STATUSDESCRIPTION']
                );
                $toMpesa = $this->prepareSafcomRegJson($jsondata);
                $this->functions->writeLog('C2B', 'registration', __LINE__, 'JSON to MPESA :- ' . $toMpesa);
                $respo = $this->functions->curlPostToURL($this->MPESAC2BREGISTRATIONURL, $toMpesa, $b2cheader);
                $this->functions->writeLog('C2B', 'registration', __LINE__, 'JSON from MPESA :- ' . $respo);
                $toChannel = json_encode($respo);
            } else {
                $toChannel = json_encode($authentication);
            }
        }

        return $toChannel;
    }

    private function prepareSafcomRegJson($jsondata)
    {
        $safJson = array(
            'ShortCode' => $jsondata['SHORTCODE'],
            'ResponseType' => 'Completed',
            'ConfirmationURL' => $this->CONFIRMATIONURL,
            'ValidationURL' => $this->VALIDATIONURL
        );

        return json_encode($safJson);
    }

    private function loadConfig()
    {
        $this->VALIDATIONURL = $this->configs['C2B']['VALIDATIONURL'];
        $this->CONFIRMATIONURL = $this->configs['C2B']['CONFIRMATIONURL'];
        $this->SHORTCODE = $this->configs['C2B']['SHORTCODE'];
        $this->MPESAC2BREGISTRATIONURL = $this->configs['C2B']['MPESAC2BREGISTRATIONURL'];
    }
}

$c2breg = new C2BRegistration();
echo $c2breg->init();
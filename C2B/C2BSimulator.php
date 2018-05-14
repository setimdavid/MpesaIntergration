<?php
include_once '..' . DIRECTORY_SEPARATOR . 'config.php';
include_once '..' . DIRECTORY_SEPARATOR . 'Functions.php';

class C2BSimulation
{

    private $configs;

    private $MPESAC2BSIMULATIONURL;

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
        $this->functions->writeLog('C2B', 'simulation', __LINE__, 'Request From channel :- ' . $dataStream);
        if ($dataStream === null) {
            $error = array(
                'STATUS' => '99',
                'STATUSDESCRIPTION' => 'Request cannot be empty.'
            );
            $this->functions->writeLog('C2B', 'error', __LINE__, 'Empty request');
            return json_encode($error);
        } else if ($dataStream === 'C2BSimulation') {

            $authentication = $this->functions->getAuthToken();
            if ($authentication['STATUS'] === '00') {
                $b2cheader = array(
                    'Content-type: application/json',
                    'Authorization: Bearer ' . $authentication['STATUSDESCRIPTION']
                );
                $toMpesa = $this->prepareSafcomSimJson();
                var_dump($toMpesa);
                $this->functions->writeLog('C2B', 'simulation', __LINE__, 'JSON to MPESA :- ' . $toMpesa);
                $respo = $this->functions->curlPostToURL($this->MPESAC2BSIMULATIONURL, $toMpesa, $b2cheader);
                $this->functions->writeLog('C2B', 'simulation', __LINE__, 'JSON from MPESA :- ' . $respo);
                $toChannel = json_encode($respo);
            } else {
                $toChannel = json_encode($authentication);
            }
        } else {
            $toChannel = 'Invalid request';
        }

        return $toChannel;
    }

    private function prepareSafcomSimJson()
    {
        $safJson = array(
            'ShortCode' => $this->SHORTCODE,
            'CommandID' => 'CustomerPayBillOnline',
            'Amount' => '100',
            // 'Msisdn' => '254728011268',
            'Msisdn' => '254708374149',
            'BillRefNumber' => 'XYZ'
        );

        return json_encode($safJson);
    }

    private function loadConfig()
    {
        $this->SHORTCODE = $this->configs['C2B']['SHORTCODE'];
        $this->MPESAC2BSIMULATIONURL = $this->configs['C2B']['MPESAC2BSIMULATIONURL'];
    }
}

$c2bsim = new C2BSimulation();
echo $c2bsim->init();


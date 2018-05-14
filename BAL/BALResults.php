<?php
include_once '..' . DIRECTORY_SEPARATOR . 'config.php';
include_once '..' . DIRECTORY_SEPARATOR . 'Functions.php';

class PaybillBalResults
{

    private $configs;

    private $functions;

    private $ESBBALRESULTURL;

    public function __construct()
    {
        $this->configs = Configs::$configs;
        $this->loadConfig();

        $this->functions = new Functions(__CLASS__);
    }

    public function init()
    {
        $dataStream = file_get_contents('php://input');
        $this->functions->writeLog('BAL', 'results', __LINE__, 'From MPESA :- ' . $dataStream);
        $b2cheader = array(
            'Content-type: application/json'
        );
        $respo = $this->functions->curlPostToURL($this->ESBBALRESULTURL, $dataStream, $b2cheader);

        $esbrespo = json_decode($respo);
        $this->functions->writeLog('BAL', 'results', __LINE__, 'From ESB :- ' . $respo);

        if ($esbrespo === null) {
            $back = array(
                'ResponseCode' => '99999990',
                'ResponseDesc' => 'Failed to send'
            );
            return json_encode($back);
        } else {
            return $respo;
        }
    }

    private function loadConfig()
    {
        $this->ESBBALRESULTURL = $this->configs['BALANCE']['ESBBALRESULTURL'];
    }
}

$bal = new PaybillBalResults();
echo $bal->init();
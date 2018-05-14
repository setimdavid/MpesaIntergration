<?php
include_once '..' . DIRECTORY_SEPARATOR . 'config.php';
include_once '..' . DIRECTORY_SEPARATOR . 'Functions.php';

class C2BConfirmation
{

    private $ESBC2BCONFIRMATION;

    private $configs;

    public function __construct()
    {
        $this->configs = Configs::$configs;
        $this->loadConfig();
        $this->functions = new Functions(__CLASS__);
    }

    public function init()
    {
        $dataStream = file_get_contents('php://input');
        $this->functions->writeLog('C2B', 'confirmation', __LINE__, 'From MPESA :- ' . $dataStream);

        $c2bheader = array(
            'Content-type: application/json'
        );

        $respo = $this->functions->curlPostToURL($this->ESBC2BCONFIRMATION, $dataStream, $c2bheader);

        $esbrespo = json_decode($respo);
        $this->functions->writeLog('C2B', 'confirmation', __LINE__, 'From ESB :- ' . $respo);

        if ($esbrespo == null) {
            $back = array(
                'ResponseCode' => '99999990',
                'ResponseDesc' => 'Failed to send'
            );
            return json_encode($back);
        } else {
            return $respo;
        }

        // $back = array(
        // 'ResultCode' => 99,
        // 'ResultDesc' => 'Success',
        // 'ThirdPartyTransID' => ''
        // );

        // $this->functions->writeLog('C2B', 'confirmation', __LINE__, 'From MPESA :- ' . json_encode($back));

        // return json_encode($back);
    }

    private function loadConfig()
    {
        $this->ESBC2BCONFIRMATION = $this->configs['C2B']['ESBC2BCONFIRMATION'];
    }
}

header('Content-type: application/json; charset=utf-8');

$c2breq = new C2BConfirmation();
echo $c2breq->init();



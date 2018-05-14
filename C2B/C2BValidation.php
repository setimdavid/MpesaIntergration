<?php
include_once '..' . DIRECTORY_SEPARATOR . 'config.php';
include_once '..' . DIRECTORY_SEPARATOR . 'Functions.php';

class C2BValidation
{

    private $configs;

    private $ESBC2BVALIDATIONURL;

    public function __construct()
    {
        $this->configs = Configs::$configs;
        $this->loadConfig();

        $this->functions = new Functions(__CLASS__);
    }

    public function init()
    {
        $dataStream = file_get_contents('php://input');
        $this->functions->writeLog('C2B', 'validation', __LINE__, 'From MPESA :- ' . $dataStream);

        $b2cheader = array(
            'Content-type: application/json'
        );
        $respo = $this->functions->curlPostToURL($this->ESBC2BVALIDATIONURL, $dataStream, $b2cheader);

        $this->functions->writeLog('C2B', 'validation', __LINE__, 'From ESB :- ' . $respo);

        if ($respo === null) {
            $back = array(
                'ResultCode' => 0,
                'ResultDesc' => 'Failed',
                'ThirdPartyTransID' => ''
            );
            return json_encode($back);
        } else {
            return $respo;
        }
    }

    public function loadConfig()
    {
        $this->ESBC2BVALIDATIONURL = $this->configs['C2B']['ESBC2BVALIDATIONURL'];
    }
}

header('Content-type: application/json; charset=utf-8');

$c2bval = new C2BValidation();
echo $c2bval->init();




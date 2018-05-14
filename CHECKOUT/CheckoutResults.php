<?php
include_once '..' . DIRECTORY_SEPARATOR . 'config.php';
include_once '..' . DIRECTORY_SEPARATOR . 'Functions.php';

class MPESACheckoutResults
{

    private $configs;

    private $functions;

    private $PGCHECKOUTSERVICEURL;

    public function __construct()
    {
        $this->configs = Configs::$configs;
        $this->loadConfig();

        $this->functions = new Functions(__CLASS__);
    }

    public function init()
    {
        $dataStream = file_get_contents('php://input');
        $this->functions->writeLog('CHECKOUT', 'results', __LINE__, 'From MPESA :- ' . $dataStream);
        $b2cheader = array(
            'Content-type: application/json'
        );
        $respo = $this->functions->curlPostToURL($this->PGCHECKOUTSERVICEURL, $dataStream, $b2cheader);

        $esbrespo = json_decode($respo);
        $this->functions->writeLog('CHECKOUT', 'results', __LINE__, 'From PG :- ' . $respo);

        if ($esbrespo === null) {
            $back = array(
                'ResponseCode' => '0',
                'ResponseDesc' => 'Success'
            );
            return json_encode($back);
        } else {
            return $respo;
        }
    }

    public function loadConfig()
    {
        $this->PGCHECKOUTSERVICEURL = $this->configs['CHECKOUT']['PGCHECKOUTSERVICEURL'];
    }
}

$checkrst = new MPESACheckoutResults();
echo $checkrst->init();
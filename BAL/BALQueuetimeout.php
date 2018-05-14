<?php
include_once '..' . DIRECTORY_SEPARATOR . 'config.php';
include_once '..' . DIRECTORY_SEPARATOR . 'Functions.php';

class MPESABALTimeouts
{

    private $functions;

    public function __construct()
    {
        $this->functions = new Functions(__CLASS__);
    }

    public function init()
    {
        $dataStream = file_get_contents('php://input');
        $this->functions->writeLog('BAL', 'timeouts', __LINE__, $dataStream);

        $back = array(
            'ResponseCode' => '0',
            'ResponseDesc' => 'Success'
        );

        return json_encode($back);
    }
}

$baltimeout = new MPESABALTimeouts();
echo $baltimeout->init();
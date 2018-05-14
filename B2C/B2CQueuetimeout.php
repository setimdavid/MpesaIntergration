<?php
include_once '..' . DIRECTORY_SEPARATOR . 'config.php';
include_once '..' . DIRECTORY_SEPARATOR . 'Functions.php';

class MPESAB2CTimeouts
{

    private $functions;

    public function __construct()
    {
        $this->functions = new Functions(__CLASS__);
    }

    public function init()
    {
        $dataStream = file_get_contents('php://input');
        $this->functions->writeLog('B2C', 'timeouts', __LINE__, $dataStream);

        $back = array(
            'ResponseCode' => '0',
            'ResponseDesc' => 'Success'
        );

        return json_encode($back);
    }
}

$b2cqueue = new MPESAB2CTimeouts();
echo $b2cqueue->init();
<?php
include_once '..' . DIRECTORY_SEPARATOR . 'config.php';
include_once '..' . DIRECTORY_SEPARATOR . 'Functions.php';

class MPESAQueryResults
{

    private $configs;

    private $functions;

    private $PGB2CRESULTURL;

    public function __construct()
    {
        $this->configs = Configs::$configs;
        $this->loadConfig();

        $this->functions = new Functions(__CLASS__);
    }

    public function init()
    {
        $dataStream = file_get_contents('php://input');
        $this->functions->writeLog('B2C', 'results', __LINE__, 'From MPESA :- ' . $dataStream);

        if ($dataStream === null) {
            $back = array(
                'ResponseCode' => '99999990',
                'ResponseDesc' => 'Failed to send'
            );
            return json_encode($back);
        } else {
            $back = array(
                'ResponseCode' => '99999990',
                'ResponseDesc' => 'Failed to send'
            );
            return json_encode($back);
        }
    }

    public function loadConfig()
    {
        $this->PGB2CRESULTURL = $this->configs['B2C']['PGB2CRESULTURL'];
    }
}

$b2c = new MPESAQueryResults();
echo $b2c->init();
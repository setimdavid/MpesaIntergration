<?php
include_once '..' . DIRECTORY_SEPARATOR . 'config.php';
include_once '..' . DIRECTORY_SEPARATOR . 'Functions.php';

class MPESAStatusResults
{

    private $configs;

    private $functions;

    public function __construct()
    {
        $this->configs = Configs::$configs;
        $this->loadConfig();

        $this->functions = new Functions(__CLASS__);
    }

    public function init()
    {
        $dataStream = file_get_contents('php://input');
        $this->functions->writeLog('STATUS', 'results', __LINE__, 'From MPESA :- ' . $dataStream);
        // $b2cheader = array(
        // 'Content-type: application/json'
        // );

        // Do something with the response
        $esbrespo = $dataStream;

        $this->functions->writeLog('STATUS', 'results', __LINE__, 'From ESB :- ' . $esbrespo);

        if ($esbrespo === null) {
            $back = array(
                'ResponseCode' => '99999990',
                'ResponseDesc' => 'Failed to send'
            );
            return json_encode($back);
        } else {
            $back = array(
                'ResponseCode' => '0',
                'ResponseDesc' => 'Success'
            );
            return json_encode($back);
        }
    }

    public function loadConfig()
    {
        $this->ESBB2CRESULTURL = $this->configs['STATUS']['ESBB2CRESULTURL'];
    }
}

$query = new MPESAQueryResults();
echo $query->init();
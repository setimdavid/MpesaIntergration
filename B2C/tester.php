<?php

class Test
{

    // $myfile = fopen('CERT/cert.cer', "r") or die("Unable to open file!");
    // // echo filesize("CERT//cert.cer");
    // echo fread($myfile, 8192);
    // fclose($myfile);

    // $fp = fopen('CERT/cert.cer', 'r');
    // $pub_key = fread($fp, 8192);
    // fclose($fp);
    // echo $pub_key;
    public function init()
    {
        $type = 'INFO';
        $string = 'Test a sasas content ';
        $date = date('yyyy-mm-dd');
        $logData = "$date - [ $type ] " . $_SERVER['PHP_SELF'] . " | $string\n";

        // $fileOpen = SplFileObject('trial-logs.php', 'w');
        // $fileOpen->fwrite($logData);

        // $file = new SplFileObject('trial-logs.txt', 'w');
        // $file->fwrite($logData);

        // if ($fo = fopen('trial-logs.txt', 'a')) {
        // // $fileOpen = SplFileObject('trial-logs.php', 'w');
        // // $fileOpen->fwrite($logData);

        // $file = new SplFileObject($fo);
        // $file->fwrite($logData);
        // fclose($file);
        // }

        $file = fopen('trial-logs.txt', 'a');
        fwrite($file, date('Y-m-d h:i:s') . ' ' . $string . $content . PHP_EOL);
        fclose($file);
    }
}

$ff = new Test();
echo $ff->init();
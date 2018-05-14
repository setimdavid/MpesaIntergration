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
        // $type = '';
        // $string = 'Test content sdsds';
        // $date = date('yyyy-mm-dd');
        // $logData = "$date - [ $type ] " . $_SERVER['PHP_SELF'] . " | $string\n";

        // $fileOpen = SplFileObject('trial-logs.php', 'w');
        // $fileOpen->fwrite($logData);

        // $file = new SplFileObject('trial-logs.txt', 'w');
        // $file->fwrite($logData);

        // if ($fo = fopen('triallogs.txt', 'a')) {
        // $fileOpen = SplFileObject('trial-logs.php', 'w');
        // $fileOpen->fwrite($logData);

        // if ($file = new SplFileObject('triallogs.txt', 'a')) {
        // $file->fwrite($logData);
        // }

        // if (empty($type)) {
        // echo 'empty';
        // } else {
        // echo 'Not empty';
        // }

        // $plaintext = '314reset';
        // $publicKey = 'C:\\xampp\\htdocs\\MPESA_JSON\\CERT\\cert.cer';

        // $fp = fopen($publicKey, 'r');
        // $pub_key = fread($fp, 8192);
        // fclose($fp);
        // openssl_get_publickey($pub_key);
        // // openssl_public_encrypt($plaintext, $crypttext, $pub_key, OPENSSL_PKCS1_PADDING);
        // openssl_public_encrypt($plaintext, $crypttext, $pub_key);
        // // var_dump(base64_encode($crypttext));
        // return base64_encode($crypttext);

        // $fp = fopen($publicKey, 'r');
        // $pub_key = fread($fp, 8192);

        // echo $pub_key;
        openssl_public_encrypt($plaintext, $encrypted, $pub_key, OPENSSL_PKCS1_PADDING);

        echo base64_encode($encrypted);
    }
}

$ff = new Test();
echo $ff->init();
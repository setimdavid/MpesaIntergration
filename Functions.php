<?php
date_default_timezone_set('Africa/Nairobi');
include_once 'config.php';

class Functions
{

    private $configs;

    private $callFileName;

    public function __construct($fileName)
    {
        $this->configs = Configs::$configs;
        $this->callFileName = $fileName;
    }

    public function getAuthToken($data)
    {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $this->configs['AUTH']['AUTHURL']);
        $credentials = base64_encode($data['CONSUMERKEY'] . ':' . $data['CONSUMERSECRET']);
        curl_setopt($curl, CURLOPT_HTTPHEADER, array(
            'Authorization: Basic ' . $credentials
        )); // setting a custom header
        curl_setopt($curl, CURLOPT_HEADER, false);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);

        $curl_response = curl_exec($curl);
        $httpcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        if ($curl_response === null) {
            curl_close($curl);
            $response = array(
                'STATUS' => '57',
                'STATUSDESCRIPTION' => 'ERROR! ' . 'Curl error: ' . curl_error($channel)
            );
        } else if ((string) $httpcode === '200') {
            curl_close($curl);
            $respo = json_decode(trim($curl_response));
            $response = array(
                'STATUS' => '00',
                'STATUSDESCRIPTION' => $respo->access_token
            );
        } else {
            curl_close($curl);
            $response = array(
                'STATUS' => '99',
                'STATUSDESCRIPTION' => 'ERROR# ' . $httpcode
            );
        }

        return $response;
    }

    public function curlPostToURL($serviceURL, $payloadRequest, $header)
    {
        $channel = curl_init();
        curl_setopt($channel, CURLOPT_URL, $serviceURL);
        curl_setopt($channel, CURLOPT_CONNECTTIMEOUT, $this->configs['SYSTEM']['CONNECTIONTIMEOUT']);
        curl_setopt($channel, CURLOPT_TIMEOUT, $this->configs['SYSTEM']['READTIMEOUT']);
        curl_setopt($channel, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($channel, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($channel, CURLOPT_HTTPHEADER, $header);
        curl_setopt($channel, CURLOPT_POSTFIELDS, $payloadRequest);
        curl_setopt($channel, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($channel, CURLOPT_VERBOSE, true);

        $response = curl_exec($channel);

        if ($response === null) {
            curl_close($channel);
            $err = array(
                'STATUS' => '57',
                'STATUSDESCRIPTION' => 'ERROR! ' . 'Curl error: ' . curl_error($channel)
            );

            return json_encode($err);
        } else {
            curl_close($channel);
            return $response;
        }
    }

    public function publicB2Cencrypt($plaintext)

    {
        $fileLength = 8192;
        $fp = fopen($this->configs['GENERAL']['PUBLICCERT'], 'r');
        $pub_key = fread($fp, $fileLength);
        openssl_public_encrypt($plaintext, $encrypted, $pub_key, OPENSSL_PKCS1_PADDING);

        return base64_encode($encrypted);
    }

    public function processResponse($response, $request)
    {
        if (array_key_exists('ResponseCode', $response) && (string) $response['ResponseCode'] === '0') {
            if (array_key_exists('ResultCode', $response) && (string) $response['ResultCode'] !== '0') {
                // Final Failure
                $result = array(
                    'STATUS' => '99',
                    'RESPONSECODE' => $response['ResultCode'],
                    'STATUSDESCRIPTION' => $response['ResultDesc']
                );
            } else {
                // Mega Success
                $result = array(
                    'STATUS' => '00',
                    'RESPONSECODE' => $response['ResponseCode'],
                    'STATUSDESCRIPTION' => $response['ResponseDescription']
                );
            }
        } else if (array_key_exists('errorCode', $response) === true) {
            // Failed
            $result = array(
                'STATUS' => '99',
                'RESPONSECODE' => $response['errorCode'],
                'STATUSDESCRIPTION' => $response['errorMessage']
            );
        } else if (array_key_exists('STATUS', $response) === true) {
            $result = array(
                'RESPONSECODE' => '57'
            );
        } else {
            $result = array(
                'STATUS' => '55',
                'RESPONSECODE' => '55',
                'STATUSDESCRIPTION' => 'Unknow transaction state'
            );
        }

        $result['ORIGINAL_RESPONSE'] = $response;
        return array_merge($request, $result);
    }

    public function removeSensitiveData($data)
    {
        if (array_key_exists('PASSKEY', $data) === true) {
            unset($data['PASSKEY']);
        }

        if (array_key_exists('CONSUMERKEY', $data) === true) {
            unset($data['CONSUMERKEY']);
        }

        if (array_key_exists('CONSUMERSECRET', $data) === true) {
            unset($data['CONSUMERSECRET']);
        }

        if (array_key_exists('SECURITYCREDENTIAL', $data) === true) {
            unset($data['SECURITYCREDENTIAL']);
        }
        // if (array_key_exists('ORIGINAL_REQUEST', $data)) {
        // $sdata = $this->removeSensitiveData(json_decode($data['ORIGINAL_REQUEST']));
        // $data = [
        // 'ORIGINAL_REQUEST' => [
        // 'Kenya' => 'ytrre'
        // ]
        // ];
        // }

        return $data;
    }

    public function writeLog($path, $fileName, $line, $content)
    {
        $filePath = $this->configs['SYSTEM']['LOGS'] . DIRECTORY_SEPARATOR . $path . DIRECTORY_SEPARATOR . date('Y-m-d');
        if (! file_exists($filePath) === true) {
            mkdir($filePath, '777', true);
            // mkdir($filePath);
        }
        $logFile = $filePath . DIRECTORY_SEPARATOR . $fileName . '.log';
        $file = fopen($logFile, 'a');
        fwrite($file, stripcslashes(date('Y-m-d h:i:s') . ' ' . $this->callFileName . ' ' . $_SERVER['PHP_SELF'] . ' line - ' . $line . ' ::: ' . $content . PHP_EOL));

        fclose($file);
    }
}
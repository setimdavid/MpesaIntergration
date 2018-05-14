<?php

/**
 *
 *
 * Y2hvaWNlOmNob2ljZQ==
 *
 *
 *
 *
 * @author ERIC
 *
 */
class Configs
{

    public static $configs = array(
        'B2C' => array(
            'MPESAB2CSERVICEURL' => 'https://sandbox.safaricom.co.ke/mpesa/b2c/v1/paymentrequest',
            'QUEUETIMEOUTURL' => 'http://5322215e.ngrok.io/MPESA_JSON/B2C/B2CQueuetimeout.php',
            'RESULTURL' => 'https://testgateway.ekenya.co.ke:8443/MPESA_JSON/B2C/B2CResults.php',
            'PGB2CRESULTURL' => 'http://10.20.2.28:8080/MPESAJSONService/MPESA/B2CResultsAPI'
        ),

        'B2C_OLD' => array(
            'MPESAB2CSERVICEURL' => 'https://sandbox.safaricom.co.ke/mpesa/b2c/v1/paymentrequest',
            'QUEUETIMEOUTURL' => 'http://5322215e.ngrok.io/MPESA_JSON/B2C/B2CQueuetimeout.php',
            'RESULTURL' => 'https://testgateway.ekenya.co.ke:8443/MPESA_JSON/B2C/B2CResults.php',
            'PGB2CRESULTURL' => 'http://10.20.2.28:8080/MPESAJSONService/MPESA/B2CResultsAPI'
        ),

        'C2B' => array(
            'MPESAC2BREGISTRATIONURL' => 'https://sandbox.safaricom.co.ke/mpesa/c2b/v1/registerurl',
            'MPESAC2BSIMULATIONURL' => 'https://sandbox.safaricom.co.ke/mpesa/c2b/v1/simulate',
            'SHORTCODE' => '600000',
            // 'VALIDATIONURL' => 'https://onlinebanking.choicemfb.com/MPESA/C2B/C2BValidation.php',
            // 'CONFIRMATIONURL' => 'https://onlinebanking.choicemfb.com/MPESA/C2B/C2BConfirmation.php',
            'VALIDATIONURL' => 'http://c1899ff3.ngrok.io/MPESA/C2B/C2BValidation.php',
            'CONFIRMATIONURL' => 'http://c1899ff3.ngrok.io/MPESA/C2B/C2BConfirmation.php',

            'ESBC2BVALIDATIONURL' => 'http://localhost:8080/CHOICE_EXTAdaptor/CHOICE/MPESAC2BValidation',
            'ESBC2BCONFIRMATION' => 'http://localhost:8080/CHOICE_EXTAdaptor/CHOICE/MPESAC2BConfirmation'
        ),

        'STATUS' => array(
            'MPESAB2CSERVICEURL' => 'https://sandbox.safaricom.co.ke/mpesa/transactionstatus/v1/query',
            'COMMANDID' => 'TransactionStatusQuery',
            'SHORTCODE' => '600584',
            'RESULTURL' => 'http://1bbc0c37.ngrok.io/MPESA/STATUS/StatusResults.php',
            'QUEUETIMEOUTURL' => 'http://1bbc0c37.ngrok.io/MPESA_JSON/STATUS/StatusQueuetimeout.php'

        ),
        'CHECKOUT' => array(
            'CHECKOUTREQUESTURL' => 'https://sandbox.safaricom.co.ke/mpesa/stkpush/v1/processrequest',
            'CHECKOUTQUERYURL' => 'https://sandbox.safaricom.co.ke/mpesa/stkpushquery/v1/query',
            'RESULTURL' => 'https://testgateway.ekenya.co.ke:8443/MPESA_JSON/CHECKOUT/CheckoutResults.php',
            'PGCHECKOUTSERVICEURL' => 'http://10.20.2.28:8080/MPESAJSONService/MPESA/CheckoutResults'
        ),
        'BALANCE' => array(
            'PAYBILLBALSERVICEURL' => 'https://sandbox.safaricom.co.ke/mpesa/accountbalance/v1/query',
            'QUEUETIMEOUTURL' => 'http://05574c1d.ngrok.io/MPESA/BAL/BALQueuetimeout.php',
            'RESULTURL' => 'http://05574c1d.ngrok.io/MPESA/BAL/BALResults.php',
            'SHORTCODE' => '600407',
            'COMMANDID' => 'AccountBalance',
            'ESBBALRESULTURL' => 'http://localhost:8080/CHOICE_EXTAdaptor/CHOICE/MPESABAL/Results'

        ),

        'AUTH' => array(
            'AUTHURL' => 'https://sandbox.safaricom.co.ke/oauth/v1/generate?grant_type=client_credentials',
            'CONSUMERKEY' => 'BzswytGGh61dckuafGnrpAeaUAuMQ6ja',
            'CONSUMERSECRET' => '8KnF1OsfEjarT80y'
        ),
        'GENERAL' => array(
            'PUBLICCERT' => '/var/www/html-8443/MPESA_JSON/CERT/cert.cer',
            'INITIATORNAME' => 'testapi',
            'ENCSECURITYCREDENTIALS' => 'Vl6Wgiz3hmoEvO8/YSMs/+Kn84yqpcFsjlysmNhFFUs0pG60ym0uK/VMRXEPT05p9qp0b2OD1V/7KhGOr1kgYpjeTAEuP37JkVYpKlq/+mF4g8SNeqWD80z1j0BdolWeOEZVXs/ziLE51FEK2H5FSN6eRGAL5fHBFo7Q1Adkz4E4QF0GcUAEId20axerKI+mriJWg3q5RYVFd/9au7Vu1CGl1hCO/D9evvwgOCoyc/Eb9bNc+qCb/UBLzJgFBLV6O2N9gVRmskc27UhLTZedDAt7L6NkMWXyPChDSpr7TfPKeX70nIlyxcFOiZOpBAg0e5B0LLOqfLMnn2Ftc0Lo1w=='
        ),
        'SYSTEM' => array(
            'CONNECTIONTIMEOUT' => 30,
            'READTIMEOUT' => 30,
            'LOGS' => DIRECTORY_SEPARATOR . 'var' . DIRECTORY_SEPARATOR . 'log' . DIRECTORY_SEPARATOR . 'applications' . DIRECTORY_SEPARATOR . 'B2CResponse'
        ),
        'ERRORCODES' => array(
            '' => ''
        )

    );
}
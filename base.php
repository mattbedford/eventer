<?php

/**
 * Plugin Name: Zoho test
 * Plugin URI: https://classroomsecrets.co.uk
 * Description: Zoho SDK test
 * Version: 1.0
 * Author: Matt Bedford
 */


use com\zoho\api\authenticator\OAuthToken;

use com\zoho\api\authenticator\TokenType;

use com\zoho\api\authenticator\store\DBStore;

use com\zoho\api\authenticator\store\FileStore;

use com\zoho\crm\api\Initializer;

use com\zoho\crm\api\UserSignature;

use com\zoho\crm\api\SDKConfigBuilder;

use com\zoho\crm\api\dc\EUDataCenter;

use com\zoho\api\logger\Logger;

use com\zoho\api\logger\Levels;

require 'vendor/autoload.php';

class ZInitialize
{
    public static function initialize()
    {
        /*
            * Create an instance of Logger Class that takes two parameters
            * 1 -> Level of the log messages to be logged. Can be configured by typing Levels "::" and choose any level from the list displayed.
            * 2 -> Absolute file path, where messages need to be logged.
        */
        $logger = Logger::getInstance(Levels::INFO, "php_sdk_log.log");

        //Create an UserSignature instance that takes user Email as parameter
        $user = new UserSignature("matt.bedford@classroomsecrets.co.uk");

        /*
            * Configure the environment
            * which is of the pattern Domain.Environment
            * Available Domains: USDataCenter, EUDataCenter, INDataCenter, CNDataCenter, AUDataCenter
            * Available Environments: PRODUCTION(), DEVELOPER(), SANDBOX()
        */
        $environment = EUDataCenter::PRODUCTION();

        /*
            * Create a Token instance
            * 1 -> OAuth client id.
            * 2 -> OAuth client secret.
            * 3 -> REFRESH/GRANT token.
            * 4 -> Token type(REFRESH/GRANT).
            * 5 -> OAuth redirect URL.
        */
		
		$token = (new OAuthBuilder())
			->clientId("1000.MJRNBBWHZ42GKF031AL58POALZX42K")
			->clientSecret("d476a6e853d7354437f3822512fa495fdcd7aa243f")
			->refreshToken("1000.afb5cca2e71c53e0c944ae924420c654.b2459013d1b0a3fc412f9772c92b5273")
			->redirectURL("https://classroomsecrets.co.uk")
			->build();

        $tokenstore = new FileStore("php_sdk_token.txt");

        $autoRefreshFields = false;

        $pickListValidation = false;

        // Create an instance of SDKConfig
        $sdkConfig = (new SDKConfigBuilder())->setAutoRefreshFields($autoRefreshFields)->setPickListValidation($pickListValidation)->build();

        $resourcePath = "/configs";

        //(don't) Create an instance of RequestProxy
        $requestProxy = null;

        /*
          * Call static initialize method of Initializer class that takes the following arguments
          * 1 -> UserSignature instance
          * 2 -> Environment instance
          * 3 -> Token instance
          * 4 -> TokenStore instance
          * 5 -> SDKConfig instance
          * 6 -> resourcePath - A String
          * 7 -> Log instance (optional)
          * 8 -> RequestProxy instance (optional)
        */
        Initializer::initialize($user, $environment, $token, $tokenstore, $sdkConfig, $resourcePath, $logger, $requestProxy);
    }
}
?>
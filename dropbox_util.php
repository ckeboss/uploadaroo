<?php
namespace Uploadaroo;
//Dropbox stuff
//Not an OO based solution, going to turn this off
/* require_once 'dropbox-sdk/lib/Dropbox/strict.php'; */
require_once 'dropbox-sdk/lib/Dropbox/autoload.php';

use \Dropbox as dbx;

function getAppConfig() {
    try {
        $appInfo = dbx\AppInfo::loadFromJson(array('key' => Config::$dropbox_api_credentials['key'], 'secret' => Config::$dropbox_api_credentials['secret']));
    }
    catch (dbx\AppInfoLoadException $ex) {
        throw new Exception("Unable to load \"$appInfoFile\": " . $ex->getMessage());
    }

    $clientIdentifier = "examples-web-file-browser";
    $userLocale = null;

    return array($appInfo, $clientIdentifier, $userLocale);
}

function getClient() {
    if(!isset($_SESSION['access-token'])) {
        return false;
    }

    list($appInfo, $clientIdentifier, $userLocale) = getAppConfig();
    $accessToken = $_SESSION['access-token'];
    return new dbx\Client($accessToken, $clientIdentifier, $userLocale, $appInfo->getHost());
}

function getWebAuth() {
    list($appInfo, $clientIdentifier, $userLocale) = getAppConfig();
    $redirectUri = 'http://localhost/uploadaroo/dropbox_callback.php';
    $csrfTokenStore = new dbx\ArrayEntryStore($_SESSION, 'dropbox-auth-csrf-token');
    return new dbx\WebAuth($appInfo, $clientIdentifier, $redirectUri, $csrfTokenStore, $userLocale);
}
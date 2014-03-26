<?php
namespace Uploadaroo;

//Copy file as config.php and fill in creds.
class config {
	public static $dropbox_api_credentials = array('key' => '', 'secret' => '');
	public static $aws_config = array('key' => '', 'secret' => '', 'region' => 'us-east-1', 'bucket' => 'uploadaroo-bucket');
}
<?php
namespace Uploadaroo;

include('config.php');

include("dropbox_util.php");
use \Dropbox as dbx;

require 'aws-sdk/aws-autoloader.php';
use Aws\Common\Aws;
use Aws\S3\Exception\S3Exception;

session_start();

if(isset($_FILES['uploadedFile'])) {
	//http://www.php.net/manual/en/features.file-upload.errors.php
	switch ($_FILES['uploadedFile']['error']) {
		case UPLOAD_ERR_NO_FILE:
			$_SESSION['errors']['uploadedFile'] = 'Please select a file of 100KB or less';
			break;
		case UPLOAD_ERR_INI_SIZE:
		case UPLOAD_ERR_FORM_SIZE:
		case UPLOAD_ERR_PARTIAL:
		case UPLOAD_ERR_NO_TMP_DIR:
		case UPLOAD_ERR_CANT_WRITE:
		case UPLOAD_ERR_EXTENSION:
			$_SESSION['errors']['uploadedFile'] = 'Error uploading file, code '.$_FILES['uploadedFile']['error'];
			break;
	}
	//100KB in bytes
	if($_FILES['uploadedFile']['size'] > 102400) {
		//To big, tell the user, along with how large their file accually is
		$_SESSION['errors']['uploadedFile'] = 'Uploaded file is too large ('.formatBytes($_FILES['uploadedFile']['size']).').';
	}
} else {
	//File not set, shouldn't happen unless tampering, or server can't handle for some reason
	$_SESSION['errors']['uploadedFile'] = 'Error uploading file. Please contact the administrator or try again.';
}

if(!isset($_POST['placeToUpload']) || empty($_POST['placeToUpload'])) {
	$_SESSION['errors']['placeToUpload'] = 'You must select at least one option.';
}

foreach($_POST['placeToUpload'] as $place) {
	$_SESSION['values']['placeToUpload'][$place] = true;
}

//Validation on all of this.
if(isset($_SESSION['errors'])) {
	//There are errors, get out of here
	header("Location: index.php");
	exit;
}

//TODO should we combine the validation redirect to include all $_SESSION['errors']? Not sure.

$upload_name = uniqid().'-'.$_FILES['uploadedFile']['name'];

//Local
if(in_array('local', $_POST['placeToUpload'])) {
	//Upload to local
	if(!move_uploaded_file($_FILES['uploadedFile']['tmp_name'], 'uploads/'.$upload_name)) {
		//File could not be written there for some reason
		$_SESSION['errors']['general'] = 'Error moving file to uploads directory. Please contact the administrator or try again.';
		//TODO DRY
		header("Location: index.php");
		exit;
	} else {
		//Store it here for additional upload soruces
		$_FILES['uploadedFile']['tmp_name'] = __DIR__.'/uploads/'.$upload_name;
		$_SESSION['success']['local'] = 'Uploaded file to the local server successfully.';
		//success!
	}
}

//Dropbox
if(in_array('dropbox', $_POST['placeToUpload'])) {
	
	$dbxClient = getClient();

	$remoteDir = "/";
	if (isset($_POST['folder'])) $remoteDir = $_POST['folder'];

	$remotePath = rtrim($remoteDir, "/")."/".$upload_name;

	$fp = fopen($_FILES['uploadedFile']['tmp_name'], "rb");
	$result = $dbxClient->uploadFile($remotePath, dbx\WriteMode::add(), $fp);
	fclose($fp);
	if(!$result) {
		$_SESSION['errors']['general'] = 'Error uploading file to dropbox. Please contact the administrator or try again.';
		//TODO DRY
		header("Location: index.php");
	} else {
		$_SESSION['success']['dropbox'] = 'Uploaded file to dropbox successfully.';
		//success!
	}
}

//AWS
if(in_array('s3', $_POST['placeToUpload'])) {

	$aws = Aws::factory(
		array(
			'key'    => Config::$aws_config['key'],
			'secret' => Config::$aws_config['secret'],
			'region' => Config::$aws_config['region'],
		)
	);
	$s3 = $aws->get('s3');
	
	try {
	    $s3->upload(Config::$aws_config['bucket'], $upload_name, fopen($_FILES['uploadedFile']['tmp_name'], 'r'), 'public-read');
	} catch (S3Exception $e) {
		$_SESSION['errors']['general'] = 'Error uploading file to dropbox. Please contact the administrator or try again. Error: '.$e->getMessage();
	}
	
	$_SESSION['success']['s3'] = 'Uploaded file to S3 successfully.';
}

//Show success/errors of uploading
header("Location: index.php");

//http://stackoverflow.com/a/2510540/3191901
function formatBytes($size, $precision = 2) {
	$base = log($size) / log(1024);
	$suffixes = array('', 'k', 'M', 'G', 'T');   

	return round(pow(1024, $base - floor($base)), $precision) . $suffixes[floor($base)];
}
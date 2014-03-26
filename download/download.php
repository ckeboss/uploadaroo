<?php
namespace Uploadaroo;

session_start();

include("../config.php");
include("../dropbox_util.php");

$dbxClient = getClient();

require '../aws-sdk/aws-autoloader.php';
use Aws\Common\Aws;
use Aws\S3\Exception\S3Exception;

if(!isset($_GET['file'])) {
	could_not_find_file();
}

switch ($_GET['type']) {
	case 'local' :
		$file = '../uploads/'.urldecode($_GET['file']);
		if(file_exists($file)) {
			download_headers(basename($file));
			readfile($file);
			exit;
		} else {
			could_not_find_file();
		}
		break;
	case 'dropbox' :
		$fd = tmpfile();
		try {
			$metadata = $dbxClient->getFile(urldecode($_GET['file']), $fd);
			download_headers(substr($metadata['path'], 1));
			fseek($fd, 0);
			fpassthru($fd);
			fclose($fd);
		} catch (Exception $e) {
			could_not_find_file();
		}
		break;
	case 's3' :
		$aws = Aws::factory(
			array(
				'key'    => Config::$aws_config['key'],
				'secret' => Config::$aws_config['secret'],
				'region' => Config::$aws_config['region'],
			)
		);
		$s3 = $aws->get('s3');
		
		try {
			$result = $s3->getObject(array(
				'Bucket' => Config::$aws_config['bucket'],
				'Key'    => urldecode($_GET['file'])
			));
			
			download_headers(urldecode($_GET['file']));
			echo $result['Body'];
		} catch (Exception $e) {
			could_not_find_file();
		}
		
		break;
}

function could_not_find_file() {
	header("HTTP/1.0 404 Not Found");
	echo '<h1>Could not find file</h1>';
	exit;
}

function download_headers($file_name) {
	header('Content-Description: File Transfer');
	header('Content-Type: application/octet-stream');
	header('Content-Disposition: attachment; filename='.$file_name);
	header('Expires: 0');
	header('Cache-Control: must-revalidate');
	header('Pragma: public');
}
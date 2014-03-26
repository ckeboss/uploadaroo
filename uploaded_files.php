<?php
namespace Uploadaroo;

session_start();

include("config.php");
include("dropbox_util.php");

$dbxClient = getClient();

require 'aws-sdk/aws-autoloader.php';
use Aws\Common\Aws;
use Aws\S3\Exception\S3Exception;

?>
<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="utf-8">
		<meta http-equiv="X-UA-Compatible" content="IE=edge">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<title>Uploadaroo!</title>

		<!-- Bootstrap -->
		<link href="bootstrap/css/bootstrap.min.css" rel="stylesheet">

		<!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
		<!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
		<!--[if lt IE 9]>
			<script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
			<script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
		<![endif]-->
	</head>
	<body>
		<div class="navbar navbar-inverse" role="navigation">
			<div class="container">
				<div class="navbar-header">
					<button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-collapse">
						<span class="sr-only">Toggle navigation</span>
						<span class="icon-bar"></span>
						<span class="icon-bar"></span>
						<span class="icon-bar"></span>
					</button>
					<a class="navbar-brand" href="index.php">Uploadaroo</a>
				</div>
				<div class="collapse navbar-collapse">
					<ul class="nav navbar-nav">
						<li><a href="index.php">Upload</a></li>
						<li class="active"><a href="uploaded_files.php">Uploaded Files</a></li>
						<li><a href="https://github.com/ckeboss/uploadaroo">Code</a></li>
					</ul>
				</div><!--/.nav-collapse -->
			</div>
		</div>
		<div class="container">
			<div class="col-sm-4">
				<h2>Local Files</h2>
				<?php
				$local_files = preg_grep('/^([^.])/', scandir('uploads', SCANDIR_SORT_NONE));
					
				if(!empty($local_files)) {
				?>
				<ul class="list-unstyled">
				<?php
					foreach($local_files as $local_file) {
						echo '<li><a href="download/download.php?file='.urlencode($local_file).'&type=local">'.$local_file.'</a></li>';
					}
				?>
				</ul>
				<?php
				}
				?>
			</div>
			<div class="col-sm-4">
				<?php if($dbxClient !== false) { ?>
				<h2>Dropbox Files</h2>
				<?php
				$files = $dbxClient->getMetadataWithChildren('/');
				if(!empty($files['contents'])) {
				?>
				<ul class="list-unstyled">
				<?php
					foreach($files['contents'] as $file) {
						echo '<li><a href="download/download.php?file='.urlencode($file['path']).'&type=dropbox">'.substr($file['path'], 1).'</a></li>';
					}
				?>
				</ul>
				<?php
				}
				?>
				<?php } ?>
			</div>
			<div class="col-sm-4">
				<h2>S3 Files</h2>
				<?php
					$aws = Aws::factory(
						array(
							'key'    => Config::$aws_config['key'],
							'secret' => Config::$aws_config['secret'],
							'region' => Config::$aws_config['region'],
						)
					);
					$s3 = $aws->get('s3');
					
					$aws_files = $s3->getIterator('ListObjects', array(
						'Bucket' => Config::$aws_config['bucket'],
					));
					
					if(!empty($aws_files)) {
					?>
					<ul class="list-unstyled">
					<?php
						foreach ($aws_files as $aws_file) {
							echo '<li><a href="download/download.php?file='.urlencode($aws_file['Key']).'&type=s3">'.$aws_file['Key'].'</a></li>';
						}
					?>
					</ul>
					<?php
					}
				?>
			</div>
		</div>

		<!-- jQuery (necessary for Bootstrap's JavaScript plugins) -->
		<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.0/jquery.min.js"></script>
		<!-- Include all compiled plugins (below), or include individual files as needed -->
		<script src="bootstrap/js/bootstrap.min.js"></script>
		<script src="js/jquery.client.js"></script>
		<script src="js/scripts.js"></script>
	</body>
</html>
<?php unset($_SESSION['errors']); unset($_SESSION['success']); unset($_SESSION['values']); //Clear errors once we're done displaying them ?>
<?php
namespace Uploadaroo;

session_start();

include("config.php");
include("dropbox_util.php");

$dbxClient = getClient();

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
						<li class="active"><a href="index.php">Upload</a></li>
						<li><a href="uploaded.php">Uploaded Files</a></li>
						<li><a href="https://github.com/ckeboss/uploadaroo">Code</a></li>
					</ul>
				</div><!--/.nav-collapse -->
			</div>
		</div>
		<div class="container">
			<div class="col-sm-12">
				<?php if(isset($_SESSION['errors']['general'])) { ?>
				<div class="alert alert-danger alert-dismissable">
					<button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
					<?php echo $_SESSION['errors']['general']; ?>
				</div>
				<?php } ?>
				<?php if(isset($_SESSION['success'])) { ?>
				<div class="alert alert-success alert-dismissable">
					<button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
					<?php
					$i = 0;
					foreach($_SESSION['success'] as $success) {
						echo $success;
						$i++;
						//Put in a line break if we have more to echo
						if(count($_SESSION['success']) != $i) {
							echo "<br />";
						}
					}
					?>
				</div>
				<?php } ?>
				<form role="form" action="upload.php" method="post" enctype="multipart/form-data">
					<div class="form-group<?php echo isset($_SESSION['errors']['uploadedFile']) ? ' has-error error' : ''; ?>">
						<label for="uploadedFile">File to upload</label>
						<input type="file" id="uploadedFile" name="uploadedFile">
						<?php if(isset($_SESSION['errors']['uploadedFile'])) { ?>
						<p class="help-block text-danger"><?php echo $_SESSION['errors']['uploadedFile']; ?></p>
						<?php } else { ?>
						<p class="help-block">Maximum of 100KB please</p>
						<?php } ?>
					</div>
					<div class="form-group<?php echo isset($_SESSION['errors']['placeToUpload']) ? ' has-error error' : ''; ?>">
						<label for="placeToUpload">Where to upload to</label>
						<?php if($dbxClient === false) { ?>
						<div style="margin-bottom: 10px;">
							<a href="<?php echo getWebAuth()->start(); ?>" class="btn btn-default">Login with Dropbox</a>
						</div>
						<?php } ?>
						<select multiple class="form-control" id="placeToUpload" name="placeToUpload[]">
							<option value="local"<?php echo isset($_SESSION['values']['placeToUpload']['local']) ? ' selected="selected"' : ''; ?>>This Server</option>
							<?php if($dbxClient !== false) { ?><option value="dropbox"<?php echo isset($_SESSION['values']['placeToUpload']['dropbox']) ? ' selected="selected"' : ''; ?>>Dropbox</option><?php } ?>
							<option value="s3"<?php echo isset($_SESSION['values']['placeToUpload']['s3']) ? ' selected="selected"' : ''; ?>>AWS S3</option>
						</select>
						<?php if(isset($_SESSION['errors']['placeToUpload'])) { ?>
						<p class="help-block text-danger"><?php echo $_SESSION['errors']['placeToUpload']; ?></p>
						<?php } ?>
						<p class="help-block" id="file-upload-help"><noscript>Use Command(Mac) or <abbr title="Control">Ctrl</abbr>(Windows/Linux) to select more than one</noscript></p>
					</div>
					<button type="submit" class="btn btn-default">Submit</button>
				</form>
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
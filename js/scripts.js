jQuery(document).ready(function($) {
	if($.client.os == 'Windows' || $.client.os == 'Linux') {
		$('#file-upload-help').html('Use <kbd><abbr title="Control">Ctrl</abbr></kbd> to select more than one');
	} else if($.client.os == 'Mac') {
		$('#file-upload-help').html('Use <kbd>Command</kbd> to select more than one');
	}
	//On most mobile devices, selecting multiple select options is very intuitive and needs no explination
	
	$('#placeToUpload').change(function() {
		$( "select#placeToUpload option:selected").each(function() {
			if($( this ).val() == 'dropbox') {
/*
				authWindow = window.open('auth_dropbox.php', 'Authenticate with Dropbox', 'height=400,width=400');
				if(window.focus) {
					authWindow.focus();
				}
				return false;
*/
			}
		});
	});
});
<?php
namespace Uploadaroo;

include("config.php");
include("dropbox_util.php");

session_start();

try {
    list($accessToken, $userId, $urlState) = getWebAuth()->finish($_GET);
    // We didn't pass in $urlState to finish, and we're assuming the session can't be
    // tampered with, so this should be null.
    assert($urlState === null);
}
catch (dbx\WebAuthException_BadRequest $ex) {
    respondWithError(400, "Bad Request");
    // Write full details to server error log.
    // IMPORTANT: Never show the $ex->getMessage() string to the user -- it could contain
    // sensitive information.
    error_log("/dropbox-auth-finish: bad request: " . $ex->getMessage());
    exit;
}
catch (dbx\WebAuthException_BadState $ex) {
    // Auth session expired.  Restart the auth process.
    header("Location: ".getWebAuth()->start());
    exit;
}
catch (dbx\WebAuthException_Csrf $ex) {
    //respondWithError(403, "Unauthorized", "CSRF mismatch");
    // Write full details to server error log.
    // IMPORTANT: Never show the $ex->getMessage() string to the user -- it contains
    // sensitive information that could be used to bypass the CSRF check.
    error_log("/dropbox-auth-finish: CSRF mismatch: " . $ex->getMessage());
    exit;
}
catch (dbx\WebAuthException_NotApproved $ex) {
    echo 'Error authorizing. Please try again.';
    exit;
}
catch (dbx\WebAuthException_Provider $ex) {
    error_log("/dropbox-auth-finish: unknown error: " . $ex->getMessage());
    header("HTTP/1.1 500 Internal Server Error");
    exit;
}
catch (dbx\Exception $ex) {
    error_log("/dropbox-auth-finish: error communicating with Dropbox API: " . $ex->getMessage());
    header("HTTP/1.1 500 Internal Server Error");
    exit;
}

// NOTE: A real web app would store the access token in a database.
$_SESSION['access-token'] = $accessToken;

header("Location: index.php");
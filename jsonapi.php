<?php
$tool_user_name = 'expose-data';

include_once ( 'shared/common.php' ) ;
error_reporting( E_ALL & ~E_NOTICE ); # Don't clutter the directory with unhelpful stuff

$prot = getProtocol();
if ( array_key_exists( 'HTTP_ORIGIN', $_SERVER ) ) {
	$origin = $_SERVER['HTTP_ORIGIN'];
}


// Response Headers
header('Content-type: application/json; charset=utf-8');
header('Cache-Control: private, s-maxage=0, max-age=0, must-revalidate');
header('x-content-type-options: nosniff');
header('X-Frame-Options: SAMEORIGIN');
header('X-JSONAPI-VERSION: 0.0.0.0');
  
if ( isset( $origin ) ) {
	// Check protocol
	$protOrigin = parse_url( $origin, PHP_URL_SCHEME );
	if ($protOrigin != $prot) {
		header('HTTP/1.0 403 Forbidden');
		if ('https' == $protOrigin) {
			echo '{"error":"Please use this service over https."}';
		} else {
			echo '{"error":"Please use this service over http."}';
		}
		exit;
	}
	
	// Do we serve content to this origin?
	if ( matchOrigin( $origin ) ) {
		header('Access-Control-Allow-Origin: ' . $origin);
		header('Access-Control-Allow-Methods: GET');
	} else {
		header('HTTP/1.0 403 Forbidden');
		echo '{"error":"Accesing this tool from the origin you are attempting to connet from is not allowed."}';
		exit;
	}
}

// There are more clever ways to achieve this but for now, it should be sufficient
$action = '';
if ( array_key_exists('action', $_REQUEST) ) {
	$action = $_REQUEST['action'];
}
switch ($action) {
	case 'sha1lookup':
		// Files by SHA1
		include_once ( 'php/sha1lookup.php' );
		$sha1 = '';
		$showDeleted = false;
		
		if ( array_key_exists('sha1', $_REQUEST) ) {
			$sha1 = $_REQUEST['sha1'];
		}
		if ( array_key_exists('showdeleted', $_REQUEST) ) {
			$showDeleted = true;
		}
		$res['sha1lookup'] = sha1lookup( $sha1, $showDeleted );
		break;
	case 'getsha1':
		// SHA1 from file name
		include_once ( 'php/getsha1.php' );
		$filename = '';
		
		if ( array_key_exists('filename', $_REQUEST) ) {
			$filename = $_REQUEST['filename'];
		}
		$res['getsha1'] = getsha1( $filename );
		break;
	case 'uploadcount':
		include_once ( 'php/uploadcount.php' );
		$user = '';
		
		if ( array_key_exists('user', $_REQUEST) ) {
			$user = $_REQUEST['user'];
		}
		$res['uploadcount'] = uploadcount( $user );
		break;
	case 'useruploads':
		include_once ( 'php/useruploads.php' );

		$res['useruploads'] = useruploads( $_REQUEST['user'], $_REQUEST['dir'], $_REQUEST['start'], $_REQUEST['limit'] );
		break;
	default:
		header('HTTP/1.0 501 Not implemented');
		$res['error'] = 'Unknown action "' . $action . '". Allowed are sha1lookup, uploadcount, useruploads.';
		break;
}
if (!isset( $res )) {
	$res[] = array();
}
echo json_encode($res);
?>

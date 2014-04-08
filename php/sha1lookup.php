<?php
include_once ( 'shared/common.php' ) ;

function sha1lookup( $sha1 ) {
	$res = array();
	
	// INPUT VALIDATION
	$db = openDB('commons', 'commons');
	$sha1 = $db->real_escape_string( $sha1 );
	
	if ( strlen( $sha1 ) != 40 || !preg_match( '/^[0-9a-fA-F]{40}$/', $sha1 ) ) {
		return 'Invalid SHA1 supplied. Must be 40 HEX chars (20 Bytes).';
	}
	
	$sha1 = wfBaseConvert( $sha1, 16, 36, 31 );
	
	$sql = "SELECT oi_name, oi_archive_name, oi_size, oi_sha1, oi_timestamp FROM oldimage WHERE oi_sha1='" . $sha1 . "';";
	
	if(!$result = $db->query($sql)) {
		return 'There was an error running the query.'; // [' . $db->error . ']
	}
	
	$res['oldimage'] = array();
	while($row = $result->fetch_assoc()){
		$res['oldimage'][] = $row;
	}
	
	return $res;
}
?>

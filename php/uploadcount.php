<?php
include_once ( 'shared/common.php' ) ;

function uploadcount( $username ) {
	$res = -1;

	$db = openDB('commons', 'commons');

	// INPUT VALIDATION
	if ( strlen( $username ) > 255 || preg_match( '/(SELECT.+)|(DROP.+)|(ALTER.+)|(UNION.+)|(INSERT.+)|(\{|\}|;|%)/', $username ) ) {
		return 'Invalid user name supplied.';
	}
	$username = $db->real_escape_string( ucfirst ( $username ) );


	$sql = "SELECT count(*) AS count FROM image WHERE img_user_text='$username' ORDER BY img_timestamp DESC;";

	if(!$result = $db->query($sql)) {
		return 'There was an error running the query.'; // [' . $db->error . ']
	}

	while($row = $result->fetch_assoc()){
		$res = $row['count'];
	}

	return $res;
}
?>

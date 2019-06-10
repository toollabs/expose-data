<?php
include_once ( 'shared/common.php' ) ;

function getsha1( $filename ) {
	$res = array();
	
	// INPUT VALIDATION
	$db = openDB('commons', 'commons');
	$filename = str_replace( ' ', '_', $filename );
	$filename = $db->real_escape_string( $filename );

	$sql = "SELECT fa_name, fa_size, actor_name AS fa_user_text, comment_text AS fa_description, fa_sha1, fa_timestamp FROM filearchive INNER JOIN actor ON fa_actor = actor_id INNER JOIN comment ON fa_description_id = comment_id WHERE fa_name='" . $filename . "';";

	if(!$result = $db->query($sql)) {
		return 'There was an error running the query.'; // [' . $db->error . ']
	}

	$res['filearchive'] = array();
	while($row = $result->fetch_assoc()){
		if ( $row['fa_sha1'] ) {
			$row['fa_sha1'] = wfBaseConvert( $row['fa_sha1'], 36, 16, 40 );
		}
		$res['filearchive'][] = $row;
	}

	return $res;
}
?>

<?php
include_once ( 'shared/common.php' ) ;

function useruploads( $username, $dir, $start, $limit, $continue ) {
	$res = Array();

	$db = openDB('commons', 'commons');

	// INPUT VALIDATION
	if ( strlen( $username ) > 255 || preg_match( '/(SELECT.+)|(DROP.+)|(ALTER.+)|(UNION.+)|(INSERT.+)|(\{|\}|;|%)/', $username ) ) {
		return 'Invalid user name supplied.';
	}
	$username = $db->real_escape_string( ucfirst ( $username ) );

	if ( empty( $dir ) ) {
		$dir = 'older';
	}
	if ( $dir && $dir !== 'newer' && $dir !== 'older' ) {
		return 'Invalid direction supplied.';
	}

	$rDate1 = '/\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}Z/';
	$rDate2 = '/\d{14}/';
	if ( !empty( $start ) ) {
		if ( strlen( $start ) > 20 || !( preg_match( $rDate1, $start ) && preg_match( $rDate2, $start ) ) ) {
			return 'Invalid start date supplied.';
		}
		$start = preg_replace( '/\D+/', '' );
		$start = $db->real_escape_string( ucfirst ( $start ) );
	} else {
		$start = '';
	}

	if ( !$limit ) {
		$limit = '15';
	}
	if ( strlen( $limit ) > 3 || !preg_match( '/^\d+$/', $limit ) ) {
		return 'Invalid limit supplied.';
	}

	$sql = "SELECT count(*) AS count FROM image INNER JOIN actor ON img_actor = actor_id WHERE actor_name='$username' ORDER BY img_timestamp DESC;";

	if(!$result = $db->query($sql)) {
		return 'There was an error running the query.'; // [' . $db->error . ']
	}

	while($row = $result->fetch_assoc()){
		$res = $row['count'];
	}

	return $res;
}
?>

<?php
include_once ( 'shared/common.php' ) ;

function sha1lookup( $sha1, $deleted ) {
	$res = array();
	
	// INPUT VALIDATION
	$db = openDB('commons', 'commons');
	$sha1 = $db->real_escape_string( $sha1 );
	
	if ( strlen( $sha1 ) != 40 || !preg_match( '/^[0-9a-fA-F]{40}$/', $sha1 ) ) {
		return 'Invalid SHA1 supplied. Must be 40 HEX chars (20 Bytes).';
	}
	
	$sha1 = wfBaseConvert( $sha1, 16, 36, 32 );
	
	$sql = "SELECT oi_name, oi_archive_name, oi_size, oi_sha1, oi_timestamp FROM oldimage WHERE oi_sha1='" . $sha1 . "';";
	
	if(!$result = $db->query($sql)) {
		return 'There was an error running the query.'; // [' . $db->error . ']
	}
	
	$res['oldimage'] = array();
	while($row = $result->fetch_assoc()){
		$res['oldimage'][] = $row;
	}

	// Something sophisticated quota logic to prevent abuse
	if ( $deleted ) {
		$key = 'expose-data-faquery-rel';
		$keyAbs = 'expose-data-faquery-abs';
		$quota = 3;
		$quotaAbs = 1000;
		$redis = new Redis();
		$redis->connect( 'tools-redis', 6379 );
		if ( $redis->exists( $key ) ) {
			$quota = $redis->get( $key );
			if ( is_numeric( $quota ) ) {
				if ( (int) $quota < 1 ) {
					$redis->close();
					return $res;
				}
			} else {
				$quota = 3;
			}
		}
		if ( $redis->exists( $keyAbs ) ) {
			$quotaAbs = $redis->get( $keyAbs );
			if ( is_numeric( $quotaAbs ) ) {
				if ( (int) $quotaAbs < 1 ) {
					$redis->close();
					return $res;
				}
			} else {
				$quotaAbs = 1000;
			}
		}
		$quota--;
		$quotaAbs--;

		$redis->setex( $key, 500, $quota );
		// Expires in one hour
		$redis->setex( $keyAbs, 3600, $quotaAbs );
		$redis->close();

		$res['quota-rel'] = $quota;
		$res['quota-abs'] = $quotaAbs;

		$sql = "SELECT fa_name, fa_size, fa_sha1, fa_timestamp FROM filearchive WHERE fa_sha1='" . $sha1 . "';";
		
		if(!$result = $db->query($sql)) {
			return 'There was an error running the query.'; // [' . $db->error . ']
		}
		
		$res['filearchive'] = array();
		while($row = $result->fetch_assoc()){
			$res['filearchive'][] = $row;
		}

		$redis->connect( 'tools-redis', 6379 );
		if ( $redis->exists( $key ) ) {
			$quota = $redis->get( $key );
			if ( is_numeric( $quota ) ) {
				$quota++;
				$redis->setex( $key, 60, $quota );
			}
		}
		$redis->close();
	}
	
	return $res;
}
?>

<?php
include_once ( 'shared/common.php' ) ;

function copyuploadsdomains() {
	$res = array();

	$initializeSettingsURL = 'https://noc.wikimedia.org/conf/InitialiseSettings.php.txt';
	$jsonFileName = 'copyuploadsdomains.json';
	$key = 'expose-data-copyuploadsdomains';
	$now = time();
	$redis = new Redis();
	$redis->connect( 'tools-redis', 6379 );
	if ( $redis->exists( $key ) ) {
		$lastRun = $redis->get( $key );
		if ( is_numeric( $lastRun ) ) {
			$lastRun = (int) $lastRun;
			$diff = $now - $lastRun;
			if ( $diff < 1 * 24 * 60 * 60 ) {
				$redis->close();
				return copyuploadsdomains_cached( $jsonFileName );
			}
		}
	}

	// Expires in one day
	$redis->setex( $key, 1 * 24 * 60 * 60, $now );
	$redis->close();

	$wgConf = array(
		'settings' => null,
	);
	// Since it would likely to be more evil to store AND execute
	// a compromised PHP script, let's just execute :)
	$InitialiseSettings = file_get_contents( $initializeSettingsURL );
	// Remove php-mode indicator
	$InitialiseSettings = str_replace( '<?php', '', $InitialiseSettings );
	// Evil eval
	eval( $InitialiseSettings );
	// Now, we should have a property called "wgCopyUploadsDomains"
	$wgCopyUploadsDomains = $wgConf->settings['wgCopyUploadsDomains']['+commonswiki'];
	$fp = fopen( $jsonFileName, 'w' );
	fwrite( $fp, json_encode( $wgCopyUploadsDomains ) );
	fclose( $fp );
	return copyuploadsdomains_cached( $jsonFileName );
}

function copyuploadsdomains_cached( $jsonFileName ) {
	$wgCopyUploadsDomains = json_decode( file_get_contents( $jsonFileName ) );
	return $wgCopyUploadsDomains;
}

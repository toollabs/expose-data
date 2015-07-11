<?php
include_once ( 'shared/common.php' ) ;

function autopatrolled_candidates() {
	$tsvFileName = '../www/static/autopatrolled_candidates.tsv';
	$jsonFileName = '../www/static/autopatrolled_candidates.json';
	$tsvLastWritten = filemtime( $tsvFileName );
	$jsonLastWritten = filemtime( $jsonFileName );

	if ( false === $tsvLastWritten ) {
		return 'error: TSV list not found.';
	}
	if ( false === $jsonLastWritten ) {
		$jsonLastWritten = $tsvLastWritten - 1;
	}

	$parsed;
	if ( $tsvLastWritten >  $jsonLastWritten ) {
		// Update JSON file
		$parsed = parseTSV( $tsvFileName );
		$fp = fopen( $jsonFileName, 'w' );
		fwrite( $fp, json_encode( $parsed ) );
		fclose( $fp );
	} else {
		$parsed = json_decode( file_get_contents( $jsonFileName ) );
	}
	return $parsed;
}

function parseTSV( $tsvFileName ) {
	$fp = fopen( $tsvFileName, 'r' );
	$line = 0;
	$result = array();
	$firstLine;
	$fieldCount;
	while ( ( $data = fgetcsv( $fp, 0, "\t" ) ) !== false ) {
		if ( $line === 0 ) {
			$firstLine = $data;
			$fieldCount = count( $data );
		} else {
			$record = array();
			for ( $c = 0; $c < $fieldCount; $c++ ) {
				$fieldValue = $data[$c];
				$record[ $firstLine[$c] ] = ($fieldValue === 'NULL') ? '' : $fieldValue;
			}
			$result[] = $record;
		}
		$line++;
	}
	return $result;
}



<?php

namespace pinst\helper;
use pinst\exception\ExitException;

class FileHelper {
	public static function createDirectory($path, $mode = 0775, $recursive = true) {
		if (is_dir ( $path )) {
			return true;
		}
		$parentDir = dirname ( $path );
		if ($recursive && ! is_dir ( $parentDir )) {
			static::createDirectory ( $parentDir, $mode, true );
		}
		try {
			$result = mkdir ( $path, $mode );
			chmod ( $path, $mode );
		} catch ( \Exception $e ) {
			throw new ExitException( "Failed to create directory,", $e->getCode (), $e );
		}
		return $result;
	}
	
	public static function getExtension($path) {
		return pathinfo ( $path, PATHINFO_EXTENSION );
	}
}

?>
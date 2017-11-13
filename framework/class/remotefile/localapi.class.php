<?php
/**
 * @package     ${NAMESPACE}
 * @subpackage
 *
 * @copyright   A copyright
 * @license     A "Slug" license name e.g. GPL2
 */
class LocalApi {

	public function putFile($path, $file) {
		$ismk = mkdir($path, 0777, true);
		if($ismk) {
			if(is_uploaded_file($file)){
				return move_uploaded_file($file, $path);
			}else {
				return rename($file, $path);
			}
		}
	}

	public function put($path, $content) {
		file_put_contents($path, $content);
	}

	public function delete($path) {
		unlink($path);
	}
}
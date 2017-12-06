<?php
/**
 * 图片处理类
 *
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 */
defined('IN_IA') or exit('Access Denied');

class Image {

	private $src;
	private $resize_width = 0;
	private $resize_height = 0;
	private $resize = false;
	private $image = null;
	private $new_image = null;
	private $imageinfo = array('width'=>0, 'height'=>0);
	private $ext = '';
	public function __construct($src) {
		$this->src = $src;
		$this->ext = pathinfo($src, PATHINFO_EXTENSION);
	}

	public static function create($src) {
		return new Image($src);
	}

	public function resize($width = 0, $height = 0) {
		if($width > 0 || $height > 0) {
			$this->resize = true;
		}
		if($width > 0 && $height == 0) {
			$height = $width;
		}
		if($height > 0 && $width == 0) {
			$width = $height;
		}
		$this->resize_width = $width;
		$this->resize_height = $height;
		return $this;
	}

	public function crop() {
		throw new Exception('未实现');
	}

	public function getExt() {
		return $this->ext;
	}

	public function isPng() {
		return file_is_image($this->src) && $this->ext == 'png';
	}

	public function isJPEG() {
		return file_is_image($this->src) && in_array($this->ext, array('jpg', 'jpeg'));
	}

	public function isGif() {
		return file_is_image($this->src) && $this->ext == 'gif';
	}
	/**
	 *  保存
	 * @param $path
	 * @since version
	 */
	public function saveTo($path) {
		$this->handle();
		$ext = $this->ext;
		if($ext == 'jpg') {
			$ext = 'jpeg';
		}
		$func = 'image'.$ext;
		$saved = $func($this->image(), $path);
		$this->destroy();
		return $saved;
	}

	protected function handle() {
		$this->image = $this->createResource();
		if($this->resize) {
			$this->doResize();
		}
	}

	protected function doResize() {
		$this->imageinfo = getimagesize($this->src);
		$this->new_image = imagecreatetruecolor($this->resize_width, $this->resize_height);
		imagealphablending($this->new_image, false);
		imagesavealpha($this->new_image, true);
		imagecopyresampled($this->new_image, $this->image, 0, 0, 0, 0, $this->resize_width, $this->resize_height,
		$this->imageinfo[0],$this->imageinfo[1]);
	}

	private function image() {
		return $this->new_image ? $this->new_image : $this->image;
	}
	public function destroy() {
		if($this->image) {
			imagedestroy($this->image);
		}
		if($this->new_image) {
			imagedestroy($this->new_image);
		}
	}


	protected function createResource() {
		if($this->isPng()) {
			return imagecreatefrompng($this->src);
		}
		if($this->isJPEG()) {
			return imagecreatefromjpeg($this->src);
		}
		if($this->isGif()) {
			return imagecreatefromgif($this->src);
		}
		return null;
	}

	/**
	 * 转为base64
	 * @param string $prefix
	 *
	 * @return string
	 *
	 * @since version
	 */
	public function toBase64($prefix = 'data:image/%s;base64,') {
		$filename = tempnam('tmp', 'base64');
		$prefix = sprintf($prefix, $this->ext);
		$this->saveTo($filename);
		$content = file_get_contents($filename);
		$base64 = base64_encode($content);
		unlink($filename);
		return $prefix.$base64;
	}

	public function __destruct() {
//		$this->destroy();
	}
}
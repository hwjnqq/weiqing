<?php

/**
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 * User: fanyk
 * Date: 2017/10/28
 * Time: 14:58.
 */
class UploadedFile extends SplFileInfo {

	private $img_mimetype = array('image/jpeg','image/jpg','image/png','image/gif','image/bmp');
	private $harmtype = array('asp', 'php', 'jsp', 'js', 'css', 'php3', 'php4', 'php5', 'ashx', 'aspx', 'exe', 'cgi');
	/**
	 * @var int[]
	 */
	private static $errors = array(
		UPLOAD_ERR_OK,
		UPLOAD_ERR_INI_SIZE,
		UPLOAD_ERR_FORM_SIZE,
		UPLOAD_ERR_PARTIAL,
		UPLOAD_ERR_NO_FILE,
		UPLOAD_ERR_NO_TMP_DIR,
		UPLOAD_ERR_CANT_WRITE,
		UPLOAD_ERR_EXTENSION,
	);

	/**
	 * 上传文件名
	 * @var string
	 */
	private $clientFilename;

	/**
	 * //上传的mimeType
	 * @var string
	 */
	private $clientMediaType;

	/**
	 * @var int
	 */
	private $error;

	/**
	 * @var null|string
	 */
	private $file;

	/**
	 * @var bool
	 */
	private $moved = false;

	/**
	 * @var int
	 */
	private $size;

	public function __construct(
		$streamOrFile,
		$size,
		$errorStatus,
		$clientFilename = null,
		$clientMediaType = null
	) {
		$this->setError($errorStatus);
		$this->setSize($size);
		$this->setClientFilename($clientFilename);
		$this->setClientMediaType($clientMediaType);
		parent::__construct($streamOrFile);
		if ($this->isOk()) {
			$this->setStreamOrFile($streamOrFile);
		}
	}

	/**
	 * Depending on the value set file or stream variable.
	 *
	 * @param mixed $streamOrFile
	 *
	 * @throws InvalidArgumentException
	 */
	private function setStreamOrFile($streamOrFile) {
		if (is_string($streamOrFile)) {
			$this->file = $streamOrFile;
		} else {
			throw new InvalidArgumentException(
				'Invalid stream or file provided for UploadedFile'
			);
		}
	}

	/**
	 * @param int $error
	 *
	 * @throws InvalidArgumentException
	 */
	private function setError($error) {
		if (false === is_int($error)) {
			throw new InvalidArgumentException(
				'Upload file error status must be an integer'
			);
		}

		if (false === in_array($error, self::$errors)) {
			throw new InvalidArgumentException(
				'Invalid error status for UploadedFile'
			);
		}

		$this->error = $error;
	}

	/**
	 * @param int $size
	 *
	 * @throws InvalidArgumentException
	 */
	private function setSize($size) {
		if (false === is_int($size)) {
			throw new InvalidArgumentException(
				'Upload file size must be an integer'
			);
		}

		$this->size = $size;
	}

	/**
	 * @param mixed $param
	 *
	 * @return boolean
	 */
	private function isStringOrNull($param) {
		return in_array(gettype($param), array('string', 'NULL'));
	}

	/**
	 * @param mixed $param
	 *
	 * @return boolean
	 */
	private function isStringNotEmpty($param) {
		return is_string($param) && false === empty($param);
	}

	/**
	 * @param string|null $clientFilename
	 *
	 * @throws InvalidArgumentException
	 */
	private function setClientFilename($clientFilename) {
		if (false === $this->isStringOrNull($clientFilename)) {
			throw new InvalidArgumentException(
				'Upload file client filename must be a string or null'
			);
		}

		$this->clientFilename = $clientFilename;
	}

	/**
	 * @param string|null $clientMediaType
	 *
	 * @throws InvalidArgumentException
	 */
	private function setClientMediaType($clientMediaType) {
		if (false === $this->isStringOrNull($clientMediaType)) {
			throw new InvalidArgumentException(
				'Upload file client media type must be a string or null'
			);
		}

		$this->clientMediaType = $clientMediaType;
	}

	/**
	 * Return true if there is no upload error.
	 *
	 * @return boolean
	 */
	public function isOk() {
		return $this->error === UPLOAD_ERR_OK && !in_array($this->getExtension(),$this->harmtype);
	}

	/**
	 * @return boolean
	 */
	public function isMoved() {
		return $this->moved;
	}

	/**
	 * @throws RuntimeException if is moved or not ok
	 */
	private function validateActive() {
		if (false === $this->isOk()) {
			throw new RuntimeException('Cannot retrieve stream due to upload error');
		}

		if ($this->isMoved()) {
			throw new RuntimeException('Cannot retrieve stream after it has already been moved');
		}
	}

	public function moveTo($targetPath) {
		$this->validateActive();

		if (false === $this->isStringNotEmpty($targetPath)) {
			throw new InvalidArgumentException(
				'Invalid path provided for move operation; must be a non-empty string'
			);
		}

		if ($this->file) {
			$this->moved = php_sapi_name() == 'cli'
				? rename($this->file, $targetPath)
				: move_uploaded_file($this->file, $targetPath);
		}

		if (false === $this->moved) {
			throw new RuntimeException(
				sprintf('Uploaded file could not be moved to %s', $targetPath)
			);
		}
	}

	/**
	 * {@inheritdoc}
	 *
	 * @return int|null The file size in bytes or null if unknown.
	 */
	public function getSize() {
		return $this->size;
	}

	/**
	 * {@inheritdoc}
	 *  上传错误码
	 * @see http://php.net/manual/en/features.file-upload.errors.php
	 *
	 * @return int One of PHP's UPLOAD_ERR_XXX constants.
	 */
	public function getError() {
		return $this->error;
	}

	/**
	 * {@inheritdoc}
	 *
	 * @return string|null The filename sent by the client or null if none
	 *                     was provided.
	 */
	public function getClientFilename() {
		return $this->clientFilename;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getClientMediaType() {
		return $this->clientMediaType;
	}

	/**
	 *
	 * 是否是图片
	 * @return bool
	 *
	 * @since version
	 */
	public function isImage() {
		return in_array($this->clientMediaType, $this->img_mimetype);
	}

	/**
	 *  验证图片 大小
	 * @param $type
	 *
	 *
	 * @since version
	 * @throws Exception
	 */
	public function valid($type, $is_wechat = false, $option = array()) {
		if(!$this->isOk()) {
			throw new Exception('请选择上传文件');
		}
		$rule = $this->getValidRule($type);
		$ext = $this->clientExt();

		if(!in_array($ext, $rule['ext'])) {
			throw new Exception('不允许的文件后缀');
		}
		if($this->getSize()/1024 > $rule['size']) {
			throw new Exception('图片大小超出'.$rule['size']);
		}
		if($rule['max']) {
			if(! $this->validMaxResource($rule['max'], $type, $option)) {
				throw new Exception('文件数量超过限制,请先删除部分文件再上传');
			}

		}
	}

	private function validMaxResource($max, $type, $option) {
		$uniacid = $option['uniacid'];
		$acid = $option['acid'];
		$now_count = pdo_fetchcolumn('SELECT COUNT(*) FROM ' . tablename('wechat_attachment') .
			' WHERE uniacid = :aid AND acid = :acid AND model = :model AND type = :type',
			array(':aid' => $uniacid, ':acid' => $acid, ':model' => 'perm', ':type' => $type));
		if($now_count >= $max) {
			return false;
		}
		return true;
	}

	/**
	 *  获取图片上传验证规则
	 * @param $type
	 * @param bool $is_wechat
	 *
	 * @return array
	 *
	 * @since version
	 */
	private function getValidRule($type, $is_wechat = false) {
		if($is_wechat) {
			return config()->wechatLimit($type);
		}
		return config()->localLimit($type);
	}

	/**
	 * 上传文件后缀
	 * @return mixed
	 *
	 * @since version
	 */
	public function clientExt() {
		$ext = pathinfo($this->getClientFilename(), PATHINFO_EXTENSION);
		return $ext;
	}

	public function validImage() {
		if(!$this->isImage()) {
			throw new Exception('不是有效的图片');
		}
		$ext = $this->clientExt();
		if(!config()->allowImageExt($ext)) {
			throw new Exception('不允许的文件后缀');
		}
		if($this->getSize()/1024 > config()->maxImageSize()) {
			throw new Exception('图片大小超出'.(config()->maxImageSize()));
		}
	}

	public function hashName() {
		return random(40).'.'.$this->clientExt();
	}
	/**
	 * Store the uploaded file on a filesystem disk.
	 *
	 * @param  string  $path
	 * @param  array|string  $options
	 * @return string|false
	 */
	public function store($path = null, $option = array())
	{
		return $this->storeAs($path, $this->hashName());
	}


	public function storeAs($path, $filename) {
		if(!$path) {
			$path = $this->getStorePath();
		}
		$path = rtrim($path, '/');
		$path = $path.'/'.$filename;
		if($this->isImage()) {
			return Storage::disk()->putFile($path, $this);// 只有图片才存到cdn
		}
		return Storage::disk('files')->putFile($path, $this);

	}



	/**
	 * 保存到微信
	 *
	 * @since version
	 */
	public function storeToWechat($uniacid) {
		$path = $this->getStorePath($uniacid);
		Storage::disk('wechat')->putFile($path, $this, array('uniacid'=>$uniacid));
	}

	public function storeTo($global, $dest_dir) {

	}


	//获取存储路径
	private function getStorePath($uniacid = 0) {
		global $_W;
		if(!$uniacid) {
			$uniacid = intval($_W['uniacid']);
		}
		$date =  date('Y/m');
		return "images/$uniacid/$date/";
	}


	public static function createFromGlobal() {
		$files = array();
		foreach ($_FILES as $key => $file) {
			$createFiles = static::create($file);
			$files[$key] = $createFiles;
		}

		return $files;
	}

	/**
	 *  从数组中创建文件.
	 *
	 * @param $file
	 *
	 * @return array|UploadedFile
	 */
	private static function create($file) {
		if (is_array($file['tmp_name'])) {
			return static::createArrayFile($file);
		}

		return static::createUploadedFile($file);
	}

	/**
	 *  如果传的是多个文件.
	 *
	 * @param $files
	 *
	 * @return array
	 */
	public static function createArrayFile($files) {
		$data = array();
		foreach (array_keys($files['tmp_name']) as $key) {
			$file = array(
				'tmp_name' => $files['tmp_name'][$key],
				'size' => $files['size'][$key],
				'error' => $files['error'][$key],
				'name' => $files['name'][$key],
				'type' => $files['type'][$key],
			);
			$data[$key] = self::createUploadedFile($file);
		}

		return $data;
	}

	private static function createUploadedFile($value) {
		$upfile = new static(
			$value['tmp_name'],
			$value['size'],
			$value['error'],
			$value['name'],
			$value['type']
		);

		return $upfile;
	}

}

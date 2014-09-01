<?php
class Logger {
	private static $_lName = null;
	private static $_handle = null;
	private static $_singleton;

	public function __construct() {
		$uploadDir = wp_upload_dir();
		self::$_lName = $uploadDir['path'] . '/import-log/log';
		$this->logOpen(); //Begin logging.
	}

	public static function getInstance() {
		if(!self::$_singleton) {
			self::$_singleton = new logger();
		}
		return self::$_singleton;
	}

	function __destruct() {
		fclose(self::$_lName); //Close when php script ends (always better to be proper.)
	}

	//Open Logfile
	private function logOpen(){
		$this->handle = fopen(self::$_lName, 'a') or exit("Can't open " . self::$_lName); //Open log file for writing, if it does not exist, create it.
	}

	//Write Message to Logfile
	public function logWrite($message){
		$time = date('m-d-Y @ H:i:s -'); //Grab Time
		fwrite($this->handle, $time . " " . $message . "\n"); //Output to logfile
	}

	//Clear Logfile
	public function logClear(){
		ftruncate($this->handle, 0);
	}
}
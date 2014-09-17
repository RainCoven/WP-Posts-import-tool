<?php
require_once(dirname( __FILE__ ) . '/logger.php');

/**
 * Used to connect remote database
 *
 * Class RemoteDbConnect
 */
class RemoteDbConnect {

	/**
	 * DB Credentials
	 *
	 * @var array
	 */
	private static $_credentials = array(
		'host' => '127.0.0.1',
		'user' => 'root',
		'pass' => '123321',
		'db' => 'wordpress'
	);

	private static $_singleton;

	private function __construct() {
		self::_setCredentials();
		$creds = self::$_credentials;
		$this->_connect(
			$creds['host'],
			$creds['user'],
			$creds['pass'],
			$creds['db']
		);
		// set error level to warnings
		$this->dbh->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING );
	}

	public static function _getInstance() {
		if(!self::$_singleton) {
			self::$_singleton = new RemoteDbConnect();
		}
		return self::$_singleton;
	}


	/**
	 * Return result of custom SQL query execution
	 *
	 * @param $sql
	 *
	 * @return mixed
	 */
	public function runQuery($sql) {
		$log = Logger::getInstance();
		try {
			$query = $this->dbh->prepare($sql);
			$query->execute();
			$result = $query->fetchAll(PDO::FETCH_ASSOC);
			$log->logWrite('PDO SQL query execution: OK');
			return $result;
		} catch ( PDOException $e ) {
			$log->logWrite('PDO SQL query execution: ERROR! : ' . $e->getMessage());
			return $e->getMessage();
		}
	}

	private function _connect($host, $user, $pass, $db) {
		$log = Logger::getInstance();
		try {
			$this->dbh = new PDO( "mysql:host=$host;dbname=$db", $user, $pass );
		} catch ( PDOException $e ) {
			$log->logWrite('Connected to remote DB: ERROR! : ' . $e->getMessage());
			return $e->getMessage();
		}
		$log->logWrite('Connected to remote DB: OK');
		return true;
	}

	public function disconnect() {
		$this->dbh = null;
	}

	public static function _setCredentials($newCredentials = array()) {
		if(!empty($newCredentials)) {
			self::$_credentials = $newCredentials;
		} else {
			self::$_credentials = array(
				'host' => get_option('a2idb-host'),
				'user' => get_option('a2idb-user'),
				'pass' => get_option('a2idb-pass'),
				'db' => get_option('a2idb-name')
			);
		}
	}
}
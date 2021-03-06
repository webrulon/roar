<?php namespace System\Database;

/**
 * Nano
 *
 * Just another php framework
 *
 * @package		nano
 * @link		http://madebykieron.co.uk
 * @copyright	Copyright 2013 Kieron Wilson
 * @license		http://opensource.org/licenses/MIT The MIT License (MIT)
 */

use PDO;
use PDOException;
use ErrorException;
use Exception;
use System\Config;

abstract class Connector {

	/**
	 * Holds the php pdo instance
	 *
	 * @var object
	 */
	protected $pdo;

	/**
	 * Table prefix string
	 *
	 * @var string
	 */
	public $table_prefix = '';

	/**
	 * Log of all queries
	 *
	 * @var array
	 */
	private $queries = array();

	/**
	 * Establish new connection
	 *
	 * @param array
	 */
	public function __construct($config) {
		try {
			$this->pdo = $this->connect($config);
			$this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

			if(isset($config['prefix'])) {
				$this->table_prefix = $config['prefix'];
			}
		} catch(PDOException $e) {
			throw new ErrorException($e->getMessage());
		}
	}

	/**
	 * Returns a new PDO instance
	 *
	 * @param array
	 * @return object
	 */
	abstract protected function connect($config);

	/**
	 * A simple database query wrapper
	 *
	 * @param string
	 * @param array
	 * @return array
	 */
	public function ask($sql, $binds = array()) {
		if(Config::db('profiling')) {
			$this->queries[] = compact('sql', 'binds');
		}

		$statement = $this->pdo->prepare($sql);
		$result = $statement->execute($binds);

		return array($result, $statement);
	}

	/**
	 * Return the profile array
	 *
	 * @return array
	 */
	public function profile() {
		return $this->queries;
	}

	/**
	 * Get the PDO instance
	 *
	 * @return object
	 */
	public function instance() {
		return $this->pdo;
	}

	/**
	 * Magic method for calling methods on PDO instance
	 *
	 * @param string
	 * @param array
	 * @return mixed
	 */
	public static function __callStatic($method, $arguments) {
		return call_user_func_array(array($this->pdo, $method), $arguments);
	}

}
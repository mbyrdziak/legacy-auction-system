<?php
class Repository {
	
	/**
	 * @var Repository
	 */
	private static $repository;
	
	public static function getInstance() {
		
		if (self::$repository == null) {
			self::$repository = new Repository();
		}
		
		return self::$repository;
	}
	
	public function persist($_object) {
		usleep(200);
		echo "Storing object " . $_object->__toString() . "\n";
	}
	
	public function remove($_object) {
		usleep(200);
		echo "Removing object " . $_object->__toString() . "\n";
	}
	
	public function beginTransaction() {
		usleep(200);
		echo "Starting Transaction\n";
	}
	
	public function rollback() {
		usleep ( 200 );
		echo "Rolling back!\n";
	}
	
	public function commit() {
		usleep ( 200 );
		echo "Commiting\n";
	}
}
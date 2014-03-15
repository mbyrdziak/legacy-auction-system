<?php
class User {
	
	/**
	 * @var int
	 */
	private $id;
	
	/**
	 * @var string
	 */
	private $login;
	
	public function __construct($login) {
		$this->login = $login;
	}
	
	/**
	 * @return string
	 */
	public function getLogin() {
		return $this->login;
	}
	
	/**
	 * @param string $login
	 */
	public function setLogin($login) {
		$this->login = $login;
	} 
	
	/**
	 * @return int
	 */
	public function getId() {
		return $this->id;
	}
	
	/**
	 * @param int $id
	 */
	public function setId($id) {
		$this->id = $id;
	}
	
	public function __toString() {
		return $this->login;
	}
}
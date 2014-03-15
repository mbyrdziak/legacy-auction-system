<?php

include_once 'User.php';
include_once 'HistoryProvider.php';
include_once 'SimpleHistoryProvider.php';
include_once 'MailProvider.php';
include_once 'SimpleMailProvider.php';

class RequestContext {
	
	/**
	 * @var User
	 */
	private $user;
	
	/**
	 * @var DateTime
	 */
	private $now;
	
	/**
	 * @var HistoryProvider
	 */
	private $historyProvider;
	
	/**
	 * @var MailProvider
	 */
	private $mailProvider;
	
	/**
	 * @param User user
	 */
	public function __construct(User $user) {
		$this->user = $user;
		$this->now = new DateTime('NOW');
		
		$this->historyProvider = new SimpleHistoryProvider();
		$this->mailProvider = new SimpleMailProvider();
	}
	
	/**
	 * @return User
	 */
	public function getUser() {
		return $this->user;
	}
	
	/**
	 * @return DateTime
	 */
	public function getNow() {
		return $this->now;
	}
	
	/**
	 * @return HistoryProvider
	 */
	public function getHistoryProvider() {
		return $this->historyProvider;
	}
	
	/**
	 * @return MailProvider
	 */
	public function getMailProvider() {
		return $this->mailProvider;
	}
	
	public function setNow(DateTime $now) {
		$this->now = $now;
	}
}
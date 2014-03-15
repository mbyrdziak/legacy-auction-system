<?php
include_once 'Auction.php';
include_once 'User.php';

class Bid {
	
	/**
	 * @var Auction
	 */
	private $auction;
	
	/**
	 * @var int
	 */
	private $price;
	
	/**
	 * @var User
	 */
	private $buyer;
	
	/**
	 * @var DateTime
	 */
	private $bidDate;
	
	/**
	 * @param Auction $auction
	 * @param int $price
	 * @param User $buyer
	 * @param DateTime $bidDate
	 */
	public function __construct(Auction $auction, $price, User $buyer, DateTime $bidDate) {
		$this->auction = $auction;
		$this->price = $price;
		$this->buyer = $buyer;
		$this->bidDate = $bidDate;
	}
	
	/**
	 * @return Auction
	 */
	public function getAuction() {
		return $this->auction;
	}
	
	/**
	 * @return int
	 */
	public function getPrice() {
		return $this->price;
	}
	
	/**
	 * @return User
	 */
	public function getBuyer() {
		return $this->buyer;
	}
	
	/**
	 * @return DateTime
	 */
	public function getBidDate() {
		return $this->bidDate;
	}
}
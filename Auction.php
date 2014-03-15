<?php
include_once 'RequestContext.php';
include_once 'AuctionStatus.php';
include_once 'AuctionType.php';
include_once 'Bid.php';
include_once 'Exceptions.php';
include_once 'HistoryEvent.php';

class Auction {
	
	/**
	 * @var RequestContext
	 */
	private $context;
	
	/**
	 * @var string
	 */
	private $name;
	
	/**
	 * @var DateTime
	 */
	private $startTime;
	
	/**
	 * @var DateTime
	 */
	private $endTime;
	
	/**
	 * @var DateTime
	 */
	private $activateDate;
	
	/**
	 * @var AuctionStatus
	 */
	private $status;
	
	/**
	 * @var AuctionType
	 */
	private $type;
	
	/**
	 * @var int
	 */
	private $startingPrice;
	
	/**
	 * @var int
	 */
	private $buy_now_price;
	
	/**
	 * @var User
	 */
	private $owner;
	
	
	/**
	 * @var Bid[]
	 */
	private $bids = array();
	

	/**
	 * This is constructor, set auction type and status
	 * 
	 * @param RequestContext $p_context
	 * @param string $p_auctionName
	 * @param DateTime $p_startTime
	 * @param DateTime $p_endTime
	 * @param AuctionType $p_type
	 * @param int $p_startingPrice
	 * @param int $p_buyNowPrice
	 * @param AuctionStatus $p_status
	 * @throws OperationNotAllowedException
	 * @throws InvalidObjectTypeException
	 */
	public function __construct(RequestContext $p_context, $p_auctionName, DateTime $p_startTime, 
			DateTime $p_endTime, $p_type, $p_startingPrice, $p_buyNowPrice, $p_status = AuctionStatus::NEW_) {
		
		$this->context = $p_context;
		
		if ($p_startTime->getTimestamp() < $p_context->getNow()->getTimestamp()) {
			throw new OperationNotAllowedException("StartTime must be from future");
		}
		
		if ($p_endTime->getTimestamp() < $p_context->getNow()->getTimestamp()) {
			throw new OperationNotAllowedException("EndTime is not from future!!!");
		}
		
		if ($p_endTime->getTimestamp() < $p_startTime->getTimestamp()) {
			throw new OperationNotAllowedException("request.StartTime must be before request.EndTime");
		}
		
		$this->name = $p_auctionName;
		$this->startTime = $p_startTime;
		$this->endTime = $p_endTime;
		$this->type = $p_type;
		
		if ($p_type == AuctionType::BUY_NOW) {
			$this->buy_now_price = $p_buyNowPrice;
		} else if ($p_type == AuctionType::BID) {
			$this->startingPrice = $p_startingPrice;
		} else {
			throw new InvalidObjectTypeException("Unexpected object type");
		}
		
		$this->owner = $p_context->getUser();
		$this->status = $p_status;
		
		$p_context->getHistoryProvider()->saveHistory(HistoryEvent::AUCTION_CREATED);
	}
	
	/**
	 * @throws InvalidObjectStatusException
	 * @throws OperationNotAllowedException
	 */
	public function activate() {
		if ($this->status != AuctionStatus::NEW_) {
			throw new InvalidObjectStatusException(printf("Current auction status is %s, expected %s", $this->status, AuctionStatus::NEW_));
		}
		
		if ($this->startTime->getTimestamp() > $this->context->getNow()->getTimestamp()) {
			throw new OperationNotAllowedException("Given time must be from past");
		}
		
		$this->status = AuctionStatus::ACTIVE;
		$this->activateDate = $this->context->getNow();
		
		$this->context->getHistoryProvider()->saveHistory(HistoryEvent::AUCTION_ACTIVATED);
	}
	
	/**
	 * @param unknown $price
	 * @throws InvalidObjectTypeException
	 * @throws NotSufficientFoundsException
	 * @throws InvalidObjectStatusException
	 * @throws InvalidUserException
	 */
	public function bid($price)
	{
		if ($this->type != AuctionType::BID)
		{
			throw new InvalidObjectTypeException(printf("Current auction type is %s, expected %s", $this->type, AuctionType::BID));
		}
	
		if ($this->startingPrice > $price)
		{
			throw new NotSufficientFoundsException("Current bid price must be heigher than starting price");
		}
		
		if (count($this->bids) != 0)
		{
			$lastBid = end(array_values($this->bids));
			if ($lastBid != null && $lastBid->getPrice > $price) {
				throw new NotSufficientFoundsException("Current bid price is to low");
			} else {
				
			}
		}

		if ($this->status != AuctionStatus::ACTIVE) {
			throw new InvalidObjectStatusException("Auction has to be Active");
		} else {
			if ($this->owner === $this->context->getUser()) {
				throw new InvalidUserException("Owner is not loggedUser");
			} else {
				$bid = new Bid($this, $price, $this->context->getUser(), $this->context->getNow());
				$this->bids[] = $bid;
				$this->context->getHistoryProvider()->saveHistory(HistoryEvent::BID_ADDED);	
			}
		}
	}
	
	/**
	 * @throws InvalidObjectTypeException
	 * @throws InvalidUserException
	 */
	public function buyNow()
	{
		if ($this->type != AuctionType::BUY_NOW || $this->status != AuctionStatus::ACTIVE) {
			throw new InvalidObjectTypeException("Wrong type or status");
		} else if ($this->owner != $this->context->getUser()) {
			throw new InvalidUserException("Not an owner");
		}
		
		$this->bids[] = new Bid($this, $this->buy_now_price, $this->context->getUser(), $this->context->getNow());
		
		$this->context->getHistoryProvider()->saveHistory(HistoryEvent::BOUGHT_NOW);
		
		$this->finish();
	}

	/**
	 * @throws InvalidObjectStatusException
	 * @throws OperationNotAllowedException
	 */
	public function finish()
	{
		if ($this->status != AuctionStatus::ACTIVE) {
			throw new InvalidObjectStatusException("Auction has to be Active");
		}
		
		if ($this->type == AuctionType::BID && $this->endTime->getTimestamp() < $this->context->getNow()->getTimestamp()) {
			throw new OperationNotAllowedException("Auction has to be after its finish time");
		}
		
		if (count($this->bids) != 0) {
			$this->context->getMailProvider()->sendMail(MailTemplate::YOU_BOUGHT_AUCTION);
			$this->context->getHistoryProvider()->saveHistory(HistoryEvent::AUCTION_FINISHED);
			$this->context->getMailProvider()->sendMail(MailTemplate::YOUR_AUCTION_IS_SOLD);
		} else {
			$mailProvider = $this->context->getMailProvider();
			$mailProvider->sendMail(MailTemplate::YOUR_AUCTION_WAS_NOT_SOLD);
		}
	
		$this->status = AuctionStatus::FINISHED;
	}
	
	/**
	 * @param int $price
	 * @param HistoryEvent $historyEvent
	 * @throws InvalidObjectStatusException
	 * @throws InvalidUserException
	 */
	private function addBid($price, $historyEvent)
	{
		if ($this->status != AuctionStatus::ACTIVE) {
			throw new InvalidObjectStatusException("Auction has to be Active");
		} else {
			if ($this->owner != $this->context->getUser()) {
				throw new InvalidUserException("Owner is not loggedUser");;
			}
		}
		
		$bid = new Bid($this, $price, $this->context->getUser(), $this->context->getNow());
		$this->bids[] = $bid;
		
		$this->context->getHistoryProvider()->saveHistory($historyEvent);
	}
	
	/**
	 * @param int $price
	 * @param HistoryEvent $historyEvent
	 */
	private function addNewBidToBids($price, $historyEvent) {
		$bid = new Bid($this, $price, $this->context->getUser(), $this->context->getNow());
		$this->bids[] = $bid;
		
		$this->context->getHistoryProvider()->saveHistory($historyEvent);
	}
	
	public function setName($name) {
		$this->name = $name;
	}
	
	public function getName() {
		return $this->name;
	}
	
	public function setStartTime(DateTime $startTime) {
		$this->startTime = $startTime;
	}
	
	public function getStartTime() {
		return $this->startTime;
	}
	
	public function setEndTime(DateTime $endTime) {
		$this->endTime = $endTime;
	}
	
	public function getEndTime() {
		return $this->endTime;
	}
	
	public function setActivateDate(DateTime $activateDate) {
		$this->activateDate = $activateDate;
	}
	
	public function getActivateDate() {
		return $this->activateDate;
	}
	
	public function setStatus($status) {
		$this->status = $status;
	}
	
	public function getStatus() {
		return $this->status;
	}
	
	public function setType($type) {
		$this->type = $type;
	}
	
	public function getType() {
		return $this->type;
	}
	
	public function setBuyNowPrice($buyNowPrice) {
		$this->buy_now_price = $buyNowPrice;
	}
	
	public function getBuyNowPrice() {
		return $this->buy_now_price;
	}
	
	public function setOwner($owner) {
		$this->owner = $owner;
	}
	
	public function getOwner() {
		return $this->owner;
	}
	
	public function setBids(array $bids) {
		$this->bids = $bids;
	}
	
	public function getBids() {
		return $this->bids;
	}

	public function __toString() {
		return "auction with name: " . $this->name;
	}
}
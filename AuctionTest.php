<?php
include_once 'Stubs.php';
include_once 'Auction.php';

class AuctionTest extends PHPUnit_Framework_TestCase {
	
	/**
	 * @var Repository
	 */
	private $repo;
	
	protected function setUp() {
        date_default_timezone_set('UTC');
		$this->repo = Repository::getInstance();
		$this->repo->beginTransaction();
	}
	
	protected function tearDown() {
		$this->repo->rollback();
	}
	
	/** @test */
	public function test1() {
		$user = new User("example.user");
		$this->repo->persist($user);
		
		$context = new RequestContext($user);
		
		$startTime = new DateTime('NOW');
		$startTime->modify('+1 day');
		$endTime = new DateTime('NOW');
		$endTime->modify('+7 days');
		
		$auction = new Auction($context, "Karma dla kota", $startTime, $endTime, AuctionType::BUY_NOW, 0, 1000);
		$this->repo->persist($auction);
		
		$this->assertEquals(0, count($auction->getBids()));
		$this->assertEquals("Karma dla kota", $auction->getName());
		$this->assertEquals($startTime, $auction->getStartTime());
		$this->assertEquals($endTime, $auction->getEndTime());
		$this->assertEquals(AuctionType::BUY_NOW, $auction->getType());
		$this->assertEquals(AuctionStatus::NEW_, $auction->getStatus());
		$this->assertEquals(1000, $auction->getBuyNowPrice());
		$this->assertEquals($user, $auction->getOwner());
	}
	
	/** @test */
	public function test2() {
		$user = new User("example.user");
		$this->repo->persist($user);
		
		$context = new RequestContext($user);
		
		$startTime = new DateTime('NOW');
		$startTime->modify('+1 day');
		$endTime = new DateTime('NOW');
		$endTime->modify('+7 days');
		
		$auction = new Auction($context, null, $startTime, $endTime, AuctionType::BID, 1000, 1000);
		$this->repo->persist($auction);
		
		$now = new DateTime('NOW');
		$now->modify('+1 day');
		$now->modify('+1 second');
		
		$context->setNow($now);
		
		$auction->activate();
		
		$this->assertNotNull($auction->getActivateDate());
		$this->assertEquals(AuctionStatus::ACTIVE, $auction->getStatus());
	}
}
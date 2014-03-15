<?php
abstract class HistoryEvent {
	const BID_ADDED = 1;
	const BOUGHT_NOW = 2;
	const AUCTION_CREATED = 3;
	const AUCTION_ACTIVATED = 4;
	const AUCTION_FINISHED = 5;
	const AUCTION_DATE_CHANGED = 6;
}
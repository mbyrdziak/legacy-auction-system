<?php
interface HistoryProvider {
	
	/**
	 * @param unknown $oldValue
	 * @param unknown $newValue
	 */
	public function collect($oldValue, $newValue);
	
	/**
	 * @param HistoryEvent $event
	 */
	public function saveHistory($event);
}
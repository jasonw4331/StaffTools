<?php
declare(strict_types=1);
namespace jasonwynn10\StaffTools\event;

use jasonwynn10\StaffTools\PlayerSession;
use pocketmine\event\Event;

class SessionInitializeEvent extends Event {
	/** @var PlayerSession $session */
	private $session;

	public function __construct(PlayerSession $session){
		$this->session = $session;
	}

	/**
	 * @return PlayerSession
	 */
	public function getSession(): PlayerSession{
		return $this->session;
	}
}
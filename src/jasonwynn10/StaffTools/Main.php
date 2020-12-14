<?php
declare(strict_types=1);
namespace jasonwynn10\StaffTools;

use pocketmine\plugin\PluginBase;

class Main extends PluginBase {
	/** @var PlayerSession[] $sessions */
	private static $sessions = [];

	public function onEnable() {
		new EventListener($this);
	}

	public static function addSession(PlayerSession $session) : void {
		self::$sessions[$session->getUsername()] = $session;
	}

	public static function getSession(string $username) : ?PlayerSession {
		return self::$sessions[$username] ?? null;
	}
}
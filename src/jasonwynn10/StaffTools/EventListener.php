<?php
declare(strict_types=1);
namespace jasonwynn10\StaffTools;

use pocketmine\event\Listener;
use pocketmine\event\player\PlayerDataSaveEvent;
use pocketmine\event\player\PlayerJoinEvent;

class EventListener implements Listener {

	public function __construct(Main $main) {
		$main->getServer()->getPluginManager()->registerEvents($this, $main);
	}

	public function onPlayerJoin(PlayerJoinEvent $event) : void {
		$player = $event->getPlayer();
		Main::addSession(new PlayerSession($player->getName(), PlayerSession::NORMAL, null));
	}

	public function onPlayerSaveEvent(PlayerDataSaveEvent $event) : void {
		$player = $event->getPlayer();
		$session = Main::getSession($player->getName());
		if($session === null or $session->getMode() !== PlayerSession::NORMAL) {
			$event->setCancelled();
		}
	}
}
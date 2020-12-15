<?php
declare(strict_types=1);
namespace jasonwynn10\StaffTools;

use pocketmine\event\Listener;
use pocketmine\event\player\PlayerDataSaveEvent;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerQuitEvent;

class EventListener implements Listener {

	/** @var Main $main */
	protected $main;

	public function __construct(Main $main) {
		$main->getServer()->getPluginManager()->registerEvents($this, $main);
		$this->main = $main;
	}

	public function onPlayerJoin(PlayerJoinEvent $event) : void {
		$player = $event->getPlayer();
		Main::addSession(new PlayerSession($player->getName(), PlayerSession::NORMAL, $player->getGamemode(), $player->namedtag));
	}

	public function onPlayerSaveEvent(PlayerDataSaveEvent $event) : void {
		$player = $event->getPlayer();
		$session = Main::getSession($player->getName());
		if($session === null or $session->getMode() !== PlayerSession::NORMAL) {
			$event->setCancelled();
		}
	}

	public function onPlayerQuit(PlayerQuitEvent $event) : void {
		$player = $event->getPlayer();
		$this->main->endPermissionSession($player);
		$session = Main::getSession($player->getName());
		if($session !== null) {
			$session->setMode(PlayerSession::NORMAL);
		}
	}
}
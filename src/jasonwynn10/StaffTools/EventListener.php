<?php
declare(strict_types=1);
namespace jasonwynn10\StaffTools;

use jasonwynn10\StaffTools\event\SessionInitializeEvent;
use pocketmine\entity\Human;
use pocketmine\event\Cancellable;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\entity\EntityTeleportEvent;
use pocketmine\event\inventory\InventoryPickupArrowEvent;
use pocketmine\event\inventory\InventoryPickupItemEvent;
use pocketmine\event\inventory\InventoryTransactionEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerDataSaveEvent;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerMoveEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\event\server\DataPacketReceiveEvent;
use pocketmine\inventory\PlayerInventory;
use pocketmine\network\mcpe\protocol\LevelSoundEventPacket;
use pocketmine\Player;
use pocketmine\Server;

class EventListener implements Listener {

	/** @var Main $main */
	protected $main;

	public function __construct(Main $main) {
		$main->getServer()->getPluginManager()->registerEvents($this, $main);
		$this->main = $main;
	}

	public function onPlayerJoin(PlayerJoinEvent $event) : void {
		$player = $event->getPlayer();
		Main::addSession(new PlayerSession($player));
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
			if($session->isVanished())
				$event->setQuitMessage("");
			$session->setVanished(false);
		}
	}

	/**
	 * @param SessionInitializeEvent $ev
	 *
	 * @priority HIGHEST
	 */
	public function onSessionize(SessionInitializeEvent $ev) : void {
		$s = $ev->getSession();
		if($s->isVanished()){
			return;
		}
		$p = $s->getPlayer();
		if($p === null)
			return;
		foreach(Server::getInstance()->getOnlinePlayers() as $player) {
			$session = Main::getSession($player->getName());
			if($session !== null and $session->getMode() === PlayerSession::NORMAL)
				$p->hidePlayer($player);
		}
	}

	public function onDataPacketReceive(DataPacketReceiveEvent $ev) : void {
		$pk = $ev->getPacket();
		if($pk instanceof LevelSoundEventPacket) {
			$session = Main::getSession(($p = $ev->getPlayer())->getName());
			if($session instanceof PlayerSession && $session->isVanished()){
				$ev->setCancelled();
			}
		}
	}

	public function onInventoryTransaction(InventoryTransactionEvent $ev) : void {
		$this->cancelOnHotBar($ev, $ev->getTransaction()->getSource());
	}

	public function onPickupItem(InventoryPickupItemEvent $ev) : void {
		$inv = $ev->getInventory();
		if($inv instanceof PlayerInventory) {
			$this->cancelOnHotBar($ev, $inv->getHolder());
		}
	}

	public function onPickupArrow(InventoryPickupArrowEvent $ev) : void {
		$inv = $ev->getInventory();
		if($inv instanceof PlayerInventory) {
			$this->cancelOnHotBar($ev, $inv->getHolder());
		}
	}

	private function cancelOnHotBar(Cancellable &$ev, Human $player) : void {
		$ses = Main::getSession($player->getName());
		if($ses !== null && $ses->getHotbar() !== null){
			$ev->setCancelled();
		}
	}

	public function onInteract(PlayerInteractEvent $ev) : void {
		$p = $ev->getPlayer();
		if(($hb = Main::getSession($p->getName())->getHotbar()) !== null){
			if(!$ev->getItem()->isNull()){
				$ev->setCancelled();
			}
			$hb->handleBlockInteract($p, $p->getInventory()->getHeldItemIndex(), $ev->getBlock(), $ev->getAction(), $ev->getTouchVector(), $ev->getFace());
		}
	}

	public function onMove(PlayerMoveEvent $ev) : void {
		if(
			$ev->getFrom()->distance($ev->getTo()) > 0.001 &&
			Main::getSession($ev->getPlayer()->getName())->isFrozen()
		){
			$ev->setCancelled();
		}
	}

	/**
	 * @param EntityDamageEvent $ev
	 * @priority LOWEST
	 * @ignoreCancelled true
	 */
	public function onDamage(EntityDamageEvent $ev) : void {
		$ent = $ev->getEntity();
		if($ent instanceof Player){
			$session = Main::getSession($ent->getName());
			if($session instanceof PlayerSession && $session->isGodMode()){
				$ev->setCancelled();
				return;
			}

			if($ev instanceof EntityDamageByEntityEvent){
				$d = $ev->getDamager();
				if($d instanceof Player){
					if(Main::getSession($d->getName())->isFrozen()){
						$ev->setCancelled();
						return;
					}
				}
			}
		}
	}

	/**
	 * @param EntityTeleportEvent $ev
	 *
	 * @priority HIGHEST
	 * @ignoreCancelled true
	 */
	public function onTeleport(EntityTeleportEvent $ev) : void {
		$player = $ev->getEntity();
		if($player instanceof Player){
			$session = Main::getSession($player->getName());
			if($session instanceof PlayerSession){
				if($session->isFrozen()){
					$ev->setCancelled();
					return;
				}
				$session->setLastPreTPLocation($ev->getFrom());
			}
		}
	}
}
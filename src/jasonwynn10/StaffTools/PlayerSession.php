<?php
declare(strict_types=1);
namespace jasonwynn10\StaffTools;

use jasonwynn10\StaffTools\event\SessionInitializeEvent;
use pocketmine\item\Item;
use pocketmine\level\Position;
use pocketmine\Player;
use pocketmine\Server;

final class PlayerSession {
	public CONST NORMAL = 0;
	public CONST CHEAT = 1;
	public CONST STAFF = 2;

	/** @var string */
	private $username;
	/** @var int */
	private $mode = self::NORMAL;
	/** @var mixed[][] */
	private $preHotbarInv;
	/** @var string[] */
	private $displayNames = [];
	/** @var string */
	private $prefix = "";
	/** @var bool */
	private $vanished = false;
	/** @var Hotbar|null */
	private $hotbar = null;
	/** @var bool */
	private $godMode = false;
	/** @var bool */
	private $frozen = false;
	/** @var Position|null */
	private $lastPreTPLocation = null;
	/** @var bool */
	private $hiddenFromPlayerList = false;

	public function __construct(Player $player) {
		$this->username = $player->getName();
		$this->setHotbar(null);
		(new SessionInitializeEvent($this))->call();
	}

	public function getUsername() : string {
		return $this->username;
	}

	public function getPlayer(): ?Player {
		return Server::getInstance()->getPlayerExact($this->username);
	}

	public function setDisplayName(int $priority, string $displayName): void {
		$this->displayNames[$priority] = $displayName;
		$this->applyDisplayName();
	}

	public function resetDisplayName(int $priority): void {
		unset($this->displayNames[$priority]);
	}

	public function resetDisplayNames(): void {
		$this->displayNames = [];
	}

	public function getDisplayName(int $priority): ?string {
		return $this->displayNames[$priority] ?? null;
	}

	public function applyDisplayName(): void {
		$nm = $this->username;
		if(count($this->displayNames) > 0) {
			$nm = $this->displayNames[max(array_keys($this->displayNames))];
		}
		$this->getPlayer()->setDisplayName($this->prefix . $nm);
	}

	public function isVanished(): bool {
		return $this->vanished;
	}

	public function setVanished(bool $vanished): void {
		$sv = Server::getInstance();
		$pl = $this->getPlayer();
		if($vanished) {
			foreach($sv->getOnlinePlayers() as $p) {
				$ps = Main::getSession($p->getName());
				if($ps instanceof PlayerSession && $ps->isVanished()){
					continue;
				}
				$p->hidePlayer($pl);
			}
		} else {
			foreach($sv->getOnlinePlayers() as $p) {
				$p->showPlayer($pl);
			}
		}
		$this->vanished = $vanished;
	}

	public function getPrefix(): string {
		return $this->prefix;
	}

	public function setPrefix(string $prefix): void {
		$this->prefix = $prefix;
		$this->applyDisplayName();
	}

	public function getHotbar(): ?Hotbar {
		return $this->hotbar;
	}

	public function setHotbar(?Hotbar $hotbar): void {
		$pl = $this->getPlayer();
		$inv = $pl->getInventory();
		$ainv = $pl->getArmorInventory();
		if($hotbar === null and isset($this->preHotbarInv)) {
			$dat = $this->preHotbarInv;
			$inv->clearAll();
			$ainv->clearAll();
			foreach($dat as $slot => $item) {
				if($slot >= 1000){
					$ainv->setItem($slot - 1000, Item::jsonDeserialize($item));
				} else {
					$inv->setItem($slot, Item::jsonDeserialize($item));
				}
			}
			unset($this->preHotbarInv);
		} elseif($hotbar !== null and !isset($this->hotbar)) {
			$d = [];
			foreach($inv->getContents() as $slot => $item) {
				$d[$slot] = $item->jsonSerialize();
			}
			foreach($ainv->getContents() as $slot => $item) {
				$d[$slot + 1000] = $item->jsonSerialize();
			}
			$inv->clearAll();
			$ainv->clearAll();
			$this->preHotbarInv = $d;
		}
		$this->hotbar = $hotbar;
	}

	public function isFrozen(): bool {
		return $this->frozen;
	}

	public function setFrozen(bool $frozen): void {
		$this->getPlayer()->setImmobile($frozen);
		$this->frozen = $frozen;
	}

	public function isGodMode(): bool {
		return $this->godMode;
	}

	/**
	 * @param bool $godMode
	 * @param bool $save
	 */
	public function setGodMode(bool $godMode): void {
		$this->godMode = $godMode;
	}

	public function getLastPreTPLocation(): ?Position {
		return $this->lastPreTPLocation;
	}

	public function setLastPreTPLocation(Position $pos): void {
		$this->lastPreTPLocation = $pos;
	}

	public function isHiddenFromPlayerList(): bool {
		return $this->hiddenFromPlayerList;
	}

	public function setHiddenFromPlayerList(bool $hiddenFromPlayerList): void {
		if($this->hiddenFromPlayerList !== $hiddenFromPlayerList){
			$this->hiddenFromPlayerList = $hiddenFromPlayerList;
			$sv = Server::getInstance();
			if($hiddenFromPlayerList){
				$sv->removePlayerListData($this->getPlayer()->getUniqueId());
			} else {
				foreach($sv->getOnlinePlayers() as $player){
					$sv->sendFullPlayerListData($player);
				}
			}
		}
	}

	/**
	 * @return int
	 */
	public function getMode() : int {
		return $this->mode;
	}

	/**
	 * @param int $mode
	 */
	public function setMode(int $mode) : void {
		$this->mode = $mode;
	}
}
<?php
declare(strict_types=1);
namespace jasonwynn10\StaffTools;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\permission\PermissionAttachment;
use pocketmine\Player;
use pocketmine\plugin\PluginBase;

class Main extends PluginBase {
	/** @var PlayerSession[] $sessions */
	private static $sessions = [];
	/** @var PermissionAttachment[] $att */
	private $att = [];
	/** @var bool[][] $data */
	private $data = [];

	public function onEnable() {
		new EventListener($this);
	}

	public static function addSession(PlayerSession $session) : void {
		self::$sessions[$session->getUsername()] = $session;
	}

	public static function getSession(string $username) : ?PlayerSession {
		return self::$sessions[$username] ?? null;
	}

	public function onCommand(CommandSender $sender, Command $command, string $label, array $args) : bool {
		if(!$sender instanceof Player) {
			$sender->sendMessage("Please run this command in-game.");
			return true;
		}
		if(!isset($args[0]) or ((bool)$args[0]) === false) { // staff mode
			$this->toggleStaffMode($sender);
			return true;
		}
		// cheat mode
		$this->toggleCheatMode($sender);
		return true;
	}

	public function toggleStaffMode(Player $player) : bool {
		$session = Main::getSession($player->getName());
		if($session === null)
			return false;
		if($session->getMode() === PlayerSession::STAFF) { // already in staff mode
			// TODO: fix display tag
			// TODO: take tool set from hotbar
			$this->endPermissionSession($player);
			$this->getServer()->updatePlayerListData($player->getUniqueId(), $player->getId(), $player->getDisplayName(), $player->getSkin(), $player->getXuid());
			foreach($this->getServer()->getOnlinePlayers() as $onlinePlayer) {
				$onlinePlayer->showPlayer($player);
			}
			$player->setGamemode($session->getGamemode());
			$session->setMode(PlayerSession::NORMAL);
			$player->namedtag = $session->getSaveData();
		}else{
			$session->setGamemode($player->getGamemode());
			$session->setSaveData($player->namedtag);
			$player->save();
			$session->setMode(PlayerSession::STAFF);
			$player->getInventory()->clearAll(true);
			$player->getArmorInventory()->clearAll(true);
			$player->setGamemode(Player::CREATIVE);
			foreach($this->getServer()->getOnlinePlayers() as $onlinePlayer) {
				$psession = Main::getSession($onlinePlayer->getName());
				if($psession !== null and $psession->getMode() === PlayerSession::NORMAL)
					$onlinePlayer->hidePlayer($player);
			}
			$this->getServer()->removePlayerListData($player->getUniqueId());
			$this->startPermissionSession($player, PlayerSession::STAFF);
			// TODO: give tool set in hotbar
			// TODO: change display tag
		}
		return true;
	}

	public function toggleCheatMode(Player $player) : bool {
		$session = Main::getSession($player->getName());
		if($session === null)
			return false;
		if($session->getMode() === PlayerSession::CHEAT) { // already in cheat mode
			$this->endPermissionSession($player);
			$this->getServer()->updatePlayerListData($player->getUniqueId(), $player->getId(), $player->getDisplayName(), $player->getSkin(), $player->getXuid());
			foreach($this->getServer()->getOnlinePlayers() as $onlinePlayer) {
				$onlinePlayer->showPlayer($player);
			}
			$player->setAllowFlight(false);
			$session->setMode(PlayerSession::NORMAL);
			//$sender->namedtag = $session->getSaveData();
		}else{
			$session->setGamemode($player->getGamemode());
			$player->save();
			$session->setMode(PlayerSession::CHEAT);
			$player->setAllowFlight(true);
			foreach($this->getServer()->getOnlinePlayers() as $onlinePlayer) {
				$psession = Main::getSession($onlinePlayer->getName());
				if($psession !== null and $psession->getMode() === PlayerSession::NORMAL)
					$onlinePlayer->hidePlayer($player);
			}
			$this->getServer()->removePlayerListData($player->getUniqueId());
			$this->startPermissionSession($player, PlayerSession::CHEAT);
		}
		return true;
	}

	public function startPermissionSession(Player $player, int $mode) : void {
		if(!isset($this->data[$mode]))
			throw new \UnexpectedValueException("Mode number $mode is not valid");
		$att = $this->att($player);
		$att->setPermissions($this->data[$mode]);
	}

	private function att(Player $player) : PermissionAttachment {
		if(!isset($this->att[$player->getId()])){
			return $this->att[$player->getId()] = $player->addAttachment($this);
		}
		return $this->att[$player->getId()];
	}

	public function endPermissionSession(Player $player) : void {
		if(isset($this->att[$player->getId()])){
			$this->att[$player->getId()]->clearPermissions();
			$player->removeAttachment($this->att[$player->getId()]);
			unset($this->att[$player->getId()]);
		}
	}
}
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
			$session = Main::getSession($sender->getName());
			if($session === null)
				return false;
			if($session->getMode() === PlayerSession::STAFF) { // already in staff mode
				// TODO: fix nametag
				// TODO: take tool set from hotbar
				// TODO: undo permission level changes
				$this->getServer()->updatePlayerListData($sender->getUniqueId(), $sender->getId(), $sender->getDisplayName(), $sender->getSkin(), $sender->getXuid());
				foreach($this->getServer()->getOnlinePlayers() as $player) {
					$player->showPlayer($sender);
				}
				$sender->setGamemode(Player::SURVIVAL);
				$session->setMode(PlayerSession::NORMAL);
				$sender->namedtag = $session->getSaveData();
			}else{
				$session->setSaveData($sender->namedtag);
				$sender->save();
				$session->setMode(PlayerSession::STAFF);
				$sender->getInventory()->clearAll(true);
				$sender->getArmorInventory()->clearAll(true);
				$sender->setGamemode(Player::CREATIVE);
				foreach($this->getServer()->getOnlinePlayers() as $player) {
					$psession = Main::getSession($player->getName());
					if($psession !== null and $psession->getMode() === PlayerSession::NORMAL)
						$player->hidePlayer($sender);
				}
				$this->getServer()->removePlayerListData($sender->getUniqueId());
				// TODO: permission level changes
				// TODO: give tool set in hotbar
				// TODO: change nametag
			}
		}
		// cheat mode
		$session = Main::getSession($sender->getName());
		if($session === null)
			return false;
		if($session->getMode() === PlayerSession::CHEAT) { // already in cheat mode
			// TODO: fix nametag
			// TODO: take tool set from hotbar
			// TODO: undo permission level changes
			$this->getServer()->updatePlayerListData($sender->getUniqueId(), $sender->getId(), $sender->getDisplayName(), $sender->getSkin(), $sender->getXuid());
			foreach($this->getServer()->getOnlinePlayers() as $player) {
				$player->showPlayer($sender);
			}
			$sender->setGamemode(Player::SURVIVAL);
			$session->setMode(PlayerSession::NORMAL);
			$sender->namedtag = $session->getSaveData();
		}else{
			$sender->save();
			$session->setMode(PlayerSession::CHEAT);
			//$sender->setGamemode(Player::SURVIVAL);
			foreach($this->getServer()->getOnlinePlayers() as $player) {
				$psession = Main::getSession($player->getName());
				if($psession !== null and $psession->getMode() === PlayerSession::NORMAL)
					$player->hidePlayer($sender);
			}
			$this->getServer()->removePlayerListData($sender->getUniqueId());
			// TODO: permission level changes
			// TODO: give tool set in hotbar
			// TODO: change nametag
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
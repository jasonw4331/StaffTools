<?php
declare(strict_types=1);
namespace jasonwynn10\StaffTools;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\Player;
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

	public function onCommand(CommandSender $sender, Command $command, string $label, array $args) : bool {
		if(!$sender instanceof Player) {
			$sender->sendMessage("Please run this command in-game.");
			return true;
		}
		if(!isset($args[0]) or ((bool)$args[0]) === false) { // staff mode
			$session = Main::getSession($sender->getName());
			if($session === null)
				return false;
			if($session->getMode() === PlayerSession::STAFF) {
				// TODO: fix nametag
				// TODO: take tool set in hotbar
				// TODO: undo permission level changes
				$this->getServer()->updatePlayerListData($sender->getUniqueId(), $sender->getId(), $sender->getDisplayName(), $sender->getSkin(), $sender->getXuid());
				foreach($this->getServer()->getOnlinePlayers() as $player) {
					$player->showPlayer($sender);
				}
				$sender->setGamemode(Player::SURVIVAL);
				$session->setMode(PlayerSession::NORMAL);
				$sender->namedtag = $session->getSaveData();
			}else{ // already in staff mode
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
		if($session->getMode() === PlayerSession::STAFF) {
			// TODO: fix nametag
			// TODO: take tool set in hotbar
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
}
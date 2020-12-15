<?php
declare(strict_types=1);
namespace jasonwynn10\StaffTools;

use pocketmine\nbt\tag\CompoundTag;
use pocketmine\Player;

final class PlayerSession {
	public CONST NORMAL = 0;
	public CONST CHEAT = 1;
	public CONST STAFF = 2;

	/** @var string $username */
	private $username;
	/** @var int $mode */
	private $mode;
	/** @var CompoundTag|null $saveData */
	private $saveData;
	/** @var int $gamemode */
	private $gamemode;

	public function __construct(string $username, int $mode = self::NORMAL, int $gamemode = Player::SURVIVAL, ?CompoundTag $saveData = null) {
		$this->username = $username;
		$this->mode = $mode;
		$this->saveData = $saveData;
		$this->gamemode = $gamemode;
	}

	/**
	 * @return string
	 */
	public function getUsername() : string {
		return $this->username;
	}

	/**
	 * @return int
	 */
	public function getMode() : int {
		return $this->mode;
	}

	/**
	 * @return CompoundTag|null
	 */
	public function getSaveData() : ?CompoundTag {
		return $this->saveData;
	}

	/**
	 * @return int
	 */
	public function getGamemode() : int {
		return $this->gamemode;
	}

	/**
	 * @param int $mode
	 *
	 * @return PlayerSession
	 */
	public function setMode(int $mode) : PlayerSession {
		$this->mode = $mode;
		return $this;
	}

	/**
	 * @param CompoundTag|null $saveData
	 *
	 * @return PlayerSession
	 */
	public function setSaveData(?CompoundTag $saveData) : PlayerSession {
		$this->saveData = $saveData;
		return $this;
	}

	/**
	 * @param int $gamemode
	 *
	 * @return PlayerSession
	 */
	public function setGamemode(int $gamemode) : PlayerSession {
		$this->gamemode = $gamemode;
		return $this;
	}


}
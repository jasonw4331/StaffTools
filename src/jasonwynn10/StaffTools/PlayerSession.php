<?php
declare(strict_types=1);
namespace jasonwynn10\StaffTools;

use pocketmine\nbt\tag\CompoundTag;

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

	public function __construct(string $username, int $mode = self::NORMAL, ?CompoundTag $saveData = null) {
		$this->username = $username;
		$this->mode = $mode;
		$this->saveData = $saveData;
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
	 * @param int $mode
	 */
	public function setMode(int $mode) : void {
		$this->mode = $mode;
	}

	/**
	 * @return CompoundTag|null
	 */
	public function getSaveData() : ?CompoundTag {
		return $this->saveData;
	}

	/**
	 * @param CompoundTag|null $saveData
	 */
	public function setSaveData(?CompoundTag $saveData) : void {
		$this->saveData = $saveData;
	}

}
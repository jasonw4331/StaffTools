<?php
declare(strict_types=1);
namespace jasonwynn10\StaffTools;

use pocketmine\block\Block;
use pocketmine\entity\Entity;
use pocketmine\item\Item;
use pocketmine\math\Vector3;
use pocketmine\Player;
use pocketmine\utils\Utils;

class Hotbar {
	/** @var array[] */
	protected $items = [];

	public function applyTo(PlayerSession $session): void {
		$session->setHotbar($this);
		$inv = $session->getPlayer()->getInventory();
		foreach($this->items as $slot => $item) {
			$inv->setItem($slot, $item["item"]);
		}
	}

	public function removeFrom(PlayerSession $session): void {
		$session->setHotbar(null);
	}

	public function setItem(int $index, Item $item, ?callable $blockCallback = null, ?callable $entityCallback = null): void {
		if($index >= 0 && $index <= 8) {
			if($blockCallback !== null) {
				Utils::validateCallableSignature(function (Player $player, Block $block, int $action, Vector3 $clickVector, int $face): void {
				}, $blockCallback);
			}
			if($entityCallback !== null) {
				Utils::validateCallableSignature(function (Player $player, Entity $entity): void {
				}, $entityCallback);
			}
			$this->items[$index] = [
				"item" => clone $item,
				"blockCallable" => $blockCallback ?? null,
				"entityCallback" => $entityCallback ?? null
			];
			return;
		}
		throw new \Exception("Index out of range, only 0-8 is accepted");
	}

	public function handleBlockInteract(Player $player, int $index, Block $block, int $action, Vector3 $clickVector, int $face): void {
		if(isset($this->items[$index])) {
			if($this->items[$index]["blockCallable"] !== null) {
				($this->items[$index]["blockCallable"])($player, $block, $action, $clickVector, $face);
			}
		}
	}

	public function handleEntityInteract(Player $player, int $index, Entity $entity): void {
		if(isset($this->items[$index])) {
			if($this->items[$index]["entityCallback"] !== null) {
				($this->items[$index]["entityCallback"])($player, $entity);
			}
		}
	}
}
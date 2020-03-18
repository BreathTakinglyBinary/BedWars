<?php
declare(strict_types=1);

namespace xenialdan\BedWars\ui\shop;


use BreathTakinglyBinary\libDynamicForms\Form;
use BreathTakinglyBinary\libDynamicForms\SimpleForm;
use pocketmine\item\Item;
use pocketmine\item\ItemFactory;
use pocketmine\Player;
use pocketmine\utils\TextFormat;
use xenialdan\BedWars\Loader;
use BreathTakinglyBinary\minigames\API;
use BreathTakinglyBinary\minigames\Arena;

class WeaponsMenu extends SimpleForm{

    public function __construct(?Form $previousForm = null){
        parent::__construct(TextFormat::DARK_GREEN . "Weapons", $previousForm);
        $this->addButton(TextFormat::DARK_BLUE . "Stone Sword \n" . TextFormat::DARK_RED . "5 Bronze", "stone");
        $this->addButton(TextFormat::DARK_BLUE . "Iron Sword \n" . TextFormat::DARK_BLUE . "5 Iron", "iron");
        $this->addButton(TextFormat::DARK_BLUE . "Diamond Sword \n" . TextFormat::GOLD . "5 Gold", "diamond");
        $this->addButton(TextFormat::DARK_BLUE . "Bow\n" . TextFormat::DARK_BLUE . "2 Silver", "bow");
        $this->addButton(TextFormat::DARK_BLUE . "16x Arrows\n" . TextFormat::DARK_RED . "35 Bronze", "arrow");
    }

    /**
     * @param Player $player
     * @param        $data
     */
    public function onResponse(Player $player, $data) : void{
        $this->setContent("");
        $arena = API::getArenaOfPlayer($player);
        if(!$arena instanceof Arena or $arena->getState() !== Arena::INGAME){
            return;
        }
        switch($data){
            case "stone":
                $value = 5;
                $valueType = Loader::BRONZE;
                $item = ItemFactory::get(Item::STONE_SWORD);
                break;
            case "iron":
                $value = 5;
                $valueType = Loader::SILVER;
                $item = ItemFactory::get(Item::IRON_SWORD);
                break;
            case "diamond":
                $value = 5;
                $valueType = Loader::GOLD;
                $item = ItemFactory::get(Item::DIAMOND_SWORD);
                break;
            case "bow":
                $value = 2;
                $valueType = Loader::SILVER;
                $item = ItemFactory::get(Item::BOW);
                break;
            case "arrow":
                $value = 35;
                $valueType = Loader::BRONZE;
                $item = ItemFactory::get(Item::ARROW, 0, 16);
                break;
            default:
                return;
        }
        if(!Loader::buyItem($item, $player, $valueType, $value)){
            $this->setContent(TextFormat::RED . "Not Enough " . $valueType . "!");
        }
        $player->sendForm($this);
    }
}
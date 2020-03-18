<?php
declare(strict_types=1);

namespace xenialdan\BedWars\ui\shop;


use BreathTakinglyBinary\libDynamicForms\Form;
use BreathTakinglyBinary\libDynamicForms\SimpleForm;
use pocketmine\block\Stone;
use pocketmine\item\Item;
use pocketmine\item\ItemFactory;
use pocketmine\Player;
use pocketmine\utils\TextFormat;
use xenialdan\BedWars\Loader;
use BreathTakinglyBinary\minigames\API;
use BreathTakinglyBinary\minigames\Arena;

class BlocksMenu extends SimpleForm{

    public function __construct(?Form $previousForm = null){
        parent::__construct(TextFormat::DARK_GREEN . "Blocks", $previousForm);
        $this->addButton(TextFormat::DARK_BLUE . "16x Wool \n" . TextFormat::DARK_RED . "5 Bronze", "wool");
        $this->addButton(TextFormat::DARK_BLUE . "16x Wood \n" . TextFormat::DARK_RED . "10 Bronze", "wood");
        $this->addButton(TextFormat::DARK_BLUE . "10x Diorite\n" . TextFormat::DARK_RED . "20 Bronze", "diorite");
        $this->addButton(TextFormat::DARK_BLUE . "1x Obsidian\n" . TextFormat::GOLD . "1 Gold", "obsidian");
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
            case "wool":
                $value = 5;
                $valueType = Loader::BRONZE;
                $meta = API::getMetaByColor($arena->getTeamByPlayer($player)->getColor());
                $item = ItemFactory::get(Item::WOOL, $meta, 16);
                break;
            case "wood":
                $value = 10;
                $valueType = Loader::BRONZE;
                $item = ItemFactory::get(Item::PLANKS, 0, 16);
                break;
            case "diorite":
                $value = 20;
                $valueType = Loader::BRONZE;
                $item = ItemFactory::get(Item::STONE, Stone::DIORITE, 10);
                break;
            case "obsidian":
                $value = 1;
                $valueType = Loader::GOLD;
                $item = ItemFactory::get(Item::OBSIDIAN, 0, 1);
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
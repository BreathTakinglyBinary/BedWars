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

class ToolsMenu extends SimpleForm{

    public function __construct(?Form $previousForm = null){
        parent::__construct(TextFormat::DARK_GREEN . "Tools", $previousForm);
        $this->addButton(TextFormat::DARK_BLUE . "Iron Pickaxe \n" . TextFormat::DARK_RED . "10 " . Loader::TIER_1, "ironpick");
        $this->addButton(TextFormat::DARK_BLUE . "Diamond Pickaxe \n" . TextFormat::DARK_BLUE . "5 "  . Loader::TIER_3, "diamondpick");
        $this->addButton(TextFormat::DARK_BLUE . "Iron Axe\n" . TextFormat::DARK_RED . "10 " . Loader::TIER_1, "ironaxe");
        $this->addButton(TextFormat::DARK_BLUE . "Diamond Axe\n" . TextFormat::DARK_BLUE . "5 " . Loader::TIER_3, "diamondaxe");
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
            case "ironpick":
                $value = 10;
                $valueType = Loader::TIER_1;
                $item = ItemFactory::get(Item::IRON_PICKAXE);
                break;
            case "diamondpick":
                $value = 5;
                $valueType = Loader::TIER_3;
                $item = ItemFactory::get(Item::DIAMOND_PICKAXE);
                break;
            case "ironaxe":
                $value = 10;
                $valueType = Loader::TIER_1;
                $item = ItemFactory::get(Item::IRON_AXE);
                break;
            case "diamondaxe":
                $value = 5;
                $valueType = Loader::TIER_3;
                $item = ItemFactory::get(Item::DIAMOND_AXE);
                break;
            default:
                return;
        }
        if(!Loader::buyItem($item, $player, $valueType, $value)){
            $this->setContent(TextFormat::RED . "Not Enough " . $valueType . "!");
            $player->sendForm($this);
        }else{
            $form = $this->getPreviousForm();
            if($form instanceof Form){
                $player->sendForm($form);
            }
        }
    }

}
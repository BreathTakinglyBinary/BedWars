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
use xenialdan\gameapi\API;
use xenialdan\gameapi\Arena;

class ToolsMenu extends SimpleForm{

    public function __construct(?Form $previousForm = null){
        parent::__construct(TextFormat::DARK_GREEN . "Tools", $previousForm);
        $this->addButton(TextFormat::DARK_BLUE . "Stone Pickaxe \n" . TextFormat::DARK_RED . "5 Bronze", "stonepick");
        $this->addButton(TextFormat::DARK_BLUE . "Iron Pickaxe \n" . TextFormat::DARK_BLUE . "5 Silver", "ironpick");
        $this->addButton(TextFormat::DARK_BLUE . "Stone Axe\n" . TextFormat::DARK_RED . "5 bronze", "stoneaxe");
        $this->addButton(TextFormat::DARK_BLUE . "Iron Axe\n" . TextFormat::DARK_BLUE . "5 silver", "ironaxe");
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
            case "stonepick":
                $value = 5;
                $valueType = Loader::BRONZE;
                $item = ItemFactory::get(Item::IRON_SWORD);
                break;
            case "ironpick":
                $value = 5;
                $valueType = Loader::SILVER;
                $item = ItemFactory::get(Item::DIAMOND_SWORD);
                break;
            case "stoneaxe":
                $value = 5;
                $valueType = Loader::BRONZE;
                $item = ItemFactory::get(Item::BOW);
                break;
            case "ironaxe":
                $value = 5;
                $valueType = Loader::SILVER;
                $item = ItemFactory::get(Item::ARROW, 0, 16);
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
                $player->sendForm($this->getPreviousForm());
            }
        }
    }

}
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

class ArmorMenu extends SimpleForm{

    public function __construct(?Form $previousForm = null){
        parent::__construct(TextFormat::GOLD . "Armor", $previousForm);
        $this->addButton(TextFormat::DARK_BLUE . "Chain \n" . TextFormat::DARK_RED . "30 Bronze", "chain");
        $this->addButton(TextFormat::DARK_BLUE . "Iron \n" . TextFormat::DARK_BLUE . "30 Silver", "iron");
        $this->addButton(TextFormat::DARK_BLUE . "Diamond\n" . TextFormat::GOLD . "20 GOLD", "diamond");
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
            case "chain":
                $value = 30;
                $valueType = Loader::BRONZE;
                $items = [
                    ItemFactory::get(Item::CHAIN_HELMET),
                    ItemFactory::get(Item::CHAIN_CHESTPLATE),
                    ItemFactory::get(Item::CHAIN_LEGGINGS),
                    ItemFactory::get(Item::CHAIN_BOOTS)
                ];
                break;
            case "iron":
                $value = 30;
                $valueType = Loader::SILVER;
                $items = [
                    ItemFactory::get(Item::IRON_HELMET),
                    ItemFactory::get(Item::IRON_CHESTPLATE),
                    ItemFactory::get(Item::IRON_LEGGINGS),
                    ItemFactory::get(Item::IRON_BOOTS)
                ];
                break;
            case "diamond":
                $value = 20;
                $valueType = Loader::GOLD;
                $items = [
                    ItemFactory::get(Item::DIAMOND_HELMET),
                    ItemFactory::get(Item::DIAMOND_CHESTPLATE),
                    ItemFactory::get(Item::DIAMOND_LEGGINGS),
                    ItemFactory::get(Item::DIAMOND_BOOTS)
                ];
                break;
            default:
                return;
        }
        if(!Loader::buyItem(ItemFactory::get(0), $player, $valueType, $value)){
            $this->setContent(TextFormat::RED . "Not Enough " . $valueType . "!");
            $player->sendForm($this);
        }else{
            $oldArmor = $player->getArmorInventory()->getContents();
            foreach($oldArmor as $item){
                $player->getInventory()->addItem($item);
            }
            foreach($items as $index => $armor){
                $player->getArmorInventory()->setItem($index, $armor);
            }

            $form = $this->getPreviousForm();
            if($form instanceof Form){
                $player->sendForm($this->getPreviousForm());
            }
        }
    }
}
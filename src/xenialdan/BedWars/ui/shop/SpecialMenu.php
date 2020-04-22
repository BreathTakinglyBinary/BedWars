<?php
declare(strict_types=1);

namespace xenialdan\BedWars\ui\shop;


use BreathTakinglyBinary\libDynamicForms\Form;
use BreathTakinglyBinary\libDynamicForms\SimpleForm;
use pocketmine\item\enchantment\Enchantment;
use pocketmine\item\enchantment\EnchantmentInstance;
use pocketmine\item\Item;
use pocketmine\item\ItemFactory;
use pocketmine\Player;
use pocketmine\utils\TextFormat;
use xenialdan\BedWars\Loader;
use BreathTakinglyBinary\minigames\API;
use BreathTakinglyBinary\minigames\Arena;

class SpecialMenu extends SimpleForm{

    public function __construct(?Form $previousForm = null){
        parent::__construct(TextFormat::DARK_GREEN . "Special Items", $previousForm);
        $this->addButton(TextFormat::DARK_BLUE . "Ender Pearl\n" . TextFormat::GOLD . "5 " . Loader::TIER_3, "epearl");
        $this->addButton(TextFormat::DARK_BLUE . "10x Snowballs\n" . TextFormat::GOLD . "3 " . Loader::TIER_3, "snowballs");
        $this->addButton(TextFormat::DARK_BLUE . "Enchanted Bow\n" . TextFormat::GOLD . "20 " . Loader::TIER_3, "ebow");
        $this->addButton(TextFormat::DARK_BLUE . "Enchanted Sword\n" . TextFormat::GOLD . "20 " . Loader::TIER_3, "esword");
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
            case "epearl":
                $value = 5;
                $valueType = Loader::TIER_3;
                $item = ItemFactory::get(Item::ENDER_PEARL);
                break;
            case "snowballs":
                $value = 3;
                $valueType = Loader::TIER_3;
                $item = ItemFactory::get(Item::SNOWBALL, 0, 8);
                break;
            case "ebow":
                $value = 20;
                $valueType = Loader::TIER_3;
                $item = ItemFactory::get(Item::BOW);
                $item->addEnchantment(new EnchantmentInstance(Enchantment::getEnchantment(Enchantment::POWER), 2));
                $item->addEnchantment(new EnchantmentInstance(Enchantment::getEnchantment(Enchantment::PUNCH), 1));
                break;
            case "esword":
                $value = 20;
                $valueType = Loader::TIER_3;
                $item = ItemFactory::get(Item::DIAMOND_SWORD);
                $item->addEnchantment(new EnchantmentInstance(Enchantment::getEnchantment(Enchantment::SHARPNESS), 2));
                $item->addEnchantment(new EnchantmentInstance(Enchantment::getEnchantment(Enchantment::KNOCKBACK), 1));
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
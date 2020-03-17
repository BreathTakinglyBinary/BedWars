<?php
declare(strict_types=1);

namespace xenialdan\BedWars\ui\shop;


use BreathTakinglyBinary\libDynamicForms\Form;
use BreathTakinglyBinary\libDynamicForms\SimpleForm;
use pocketmine\Player;
use pocketmine\utils\TextFormat;
use xenialdan\BedWars\Loader;
use xenialdan\gameapi\API;
use xenialdan\gameapi\Arena;

class ShopMainMenu extends SimpleForm{

    public function __construct(?Form $previousForm = null){
        parent::__construct(TextFormat::DARK_BLUE . "Main Menu");
        $this->addButton("Blocks", "blocks");
        $this->addButton("Weapons", "weapons");
        $this->addButton("Armor", "armor");
        $this->addButton("Tools", "tools");
        $this->addButton("Special Items", "special");
    }

    /**
     * Children classes should implement this method to properly
     * deal with non-null player responses.
     *
     * @param Player $player
     * @param        $data
     */
    public function onResponse(Player $player, $data) : void{
        $arena = API::getArenaOfPlayer($player);
        if(!$arena instanceof Arena or $arena->getState() !== Arena::INGAME){
            //return;
        }
        switch($data){
            case "blocks":
                $form = new BlocksMenu($this);
                break;
            case "weapons":
                $form = new WeaponsMenu($this);
                break;
            case "armor":
                $form = new ArmorMenu($this);
                break;
            case "tools":
                $form = new ToolsMenu($this);
                break;
            case "special":
                $form = new SpecialMenu($this);
                break;
            default:
                return;
        }
        $player->sendForm($form);
    }
}
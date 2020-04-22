<?php
declare(strict_types=1);

namespace xenialdan\BedWars\listeners;


use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\entity\EntityLevelChangeEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerExhaustEvent;
use pocketmine\Player;
use pocketmine\Server;
use xenialdan\BedWars\Loader;

class LobbyListener implements Listener{


    public function levelChange(EntityLevelChangeEvent $event) : void{
        $player = $event->getEntity();
        if(!$player instanceof Player){
            return;
        }
        if($event->getTarget()->getId() !== $this->getDefaultLevelID()){
            return;
        }
        $player->getInventory()->clearAll();
        $player->getArmorInventory()->clearAll();
        $player->setFood($player->getMaxFood());
        $player->setGamemode(Player::ADVENTURE);
    }

    public function onDamage(EntityDamageEvent $event) : void{
        $player = $event->getEntity();
        if(!$player instanceof Player){
            return;
        }
        if($player->getLevel()->getId() !== $this->getDefaultLevelID()){
            return;
        }
        $event->setCancelled();
        Loader::getInstance()->getLogger()->debug("Canceled Lobby Damage");
    }

    public function onExhaust(PlayerExhaustEvent $event){
        if($event->getPlayer()->getLevel()->getId() !== $this->getDefaultLevelID()){
            return;
        }
        $event->setCancelled();
    }

    private function getDefaultLevelID() : int{
        return Server::getInstance()->getDefaultLevel()->getId();
    }

}
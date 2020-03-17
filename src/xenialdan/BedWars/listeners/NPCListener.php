<?php
declare(strict_types=1);

namespace xenialdan\BedWars\listeners;


use pocketmine\entity\Villager;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\Listener;
use pocketmine\Player;
use xenialdan\gameapi\API;
use xenialdan\gameapi\Arena;

class NPCListener implements Listener{

    public function onDamage(EntityDamageByEntityEvent $event){
        $damager = $event->getDamager();
        if(!$damager instanceof Player){
            return;
        }

        $arena = API::getArenaOfPlayer($damager);
        if(!$arena instanceof Arena or !API::isArenaOf(Loader::getInstance(), $arena->getLevel()) or $arena->getState() !== Arena::INGAME){
            return;
        }

        if(!$event->getEntity() instanceof Villager){
            return;
        }
        $event->setCancelled();
        $damager->sendForm(new ShopMainMenu());
    }
}
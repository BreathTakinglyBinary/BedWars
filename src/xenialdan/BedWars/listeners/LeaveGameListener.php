<?php

namespace xenialdan\BedWars\listeners;

use pocketmine\event\entity\EntityLevelChangeEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerDeathEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\Player;
use xenialdan\BedWars\BedwarsTeam;
use xenialdan\BedWars\Loader;
use BreathTakinglyBinary\minigames\API;

/**
 * Class LeaveGameListener
 * @package xenialdan\XBedWars
 * Listens for interacts for leaving games or teams
 */
class LeaveGameListener implements Listener{

    public function onDeath(PlayerDeathEvent $ev){
        if(API::isArenaOf(Loader::getInstance(), ($player = $ev->getPlayer())->getLevel()) && API::isPlaying($player, Loader::getInstance())){
            $team = API::getTeamOfPlayer($player);
            /** @var BedwarsTeam $team */
            if($team->isBedDestroyed()){
                /** @noinspection PhpUnhandledExceptionInspection */
                API::getArenaByLevel(Loader::getInstance(), $player->getLevel())->removePlayer($player);
            }
        }
    }

    public function onDisconnectOrKick(PlayerQuitEvent $ev){
        if(API::isArenaOf(Loader::getInstance(), $ev->getPlayer()->getLevel()))
            /** @noinspection PhpUnhandledExceptionInspection */
            API::getArenaByLevel(Loader::getInstance(), $ev->getPlayer()->getLevel())->removePlayer($ev->getPlayer());
    }

    public function onLevelChange(EntityLevelChangeEvent $ev){
        $entity = $ev->getEntity();
        if($entity instanceof Player){
            if(API::isArenaOf(Loader::getInstance(), $ev->getOrigin()) && API::isPlaying($entity, Loader::getInstance()))//TODO test if still calls it twice
                /** @noinspection PhpUnhandledExceptionInspection */
                API::getArenaByLevel(Loader::getInstance(), $ev->getOrigin())->removePlayer($entity);
        }
    }
}
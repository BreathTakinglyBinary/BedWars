<?php /** @noinspection PhpUnhandledExceptionInspection */

namespace xenialdan\BedWars;

use pocketmine\block\Bed;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\block\BlockPlaceEvent;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerExhaustEvent;
use pocketmine\item\ItemIds;
use pocketmine\network\mcpe\protocol\PlaySoundPacket;
use pocketmine\utils\TextFormat;
use xenialdan\gameapi\API;
use xenialdan\gameapi\Arena;
use xenialdan\gameapi\event\StopGameEvent;

/**
 * Class EventListener
 * @package xenialdan\XBedWars
 * Listens for all normal events
 */
class EventListener implements Listener{

    private $blocksPlaced = [];

    public function onDamage(EntityDamageByEntityEvent $event){

    }

    /**
     * @param BlockBreakEvent $event
     *
     * @priority HIGHEST
     */
    public function onBlockBreakEvent(BlockBreakEvent $event){
        $level = ($entity = $event->getPlayer())->getLevel();
        if(!API::isPlaying($entity, Loader::getInstance())){
            return;
        }
        $arena = API::getArenaByLevel(Loader::getInstance(), $level);
        if(!$arena instanceof Arena){
            return;
        }

        $block = $event->getBlock();
        if($block instanceof Bed){
            foreach($arena->getTeams() as $team){
                /** @var BedwarsTeam $team */

            }
            $bedTile = $level->getTile($block);
            if($bedTile instanceof \pocketmine\tile\Bed){
                $event->setDrops([]);
                $c = $bedTile->getColor();
                /** @var BedwarsTeam $attackedTeam */
                $attackedTeam = API::getTeamByColor(Loader::getInstance(), $event->getBlock()->getLevel(), API::getColorByMeta($c));
                if(is_null($attackedTeam)){//no team but bed for color
                    Loader::getInstance()->getLogger()->notice("Tried to break a bed for a non existing team. You might want to fix your map. Bed: Color: " . API::getColorByMeta($c) . "" . $event->getBlock() . " " . $event->getBlock()->asVector3() . " " . $event->getBlock()->getLevel()->getName());

                    return;
                }
                $event->setCancelled();
                if($attackedTeam->inTeam($entity)){
                    $entity->sendTip(TextFormat::RED . "You can not break your own teams bed!");//TODO add a warning to the player?

                    return;
                }else{
                    if($attackedTeam->isBedDestroyed()) return;
                    $event->setCancelled(false);
                    $attackedTeam->setBedDestroyed();
                    $teamOfPlayer = API::getTeamOfPlayer($entity);//TODO test if still happens in setup
                    if(is_null($teamOfPlayer)){
                        Loader::getInstance()->getLogger()->debug("Team of player was found null.");
                        $event->setCancelled(false);

                        return;
                    }
                    Loader::getInstance()->getServer()->broadcastTitle(TextFormat::RED . "Your Teams bed was destroyed", TextFormat::RED . "by team " . $teamOfPlayer->getColor() . $teamOfPlayer->getName(), -1, -1, -1, $attackedTeam->getPlayers());
                    foreach($attackedTeam->getPlayers() as $attackedTeamPlayer){
                        $attackedTeamPlayer->setSpawn($attackedTeamPlayer->getServer()->getDefaultLevel()->getSafeSpawn());
                    }
                    Loader::getInstance()->getServer()->broadcastTitle($attackedTeam->getColor() . "The bed of team " . $attackedTeam->getName(), $attackedTeam->getColor() . "was destroyed by team " . $teamOfPlayer->getColor() . $teamOfPlayer->getName(), -1, -1, -1, $attackedTeam->getPlayers());
                    $spk = new PlaySoundPacket();
                    [$spk->x, $spk->y, $spk->z] = [$entity->x, $entity->y, $entity->z];
                    $spk->volume = 1;
                    $spk->pitch = 0.0;
                    $spk->soundName = "mob.enderdragon.end";
                    $entity->getLevel()->broadcastGlobalPacket($spk);
                    #if (count($arena->getPlayers()) <= 1) $arena->stopArena();
                }
            }
            return;
        }
        if(isset($this->blocksPlaced[(int) $block->x][(int) $block->y][(int) $block->z])){
            $event->setCancelled(false);
        }
    }

    /**
     * @param BlockPlaceEvent $event
     *
     * @priority HIGHEST
     */
    public function onBlockPlaceEvent(BlockPlaceEvent $event){
        $block = $event->getBlock();
        $level = $block->getLevel();
        $arena = API::getArenaByLevel(Loader::getInstance(), $level);
        if(!$arena instanceof Arena){
            return;
        }
        if(($arena->getState() === Arena::STARTING || $arena->getState() === Arena::WAITING) && $event->getItem()->getId() === ItemIds::BED){
            //TODO: Finish removing the team swap function.
            $event->setCancelled();
            return;
        }
        if($arena->getState() === Arena::INGAME){
            $event->setCancelled(false);
            $this->blocksPlaced[(int) $block->x][(int) $block->y][(int) $block->z] = true;
        }
        /*if ($arena->getState() !== Arena::INGAME && $arena->getState() !== Arena::SETUP) {
            $event->setCancelled();
        }*/
    }

    public function onExhaust(PlayerExhaustEvent $event){
        $event->setCancelled();
    }

    public function onGameEnd(StopGameEvent $event){
        $this->blocksPlaced = [];
    }
}
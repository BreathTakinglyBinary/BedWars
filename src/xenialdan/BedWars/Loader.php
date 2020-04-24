<?php

namespace xenialdan\BedWars;

use BreathTakinglyBinary\minigames\API;
use BreathTakinglyBinary\minigames\Arena;
use BreathTakinglyBinary\minigames\Game;
use BreathTakinglyBinary\minigames\Team;
use pocketmine\entity\Entity;
use pocketmine\entity\object\ItemEntity;
use pocketmine\entity\object\PrimedTNT;
use pocketmine\entity\projectile\Arrow;
use pocketmine\item\Item;
use pocketmine\item\ItemIds;
use pocketmine\level\Position;
use pocketmine\math\Vector3;
use pocketmine\network\mcpe\protocol\LevelEventPacket;
use pocketmine\network\mcpe\protocol\LevelSoundEventPacket;
use pocketmine\Player;
use pocketmine\utils\TextFormat;
use xenialdan\BedWars\commands\BedwarsCommand;
use xenialdan\BedWars\listeners\EventListener;
use xenialdan\BedWars\listeners\LeaveGameListener;
use xenialdan\BedWars\listeners\LobbyListener;
use xenialdan\BedWars\listeners\NPCListener;
use xenialdan\BedWars\task\SpawnItemsTask;

class Loader extends Game{
    const TIER_1 = "Coins";
    const TIER_2 = "Pearls";
    const TIER_3 = "Emeralds";

    /** @var Loader */
    private static $instance = null;

    /**
     * Returns an instance of the plugin
     * @return Loader
     */
    public static function getInstance(){
        return self::$instance;
    }

    public function onLoad(){
        self::$instance = $this;
    }

    public function onEnable(){
        $this->registerListeners();
        $this->getServer()->getCommandMap()->register("XBedWars", new BedwarsCommand($this));
        /** @noinspection PhpUnhandledExceptionInspection */
        API::registerGame($this);
        foreach(glob($this->getDataFolder() . "*.json") as $v){
            $this->getLogger()->info("Adding arena " . basename($v, ".json"));
            $this->addArena($this->getNewArena($v));
        }
    }

    private function registerListeners() : void{
        $listeners = [
            new EventListener(),
            new LeaveGameListener(),
            new LobbyListener(),
            new NPCListener()
        ];
        foreach($listeners as $listener){
            $this->getServer()->getPluginManager()->registerEvents($listener, $this);
        }
    }

    public function getNewArena(string $settingsPath) : Arena{
        $settings = new BedwarsSettings($settingsPath);
        $levelname = basename($settingsPath, ".json");
        $arena = new Arena($levelname, $this, $settings);
        foreach($settings->get("teams", []) as $teamname => $teaminfo){
            $team = new BedwarsTeam($teaminfo["color"] ?? TextFormat::RESET, $teamname);
            $team->setMinPlayers(1);
            $team->setMaxPlayers($teaminfo["maxplayers"] ?? 1);

            if(!isset($teaminfo["bedlocation"]["x"]) or !isset($teaminfo["bedlocation"]["y"]) or !isset($teaminfo["bedlocation"]["z"])){
                $this->getLogger()->error("Invalid bedlocation found for $teamname in Arena " . $arena->getLevelName() . ".");
            }else{
                $team->setBedLocation(new Vector3($teaminfo["bedlocation"]["x"], $teaminfo["bedlocation"]["y"], $teaminfo["bedlocation"]["z"]));
            }

            #if (!is_null($teaminfo["spawn"]))
            $team->setSpawn(new Vector3(
                    $teaminfo["spawn"]["x"] ?? $arena->getLevel()->getSpawnLocation()->getFloorX(),
                    $teaminfo["spawn"]["y"] ?? $arena->getLevel()->getSpawnLocation()->getFloorY(),
                    $teaminfo["spawn"]["z"] ?? $arena->getLevel()->getSpawnLocation()->getFloorZ()
                )
            );
            $arena->addTeam($team);
        }

        return $arena;
    }

    public function setupArena(Player $player) : void{
        $this->getLogger()->info("This shouldn't be required in production.  Tsk Tsk");
    }

    /**
     * @param Arena  $arena
     * @param Player $player
     */
    public function removePlayer(Arena $arena, Player $player){
        $arena->bossbar->setTitle(count(array_filter($arena->getTeams(), function(Team $team) : bool{
                return count($team->getPlayers()) > 0;
            })) . ' teams alive');
    }

    /**
     * @param Arena $arena
     */
    public function startArena(Arena $arena) : void{
        /** @var BedwarsTeam $team */
        foreach($arena->getTeams() as $team){
            $team->setBedDestroyed(false);
            foreach($team->getPlayers() as $player){
                $player->setSpawn(Position::fromObject($team->getSpawn(), $arena->getLevel()));
                $player->teleport($player->getSpawn());
            }
        }

        $arena->bossbar->setSubTitle()->setTitle(count(array_filter($arena->getTeams(), function(BedwarsTeam $team) : bool{
                return count($team->getPlayers()) > 0;
            })) . ' teams alive')->setPercentage(1);

        $this->getScheduler()->scheduleDelayedRepeatingTask(new SpawnItemsTask($arena), 100, 1);
    }

    /**
     * @param Arena $arena
     */
    public function stopArena(Arena $arena) : void{
    }

    public function spawnTier1(Arena $arena){
        /** @var BedwarsSettings $settings */
        $settings = $arena->getSettings();
        foreach($settings->tier1 ?? [] as $i => $spawn){
            $v = new Vector3($spawn["x"] + 0.5, $spawn["y"] + 1, $spawn["z"] + 0.5);
            if(!$arena->getLevel()->isChunkLoaded($v->x >> 4, $v->z >> 4)) $arena->getLevel()->loadChunk($v->x >> 4, $v->z >> 4);
            //Stack items if too many
            if(count($arena->getLevel()->getChunkEntities($v->x >> 4, $v->z >> 4)) >= 50){
                /** @var ItemEntity|null $last */
                $last = null;
                foreach($arena->getLevel()->getChunkEntities($v->x >> 4, $v->z >> 4) as $chunkEntity){
                    if(!$chunkEntity instanceof ItemEntity) continue;
                    if($chunkEntity->getItem()->getId() === ItemIds::DOUBLE_PLANT){
                        if($last === null || $last->getItem()->getCount() >= 64){
                            $last = $chunkEntity;
                            continue;
                        }
                        $last->getItem()->setCount($last->getItem()->getCount() + $chunkEntity->getItem()->getCount());
                        $chunkEntity->close();
                        $last->respawnToAll();
                    }
                }
                if($last instanceof ItemEntity){
                    $last->getLevel()->broadcastLevelEvent($last, LevelEventPacket::EVENT_PARTICLE_EYE_DESPAWN);
                    $last->getLevel()->broadcastLevelEvent($last, LevelEventPacket::EVENT_PARTICLE_EYE_DESPAWN);
                    $last->getLevel()->broadcastLevelEvent($last, LevelEventPacket::EVENT_PARTICLE_EYE_DESPAWN);
                }
            }

            $arena->getLevel()->dropItem($v, (new Item(ItemIds::DOUBLE_PLANT))->setCount(1)->setCustomName(TextFormat::GOLD . "Coin"));
            $arena->getLevel()->broadcastLevelSoundEvent($v, LevelSoundEventPacket::SOUND_DROP_SLOT);
        }
    }

    public function spawnTier2(Arena $arena){
        /** @var BedwarsSettings $settings */
        $settings = $arena->getSettings();
        foreach($settings->tier2 ?? [] as $i => $spawn){
            $v = new Vector3($spawn["x"] + 0.5, $spawn["y"] + 1, $spawn["z"] + 0.5);
            if(!$arena->getLevel()->isChunkLoaded($v->x >> 4, $v->z >> 4)) $arena->getLevel()->loadChunk($v->x >> 4, $v->z >> 4);
            $arena->getLevel()->dropItem($v, (new Item(ItemIds::HEART_OF_THE_SEA))->setCustomName(TextFormat::WHITE . "Pearl"));
            $arena->getLevel()->broadcastLevelSoundEvent($v, LevelSoundEventPacket::SOUND_DROP_SLOT);
        }
    }

    public function spawnTier3(Arena $arena){
        /** @var BedwarsSettings $settings */
        $settings = $arena->getSettings();
        foreach($settings->tier3 ?? [] as $i => $spawn){
            $v = new Vector3($spawn["x"] + 0.5, $spawn["y"] + 1, $spawn["z"] + 0.5);
            if(!$arena->getLevel()->isChunkLoaded($v->x >> 4, $v->z >> 4)) $arena->getLevel()->loadChunk($v->x >> 4, $v->z >> 4);
            $arena->getLevel()->dropItem($v, (new Item(ItemIds::EMERALD))->setCustomName(TextFormat::GREEN . "Emerald"));
            $arena->getLevel()->broadcastLevelSoundEvent($v, LevelSoundEventPacket::SOUND_DROP_SLOT);
        }
    }

    /**
     * Called right when a player joins a game in an arena. Used to set up players
     *
     * @param Player $player
     */
    public function onPlayerJoinTeam(Player $player) : void{
        $player->setSpawn(Position::fromObject(API::getTeamOfPlayer($player)->getSpawn(), API::getArenaOfPlayer($player)->getLevel()));
    }

    /**
     * Callback function for @param Entity $entity
     * @return bool
     * @see array_filter
     * If return value is true, this entity will be deleted.
     */
    public function removeEntityOnArenaReset(Entity $entity) : bool{
        return $entity instanceof ItemEntity || $entity instanceof PrimedTNT || $entity instanceof Arrow;
    }

    public static function buyItem(Item $item, Player $player, string $valueType, int $value) : bool{
        $item = $item->setLore([]);
        switch($valueType){
            case self::TIER_1:
                $id = ItemIds::DOUBLE_PLANT;
                break;
            case self::TIER_2:
                $id = ItemIds::HEART_OF_THE_SEA;
                break;
            case self::TIER_3:
                $id = ItemIds::EMERALD;
                break;
            default:
                throw new \InvalidArgumentException("ValueType is wrong");
        }
        $payment = Item::get($id, 0, $value);
        if($player->getInventory()->contains($payment)){
            $player->getInventory()->removeItem($payment);
            $player->getInventory()->addItem($item);

            return true;
        }

        return false;
    }
}
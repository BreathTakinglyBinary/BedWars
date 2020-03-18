<?php


namespace xenialdan\BedWars;

use pocketmine\math\Vector3;
use BreathTakinglyBinary\minigames\Team;

class BedwarsTeam extends Team{
    private $bedDestroyed = false;

    /**
     * @return bool
     */
    public function isBedDestroyed() : bool{
        return $this->bedDestroyed;
    }

    /**
     * @param bool $bedDestroyed
     */
    public function setBedDestroyed(bool $bedDestroyed = true) : void{
        $this->bedDestroyed = $bedDestroyed;
    }

}
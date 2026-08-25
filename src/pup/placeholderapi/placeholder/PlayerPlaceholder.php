<?php

namespace pup\placeholderapi\placeholder;

use pocketmine\player\Player;
use pup\placeholderapi\AbstractPlaceholder;

abstract class PlayerPlaceholder extends AbstractPlaceholder
{
    public function requiresPlayer(): bool
    {
        return true;
    }

    public function process(?Player $player, ?string $params = null): string
    {
        if ($player === null) {
            return "";
        }

        return $this->processPlayer($player, $params);
    }

    /**
     * Process the placeholder with a  player context
     *
     * @param Player $player The player
     * @param string|null $params Optional parameters
     * @return string The replacement value
     */
    abstract protected function processPlayer(Player $player, ?string $params): string;
}

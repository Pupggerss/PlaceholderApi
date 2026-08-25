<?php

namespace pup\placeholderapi\placeholder;

use pocketmine\player\Player;
use pup\placeholderapi\AbstractPlaceholder;

abstract class ServerPlaceholder extends AbstractPlaceholder
{
    public function requiresPlayer(): bool
    {
        return false;
    }

    public function process(?Player $player, ?string $params = null): string
    {
        return $this->processServer($params);
    }

    /**
     * Process the placeholder without player context
     *
     * @param string|null $params Optional parameters
     * @return string The replacement value
     */
    abstract protected function processServer(?string $params): string;
}

<?php

namespace pup\placeholderapi\placeholder;

use Closure;
use pocketmine\player\Player;
use pup\placeholderapi\Placeholder;
readonly class ClosurePlaceholder implements Placeholder
{
    /**
     * @param string $identifier The placeholder identifier
     * @param Closure $handler The closure that processes the placeholder
     * @param bool $requiresPlayer Whether this placeholder requires a player
     * @param bool $supportsParameters Whether this placeholder supports parameters
     */
    public function __construct(
        private string  $identifier,
        private Closure $handler,
        private bool    $requiresPlayer = false,
        private bool    $supportsParameters = false
    ) {}

    public function getIdentifier(): string
    {
        return $this->identifier;
    }

    public function process(?Player $player, ?string $params = null): string
    {
        return (string)($this->handler)($player, $params);
    }

    public function requiresPlayer(): bool
    {
        return $this->requiresPlayer;
    }

    public function supportsParameters(): bool
    {
        return $this->supportsParameters;
    }
}

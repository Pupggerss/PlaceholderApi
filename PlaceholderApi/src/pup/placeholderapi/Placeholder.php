<?php

namespace pup\placeholderapi;

use pocketmine\player\Player;

interface Placeholder
{
    /**
     * Get unique identifier for this placeholder
     *
     * @return string
     */
    public function getIdentifier(): string;

    /**
     * Process the placeholder and return the replacement value
     *
     * @param Player|null $player The player context (can be null for server-wide placeholders)
     * @param string|null $params Optional parameters passed to the placeholder (e.g., {placeholder:param})
     * @return string The value to replace the placeholder with
     */
    public function process(?Player $player, ?string $params = null): string;

    /**
     * Check if this placeholder requires a player context
     *
     * @return bool
     */
    public function requiresPlayer(): bool;

    /**
     * Check if this placeholder supports parameters
     *
     * @return bool
     */
    public function supportsParameters(): bool;
}

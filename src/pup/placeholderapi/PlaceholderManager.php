<?php

namespace pup\placeholderapi;

use pocketmine\player\Player;
use Throwable;

class PlaceholderManager
{
    /** @var array<string, Placeholder> */
    private array $placeholders = [];

    /** @var int */
    private int $recursionDepth = 0;

    /** @var int */
    private const MAX_RECURSION_DEPTH = 10;

    /**
     * Register a placeholder
     *
     * @param Placeholder $placeholder The placeholder to register
     * @return bool True if registered successfully, false if identifier already exists
     */
    public function registerPlaceholder(Placeholder $placeholder): bool
    {
        $identifier = strtolower($placeholder->getIdentifier());

        if (isset($this->placeholders[$identifier])) {
            return false;
        }

        $this->placeholders[$identifier] = $placeholder;
        return true;
    }

    /**
     * Unregister a placeholder by identifier
     *
     * @param string $identifier The placeholder identifier
     * @return bool True if unregistered, false if not found
     */
    public function unregisterPlaceholder(string $identifier): bool
    {
        $identifier = strtolower($identifier);

        if (!isset($this->placeholders[$identifier])) {
            return false;
        }

        unset($this->placeholders[$identifier]);
        return true;
    }

    /**
     * Check if a placeholder is registered
     *
     * @param string $identifier The placeholder identifier
     * @return bool
     */
    public function isRegistered(string $identifier): bool
    {
        return isset($this->placeholders[strtolower($identifier)]);
    }

    /**
     * Get a registered placeholder
     *
     * @param string $identifier The placeholder identifier
     * @return Placeholder|null
     */
    public function getPlaceholder(string $identifier): ?Placeholder
    {
        return $this->placeholders[strtolower($identifier)] ?? null;
    }

    /**
     * Get all registered placeholder identifiers
     *
     * @return string[]
     */
    public function getRegisteredIdentifiers(): array
    {
        return array_keys($this->placeholders);
    }

    /**
     * Parse a message with placeholders
     *
     * @param string $message The message to parse
     * @param Player|null $player Optional player context
     * @return string The parsed message
     */
    public function parse(string $message, ?Player $player = null): string
    {
        if ($this->recursionDepth >= self::MAX_RECURSION_DEPTH) {
            return $message;
        }

        $this->recursionDepth++;

        $result = preg_replace_callback(
            '/{([a-zA-Z0-9_]+)(?::([^}]+))?}/',
            function($matches) use ($player) {
                $identifier = strtolower($matches[1]);
                $params = $matches[2] ?? null;

                $placeholder = $this->getPlaceholder($identifier);

                if ($placeholder === null) {
                    return $matches[0];
                }

                if ($placeholder->requiresPlayer() && $player === null) {
                    return $matches[0];
                }

                if ($params !== null && !$placeholder->supportsParameters()) {
                    return $matches[0];
                }

                try {
                    $value = $placeholder->process($player, $params);
                    return $this->parse($value, $player);
                } catch (Throwable $e) {
                    return $matches[0];
                }
            },
            $message
        );

        $this->recursionDepth--;
        return $result;
    }

    /**
     * Parse multiple messages at once
     *
     * @param string[] $messages Array of messages to parse
     * @param Player|null $player Optional player context
     * @return string[] Parsed messages
     */
    public function parseMultiple(array $messages, ?Player $player = null): array
    {
        return array_map(function ($message) use ($player) {
            return $this->parse($message, $player);
        }, $messages);
    }

    /**
     * Get count of registered placeholders
     *
     * @return int
     */
    public function count(): int
    {
        return count($this->placeholders);
    }
}

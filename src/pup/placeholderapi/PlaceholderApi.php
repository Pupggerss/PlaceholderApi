<?php

namespace pup\placeholderapi;

use pocketmine\player\Player;
use pocketmine\Server;
use pocketmine\utils\TextFormat as TF;

class PlaceholderApi
{
    /** @var array<string, string> */
    private static array $colorMap = [
        'black'        => TF::BLACK,
        'dark_blue'    => TF::DARK_BLUE,
        'dark_green'   => TF::DARK_GREEN,
        'dark_aqua'    => TF::DARK_AQUA,
        'dark_red'     => TF::DARK_RED,
        'dark_purple'  => TF::DARK_PURPLE,
        'gold'         => TF::GOLD,
        'gray'         => TF::GRAY,
        'dark_gray'    => TF::DARK_GRAY,
        'blue'         => TF::BLUE,
        'green'        => TF::GREEN,
        'aqua'         => TF::AQUA,
        'red'          => TF::RED,
        'light_purple' => TF::LIGHT_PURPLE,
        'yellow'       => TF::YELLOW,
        'white'        => TF::WHITE,
        'bold'         => TF::BOLD,
        'italic'       => TF::ITALIC,
        'obfuscated'   => TF::OBFUSCATED,
        'underline'    => TF::UNDERLINE,
        'strikethrough'=> TF::STRIKETHROUGH,
        'reset'        => TF::RESET
    ];

    /** @var array<string, callable> */
    private static array $customPlaceholders = [];

    /**
     * Register a custom placeholder handler
     *
     * @param string $placeholder The placeholder name
     * @param callable $handler Function that returns the replacement value
     */
    public static function registerPlaceholder(string $placeholder, callable $handler): void
    {
        self::$customPlaceholders[strtolower($placeholder)] = $handler;
    }

    /**
     * Unregister a custom placeholder
     *
     * @param string $placeholder The placeholder name to remove
     */
    public static function unregisterPlaceholder(string $placeholder): void
    {
        unset(self::$customPlaceholders[strtolower($placeholder)]);
    }

    /**
     * Parse a message with all placeholders
     *
     * @param string $message The message to parse
     * @param Player|null $player Optional player context for player-specific placeholders
     * @return string The formatted message
     */
    public static function parse(string $message, ?Player $player = null): string
    {
        $message = self::parseColors($message);
        if ($player !== null) {
            $message = self::parsePlayerPlaceholders($message, $player);
        }
        $message = self::parseServerPlaceholders($message);

        return self::parseCustomPlaceholders($message, $player);
    }

    /**
     * Parse color placeholders only
     *
     * @param string $message The message to parse
     * @return string The message with colors applied
     */
    public static function parseColors(string $message): string
    {
        return preg_replace_callback(
            '/{([a-zA-Z_]+)}/',
            function($matches) {
                $color = strtolower($matches[1]);
                return self::$colorMap[$color] ?? $matches[0];
            },
            $message
        );
    }

    /**
     * Parse player-specific placeholders
     *
     * @param string $message The message to parse
     * @param Player $player The player context
     * @return string The parsed message
     */
    private static function parsePlayerPlaceholders(string $message, Player $player): string
    {
        $replacements = [
            '{player}'          => $player->getName(),
            '{player_name}'     => $player->getName(),
            '{player_display}'  => $player->getDisplayName(),
            '{player_health}'   => (string)round($player->getHealth(), 1),
            '{player_max_health}' => (string)$player->getMaxHealth(),
            '{player_food}'     => (string)$player->getHungerManager()->getFood(),
            '{player_level}'    => (string)$player->getXpManager()->getXpLevel(),
            '{player_xp}'       => (string)$player->getXpManager()->getCurrentTotalXp(),
            '{player_gamemode}' => $player->getGamemode()->getEnglishName(),
            '{player_ping}'     => (string)$player->getNetworkSession()->getPing(),
            '{player_x}'        => (string)round($player->getPosition()->getX(), 2),
            '{player_y}'        => (string)round($player->getPosition()->getY(), 2),
            '{player_z}'        => (string)round($player->getPosition()->getZ(), 2),
            '{player_world}'    => $player->getWorld()->getFolderName(),
        ];

        return str_replace(
            array_keys($replacements),
            array_values($replacements),
            $message
        );
    }

    /**
     * Parse server-wide placeholders
     *
     * @param string $message The message to parse
     * @return string The parsed message
     */
    private static function parseServerPlaceholders(string $message): string
    {
        $server = Server::getInstance();

        $replacements = [
            '{server_motd}'     => $server->getMotd(),
            '{server_online}'   => (string)count($server->getOnlinePlayers()),
            '{server_max}'      => (string)$server->getMaxPlayers(),
            '{server_tps}'      => (string)round($server->getTicksPerSecond(), 2)
        ];

        return str_replace(
            array_keys($replacements),
            array_values($replacements),
            $message
        );
    }

    /**
     * Parse custom registered placeholders
     *
     * @param string $message The message to parse
     * @param Player|null $player Optional player context
     * @return string The parsed message
     */
    private static function parseCustomPlaceholders(string $message, ?Player $player = null): string
    {
        return preg_replace_callback(
            '/{([a-zA-Z0-9_]+)}/',
            function($matches) use ($player) {
                $placeholder = strtolower($matches[1]);
                if (isset(self::$customPlaceholders[$placeholder])) {
                    $result = call_user_func(self::$customPlaceholders[$placeholder], $player);
                    return (string)$result;
                }
                return $matches[0];
            },
            $message
        );
    }

    /**
     * Revert formatted message back to placeholder format
     * Idk what this would be useful for in some cases!
     *
     * @param string $formattedMessage The formatted message
     * @return string The message with placeholders
     */
    public static function revert(string $formattedMessage): string
    {
        $result = '';
        $parts = preg_split('/(?=' . TF::ESCAPE . ')/', $formattedMessage);
        $flippedMap = array_flip(self::$colorMap);

        foreach ($parts as $part) {
            if (str_starts_with($part, TF::ESCAPE)) {
                $colorCode = substr($part, 0, 2);
                if (isset($flippedMap[$colorCode])) {
                    $result .= '{' . $flippedMap[$colorCode] . '}';
                    $part = substr($part, 2);
                }
            }
            $result .= $part;
        }

        return $result;
    }

    /**
     * Create a character-by-character gradient effect
     *
     * @param string $text The text to apply gradient to
     * @param array $colors Array of color placeholder names
     * @return string The gradient text
     */
    public static function createCharacterGradient(string $text, array $colors): string
    {
        $result = "";
        $textLength = strlen($text);
        $colorCount = count($colors);

        if ($colorCount === 0) {
            return $text;
        }

        for ($i = 0; $i < $textLength; $i++) {
            $colorIndex = (int)floor(($i / $textLength) * $colorCount);
            $colorIndex = min($colorIndex, $colorCount - 1);
            $color = self::$colorMap[strtolower($colors[$colorIndex])] ?? TF::WHITE;
            $result .= $color . $text[$i];
        }

        return $result;
    }

    /**
     * Create a word-by-word gradient effect
     *
     * @param string $text The text to apply gradient to
     * @param array $colors Array of color placeholder names
     * @return string The gradient text
     */
    public static function createWordGradient(string $text, array $colors): string
    {
        $words = explode(' ', $text);
        $wordCount = count($words);
        $colorCount = count($colors);

        if ($colorCount === 0) {
            return $text;
        }

        $result = [];
        for ($i = 0; $i < $wordCount; $i++) {
            $colorIndex = (int)floor(($i / $wordCount) * $colorCount);
            $colorIndex = min($colorIndex, $colorCount - 1);
            $color = self::$colorMap[strtolower($colors[$colorIndex])] ?? TF::WHITE;
            $result[] = $color . $words[$i];
        }

        return implode(' ', $result);
    }

    /**
     * Strip all formatting from a message
     *
     * @param string $message The formatted message
     * @return string Clean text without formatting
     */
    public static function stripFormatting(string $message): string
    {
        return preg_replace('/§[0-9a-fk-or]/', '', $message);
    }

    /**
     * Get all registered custom placeholders
     *
     * @return array<string> List of placeholder names
     */
    public static function getRegisteredPlaceholders(): array
    {
        return array_keys(self::$customPlaceholders);
    }
}
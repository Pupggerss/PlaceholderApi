<?php

namespace pup\placeholderapi;

use pocketmine\player\Player;
use pocketmine\Server;
use pocketmine\utils\TextFormat as TF;
use pup\placeholderapi\placeholder\ClosurePlaceholder;
use pup\placeholderapi\placeholder\PlayerPlaceholder;
use pup\placeholderapi\placeholder\ServerPlaceholder;

class PlaceholderApi
{
    private static ?PlaceholderManager $manager = null;
    private static bool $defaultsRegistered = false;

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

    /**
     * Check if the PlaceholderAPI has been initialized
     *
     * @return bool
     */
    public static function isInitialized(): bool
    {
        return self::$manager !== null;
    }

    /**
     * Initialize the PlaceholderAPI
     * MUST be called before registering any placeholders to ensure all placeholders
     * are registered to the same manager instance.
     *
     */
    public static function initialize(): void
    {
        if (self::$manager !== null) {
            return;
        }

        self::$manager = new PlaceholderManager();
        self::registerDefaultPlaceholders();
    }

    /**
     * Get the PlaceholderManager instance
     *
     * @return PlaceholderManager
     */
    public static function getManager(): PlaceholderManager
    {
        if (self::$manager === null) {
            self::initialize();
        }
        return self::$manager;
    }

    private static function registerDefaultPlaceholders(): void
    {
        if (self::$defaultsRegistered) {
            return;
        }

        $manager = self::getManager();

        $manager->registerPlaceholder(new class extends PlayerPlaceholder {
            public function __construct() { parent::__construct("player"); }
            protected function processPlayer(Player $player, ?string $params): string {
                return $player->getName();
            }
        });

        $manager->registerPlaceholder(new class extends PlayerPlaceholder {
            public function __construct() { parent::__construct("player_name"); }
            protected function processPlayer(Player $player, ?string $params): string {
                return $player->getName();
            }
        });

        $manager->registerPlaceholder(new class extends PlayerPlaceholder {
            public function __construct() { parent::__construct("player_display"); }
            protected function processPlayer(Player $player, ?string $params): string {
                return $player->getDisplayName();
            }
        });

        $manager->registerPlaceholder(new class extends PlayerPlaceholder {
            public function __construct() { parent::__construct("player_health"); }
            protected function processPlayer(Player $player, ?string $params): string {
                return (string)round($player->getHealth(), 1);
            }
        });

        $manager->registerPlaceholder(new class extends PlayerPlaceholder {
            public function __construct() { parent::__construct("player_max_health"); }
            protected function processPlayer(Player $player, ?string $params): string {
                return (string)$player->getMaxHealth();
            }
        });

        $manager->registerPlaceholder(new class extends PlayerPlaceholder {
            public function __construct() { parent::__construct("player_food"); }
            protected function processPlayer(Player $player, ?string $params): string {
                return (string)$player->getHungerManager()->getFood();
            }
        });

        $manager->registerPlaceholder(new class extends PlayerPlaceholder {
            public function __construct() { parent::__construct("player_level"); }
            protected function processPlayer(Player $player, ?string $params): string {
                return (string)$player->getXpManager()->getXpLevel();
            }
        });

        $manager->registerPlaceholder(new class extends PlayerPlaceholder {
            public function __construct() { parent::__construct("player_xp"); }
            protected function processPlayer(Player $player, ?string $params): string {
                return (string)$player->getXpManager()->getCurrentTotalXp();
            }
        });

        $manager->registerPlaceholder(new class extends PlayerPlaceholder {
            public function __construct() { parent::__construct("player_gamemode"); }
            protected function processPlayer(Player $player, ?string $params): string {
                return $player->getGamemode()->getEnglishName();
            }
        });

        $manager->registerPlaceholder(new class extends PlayerPlaceholder {
            public function __construct() { parent::__construct("player_ping"); }
            protected function processPlayer(Player $player, ?string $params): string {
                return (string)$player->getNetworkSession()->getPing();
            }
        });

        $manager->registerPlaceholder(new class extends PlayerPlaceholder {
            public function __construct() { parent::__construct("player_x"); }
            protected function processPlayer(Player $player, ?string $params): string {
                return (string)round($player->getPosition()->getX(), 2);
            }
        });

        $manager->registerPlaceholder(new class extends PlayerPlaceholder {
            public function __construct() { parent::__construct("player_y"); }
            protected function processPlayer(Player $player, ?string $params): string {
                return (string)round($player->getPosition()->getY(), 2);
            }
        });

        $manager->registerPlaceholder(new class extends PlayerPlaceholder {
            public function __construct() { parent::__construct("player_z"); }
            protected function processPlayer(Player $player, ?string $params): string {
                return (string)round($player->getPosition()->getZ(), 2);
            }
        });

        $manager->registerPlaceholder(new class extends PlayerPlaceholder {
            public function __construct() { parent::__construct("player_world"); }
            protected function processPlayer(Player $player, ?string $params): string {
                return $player->getWorld()->getFolderName();
            }
        });

        // Server placeholders
        $manager->registerPlaceholder(new class extends ServerPlaceholder {
            public function __construct() { parent::__construct("server_motd"); }
            protected function processServer(?string $params): string {
                return Server::getInstance()->getMotd();
            }
        });

        $manager->registerPlaceholder(new class extends ServerPlaceholder {
            public function __construct() { parent::__construct("server_online"); }
            protected function processServer(?string $params): string {
                return (string)count(Server::getInstance()->getOnlinePlayers());
            }
        });

        $manager->registerPlaceholder(new class extends ServerPlaceholder {
            public function __construct() { parent::__construct("server_max"); }
            protected function processServer(?string $params): string {
                return (string)Server::getInstance()->getMaxPlayers();
            }
        });

        $manager->registerPlaceholder(new class extends ServerPlaceholder {
            public function __construct() { parent::__construct("server_tps"); }
            protected function processServer(?string $params): string {
                return (string)round(Server::getInstance()->getTicksPerSecond(), 2);
            }
        });

        foreach (self::$colorMap as $colorName => $colorCode) {
            $manager->registerPlaceholder(new ClosurePlaceholder(
                $colorName,
                fn(?Player $player, ?string $params): string => $colorCode,
                requiresPlayer: false,
                supportsParameters: false
            ));
        }

        self::$defaultsRegistered = true;
    }

    /**
     * Register a custom placeholder using the Placeholder interface
     *
     * @param Placeholder $placeholder The placeholder to register
     * @return bool True if registered successfully, false if identifier already exists
     */
    public static function registerCustomPlaceholder(Placeholder $placeholder): bool
    {
        return self::getManager()->registerPlaceholder($placeholder);
    }

    /**
     * Register a custom placeholder handler using a callable (legacy support v1)
     *
     * @param string $identifier The placeholder identifier
     * @param callable $handler Function that returns the replacement value (receives ?Player, ?string params)
     * @param bool $requiresPlayer Whether this placeholder requires a player
     * @param bool $supportsParameters Whether this placeholder supports parameters
     * @return bool True if registered successfully
     */
    public static function registerPlaceholder(
        string $identifier,
        callable $handler,
        bool $requiresPlayer = false,
        bool $supportsParameters = false
    ): bool {
        $placeholder = new ClosurePlaceholder(
            $identifier,
            $handler(...),
            $requiresPlayer,
            $supportsParameters
        );
        return self::getManager()->registerPlaceholder($placeholder);
    }

    /**
     * Unregister a custom placeholder
     *
     * @param string $identifier The placeholder identifier to remove
     * @return bool True if unregistered, false if not found
     */
    public static function unregisterPlaceholder(string $identifier): bool
    {
        return self::getManager()->unregisterPlaceholder($identifier);
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
        return self::getManager()->parse($message, $player);
    }

    /**
     * Parse multiple messages at once
     *
     * @param string[] $messages Array of messages to parse
     * @param Player|null $player Optional player context
     * @return string[] Parsed messages
     */
    public static function parseMultiple(array $messages, ?Player $player = null): array
    {
        return self::getManager()->parseMultiple($messages, $player);
    }

    /**
     * Parse color placeholders only
     *
     * @param string $message The message to parse
     * @return string The message with colors applied
     * @deprecated Use parse() instead, colors are now handled as regular placeholders
     */
    public static function parseColors(string $message): string
    {
        return self::parse($message);
    }

    /**
     * Revert formatted message back to placeholder format
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
}
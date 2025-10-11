# PlaceholderAPI v2.0

A powerful, type-safe placeholder system for PocketMine-MP plugins. Create reusable, parameterized placeholders with a clean, extensible API.

## What's New in v2.0

- **Type-safe interface system** - Implement `Placeholder` interface for full control
- **Parameterized placeholders** - Use `{placeholder:parameter}` syntax
- **Better organization** - Separate `PlayerPlaceholder` and `ServerPlaceholder` base classes
- **Validation & safety** - Automatic player requirement checks, recursion protection
- **Backward compatible** - All v1.x code still works!

## Features

- Built-in color formatting with named placeholders
- Extensive player data placeholders
- Server information placeholders
- Easy custom placeholder registration (closure or class-based)
- Parameterized placeholders for dynamic content
- Gradient text effects
- Recursive placeholder parsing
- Type-safe architecture

## Installation

### As a Virion (Recommended)
```bash
composer require pup/placeholderapi
```

### Direct Include
Drop the `src/pup/placeholderapi` folder into your plugin's source directory.

## Quick Start

### Initialization

**IMPORTANT:** Always initialize PlaceholderAPI before registering custom placeholders to ensure all placeholders are registered to the same manager instance.

```php
use pup\placeholderapi\PlaceholderApi;

// In your plugin's onEnable()
public function onEnable(): void
{
    // Initialize PlaceholderAPI first
    PlaceholderApi::initialize();

    // Now register your custom placeholders
    PlaceholderApi::registerPlaceholder(...);
}
```

### Basic Usage

```php
use pup\placeholderapi\PlaceholderApi;

// Parse message without player context (colors + server placeholders)
$message = "{gold}Server: {server_online}/{server_max} players online";
$formatted = PlaceholderApi::parse($message);

// Parse with player context (includes player-specific placeholders)
$message = "{green}Welcome {player_name}! Health: {player_health}";
$formatted = PlaceholderApi::parse($message, $player);
$player->sendMessage($formatted);

// Parse multiple messages at once
$messages = [
    "{gold}Welcome to the server!",
    "{aqua}You have {player_health} health",
    "{yellow}TPS: {server_tps}"
];
$parsed = PlaceholderApi::parseMultiple($messages, $player);
```

### Register Custom Placeholders

#### Method 1: Quick Closure Registration

```php
use pup\placeholderapi\PlaceholderApi;

// Simple server-wide placeholder
PlaceholderApi::registerPlaceholder(
    "server_name",
    fn(?Player $player, ?string $params): string => "My Server",
    requiresPlayer: false,
    supportsParameters: false
);

// Player-specific placeholder
PlaceholderApi::registerPlaceholder(
    "player_coins",
    function(?Player $player, ?string $params): string {
        if ($player === null) return "0";
        return (string)MyEconomy::getCoins($player);
    },
    requiresPlayer: true,
    supportsParameters: false
);

// Placeholder with parameters: {top_player:1}, {top_player:5}
PlaceholderApi::registerPlaceholder(
    "top_player",
    fn(?Player $player, ?string $params): string =>
        MyPlugin::getTopPlayer($params !== null ? (int)$params : 1),
    requiresPlayer: false,
    supportsParameters: true
);
```

#### Method 2: Class-Based Registration (Recommended)

```php
use pocketmine\player\Player;
use pup\placeholderapi\PlaceholderApi;
use pup\placeholderapi\placeholder\PlayerPlaceholder;
use pup\placeholderapi\placeholder\ServerPlaceholder;

// Player-specific placeholder
class PlayerBalancePlaceholder extends PlayerPlaceholder
{
    public function __construct()
    {
        parent::__construct("player_balance");
    }

    protected function processPlayer(Player $player, ?string $params): string
    {
        $balance = MyEconomy::getBalance($player);
        return "$" . number_format($balance, 2);
    }
}

// Server-wide placeholder
class TotalMoneyPlaceholder extends ServerPlaceholder
{
    public function __construct()
    {
        parent::__construct("total_money");
    }

    protected function processServer(?string $params): string
    {
        return "$" . number_format(MyEconomy::getTotalMoney(), 2);
    }
}

// Register them
PlaceholderApi::registerCustomPlaceholder(new PlayerBalancePlaceholder());
PlaceholderApi::registerCustomPlaceholder(new TotalMoneyPlaceholder());
```

## Built-in Placeholders

### Player Placeholders (require player context)

| Placeholder | Description | Example Output |
|------------|-------------|----------------|
| `{player}` or `{player_name}` | Player's name | `Steve` |
| `{player_display}` | Display name | `§aSteve` |
| `{player_health}` | Current health | `20.0` |
| `{player_max_health}` | Max health | `20.0` |
| `{player_food}` | Food level | `20` |
| `{player_level}` | XP level | `5` |
| `{player_xp}` | Total XP | `250` |
| `{player_gamemode}` | Game mode | `Survival` |
| `{player_ping}` | Network ping | `42` |
| `{player_x}` | X coordinate | `123.45` |
| `{player_y}` | Y coordinate | `64.00` |
| `{player_z}` | Z coordinate | `-789.12` |
| `{player_world}` | World name | `world` |

### Server Placeholders (work without player)

| Placeholder | Description | Example Output |
|------------|-------------|----------------|
| `{server_motd}` | Server MOTD | `My Server` |
| `{server_online}` | Online players | `15` |
| `{server_max}` | Max players | `20` |
| `{server_tps}` | Server TPS | `19.95` |

### Color Placeholders

**Colors:** `{black}` `{dark_blue}` `{dark_green}` `{dark_aqua}` `{dark_red}` `{dark_purple}` `{gold}` `{gray}` `{dark_gray}` `{blue}` `{green}` `{aqua}` `{red}` `{light_purple}` `{yellow}` `{white}`

**Formatting:** `{bold}` `{italic}` `{underline}` `{strikethrough}` `{obfuscated}` `{reset}`

## Advanced Features

### Parameterized Placeholders

```php
// Register a placeholder that accepts parameters
PlaceholderApi::registerPlaceholder(
    "player_stat",
    function(?Player $player, ?string $params): string {
        if ($player === null || $params === null) return "N/A";

        return match($params) {
            "kills" => (string)Stats::getKills($player),
            "deaths" => (string)Stats::getDeaths($player),
            "wins" => (string)Stats::getWins($player),
            default => "Unknown"
        };
    },
    requiresPlayer: true,
    supportsParameters: true
);

// Use in messages: {player_stat:kills}, {player_stat:deaths}, {player_stat:wins}
$msg = "{player_name} has {player_stat:kills} kills!";
```

### Recursive Placeholders

Placeholders can return other placeholders, which will be automatically parsed:

```php
PlaceholderApi::registerPlaceholder(
    "welcome_msg",
    fn(?Player $p, ?string $params): string =>
        "{gold}Welcome {player_name}! You have {player_coins} coins.",
    requiresPlayer: true
);

// The nested {player_name} and {player_coins} will be automatically parsed
$result = PlaceholderApi::parse("{welcome_msg}", $player);
```

### Gradient Text Effects

```php
// Character-by-character gradient
$text = "Rainbow Text!";
$colors = ['red', 'gold', 'yellow', 'green', 'aqua', 'blue', 'light_purple'];
$gradient = PlaceholderApi::createCharacterGradient($text, $colors);
$player->sendMessage($gradient);

// Word-by-word gradient
$text = "Each word different color";
$colors = ['red', 'yellow', 'green', 'aqua'];
$gradient = PlaceholderApi::createWordGradient($text, $colors);
$player->sendMessage($gradient);
```

## Complete Plugin Example

```php
namespace MyPlugin;

use pocketmine\plugin\PluginBase;
use pocketmine\player\Player;
use pup\placeholderapi\PlaceholderApi;
use pup\placeholderapi\placeholder\PlayerPlaceholder;

class Main extends PluginBase
{
    public function onEnable(): void
    {
        // IMPORTANT: Initialize PlaceholderAPI first
        PlaceholderApi::initialize();

        // Now register custom placeholders
        PlaceholderApi::registerPlaceholder(
            "server_name",
            fn(?Player $p, ?string $params): string => $this->getConfig()->get("name", "My Server"),
            requiresPlayer: false
        );

        PlaceholderApi::registerCustomPlaceholder(new PlayerKillsPlaceholder($this));
    }

    public function onDisable(): void
    {
        // Clean up
        PlaceholderApi::unregisterPlaceholder("server_name");
        PlaceholderApi::unregisterPlaceholder("player_kills");
    }

    public function sendWelcome(Player $player): void
    {
        $messages = [
            "{gold}{bold}========================",
            "{aqua}Welcome {player_name}!",
            "{gray}Health: {red}{player_health}§r{gray}/{player_max_health}",
            "{gray}World: {yellow}{player_world}",
            "{gray}Your kills: {green}{player_kills}",
            "{gray}Online: {white}{server_online}/{server_max}",
            "{gold}{bold}========================"
        ];

        $parsed = PlaceholderApi::parseMultiple($messages, $player);
        foreach ($parsed as $msg) {
            $player->sendMessage($msg);
        }
    }
}

class PlayerKillsPlaceholder extends PlayerPlaceholder
{
    public function __construct(private Main $plugin)
    {
        parent::__construct("player_kills");
    }

    protected function processPlayer(Player $player, ?string $params): string
    {
        // Get kills from your stats system
        return (string)$this->plugin->getKills($player);
    }
}
```

## Utility Methods

```php
// Check if a placeholder is registered
if (PlaceholderApi::isPlaceholderRegistered("player_coins")) {
    // ...
}

// Get all registered placeholders
$placeholders = PlaceholderApi::getRegisteredPlaceholders();

// Strip all formatting from a message
$clean = PlaceholderApi::stripFormatting("§aColored §bText");
// Result: "Colored Text"

// Unregister a placeholder
PlaceholderApi::unregisterPlaceholder("my_custom");
```

## Migration from v1.x

All v1.x code continues to work! The old API is fully supported:

```php
// Old way (still works)
PlaceholderApi::registerPlaceholder('my_placeholder', function($player) {
    return "value";
});

// New way (recommended)
PlaceholderApi::registerPlaceholder(
    'my_placeholder',
    fn(?Player $player, ?string $params): string => "value",
    requiresPlayer: false,
    supportsParameters: false
);
```

## Best Practices

1. **Initialize first** - Always call `PlaceholderApi::initialize()` in `onEnable()` before registering placeholders
2. **Use class-based placeholders** for complex logic (easier to test & maintain)
3. **Always validate player null** in player-specific placeholders
4. **Clean up on disable** - unregister your placeholders in `onDisable()`
5. **Use parameters** for flexibility - one placeholder can handle multiple cases
6. **Handle errors gracefully** - wrap risky operations in try-catch

## Credits

Originally created for the Kyro core by Pupggers.
Refactored to v2.0 with improved architecture and type safety.


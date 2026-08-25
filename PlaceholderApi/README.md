# PlaceholderAPI

A placeholder-parsing library for PocketMine-MP plugins. Register placeholders as closures or classes, use them in messages with `{identifier}` or `{identifier:params}` syntax, and parse them with or without player context.

## Installation

**As a virion**
```bash
composer require pup/placeholderapi
```

**Direct include**
Copy `src/pup/placeholderapi` into your plugin's source directory.

## Initialization

```php
use pup\placeholderapi\PlaceholderApi;

public function onEnable(): void
{
    PlaceholderApi::initialize();
    PlaceholderApi::registerPlaceholder(...);
}
```

`$manager` is a static singleton. `getManager()` lazily initializes it if `initialize()` was never called, so registering a placeholder without an explicit `initialize()` call still works. Calling it explicitly in `onEnable()` doesn't change behavior, it just makes the setup order visible in your code.

## Parsing

```php
use pup\placeholderapi\PlaceholderApi;

// No player: colors and server placeholders only
$message = "{gold}Server: {server_online}/{server_max} players online";
$formatted = PlaceholderApi::parse($message);

// With player context
$message = "{green}Welcome {player_name}! Health: {player_health}";
$formatted = PlaceholderApi::parse($message, $player);
$player->sendMessage($formatted);

// Multiple messages
$messages = [
    "{gold}Welcome to the server!",
    "{aqua}You have {player_health} health",
    "{yellow}TPS: {server_tps}"
];
$parsed = PlaceholderApi::parseMultiple($messages, $player);
```

A placeholder that requires a player and is parsed without one is left as literal text (`{player_name}` stays as-is). Same for a `:params` value passed to a placeholder that doesn't support parameters.

## Registering placeholders

### Closures

```php
use pup\placeholderapi\PlaceholderApi;

PlaceholderApi::registerPlaceholder(
    "server_name",
    fn(?Player $player, ?string $params): string => "My Server",
    requiresPlayer: false,
    supportsParameters: false
);

PlaceholderApi::registerPlaceholder(
    "player_coins",
    function(?Player $player, ?string $params): string {
        if ($player === null) return "0";
        return (string)MyEconomy::getCoins($player);
    },
    requiresPlayer: true,
    supportsParameters: false
);

// {top_player:1}, {top_player:5}
PlaceholderApi::registerPlaceholder(
    "top_player",
    fn(?Player $player, ?string $params): string =>
        MyPlugin::getTopPlayer($params !== null ? (int)$params : 1),
    requiresPlayer: false,
    supportsParameters: true
);
```

### Classes

Use `PlayerPlaceholder` or `ServerPlaceholder` when the logic is more than a couple of lines, or when you want it unit-testable independent of the registration call.

```php
use pocketmine\player\Player;
use pup\placeholderapi\PlaceholderApi;
use pup\placeholderapi\placeholder\PlayerPlaceholder;
use pup\placeholderapi\placeholder\ServerPlaceholder;

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

PlaceholderApi::registerCustomPlaceholder(new PlayerBalancePlaceholder());
PlaceholderApi::registerCustomPlaceholder(new TotalMoneyPlaceholder());
```

`PlayerPlaceholder::process()` already returns `""` if `$player` is null, before your `processPlayer()` runs, so you don't need to null-check inside it.

## Built-in placeholders

### Player (require a player)

| Placeholder | Description | Example |
|---|---|---|
| `{player}` / `{player_name}` | Player's name | `Steve` |
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

### Server

| Placeholder | Description | Example |
|---|---|---|
| `{server_motd}` | Server MOTD | `My Server` |
| `{server_online}` | Online players | `15` |
| `{server_max}` | Max players | `20` |
| `{server_tps}` | Server TPS | `19.95` |

### Colors and formatting

`{black}` `{dark_blue}` `{dark_green}` `{dark_aqua}` `{dark_red}` `{dark_purple}` `{gold}` `{gray}` `{dark_gray}` `{blue}` `{green}` `{aqua}` `{red}` `{light_purple}` `{yellow}` `{white}`

`{bold}` `{italic}` `{underline}` `{strikethrough}` `{obfuscated}` `{reset}`

## Parameters

```php
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

$msg = "{player_name} has {player_stat:kills} kills";
```

## Recursion

A placeholder's return value is parsed again, so it can contain other placeholders:

```php
PlaceholderApi::registerPlaceholder(
    "welcome_msg",
    fn(?Player $p, ?string $params): string =>
        "{gold}Welcome {player_name}! You have {player_coins} coins.",
    requiresPlayer: true
);

$result = PlaceholderApi::parse("{welcome_msg}", $player);
```

Depth is capped at 10 (`PlaceholderManager::MAX_RECURSION_DEPTH`). Past that, whatever is left unresolved is returned as-is instead of infinitely looping. If a placeholder's `process()` throws, the manager catches it and returns the original `{tag}` unmodified rather than propagating the exception, so a broken custom placeholder won't crash whatever is sending the message. It's still worth logging inside your placeholder if you want to know it's failing.

## Gradients

```php
$text = "Rainbow Text";
$colors = ['red', 'gold', 'yellow', 'green', 'aqua', 'blue', 'light_purple'];
$gradient = PlaceholderApi::createCharacterGradient($text, $colors);
$player->sendMessage($gradient);

$text = "Each word different color";
$colors = ['red', 'yellow', 'green', 'aqua'];
$gradient = PlaceholderApi::createWordGradient($text, $colors);
$player->sendMessage($gradient);
```

## Other utilities

- `PlaceholderApi::revert($formatted)` — converts a formatted string (with § codes) back into `{color}` placeholder tags. Useful if you store formatted strings and need to make them editable again.
- `PlaceholderApi::stripFormatting($message)` — strips all § formatting codes from a string.

## Full example

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
        PlaceholderApi::initialize();

        PlaceholderApi::registerPlaceholder(
            "server_name",
            fn(?Player $p, ?string $params): string => $this->getConfig()->get("name", "My Server"),
            requiresPlayer: false
        );

        PlaceholderApi::registerCustomPlaceholder(new PlayerKillsPlaceholder($this));
    }

    public function onDisable(): void
    {
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
        return (string)$this->plugin->getKills($player);
    }
}
```

## Migrating from v1

v1-style calls still work unchanged:

```php
PlaceholderApi::registerPlaceholder('my_placeholder', function($player) {
    return "value";
});
```

is equivalent to:

```php
PlaceholderApi::registerPlaceholder(
    'my_placeholder',
    fn(?Player $player, ?string $params): string => "value",
    requiresPlayer: false,
    supportsParameters: false
);
```

`requiresPlayer` and `supportsParameters` default to `false`, so old registrations behave the same way they did before those flags existed.

## Notes

- Unregister placeholders you registered in `onDisable()` if your plugin can be reloaded without a full server restart, otherwise a second `onEnable()` will fail to re-register them (identifiers already in use return `false` instead of overwriting).
- Class-based placeholders are easier to unit test in isolation than closures, since you can call `processPlayer()`/`processServer()` directly without going through the manager.
- Identifiers are matched case-insensitively and stored lowercased internally.

## Credits

Originally written for the Kyro core (Pupggers). Refactored in v2.0.
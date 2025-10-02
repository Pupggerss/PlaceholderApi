# PlaceholderApi

A simple and efficient placeholder system for PocketMine-MP. Originally created for the Kyro core.

## Features

- Color formatting with named placeholders
- Player data placeholders
- Server information placeholders
- Custom placeholder registration
- Gradient text effects

## Installation

```bash
composer require pup/placeholderapi
```

Or just drop the `PlaceholderApi.php` file into your plugin's source folder.

## Basic Usage

```php
use pup\placeholderapi\PlaceholderApi;

// Simple color formatting
$message = "{gold}Hello {aqua}World!";
$formatted = PlaceholderApi::parse($message);

// With player data
$message = "{green}Welcome {player}! Health: {player_health}";
$formatted = PlaceholderApi::parse($message, $player);
$player->sendMessage($formatted);
```

## Available Placeholders

### Colors
`{black}` `{dark_blue}` `{dark_green}` `{dark_aqua}` `{dark_red}` `{dark_purple}` `{gold}` `{gray}` `{dark_gray}` `{blue}` `{green}` `{aqua}` `{red}` `{light_purple}` `{yellow}` `{white}`

### Formatting
`{bold}` `{italic}` `{underline}` `{strikethrough}` `{obfuscated}` `{reset}`

### Player Placeholders
- `{player}` - Player name
- `{player_health}` - Current health
- `{player_max_health}` - Max health
- `{player_food}` - Food level
- `{player_level}` - XP level
- `{player_xp}` - Total XP
- `{player_gamemode}` - Gamemode
- `{player_ping}` - Ping in ms
- `{player_x}` `{player_y}` `{player_z}` - Coordinates
- `{player_world}` - World name

### Server Placeholders
- `{server_motd}` - Server MOTD
- `{server_online}` - Online players
- `{server_max}` - Max players
- `{server_tps}` - Server TPS

## Custom Placeholders

Register your own placeholders:

```php
// Simple placeholder
PlaceholderApi::registerPlaceholder('my_placeholder', function(?Player $player) {
    return "Custom value";
});

// With economy integration
PlaceholderApi::registerPlaceholder('player_money', function(?Player $player) {
    if ($player === null) return "$0";
    return "$" . EconomyAPI::getInstance()->myMoney($player);
});

// Usage
$message = "You have {player_money}!";
$formatted = PlaceholderApi::parse($message, $player);
```

## Gradient Effects

```php
// Character gradient
$text = "Rainbow Text!";
$colors = ['red', 'gold', 'yellow', 'green', 'aqua', 'blue', 'light_purple'];
$gradient = PlaceholderApi::createCharacterGradient($text, $colors);

// Word gradient
$text = "Each word different color";
$colors = ['red', 'yellow', 'green', 'aqua'];
$gradient = PlaceholderApi::createWordGradient($text, $colors);
```

## Example

```php
// Register custom placeholders
PlaceholderApi::registerPlaceholder('server_prefix', function(?Player $player) {
    return "[Kyro]";
});

// Create welcome message
$welcome = [
    "{gold}{bold}===========================",
    "{server_prefix} {aqua}Welcome {player}!",
    "{gray}Health: {red}{player_health}/{player_max_health}",
    "{gray}World: {yellow}{player_world}",
    "{gray}Online: {white}{server_online}/{server_max}",
    "{gold}{bold}==========================="
];

foreach ($welcome as $line) {
    $player->sendMessage(PlaceholderApi::parse($line, $player));
}
```
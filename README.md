# Glyphify

PHP font generator supporting Persian and Latin with 50+ unique font styles.

## Overview

Glyphify is a comprehensive text styling library that transforms ordinary text into various Unicode-based font styles. It supports both Persian and Latin scripts, offering over 50 different font mappings including mathematical symbols, decorative styles, and specialized Persian fonts. Perfect for social media posts, creative writing, and any application requiring styled text.

## Donate

<a href="http://www.coffeete.ir/afaz">
  <img src="http://www.coffeete.ir/images/buttons/lemonchiffon.png" width="260" />
</a>

## Table of Contents

* [Features](#features)
* [Requirements](#requirements)
* [Installation](#installation)
* [Usage](#usage)
* [Contributing](#contributing)
* [License](#license)

## Features

- **Multi-script Support**: Fully supports Persian (Arabic script) and Latin text
- **50+ Font Styles**: Includes bold, italic, circled, squared, fraktur, script, and more
- **Persian Specialization**: Dedicated Persian fonts with proper character mapping and contextual forms
- **Smart Text Detection**: Automatically detects Persian, English, or mixed text
- **Finglish Transliteration**: Converts Persian text to Finglish (Persian in Latin script)
- **Flexible Output**: Generate styled text as array, JSON, or CSV
- **Configurable Options**: Control Persian, Latin, Finglish, and all-fonts modes
- **Lightweight**: No external dependencies, pure PHP implementation

## Requirements

- PHP 7.4 or higher
- JSON extension (enabled by default)
- Multibyte string support (PHP's mbstring extension)

## Installation

Install via Composer:

```bash
composer require afaztech/glyphify
```

Or clone the repository directly:

```bash
git clone https://github.com/AfazTech/glyphify.git
cd glyphify
composer install
```

## Usage

### Basic Usage

```php
<?php

require_once __DIR__ . '/vendor/autoload.php';

use AfazTech\Glyphify\Glyphify;

// Create instance with your text
$glyphify = new Glyphify("Hello World");

// Generate all available fonts
$results = $glyphify->generate();

// Display styled text
$glyphify->display();

// Get specific font
$specific = $glyphify->generate('Bold');
```

### Configuration Options

```php
$config = [
    'persian' => true,     // Enable Persian font generation
    'latin' => true,       // Enable Latin font generation
    'finglish' => true,    // Enable Finglish transliteration
    'all_fonts' => false,  // Generate ALL fonts at once
];

$glyphify = new Glyphify("Your text here", $config);
```

### Output Formats

```php
// Get as array
$array = $glyphify->getArray();

// Get as JSON
$json = $glyphify->toJson();

// Get as CSV
$csv = $glyphify->toCsv();

// Display in CLI
$glyphify->display();
```

#### Methods

| Method | Description |
|--------|-------------|
| `generate(?string $fontName = null): array` | Generate styled text. If font name provided, returns only that font |
| `getArray(): array` | Returns the last generated output array |
| `toJson(?string $fontName = null): string` | Returns output as JSON |
| `toCsv(?string $fontName = null): string` | Returns output as CSV |
| `display(?string $fontName = null): void` | Displays output in CLI with formatting |
| `getFontNames(): array` | Returns all available font names |
| `getFontByName(string $name): ?array` | Returns specific font configuration |
| `getAllFonts(): array` | Returns all font configurations |
| `getLatinFontNames(): array` | Returns only Latin font names |
| `getPersianFontNames(): array` | Returns only Persian font names |
| `getPersianStyledFontNames(): array` | Returns only Persian styled font names |


## Contributing

Contributions are welcome! Here's how you can help:

1. Fork the repository
2. Create your feature branch (`git checkout -b feature/AmazingFeature`)
3. Commit your changes (`git commit -m 'Add some AmazingFeature'`)
4. Push to the branch (`git push origin feature/AmazingFeature`)
5. Open a Pull Request


## License

This project is licensed under the MIT License. See the [LICENSE](https://github.com/AfazTech/glyphify/blob/main/LICENSE) file for more information.

---

Built with ❤️ by [AfazTech](https://github.com/AfazTech)
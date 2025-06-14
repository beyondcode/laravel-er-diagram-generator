# Laravel ER Diagram Generator

[![Latest Version on Packagist](https://img.shields.io/packagist/v/beyondcode/laravel-er-diagram-generator.svg?style=flat-square)](https://packagist.org/packages/beyondcode/laravel-er-diagram-generator)
[![Total Downloads](https://img.shields.io/packagist/dt/beyondcode/laravel-er-diagram-generator.svg?style=flat-square)](https://packagist.org/packages/beyondcode/laravel-er-diagram-generator)

This package lets you generate entity relation diagrams by inspecting the relationships defined in your model files.
It is highly customizable.
Behind the scenes, it uses [GraphViz](https://www.graphviz.org) to generate the graph.

> If you want to learn how to create reusable PHP packages yourself, take a look at my upcoming [PHP Package Development](https://phppackagedevelopment.com) video course.

## Prerequisites

The minimum required PHP version is 7.1.0.

## Requirements

This package requires the `graphviz` tool.

You can install Graphviz on MacOS via homebrew:

```bash
brew install graphviz
```

Or, if you are using Homestead:

```bash
sudo apt-get install graphviz
```

To install Graphviz on Windows, download it from the [official website](https://graphviz.gitlab.io/_pages/Download/Download_windows.html).

## Installation

You can install the package via composer:

```bash
composer require beyondcode/laravel-er-diagram-generator --dev
```

If you are using Laravel 5.5+, the package will automatically register the service provider for you.

If you are using Lumen, you will need to add the following to `bootstrap\app.php`:

```php
# Register Service Providers
$app->register(BeyondCode\ErdGenerator\ErdGeneratorServiceProvider::class);
```

## Usage

By default, the package will automatically detect all models in your `app/Models` directory that extend the Eloquent Model class. If you would like you explicitly define where your models are located, you can publish the configuration file using the following command.

```bash
php artisan vendor:publish --provider=BeyondCode\\ErdGenerator\\ErdGeneratorServiceProvider
```

If you're using Lumen and you want to customize the config, you'll need to copy the config file from the vendor directory:

```bash
cp ./vendor/beyondcode/laravel-er-diagram-generator/config/config.php config/erd-generator.php
```

## Generating Diagrams

You can generate entity relation diagrams using the provided artisan command:

```bash
php artisan generate:erd
```

This will generate a file called `graph.png`.

You can also specify a custom filename:

```bash
php artisan generate:erd output.png
```

Or use one of the other [output formats](https://www.graphviz.org/doc/info/output.html), like SVG:

```bash
php artisan generate:erd output.svg --format=svg
```

### Text Output

If you want to generate a text representation of the ER diagram instead of an image, you can use the `--text-output` option:

```bash
php artisan generate:erd output.txt --text-output
```

This will generate a text file with the GraphViz DOT representation of the ER diagram.

### Structured Text Output for AI Models

If you want to generate a structured text representation of the ER diagram that is more suitable for AI models, simply specify a filename with a `.txt` extension:

```bash
php artisan generate:erd output.txt
```

This will automatically generate a Markdown file with a structured representation of the entities and their relationships, which can be used as context for AI models.

#### Output Format

The structured output format looks like this:

```markdown
# Entity Relationship Diagram

## Entities

### User (`App\Models\User`)

#### Attributes:
- `id` (integer)
- `name` (string)
- `email` (string)
...

## Relationships

### User Relationships
- **HasMany** `posts` to Post (Local Key: `id`, Foreign Key: `user_id`)
...
```

This format is particularly useful when providing context to AI models about your database structure.

## Customization

Please take a look at the published `erd-generator.php` configuration file for all available customization options.

## Examples

Here are some examples taken from the [Laravel.io](https://laravel.io) codebase.

![Using Database Schema](https://beyondco.de/github/erd-generator/schema.png)

![Customized](https://beyondco.de/github/erd-generator/customized.png)

### Testing

``` bash
composer test
```

### Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

### Security

If you discover any security related issues, please email marcel@beyondco.de instead of using the issue tracker.

## Credits

- [Marcel Pociot](https://github.com/mpociot)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
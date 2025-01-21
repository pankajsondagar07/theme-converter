# Simplifying Theme Integration

A custom Laravel package that generates Blade files based on uploaded theme zip files, AdminLTE, Gentellia, Bootstrap themes, etc., into Laravel projects. Users can upload the theme zip file, and the package automatically converts the template files into Blade syntax, making them ready for use in Laravel views

## Installation

You can install the package via composer:

```bash
composer require pankaj/theme-converter
```

You can publish and run the migrations with:

```bash
php artisan vendor:publish --tag="theme-converter-migrations"
php artisan migrate
```

You can publish the config file with:

```bash
php artisan vendor:publish --tag="theme-converter-config"
```

This is the contents of the published config file:

```php
return [
];
```

Optionally, you can publish the views using

```bash
php artisan vendor:publish --tag="theme-converter-views"
```

## Usage

```php
$themeConverter = new Pankaj\ThemeConverter();
echo $themeConverter->echoPhrase('Hello, Pankaj!');
```

## Testing

```bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Security Vulnerabilities

Please review [our security policy](../../security/policy) on how to report security vulnerabilities.

## Credits

- [Pankaj Sondagar](https://github.com/139882819+pankajsondagar07)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

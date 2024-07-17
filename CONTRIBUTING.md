# Contributing

Contributions are **welcome** and will be fully **credited**.

Please read and understand the contribution guide before creating an issue or pull request.

## Etiquette

Be kind.

## Viability

When requesting or submitting new features, first consider whether it might be useful to others. Open source projects are used by many developers, who may have entirely different needs to your own. Think about whether or not your feature is likely to be used by other users of the project.

## Procedure

Before filing an issue:

- Attempt to replicate the problem, to ensure that it wasn't a coincidental incident.
- Check to make sure your feature suggestion isn't already present within the project.
- Check the pull requests tab to ensure that the bug doesn't have a fix in progress.
- Check the pull requests tab to ensure that the feature isn't already in progress.

Before submitting a pull request:

- Check the codebase to ensure that your feature doesn't already exist.
- Check the pull requests to ensure that another person hasn't already submitted the feature or fix.

## Requirements

If the project maintainer has any additional requirements, you will find them listed here.

When working locally you will need to install the dev dependencies.

```bash
COMPOSER=composer-dev.json composer install
```

## Dependencies

To update dependencies to latest:

```bash
# Production
composer require friendsofphp/php-cs-fixer laravel-zero/framework laravel/pint nunomaduro/termwind tightenco/tlint --dev
composer require larastan/larastan mrchetan/php_standard

# Development
COMPOSER=composer-dev.json composer require friendsofphp/php-cs-fixer laravel-zero/framework laravel/pint nunomaduro/termwind pestphp/pest rector/rector tightenco/tlint --dev
COMPOSER=composer-dev.json composer require larastan/larastan mrchetan/php_standard
```
## PHPStan

If PHPStan fails locally, try increasing the memory:

```bash
./vendor/bin/phpstan analyze --memory-limit 1G
```

# Technology Stack

**Analysis Date:** 2026-02-06

## Languages

**Primary:**
- PHP 8.2+ - Core plugin development (WordPress plugin)

**Secondary:**
- JavaScript (ES2018) - Frontend comment handling and admin interactions
- CSS - Styling for admin interface and frontend components

## Runtime

**Environment:**
- WordPress core (no minimum version specified, but tested with recent versions)
- PHP runtime via WordPress hosting environment

**Package Manager:**
- Composer - PHP dependency management
- npm - JavaScript/development tooling

**Lockfile:**
- `composer.lock` - Present and committed
- `package-lock.json` - Present and committed

## Frameworks

**Core:**
- WordPress - Version unspecified, primary framework for plugin
- WP Test Utils (yoast/wp-test-utils ^1.2) - WordPress testing utilities

**Testing:**
- PHPUnit ^9.3 - PHP unit testing
- Brain Monkey - PHP mocking framework

**Build/Dev:**
- PHP CS Fixer ^3.75 - PHP code formatting
- PHPStan ^2.0 - PHP static analysis
- PHP Parallel Lint ^1.3 - PHP syntax checking
- PHPCS (squizlabs/php_codesniffer) - Code standards checking

## Key Dependencies

**Critical:**
- composer/installers ^2.3.0 - Composer plugin installer for WordPress

**Code Standards:**
- wp-coding-standards/wpcs ^3.1 - WordPress coding standards for PHPCS
- yoast/yoastcs ^3.0 - Yoast coding standards extension
- phpcompatibility/phpcompatibility-wp * - PHP compatibility checking for WordPress
- szepeviktor/phpstan-wordpress ^2.0 - PHPStan extension for WordPress
- phpstan/extension-installer ^1.4 - PHPStan extension autoloader

**PHP Compatibility:**
- php-stubs/wordpress-stubs - WordPress function stubs for static analysis

**Testing Support:**
- mockery/mockery - PHP mocking library
- hamcrest/hamcrest-php - PHP assertions
- yoast/phpunit-polyfills - PHPUnit compatibility layer

**Optional Plugin Integration:**
- Progress_Planner (conditionally loaded) - Task provider integration when present

## Configuration

**Environment:**
- Configuration stored as WordPress options via `get_option()` / `update_option()`
- Option key: `comment_hacks`
- No environment variables required for core functionality

**Build:**
- `.phpcs.xml.dist` - PHPCS ruleset configuration
- `phpstan.neon.dist` - PHPStan analysis rules (level 6)
- `.eslintrc` - ESLint configuration for JavaScript (extends Yoast config)
- `phpunit.xml.dist` - PHPUnit test configuration
- `.php-cs-fixer.dist.php` - PHP CS Fixer rules

**Key Settings:**
- PHP platform target: 8.3
- Minimum PHP: 8.2
- PHPCS test version: PHP 7.4+
- PHPStan level: 6 (strict)

## Platform Requirements

**Development:**
- PHP 8.2+
- Composer
- npm (for development tooling)
- WordPress local environment
- phpunit/phpunit 9.3+

**Production:**
- WordPress installation (version unspecified, recent versions)
- PHP 8.2+ (minimum, but 7.4 mentioned in main plugin file as fallback)
- No additional server-side dependencies beyond WordPress

## JavaScript Tooling

**Configuration:**
- ESLint config: `.eslintrc` (extends `yoast` preset)
- ECMAScript: 2018
- Complexity rules: Max 6
- JSDoc requirement: Required for methods and functions

**Asset Location:**
- Admin assets: `admin/assets/js/` and `admin/assets/css/`
- Frontend scripts: Enqueued via WordPress `wp_enqueue_script()`

---

*Stack analysis: 2026-02-06*

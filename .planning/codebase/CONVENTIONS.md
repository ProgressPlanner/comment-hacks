# Coding Conventions

**Analysis Date:** 2026-02-06

## Naming Patterns

**Files:**
- Classes: PascalCase with hyphens for multi-word names: `clean-emails.php`, `comment-parent.php`
- Traits/Interfaces: PascalCase (none currently used)
- Follows `Yoast.Files.FileName` rule excluding main plugin file (`comment-hacks.php`)

**Functions:**
- Method names: snake_case: `comment_notification_text()`, `add_comment_basics()`, `check_comment_policy()`
- Static methods: snake_case: `autoload()`, `get_options()`
- Callback methods: snake_case in action/filter hooks: `'comment_notification_text'`, `'admin_bar_menu'`
- Private methods prefix with underscore consideration: not used, visibility via `private` keyword instead

**Variables:**
- Private properties: snake_case with `private` keyword: `$comment_id`, `$comment`, `$message`, `$options`
- Local variables: snake_case: `$comment_data`, `$comment_link`, `$actions`
- Constants (class-level): UPPER_SNAKE_CASE: `NOTIFICATION_RECIPIENT_KEY`
- Plugin-level constants (backward compat): UPPER_SNAKE_CASE: `EMILIA_COMMENT_HACKS_PATH`, `EMILIA_COMMENT_HACKS_VERSION`

**Types:**
- Class names: PascalCase with namespace: `EmiliaProjects\WP\Comment\Inc\Clean_Emails`
- Namespace follows vendor/project/layer pattern: `EmiliaProjects\WP\Comment\Inc`, `EmiliaProjects\WP\Comment\Admin`
- Type hints used extensively: `string`, `int`, `array`, `bool`, `WP_Comment`, `WP_Post` with full qualification
- Return types explicitly declared: `: void`, `: string`, `: array`, `: int`

## Code Style

**Formatting:**
- Indentation: Tabs (WordPress standard)
- Line length: No strict enforced limit observed
- Brace placement: Opening braces on same line for classes/methods, separate line for control structures (WordPress style)
- `else` and `elseif` on own line after closing brace (CLAUDE.md requirement, WordPress PHPCS enforced)

**Linting:**
- Tool: PHPCS with Yoast ruleset (WordPress Coding Standards based)
- Config: `.phpcs.xml.dist`
- Key rules enforced:
  - Yoast standard as base rule with selective exclusions
  - Text domain validation: checks for `yoast-comment-hacks`, `default`
  - Prefix validation: `EmiliaProjects\WP\Comment`, `comment_hacks`, `comment_experience`
  - PHPCompatibility for PHP 7.4+ targets
  - SlevomatCodingStandard for namespaced global functions (custom exemption for `defined()` - Plugin Check requirement)

**Static Analysis:**
- Tool: PHPStan level 6
- Config: `phpstan.neon.dist`
- Known ignores for external/optional integration (Progress_Planner, EMILIA_COMMENT_HACKS_PATH constant)

## Import Organization

**Order:**
1. Namespace declaration first
2. Standard PHP checks (`if ( ! defined( 'ABSPATH' ) )`)
3. `use` statements for specific classes (examples: `use WP_Comment;`, `use WP_Post;`, `use EmiliaProjects\WP\Comment\Inc\Hacks;`)
4. DocBlock comment
5. Class declaration

**Example from `inc/clean-emails.php`:**
```php
<?php

namespace EmiliaProjects\WP\Comment\Inc;

// phpcs:ignore SlevomatCodingStandard.Namespaces.FullyQualifiedGlobalFunctions.NonFullyQualified -- Plugin Check requires defined() without backslash.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use WP_Comment;
use WP_Post;

/**
 * Clean the emails.
 */
class Clean_Emails {
```

**Path Aliases:**
- None detected - imports use fully qualified class names
- WordPress functions accessed with leading backslash: `\add_filter()`, `\get_comment()`, `\esc_html__()`

## Error Handling

**Patterns:**
- WordPress standard: Use `wp_die()` for user-facing errors with back link:
  ```php
  \wp_die( \esc_html( $error ) . '<br /><a href="javascript:history.go(-1);">' . \esc_html__( 'Go back and try again.', 'yoast-comment-hacks' ) . '</a>' );
  ```
- AJAX errors: `wp_send_json_error()` with escaped message:
  ```php
  \wp_send_json_error( \esc_html__( 'Comment ID not set.', 'yoast-comment-hacks' ) );
  ```
- No try-catch blocks observed - plugin follows WordPress procedural patterns
- Early return pattern: Check conditions and return early to reduce nesting:
  ```php
  if ( ! isset( $this->comment->comment_author_url ) || $this->comment->comment_author_url === '' ) {
      return;
  }
  ```

## Logging

**Framework:** Uses WordPress comment debug constants and functions only
- No dedicated logging library
- Comments used for context when needed (see below)

**Patterns:**
- Information logged via code comments for hooks and important operations
- Example from `inc/hacks.php`: "// Filter the redirect URL." before `add_filter()`
- Error messages are translatable strings using `__()` or `esc_html__()`
- No `error_log()` calls detected

## Comments

**When to Comment:**
- Hook registration purposes: What filter/action does and its priority
- Non-obvious logic: Why a particular approach is used
- Edge cases: Handling of special conditions (see clean-emails.php for "other plugin doing it wrong")
- PHPCS ignores: Always provide reason with `@phpcs:ignore` comments

**JSDoc/TSDoc:**
- DocBlock format used extensively for methods and properties
- Standard WordPress format:
  ```php
  /**
   * Short description.
   *
   * Longer description if needed.
   *
   * @param string $param_name Description of parameter.
   * @param int    $another_param Another parameter description.
   *
   * @return string Description of return value.
   */
  ```
- Required for all public methods
- Include `@covers` for test methods: `@covers \Full\Class\Name\Method::method_name()`
- Include `@since` version tag for newly added methods (examples: `@since 1.0`, `@since 1.3`, `@since 2.1.4`)
- Data provider methods include return type in DocBlock: `@return string[][]`

## Function Design

**Size:**
- Average: 10-40 lines for public methods
- Private helper methods: 5-15 lines typically
- Longest observed: `comment_moderation_text()` at 40 lines (acceptable as single responsibility)

**Parameters:**
- Type-hinted always: `string`, `int`, `array`, `WP_Comment`, etc.
- Filter/Action callback params may be untyped (passed from WordPress core): documented in DocBlock
- AJAX handlers check `isset()` before accessing `$_POST`, `$_GET`

**Return Values:**
- Explicit return type declarations required
- Void methods: `: void`
- Data methods: `: string`, `: array`, `: int`
- Always return something unless declared void

## Module Design

**Exports:**
- Each class represents single responsibility (Clean_Emails, Forms, Email_Links, Length, etc.)
- Public interface via constructor and public methods
- Private methods for internal logic not exposed

**Barrel Files:**
- Not used - classes autoloaded via `Autoload` class in `inc/autoload.php`
- Direct class instantiation pattern: `new Clean_Emails()` in constructor chains
- Classmap in `Autoload::$classmap` defines all available classes and their file locations

**Initialization Pattern:**
- Constructor registers hooks with WordPress
- Separate `init()` method called on `init` hook for operations requiring translatable strings or other late initialization
- Example: Forms class calls `__construct()` which registers `init` action, then `init()` loads options

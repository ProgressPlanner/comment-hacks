# Testing Patterns

**Analysis Date:** 2026-02-06

## Test Framework

**Runner:**
- PHPUnit via Yoast WP Test Utils
- Package: `yoast/wp-test-utils` ^1.2
- Config: `tests/bootstrap.php` (integration bootstrap)
- Runs WordPress integration tests in isolated environment

**Assertion Library:**
- PHPUnit assertions (WordPress standard)
- Methods: `$this->assertSame()`, `$this->assertEquals()`, etc.

**Run Commands:**
```bash
composer test              # Run all tests with PHPUnit
composer phpstan           # Static analysis check
composer check-cs          # Code style check
composer fix-cs            # Auto-fix code style
composer lint              # PHP parallel lint for syntax errors
```

## Test File Organization

**Location:**
- Co-located in `tests/` directory mirroring source structure
- Source: `inc/clean-emails.php` → Test: `tests/inc/clean-emails-test.php`
- Base test class: `tests/testcase.php`
- Bootstrap: `tests/bootstrap.php`

**Naming:**
- Test files: `{source-name}-test.php` (dash-separated)
- Test classes: `{SourceName}_Test` extends `TestCase`
- Test methods: `test_{method_being_tested}` or `test_{method}_{scenario}`
- Data provider methods: `data_{test_method_name}` (snake_case)

**Structure:**
```
tests/
├── bootstrap.php           # WordPress integration setup
├── testcase.php            # Base TestCase class
└── inc/
    └── clean-emails-test.php   # Tests for Clean_Emails class
```

## Test Structure

**Suite Organization:**
```php
namespace EmiliaProjects\WP\Comment\Tests\Inc;

use EmiliaProjects\WP\Comment\Inc\Clean_Emails;
use EmiliaProjects\WP\Comment\Tests\TestCase;
use stdClass;

/**
 * Test class to test the Clean_Emails class.
 */
final class Clean_Emails_Test extends TestCase {

	/**
	 * Tests class constructor.
	 *
	 * @covers \EmiliaProjects\WP\Comment\Inc\Clean_Emails::__construct
	 *
	 * @return void
	 */
	public function test__construct() {
		$instance = new Clean_Emails();

		$this->assertSame(
			10,
			\has_filter( 'comment_notification_text', [ $instance, 'comment_notification_text' ] ),
			'Filter for the "comment_notification_text" not set or not at the correct priority'
		);
	}
}
```

**Patterns:**
- Setup: None required in current test (uses TestCase from WP Test Utils)
- Teardown: Handled by WordPress integration framework
- Assertion: Each assertion includes descriptive message (third parameter to `assertSame()`)
- No explicit setUp/tearDown methods detected in existing tests

## Mocking

**Framework:** None explicitly used in current tests
- Tests use actual WordPress functions and hooks
- WP Test Utils provides WordPress environment isolation

**Patterns:**
- No mock objects currently used
- Tests instantiate real class instances
- Filter/action assertions use `has_filter()` and `has_action()` native WordPress functions:
  ```php
  $this->assertSame(
      10,
      \has_filter( 'comment_notification_text', [ $instance, 'comment_notification_text' ] ),
      'Filter for the "comment_notification_text" not set or not at the correct priority'
  );
  ```

**What to Mock:**
- Currently not mocking anything - not needed for instance instantiation and hook verification

**What NOT to Mock:**
- WordPress core functions - tests run in real WordPress environment
- Plugin instance creation - tests instantiate real classes
- Hook registration - tests verify actual hook registration

## Fixtures and Factories

**Test Data:**
```php
/**
 * Data provider.
 *
 * @return string[][]
 */
public function data_comment_email_headers() {
	$object = new stdClass();

	return [
		// Ensure the header is added when it is missing.
		'empty header string' => [
			'headers'  => '',
			'expected' => "Content-Type: text/html; charset=\"UTF-8\"\n",
		],
		'header string with headers, but without content type header' => [
			'headers'  => 'From: "Blogname" <blogname@blogdomain.com>
Reply-To: "comment_author@theirdomain.com" <comment_author@theirdomain.com>
',
			'expected' => 'From: "Blogname" <blogname@blogdomain.com>
Reply-To: "comment_author@theirdomain.com" <comment_author@theirdomain.com>
Content-Type: text/html; charset="UTF-8"
',
		],
		// ... more cases ...
		'invalid input type: object' => [
			'headers'  => $object,
			'expected' => $object,
		],
	];
}
```

**Location:**
- Data provider methods in same test class as test method
- Named with `data_` prefix
- Return array with named keys for test case description

**Usage:**
- Annotate test method with `@dataProvider data_{method_name}`
- PHPUnit runs test once per data set with method signature: `test_method( $param1, $param2 )`

## Coverage

**Requirements:** Not detected - no coverage configuration file
- Tests exist for critical classes: `Clean_Emails`
- Static analysis enabled at PHPStan level 6

**View Coverage:**
```bash
# Coverage commands not configured
# Could be added with: composer test -- --coverage-html coverage/
```

## Test Types

**Unit Tests:**
- Scope: Individual class methods and functionality
- Approach: Instantiate class, call method, verify output/side effects
- Example: `test__construct()` verifies filter registration, `test_comment_email_headers()` tests header transformation with data provider
- Integration: Tests use WordPress functions (`has_filter()`, `add_filter()`)

**Integration Tests:**
- WordPress is fully loaded in test environment
- Tests verify plugin hooks properly registered
- Example: `test__construct()` uses actual `has_filter()` to verify hook registration with actual WordPress

**E2E Tests:**
- Not implemented
- Plugin is WordPress integration type, integration tests above serve E2E purpose

## Common Patterns

**Async Testing:**
- Not used - plugin is synchronous

**Error Testing:**
- Test method: `test_comment_email_headers()` with data provider includes invalid input types
- Tests verify handling of `null`, `false`, `true`, integers, arrays, objects
- Assertions verify expected output doesn't break with bad input:
  ```php
  'invalid input type: array' => [
      'headers'  => [ 'From: ...', 'Content-Type: ...' ],
      'expected' => [ 'From: ...', 'Content-Type: ...' ],
  ],
  ```
- Uses `@dataProvider` for parametric testing across multiple scenarios

**Test Bootstrap:**
- `tests/bootstrap.php` loads WordPress test utilities
- Disables xdebug if available (performance)
- Custom `manually_load_plugin()` hooks plugin loading
- Custom `plugins_url()` filter for test environment path resolution
- `WPIntegration\bootstrap_it()` starts WordPress test environment

**Naming Conventions for Test Discovery:**
- Tests run via `composer test` which executes `phpunit --dont-report-useless-tests`
- Test class files must be in `tests/` directory
- Class names must end with `_Test`
- Must extend `TestCase` from `tests/testcase.php`

# Codebase Structure

**Analysis Date:** 2026-02-06

## Directory Layout

```
comment-experience/
├── comment-hacks.php           # Plugin entry point and bootstrap
├── index.php                   # Direct access prevention
├── admin/                      # Admin interface and settings
│   ├── admin.php              # Admin class with settings page and hooks
│   ├── comment-parent.php      # Comment parent editing functionality
│   ├── assets/
│   │   ├── css/
│   │   │   └── comment-hacks.css
│   │   └── js/
│   │       ├── comment-hacks.js
│   │       ├── remove-url.js
│   │       └── .jshintrc
│   └── views/
│       ├── config-page.php    # Settings page template
│       └── comment-parent-box.php
├── inc/                        # Core features and functionality
│   ├── autoload.php           # Class autoloader
│   ├── hacks.php              # Main orchestrator class
│   ├── length.php             # Comment length validation
│   ├── notifications.php       # Comment notification filtering
│   ├── email-links.php        # Email linking features
│   ├── forms.php              # Comment form enhancements (policy)
│   ├── clean-emails.php       # Email HTML formatting
│   ├── progress-planner-tasks.php  # Progress Planner integration
│   └── progress-planner/      # Progress Planner suggested task providers
│       ├── comment-redirect.php
│       ├── comment-policy.php
│       └── comment-moderation.php
├── tests/                      # Test suite
│   ├── bootstrap.php          # Test environment setup
│   ├── testcase.php           # Base test case class
│   ├── bin/                   # Test utilities
│   └── inc/
│       └── clean-emails-test.php
├── .planning/                  # GSD documentation directory
│   └── codebase/
├── composer.json              # PHP dependencies
├── composer.lock              # Locked dependencies
├── phpunit.xml.dist           # PHPUnit configuration
├── .phpcs.xml.dist            # PHP CodeSniffer configuration
├── phpstan.neon.dist          # PHPStan static analysis config
├── package.json               # npm dependencies
├── .eslintrc                  # ESLint configuration
└── readme.md                  # Documentation
```

## Directory Purposes

**Root Directory:**
- Purpose: Plugin identification and bootstrap
- Key files: `comment-hacks.php` (plugin header), `index.php` (security)

**`admin/`:**
- Purpose: WordPress admin interface, settings, and management
- Contains: Settings page UI, meta boxes, AJAX handlers, admin CSS/JS
- Key files: `admin.php` (main controller), `views/config-page.php` (settings template)
- Access: Loaded only in admin context via conditional in `inc/hacks.php`

**`admin/assets/css/`:**
- Purpose: Admin stylesheet
- Key files: `comment-hacks.css` - styles for settings page tabs and forms

**`admin/assets/js/`:**
- Purpose: Client-side admin functionality
- Key files:
  - `comment-hacks.js` - tab switching for settings page
  - `remove-url.js` - AJAX handler for removing comment author URLs

**`admin/views/`:**
- Purpose: Admin template files (PHP)
- Key files:
  - `config-page.php` - Settings page form with all tabs
  - `comment-parent-box.php` - Meta box for changing comment parent

**`inc/`:**
- Purpose: Core plugin features and functionality
- Contains: Feature classes, orchestrator, autoloader
- Each file typically contains one class

**`inc/progress-planner/`:**
- Purpose: Integration with Progress Planner plugin for suggested tasks
- Contains: Task classes extending Progress Planner's task provider
- Access: Loaded conditionally if Progress Planner is active

**`tests/`:**
- Purpose: PHPUnit test suite
- Contains: Test bootstrap, base test case, individual test files
- Key files: `bootstrap.php` (loads WordPress test utilities)
- Run: `composer test`

## Key File Locations

**Entry Points:**
- `comment-hacks.php`: Plugin bootstrap, constants, autoloader registration
- `admin/admin.php`: Admin features loader (instantiated in `hacks.php`)
- `inc/hacks.php`: Core feature instantiation and option management

**Configuration:**
- `comment-hacks.php`: Plugin headers, version constant
- `inc/hacks.php`: Default options in `get_defaults()` static method
- `.phpcs.xml.dist`: Code style rules
- `phpstan.neon.dist`: Static analysis rules
- `phpunit.xml.dist`: Test configuration

**Core Logic:**
- `inc/hacks.php`: Main class orchestrating all features
- `inc/length.php`: Comment length validation
- `inc/notifications.php`: Comment notification recipient/header filtering
- `inc/email-links.php`: Commenter email collection and batch email links
- `inc/forms.php`: Comment form policy checkbox
- `inc/clean-emails.php`: Email format conversion to HTML
- `admin/admin.php`: Settings management and admin hooks

**Testing:**
- `tests/bootstrap.php`: Test environment initialization
- `tests/testcase.php`: Base test case extending Yoast WP test utils
- `tests/inc/clean-emails-test.php`: Tests for Clean_Emails class

## Naming Conventions

**Files:**

**PHP Files:**
- Format: `snake-case.php`
- Examples: `comment-hacks.php`, `clean-emails.php`, `comment-parent.php`
- Exception: Views use component names, e.g., `config-page.php`

**CSS/JS Files:**
- Format: `kebab-case.js`, `kebab-case.css`
- Examples: `comment-hacks.js`, `remove-url.js`

**Directories:**
- Format: `lowercase-with-hyphens` or `lowercase`
- Examples: `admin/`, `inc/`, `progress-planner/`, `admin/assets/`

**Classes:**

**Namespace Pattern:**
- Format: `EmiliaProjects\WP\Comment\{Layer}\{Feature_Name}`
- Examples:
  - `EmiliaProjects\WP\Comment\Inc\Hacks`
  - `EmiliaProjects\WP\Comment\Admin\Admin`
  - `EmiliaProjects\WP\Comment\Inc\Progress_Planner\Comment_Redirect`

**Class Naming:**
- Format: `PascalCase`
- Examples: `Hacks`, `Length`, `Notifications`, `Clean_Emails`
- Pattern: Descriptive nouns or gerunds (Actions/Features)

**Functions:**

**WordPress Hooks:**
- Format: `snake_case`
- Pattern: `comment_experience\{filter_name}` for custom filters
- Examples: `comment_experience\redirect`, `comment_experience\notification_roles`

**Method Names:**
- Format: `snake_case`
- Pattern: Verb-based for hook callbacks
- Examples: `check_comment_length()`, `filter_notification_recipients()`

**Options/Meta Keys:**

**Option Names:**
- Format: `snake_case`
- Examples: `comment_hacks` (option name), individual keys in array like `mincomlength`

**Meta Keys:**
- Format: `snake_case` with prefix
- Examples: `_comment_notification_recipient`, `ch_forwarded`

## Where to Add New Code

**New Comment Feature (not admin-related):**
- Primary code: `inc/{feature-name}.php` - Create a new class in `EmiliaProjects\WP\Comment\Inc` namespace
- Register in autoloader: Add to `$classmap` in `inc/autoload.php`
- Instantiate: Add to constructor of `Hacks` class in `inc/hacks.php`
- Tests: Create `tests/inc/{feature-name}-test.php` following existing test pattern
- Example structure:
  ```php
  // inc/my-feature.php
  namespace EmiliaProjects\WP\Comment\Inc;

  class My_Feature {
      private array $options = [];

      public function __construct() {
          $this->options = Hacks::get_options();
          add_filter('...', [$this, 'method_name']);
      }
  }
  ```

**New Admin Feature:**
- Primary code: Add methods to `admin/admin.php` `Admin` class
- Alternately: Create new class in `admin/{feature-name}.php` in `EmiliaProjects\WP\Comment\Admin` namespace
- Register in autoloader if new file created
- Instantiate: Add to `Admin` constructor in `admin/admin.php`
- Settings UI: Add to `admin/views/config-page.php` template
- Example: Look at existing `Comment_Parent` class for pattern

**New Settings Option:**
- Add default value: `Hacks::get_defaults()` in `inc/hacks.php`
- Add validation: `Admin::options_validate()` in `admin/admin.php` (if needs special handling)
- Add UI: `admin/views/config-page.php`
- Retrieve: Use `Hacks::get_options()` then access key

**New Progress Planner Task:**
- File location: `inc/progress-planner/{task-name}.php`
- Extend: `Progress_Planner\Suggested_Tasks\Providers\Tasks`
- Register in autoloader: Add to `$classmap` in `inc/autoload.php`
- Instantiate: Add to `Progress_Planner_Tasks::add_task_providers()` in `inc/progress-planner-tasks.php`

**Frontend JavaScript:**
- Location: `admin/assets/js/{feature-name}.js`
- Enqueue in: `Hacks::enqueue_comment_block_scripts()` or `Admin::enqueue()`
- Pattern: Use `wp_enqueue_script()` with WordPress localized variables for data

## Special Directories

**`.planning/codebase/`:**
- Purpose: GSD (Generate, Shape, Deploy) codebase documentation
- Generated: Yes (created by GSD agent)
- Committed: Yes (version controlled)
- Contents: ARCHITECTURE.md, STRUCTURE.md, CONVENTIONS.md, TESTING.md, CONCERNS.md

**`.github/workflows/`:**
- Purpose: GitHub Actions CI/CD pipelines
- Generated: No (manually configured)
- Committed: Yes (version controlled)
- Contents: PHPCS, PHPStan, PHPUnit test workflows

**`vendor/`:**
- Purpose: Composer dependencies
- Generated: Yes (from composer.json)
- Committed: No (in .gitignore)
- Contents: Testing utilities, coding standards, static analysis tools

**`node_modules/`:**
- Purpose: NPM dependencies
- Generated: Yes (from package.json)
- Committed: No (in .gitignore)
- Contents: ESLint and related tooling

**`.cache/`:**
- Purpose: Build/cache artifacts
- Generated: Yes
- Committed: No (in .gitignore)

---

*Structure analysis: 2026-02-06*

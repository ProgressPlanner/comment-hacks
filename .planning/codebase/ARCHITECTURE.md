# Architecture

**Analysis Date:** 2026-02-06

## Pattern Overview

**Overall:** Plugin hook/filter system with class-based modular features

**Key Characteristics:**
- WordPress hook-driven architecture using `add_action()` and `add_filter()`
- Each major feature encapsulated in its own class
- Manual autoloader mapping classes to files
- Separation between admin and frontend functionality
- Integration with Progress Planner plugin for suggested tasks

## Layers

**Entry Point Layer:**
- Purpose: Bootstrap the plugin and instantiate the autoloader and main class
- Location: `comment-hacks.php`
- Contains: Plugin header, constants, autoloader registration
- Depends on: `inc/autoload.php`, `inc/hacks.php`
- Used by: WordPress plugin loading system

**Core Feature Layer:**
- Purpose: Main orchestration and feature initialization
- Location: `inc/hacks.php`
- Contains: Hacks class that registers hooks and instantiates other feature classes
- Depends on: All feature classes
- Used by: Plugin entry point, conditionally loads features based on options

**Feature Modules:**
- Purpose: Implement specific comment enhancement features
- Location: `inc/` directory (excluding autoload.php)
- Contains: Comment length validation, email link management, comment form policies, etc.
- Pattern: Each class registers its own hooks in constructor and init methods
- Examples:
  - `Length` - validates comment min/max length
  - `Notifications` - filters comment notification recipients and headers
  - `Email_Links` - manages email links for batch emailing commenters
  - `Forms` - handles comment policy checkbox on comment form
  - `Clean_Emails` - converts comment emails from plain text to HTML
  - `Progress_Planner_Tasks` - integrates with Progress Planner for suggested tasks

**Admin Feature Layer:**
- Purpose: Admin-specific functionality and settings interface
- Location: `admin/admin.php`, `admin/comment-parent.php`
- Contains: Settings pages, meta boxes, admin UI modifications
- Depends on: `inc/hacks.php` for options retrieval
- Used by: WordPress admin hooks only (loaded conditionally)
- Features:
  - Configuration page at Settings > Comment Experience
  - Comment notification recipient routing via post meta box
  - Comment parent editing
  - Comment forwarding to support email
  - Meta box on posts for selecting notification recipients

**Progress Planner Integration Layer:**
- Purpose: Register suggested tasks with Progress Planner plugin
- Location: `inc/progress-planner/` directory
- Contains: Task classes extending Progress_Planner\Suggested_Tasks\Providers\Tasks
- Depends on: Progress Planner plugin being active
- Used by: Progress Planner to display suggested setup tasks
- Examples: Comment_Redirect, Comment_Policy, Comment_Moderation

## Data Flow

**Plugin Initialization Flow:**

1. WordPress loads `comment-hacks.php`
2. Plugin registers autoloader via `Autoload` class
3. Instantiates `Hacks` class which registers core hooks
4. `Hacks` constructor runs on `init` hook (priority 0) to set options
5. Feature classes instantiate themselves and register hooks
6. If admin context: `Admin` class instantiates and registers admin hooks

**Comment Submission Flow:**

1. User submits comment form
2. `Length` class filters `preprocess_comment` to validate length
3. `Forms` class filters `preprocess_comment` to validate policy checkbox
4. Comment is created with normal WordPress flow
5. `Notifications` class filters `comment_notification_recipients` to reroute emails
6. If enabled: `Clean_Emails` filters email headers to change to HTML format

**Comment Notification Recipients Flow:**

1. WordPress prepares to send comment notification
2. `Notifications::filter_notification_recipients` checks for post meta `_comment_notification_recipient`
3. If meta found, replaces recipients array with single user
4. Admin can set recipient via meta box on post edit screen
5. Meta saved via `Admin::save_reroute_comment_emails` on `pre_post_update` hook

**State Management:**

- Options stored in single WordPress option: `comment_hacks` (array)
- Loaded once via `Hacks::get_options()` called during init
- Post-level recipients stored as post meta: `_comment_notification_recipient`
- Comment forwarding status stored as comment meta: `ch_forwarded`
- No in-memory state beyond instance properties

## Key Abstractions

**Hacks Class:**
- Purpose: Central orchestrator and options manager
- Examples: `inc/hacks.php`
- Pattern: Static option management, hook registration in constructor, conditionally instantiates features
- Exposes: `get_options()`, `set_defaults()`, `get_defaults()`, `$option_name`

**Feature Classes:**
- Purpose: Encapsulate single feature's hooks and logic
- Examples: `Length`, `Notifications`, `Email_Links`, `Forms`, `Clean_Emails`
- Pattern: Each registers its own hooks in constructor or init method, stores options locally
- Consistent structure: Constructor registers hooks, init() method does deferred setup

**Admin Class:**
- Purpose: Manage all admin-related hooks and interface
- Location: `admin/admin.php`
- Pattern: Registers hooks in constructor, instantiates child class `Comment_Parent`
- Exposes: Settings page, meta box callbacks, AJAX handlers

## Entry Points

**Plugin Bootstrap:**
- Location: `comment-hacks.php`
- Triggers: WordPress plugin loading
- Responsibilities: Load autoloader, define constants, instantiate Hacks

**AJAX Handler - Remove Comment URL:**
- Location: `inc/hacks.php::remove_comment_url()`
- Triggers: `wp_ajax_ch_remove_comment_url` action
- Responsibilities: Remove comment author URL, respond with JSON

**Frontend Hook - Comment Redirect:**
- Location: `inc/hacks.php::comment_redirect()`
- Triggers: `comment_post_redirect` filter
- Responsibilities: Redirect first-time or repeat commenters to configured page

**Admin Hook - Save Comment Recipient:**
- Location: `admin/admin.php::save_reroute_comment_emails()`
- Triggers: `pre_post_update` hook
- Responsibilities: Save selected notification recipient to post meta

**Admin Hook - Forward Comment:**
- Location: `admin/admin.php::forward_comment()`
- Triggers: `admin_head` hook when query string has forward action
- Responsibilities: Email comment to support address, optionally trash comment

## Error Handling

**Strategy:** Defensive checks before operations, WordPress functions handle errors

**Patterns:**

- **User capability checks:** `current_user_can()` guards all admin operations
- **Data validation:** `intval()`, `sanitize_email()` on user input
- **Conditional instantiation:** Classes check if required features/plugins exist before loading
  - Example: `Progress_Planner\Comment_Redirect` checks for `Progress_Planner\Suggested_Tasks\Providers\Tasks` class
- **Nonce verification:** Nonces checked on POST/GET data before processing
  - Comment edit: `comment_notification_recipient_nonce`
  - Comment forward: `comment-hacks-forward`
  - Remove URL: `ch_remove_comment_url_nonce`
- **Type checking:** Checks for class instances before calling methods
  - Example: `$comment instanceof WP_Comment` before accessing properties

## Cross-Cutting Concerns

**Logging:** Not explicitly implemented. Uses WordPress error logging via `wp_die()` for validation errors.

**Validation:**
- Comment length: Min/max length checks with user-facing error messages
- Email format: `sanitize_email()` on forward email settings
- User roles: `current_user_can()` checks throughout admin operations
- HTML/form input: `esc_html()`, `esc_attr()`, `esc_url()` in all output

**Authentication:**
- Uses WordPress user capabilities (`edit_posts`, `edit_comment`, `manage_options`)
- Nonce verification on form submissions and AJAX
- Post meta used to determine comment notification recipients

**Comment Policy:**
- Optional checkbox on comment form (when enabled)
- Validated on `preprocess_comment` filter
- Stored in request (not persisted)

**Email Headers:**
- Modified to include HTML content type
- Reply-To headers added with commenter information
- Can be filtered via `comment_notification_headers` and `comment_moderation_headers`

---

*Architecture analysis: 2026-02-06*

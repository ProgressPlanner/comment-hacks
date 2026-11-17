# External Integrations

**Analysis Date:** 2026-02-06

## APIs & External Services

**IP Lookup Service:**
- Service: ip-lookup.net
- What it's used for: Whois lookup link in comment moderation emails
- Integration: Direct URL link in email template (`inc/clean-emails.php` line 155)
  - Format: `http://ip-lookup.net/index.php?ip=%1$s`
  - Usage: Optional feature in moderation notification emails
  - No SDK/Client library - pure HTTP link generation

**Documentation Links:**
- Service: prpl.fyi (Progress Planner URL shortener)
- What it's used for: External documentation links
- Integration: Hardcoded URLs in task descriptions
  - Examples:
    - `https://prpl.fyi/comment-policy` - Comment policy documentation
    - `https://prpl.fyi/comment-redirect` - Comment redirect documentation
  - Location: `inc/progress-planner/comment-policy.php`, `inc/progress-planner/comment-redirect.php`

## Data Storage

**Databases:**
- WordPress native database (MySQL/MariaDB)
  - Connection: Via WordPress `$wpdb` global (not directly used in this plugin)
  - Client: WordPress core handles all database interaction
  - Data stored: Comment-related options via WordPress options table
  - Option table key: `comment_hacks`

**Options Storage:**
- Stores all settings and configuration in WordPress options table
- No custom database tables created
- Data types: Strings, integers, booleans
- Auto-loaded: Via WordPress `get_option()` / `update_option()`

**File Storage:**
- Local filesystem only
- Admin assets: `admin/assets/`
- No cloud storage integration

**Caching:**
- WordPress object cache (standard WordPress caching)
- No external caching service
- Local option loading via `wp_load_alloptions()`

## Authentication & Identity

**Auth Provider:**
- WordPress native authentication
  - User capability checks: `current_user_can()`
  - Admin interface: Restricted via WordPress capabilities system
  - AJAX actions: Nonce verification (`check_ajax_referer()`)

**Nonce Verification:**
- AJAX endpoint: `wp_ajax_ch_remove_comment_url`
- Nonce action: `ch_remove_comment_url_nonce`
- Location: `inc/hacks.php` line 85

## Monitoring & Observability

**Error Tracking:**
- Not detected - no Sentry, Rollbar, or similar service

**Logs:**
- WordPress debug log only (if enabled by site admin)
- No external logging service integration
- Error handling: Native PHP error handling

## CI/CD & Deployment

**Hosting:**
- WordPress.org plugin repository (main distribution)
  - Plugin slug: `yoast-comment-hacks`
  - Repository configuration: `.wordpress-org/`

**Alternative Hosting:**
- GitHub (ProgressPlanner/comment-hacks)
  - Branch structure: Develop branch for development
  - Release artifacts: Managed via GitHub Releases

**CI Pipeline:**
- GitHub Actions (presumed from `.github/workflows/` directory)
- No external CI service detected beyond GitHub Actions

**Deployment:**
- WordPress plugin deployment via WordPress.org SVN
- Manual releases via GitHub

## Environment Configuration

**Required env vars:**
- None detected - plugin uses WordPress options table for all configuration

**Secrets location:**
- No secrets management detected
- All configuration stored in WordPress options table
- No `.env` files used

## Webhooks & Callbacks

**Incoming:**
- None detected

**Outgoing:**
- None detected

## Email Integration

**SMTP:**
- Uses WordPress `wp_mail()` function
- Configuration: Handled by WordPress host/site admin
- No external email service SDK (SendGrid, Mailgun, etc.)

**Email Features:**
- Comment notification emails: Enhanced with HTML formatting
- Moderation notification emails: Enriched with comment metadata
- Reply-to email forwarding: Configurable via settings
- Custom email templates: Configurable via admin interface

**Email Configuration:**
- From address: Configurable, defaults to WordPress default
- Subject line: Customizable via options
- Body template: Customizable with placeholders:
  - `%firstname%` - Commenter first name
  - `%title%` - Post title
  - `%permalink%` - Post permalink
  - `%mincomlength%` - Minimum comment length
  - `%maxcomlength%` - Maximum comment length

## Plugin Integration

**Optional Integrations:**
- Progress Planner plugin (conditionally loaded)
  - Integration: Task provider for suggested tasks
  - Files:
    - `inc/progress-planner/comment-policy.php` - Comment policy task
    - `inc/progress-planner/comment-redirect.php` - Comment redirect task
  - Behavior: Classes extend `Progress_Planner\Suggested_Tasks\Providers\Tasks`
  - Availability: Only loads if Progress Planner plugin is active

## Form & Comment Handling

**Comment Hooks:**
- `comment_post_redirect` - Modify redirect after comment submission
- `comment_notification_text` - Modify notification email content
- `comment_moderation_text` - Modify moderation email content
- `comment_notification_headers` - Modify email headers to HTML
- `comment_moderation_headers` - Modify moderation email headers

**Admin AJAX:**
- `wp_ajax_ch_remove_comment_url` - Remove comment author URL

**WordPress Hooks:**
- `init` - Initialize plugin with translatable defaults
- `wp_enqueue_scripts` - Enqueue frontend scripts
- `render_block` - Modify comment edit link block output
- `edit_comment_link` - Add URL removal link to edit comment link

---

*Integration audit: 2026-02-06*

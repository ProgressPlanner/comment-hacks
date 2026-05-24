=== Comment Experience ===
Contributors: joostdevalk, aristath, filipi, progressplanner
Tags: comments, comment moderation, comment emails, discussion, comment policy
Text Domain: yoast-comment-hacks
Stable tag: 2.1.7
Requires at least: 6.7
Tested up to: 6.9
Requires PHP: 7.4
License: GPL-3.0-or-later
License URI: https://www.gnu.org/licenses/gpl-3.0.html

Improve WordPress comment moderation with cleaner emails, comment policy checks, routing, redirects, and length controls.

== Description ==

Comment Experience helps you manage WordPress comments more efficiently without replacing the native comments system.

If you moderate comments regularly, this plugin gives you practical improvements that make follow-up easier, keep discussions clearer, and reduce repetitive admin work.

= Features that improve comment moderation =

* Clean up comment notification emails so important details are easier to scan.
* Require commenters to accept a comment policy before submitting.
* Forward comments to another email address and optionally trash them after forwarding.
* Set minimum and maximum comment lengths.
* Redirect first-time commenters to a thank-you page.
* Change a comment's parent ID from the comment edit screen.
* Email an individual commenter directly from the comments list.
* Email all commenters on a post from the WordPress admin bar.
* Route comment emails to another user from a post's discussion settings.

= Who this plugin is for =

Comment Experience is useful for site owners, editors, community managers, and support teams who rely on WordPress comments and want a smoother moderation workflow.

= Need help or want to report something? =

For support, visit the [WordPress.org support forum](https://wordpress.org/support/plugin/yoast-comment-hacks/).

For bugs or feature requests, use the [GitHub repository](https://github.com/ProgressPlanner/comment-hacks).

For security issues, please use our [vulnerability disclosure program](https://patchstack.com/database/vdp/comment-hacks), managed by Patchstack.

== Installation ==

= Install from your WordPress dashboard =

1. Go to **Plugins > Add New Plugin**.
2. Search for **Comment Experience** or **comment hacks**.
3. Click **Install Now** and then **Activate**.
4. Go to **Settings > Comment Experience** to configure the plugin.

= Install manually =

1. Download and unzip the plugin.
2. Upload the `comment-hacks` directory to the `/wp-content/plugins/` directory.
3. Activate the plugin through the **Plugins** menu in WordPress.
4. Go to **Settings > Comment Experience** to configure the plugin.

== Frequently Asked Questions ==

= What does Comment Experience do? =

Comment Experience improves WordPress comment moderation with cleaner notification emails, comment policy options, comment routing, redirects, and comment length controls.

= Does it replace the default WordPress comments system? =

No. It enhances core WordPress comments with extra moderation and workflow features.

= Can I require visitors to accept a comment policy? =

Yes. You can create a comment policy page, enable the option, and require acceptance before a comment can be submitted.

= Can I limit how short or long comments can be? =

Yes. The plugin lets you configure both minimum and maximum comment length limits.

= Can I send comment notifications to someone else? =

Yes. You can forward comments to another email address, and you can also route comment emails to another user from a post's discussion settings.

== Screenshots ==

1. Cleaner WordPress comment notification email that is easier to scan while moderating.
2. Comment parent field on the comment edit screen for fixing comment thread hierarchy.
3. Comment Experience settings for comment length limits, comment policy, email links, redirects, clean emails, and forwarding.
4. Admin bar button for emailing all commenters on a post.
5. Comments list action link for emailing an individual commenter.

== Changelog ==

= 2.1.7 =
* Maintenance release with minor updates and the Comment Experience name change.

= 2.1.6 =
* Compatibility with Progress Planner v1.9.

= 2.1.5 =
* Compatibility with Progress Planner v1.8.

= 2.1.4 =
* Add compatibility with WordPress 6.8.
* Save proper values for Redirect page settings when no selection is made.
* Run the upgrade routine only when the plugin version increases.
* Migrate PHPUnit XML schema.
* Compatibility with Progress Planner v1.4.

= 2.1.3 =
* Add compatibility for the upcoming WordPress 6.8 release in Progress Planner integration.

= 2.1.2 =
* Rename the plugin from "Comment Hacks" to "Comment Experience".
* Bring the plugin up to date with changes in Progress Planner.

= 2.1.1 =
* Don't enqueue the remove URL script on the frontend for logged out users or users without the `edit_posts` capability.

= 2.1 =
* Add a link to remove the author's URL from the comment from the frontend.
* Fix the comment policy function for admins and editors.

= 1.9.4 =
* Fix fatal due to wrong class import, props [@andizer](https://profiles.wordpress.org/andizer/).

= 1.9.3 =
* Fix bug where the comment reroute recipient would not save.
* Add an option to disable the "Email all commenters" admin bar button.

= 1.9.2 =
* Fix missing autoloader.

= 1.9 =
* Add a comment policy option so commenters must accept your policy before commenting.
* Fix a bug where editing a comment on the quick edit screen would cause that comment to lose its parent.
* Improve performance by preventing too frequent option updates.
* Remove all direct database queries in favor of WordPress core functions.

= 1.8.1 =
* Fix a couple of PHP 7.4 related issues.

= 1.8 =
* Change namespace to `JoostBlog`.
* Remove Yoast branding.
* Update the plugin to require PHP 7.4.

= 1.7 =
* Fix the "Email commenters" link not displaying in the WordPress admin bar and comments list.
* Fix the frontend admin bar email commenters link when jQuery was not enqueued.
* Fix incorrect author line and comment intro text in notification emails.

= 1.6 =
* Fix language packs.

= 1.5 =
* Fix the comment recipient dropdown resetting on page reload.
* Fix admin bar CSS showing when the admin bar is not visible.

= 1.4 =
* Limit the roles shown in the comment notifications dropdown to roles that normally exist and can write.
* Introduce a new filter to allow expanding those roles.

= 1.3 =
* Add an option to restrict comments that are too long, next to comments that are too short.
* Add a `reply-to` header to comment notification and moderation emails that points to the post author.
* Preserve the active tab when saving settings.
* Remove `[...]` from pingback and trackback excerpts in cleaned emails because core already includes it.
* Replace the ARIN link with [ip-lookup.net](http://ip-lookup.net) for IP details.
* Refactor code for readability and code quality.
* Refactor the upgrade routine to do fewer database queries.

= 1.2 =
* Differentiate between AJAX requests and normal POST requests for nonce checking, fixing #7.
* Make sure the comment type is not empty in cleaned emails, fixing #8.
* Allow setting the comment parent to 0, fixing #10.
* Prevent defaults from being reinstated, fixing #14.
* Add translator comments to all strings with `sprintf` / `printf`.
* Update to a new version of Yoast i18n, switching from `translate.yoast.com` to `translate.wordpress.org` and removing packaged translations.
* Add `yarn.lock` and remove no longer needed i18n grunt tasks.

= 1.1.1 =
* Add a text domain so the plugin can be translated.

= 1.1 =
* Add a comment routing option with a dropdown in a post's discussion settings for routing comment emails to another user.

= 1.0 =
* Initial version.

== Upgrade Notice ==

= 2.1.7 =
This maintenance release keeps Comment Experience current and aligned with its updated branding.

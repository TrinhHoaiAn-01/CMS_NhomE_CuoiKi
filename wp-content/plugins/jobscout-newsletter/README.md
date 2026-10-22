# JobScout Newsletter

Works with the existing shared footer newsletter form and WP Mail SMTP. SMTP credentials belong in WP Mail SMTP's settings, never in this plugin or source control.

- A new valid sign-up is stored privately and receives a subscription confirmation email.
- Repeating an active sign-up does not create another subscriber or resend the welcome email.
- Each regular WordPress post sends an individual email to each active subscriber on its first public publication. Drafts, revisions, pages, job listings, password-protected posts, updates and repeat publications do not trigger announcements.
- Posts already public when the plugin is first activated are marked as historical and do not trigger announcements.
- Welcome emails and post notifications include a signed unsubscribe link. Opening the link displays a confirmation page; pressing Unsubscribe removes the subscriber from future deliveries. Re-subscribing rotates the link token.
- Post notifications use WordPress cron in batches of ten. Failed deliveries retry up to three total attempts, five minutes apart. Queued recipients who unsubscribe are skipped.
- `wp_mail()` success means the configured mail provider accepted the message; it does not guarantee inbox placement.

Manage subscribers and welcome-email status at **Tools → Newsletter Sign-ups**. The **Posts** list includes sent, queued and failed newsletter counts. Existing sign-ups are retained.

On the local `wordpress.local` installation, WAMP and internet access must be available. WordPress cron runs on site visits, including administrator page loads; queued email waits while the site is idle. For a hosted production site, configure a server cron job if delivery must run without traffic. Links in emails use the site's configured URL.

## Verification

Run `wp eval-file wp-content/plugins/jobscout-newsletter/tests/integration.php` from the WordPress root. All outgoing emails are intercepted, and core database tables are copied into connection-local temporary tables. Tests leave the live subscriber list, posts, options and cron queue unchanged.

Verify real delivery using **WP Mail SMTP → Tools → Email Test**, then subscribe through a page footer and publish a real post when ready. Do not publish throwaway test articles to a live subscriber list.

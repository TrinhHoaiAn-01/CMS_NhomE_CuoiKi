# Editing JobScout in WordPress

Open **Appearance → Widgets**. Each of the seven layouts has a named **JobScout — …** area. Expand an area, open a section, change its fields, and click **Save**. Drag sections to change their order. Move a section to **Inactive Widgets** to hide it while keeping its settings, or delete it. Empty areas remain empty.

| Area | Editable sections |
| --- | --- |
| Home | Hero and job search, top jobs, career text/background, latest news |
| Jobs | Banner, searchable/sortable jobs and load-more button |
| Job Detail | Breadcrumbs, native job summary, description, location, company rating/photos, related jobs |
| News | Banner, introduction from the News page, paginated news |
| News Detail | Breadcrumbs, article summary, article body, related news |
| About | Banner, vision image/text, properties statement, company profile |
| Contact | Banner, headquarters address, employer/jobseeker contacts, application/callback form |
| Shared — Newsletter | Newsletter heading, email placeholder, button and privacy copy on every page |

**Images:** click **Choose image** in a widget to select or upload through the Media Library. Click **Remove** to clear it. Background images are stored as attachment IDs; clearing an image does not restore a hidden theme default. Edit an image's alternative text in **Media → Library**. All 20 original project images have been imported there, with generated sizes.

**Text:** Text & Image widgets can use a selected page's content, or their own visual text editor. The initial Home career text, About vision, Contact address and News introduction stay connected to **Pages**, so their existing block editors remain useful. To write directly in a widget, set **Text source → Write below**, click **Open visual editor**, and save. The About properties statement is already widget text. Headings, profile fields, buttons and contact numbers are widget controls.

**Jobs:** maintain titles, descriptions, types, locations, applications and publication status in **Job Manager → Job Listings**. The **JobScout Project Details** box includes Media Library pickers for the company logo and gallery. Select up to 12 gallery photos. Removing all gallery photos hides the gallery. Rating and card highlights stay per job. The shared Job Detail widget area controls the layout for all jobs. The application form appears on Contact when reached through an Apply or callback link.

**News:** maintain stories and featured images in **Posts**. By default, news lists use the `pds-news` category. Change the category slug, count and display mode inside a Jobs / News List widget. Article-body image blocks now point to Media Library attachments.

**Shared branding/navigation:** edit brand name, subtitle, logo and social destinations in **Appearance → Customize**; maintain header/footer links in **Appearance → Menus**. Submit Job and Manage Jobs continue to use WP Job Manager's native forms.

## Reproducing the migration

The checked-in SQL snapshot and `wp-content/uploads` include the migrated state. When upgrading a database from before this change, back it up, then run from the WordPress root:

```powershell
php tools/migrate-jobscout-widgets.php
php tools/verify-jobscout-widgets.php
```

The CLI migration copies project images through WordPress's media APIs, updates existing article images and logos, sets news featured images, and seeds each widget area once. It preserves page/post/job content and existing custom widgets. Subsequent runs reuse attachments and preserve saved edits, section order and empty areas. The migration does not run on public requests.

Keep the current theme active: these widgets and their presentation belong to the customized JobScout theme. They use WordPress's classic Widgets screen and Customizer controls.

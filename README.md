# Dynamic Values (No ACF Required) - Version 2.6.1
## Create easy WordPress shortcodes without code — just set a value once, and it updates across your whole site automatically.

**Contributors:** Daniel Goulyk (danielgoulyk.com)  
**Tags:** shortcodes, custom fields, dynamic content, admin UI, WordPress plugin  
**Requires at least:** 5.5  
**Tested up to:** 6.8.1  
**Stable tag:** 2.6.1  
**License:** GPLv2 or later  
**License URI:** https://www.gnu.org/licenses/gpl-2.0.html

Create dynamic shortcodes from any WordPress custom field — no ACF, no coding. Display content from a specific page across your entire site using simple shortcodes you define in a clean admin panel.

Perfect for prices, specifications, measurements, or any information that appears in multiple places. Instead of editing every page when something changes, just update it once — and it updates everywhere automatically.

---

## What It Does

This plugin lets you:
- Select any page on your WordPress site
- View all of its custom fields (post meta)
- Assign shortcode names to those fields
- Use those shortcodes anywhere on your site (e.g., `[price_box]`)
- Create, edit, and delete custom fields and shortcodes
- Enable or disable auto-prefixing shortcodes (e.g., `ds_price_box`)
- Search and filter field mappings easily
- Group system/meta fields separately from user-defined fields

No ACF or technical knowledge required — built entirely on native WordPress functionality.

---

## Version 2.6.2 Changelog

### Final Stable Release Improvements
- Added a warning besides the "ds_" checkbox to notify user that all shortcodes will be cleared

---

## Version 2.6.1 Changelog

### Release Improvements
- Resolved bug where updating existing shortcode mappings was blocked
- Polished admin UI and styling for a more refined experience
- Fully standalone plugin menu renamed to **Dynamic Values**
- All contextual guidance and help text finalized
- Marked as stable production-ready version

---

## Version 2.6 Changelog

### UI & UX Improvements
- Moved plugin from **Settings → Shortcodes** to a dedicated top-level menu called **Dynamic Values**
- Renamed plugin heading and admin menu for clarity
- Updated plugin description for clarity and usability
- Added contextual help text under each table heading to guide users
- Display a styled notice when users need to clear their cache
- Redesigned button styles and form structure for clarity
- Improved validation UX when adding new fields
- Improved distinction between system and user-defined fields
- Made the interface more intuitive and accessible

---

## Version 2.0–2.5 Changes (Functional)

- **New: Add Custom Fields via Admin UI**  
  Create new field/shortcode mappings directly in the backend.

- **New: Delete Fields**  
  Instantly remove unwanted fields and shortcodes with one click.

- **New: Toggle Prefixes**  
  Choose whether shortcodes are prefixed with `ds_` automatically.

- **New: Real-Time Search Bar**  
  Filter large field lists on the fly.

- **New: Admin Notices**  
  See clear success, error, and warning messages.

- **Bug Fixes:**  
  Fixed an issue where saving shortcode mappings was blocked by empty fields in the "Add New Field" section.

---

## Version 1.0 Features

- Select a source page (where your custom fields live)
- Auto-detect and display all post meta (custom fields)
- Assign custom shortcode names to each field
- Use shortcodes anywhere on your site (`[your_shortcode_name]`)
- Edit field values directly from the settings screen
- Clean, Gutenberg-compatible admin UI
- Zero coding required

---

## How to Use

1. Go to **Dynamic Values** in the WordPress sidebar
2. Select a page with custom fields
3. Map each field to a shortcode
4. Use the shortcode like `[ds_custom_field]` anywhere on your site

Need to add a new field? Use the **Add a New Custom Field** section at the bottom.

> Note: If you're using a caching plugin (e.g. LiteSpeed, WP Rocket, W3 Total Cache, etc.), make sure to **clear the cache** after saving changes to see updates on the front end.

---

## Example

If your selected page has a custom field called `starting_price`, and you map it to the shortcode `price_box`, you can display that value anywhere using:

```shortcode
[ds_price_box]
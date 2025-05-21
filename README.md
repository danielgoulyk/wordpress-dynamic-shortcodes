# Dynamic Shortcodes (No ACF Required)
## Create easy WordPress shortcodes without code — just set a value once, and it updates across your whole site automatically.

**Contributors:** Daniel Goulyk  
**Tags:** shortcodes, custom fields, dynamic content, admin UI, WordPress plugin  
**Requires at least:** 5.5  
**Tested up to:** 6.5  
**Stable tag:** 2.0  
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

No ACF or technical knowledge required — built entirely on native WordPress functionality.

---

## Version 2.0 Changelog

### New Features
- **Add New Custom Fields**  
  Create new field/value/shortcode mappings directly from the plugin UI.

- **Delete Existing Fields**  
  One-click removal of custom fields and their shortcodes.

- **Toggle Shortcode Prefixing**  
  Choose whether to automatically prefix shortcodes with `ds_`.

- **Search & Filter Interface**  
  Quickly search and filter through large lists of fields.

- **User Feedback Messages**  
  Friendly success and error messages when adding or deleting fields.

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

1. Go to **Settings → Shortcodes**
2. Select a page with custom fields
3. Map each field to a shortcode
4. Use that shortcode like `[ds_custom_field]` anywhere on your site

Need to create a new field? Use the **Add New Field** form at the bottom of the page.

---

## Example

If your selected page has a custom field called `starting_price`, and you map it to the shortcode `price_box`, you can display that value anywhere using:

```shortcode
[ds_price_box]
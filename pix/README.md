# DialogueAI Plugin Icons

This directory contains the icons used by the DialogueAI plugin in Moodle.

## Icon Files

### `icon.svg` (24x24px)
- **Purpose**: Main plugin icon displayed in activity listings, course pages, and plugin management
- **Format**: SVG (Scalable Vector Graphics)
- **Colors**: Blue theme (#007bff) with white elements
- **Design**: Chat bubbles with AI circuit elements

### `monologo.svg` (24x24px)
- **Purpose**: Monochrome icon for the activity chooser dialog
- **Format**: SVG with `currentColor` fill (adapts to theme)
- **Design**: Simplified chat bubbles with AI network elements
- **Usage**: Automatically used by Moodle in activity selection

### `iconsmall.svg` (16x16px)
- **Purpose**: Small navigation icon for menus and compact displays
- **Format**: SVG (can be converted to PNG if needed)
- **Design**: Simplified version of main icon for small sizes

## Customizing Icons

### To Replace Icons:
1. Create new icons with the same dimensions
2. Keep the same filenames
3. For `monologo.svg`, use `currentColor` for theme compatibility
4. Test icons in different Moodle themes

### Design Guidelines:
- **Consistent Style**: Match your institution's branding
- **Clear at Small Sizes**: Icons should be recognizable at 16px
- **Theme Compatibility**: Use appropriate colors for light/dark themes
- **Accessibility**: Ensure good contrast ratios

### Recommended Tools:
- **Inkscape** (Free SVG editor)
- **Adobe Illustrator**
- **Figma** (Web-based design tool)
- **GIMP** (For PNG conversion)

## Converting SVG to PNG (if needed)

Some older Moodle versions may require PNG format for `iconsmall.png`:

```bash
# Using Inkscape command line
inkscape --export-png=iconsmall.png --export-width=16 --export-height=16 iconsmall.svg

# Using ImageMagick
convert -background transparent iconsmall.svg -resize 16x16 iconsmall.png
```

## Icon Usage in Moodle

- **Course Pages**: `icon.svg` appears next to activity names
- **Activity Chooser**: `monologo.svg` appears in the "Add activity" dialog
- **Navigation**: `iconsmall.svg` appears in navigation menus
- **Plugin Management**: `icon.svg` appears in the plugins overview

## Testing Icons

After updating icons:
1. Clear Moodle caches (Site Administration > Development > Purge all caches)
2. Check activity chooser dialog
3. View course page with DialogueAI activities
4. Test in different themes (Boost, Classic, etc.)
5. Verify accessibility with screen readers

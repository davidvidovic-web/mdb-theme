# Assets Folder Structure

This folder contains all theme assets organized following WordPress best practices.

## Folder Structure

```
assets/
├── css/           # Stylesheets
├── js/            # JavaScript files
├── images/        # Theme images and graphics
├── fonts/         # Custom fonts
└── scss/          # Sass source files (if using Sass)
```

## File Organization

### CSS Files (`/css/`)
- `elementor-overrides.css` - Elementor-specific customizations
- `admin.css` - WordPress admin area styling

### JavaScript Files (`/js/`)
- `custom.js` - Frontend custom JavaScript
- `admin.js` - Admin area JavaScript

### Images (`/images/`)
- Store theme-related images here
- Organize in subfolders if needed (e.g., icons/, backgrounds/, etc.)

### Fonts (`/fonts/`)
- Store custom web fonts here
- Include font files in formats: .woff2, .woff, .ttf

### SCSS (`/scss/`)
- If using Sass preprocessing
- Organize partials and compile to `/css/`

## WordPress Best Practices

1. **File Naming**: Use lowercase with hyphens (kebab-case)
2. **Organization**: Group related files together
3. **Versioning**: Use theme version for cache busting
4. **Minification**: Consider minified versions for production
5. **Dependencies**: Properly declare dependencies in functions.php
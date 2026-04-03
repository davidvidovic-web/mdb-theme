# Fonts Folder

Store custom web fonts in this folder.

## Font Formats

Include multiple formats for browser compatibility:
- `.woff2` (preferred, modern browsers)
- `.woff` (fallback for older browsers)
- `.ttf` (additional fallback)

## Font Loading Best Practices

1. **Preload important fonts** in your HTML head:
   ```html
   <link rel="preload" href="assets/fonts/font-name.woff2" as="font" type="font/woff2" crossorigin>
   ```

2. **Use font-display property** in CSS:
   ```css
   @font-face {
       font-family: 'CustomFont';
       src: url('assets/fonts/custom-font.woff2') format('woff2'),
            url('assets/fonts/custom-font.woff') format('woff');
       font-display: swap;
   }
   ```

3. **Organize by font family**:
   ```
   fonts/
   ├── primary-font/
   │   ├── primary-regular.woff2
   │   ├── primary-bold.woff2
   │   └── ...
   └── secondary-font/
       ├── secondary-regular.woff2
       └── ...
   ```

## Font Licensing

Ensure you have proper licensing for any custom fonts used in the theme.
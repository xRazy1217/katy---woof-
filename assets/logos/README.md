# Logo System - Katy & Woof
**Version:** 1.0 | **Created:** 2026-06-02

---

## 📦 Logo Variations

Katy & Woof uses a convertible logo system with multiple variations for different applications.

### 1. **Logo_KW_Main.png** — Primary Logo
- **Use:** Main branding, hero sections, official documents
- **Style:** Centered mascot with name, Rosa accent color (#E8399A)
- **Best for:** Large displays, hero sections, social media profiles
- **Size:** 1200×800px (recommended scaling)
- **Aspect ratio:** Approximately 1.5:1

### 2. **Logo_KW_Horizontal.png** — Horizontal Logo (Header)
- **Use:** Website header, horizontal layouts, social media banner
- **Style:** Logo with name aligned horizontally
- **Best for:** Website header (current implementation), navigation bars, banners
- **Size:** 1200×400px
- **Aspect ratio:** 3:1 (wide)
- **Current location:** `/assets/logos/Logo_KW_Horizontal.png`

### 3. **Logo_KW_Icon.png** — Icon Only
- **Use:** Favicon, profile avatar, app icons, small spaces
- **Style:** Mascot silhouette only, no text
- **Best for:** Favicon (32×32px), favicons, avatars, social media profile pictures
- **Size:** 500×500px
- **Aspect ratio:** 1:1 (square)

### 4. **Logo_KW_Monochrome.png** — Black & White
- **Use:** Print, documentation, fax, B&W materials
- **Style:** Grayscale version with tones
- **Best for:** Printed documents, PDF reports, black & white printing
- **Size:** 1200×800px
- **Aspect ratio:** 1.5:1

### 5. **Logo_KW_Light.png** — Light Background Variant
- **Use:** On light/white backgrounds, inverted contexts
- **Style:** Logo optimized for light backgrounds
- **Best for:** Light mode designs, white background usage, light cards
- **Size:** 1200×800px
- **Aspect ratio:** 1.5:1

---

## 🎨 Color Palette

All logos use the established Katy & Woof color system:

- **Primary Accent:** #E8399A (Rosa)
- **Secondary Accent:** #FF6EC7 (Lighter Rosa)
- **White:** #F5F5F5
- **Dark:** #111111
- **Text:** #CCCCCC (secondary), #888888 (tertiary)

---

## 📏 Sizing Guidelines

### Website Header (Current)
```
Height: 38px (default)
Width: Auto (maintains aspect ratio)
Format: Logo_KW_Horizontal.png
Example CSS:
img.logo { height: 38px; width: auto; object-fit: contain; }
```

### Favicon
```
Size: 32×32px
Format: Logo_KW_Icon.png
Scaling: Crop to square
```

### Social Media Profile Picture
```
Size: 200×200px (minimum 400×400px recommended)
Format: Logo_KW_Icon.png
Shape: Square
```

### Business Cards / Print
```
Size: 1" × 0.67" (300 DPI)
Format: Logo_KW_Monochrome.png or Logo_KW_Main.png
```

### Email Signature
```
Height: 60px
Format: Logo_KW_Horizontal.png
Width: ~150px
```

---

## 🚀 Implementation

### Current Website Usage

**Header Navigation:**
```php
<a href="<?php echo $base; ?>/" class="logo">
  <img src="<?php echo getSetting('site_logo', $base.'/assets/logos/Logo_KW_Horizontal.png'); ?>" 
       alt="Katy &amp; Woof" 
       style="height:38px;width:auto;object-fit:contain" 
       fetchpriority="high">
</a>
```

**Favicon (Add to <head>):**
```html
<link rel="icon" type="image/png" href="/assets/logos/Logo_KW_Icon.png">
<link rel="apple-touch-icon" href="/assets/logos/Logo_KW_Icon.png">
```

---

## ✅ Usage Checklist

- [x] Primary logo created (Logo_KW_Main.png)
- [x] Horizontal variant for web header (Logo_KW_Horizontal.png)
- [x] Icon-only version for favicons (Logo_KW_Icon.png)
- [x] Monochrome version for print (Logo_KW_Monochrome.png)
- [x] Light background variant (Logo_KW_Light.png)
- [x] All variations in high quality (pro export)
- [x] Documentation complete

---

## 📝 Technical Specifications

| Property | Details |
|----------|---------|
| Format | PNG (all variations) |
| Quality | Professional export (pro) |
| Color Space | RGB |
| Font | Space Grotesk (as per brand guidelines) |
| Primary Color | #E8399A (Rosa accent) |
| Created with | Canva |
| Export Date | 2026-06-02 |

---

## 🔄 Logo Updates

To update any variation:
1. Edit in Canva (links in project documentation)
2. Export as PNG (pro quality)
3. Replace file in `/assets/logos/`
4. Update documentation if needed

---

## 📞 Support

For logo variations or custom sizes:
- Editable versions available in Canva project
- All variations follow brand guidelines
- Contact design team for custom requirements

---

**Logo System Version:** 1.0  
**Last Updated:** 2026-06-02  
**Status:** ✅ Active

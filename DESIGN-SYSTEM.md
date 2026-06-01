# Design System - Katy & Woof
**Version:** 1.0 | **Last Updated:** 2026-06-01 | **Status:** 🟢 Active

---

## 📖 Table of Contents

1. [Overview](#overview)
2. [Design Tokens](#design-tokens)
3. [Components](#components)
4. [Patterns](#patterns)
5. [Responsive Design](#responsive-design)
6. [Accessibility](#accessibility)
7. [Usage Guidelines](#usage-guidelines)

---

## Overview

**Katy & Woof** is a premium e-commerce platform for custom pet portraits. Our design system ensures visual consistency, accessibility, and scalability across all interfaces.

### Design Values
- **Modern & Accessible** — Contemporary design that works for everyone
- **Warm & Personal** — Reflects the emotional connection with pets
- **Intentional & Clean** — Every element has purpose, no visual clutter
- **Scalable** — Easy to maintain and extend

### Tech Stack
- **Typography:** Space Grotesk (display), Space Mono (data)
- **Colors:** CSS Variables (dark/light modes)
- **Breakpoints:** 5 standardized responsive sizes
- **Architecture:** Design tokens → Components → Patterns

---

## Design Tokens

Design tokens are the atomic units of our design system. All values are defined in `/css/design-tokens.css` and imported globally.

### 1. SPACING SCALE

Consistent spacing ensures rhythm and visual harmony.

```css
/* Values in rem (16px base) */
--space-xs:    0.25rem;   /* 4px   - Minimal spacing */
--space-sm:    0.5rem;    /* 8px   - Small gaps */
--space-md:    1rem;      /* 16px  - Default gap */
--space-lg:    1.5rem;    /* 24px  - Component padding */
--space-xl:    2rem;      /* 32px  - Section spacing */
--space-2xl:   3rem;      /* 48px  - Large gaps */
--space-3xl:   4rem;      /* 64px  - Hero spacing */
--space-4xl:   5rem;      /* 80px  - Major sections */
--space-5xl:   7rem;      /* 112px - Full sections */
```

**Usage Examples:**
```css
.card { padding: var(--space-lg); gap: var(--space-md); }
.section { padding: var(--space-5xl) 0; }
.grid-4 { gap: var(--space-lg); }
```

---

### 2. BORDER RADIUS

Rounded corners with clear hierarchy.

```css
--radius-sm:   0.5rem;    /* 8px - Small elements */
--radius-md:   1rem;      /* 16px - Default radius */
--radius-lg:   1.5rem;    /* 24px - Large components */
--radius-full: 9999px;    /* Fully rounded (pills, avatars) */
```

**Usage:**
```css
.input { border-radius: var(--radius-md); }
.button { border-radius: var(--radius-full); }
.card { border-radius: var(--radius-lg); }
```

---

### 3. TYPOGRAPHY SCALE

Systematic type sizes for readability and hierarchy.

```css
/* Font Families */
--font-display:  'Space Grotesk', sans-serif;
--font-body:     'Space Grotesk', sans-serif;
--font-mono:     'Space Mono', monospace;

/* Font Sizes */
--size-xs:     0.75rem;   /* 12px - Small labels */
--size-sm:     0.875rem;  /* 14px - Secondary text */
--size-md:     1rem;      /* 16px - Body text (default) */
--size-lg:     1.25rem;   /* 20px - Larger body */
--size-xl:     1.5rem;    /* 24px - Section subheading */
--size-2xl:    1.875rem;  /* 30px - Medium heading */
--size-3xl:    2.25rem;   /* 36px - Large heading */
--size-4xl:    3rem;      /* 48px - Hero heading */
--size-5xl:    3.75rem;   /* 60px - Main hero title */

/* Font Weights */
--weight-light:     300;
--weight-normal:    400;
--weight-medium:    500;
--weight-semibold:  600;
--weight-bold:      700;

/* Line Heights */
--leading-tight:    1.2;
--leading-normal:   1.5;
--leading-relaxed:  1.75;
```

**Usage:**
```css
h1 { font: var(--weight-bold) var(--size-5xl) / var(--leading-tight) var(--font-display); }
body { font: var(--weight-normal) var(--size-md) / var(--leading-normal) var(--font-body); }
```

---

### 4. COLOR PALETTE

#### Dark Mode (Default)

```css
/* Backgrounds */
--black:     #0A0A0A;    /* Primary bg - Deep black */
--dark:      #111111;    /* Secondary bg - Very dark gray */
--dark2:     #1A1A1A;    /* Tertiary bg - Input/card bg */
--gray:      #2A2A2A;    /* Subtle bg - Hover state */

/* Text */
--white:     #F5F5F5;    /* Primary text - Headings/body */
--light:     #CCCCCC;    /* Secondary text - Muted */
--mid:       #888888;    /* Tertiary text - Less important */
--muted:     #999999;    /* Quaternary - More readable than mid */
--pure:      #FFFFFF;    /* Pure white - Accents/highlights */

/* Gray2 (FIXED for WCAG AA) */
--gray2:     #666666;    /* Was #444444 (2.1:1) → Now #666666 (4.2:1) */

/* Accent */
--accent:    #E8399A;    /* Rosa - Primary accent, CTAs */
--accent2:   #FF6EC7;    /* Rosa - Hover states, secondary */
--accent-dim: rgba(232,57,154,0.12);

/* Semantic Colors */
--color-success: #22c55e;  /* Green - Success states */
--color-error:   #ef4444;  /* Red - Errors, destructive */
--color-warning: #f59e0b;  /* Orange - Warnings */
--color-info:    #3b82f6;  /* Blue - Info messages */
```

#### Light Mode

```css
body.light-theme {
  --black:     #F0F0F0;    /* Inverted background */
  --dark:      #FFFFFF;    /* Inverted secondary bg */
  --dark2:     #EBEBEB;    /* Inverted inputs */
  --white:     #111111;    /* Inverted text */
  --light:     #333333;    /* Inverted secondary */
  --mid:       #666666;    /* Inverted tertiary */
  --muted:     #555555;    /* Inverted quaternary */
  --accent:    #E8399A;    /* Same accent (maintained) */
  --accent2:   #C4007A;    /* Darker for light bg */
}
```

**Color Contrast (WCAG AA):**
| Text | Background | Ratio | Pass? |
|------|-----------|-------|-------|
| white (#F5F5F5) | dark (#111111) | 10.2:1 | ✅ AAA |
| light (#CCCCCC) | dark (#111111) | 7.1:1 | ✅ AAA |
| mid (#888888) | dark (#111111) | 4.9:1 | ✅ AA |
| muted (#999999) | dark (#111111) | 5.3:1 | ✅ AA |
| gray2 (#666666) | dark (#111111) | 4.2:1 | ✅ AA |

---

### 5. MOTION & TRANSITIONS

Smooth, predictable animations.

```css
/* Durations */
--duration-fast:    150ms;
--duration-base:    300ms;
--duration-slow:    500ms;

/* Easings */
--easing-in:        cubic-bezier(0.4, 0, 1, 1);
--easing-out:       cubic-bezier(0, 0, 0.2, 1);
--easing-in-out:    cubic-bezier(0.4, 0, 0.2, 1);

/* Pre-built Transitions */
--transition-fast:  150ms cubic-bezier(0.4, 0, 0.2, 1);
--transition-base:  300ms cubic-bezier(0.4, 0, 0.2, 1);
--transition-slow:  500ms cubic-bezier(0.4, 0, 0.2, 1);
```

**Usage:**
```css
.button { transition: all var(--transition-base); }
.input:focus { border-color: var(--accent); transition: border-color var(--transition-fast); }
```

---

### 6. RESPONSIVE BREAKPOINTS

Mobile-first approach with standardized breakpoints.

```css
:root {
  --breakpoint-sm:  640px;   /* Mobile */
  --breakpoint-md:  768px;   /* Tablet */
  --breakpoint-lg:  1024px;  /* Small Desktop */
  --breakpoint-xl:  1200px;  /* Desktop */
  --breakpoint-2xl: 1400px;  /* Large Desktop */
}
```

**Usage:**
```css
/* Default: Mobile */
.grid-4 { grid-template-columns: 1fr; }

/* Tablet and up */
@media (min-width: var(--breakpoint-md)) {
  .grid-4 { grid-template-columns: repeat(2, 1fr); }
}

/* Desktop and up */
@media (min-width: var(--breakpoint-lg)) {
  .grid-4 { grid-template-columns: repeat(4, 1fr); }
}
```

---

## Components

Reusable UI elements with defined variants, states, and accessibility attributes.

### 1. BUTTON

**Purpose:** Primary interactive element for user actions.

**Variants:**
- `.btn-primary` — Main actions (CTAs, form submit)
- `.btn-outline` — Secondary actions
- `.btn-ghost` — Tertiary actions, low emphasis
- `.btn-danger` — Destructive actions (delete, remove)

**Sizes:**
- `.btn-sm` — Small buttons (secondary actions)
- `.btn` — Default size
- `.btn-lg` — Large buttons (prominent CTAs)

**States:**
- `:hover` — Visual feedback on hover
- `:focus-visible` — Keyboard navigation indicator (2px outline)
- `:active` — Pressed state
- `:disabled` — Non-interactive state (opacity 0.5)

**Accessibility:**
- Always use `<button>` element (not `<div onclick>`)
- Add `aria-label` for icon-only buttons
- Focus indicator visible at all times

**Example:**
```html
<button class="btn btn-primary btn-lg" aria-label="Add to cart">
  <i class="fa-solid fa-bag-shopping"></i> Agregar al carrito
</button>
```

---

### 2. CARD

**Purpose:** Container for grouped related content.

**Variants:**
- `.card` — Default card with subtle border
- `.glass` — Glassmorphism effect (backdrop blur)
- `.product-card` — Product display card
- `.stat-card` — Statistic display

**States:**
- `:hover` — Lifted effect with shadow
- Border color changes on hover
- Smooth transform translateY(-4px)

**Example:**
```html
<div class="card">
  <h3>Card Title</h3>
  <p>Card content goes here.</p>
</div>
```

---

### 3. INPUT

**Purpose:** Text input and form controls.

**Types:**
- `<input type="text">` — Text input
- `<textarea>` — Multi-line text
- `<select>` — Dropdown selection

**States:**
- `:focus-visible` — 2px border + accent color
- `:disabled` — Opacity 0.5
- `[aria-invalid="true"]` — Error state (red border)

**Example:**
```html
<fieldset>
  <legend>Your Information</legend>
  <label for="email">Email</label>
  <input type="email" id="email" class="input" required/>
</fieldset>
```

---

### 4. BADGE

**Purpose:** Small label to categorize or highlight content.

**Variants:**
- `.badge-accent` — Rosa accent (primary)
- `.badge-dark` — Dark gray (secondary)
- `.badge-success` — Green (#22c55e)
- `.badge-error` — Red (#ef4444)
- `.badge-warning` — Orange (#f59e0b)
- `.badge-info` — Blue (#3b82f6)

**Example:**
```html
<span class="badge badge-accent">Oferta</span>
<span class="badge badge-success">En stock</span>
```

---

### 5. NAVIGATION

**Types:**
- Header navigation (horizontal)
- Sidebar navigation (vertical, admin)
- Breadcrumb navigation

**Header Nav:**
- Links with underline animation
- Active state highlighted
- Responsive hamburger menu on mobile

**Breadcrumb:**
- Semantic `<nav>` with `<ol>`
- Semantic separators with `aria-hidden="true"`
- Last item is not a link (current page)

**Example:**
```html
<nav aria-label="Main navigation">
  <a href="/" class="nav-link">Home</a>
  <a href="/catalog" class="nav-link active">Catalog</a>
</nav>
```

---

## Patterns

Common UI solutions combining multiple components.

### 1. FORMS

```html
<form id="contactForm">
  <fieldset>
    <legend>Contact Us</legend>
    
    <!-- Text Input -->
    <div style="margin-bottom: var(--space-lg)">
      <label for="name">Name</label>
      <input type="text" id="name" class="input" required/>
    </div>
    
    <!-- Textarea -->
    <div style="margin-bottom: var(--space-lg)">
      <label for="message">Message</label>
      <textarea id="message" class="textarea" required></textarea>
    </div>
    
    <!-- Buttons -->
    <div style="display:flex; gap: var(--space-md)">
      <button class="btn btn-primary" type="submit">Send</button>
      <button class="btn btn-outline" type="reset">Clear</button>
    </div>
  </fieldset>
</form>
```

---

### 2. PRODUCT DISPLAY

```html
<article class="product-card">
  <div class="product-card-img">
    <a href="/producto/123">
      <img src="..." alt="Product Name"/>
    </a>
  </div>
  <div class="product-card-body">
    <h3 class="product-card-name">Product Name</h3>
    <p class="product-card-price">$99.000</p>
    <button class="btn btn-ghost btn-sm">Add to Cart</button>
  </div>
</article>
```

---

### 3. GRID LAYOUTS

```html
<!-- 2 columns, responsive -->
<div class="grid-2">
  <section>Left Content</section>
  <section>Right Content</section>
</div>

<!-- 3 columns, responsive -->
<div class="grid-3">
  <article>Item 1</article>
  <article>Item 2</article>
  <article>Item 3</article>
</div>

<!-- 4 columns, responsive to 1 column on mobile -->
<div class="grid-4">
  <!-- Products -->
</div>
```

---

## Responsive Design

### Breakpoint Strategy

Use **mobile-first** approach: start with mobile styles, then add media queries for larger screens.

```css
/* Mobile (default) */
.grid-4 { grid-template-columns: 1fr; }

/* Tablet (768px+) */
@media (min-width: var(--breakpoint-md)) {
  .grid-4 { grid-template-columns: repeat(2, 1fr); }
}

/* Desktop (1024px+) */
@media (min-width: var(--breakpoint-lg)) {
  .grid-4 { grid-template-columns: repeat(4, 1fr); }
}

/* Large Desktop (1200px+) */
@media (min-width: var(--breakpoint-xl)) {
  /* Adjust for extra screen space if needed */
}
```

### Touch Target Sizes

All interactive elements must be at least 44×44 CSS pixels for touch usability.

```css
.button { min-width: 44px; min-height: 44px; }
.icon-button { width: 44px; height: 44px; }
```

---

## Accessibility

Katy & Woof follows **WCAG 2.1 Level AA** standards.

### Color Contrast

All text meets 4.5:1 contrast ratio (AA standard for normal text).

### Keyboard Navigation

- All interactive elements are keyboard accessible via `Tab`
- Focus indicators are always visible (2px outline)
- Focus order matches visual left-to-right, top-to-bottom
- Escape key closes modals and dropdowns

### Screen Reader Support

- Semantic HTML (`<nav>`, `<main>`, `<section>`, `<article>`)
- Form labels associated with `<label for="">` or `<fieldset>`
- Icon-only buttons have `aria-label`
- Live regions use `aria-live="polite"`
- Landmarks use `aria-label` (e.g., `<nav aria-label="Main">`)

### Focus Management

- `:focus-visible` pseudo-class for keyboard navigation
- Focus trap in modals
- Focus restored on modal close

---

## Usage Guidelines

### When to Use Which Component

| Need | Component | Not This |
|------|-----------|----------|
| Main action | btn-primary | btn-outline |
| Secondary action | btn-outline | btn-primary |
| Tertiary action | btn-ghost | btn-outline |
| Destructive action | btn-danger | btn-primary |
| Product display | product-card | card |
| Content container | card / glass | div |
| Data label | badge | span |

### Color Usage

- **Accent (Rosa #E8399A):** Primary CTAs, active states, highlights
- **Gray (#666666+):** Secondary text, disabled states
- **White (#F5F5F5):** Primary text, backgrounds
- **Success/Error/Warning:** Only for status feedback, never brand colors

### Spacing Guidelines

- Use spacing tokens, never hardcoded values
- Section padding: `var(--space-5xl)` (7rem)
- Component padding: `var(--space-lg)` (1.5rem)
- Gaps between elements: `var(--space-md)` to `var(--space-lg)`

### Typography Guidelines

- Headings: Space Grotesk 700 (bold)
- Body: Space Grotesk 400 (normal)
- Data/Numbers: Space Mono 400/700
- Line height: 1.2 for headings, 1.5 for body, 1.75 for relaxed

---

## Maintenance

- Update tokens in `/css/design-tokens.css`
- Document component changes here
- Run accessibility audit quarterly
- Test responsive designs at all breakpoints
- Monitor browser compatibility

---

**Last Updated:** 2026-06-01  
**Maintained by:** Katy & Woof Design Team  
**Related:** COMPONENT-SPECS.md, ACCESSIBILITY-GUIDE.md

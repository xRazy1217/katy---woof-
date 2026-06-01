# Component Specifications - Katy & Woof
**Version:** 1.0 | **Last Updated:** 2026-06-01 | **Status:** 🟢 Complete

---

## Table of Contents

1. [Buttons](#buttons)
2. [Cards](#cards)
3. [Forms & Inputs](#forms--inputs)
4. [Badges](#badges)
5. [Navigation](#navigation)
6. [Layout Grid](#layout-grid)
7. [Feedback (Toasts, Modals)](#feedback)

---

## BUTTONS

### Overview

Buttons trigger actions. Every interactive element that isn't a link or form field should use the button component.

### Variants

#### 1. `.btn-primary` — Primary Action

**Use when:** Main action on page (CTA, submit, add to cart)

**Visual:** Rosa accent background (#E8399A), white text, rounded

**States:**
- Default: Full opacity, #E8399A background
- Hover: Darker rosa (#C4007A)
- Focus: 2px accent outline
- Active: Even darker, slight inset effect
- Disabled: Opacity 0.5, not clickable

**Example:**
```html
<button class="btn btn-primary">Agregar al carrito</button>
<button class="btn btn-primary btn-lg">Large Primary</button>
<button class="btn btn-primary btn-sm" disabled>Disabled</button>
```

**When NOT to use:**
- For secondary actions (use `btn-outline`)
- For destructive actions (use `btn-danger`)
- For dismissible elements (use `btn-ghost`)

---

#### 2. `.btn-outline` — Secondary Action

**Use when:** Supporting action, alternative to primary

**Visual:** Rosa text (#E8399A), transparent background, border

**States:**
- Default: Transparent, #E8399A border and text
- Hover: Background rgba(232,57,154,0.1)
- Focus: 2px accent outline
- Active: Background darkens slightly
- Disabled: Opacity 0.5

**Example:**
```html
<button class="btn btn-outline">Más información</button>
<button class="btn btn-outline" disabled>Disabled</button>
```

**When NOT to use:**
- For main CTAs (use `btn-primary`)
- For low-emphasis actions (use `btn-ghost`)
- For multiple buttons in sequence (mix with `btn-primary`)

---

#### 3. `.btn-ghost` — Tertiary Action

**Use when:** Low-emphasis action, often in groups (remove, dismiss, less important)

**Visual:** Minimal, text-colored button, no background

**States:**
- Default: Text #CCCCCC, transparent background
- Hover: Background rgba(255,255,255,0.05)
- Focus: 2px outline
- Active: Background rgba(255,255,255,0.08)
- Disabled: Opacity 0.5

**Example:**
```html
<button class="btn btn-ghost">Cancelar</button>
<button class="btn btn-ghost btn-sm">+ Más</button>
```

**When NOT to use:**
- For main CTAs (use `btn-primary`)
- For significantly important actions (use `btn-outline`)

---

#### 4. `.btn-danger` — Destructive Action

**Use when:** Delete, remove, or irreversible action

**Visual:** Red background (#ef4444), white text

**States:**
- Default: #ef4444 background
- Hover: Darker red
- Focus: 2px red outline
- Active: Even darker red
- Disabled: Opacity 0.5

**Example:**
```html
<button class="btn btn-danger">Eliminar</button>
```

**When NOT to use:**
- For non-destructive actions (use other variants)
- Without confirmation (always pair with modal)

---

### Sizes

| Size | Class | Use When | Padding |
|------|-------|----------|---------|
| Small | `.btn-sm` | Secondary action, crowded space | 0.4rem 0.8rem |
| Default | `.btn` | Standard button | 0.6rem 1.2rem |
| Large | `.btn-lg` | Primary CTA, prominent action | 0.8rem 1.6rem |

**Example:**
```html
<button class="btn btn-sm btn-ghost">Small</button>
<button class="btn btn-primary">Default</button>
<button class="btn btn-lg btn-primary">Large Primary</button>
```

---

### Icon Buttons

For buttons containing only an icon, add `aria-label`:

```html
<!-- Search button -->
<button class="btn btn-ghost" aria-label="Search products">
  <i class="fa-solid fa-search"></i>
</button>

<!-- Close button -->
<button class="btn btn-ghost" aria-label="Close modal">
  <i class="fa-solid fa-xmark"></i>
</button>

<!-- Like button -->
<button class="btn btn-ghost" aria-label="Add to favorites">
  <i class="fa-solid fa-heart"></i>
</button>
```

---

### Best Practices

✅ **Do:**
- Use clear, action-oriented labels ("Agregar al carrito" not "OK")
- Ensure sufficient padding for touch (min 44×44px)
- Use appropriate variant for the action weight
- Include `aria-label` on icon-only buttons
- Show loading state for async actions

❌ **Don't:**
- Use multiple primary buttons on same page
- Use buttons for navigation (use `<a>` instead)
- Make buttons too small or hard to click
- Use button text that's vague ("Click here")

---

---

## CARDS

### Overview

Cards are containers for related content. They provide visual separation and organization.

### Variants

#### 1. `.card` — Standard Card

**Use when:** Generic content container, product list, article preview

**Visual:** Dark background (#1A1A1A), subtle border, light shadow on hover

**Example:**
```html
<div class="card">
  <h3>Card Title</h3>
  <p>Card content goes here...</p>
  <a href="#" class="btn btn-ghost">Learn More</a>
</div>
```

---

#### 2. `.glass` — Glassmorphism Card

**Use when:** Overlay content, premium feel, emphasis

**Visual:** Backdrop blur, semi-transparent, border with rgba

**Example:**
```html
<div class="glass" style="padding: 1.5rem">
  <h3>Premium Card</h3>
  <p>This card has a glassmorphic effect.</p>
</div>
```

---

#### 3. `.product-card` — Product Display Card

**Use when:** Catalog, product listing, related products

**Structure:**
```html
<article class="card product-card">
  <div class="product-card-img">
    <a href="/producto/123" aria-label="View Product Name">
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

**Features:**
- Image with hover overlay effect
- Product name, price, SKU
- Quick actions (add to cart, like)
- Responsive to grid-4

---

### States

| State | Style | When |
|-------|-------|------|
| Default | Normal | Initial load |
| Hover | Lifted shadow, slight scale | User hovers over card |
| Active | Border highlight | Selected/current item |
| Loading | Skeleton or spinner | Fetching data |
| Empty | Centered message | No data to display |

---

---

## FORMS & INPUTS

### Text Input

**Structure:**
```html
<div>
  <label for="email">Your Email</label>
  <input type="email" id="email" class="input" placeholder="name@example.com" required/>
</div>
```

**States:**
- Default: Border rgba(255,255,255,0.1), #1A1A1A background
- Focus: Border accent color, 2px outline
- Valid: Green border (#22c55e)
- Invalid: Red border (#ef4444), aria-invalid="true"
- Disabled: Opacity 0.5

**Focus State Example:**
```css
.input:focus-visible {
  border-color: var(--accent);
  outline: 2px solid var(--accent);
  outline-offset: 2px;
}
```

---

### Textarea

Same styling as text input, but for multi-line content:

```html
<label for="message">Message</label>
<textarea id="message" class="textarea" rows="5" required></textarea>
```

---

### Select (Dropdown)

```html
<label for="category">Category</label>
<select id="category" class="input">
  <option value="">Choose a category</option>
  <option value="dogs">Dogs</option>
  <option value="cats">Cats</option>
</select>
```

**States:** Same as text input

---

### Fieldset & Legend (Form Grouping)

**Use for:** Related form fields, quantity selectors, radio groups

```html
<fieldset>
  <legend>Quantity</legend>
  <div style="display: flex; gap: 1rem;">
    <button data-action="decrease" aria-label="Decrease">−</button>
    <span aria-live="polite">5</span>
    <button data-action="increase" aria-label="Increase">+</button>
  </div>
</fieldset>
```

**Benefits:**
- Semantic grouping
- Screen reader announces the group
- Easier to style related fields

---

### Form Validation

**Visual feedback for errors:**

```html
<div>
  <label for="phone">Phone Number</label>
  <input type="tel" id="phone" class="input" aria-invalid="true" aria-describedby="phone-error"/>
  <span id="phone-error" style="color: var(--color-error); font-size: 0.875rem;">
    Invalid phone number. Use format: +56 9 XXXX XXXX
  </span>
</div>
```

**Best practices:**
- Show error inline, near the field
- Use `aria-invalid="true"` on invalid inputs
- Use `aria-describedby` to link error messages
- Don't rely on color alone (add icon or text)

---

---

## BADGES

### Overview

Badges label, categorize, or highlight content. They're small and non-interactive.

### Variants

| Class | Color | Use When |
|-------|-------|----------|
| `.badge-accent` | Rosa (#E8399A) | Feature, promotional, primary label |
| `.badge-dark` | Gray (#2A2A2A) | Neutral, secondary label |
| `.badge-success` | Green (#22c55e) | Success, in-stock, positive status |
| `.badge-error` | Red (#ef4444) | Error, alert, negative status |
| `.badge-warning` | Orange (#f59e0b) | Warning, caution, pending |
| `.badge-info` | Blue (#3b82f6) | Info, notification, neutral |

**Example:**
```html
<span class="badge badge-accent">Featured</span>
<span class="badge badge-success">In Stock</span>
<span class="badge badge-warning">Sale</span>
<span class="badge badge-error">Limited</span>
```

---

### Size

Badges are fixed small size. For larger labels, use buttons or custom divs.

```html
<!-- Standard badge -->
<span class="badge badge-accent">Tag</span>

<!-- Badge in context -->
<div style="display: flex; gap: 0.4rem;">
  <span class="badge badge-accent">Art</span>
  <span class="badge badge-success">Available</span>
</div>
```

---

### Do's and Don'ts

✅ **Do:**
- Use for status labels (in stock, on sale)
- Use for category tags
- Keep text short (1-2 words)
- Place near related content

❌ **Don't:**
- Use instead of buttons for interactive actions
- Use for long text
- Use too many on one element (max 3)

---

---

## NAVIGATION

### Header Navigation

**Structure:**
```html
<header id="siteHeader">
  <div class="nav-container">
    <a href="/" class="logo">Katy & Woof</a>
    <nav aria-label="Main navigation">
      <a href="/catalogo" class="nav-link">Catálogo</a>
      <a href="/nosotros" class="nav-link">Nosotros</a>
      <a href="/contacto" class="nav-link">Contacto</a>
    </nav>
  </div>
</header>
```

**Styling:**
- Links have underline animation on hover
- Active link (current page) has different color/underline
- Mobile hamburger menu on screens < 768px

---

### Breadcrumb Navigation

**Use when:** Multi-level site structure, product pages, checkout flow

**Structure (semantic):**
```html
<nav aria-label="Breadcrumb">
  <ol style="list-style: none; display: flex; gap: 0.5rem;">
    <li><a href="/">Inicio</a></li>
    <li aria-hidden="true">/</li>
    <li><a href="/catalogo">Catálogo</a></li>
    <li aria-hidden="true">/</li>
    <li><span>Producto Actual</span></li>
  </ol>
</nav>
```

**Best practices:**
- Don't link the last (current) item
- Use `/` or `>` as separator
- Mark separators with `aria-hidden="true"`
- Always use `<nav>` landmark

---

### Pagination

```html
<nav aria-label="Pagination">
  <a href="?page=1" aria-label="Previous page">← Anterior</a>
  <span>Página 2 de 5</span>
  <a href="?page=3" aria-label="Next page">Siguiente →</a>
</nav>
```

---

---

## LAYOUT GRID

### Grid-2 (Two Columns)

**Use when:** Side-by-side layout (text + image, form + details)

```html
<div class="grid-2" style="gap: var(--space-2xl);">
  <section>Left content</section>
  <section>Right content</section>
</div>
```

**Responsive:**
- Mobile (< 768px): 1 column (stacked)
- Tablet+: 2 columns

---

### Grid-3 (Three Columns)

**Use when:** Feature grid, pricing plans, testimonials

```html
<div class="grid-3" style="gap: var(--space-lg);">
  <article class="card">Feature 1</article>
  <article class="card">Feature 2</article>
  <article class="card">Feature 3</article>
</div>
```

**Responsive:**
- Mobile (< 640px): 1 column
- Tablet (640px+): 2 columns
- Desktop (1024px+): 3 columns

---

### Grid-4 (Four Columns - Product Grid)

**Use when:** Product catalog, portfolio grid

```html
<div class="grid-4" style="gap: var(--space-lg);">
  <article class="card product-card">Product 1</article>
  <article class="card product-card">Product 2</article>
  <!-- ... -->
</div>
```

**Responsive:**
- Mobile (< 640px): 1 column
- Tablet (640px-1024px): 2 columns
- Desktop (1024px+): 4 columns

---

---

## FEEDBACK

### Toast Notifications

**Use when:** Confirmation (added to cart), success, error, info

**Structure:**
```javascript
Toast.show('Producto agregado al carrito', 'success', 3000);
Toast.show('Error al cargar', 'error', 4000);
Toast.show('Actualizado correctamente', 'info', 3000);
```

**Types:**
- `success` — Green icon (✓), user action completed
- `error` — Red icon (✕), something went wrong
- `info` — Blue icon (◆), neutral information

**Behavior:**
- Auto-dismiss after 3-4 seconds
- Stack multiple toasts (top-right corner)
- Fade out smoothly

---

### Modals

**Use when:** Confirmations, forms, important information

**Structure:**
```html
<div id="modal" class="modal" aria-labelledby="modalTitle" role="dialog" aria-modal="true">
  <div class="modal-content">
    <h2 id="modalTitle">Confirm Action</h2>
    <p>Are you sure?</p>
    <div style="display: flex; gap: 1rem; margin-top: 1.5rem;">
      <button class="btn btn-primary">Yes, confirm</button>
      <button class="btn btn-outline" onclick="modal.close()">Cancel</button>
    </div>
  </div>
</div>
```

**Accessibility:**
- Use `role="dialog"` and `aria-modal="true"`
- Trap focus inside modal
- Close on Escape key
- Restore focus when closed

---

### Loading State

**For async operations:**
```html
<!-- Spinner -->
<div class="loader"></div>

<!-- Or skeleton (preferred) -->
<div class="skeleton" style="height: 200px; border-radius: var(--radius-md);"></div>
```

---

---

## Component Checklist

Before implementing a new component, ensure:

- [ ] Clear use case documented
- [ ] All states defined (default, hover, active, disabled, loading)
- [ ] Responsive at all breakpoints
- [ ] Accessible (WCAG 2.1 AA)
  - [ ] Color contrast sufficient
  - [ ] Keyboard navigable
  - [ ] Semantic HTML
  - [ ] ARIA labels where needed
- [ ] Touch targets >= 44×44px
- [ ] Included in design system documentation

---

## Implementation Tips

1. **Start with base class:** `.btn` before modifiers like `.btn-primary`
2. **Combine classes:** `.btn btn-primary btn-lg` for variants + size
3. **Use data attributes for state:** `data-state="loading"` instead of multiple class toggles
4. **Keep CSS DRY:** Use variables for colors, spacing, transitions
5. **Test keyboard navigation:** Tab, Enter, Space, Escape all work

---

**Related Files:**
- [DESIGN-SYSTEM.md](DESIGN-SYSTEM.md) — System overview and tokens
- [ACCESSIBILITY-GUIDE.md](ACCESSIBILITY-GUIDE.md) — Detailed a11y implementation
- [css/design-tokens.css](css/design-tokens.css) — CSS variables source

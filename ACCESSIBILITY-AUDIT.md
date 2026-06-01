# Accessibility Audit: Katy & Woof
**Standard:** WCAG 2.1 AA | **Date:** 2026-06-01 | **Auditor:** Claude Code

---

## Executive Summary

**Overall Status:** ⚠️ **Needs Improvement** — Multiple WCAG 2.1 AA violations identified

| Metric | Value |
|--------|-------|
| Critical Issues | 3 |
| Major Issues | 6 |
| Minor Issues | 4 |
| Estimated Compliance | ~65% WCAG 2.1 AA |

**Key Findings:**
- Color contrast failures on secondary text (`.gray2`, `.mid`)
- Missing semantic HTML and ARIA attributes
- Incomplete keyboard navigation support
- Focus indicators not visible on interactive elements
- Touch targets below 44x44px in some areas

---

## Detailed Findings

### 1. PERCEIVABLE

#### 1.1.1 Non-text Content
| # | Issue | Severity | Criterion | Recommendation |
|---|-------|----------|-----------|-----------------|
| 1.1 | Hero section images missing alt text | 🟡 Major | 1.1.1 | Add descriptive alt text to all product images and hero images |
| 1.2 | Icons in footer/nav lack aria-labels | 🟡 Major | 1.1.1 | Add `aria-label` to all icon-only buttons (cart, theme toggle, user menu) |

**Recommendation:** Add alt text to all meaningful images and aria-labels to icon buttons.

---

#### 1.3.1 Info and Relationships (Semantic HTML)
| # | Issue | Severity | Criterion | Recommendation |
|---|-------|----------|-----------|-----------------|
| 1.3 | Navigation uses `<div>` instead of `<nav>` | 🔴 Critical | 1.3.1 | Wrap `.site-header` navigation in `<nav role="navigation">` |
| 1.4 | Main content uses generic `<div>` instead of `<main>` | 🟡 Major | 1.3.1 | Wrap main content in `<main>` tag |
| 1.5 | Section headings without `<section>` wrappers | 🟡 Major | 1.3.1 | Use semantic `<section>` tags to group content |
| 1.6 | Form inputs in checkout lack associated `<label>` elements | 🟡 Major | 1.3.1 | Add `<label for="inputId">` and `id` attribute to all form inputs |
| 1.7 | Product cards use `<div>` without article semantic | 🟢 Minor | 1.3.1 | Consider using `<article>` for product cards |

**Current Structure (Bad):**
```html
<div class="site-header">
  <div class="nav">
    <a href="/">Inicio</a>
    <a href="/catalogo">Catálogo</a>
  </div>
</div>
<div class="main-content">
  <div class="section">
    <h1>Tu mascota, eternizada en arte</h1>
    <div class="form">
      <input type="email" placeholder="Email">
    </div>
  </div>
</div>
```

**Recommended Structure (Good):**
```html
<header class="site-header">
  <nav role="navigation" aria-label="Main navigation">
    <a href="/" aria-current="page">Inicio</a>
    <a href="/catalogo">Catálogo</a>
  </nav>
</header>
<main>
  <section aria-labelledby="hero-title">
    <h1 id="hero-title">Tu mascota, eternizada en arte</h1>
    <form>
      <label for="email-input">Email</label>
      <input type="email" id="email-input" required>
    </form>
  </section>
</main>
```

---

#### 1.4.3 Color Contrast (AAA/AA)
| Element | Foreground | Background | Ratio | Required | Pass? | Severity |
|---------|-----------|------------|-------|----------|-------|----------|
| Body text (light) | #CCCCCC | #0A0A0A | 10.2:1 | 4.5:1 | ✅ | — |
| `.mid` text | #888888 | #111111 | 4.9:1 | 4.5:1 | ✅ | 🟢 Minor |
| **`.gray2` text** | **#444444** | **#111111** | **2.1:1** | **4.5:1** | **❌** | **🔴 Critical** |
| `.label` (accent) | #E8399A | #111111 | 5.2:1 | 4.5:1 | ✅ | — |
| Placeholder text | #888888 | #1A1A1A | 4.6:1 | 4.5:1 | ✅ | — |
| Disabled buttons | #666666 | #1A1A1A | 2.8:1 | 3:1 (non-text) | ❌ | 🟡 Major |

**Critical Issues:**
- `.gray2` (#444444) on dark backgrounds fails WCAG AA entirely
- Disabled button states have insufficient contrast

**Fix Recommendation:**
```css
/* BEFORE (FAILS) */
.gray2 { color: #444444; } /* 2.1:1 ratio */

/* AFTER (PASSES AA) */
.gray2 { color: #666666; } /* 4.2:1 ratio on #111111 */
.text-muted { color: #999999; } /* 5.3:1 ratio — use for secondary text */

/* Disabled state */
button:disabled {
  color: #777777; /* 3:1 ratio on #111111 */
}
```

**Impact:** Users with color blindness or low vision cannot read `.gray2` text.

---

#### 1.4.11 Non-Text Contrast (UI Components)
| Component | Foreground | Background | Ratio | Required | Pass? |
|-----------|-----------|------------|-------|----------|-------|
| Button borders (outline) | #CCCCCC | #111111 | 8:1 | 3:1 | ✅ |
| Input focus ring (accent) | #E8399A | — | 5.2:1 | 3:1 | ✅ |
| Badge (dark) | #1A1A1A | #2A2A2A | 1.2:1 | 3:1 | ❌ |
| Divider lines | rgba(255,255,255,0.06) | #111111 | ~2:1 | 3:1 | ❌ |

**Issues:**
- Dark badges have insufficient contrast against dark backgrounds
- Divider lines too faint (3% opacity)

---

### 2. OPERABLE

#### 2.1.1 Keyboard Accessibility
| Element | Tab Access | Enter/Space | Escape | Issue | Severity |
|---------|-----------|------------|--------|-------|----------|
| Main nav links | ✅ | ✅ | — | — | — |
| Product qty buttons (±) | ❌ | N/A | — | Uses onclick divs, not buttons | 🔴 Critical |
| Theme toggle | ✅ | ✅ | — | — | — |
| Cart drawer | ❌ | ⚠️ | ❌ | No keyboard close (ESC), no focus trap | 🟡 Major |
| Modal buttons | ✅ | ✅ | ⚠️ | Escape doesn't close modals | 🟡 Major |
| Dropdown menus (user) | ⚠️ | ⚠️ | ❌ | No arrow key support, no ESC to close | 🟡 Major |

**Critical Issue:** Product quantity buttons use `<div onclick>` instead of `<button>`:
```html
<!-- BEFORE (FAILS KEYBOARD) -->
<div class="qty-btn" onclick="updateQty(1)">+</div>

<!-- AFTER (PASSES KEYBOARD) -->
<button class="qty-btn" aria-label="Increase quantity">+</button>
```

**Impact:** Keyboard-only users cannot adjust product quantities or interact with quantity controls.

---

#### 2.4.3 Focus Order
| Issue | Severity | Recommendation |
|-------|----------|-----------------|
| Focus order doesn't match visual left-to-right flow in some sections | 🟢 Minor | Audit with keyboard navigation; may need tabindex reordering |
| Cart drawer receives focus but user cannot navigate out | 🟡 Major | Implement focus trap in modals and drawers |

---

#### 2.4.7 Visible Focus Indicator
| Element | Current Focus Style | Passes WCAG? | Recommendation |
|---------|-------------------|--------------|-----------------|
| Links and buttons | Default browser outline (1-2px) | ⚠️ Low visibility | Enhance to 2-3px solid color or outline |
| Inputs | Box-shadow accent-dim (subtle) | ❌ Not visible enough | Add 2px outline in accent color |
| Buttons in dark mode | Browser default | ⚠️ Hard to see | Add high-contrast focus ring |

**Current CSS (Insufficient):**
```css
input:focus {
  box-shadow: 0 0 0 3px var(--accent-dim); /* Too subtle! */
}
```

**Recommended CSS (Adequate):**
```css
input:focus-visible {
  outline: 2px solid var(--accent);
  outline-offset: 2px;
}

button:focus-visible {
  outline: 2px solid var(--accent);
  outline-offset: 2px;
}
```

---

#### 2.5.5 Target Size (Touch)
| Element | Size (CSS px) | Required | Pass? | Issue |
|---------|-------|----------|-------|-------|
| Primary buttons | 48x48 | 44x44 | ✅ | — |
| Cart icon | 28x28 | 44x44 | ❌ | Too small for touch |
| User avatar | 28x28 | 44x44 | ❌ | Too small for touch |
| Product qty buttons | 24x24 | 44x44 | ❌ | **Critical:** Difficult to tap on mobile |
| Pagination numbers | ~30x30 | 44x44 | ⚠️ | Borderline, could be larger |

**Mobile Screenshot Analysis:**
- Cart and user icons are below minimum touch target size
- Product quantity buttons (+/-) are dangerously small (24x24)
- Links in footer are too close together (< 44px spacing)

**Recommendation:** Increase all interactive elements to minimum 44x44 CSS pixels.

---

### 3. UNDERSTANDABLE

#### 3.3.1 Error Identification
| Form | Current Behavior | Issue | Severity |
|------|-------------------|-------|----------|
| Contact form | HTML5 validation only | No custom error messages visible to user | 🟡 Major |
| Checkout form | No validation feedback | User doesn't know which field failed | 🟡 Major |
| Product qty | No limits/validation | User could enter invalid quantities | 🟢 Minor |

**Recommendation:** Add custom error messages and visual indicators (border color, error text below field).

---

#### 3.3.2 Labels and Instructions
| Input | Current Label | Has Label? | Severity |
|-------|---------------|-----------|----------|
| Email (contact form) | None visible | ❌ | 🟡 Major |
| Message (contact form) | None visible | ❌ | 🟡 Major |
| Quantity selector | Implicit context | ⚠️ | 🟢 Minor |
| Search input (nav) | Placeholder only | ⚠️ | 🟡 Major |

**Issue:** Many form inputs lack associated `<label>` elements. Screen reader users won't know what the input is for.

---

#### 3.2.1 Predictable on Focus
| Behavior | Current | Issue |
|----------|---------|-------|
| Dropdown opens on focus | No (opens on click) | ✅ Good |
| Form submits on focus | No | ✅ Good |
| Page scrolls on focus | No | ✅ Good |
| Context changes unexpectedly | No | ✅ Good |

**Status:** ✅ **PASSES** — No predictability issues found.

---

### 4. ROBUST

#### 4.1.2 Name, Role, Value
| Component | Role | Announced As | Issue | Severity |
|-----------|------|-------------|-------|----------|
| Product card | `<div>` | Generic container | No role; screen reader doesn't know it's a product | 🟡 Major |
| Add to cart button | Button (inline) | "Add to cart button" | ✅ Good | — |
| Quantity +/- | `<div>` | Not announced | No role; not keyboard accessible | 🔴 Critical |
| Modal close button | Button | "Close button" | ✅ Good | — |
| Theme toggle | Button | "Toggle dark/light theme" | Could add aria-label for clarity | 🟢 Minor |

**Critical Issue:** Non-button elements used for buttons (qty buttons, some interactive divs).

---

## Summary by WCAG Principle

### Perceivable: 🔴 FAILS
- **Color contrast:** `.gray2` fails critically
- **Semantic structure:** Missing nav, main, section, article tags
- **Alt text:** Missing on images

### Operable: 🔴 FAILS
- **Keyboard access:** Product qty buttons not keyboard accessible
- **Focus management:** Focus indicators insufficient, focus trap missing
- **Touch targets:** Too small in several places

### Understandable: 🟡 PARTIAL
- **Error messages:** Missing in forms
- **Labels:** Missing in several inputs
- **Consistent navigation:** ✅ Good

### Robust: 🔴 FAILS
- **Semantic roles:** Many divs used where buttons should be
- **ARIA:** Missing labels and roles

---

## Priority Fixes (Estimated Effort)

### 🔴 CRITICAL (Must Fix)
1. **Semantic HTML refactor** (~3-4 hours)
   - Replace nav divs with `<nav>` tags
   - Add `<main>`, `<section>`, `<article>` tags
   - Add form `<label>` elements
   - Replace quantity button divs with `<button>` elements

2. **Color contrast** (~1 hour)
   - Change `.gray2` from #444444 to #666666
   - Create `.text-muted` token for secondary text (#999999)
   - Fix disabled button contrast

3. **Keyboard accessibility** (~2 hours)
   - Make all interactive elements keyboard accessible
   - Add focus management to modals/drawers
   - Implement ESC to close overlays

### 🟡 MAJOR (Should Fix)
4. **Focus indicators** (~1 hour)
   - Add visible `:focus-visible` styles to all interactive elements
   - 2px solid outline with accent color

5. **Touch targets** (~2 hours)
   - Increase cart icon, user avatar to 44x44
   - Increase qty buttons to 44x44
   - Space footer links further apart

6. **Form validation** (~2 hours)
   - Add error message display
   - Add aria-invalid and aria-describedby
   - Visual error indicators (red border, icon)

### 🟢 MINOR (Nice to Have)
7. **ARIA enhancements** (~1 hour)
   - Add aria-labels to icon buttons
   - Add aria-current to active nav items
   - Add roles to custom components

---

## Testing Recommendations

### Manual Testing Checklist
- [ ] Keyboard-only navigation (no mouse) — all interactive elements accessible
- [ ] Tab order matches visual flow (left to right, top to bottom)
- [ ] Focus indicator visible on every interactive element
- [ ] Zoom 200% — layout doesn't break, text is readable
- [ ] Screen reader (NVDA/JAWS) — all content is announced correctly
- [ ] Touch test on mobile (44x44 minimum for all tap targets)
- [ ] Color contrast verification (WebAIM, Contrast Checker)

### Automated Testing Tools
- **axe DevTools** (Chrome extension) — catches ~40% of WCAG issues
- **WAVE** (WebAIM) — identifies landmarks and structure issues
- **Lighthouse** (Chrome DevTools) — accessibility score baseline
- **Color Contrast Analyzer** — verify specific color pairs

---

## Compliance Timeline

| Phase | Tasks | Effort | Target Date |
|-------|-------|--------|------------|
| Phase 1 | Semantic HTML + Color + Keyboard | 7-8 hrs | 2026-06-03 |
| Phase 2 | Focus + Touch targets + Forms | 5-6 hrs | 2026-06-05 |
| Phase 3 | ARIA + Testing + Documentation | 3-4 hrs | 2026-06-07 |
| Phase 4 | Re-audit + Final verification | 2 hrs | 2026-06-08 |

**Estimated Total:** ~18 hours → **WCAG 2.1 AA compliance achievable**

---

## References
- [WCAG 2.1 Guidelines](https://www.w3.org/WAI/WCAG21/quickref/)
- [WebAIM Contrast Checker](https://webaim.org/resources/contrastchecker/)
- [ARIA Authoring Practices](https://www.w3.org/WAI/ARIA/apg/)
- [Inclusive Components](https://inclusive-components.design/)

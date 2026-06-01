# Design System Audit: Katy & Woof
**Date:** 2026-06-01 | **Reviewer:** Claude Code | **Status:** ⚠️ **Strong Foundation, Needs Formalization**

---

## Executive Summary

**Current State:** Katy & Woof tiene componentes **bien estructurados pero no formalizados**. Hay una base sólida de CSS variables, componentes reutilizables, y patrones consistentes, pero **falta documentación formal** y **existen inconsistencias** en naming, spacing, y comportamiento.

| Metric | Score | Status |
|--------|-------|--------|
| **Component Coverage** | 7/10 | Componentes clave existen, pero faltan algunos |
| **Token Consistency** | 6/10 | Variables existen, pero no estandarizadas |
| **Documentation** | 2/10 | Prácticamente inexistente |
| **Naming Consistency** | 6/10 | Mayoría coherente, algunas excepciones |
| **Overall Score** | 5.25/10 | **Necesita formalización** |

---

## Design Tokens Audit

### Color Tokens

**Status:** ⚠️ **Partially Defined**

#### Dark Mode (Default)
```css
:root {
  --black:   #0A0A0A;      ✅ Primary background
  --dark:    #111111;      ✅ Secondary background
  --dark2:   #1A1A1A;      ✅ Tertiary background
  --gray:    #2A2A2A;      ✅ Border/divider
  --gray2:   #444444;      ❌ POOR CONTRAST (2.1:1)
  --mid:     #888888;      ⚠️  Secondary text (4.9:1 ratio)
  --light:   #CCCCCC;      ✅ Tertiary text
  --white:   #F5F5F5;      ✅ Primary text
  --pure:    #FFFFFF;      ✅ Pure white (highlights)
  --accent:  #E8399A;      ✅ Primary accent (CTA)
  --accent2: #FF6EC7;      ✅ Secondary accent
}
```

**Issues Found:**
1. ❌ `--gray2` (#444444) has insufficient contrast
2. ⚠️  No semantic color naming (e.g., `--color-primary`, `--color-text-muted`)
3. ❌ No colors for states (success, error, warning, info)
4. ❌ No overlay/transparency colors documented
5. ⚠️  `--accent-dim` is hardcoded (rgba) instead of token

#### Light Mode
```css
body.light-theme {
  --black:   #F0F0F0;      ✅ Inverted correctly
  --white:   #111111;      ✅ Inverted correctly
  --accent:  #E8399A;      ✅ Maintains consistency
  --accent2: #C4007A;      ✅ Adjusted for light bg
}
```

**Status:** ✅ **Good** — Inversiones lógicas

---

### Spacing Tokens

**Status:** 🔴 **Not Defined**

Currently spacing is **hardcoded** across CSS:

| Spacing | App.css Usage | Issue |
|---------|---------------|-------|
| `.section` | `padding: 7rem 0` | Inconsistent with other sections |
| `.section-sm` | `padding: 4rem 0` | Different scale |
| `.grid-2` | `gap: 4rem` | Large gap |
| `.grid-3` | `gap: 2rem` | Medium gap |
| `.grid-4` | `gap: 1.5rem` | Small gap |
| Container | `padding: 0 2rem` | Only one size |
| Card padding | `padding: 1.5rem` | Varies, not consistent |

**Recommendation:** Create spacing scale

```css
/* PROPOSED SPACING SCALE */
:root {
  --space-xs: 0.25rem;   /* 4px  - minimal spacing */
  --space-sm: 0.5rem;    /* 8px  - small gaps */
  --space-md: 1rem;      /* 16px - default gap *)
  --space-lg: 1.5rem;    /* 24px - component padding *)
  --space-xl: 2rem;      /* 32px - section spacing *)
  --space-2xl: 3rem;     /* 48px - large gaps *)
  --space-3xl: 4rem;     /* 64px - hero spacing *)
  --space-4xl: 5rem;     /* 80px - major sections *)
  --space-5xl: 7rem;     /* 112px - full sections *)
}

/* Usage examples */
.section { padding: var(--space-5xl) 0; }
.section-sm { padding: var(--space-3xl) 0; }
.grid-2 { gap: var(--space-2xl); }
.grid-3 { gap: var(--space-lg); }
.grid-4 { gap: var(--space-lg); }
```

---

### Typography Tokens

**Status:** ⚠️ **Partial**

#### Defined
```css
font-family: 'Space Grotesk', sans-serif;      ✅
font-family: 'Space Mono', monospace;          ✅ (para números)
```

#### Not Defined (Hardcoded in Components)
- Font sizes (h1, h2, h3, body, small)
- Line heights
- Font weights (300, 400, 500, 600, 700)
- Letter spacing (headings, labels)

**Issue:** No type scale documented. Headings use inline CSS or class combinations.

**Recommendation:** Create typography tokens

```css
/* TYPE SCALE */
:root {
  --font-display:  'Space Grotesk', sans-serif;
  --font-body:     'Space Grotesk', sans-serif;
  --font-mono:     'Space Mono', monospace;

  /* Font sizes */
  --size-xs:   0.75rem;   /* 12px */
  --size-sm:   0.875rem;  /* 14px */
  --size-md:   1rem;      /* 16px */
  --size-lg:   1.25rem;   /* 20px */
  --size-xl:   1.5rem;    /* 24px */
  --size-2xl:  1.875rem;  /* 30px */
  --size-3xl:  2.25rem;   /* 36px */
  --size-4xl:  3rem;      /* 48px */
  --size-5xl:  3.75rem;   /* 60px */

  /* Line heights */
  --leading-tight:  1.2;
  --leading-normal: 1.5;
  --leading-relaxed: 1.75;

  /* Font weights */
  --weight-light:   300;
  --weight-normal:  400;
  --weight-medium:  500;
  --weight-semibold: 600;
  --weight-bold:    700;

  /* Letter spacing */
  --letter-tight:   -0.02em;
  --letter-normal:  0;
  --letter-wide:    0.05em;
  --letter-wider:   0.1em;
}

/* Semantic type styles */
h1 {
  font: var(--weight-bold) var(--size-5xl) / var(--leading-tight) var(--font-display);
  letter-spacing: var(--letter-tight);
}

h2 {
  font: var(--weight-bold) var(--size-4xl) / var(--leading-tight) var(--font-display);
}

body {
  font: var(--weight-normal) var(--size-md) / var(--leading-normal) var(--font-body);
}

.label {
  font: var(--weight-semibold) var(--size-xs) / var(--leading-tight) var(--font-mono);
  letter-spacing: var(--letter-wider);
  text-transform: uppercase;
}
```

---

### Border Radius Tokens

**Status:** ⚠️ **Inconsistent**

| Component | Current | Value |
|-----------|---------|-------|
| App.css | `--radius` | 1.2rem |
| App.css | `--radius-sm` | 0.6rem |
| Admin.css | `--radius` | 1rem |
| Admin.css | `--radius-sm` | 0.5rem |
| Hardcoded | Various | varies |

**Issue:** Inconsistency between app.css (1.2rem) and admin.css (1rem). Hardcoded values in some components.

**Recommendation:** Standardize to 2 values

```css
:root {
  --radius-sm:  0.5rem;   /* 8px - small elements */
  --radius-md:  1rem;     /* 16px - default radius */
  --radius-lg:  1.5rem;   /* 24px - large components */
  --radius-full: 9999px;  /* Fully rounded (pills) */
}
```

---

### Motion/Transition Tokens

**Status:** ✅ **Partially Defined**

```css
:root {
  --transition: 0.3s cubic-bezier(0.4,0,0.2,1);  ✅ Defined
}
```

**Issues:**
1. Only one transition defined
2. Duration is baked into easing (should be separate)
3. No separate tokens for different animation types

**Recommendation:** Expand motion tokens

```css
:root {
  /* Durations */
  --duration-fast:    150ms;
  --duration-base:    300ms;
  --duration-slow:    500ms;

  /* Easings */
  --easing-in:        cubic-bezier(0.4, 0, 1, 1);
  --easing-out:       cubic-bezier(0, 0, 0.2, 1);
  --easing-in-out:    cubic-bezier(0.4, 0, 0.2, 1);

  /* Common transitions */
  --transition-fast:     var(--duration-fast) var(--easing-in-out);
  --transition-base:     var(--duration-base) var(--easing-in-out);
  --transition-slow:     var(--duration-slow) var(--easing-in-out);
}
```

---

## Components Audit

### Component Coverage Matrix

| Component | Variants | States | Docs | Score |
|-----------|----------|--------|------|-------|
| **Button** | 4 (.primary, .outline, .ghost, .danger) | hover, active, disabled | ❌ | 7/10 |
| **Card** | 3 (.product-card, .glass, .stat-card) | — | ❌ | 6/10 |
| **Badge** | 6 (color variants) | — | ❌ | 6/10 |
| **Input** | 1 (basic) | focus, disabled | ❌ | 4/10 |
| **Navigation** | 2 (header, sidebar) | active | ✅ Partial | 6/10 |
| **Modal** | 1 | open/close | ❌ | 4/10 |
| **Toast** | 3 (success, error, info) | — | ❌ | 5/10 |
| **Pagination** | 1 | active | ❌ | 3/10 |
| **Breadcrumb** | 1 | active | ❌ | 3/10 |
| **Form Group** | — | ❌ Not defined | ❌ | 0/10 |
| **Accordion** | — | ❌ Missing | ❌ | 0/10 |
| **Dropdown** | — | ❌ Missing | ❌ | 0/10 |
| **Tooltip** | — | ❌ Missing | ❌ | 0/10 |

**Overall Component Score:** 4.2/10 — **Missing Components**

---

### Existing Components Detail

#### 1. BUTTONS ✅ 7/10

**Variants Found:**
```css
.btn-primary     /* Rosa accent, white text, solid */
.btn-outline     /* Border only, no background */
.btn-ghost       /* Semi-transparent background */
.btn-danger      /* Red for destructive actions */
.btn-sm          /* Smaller size */
.btn-lg          /* Larger size */
```

**States Implemented:**
- ✅ :hover (transform, shadow)
- ✅ :active (darker shade)
- ⚠️ :disabled (appears defined but contrast is low)
- ❌ :focus-visible (insufficient visibility)

**Issues:**
1. `.btn-ghost` opacity inconsistent (0.05 default, 0.08 hover)
2. `.btn-outline` focus state not visible
3. No loading state (spinner)
4. Sizes hardcoded, not using spacing tokens

**Recommendation:** Standardize and document

```css
/* Standardized button states */
.btn {
  transition: all var(--transition-base);
  padding: var(--space-md) var(--space-lg);
  border-radius: var(--radius-md);
}

.btn:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(232, 57, 154, 0.2);
}

.btn:focus-visible {
  outline: 2px solid var(--accent);
  outline-offset: 2px;
}

.btn:disabled {
  opacity: 0.5;
  cursor: not-allowed;
  transform: none;
}
```

---

#### 2. CARDS ✅ 6/10

**Variants:**
- `.product-card` — Imagen + info de producto
- `.glass` — Efecto glassmorphism
- `.stat-card` — Stats en admin

**Issues:**
1. Borders inconsistent (.product-card usa rgba(255,255,255,0.06), .glass usa 0.07)
2. Padding hardcoded (1.5rem)
3. No "selected" or "active" state
4. No animation on hover

**Recommendation:**
```css
.card {
  border: 1px solid var(--border-color);  /* New token */
  border-radius: var(--radius-lg);
  padding: var(--space-lg);
  transition: all var(--transition-base);
}

.card:hover {
  box-shadow: 0 8px 24px rgba(232, 57, 154, 0.1);
  transform: translateY(-4px);
}

.card.active {
  border-color: var(--accent);
  background: var(--accent-dim);
}
```

---

#### 3. BADGES ✅ 6/10

**Variants:** 6 color variants (accent, dark, green, red, yellow, blue)

**Issues:**
1. Not semantic (class names are vague)
2. No size variants
3. Hardcoded spacing
4. No documentation

---

#### 4. INPUTS ❌ 4/10

**Current State:**
```css
.input, .select, .textarea {
  background: var(--dark2);
  border: 1px solid rgba(255,255,255,0.08);
  color: var(--white);
  padding: 0.75rem 1rem;  /* Hardcoded */
}

input:focus {
  border-color: var(--accent);
  box-shadow: 0 0 0 3px var(--accent-dim);  /* Too subtle */
}
```

**Major Issues:**
1. ❌ No `.label` associated with inputs
2. ❌ No error state styling
3. ❌ No placeholder styling rules
4. ⚠️ Focus shadow too subtle
5. ❌ No disabled state styling
6. ❌ No readonly state

**Recommendation:** Create form component system

```css
.form-group {
  margin-bottom: var(--space-lg);
}

.form-group label {
  display: block;
  margin-bottom: var(--space-sm);
  font-weight: var(--weight-semibold);
  font-size: var(--size-sm);
}

.form-group input,
.form-group select,
.form-group textarea {
  width: 100%;
  padding: var(--space-md) var(--space-lg);
  border-radius: var(--radius-md);
  border: 2px solid transparent;
  background: var(--dark2);
  color: var(--white);
  transition: all var(--transition-base);
}

.form-group input:focus,
.form-group select:focus,
.form-group textarea:focus {
  outline: none;
  border-color: var(--accent);
  box-shadow: 0 0 0 3px var(--accent-dim);
}

.form-group input:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.form-group input[aria-invalid="true"],
.form-group input.error {
  border-color: #ef4444;
  background: rgba(239, 68, 68, 0.05);
}

.form-group .error-message {
  color: #ef4444;
  font-size: var(--size-sm);
  margin-top: var(--space-sm);
}
```

---

#### 5. NAVIGATION ✅ 6/10

**Header:**
- ✅ Logo placement
- ✅ Nav links with underline effect
- ✅ Theme toggle
- ✅ User menu dropdown
- ⚠️ Hamburger menu (responsive) exists but undocumented

**Sidebar (Admin):**
- ✅ Vertical nav
- ✅ Active state with left border
- ✅ Icons + labels
- ⚠️ No collapse/expand functionality

**Issues:**
1. No documentation
2. Mobile menu not fully responsive in all viewports
3. Keyboard navigation incomplete

---

#### 6. MISSING COMPONENTS ❌

**Critical Gaps:**
- ❌ **Form Group** — Input + Label + Error component
- ❌ **Accordion** — Collapsible sections
- ❌ **Dropdown** — Select menu (reutilizable)
- ❌ **Tooltip** — Hover information
- ❌ **Alert** — Banner messages
- ❌ **Skeleton** — Loading placeholder
- ❌ **Tab** — Tab navigation
- ❌ **Stepper** — Multi-step process indicator

---

## Naming Consistency Audit

### CSS Class Naming

| Pattern | Usage | Consistency |
|---------|-------|------------|
| **Utility Classes** | `.grid-2`, `.section`, `.label` | ✅ Good |
| **Component Classes** | `.btn`, `.card`, `.badge` | ✅ Good |
| **State Classes** | `.active`, `.disabled` | ✅ Good |
| **Theme Classes** | `.light-theme` | ✅ Good |
| **Modifier Classes** | `.btn-primary`, `.btn-outline` | ✅ Good |

**Overall:** ✅ **Naming is consistent and follows BEM-like pattern**

---

## Inconsistencies Found

| Issue | Severity | Impact | Fix Time |
|-------|----------|--------|----------|
| Border radius inconsistent (1.2rem vs 1rem) | 🟢 Minor | Slight visual inconsistency | 30min |
| Spacing hardcoded (7rem, 4rem, 5rem) | 🟡 Medium | Maintenance nightmare | 1hr |
| Button states undefined (ghost opacity) | 🟡 Medium | Inconsistent hover effects | 1hr |
| Input focus shadow too subtle | 🟡 Medium | Accessibility issue | 30min |
| Color tokens missing (states, semantic) | 🔴 Critical | Can't add new colors consistently | 2hrs |
| Typography not tokenized | 🔴 Critical | Headings are inconsistent | 2hrs |
| Missing form component system | 🔴 Critical | Forms across app inconsistent | 3hrs |

---

## Recommendations

### Phase 1: Formalize Tokens (2-3 hours)
1. Create `css/design-tokens.css` with:
   - ✅ Color tokens (semantic + states)
   - ✅ Spacing scale
   - ✅ Typography scale
   - ✅ Border radius values
   - ✅ Shadow definitions
   - ✅ Motion tokens

2. Update `app.css` and `admin.css` to use tokens

### Phase 2: Create Component Library (4-5 hours)
1. Document existing components (Button, Card, Badge, Input, Nav)
2. Create missing components (Form Group, Alerts, Accordion)
3. Add documentation for each (variants, states, accessibility)

### Phase 3: Documentation (2-3 hours)
1. Create `DESIGN-SYSTEM.md`
2. Create `COMPONENT-CATALOG.md`
3. Create component examples with HTML/CSS

---

## Estimated Effort

| Task | Hours | Priority |
|------|-------|----------|
| Design tokens formalization | 3 | 🔴 Critical |
| Update CSS to use tokens | 2 | 🔴 Critical |
| Document existing components | 3 | 🟡 High |
| Create missing components | 5 | 🟡 High |
| Write Design System guide | 3 | 🟡 High |
| **Total** | **16 hours** | |

---

## Maturity Assessment

| Dimension | Score | Comments |
|-----------|-------|----------|
| **Token Definition** | 4/10 | Partial; missing many tokens |
| **Component Coverage** | 5/10 | Core components exist; missing advanced ones |
| **Consistency** | 6/10 | Generally good but with gaps |
| **Documentation** | 1/10 | Virtually non-existent |
| **Accessibility** | 4/10 | Basic keyboard support; missing ARIA |
| **Scalability** | 5/10 | Could grow but will become messy without formalization |

**Overall Design System Maturity: 4.2/10 — Foundation is good, but needs formalization**

---

## Next Steps

1. ✅ Create `css/design-tokens.css`
2. ✅ Document components in `COMPONENT-SPECS.md`
3. ✅ Create `DESIGN-SYSTEM.md` guide
4. ✅ Add missing components
5. ✅ Establish component documentation standards

# Action Matrix — Design System

**Version:** 1.0  
**Applies to:** Next.js / React frontend rebuild  
**Source of truth:** Extracted directly from the current Laravel application  
**UI Stack:** shadcn/ui + Tailwind CSS (see mapping notes per token)

> This document gives the frontend engineer everything needed to reproduce the exact look and feel of the current system. Every value here is taken from the live app's CSS — not invented.

---

## 1. Design Philosophy

- **Professional, not decorative.** The app is used by government officers daily. No gradients, no animations beyond subtle hover lifts.
- **Dark teal as the single brand color.** Everything else is neutral gray or semantic (red/green/amber).
- **Density-conscious.** Tables and cards show a lot of information. Font sizes lean small (0.7–0.875rem). Padding is tight.
- **Accessible contrast.** Soft badge variants (light background + dark text) are used throughout for status indicators.
- **Static modals.** All modals must use static backdrop — users cannot close by clicking outside.

---

## 2. Color Tokens

### 2.1 Brand / Primary

| Token | Hex | Usage |
|---|---|---|
| `primary` | `#1b3a3a` | Buttons, sidebar active, links, icons, focus rings |
| `primary-hover` | `#2a5a5a` | Button hover state |
| `primary-soft` | `rgba(27, 58, 58, 0.08)` | Active nav background, subtle highlights |
| `primary-focus-ring` | `rgba(27, 58, 58, 0.10)` | Input focus box-shadow |

### 2.2 Neutrals

| Token | Hex | Usage |
|---|---|---|
| `bg` | `#f0f2f5` | Page background |
| `surface` | `#ffffff` | Cards, sidebar, modals, dropdowns |
| `text` | `#1b2525` | Body text, headings |
| `muted` | `#748181` | Labels, timestamps, secondary text, table headers |
| `border` | `#e9eff1` | Card borders, dividers, input borders |
| `border-strong` | `#dee6e9` | Stronger dividers, focused inputs |

### 2.3 Semantic

| Token | Hex | Usage |
|---|---|---|
| `success` | `#28a745` | Success states |
| `danger` | `#dc3545` | Error states |
| `warning` | `#ffc107` | Warning states |
| `warning-text` | `#9a6a0e` | Text on warning backgrounds |

### 2.4 Slate Scale (secondary neutrals from analytics/tables)

These Tailwind-compatible values are used extensively in charts, tables, and KPI cards:

| Value | Usage |
|---|---|
| `#0f172a` | KPI large numbers |
| `#1e293b` | Heading emphasis |
| `#374151` | Table body text, chart titles |
| `#475569` | Secondary text |
| `#64748b` | Muted labels, table headers |
| `#94a3b8` | Very muted, chart subtitles |
| `#cbd5e1` | Borders in analytics panels |
| `#e2e8f0` | Analytics panel borders |
| `#f1f5f9` | Light row backgrounds, SAVED badge bg |
| `#f8fafc` | Table header backgrounds |

### 2.5 Tailwind CSS Mapping

```js
// tailwind.config.js — extend colors
colors: {
  primary: {
    DEFAULT: '#1b3a3a',
    hover:   '#2a5a5a',
    soft:    'rgba(27, 58, 58, 0.08)',
  },
  surface: '#ffffff',
  bg:      '#f0f2f5',
  text:    '#1b2525',
  muted:   '#748181',
  border: {
    DEFAULT: '#e9eff1',
    strong:  '#dee6e9',
  },
}
```

---

## 3. Typography

### 3.1 Font

**Family:** [Public Sans](https://fonts.google.com/specimen/Public+Sans)  
**Weights used:** 400 (regular), 500 (medium), 600 (semibold), 700 (bold)  
**Anti-aliasing:** `-webkit-font-smoothing: antialiased`

```html
<!-- Google Fonts import -->
<link href="https://fonts.googleapis.com/css2?family=Public+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
```

```js
// tailwind.config.js
fontFamily: {
  sans: ['Public Sans', 'sans-serif'],
}
```

### 3.2 Type Scale

| Role | Size | Weight | Color | Notes |
|---|---|---|---|---|
| Page title | `1.1rem` (h5) | 700 | `text` | e.g., "Visits", "Analytics" |
| Page subtitle | `0.8rem` | 400 | `muted` | Below page title |
| Card title | `0.9rem` | 700 | `text` | |
| Table header | `0.74rem` | 600 | `muted` | Uppercase, `0.05em` letter-spacing |
| Table body | `0.875rem` | 400 | `#374151` | |
| KPI value | `1.75rem–2rem` | 700–800 | `#0f172a` | Stat cards and analytics |
| KPI label | `0.7rem` | 700 | `#64748b` | Uppercase, `0.06em` letter-spacing |
| Form label | `0.875rem` | 600 | `text` | |
| Small/muted | `0.8125rem` | 400 | `muted` | Timestamps, helper text |
| Badge/pill | `0.68–0.75rem` | 600–700 | varies | Status badges |
| Section label | `0.65–0.68rem` | 700 | `#94a3b8` | Uppercase, sidebar group labels |
| Visit code | `0.82rem` | 700 | `primary` | Monospace (`Courier New`) |

---

## 4. Spacing & Layout

### 4.1 Shell Dimensions

| Token | Value |
|---|---|
| Sidebar width | `260px` |
| Header (topbar) height | `70px` |
| Content padding (desktop) | `1.5rem–2rem` |
| Content padding (mobile) | `0.75rem–1rem` |

### 4.2 Border Radius

| Context | Radius |
|---|---|
| Cards, panels, modals | `12px` |
| Buttons | `8px` |
| Inputs | `8px` |
| Avatar boxes | `2px` (portrait photo) |
| Nav links | `8px` |
| Sub-nav links | `6px` |
| Badges/status pills | `5px–6px` |
| Rounded pills | `20px` |
| Icon containers | `8px–12px` |
| Auth logo box | `12px` |

### 4.3 Shadows

```css
/* Default card shadow */
box-shadow: 0 4px 12px -2px rgba(27, 58, 58, 0.04),
            0 2px 4px -1px rgba(27, 58, 58, 0.03);

/* Card hover shadow */
box-shadow: 0 8px 16px -4px rgba(27, 58, 58, 0.08);

/* Stat card hover */
box-shadow: 0 6px 20px -4px rgba(27, 58, 58, 0.10);

/* Auth card */
box-shadow: 0 10px 30px -5px rgba(27, 58, 58, 0.10),
            0 4px 12px -2px rgba(27, 58, 58, 0.05);
```

---

## 5. Components

### 5.1 Layout Shell

```
┌─────────────────────────────────────────────────────────┐
│  SIDEBAR (260px, fixed, white, border-right)            │
│  ┌──────────────────────────────────────────────────┐   │
│  │ Header (70px, logo + app name)                  │   │
│  ├──────────────────────────────────────────────────┤   │
│  │ Nav items (collapsible groups)                  │   │
│  └──────────────────────────────────────────────────┘   │
├─────────────────────────────────────────────────────────┤
│  TOPBAR (70px, sticky, white/80% + blur, border-bottom) │
│  [hamburger] [breadcrumb]     [bell] [user dropdown]    │
├─────────────────────────────────────────────────────────┤
│  MAIN CONTENT (flex-grow, #f0f2f5 bg, p-4)             │
│  [page header row]                                      │
│  [stat cards row]                                       │
│  [filter bar]                                           │
│  [table / cards]                                        │
├─────────────────────────────────────────────────────────┤
│  FOOTER (white, border-top, p-4)                        │
└─────────────────────────────────────────────────────────┘
```

- Sidebar is **collapsible** on desktop (slides off-screen, content fills full width)
- Sidebar is an **overlay drawer** on mobile (hidden by default, toggled by hamburger)
- Topbar uses `backdrop-filter: blur(8px)` for a frosted-glass effect

### 5.2 Sidebar Navigation

- Logo area: primary-soft icon box + bold app name in primary color
- Group labels: `0.65rem`, uppercase, `#748181`
- Nav items: `0.875rem`, `font-weight: 500`, `8px` radius, hover = primary-soft bg + primary text
- Active nav item: primary bg + white text
- Sub-links: `0.825rem`, `#748181`, indented `2.6rem`, hover shifts left slightly
- Active sub-link: primary color, `font-weight: 600`
- Chevron rotates `90°` when group is expanded

### 5.3 Cards

```css
/* Base card */
background: #ffffff;
border: 1px solid #e9eff1;
border-radius: 12px;
box-shadow: 0 4px 12px -2px rgba(27, 58, 58, 0.04),
            0 2px 4px -1px rgba(27, 58, 58, 0.03);
transition: box-shadow 0.2s ease;

/* Card with header */
.card-header {
  padding: 0.9rem 1.25rem;
  border-bottom: 1px solid #e9eff1;
  font-weight: 700;
  font-size: 0.9rem;
}
.card-body { padding: 1.25rem; }
```

### 5.4 Stat Cards (KPI Row)

Used at the top of list pages. Clickable — filters the table below.

```
┌────────────────────────────┐
│  [icon box]  1,234         │
│              TOTAL VISITS  │
└────────────────────────────┘
```

- Icon box: `48×48px`, `12px` radius, semantic bg + icon color
- Value: `1.75rem`, `font-weight: 700`, `--text`
- Label: `0.78rem`, uppercase, `letter-spacing: 0.04em`, `--muted`
- Row: 4 cards on desktop, 2×2 on tablet, 2×2 on mobile

**Icon color examples:**
| Stat | Icon bg | Icon color |
|---|---|---|
| Total | `#f0f9ff` | `#0284c7` |
| On My Desk | `#fff7ed` | `#ea580c` |
| In Progress | `#f0fdf4` | `#16a34a` |
| Closed | `#f5f3ff` | `#7c3aed` |

### 5.5 Buttons

```css
/* Primary button */
background: #1b3a3a;
color: white;
border: none;
padding: 0.5rem 1.25rem;
border-radius: 8px;
font-weight: 600;
transition: all 0.2s;

/* Hover */
background: #2a5a5a;
transform: translateY(-1px);

/* Small variant (in page headers) */
padding: 0.45rem 1rem;
font-size: 0.875rem;
```

- Outline buttons: `border: 1px solid --border`, transparent bg, `--muted` text
- Danger button: Bootstrap `btn-danger` style
- All buttons: `8px` border radius

### 5.6 Badges & Status Pills

#### Visit Status (soft — used in list/table)

| Status | Background | Text |
|---|---|---|
| `SAVED` | `#f1f5f9` | `#64748b` |
| `PKSF_CO_SUBMITTED` | `#eff6ff` | `#2563eb` |
| `PKSF_SO_SENT_BACK` | `#fff1f2` | `#e11d48` |
| `PO_SO_REVIEW` | `#fff7ed` | `#c2410c` |
| `PO_CO_REVIEW` | `#fefce8` | `#92400e` |
| `PO_CO_SUBMITTED` | `#f0fdf4` | `#166534` |
| `PO_SO_APPROVED` | `#dcfce7` | `#15803d` |

#### Visit Status (solid — used in detail/show pages)

| Status | Background | Text |
|---|---|---|
| `SAVED` | `#64748b` | `#ffffff` |
| `PKSF_CO_SUBMITTED` | `#2563eb` | `#ffffff` |
| `PKSF_SO_SENT_BACK` | `#dc2626` | `#ffffff` |
| `PO_SO_REVIEW` | `#ea580c` | `#ffffff` |
| `PO_CO_REVIEW` | `#d97706` | `#ffffff` |
| `PO_CO_SUBMITTED` | `#16a34a` | `#ffffff` |
| `PO_SO_APPROVED` | `#059669` | `#ffffff` |

#### Observation Resolution Status

| Status | Background | Text |
|---|---|---|
| `OPEN` | `#f1f5f9` | `#475569` |
| `PENDING_RESOLVED` | `#fef3c7` | `#92400e` |
| `RESOLVED` | `#dcfce7` | `#15803d` |

#### Priority

| Priority | Background | Text |
|---|---|---|
| `HIGH` | `#fee2e2` | `#991b1b` |
| `MEDIUM` | `#fef3c7` | `#92400e` |
| `LOW` | `#f0fdf4` | `#166534` |

Priority dots (list view): `HIGH` = `#ef4444`, `MEDIUM` = `#f59e0b`, `LOW` = `#94a3b8`

#### Badge Base Styles

```css
/* Standard soft badge */
font-size: 0.72rem;
font-weight: 600;
padding: 0.3em 0.65em;
border-radius: 6px;
letter-spacing: 0.02em;
white-space: nowrap;

/* Rounded pill (detail page) */
border-radius: 20px;
text-transform: uppercase;
letter-spacing: 0.04em;
```

### 5.7 Tables

```css
/* Header row */
th {
  font-size: 0.74rem;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.05em;
  color: #748181;             /* --muted */
  border-bottom: 2px solid #e9eff1;
  white-space: nowrap;
  background: #f8fafc;        /* analytics variant */
}

/* Body cells */
td {
  font-size: 0.875rem;
  vertical-align: middle;
  color: #374151;
}

/* Row hover */
tr:hover td { background: rgba(27, 58, 58, 0.08); }  /* primary-soft */

/* My-desk highlight */
.my-desk-row { border-left: 3px solid #1b3a3a; }
```

### 5.8 Forms & Inputs

```css
/* Input */
border-radius: 8px;
padding: 0.75rem 1rem;
border: 1px solid #e9eff1;
font-size: 0.95rem;
transition: all 0.2s;

/* Focus */
border-color: #1b3a3a;
box-shadow: 0 0 0 4px rgba(27, 58, 58, 0.10);

/* Small variant (inside cards) */
padding: 0.4rem 0.75rem;
border-radius: 8px;
border: 1px solid #dee6e9;

/* Form label */
font-weight: 600;
font-size: 0.875rem;
color: #1b2525;
margin-bottom: 0.5rem;
```

- Use `Select` with searchable dropdown (equivalent of Select2 with `bootstrap-5` theme)
- File inputs show selected files as removable pill badges below the input

### 5.9 Filter Bar

A row above the main table for filtering the list:

```css
background: #ffffff;
border: 1px solid #e9eff1;
border-radius: 12px;
padding: 1rem 1.25rem;
display: flex;
flex-wrap: wrap;
gap: 0.75rem;
align-items: center;
```

Contains: period toggle buttons, PO select dropdown, status select, search input, reset button.

### 5.10 Period Toggle Buttons

```css
/* Base */
padding: 0.3rem 0.75rem;
border-radius: 6px;
border: 1px solid #e2e8f0;
background: #ffffff;
font-size: 0.78rem;
font-weight: 600;
color: #64748b;

/* Active */
background: #1b3a3a;
color: #ffffff;
border-color: #1b3a3a;
```

### 5.11 Modals

- **All modals use static backdrop** — clicking outside does NOT close them. Users must click Cancel or the × button.
- Size: default (`max-width: 500px`) or large (`max-width: 800px`) — use large for forms with multiple fields
- Header: white background
- Footer: Cancel (outline) + primary action (primary bg)

```css
/* Modal general */
border-radius: 12px;
overflow: hidden;

/* Static backdrop implementation (React) */
// Use Dialog from shadcn/ui with closeOnOutsideClick={false}
// or data-bs-backdrop="static" equivalent
```

### 5.12 Comment Bubbles

```css
/* PKSF comment */
background: #eff6ff;
border-left: 3px solid #3b82f6;
border-radius: 10px;
padding: 0.75rem 1rem;

/* PO comment */
background: #f0fdf4;
border-left: 3px solid #22c55e;
border-radius: 10px;
padding: 0.75rem 1rem;

/* Comment meta (author + time) */
font-size: 0.72rem;
color: #748181;
margin-top: 0.3rem;

/* Draft badge */
background: #fef3c7;
color: #92400e;
font-size: 0.65rem;
font-weight: 700;
padding: 0.15em 0.45em;
border-radius: 4px;
```

### 5.13 Timeline / Movement History

```css
/* Each item */
display: flex;
gap: 0.75rem;
padding-bottom: 1.1rem;
position: relative;

/* Vertical connector line */
::before {
  content: '';
  position: absolute;
  left: 14px; top: 30px; bottom: 0;
  width: 1px;
  background: #e9eff1;
}

/* Dot */
width: 28px; height: 28px;
border-radius: 50%;
font-size: 0.8rem;
```

### 5.14 Observation Cards (collapsible)

```css
/* Card */
border: 1px solid #e9eff1;
border-radius: 10px;
overflow: hidden;
transition: box-shadow 0.15s;

/* Header (click to expand) */
padding: 0.75rem 1rem;
background: #fafbfc;
display: flex;
align-items: flex-start;
gap: 0.75rem;
cursor: pointer;

/* Sequence number badge */
width: 28px; height: 28px;
border-radius: 8px;
background: #1b3a3a;
color: #fff;
font-size: 0.75rem;
font-weight: 700;
```

### 5.15 Avatars

- **Current:** Initials-based (2 letters), colored background
- **Future:** Employee photo from `storage/avatars/{emp_id}.jpg`, size `50×65px`, `border-radius: 2px`
- Fallback: initials shown when image fails to load

| Avatar type | Background |
|---|---|
| PKSF side | `#1b3a3a` (primary) |
| PO side | `#b7791f` (amber) |
| Management | `#1b3a3a` (primary) |

### 5.16 Alerts / Flash Messages

Rendered globally by the layout — **never add flash blocks inside page components**.

```css
/* Success */
background: Bootstrap alert-success
icon: bi-check-circle-fill

/* Error */
background: Bootstrap alert-danger
icon: bi-exclamation-triangle-fill
```

Use `toast` notifications in React (shadcn/ui `Sonner` or `Toast`) triggered after API responses.

---

## 6. Auth Page (Login)

```
┌──────── #f4f7f7 background with subtle radial teal gradients ────────┐
│                                                                       │
│           ┌─────────── White card (440px max-width) ───────────┐     │
│           │   [Logo icon box: 60×60px, primary-soft bg]        │     │
│           │   "Welcome Back"  (h4, bold, primary)              │     │
│           │   "Enter your credentials..."  (small, muted)      │     │
│           │                                                     │     │
│           │   Employee ID  [icon] [input]                       │     │
│           │   Password     [icon] [input]   Forgot password?   │     │
│           │   [ ] Remember this device                         │     │
│           │   [Sign In — full width primary button]            │     │
│           └─────────────────────────────────────────────────────┘     │
└───────────────────────────────────────────────────────────────────────┘
```

- Background: `#f4f7f7` with `radial-gradient(at 0% 0%, rgba(27,58,58,0.05)...)`
- Card shadow: `0 10px 30px -5px rgba(27,58,58,0.1), 0 4px 12px -2px rgba(27,58,58,0.05)`
- Inputs have a leading icon (Bootstrap Icons equivalent: `lucide-react` in Next.js)

---

## 7. Icons

**Current system:** Bootstrap Icons (`bi-*`)  
**Next.js equivalent:** Use [Lucide React](https://lucide.dev/) — same style family, tree-shakeable, TypeScript-typed.

Common icon mappings:

| Bootstrap | Lucide | Usage |
|---|---|---|
| `bi-grid-fill` | `LayoutGrid` | App logo / dashboard |
| `bi-speedometer2` | `Gauge` | Dashboard nav |
| `bi-clipboard2-check` | `ClipboardCheck` | Visits nav |
| `bi-file-earmark-pdf` | `FileText` | Reports nav |
| `bi-people` | `Users` | User management |
| `bi-bell` | `Bell` | Notifications |
| `bi-person-badge` | `BadgeCheck` | Employee ID input |
| `bi-lock` | `Lock` | Password input |
| `bi-plus-lg` | `Plus` | Add / create |
| `bi-pencil` | `Pencil` | Edit |
| `bi-trash` | `Trash2` | Delete |
| `bi-paperclip` | `Paperclip` | Attachments |
| `bi-chevron-right` | `ChevronRight` | Nav expand |
| `bi-chevron-down` | `ChevronDown` | Collapse toggle |
| `bi-list` | `Menu` | Hamburger |
| `bi-shield-lock` | `ShieldCheck` | Management Review section |
| `bi-check-circle-fill` | `CheckCircle2` | Success alert |
| `bi-exclamation-triangle-fill` | `AlertTriangle` | Error alert |

---

## 8. Transitions & Motion

- **Card hover:** `box-shadow` change, `0.2s ease` — NO position change
- **Button hover:** `background` darkens + `translateY(-1px)`, `0.2s`
- **Sidebar collapse:** `transform: translateX(-100%)`, `0.3s ease`
- **Nav link hover:** background + color, `0.2s ease`
- **Collapsible sections:** standard CSS collapse/expand (Bootstrap Collapse equivalent)
- **Chevron rotation:** `transform: rotate(90deg)`, `0.2s` when expanded

Keep motion minimal and functional — no entry animations, no page transitions.

---

## 9. Responsive Breakpoints

Follow Bootstrap 5 breakpoints (matches the current system):

| Name | Min width | Usage |
|---|---|---|
| `sm` | `576px` | Stack form cols, hide some text |
| `md` | `768px` | Show breadcrumb, 2-col grids |
| `lg` | `992px` | Sidebar becomes fixed; 4-col stat cards |
| `xl` | `1200px` | Wider content area |

Key responsive rules:
- Sidebar: fixed on `lg+`, overlay drawer on `< lg`
- Stat cards: `2×2` on mobile, `4×1` on `lg+`
- Visit detail layout: `1fr 340px` on desktop → single column on `< 1100px`
- Modals: full-screen on mobile, centered on desktop

---

## 10. shadcn/ui Component Mapping

| UI need | shadcn/ui component | Notes |
|---|---|---|
| Cards | `Card` | Customize border/shadow to match tokens |
| Tables | `Table` | Override header style with uppercase + muted |
| Modals | `Dialog` | Set `onInteractOutside={(e) => e.preventDefault()}` for static backdrop |
| Selects | `Select` or `Combobox` | Add search for large lists (PO, user dropdowns) |
| Toasts | `Sonner` | Replace flash message alerts |
| Badges | `Badge` | Create variants per status using token colors |
| Buttons | `Button` | Override primary variant with `#1b3a3a` |
| Inputs | `Input` | Override focus ring with `rgba(27,58,58,0.1)` |
| Dropdowns | `DropdownMenu` | Used for user avatar menu |
| Tooltips | `Tooltip` | For truncated text, icon buttons |
| Date picker | `Popover` + `Calendar` | Replace Flatpickr |
| Collapsible | `Collapsible` | Observation cards, sidebar nav groups |
| File upload | Custom | Pill-based file list with remove button |

---

## 11. Analytics / Chart Colors

Charts use a consistent palette. Map to your charting library (Recharts recommended with Next.js):

| Series | Color | Usage |
|---|---|---|
| Primary series | `#1b3a3a` | Main metric |
| Secondary | `#2a5a5a` | Comparison metric |
| Success / Resolved | `#22c55e` | Positive states |
| Warning / Pending | `#f59e0b` | In-progress |
| Danger / Open | `#ef4444` | Negative / unresolved |
| Info | `#3b82f6` | Neutral info |
| Muted | `#94a3b8` | Low priority / empty |

Grid lines: `#e2e8f0`  
Axis labels: `#64748b`, `0.7rem`  
Chart panel bg: `#ffffff`, `border: 1px solid #e2e8f0`, `border-radius: 12px`

---

*End of Document*

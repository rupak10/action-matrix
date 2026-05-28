# Action Matrix — Improvement Backlog

> Analysed on: 2026-05-28  
> Based on: full codebase review + business-requirements.txt  
> Status legend: 🔴 Critical · 🟠 High Value · 🟡 UX · 🟢 Reporting · ⚙️ Technical Debt

---

## 🔴 Critical Fixes *(Broken Right Now)*

### ~~1. "Pending Reviews" stat card always shows 0~~ ✅ Done — 2026-05-28
- Replaced 2 broken stat cards with 4 meaningful ones: **Total Matrices**, **Action Required** (at my desk), **In Progress** (active in workflow), **Closed**. All computed from the already-loaded `$matrices` collection — zero extra queries.

### ~~2. "Create New Matrix" button visible to PO users~~ ✅ Done — 2026-05-28
- Button and subtitle text now conditionally rendered per role (PKSF vs PO).
- Added server-side `abort(403)` guard to both `create()` and `store()` controller methods so direct URL access is also blocked.

### 3. Status labels are raw database codes
- **Files:** `index.blade.php`, `show.blade.php`
- **Problem:** Users see `PO_REVIEW`, `WAITING_FOR_CLOSURE`, `PKSF_REJECTED` — raw system enum values. Confusing for non-technical staff.
- **Fix:** Create a global status label map, e.g.:
  - `SAVED` → "Draft"
  - `SUBMITTED` → "Submitted for Review"
  - `PO_REVIEW` → "PO Response Pending"
  - `PO_SUBMITTED` → "Awaiting PO Supervisor Review"
  - `PO_APPROVED` → "PO Response Approved"
  - `WAITING_FOR_CLOSURE` → "Awaiting Closure Approval"
  - `PKSF_REJECTED` → "Returned by PKSF"
  - `CLOSED` → "Closed"

### 4. Draft comments visible to supervisors without any indicator
- **File:** `resources/views/action_matrix/show.blade.php`
- **Problem:** Comments with `is_draft = 1` appear identical to submitted comments. A PO Supervisor could be reading an unfinished draft without knowing it.
- **Fix:** Show a "Draft" badge on any comment where `is_draft = 1`, visible only to the comment author.

---

## 🟠 High Value Features *(Missing Entirely)*

### ~~5. "Action Required" dedicated view / filter tab~~ ✅ Done — 2026-05-28
- Implemented as three server-side filter dropdowns above the table instead of tabs:
  1. **View** — All Matrices / Action Required / Created by Me / Completed
  2. **PO Code** — filter by PO
  3. **Priority** — filter by priority level
- Switched to full server-side DataTables (`serverSide: true`) with an AJAX endpoint (`GET /action-matrix/data` → `getData()`).
- Service method `getMatricesTableData()` applies all filters, global search, sorting, and pagination in SQL — scales as data grows.
- Action buttons rendered client-side from JSON using `renderActions()` which replicates the full conditional logic per role/status.
- Status and priority cells use human-readable labels via `renderStatus()` / `renderPriority()`.
- A "Clear" button appears automatically whenever any filter is active.

### 6. Deadline / Due Date tracking
- **Problem:** No due date exists on a matrix. Nothing prevents a PO from ignoring an observation indefinitely.
- **Suggestion:**
  - Add `response_due_date` column to `acm_master` (set by PKSF CO at creation).
  - Show a coloured due-date badge in the index table (green → yellow → red as it approaches/passes).
  - Show overdue count in the stat cards.

### 7. Days at desk / Age indicator
- **Problem:** No visibility into how long a matrix has been idle at someone's desk.
- **Suggestion:** In the index table status column, add a small "· 12 days" indicator showing the age since the last movement. Anything over a configurable threshold (e.g. 7 days) turns red.

### 8. Email notifications on workflow transitions
- **Problem:** When a matrix is forwarded, approved, or rejected, the recipient has no idea unless they log in manually.
- **Suggestion:** Send a basic email notification on:
  - Matrix forwarded to you (with ACM ID, PO code, and a link)
  - Your submission approved / rejected (with remarks)
  - Matrix returned to you for revision
- Use Laravel's built-in `Mail` + queue system.

### 9. PO list is hardcoded in the controller
- **File:** `app/Http/Controllers/ActionMatrixController.php` — `getFormOptions()` method
- **Problem:** The list of POs is hardcoded as a PHP array. Adding a new PO requires editing source code.
- **Fix:** Replace with `User::where('emp_type', 'PO')->orderBy('name')->get()` and load from the `users` table.

---

## 🟡 UX Improvements *(Makes It Feel Professional)*

### 10. Status progression bar on the show page
- **Problem:** The status badge only shows the current state. Users can't see the full workflow journey at a glance.
- **Suggestion:** A horizontal step tracker at the top of the show page:
  `Created → Submitted → PO Review → PO Submitted → PKSF Review → Closed`
  The current step is highlighted; completed steps are ticked; future steps are greyed.

### 11. Column filters on the index table
- **Problem:** Only a global keyword search is available. Users can't filter by Status, Priority, or PO Code without typing.
- **Suggestion:** Add a filter bar above the table with dropdowns for Status, Priority, and PO Code. DataTables supports column filtering natively.

### 12. Action buttons on the show page
- **Problem:** The user must navigate back to the index list to take any workflow action (Comment, Forward, Approve, Reject). The show page is read-only even when the matrix is at the user's desk.
- **Suggestion:** Move the same context-aware action buttons (already built for the index page) to the show page header so users can act directly from the detail view.

### 13. "Time ago" format on comments and movements
- **Problem:** Comments and movements show `28 May 2026, 09:30 AM`. Hard to gauge recency.
- **Suggestion:** Show `2 hours ago` as the primary label with the full date/time on hover (`title` attribute). Use a simple JS helper or Carbon's `diffForHumans()`.

### 14. "Incoming From" column — show time elapsed
- **Problem:** The "Incoming From" column shows the sender name with a cramped timestamp.
- **Suggestion:** Reformat as "From: Karim · 3 days ago" for scannability.

### 15. Breadcrumb navigation
- **Problem:** Moving between index and show pages relies on the browser back button or the "Back to List" button buried in the top-right corner.
- **Suggestion:** Add a simple breadcrumb: `Home > Action Matrix > ACM-007-05` at the top of the show and edit pages.

---

## 🟢 Reporting & Data *(Strategic Value)*

### 16. PDF export for individual matrix
- **Problem:** PKSF management and auditors will want to print or archive a complete matrix record.
- **Suggestion:** A "Download PDF" button on the show page. The PDF should cover: matrix details, observation, all comments (with author and timestamp), movement history, and attachments list. Use `barryvdh/laravel-dompdf` or `spatie/browsershot`.

### 17. Dashboard with real analytics
- **Problem:** The current dashboard has no meaningful data. It is essentially an empty shell.
- **Suggestion:** Add widgets for:
  - Matrices by status (donut chart)
  - Matrices by PO code (bar chart — top 10)
  - Matrices by priority
  - Average days to first PO response
  - Overdue count (if deadline tracking is added)
  - My recent activity feed

### 18. Export to Excel from index table
- **Problem:** No way to extract data for offline reporting or sharing with management.
- **Suggestion:** Enable DataTables Buttons extension. Add "Export Excel" and "Export CSV" buttons to the table toolbar. These export whatever is currently filtered/visible.

---

## ⚙️ Technical Debt *(Not Visible But Matters)*

### 19. N+1 query: `hasComments()` called in index loop
- **File:** `resources/views/action_matrix/index.blade.php` ~line 162, `app/Models/AcmMaster.php`
- **Problem:** Every row in the index table fires a separate SQL query `SELECT EXISTS(...)` to check `hasComments()`. With 50 matrices, that is 50 extra queries on every page load.
- **Fix:** In the controller's `index()` method, eager-load comment counts:
  ```php
  $matrices = AcmMaster::withCount('comments')->...->get();
  ```
  Then replace `$matrix->hasComments()` with `$matrix->comments_count > 0`.

### 20. No authorization middleware on workflow action routes
- **File:** `routes/web.php`
- **Problem:** Any authenticated user can POST to `/action-matrix/approve`, `/action-matrix/po-forward`, etc. Only the UI hides the buttons — there is no server-side role check. A malicious or curious user could trigger any workflow step via a direct HTTP request.
- **Fix:** Add role/policy middleware to each workflow route, e.g.:
  ```php
  Route::post('/action-matrix/approve', ...)->middleware('role:PKSF');
  Route::post('/action-matrix/po-forward', ...)->middleware('role:PO');
  ```

### 21. `PKSF_REJECTED` used for two different workflow outcomes
- **Problem:** Both "closure rejected by PKSF supervisor" and "revision rejected by PKSF supervisor" set the status to `PKSF_REJECTED`. This makes filtering, reporting, and conditional UI logic ambiguous.
- **Fix:** Introduce a separate status, e.g. `CLOSURE_REJECTED`, for the closure rejection path. Update all `match()` and `whereIn()` usages accordingly.

### 22. `acm_discussions` table is dead code
- **File:** `database/migrations/2026_05_01_151132_refactor_acm_movements_tables.php`
- **Problem:** The `acm_discussions` table is created in the migration but is never referenced anywhere in models, controllers, or views.
- **Fix:** Either implement it (if discussions are a planned feature) or drop it with a cleanup migration to keep the schema clean.

---

## Priority Order (Recommended Implementation Sequence)

| # | Item | Effort | Impact | Status |
|---|------|--------|--------|--------|
| 1 | Fix "Pending Reviews" counter | 5 min | Medium | ✅ Done |
| 2 | Hide "Create New Matrix" from PO users | 5 min | High | ✅ Done |
| 3 | Human-readable status labels | 1 hr | High | ⬜ Pending |
| 4 | Draft comment badge | 30 min | Medium | ⬜ Pending |
| 5 | Fix hardcoded PO list | 30 min | High | ⬜ Pending |
| 6 | N+1 fix for `hasComments()` | 30 min | Medium | ⬜ Pending |
| 7 | "Action Required" filter tab + server-side DataTables | 2 hrs | Very High | ✅ Done |
| 8 | Column filters on index table | 2 hrs | High | ⬜ Pending |
| 9 | Action buttons on show page | 2 hrs | High | ⬜ Pending |
| 10 | Status progression bar | 3 hrs | High | ⬜ Pending |
| 11 | Due date + overdue indicator | 4 hrs | Very High | ⬜ Pending |
| 12 | Authorization middleware | 2 hrs | Critical (Security) | ⬜ Pending |
| 13 | Email notifications | 4 hrs | Very High | ⬜ Pending |
| 14 | Dashboard analytics | 1 day | High | ⬜ Pending |
| 15 | PDF export | 1 day | Medium | ⬜ Pending |
| 16 | `PKSF_REJECTED` status split | 3 hrs | Medium | ⬜ Pending |

---

*Document maintained by: Development Team*  
*Last updated: 2026-05-28*

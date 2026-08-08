# Action Matrix System — Technical Specification

**Version:** 1.0  
**Prepared for:** Backend Engineers (Java / Spring Boot) & Frontend Engineers (Next.js)  
**Database:** PostgreSQL  
**Timezone:** UTC+6 (Asia/Dhaka)  
**Languages:** English, Bengali (bn-BD)

---
## Table of Contents

1. [Project Overview](#1-project-overview)
2. [Domain Glossary](#2-domain-glossary)
3. [User Roles & Permissions](#3-user-roles--permissions)
4. [Functional Requirements](#4-functional-requirements)
5. [Business Logic](#5-business-logic)
6. [Database Schema](#6-database-schema)
7. [API Design](#7-api-design)
8. [Architecture](#8-architecture)
9. [Coding Standards & Best Practices](#9-coding-standards--best-practices)
10. [Non-Functional Requirements](#10-non-functional-requirements)
11. [Future Scope](#11-future-scope)

---

## 1. Project Overview

### 1.1 About PKSF

**Palli Karma-Sahayak Foundation (PKSF)** is a government-backed apex organization in Bangladesh that provides funding and capacity-building support to Partner Organizations (POs). PKSF regularly visits these partner organizations to assess their financial, operational, compliance, and governance practices.

### 1.2 Purpose of the System

The **Action Matrix System** is an internal workflow management platform that:

- Allows PKSF officers to record field visit observations about POs
- Tracks whether each observation has been addressed and resolved by the PO
- Manages a structured approval workflow between PKSF and PO sides
- Provides analytics and reports for senior management

### 1.3 The Problem This Solves

Previously, visit observations and follow-up actions were tracked manually via spreadsheets and emails — making it hard to track status, accountability, and resolution. This system centralizes the entire lifecycle from observation creation to final resolution.

### 1.4 Technology Stack

| Layer | Technology |
|---|---|
| Backend | Java 25, Spring Boot 4.1.x, Spring Security |
| Frontend | Next.js 16.x (App Router) |
| UI Components | shadcn/ui (Radix UI + Tailwind CSS) |
| Database | PostgreSQL 16 |
| Authentication | JWT (Access Token + Refresh Token) |
| File Storage | Local filesystem (Docker volume) |
| Containerization | Docker, Docker Compose |
| Deployment | On-premise server |
| Monorepo Structure | `/backend`, `/frontend`, `/docs` |

---

## 2. Domain Glossary

| Term | Meaning |
|---|---|
| **PKSF** | Palli Karma-Sahayak Foundation — the parent organization that conducts visits |
| **PO** | Partner Organization — an NGO or MFI that receives funding from PKSF |
| **Visit** | A formal field visit by PKSF to a PO. One visit can have multiple observations |
| **Observation** | A specific finding recorded during a visit (e.g., a compliance gap or financial discrepancy) |
| **Action Matrix** | An observation flagged as `action_matrix = Y`, meaning the PO must provide a formal action plan |
| **Resolution** | The process by which a PO addresses an observation and PKSF verifies and closes it |
| **CO** | Concern Officer — the primary working officer on either side |
| **SO** | Supervisor Officer — the supervisor who reviews and approves the CO's work |
| **MD** | Managing Director |
| **DMD** | Deputy Managing Director |
| **SGM** | Senior General Manager |
| **PKSF CO** | PKSF-side officer who creates visits and observations |
| **PKSF SO** | PKSF-side supervisor who approves and forwards to PO, and makes final observation resolution decisions |
| **PO CO** | PO-side officer who responds to observations and adds comments/action plans |
| **PO SO** | PO-side supervisor who reviews PO CO's work and submits back to PKSF |
| **is_draft** | A flag on observation comments: `0` = in progress (visible only to author and immediate supervisor), `1` = finalized/published (visible to both PKSF and PO sides) |
| **Desk** | The user whose turn it currently is to act on a visit (`current_desk_emp_id`) |

---

## 3. User Roles & Permissions

### 3.1 System Roles

There are **9 system roles** with fixed definitions. Roles are not dynamic — they are seeded at deployment.

| Role Name | Side | Description |
|---|---|---|
| `Super_Admin` | PKSF | Full system access, user management, all data |
| `Admin` | PKSF | Same as Super_Admin but managed separately |
| `PKSF_CO` | PKSF | Creates visits, adds observations, comments, requests resolution |
| `PKSF_SUPERVISOR` | PKSF | Reviews and approves PKSF CO's work, forwards to PO, resolves or returns observations |
| `SM_MD` | PKSF | Managing Director — read-only access to all data |
| `SM_DMD` | PKSF | Deputy MD — read-only access to assigned POs |
| `SM_SGM` | PKSF | Senior General Manager — read-only access to assigned POs |
| `PO_CO` | PO | Responds to observations, adds comments and action plans |
| `PO_SUPERVISOR` | PO | Reviews PO CO's work, approves and sends back to PKSF |

### 3.2 User Types

Every user has an `user_type` field:
- `PKSF` — belongs to PKSF side
- `PO` — belongs to a partner organization

PO users additionally have a `po_code` field identifying which PO they belong to.

### 3.3 Supervisor Relationship

Each user can have one or more supervisors defined in the `user_supervisors_pksf` table. Each user has exactly one **primary supervisor** (`is_primary = true`), which is used to determine where to route a workflow when forwarding.

### 3.4 PO Assignments (PKSF Side)

PKSF officers (CO, SO, DMD, SGM) are assigned to specific POs via the `user_po_assignments_pksf` table. This determines which PO data they can see on their analytics and dashboards, visits, observations.

SM_MD and Admin roles have unrestricted access to all POs.

### 3.5 Permissions Matrix

One section per role listing exactly what that role can do.

---

#### Super_Admin / Admin
- Full access to everything in the system
- Create, edit, delete visits and observations
- Perform all workflow transitions on both PKSF and PO sides
- Add comments on any observation
- Resolve or return any observation
- Generate PDF and Excel reports
- Manage users, roles, PO assignments, and supervisor relationships
- View all POs and all data without restriction

---

#### PKSF_CO (PKSF Concern Officer)
- Create, edit, delete own visits (only while status is SAVED)
- Add and edit observations on own visits (only while status is SAVED)
- Submit own visit to PKSF SO
- Add and edit own PKSF comments on observations (while not locked)
- Mark an observation as PENDING_RESOLVED 
- View visits they created or that are currently at their desk
- View analytics (scoped to assigned POs)
- Generate PDF and Excel reports

---

#### PKSF_SO (PKSF Supervisor Officer)
- Send back a submitted visit to PKSF CO
- Approve and forward a visit to PO side
- Add PKSF comments on observations
- Resolve an observation (mark as RESOLVED)
- Return an observation to PO (mark back as OPEN)
- View visits at their desk or created by their team
- View analytics (scoped to assigned POs)
- Generate PDF and Excel reports

---

#### SM_MD (Managing Director)
- Read-only access to all visits and observations across all POs
- View analytics (unrestricted — all POs)
- Add advisory SM comments on any visit
- Cannot perform any workflow action

---

#### SM_DMD / SM_SGM (Deputy MD / Senior General Manager)
- Read-only access to visits and observations for assigned POs only
- View analytics (scoped to assigned POs)
- Add advisory SM comments on any visit within their scope
- Cannot perform any workflow action

---

#### PO_CO (PO Concern Officer)
- View visits for their PO (status is not SAVED)
- Add and edit own PO comments on observations (while not locked and observation not resolved)
- Submit visit to PO SO
- View analytics (scoped to own PO only)

---

#### PO_SO (PO Supervisor Officer)
- View visits for their PO
- Forward a visit to PO CO
- Send a visit back to PO CO
- Approve and send visit back to PKSF (triggers comment finalization)
- Add PO comments on observations
- View analytics (scoped to own PO only)

---

## 4. Functional Requirements

### 4.1 Authentication

- Login via emp_id and password
- Returns a short-lived **access token** (15 minutes) and a **refresh token** (7 days)
- All API requests must include the access token in the `Authorization: Bearer <token>` header
- Refresh token endpoint issues a new access token without requiring re-login
- Logout invalidates the refresh token (stored in the database)
- Spring Security enforces role-based access on all protected endpoints

### 4.2 Visit Management

#### 4.2.1 Create a Visit

A **PKSF CO** creates a visit with the following information:
- PO Code (selected from assigned POs)
- Visit From Date and To Date
- Visit Type: `ONSITE` or `OFFSITE`
- Observation Department (auto-filled from logged-in user's department)

On creation, a unique **Visit Code** is auto-generated in the format `V-{PO_CODE}-{SL}` (e.g., `V-007-01`). A per-PO sequential counter in `acm_visit_tracker` tracks the serial number.

Initial status: `SAVED`. The creating officer is set as `current_desk_emp_id`.

#### 4.2.2 Add Observations to a Visit

While a visit is in `SAVED` status, the PKSF CO can add one or more observations. Each observation includes:
- Observation Category: `FINANCIAL`, `OPERATIONAL`, `COMPLIANCE`, `GOVERNANCE`
- PKSF Observation (the finding/issue description)
- Direction to PO (what PKSF expects the PO to do) (optional)
- Priority: `LOW`, `MEDIUM`, `HIGH`
- Action Matrix flag: `Y` or `N`
- Letter Issue Date (optional)
- Letter Response Date (optional)
- File attachments (up to 5 files, stored on local filesystem)

Initial resolution status of every observation: `OPEN`

#### 4.2.3 Edit / Delete Visit and Observations

- Visits can be edited only while in `SAVED` status
- Observations can be edited or deleted only while the visit is in `SAVED` status
- PO CO cannot edit or delete their own comments once an observation is `RESOLVED`

#### 4.2.4 Submit a Visit

PKSF CO submits the visit to their primary PKSF supervisor. Status changes to `PKSF_CO_SUBMITTED`. Visit moves to PKSF SO's desk.

A **remark** (optional text) can be added at submission. Remarks are logged and shown in the visit timeline.

#### 4.2.5 Visit Lifecycle (see Section 5.1 for full state machine)

### 4.3 Comment System

Each observation has a comment thread where PKSF and PO sides can communicate.

- **PKSF CO / PKSF SO** add comments with `comment_source = 'PKSF'`
- **PO CO / PO SO** add comments with `comment_source = 'PO'`
- Comments can have file attachments (up to 3 files)

**Visibility Rules** (governed by `is_draft` flag):
- `is_draft = 0` — comment is in progress (a draft). Visible only to the author and their immediate supervisor.
- `is_draft = 1` — comment is finalized and published. Visible to both PKSF and PO sides.

When PO SO approves the visit and sends it back to PKSF (`approvePoResponse`), all PO CO comments with `is_draft = 0` are automatically flipped to `is_draft = 1`, making them visible to PKSF.

**Edit / Delete rules:**
- A comment can only be edited or deleted while `is_draft = 0` (not yet finalized)
- PO CO cannot edit or delete a comment on a `RESOLVED` observation
- A supervisor can delete any draft comment from their side

### 4.4 Observation Resolution

Resolution is an observation-level process that begins after the visit reaches `PO_SO_APPROVED`.

**Resolution States:** `OPEN` → `PENDING_RESOLVED` → `RESOLVED` (or back to `OPEN`)

Steps:
1. **PO CO** addresses the observation. If `action_matrix = Y`, they must provide a formal action plan in their comment.
2. **PKSF CO** reviews the PO's response and comments, then marks the observation as `PENDING_RESOLVED`.
3. **PKSF SO** makes the final decision:
   - **Approve → Resolved:** Marks observation as `RESOLVED`. No further action needed.
   - **Return to PO:** Marks observation back to `OPEN`. PO must address it again.
4. This cycle repeats until PKSF SO is satisfied and marks the observation `RESOLVED`.

When all observations of a visit are `RESOLVED`, the visit is effectively complete.

### 4.5 Action Matrix Constraint

When an observation has `action_matrix = Y`, the PO is required to provide a formal **action plan** as part of their comment. The system enforces this — PKSF CO cannot mark an action matrix observation as `PENDING_RESOLVED` unless the PO CO has at least one active comment on that observation.

### 4.6 Senior Management (SM) Comments

Users with roles `SM_MD`, `SM_DMD`, `SM_SGM` can add read-only advisory comments on any visit. These are stored in a dedicated SM comments table and are not part of the observation workflow.

### 4.7 Analytics Dashboard

All users have access to an analytics dashboard scoped by their role:

| Role | Data Scope |
|---|---|
| Super_Admin, Admin, SM_MD | All POs — unrestricted |
| PKSF_CO, PKSF_SUPERVISOR, SM_DMD, SM_SGM | Only assigned POs (from `user_po_assignments_pksf`) |
| PO_CO, PO_SUPERVISOR | Only their own PO |

**KPI Cards (8 total):**
1. Total Visits
2. Open Visits (not completed)
3. Completed Visits (status = PO_APPROVED)
4. On My Desk (visits where `current_desk_emp_id` = logged-in user) — hidden for PO users
5. Pending Review (observations in PENDING_RESOLVED state) — shown for PO users instead of "On My Desk"
6. Total Observations
7. Resolved Observations
8. Action Matrix Observations

**Charts:**
- Visit status distribution (donut)
- Observation resolution distribution (donut)
- Observations by priority (bar)
- Observations by category (horizontal bar)
- Monthly trend — visits and observations over last 12 months (dual-line)
- PO comparison — open vs resolved observations per PO (stacked horizontal bar, top 12)

**Filters:** Period (1 month, 3 months, 6 months, 1 year, all time) and PO selection (for non-PO users)

### 4.8 Reports

Two reports are available (PKSF side only):

1. **Action Matrix Register (PDF):** All resolved observations across all POs, sorted by visit date.
2. **Action Matrix Filtered Report (PDF / Excel):** Same data filtered by PO and/or observation category. User selects filters on a form, then generates the report.

Report columns: Serial No., PO Code, Visit Date, Observation Category, Priority, Status, Closed On.

### 4.9 User Management

Managed by `Admin` / `Super_Admin` roles.

Operations:
- Create user (name, email, password, emp_id, designation, department, unit, user_type, po_code)
- Assign / remove roles
- Assign / remove PO assignments (for PKSF users)
- Set supervisor relationships
- Activate / deactivate users

### 4.10 Notifications (In-App)

Notifications are triggered on key workflow events. Designed for extensibility (email can be added later with no structural changes).

| Event | Who gets notified |
|---|---|
| Visit submitted | PKSF SO (the supervisor) |
| Visit sent back (rejected) | PKSF CO |
| Visit forwarded to PO | PO SO |
| Visit forwarded to PO CO | PO CO |
| Visit submitted back to PKSF SO (by PO CO) | PO SO |
| Visit approved by PO SO | PKSF CO |
| Observation marked as Pending Resolved | PKSF CO |
| Observation resolved by PKSF SO | PO CO |
| Observation returned to PO | PO CO |
| New SM comment added | Visit creator (PKSF CO) |

Unread notification count shown in the navbar. Notifications marked as read individually or all at once.

---

## 5. Business Logic

### 5.1 Visit Status State Machine

```
SAVED
  │
  │ PKSF CO submits
  ▼
PKSF_CO_SUBMITTED
  │
  ├─── PKSF SO sends back ──► SAVED  (back to CO for correction)
  │
  │ PKSF SO approves and forwards to PO
  ▼
PO_SO_REVIEW  (at PO SO's desk)
  │
  │ PO SO forwards to PO CO
  ▼
PO_CO_REVIEW  (at PO CO's desk)
  │
  │ PO CO submits to PO SO
  ▼
PO_CO_SUBMITTED  (at PO SO's desk)
  │
  ├─── PO SO sends back ──► PO_CO_REVIEW  (back to PO CO)
  │
  │ PO SO approves and sends to PKSF
  ▼
PO_SO_APPROVED  (at PKSF CO's desk)
  │
  │ Observation-level resolution cycle begins (see 5.2)
  │ Visit status remains PO_APPROVED throughout resolution
```

**Notes:**
- `current_desk_emp_id` always reflects whose turn it is to act
- Every status transition creates a record in `acm_visit_movements`
- Every remark entered during a transition is stored in `acm_visit_remarks`
- When PKSF SO forwards to PO, the visit goes to the **PO SO's desk first** — not directly to PO CO
- When PO SO sends back to PO CO, the system finds the PO CO from the last PO movement record

### 5.2 Observation Resolution Cycle

After a visit reaches `PO_SO_APPROVED`, each observation goes through its own resolution cycle:

```
OPEN
  │
  │ PO CO addresses observation (provides action plan if action_matrix=Y)
  │ PKSF CO reviews PO response, marks as Pending Resolved
  ▼
PENDING_RESOLVED
  │
  ├─── PKSF SO not satisfied ──► OPEN  (PO must address again)
  │
  │ PKSF SO satisfied
  ▼
RESOLVED  ← terminal state
```

**Key rules:**
- Only PKSF CO (or Admin/Super_Admin) can mark an observation as `PENDING_RESOLVED`
- Only PKSF SO (or Admin/Super_Admin) can mark as `RESOLVED` or return to `OPEN`
- If `action_matrix = Y`, PO CO must have an active comment before PKSF CO can mark pending resolved
- PO CO cannot edit/delete comments on a `RESOLVED` observation
- The cycle can repeat any number of times before reaching `RESOLVED`

### 5.3 Comment Visibility Logic

```
For any given comment, it is visible to user X if:

1. comment.is_draft = 1 (finalized) → visible to EVERYONE on both sides
2. comment.created_by = X.emp_id → user sees their own draft
3. X is a PO Supervisor AND comment.source = 'PO' AND visit.status = 'PO_SUBMITTED' → PO SO sees PO CO drafts
4. X is a PKSF Supervisor AND comment.source = 'PKSF' → PKSF SO sees PKSF CO drafts
5. None of the above → NOT visible
```

**Finalization trigger:** When PO SO approves the visit (`PO_CO_SUBMITTED → PO_SO_APPROVED`), the system automatically sets `is_draft = 1` for all PO CO comments with `is_draft = 0` on this visit. This "publishes" the PO response to PKSF.

### 5.4 Visit Code Generation

Visit codes are generated per PO using a sequential counter stored in `acm_visit_tracker`.

Format: `V-{PO_CODE}-{ZERO_PADDED_SL}`

Example: Third visit to PO `007` → `V-007-03`

The counter update and visit creation happen inside a **database transaction with a row-level lock** on `acm_visit_tracker` to prevent duplicate codes under concurrent requests.

### 5.5 Data Scoping Rules

Data visibility is enforced at the query level, not just the UI level:

```
Super_Admin / Admin / SM_MD  →  all POs, all visits, all observations

PKSF_CO / PKSF_SO           →  visits where:
                                  created_by = emp_id
                                  OR current_desk_emp_id = emp_id
                                  OR po_code IN assigned_po_codes

SM_DMD / SM_SGM              →  same as PKSF_CO/SO but filtered to assigned POs only
                                  AND visit.status NOT IN (SAVED, SUBMITTED, REJECTED)

PO_CO / PO_SO                →  visits where po_code = user.po_code
                                  AND visit.status != SAVED
```

### 5.6 File Attachments

- Observation attachments: max 5 files
- Comment attachments: max 3 files
- Remark attachments: supported
- Files stored at: `/storage/attachments/{type}/{entity_id}/{filename}`
- Files served through authenticated download endpoints (never directly accessible via static URL)
- Accepted MIME types: PDF, JPG, JPEG, PNG, XLSX, DOCX

---

## 6. Database Schema

All tables use PostgreSQL. All timestamps are stored in UTC. Application converts to UTC+6 for display.

### 6.1 `users`

| Column | Type | Notes |
|---|---|---|
| `id` | BIGSERIAL PK | Auto-increment |
| `emp_id` | VARCHAR(20) UNIQUE | Employee ID — used as the business key across all tables |
| `name` | VARCHAR(255) | Full name |
| `email` | VARCHAR(255) UNIQUE | Login email |
| `password` | VARCHAR(255) | Bcrypt hashed |
| `user_type` | VARCHAR(20) | `PKSF` or `PO` |
| `designation` | VARCHAR(255) NULLABLE | Job title |
| `dept_id` | VARCHAR(50) NULLABLE | Department ID |
| `dept_name` | VARCHAR(255) NULLABLE | Department name |
| `unit_id` | VARCHAR(50) NULLABLE | Unit ID |
| `unit_name` | VARCHAR(255) NULLABLE | Unit name |
| `po_code` | VARCHAR(20) NULLABLE | Populated only for PO users |
| `is_active` | BOOLEAN | Default true — inactive users cannot log in |
| `created_by` | VARCHAR(20) NULLABLE | emp_id of the admin who created this user |
| `updated_by` | VARCHAR(20) NULLABLE | emp_id of the admin who last updated this user |
| `created_at` | TIMESTAMP | |
| `updated_at` | TIMESTAMP | |

### 6.2 `roles`

| Column | Type | Notes |
|---|---|---|
| `id` | BIGSERIAL PK | |
| `name` | VARCHAR(255) | Role name (e.g., `PKSF_CO`) |
| `description` | TEXT NULLABLE | Human-readable description |
| `role_group` | VARCHAR(100) | Grouping (e.g., `PKSF`, `PO`, `SM`) |
| `is_active` | BOOLEAN | Default true |
| `created_by` | VARCHAR(20) NULLABLE | |
| `updated_by` | VARCHAR(20) NULLABLE | |
| `created_at` | TIMESTAMP | |
| `updated_at` | TIMESTAMP | |

### 6.3 `user_roles`

| Column | Type | Notes |
|---|---|---|
| `id` | BIGSERIAL PK | |
| `emp_id` | VARCHAR(20) FK → users.emp_id | |
| `role_id` | BIGINT FK → roles.id | |
| `created_by` | VARCHAR(20) NULLABLE | |
| `updated_by` | VARCHAR(20) NULLABLE | |
| `created_at` | TIMESTAMP | |
| `updated_at` | TIMESTAMP NULLABLE | |

Unique constraint: `(emp_id, role_id)`

### 6.4 `user_supervisors_pksf`

| Column | Type | Notes |
|---|---|---|
| `id` | BIGSERIAL PK | |
| `emp_id` | VARCHAR(20) FK → users.emp_id | The subordinate |
| `supervisor_emp_id` | VARCHAR(20) FK → users.emp_id | The supervisor |
| `is_primary` | BOOLEAN | Only one primary supervisor per user |
| `created_by` | VARCHAR(20) | |
| `updated_by` | VARCHAR(20) NULLABLE | |
| `created_at` | TIMESTAMP | |
| `updated_at` | TIMESTAMP | |

Unique constraint: `(emp_id, supervisor_emp_id)`

### 6.5 `user_supervisors_po`

Stores supervisor relationships for PO-side users.

| Column | Type | Notes |
|---|---|---|
| `id` | BIGSERIAL PK | |
| `emp_id` | VARCHAR(20) FK → users.emp_id | The subordinate |
| `supervisor_emp_id` | VARCHAR(20) FK → users.emp_id | The supervisor |
| `po_code` | VARCHAR(5) | PO this supervisor relationship belongs to |
| `is_primary` | BOOLEAN | Only one primary supervisor per user |
| `created_by` | VARCHAR(20) | |
| `created_at` | TIMESTAMP | |
| `updated_by` | VARCHAR(20) NULLABLE | |
| `updated_at` | TIMESTAMP NULLABLE | |

Unique constraint: `(emp_id, supervisor_emp_id)`

### 6.6 `user_po_assignments_pksf`

| Column | Type | Notes |
|---|---|---|
| `id` | BIGSERIAL PK | |
| `emp_id` | VARCHAR(20) FK → users.emp_id | PKSF-side employee |
| `po_code` | VARCHAR(5) | PO they are assigned to |
| `is_active` | BOOLEAN | Default true |
| `created_by` | VARCHAR(20) | |
| `updated_by` | VARCHAR(20) NULLABLE | |
| `created_at` | TIMESTAMP | |
| `updated_at` | TIMESTAMP NULLABLE | |

Unique constraint: `(emp_id, po_code)`

### 6.7 `user_po_assignments_po`

Stores assignments of PO-side users to their operational scope within a PO.

| Column | Type | Notes |
|---|---|---|
| `emp_id` | VARCHAR(20) FK → users.emp_id | PO-side employee |
| `po_code` | VARCHAR(5) | PO they belong to |
| `operation_category` | VARCHAR(255) | Operational category string for this assignment |
| `is_active` | BOOLEAN | Default true |
| `created_by` | VARCHAR(20) | |
| `created_at` | TIMESTAMP | |
| `updated_by` | VARCHAR(20) NULLABLE | |
| `updated_at` | TIMESTAMP NULLABLE | |

Primary key: `(emp_id, po_code)`

### 6.8 `acm_visit_tracker`

| Column | Type | Notes |
|---|---|---|
| `po_code` | VARCHAR(5) PK | |
| `sl` | INTEGER | Last used serial number for this PO |

### 6.9 `acm_visits`

| Column | Type | Notes |
|---|---|---|
| `id` | BIGSERIAL PK | |
| `visit_code` | VARCHAR(60) UNIQUE | e.g., `V-007-03` |
| `po_code` | VARCHAR(5) | PO being visited |
| `visit_from_date` | DATE | Visit start date |
| `visit_to_date` | DATE | Visit end date |
| `visit_type` | VARCHAR(20) | `ONSITE` or `OFFSITE` |
| `observation_dept` | VARCHAR(100) NULLABLE | Department of observing officer |
| `status` | VARCHAR(60) | Current visit status (see state machine) |
| `current_desk_emp_id` | VARCHAR(20) NULLABLE | Whose turn it is to act |
| `created_by` | VARCHAR(20) | emp_id of creator |
| `updated_by` | VARCHAR(20) NULLABLE | |
| `created_at` | TIMESTAMP | |
| `updated_at` | TIMESTAMP | |

Indexes: `(status, po_code)`, `created_by`, `current_desk_emp_id`

**Valid status values:** `SAVED`, `PKSF_CO_SUBMITTED`, `PKSF_SO_SENT_BACK`, `PO_SO_REVIEW`, `PO_CO_REVIEW`, `PO_CO_SUBMITTED`, `PO_SO_APPROVED`

### 6.10 `acm_observations`

| Column | Type | Notes |
|---|---|---|
| `id` | BIGSERIAL PK | |
| `visit_id` | BIGINT FK → acm_visits.id CASCADE DELETE | |
| `observation_category` | VARCHAR(50) | `FINANCIAL`, `OPERATIONAL`, `COMPLIANCE`, `GOVERNANCE` |
| `pksf_observation` | TEXT | The observation/finding by PKSF |
| `direction_to_po` | TEXT | Instructions given to PO | (optional)
| `priority` | VARCHAR(10) | `LOW`, `MEDIUM`, `HIGH` |
| `action_matrix` | CHAR(1) | `Y` or `N` |
| `letter_issue_date` | DATE NULLABLE | Date letter was issued to PO | (optional)
| `letter_response_date` | DATE NULLABLE | Expected PO response date | (optional)
| `resolution_status` | VARCHAR(30) | `OPEN`, `PENDING_RESOLVED`, `RESOLVED` |
| `created_by` | VARCHAR(20) | |
| `updated_by` | VARCHAR(20) NULLABLE | |
| `created_at` | TIMESTAMP | |
| `updated_at` | TIMESTAMP | |

Indexes: `visit_id`, `(visit_id, resolution_status)`

### 6.11 `acm_observation_attachments`

| Column | Type | Notes |
|---|---|---|
| `id` | BIGSERIAL PK | |
| `observation_id` | BIGINT FK → acm_observations.id CASCADE DELETE | |
| `file_name` | VARCHAR(400) | Original filename |
| `file_path` | VARCHAR(1000) | Path relative to storage root |
| `file_type` | VARCHAR(100) NULLABLE | MIME type |
| `file_size` | INTEGER NULLABLE | Size in bytes |
| `created_by` | VARCHAR(20) | |
| `created_at` | TIMESTAMP | |

### 6.12 `acm_visit_movements`

| Column | Type | Notes |
|---|---|---|
| `id` | BIGSERIAL PK | |
| `visit_id` | BIGINT FK → acm_visits.id CASCADE DELETE | |
| `movement_side` | VARCHAR(10) | `PKSF` or `PO` |
| `from_emp_id` | VARCHAR(20) | Who sent |
| `to_emp_id` | VARCHAR(20) | Who received |
| `action_type` | VARCHAR(80) | e.g., `SUBMITTED`, `SENT_BACK`, `FORWARDED_TO_PO`, |
| `remarks` | TEXT NULLABLE | Optional remark at transition |
| `created_by` | VARCHAR(20) | |
| `created_at` | TIMESTAMP | |

### 6.13 `acm_visit_remarks`

| Column | Type | Notes |
|---|---|---|
| `id` | BIGSERIAL PK | |
| `visit_id` | BIGINT FK → acm_visits.id CASCADE DELETE | |
| `movement_id` | BIGINT NULLABLE FK → acm_visit_movements.id SET NULL | |
| `remarks` | TEXT | The remark content |
| `created_by` | VARCHAR(20) | |
| `created_at` | TIMESTAMP | |

### 6.14 `acm_visit_remark_attachments`

| Column | Type | Notes |
|---|---|---|
| `id` | BIGSERIAL PK | |
| `remark_id` | BIGINT FK → acm_visit_remarks.id CASCADE DELETE | |
| `file_name` | VARCHAR(400) | |
| `file_path` | VARCHAR(1000) | |
| `file_type` | VARCHAR(100) NULLABLE | |
| `file_size` | INTEGER NULLABLE | |
| `created_by` | VARCHAR(20) | |
| `created_at` | TIMESTAMP | |

### 6.15 `acm_observation_comments`

| Column | Type | Notes |
|---|---|---|
| `id` | BIGSERIAL PK | |
| `observation_id` | BIGINT FK → acm_observations.id CASCADE DELETE | |
| `comment_source` | VARCHAR(10) | `PKSF` or `PO` |
| `comment_detail` | TEXT | The comment body |
| `is_draft` | SMALLINT | `0` = in progress, `1` = finalized/published |
| `created_by` | VARCHAR(20) | emp_id of author |
| `updated_by` | VARCHAR(20) NULLABLE | |
| `created_at` | TIMESTAMP | |
| `updated_at` | TIMESTAMP | |

Indexes: `observation_id`, `(observation_id, is_draft)`, `(observation_id, created_by)`

### 6.16 `acm_observation_comment_attachments`

| Column | Type | Notes |
|---|---|---|
| `id` | BIGSERIAL PK | |
| `comment_id` | BIGINT FK → acm_observation_comments.id CASCADE DELETE | |
| `file_name` | VARCHAR(400) | |
| `file_path` | VARCHAR(1000) | |
| `file_type` | VARCHAR(100) NULLABLE | |
| `file_size` | INTEGER NULLABLE | |
| `created_by` | VARCHAR(20) | |
| `created_at` | TIMESTAMP | |

### 6.17 `notifications` (To Be Built)

| Column | Type | Notes |
|---|---|---|
| `id` | BIGSERIAL PK | |
| `recipient_emp_id` | VARCHAR(20) | Who receives the notification |
| `type` | VARCHAR(100) | e.g., `VISIT_SUBMITTED`, `OBSERVATION_RESOLVED` |
| `title` | TEXT | Short notification title |
| `body` | TEXT NULLABLE | Longer description |
| `entity_type` | VARCHAR(50) NULLABLE | e.g., `visit`, `observation` |
| `entity_id` | BIGINT NULLABLE | ID of the related entity |
| `is_read` | BOOLEAN | Default false |
| `created_at` | TIMESTAMP | |
| `read_at` | TIMESTAMP NULLABLE | |

### 6.18 `audit_logs` (To Be Built)

| Column | Type | Notes |
|---|---|---|
| `id` | BIGSERIAL PK | |
| `emp_id` | VARCHAR(20) | Who performed the action |
| `action` | VARCHAR(100) | e.g., `CREATE_VISIT`, `RESOLVE_OBSERVATION` |
| `entity_type` | VARCHAR(50) | e.g., `visit`, `observation` |
| `entity_id` | VARCHAR(100) | ID of affected entity |
| `old_value` | JSONB NULLABLE | Previous state (for updates) |
| `new_value` | JSONB NULLABLE | New state |
| `ip_address` | VARCHAR(45) NULLABLE | |
| `user_agent` | TEXT NULLABLE | |
| `created_at` | TIMESTAMP | |

---

## 7. API Design

### 7.1 Standard Response Envelope

All API responses follow this structure:

**Success:**
```json
{
  "success": true,
  "message": "Visit created successfully.",
  "data": { ... }
}
```

**Success with pagination:**

All list endpoints use **server-side pagination**. The client sends `page` (0-based) and `size` (default 25) as query parameters. The server returns the requested page along with total count metadata so the frontend can render pagination controls without loading all records.

```json
{
  "success": true,
  "message": "Visits fetched successfully.",
  "data": {
    "content": [ ... ],
    "page": 0,
    "size": 25,
    "totalElements": 142,
    "totalPages": 6,
    "last": false
  }
}
```

**Error:**
```json
{
  "success": false,
  "message": "Validation failed.",
  "errors": {
    "visit_from_date": "Visit from date is required.",
    "po_code": "PO code must be 3–5 characters."
  }
}
```

**HTTP Status Codes:**
- `200 OK` — successful read/update
- `201 Created` — successful creation
- `400 Bad Request` — validation error
- `401 Unauthorized` — no or invalid token
- `403 Forbidden` — authenticated but not permitted
- `404 Not Found` — resource does not exist
- `409 Conflict` — business rule violation (e.g., wrong status for action)
- `500 Internal Server Error` — unexpected server error

### 7.2 Authentication Endpoints

```
POST   /api/v1/auth/login
POST   /api/v1/auth/refresh
POST   /api/v1/auth/logout
GET    /api/v1/auth/me
```

**POST /api/v1/auth/login**

Request:
```json
{ "emp_id": "E001", "password": "secret" }
```
Response:
```json
{
  "success": true,
  "data": {
    "access_token": "eyJ...",
    "refresh_token": "eyJ...",
    "expires_in": 900,
    "user": {
      "emp_id": "E001",
      "name": "Karim Ahmed",
      "email": "user@pksf.org",
      "user_type": "PKSF",
      "roles": ["PKSF_CO"],
      "po_code": null
    }
  }
}
```

**POST /api/v1/auth/refresh**

Request: `{ "refresh_token": "eyJ..." }`
Response: `{ "access_token": "eyJ...", "expires_in": 900 }`

### 7.3 Visit Endpoints

```
GET    /api/v1/visits                    — List visits (paginated, filtered)
POST   /api/v1/visits                    — Create visit
GET    /api/v1/visits/{id}               — Get visit detail
PUT    /api/v1/visits/{id}               — Update visit (SAVED status only)
DELETE /api/v1/visits/{id}               — Delete visit (SAVED status only)

POST   /api/v1/visits/{id}/submit        — Submit to PKSF SO
POST   /api/v1/visits/{id}/send-back     — PKSF SO sends back to CO
POST   /api/v1/visits/{id}/forward-to-po — PKSF SO forwards to PO SO
POST   /api/v1/visits/{id}/po-forward    — PO SO forwards to PO CO
POST   /api/v1/visits/{id}/po-submit     — PO CO submits to PO SO
POST   /api/v1/visits/{id}/po-send-back  — PO SO sends back to PO CO
POST   /api/v1/visits/{id}/po-approve    — PO SO approves and sends to PKSF

GET    /api/v1/visits/{id}/timeline      — Get visit movement timeline
```

**Query parameters for GET /api/v1/visits:**
- `page` — page number, 0-based (default: 0)
- `size` — records per page (default: 25, max: 100)
- `sort` — column to sort by (default: `created_at`)
- `direction` — `asc` or `desc` (default: `desc`)
- `status` — filter by visit status
- `po_code` — filter by PO
- `visit_type` — `ONSITE` or `OFFSITE`
- `desk` — boolean, only visits at logged-in user's desk
- `search` — search by visit code or PO code
- `from_date`, `to_date` — date range filter on visit date

### 7.4 Observation Endpoints

```
GET    /api/v1/visits/{visitId}/observations              — List observations for a visit
POST   /api/v1/visits/{visitId}/observations              — Add observation
PUT    /api/v1/visits/{visitId}/observations/{obsId}      — Update observation
DELETE /api/v1/visits/{visitId}/observations/{obsId}      — Delete observation

POST   /api/v1/observations/{obsId}/mark-pending-resolved — PKSF CO marks pending resolved
POST   /api/v1/observations/{obsId}/resolve               — PKSF SO resolves
POST   /api/v1/observations/{obsId}/reopen                — PKSF SO sends back to PO
```

### 7.5 Comment Endpoints

```
GET    /api/v1/observations/{obsId}/comments              — List visible comments
POST   /api/v1/observations/{obsId}/comments              — Add comment
PUT    /api/v1/observations/{obsId}/comments/{commentId}  — Update comment
DELETE /api/v1/observations/{obsId}/comments/{commentId}  — Delete comment
```

### 7.6 Remark Endpoints

```
GET    /api/v1/visits/{visitId}/remarks   — List remarks for a visit
POST   /api/v1/visits/{visitId}/remarks   — Add remark
```

### 7.7 Analytics Endpoints

```
GET    /api/v1/analytics                  — Get full analytics data (stats + charts)
```

Query parameters: `period` (1month / 3months / 6months / 1year / all), `po_code`

### 7.8 Report Endpoints

```
GET    /api/v1/reports/action-matrix                  — Filter form data (PO list, categories)
GET    /api/v1/reports/action-matrix/download?format=pdf|excel&po_code=&category=
```

### 7.9 User Management Endpoints (Admin Only)

```
GET    /api/v1/admin/users                       — List users (server-side paginated, query params: page, size, search, role, user_type)
POST   /api/v1/admin/users                       — Create user
GET    /api/v1/admin/users/{empId}               — Get user detail
PUT    /api/v1/admin/users/{empId}               — Update user
POST   /api/v1/admin/users/{empId}/roles         — Assign roles
DELETE /api/v1/admin/users/{empId}/roles/{roleId} — Remove role
POST   /api/v1/admin/users/{empId}/po-assignments — Add PO assignment
DELETE /api/v1/admin/users/{empId}/po-assignments/{poCode} — Remove PO assignment
POST   /api/v1/admin/users/{empId}/supervisor    — Set supervisor
```

### 7.10 Notification Endpoints

```
GET    /api/v1/notifications              — List notifications for logged-in user (server-side paginated, query params: page, size, is_read)
PATCH  /api/v1/notifications/{id}/read    — Mark one as read
PATCH  /api/v1/notifications/read-all     — Mark all as read
GET    /api/v1/notifications/unread-count — Get unread count (for navbar badge)
```

### 7.11 File Download Endpoint

```
GET    /api/v1/files/{fileType}/{fileId}  — Download/view a file (authenticated)
```

`fileType`: `observation-attachment`, `comment-attachment`, `remark-attachment`

---

## 8. Architecture

### 8.1 Monorepo Structure

```
/
├── backend/                    — Spring Boot application
│   ├── src/main/java/com/pksf/acm/
│   │   ├── config/                   — Security, CORS, Swagger, beans
│   │   ├── visit/
│   │   │   ├── VisitController.java
│   │   │   ├── VisitService.java
│   │   │   ├── VisitRepository.java
│   │   │   ├── ObservationController.java
│   │   │   ├── ObservationService.java
│   │   │   ├── ObservationRepository.java
│   │   │   ├── Visit.java            — JPA entity
│   │   │   ├── Observation.java      — JPA entity
│   │   │   ├── VisitStatus.java      — enum: SAVED, SUBMITTED, PO_SO_REVIEW, ...
│   │   │   ├── ResolutionStatus.java — enum: OPEN, PENDING_RESOLVED, RESOLVED
│   │   │   ├── Priority.java         — enum: LOW, MEDIUM, HIGH
│   │   │   ├── ObservationCategory.java — enum: FINANCIAL, OPERATIONAL, COMPLIANCE, GOVERNANCE
│   │   │   └── dto/                  — VisitRequest.java, VisitResponse.java, ObservationRequest.java
│   │   ├── comment/
│   │   │   ├── CommentController.java
│   │   │   ├── CommentService.java
│   │   │   ├── CommentRepository.java
│   │   │   ├── ObservationComment.java — JPA entity
│   │   │   ├── CommentSource.java    — enum: PKSF, PO
│   │   │   └── dto/                  — CommentRequest.java, CommentResponse.java
│   │   ├── user/
│   │   │   ├── UserController.java
│   │   │   ├── UserService.java
│   │   │   ├── UserRepository.java
│   │   │   ├── User.java             — JPA entity
│   │   │   └── dto/                  — UserRequest.java, UserResponse.java
│   │   ├── notification/
│   │   │   ├── NotificationController.java
│   │   │   ├── NotificationService.java
│   │   │   ├── NotificationRepository.java
│   │   │   ├── Notification.java     — JPA entity
│   │   │   └── dto/                  — NotificationResponse.java
│   │   ├── analytics/
│   │   │   ├── AnalyticsController.java
│   │   │   ├── AnalyticsService.java
│   │   │   └── dto/                  — AnalyticsResponse.java
│   │   ├── report/
│   │   │   ├── ReportController.java
│   │   │   ├── ReportService.java
│   │   │   └── dto/
│   │   ├── shared/
│   │   │   ├── exception/            — Global error handling, custom exceptions
│   │   │   ├── response/             — Standard API response wrapper
│   │   │   ├── audit/                — AuditService
│   │   │   └── security/             — JWT filter, UserDetailsService, Spring Security config
│   │   └── AcmApplication.java
│   ├── src/main/resources/
│   │   ├── application.yml
│   │   ├── application-dev.yml
│   │   └── application-prod.yml
│   └── Dockerfile
│
├── frontend/                   — Next.js application
│   ├── app/                    — App Router pages
│   │   ├── (auth)/             — Login page (no layout)
│   │   ├── (dashboard)/        — Authenticated pages with sidebar layout
│   │   │   ├── visits/         — Visit list, detail, create, edit
│   │   │   ├── analytics/      — Analytics dashboard
│   │   │   ├── reports/        — Report generation
│   │   │   ├── notifications/  — Notification center
│   │   │   └── admin/          — User management
│   ├── components/             — Reusable UI components
│   ├── lib/                    — API client, auth helpers, utilities
│   ├── hooks/                  — Custom React hooks
│   ├── store/                  — Zustand global state (auth, notifications)
│   ├── types/                  — TypeScript type definitions
│   └── messages/               — i18n translation files (en.json, bn.json)
│
├── docs/
│   └── system-specification.md
│
└── docker-compose.yml
```

### 8.2 Backend Architecture (Light DDD)

The backend uses a **light Domain-Driven Design** approach: code is organized by domain feature (not by technical layer), with a standard Spring Boot `Controller → Service → Repository` flow inside each feature package. No aggregates, no CQRS, no command handlers — just clean feature separation using domain language.

#### Flow

```
Controller → Service → Repository
```

#### Package Organization

All code for a domain lives together. Technical layers (controller, service, repository) are inside the domain package — not the other way around.

```
visit/
  ├── VisitController.java        — REST endpoints
  ├── VisitService.java           — Business logic (create, submit, forward, approve)
  ├── VisitRepository.java        — Spring Data JPA interface
  ├── Visit.java                  — JPA entity
  ├── Observation.java            — JPA entity
  ├── ObservationController.java
  ├── ObservationService.java
  ├── ObservationRepository.java
  ├── VisitStatus.java            — enum: SAVED, SUBMITTED, PO_SO_REVIEW, ...
  ├── ResolutionStatus.java       — enum: OPEN, PENDING_RESOLVED, RESOLVED
  ├── Priority.java               — enum: LOW, MEDIUM, HIGH
  ├── ObservationCategory.java    — enum: FINANCIAL, OPERATIONAL, COMPLIANCE, GOVERNANCE
  └── dto/
      ├── CreateVisitRequest.java
      ├── VisitResponse.java
      └── ObservationRequest.java
```

Enums use the domain's own language and are defined inside the feature package that owns them.

#### Domain Events (for Notifications)

Spring's built-in `ApplicationEvent` is used as a lightweight event mechanism. `VisitService` publishes an event after saving; `NotificationService` listens and creates the notification. This keeps the two features decoupled without a heavy event bus.

```java
// VisitService publishes after save
applicationEventPublisher.publishEvent(new VisitSubmittedEvent(visit));

// NotificationService listens
@EventListener
public void onVisitSubmitted(VisitSubmittedEvent event) {
    // create and save notification records
}
```

| Event | Triggered When |
|---|---|
| `VisitSubmittedEvent` | PKSF CO submits visit |
| `VisitSentBackEvent` | PKSF SO sends back to CO |
| `VisitForwardedToPoEvent` | PKSF SO forwards to PO |
| `VisitPoApprovedEvent` | PO SO approves visit |
| `ObservationResolvedEvent` | PKSF SO marks resolved |
| `ObservationReturnedEvent` | PKSF SO returns observation to PO |

### 8.3 Security Architecture

- **Spring Security** with stateless JWT authentication
- `JwtAuthenticationFilter` runs on every request before controllers
- Token validation: signature check, expiry check, user active check
- Refresh tokens stored in the database (can be revoked server-side)
- Role-based access via `@PreAuthorize` annotations on controllers
- CORS configured to allow only the frontend origin
- Passwords hashed with **BCrypt**
- File download endpoints check ownership/visibility before serving

### 8.4 Frontend Architecture

- **Next.js App Router** with server and client components used appropriately
- **TanStack Query (React Query)** for all server state management (fetching, caching, invalidation)
- **Zustand** for lightweight global client state (auth token, current user, notification count)
- **Axios** with interceptors:
  - Attaches `Authorization: Bearer` header on every request
  - On `401` response: transparently calls refresh endpoint, retries original request
  - On refresh failure: redirects to login
- **next-intl** for English/Bengali internationalization
- All pages are **fully responsive** (mobile-first)
- Form handling with **React Hook Form** + **Zod** validation

### 8.5 File Storage

Files are stored on the local filesystem inside a Docker volume:

```
/storage/
  ├── observation-attachments/{observation_id}/{filename}
  ├── comment-attachments/{comment_id}/{filename}
  └── remark-attachments/{remark_id}/{filename}
```

Files are never served directly. The backend streams the file content after verifying the requesting user has access.

When email notifications are added in future, attachment URLs will be pre-signed temporary links generated by the backend.

### 8.6 Docker Setup

```yaml
# docker-compose.yml (overview)
services:
  postgres:
    image: postgres:16
    volumes:
      - postgres_data:/var/lib/postgresql/data

  backend:
    build: ./backend
    depends_on: [postgres]
    volumes:
      - file_storage:/storage
    environment:
      - SPRING_DATASOURCE_URL
      - JWT_SECRET
      - STORAGE_PATH=/storage

  frontend:
    build: ./frontend
    depends_on: [backend]
    environment:
      - NEXT_PUBLIC_API_URL

volumes:
  postgres_data:
  file_storage:
```

---

## 9. Coding Standards & Best Practices

### 9.1 Backend (Java / Spring Boot)

**General:**
- Follow **Java naming conventions**: `camelCase` for variables/methods, `PascalCase` for classes, `UPPER_SNAKE_CASE` for constants
- No magic strings or numbers — use enums or constants
- Keep controllers thin — no business logic; only request parsing, delegation, and response formatting
- All business logic lives in the service layer
- Constructors over field injection (`@Autowired`) for dependencies

**Error Handling:**
- One `@ControllerAdvice` global exception handler for all errors
- Custom exceptions per domain (e.g., `VisitNotFoundException`, `UnauthorizedActionException`, `BusinessRuleViolationException`)
- Never expose stack traces to API consumers in production

**Validation:**
- All incoming request bodies validated with Jakarta Bean Validation (`@Valid`, `@NotNull`, `@Size`, etc.)
- Custom validators for business-level checks (e.g., visit date range, PO code format)

**Audit Logging:**
- Every state-changing operation writes to `audit_logs` via a shared `AuditService`
- Call `AuditService` from the service layer after each state change
- Log: who, what action, on which entity, old and new state

**Security:**
- Every controller method annotated with `@PreAuthorize("hasRole('...')")`
- Data scoping enforced at the repository/query level — never rely on UI to hide data
- Never log passwords, tokens, or PII

**Comments:**
- Comment the *why*, not the *what*
- Every public method should have a concise Javadoc explaining its purpose, key parameters, and any non-obvious side effects

### 9.2 Frontend (Next.js)

**UI Component System — shadcn/ui:**

shadcn/ui is **not a component library in the traditional sense**. It is a collection of accessible, copy-pasteable components built on top of Radix UI (behavior and accessibility) and Tailwind CSS (styling).

Unlike libraries such as MUI or Ant Design that are installed as npm packages and live in `node_modules`, shadcn/ui works differently:
- You run `npx shadcn-ui add button` and the component source code is copied directly into your project at `components/ui/button.tsx`
- The code is **yours** — no external runtime dependency, no version lock-in
- Every component can be freely modified to match the design requirements
- You only add the components you actually use — no bloat

This approach gives the frontend engineer full control over every pixel while starting from a solid, accessible foundation. All base UI components (Button, Input, Select, Dialog, Table, Card, Badge, etc.) should be sourced from shadcn/ui and customized as needed.

License: **MIT — completely free**, including commercial use.

**General:**
- Use **TypeScript** strictly — no `any` types
- Follow **ESLint** and **Prettier** configurations
- Component names in `PascalCase`, files in `kebab-case`
- Separate concerns: API calls in `lib/api/`, UI logic in components, global state in stores

**Components:**
- Small, single-responsibility components
- Use **server components** for data-fetching where possible; **client components** only when interactivity is needed
- Props typed with TypeScript interfaces, not inline objects

**State Management:**
- Server state (lists, detail pages) → TanStack Query
- Global UI state (auth, sidebar, notification count) → Zustand
- Local transient state → `useState` / `useReducer`

**API Layer:**
- All API calls through a centralized Axios instance (`lib/api/client.ts`)
- Typed API response wrappers match the backend envelope exactly
- Error handling in one place — surface user-friendly messages from `errors` field

**i18n:**
- All UI text through `next-intl` translation keys — no hardcoded English strings in JSX
- Separate translation files: `messages/en.json` and `messages/bn.json`
- Date formatting respects UTC+6 and locale (e.g., Bengali month names)

**UI/UX:**
- Clean, minimal design — professional, not decorative
- Consistent spacing, typography, and color palette across all pages
- Loading skeletons instead of spinners for list/detail pages
- Toast notifications for action feedback (success, error)
- Confirmation modals for destructive actions
- Empty states for empty lists/charts
- All modals must not close on outside click (static backdrop)
- Forms show inline validation errors, not alert popups

---

## 10. Non-Functional Requirements

| Requirement | Target |
|---|---|
| **User Scale** | ~1,000 concurrent users |
| **API Response Time** | < 500ms for standard requests; < 2s for report generation |
| **Availability** | 99.5% uptime (on-premise SLA dependent) |
| **Authentication** | JWT — access token 15 min, refresh token 7 days |
| **Timezone** | UTC+6 (Asia/Dhaka) for all display |
| **Languages** | English (default), Bengali (bn-BD) switchable |
| **Responsive Design** | Mobile, tablet, and desktop |
| **File Size Limit** | Max 10 MB per file |
| **Allowed File Types** | PDF, JPG, JPEG, PNG, XLSX, DOCX |
| **Max Attachments** | 5 per observation, 3 per comment |
| **Data Retention** | No automatic deletion — all records kept indefinitely |
| **Browser Support** | Chrome, Firefox, Edge (latest 2 versions), Safari |

---

## 11. Future Scope

The following features are planned but not in the current build. The system should be designed with these in mind so they can be added without major refactoring.

### 11.1 Email Notifications

The notification system is already designed with a `type` field. Adding email delivery means:
- Connecting an SMTP service (or SES)
- A background job (Spring `@Scheduled` or a queue) processes unsent notifications and emails them
- No changes to the existing notification creation logic

### 11.2 Excel Export

The report endpoints already accept a `format` query parameter. Adding `format=excel`:
- Backend generates Excel using Apache POI
- Same data, same query — only the output format changes

### 11.3 Dashboard for Senior Management

A dedicated read-only dashboard for SM_MD, SM_DMD, SM_SGM with higher-level KPIs, trends, and cross-PO comparisons. The analytics infrastructure already supports this — it's a UI/UX and additional query work.

### 11.4 Advanced User Management

- Bulk user import via Excel
- Role change audit history
- User activity log (last login, actions taken)

### 11.5 Automated Reminders

If a visit or observation has been idle for N days, automatically send a reminder notification to the user on whose desk it sits.

### 11.6 Mobile Application

The API is designed as a standard REST API. A React Native or Flutter mobile app can consume the same endpoints with no backend changes.

---

*End of Document*

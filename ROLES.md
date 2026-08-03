# Role Reference

This document is the single source of truth for all role names used in the system.
Role names are hardcoded in the application logic — the name entered in the database **must match exactly** (case-sensitive) or access control will silently break.

---

## Existing Roles

| Role Name       | Group            | Description                                                                 |
|-----------------|------------------|-----------------------------------------------------------------------------|
| `Super_Admin`   | ADMIN            | Full system access. Can do everything.                                      |
| `Admin`         | ADMIN            | Administrative access. Treated the same as Super_Admin in most checks.     |
| `PKSF_CO`       | PKSF             | PKSF Concern Officer. Creates observations, drives the PKSF workflow.       |
| `PKSF_SUPERVISOR` | PKSF           | PKSF Supervisor. Reviews and approves/returns observations on the PKSF side.|
| `PO_CO`         | PO               | PO Concern Officer. Handles observations on the PO side.                   |
| `PO_SUPERVISOR` | PO               | PO Supervisor. Reviews and approves PO responses before submission to PKSF. |

---

## Senior Management Roles (new)

| Role Name  | Group              | Description                                                                                   |
|------------|--------------------|-----------------------------------------------------------------------------------------------|
| `SM_MD`    | SENIOR_MANAGEMENT  | Managing Director. Can view and comment on all approved observations across all POs.          |
| `SM_DMD`   | SENIOR_MANAGEMENT  | Deputy Managing Director. Can view and comment on approved observations for assigned POs only.|
| `SM_SGM`   | SENIOR_MANAGEMENT  | Senior General Manager. Can view and comment on approved observations for assigned POs only.  |

PO assignments for `SM_DMD` and `SM_SGM` are managed via the `user_po_assignments` table.

---

## Important Notes

- Role names are **case-sensitive**. `PKSF_CO` ≠ `pksf_co`.
- `Super_Admin` uses an underscore — do **not** create it as `Super Admin` (with a space). The codebase has legacy references to `Super Admin` (with space); these should be cleaned up. Always use `Super_Admin`.
- When creating roles via the admin UI, copy the role name **exactly** from the table above.
- When adding new roles to the codebase, add them to this document first.

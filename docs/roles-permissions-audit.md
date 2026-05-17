# Roles & Permissions Audit Report

## Scope

This report reviews how roles and permissions are currently enforced in the codebase, highlights conflict points, and proposes a low-risk improvement roadmap.

---

## 1) Current architecture (what exists now)

### Authorization stack

- The project uses **spatie/laravel-permission** (`HasRoles`, role and permission middleware).  
- Role and permission management screens exist (`RoleController`, `PermissionController`, `resources/views/roles/*`).

### How access is actually enforced

In practice, access is enforced in **three mixed ways**:

1. **Role middleware** on controller actions (`middleware('role:...')`).
2. **Permission middleware** (mostly for role-management screens).
3. **Inline role checks** in controllers and Blade (`hasRole(...)`, `hasAnyRole(...)`).

This mixed approach works but creates drift and conflicts over time.

---

## 2) High-confidence conflict findings

### A. Role naming inconsistencies (case-sensitive conflict risk)

Role names are used in mixed casing for the same logical role:

- `Korso_ma`
- `korso_ma`

If role names are case-sensitive in storage, one spelling may fail authorization or return empty user sets in some paths.

### B. Legacy/duplicate role source on `users` table

The `users` table has a `roles_name` column with a default (`Verwaltung`), while Spatie role assignments are stored in pivot tables.

This introduces two sources of truth:

- `users.roles_name` (legacy/static)
- `model_has_roles` + `roles` (actual authorization model)

### C. Route-level protection is inconsistent

Many routes are public in `routes/web.php` (not grouped under `auth` or authorization middleware), while some are protected inside `Route::group(['middleware' => ['auth']])`.

This makes it hard to reason about exposure and can cause accidental access drift.

### D. Privileged exceptions by hardcoded user IDs

There are hardcoded user-ID exceptions (for example user id `16` and an explicit list in Korso exception logic).

Hardcoded IDs are brittle and bypass role strategy, making audits and onboarding/offboarding harder.

### E. Direct table manipulation bypassing package APIs

Examples:

- Deleting roles via `DB::table('roles')->where('id',$id)->delete()`.
- Clearing user-role assignments via direct delete from `model_has_roles`.

This can bypass model events, cache behavior expectations, and consistency safeguards.

### F. Permission model is underused outside admin area

Permissions (`role-list`, `role-create`, etc.) are used mainly for role-management CRUD. Most business features still rely on direct role checks, limiting fine-grained control.

---

## 3) Why conflicts happen

1. **No canonical naming convention** (snake/camel/case variations).  
2. **No centralized authorization map** per feature/action.  
3. **Role checks scattered across controllers and views**.  
4. **Legacy fields and hardcoded exceptions coexist with Spatie roles**.

---

## 4) Low-risk improvement plan (no breaking changes)

## Phase 0 — Safety baseline (first)

1. **Create a role/permission inventory export** from DB (roles, permissions, role-permission map, user-role map).
2. **Snapshot existing behavior** with smoke tests for critical pages/actions by role.
3. **Enable strict logging** for authorization denials and unexpected access.

## Phase 1 — Standardization without behavior changes

1. Define and document canonical role naming (e.g., `Super_Admin`, `Korso_Admin`, `Korso_MA` etc.) and keep a temporary alias map.
2. Add constants/enums for role names and permission names; stop using raw strings directly.
3. Add a single helper/policy layer for access checks, then call it from controllers/views.

## Phase 2 — Remove conflict sources gradually

1. Replace hardcoded user ID exceptions with dedicated roles/permissions (e.g., `special-ticket-admin`).
2. Replace direct DB role operations with Spatie methods (`syncRoles`, `removeRole`, `delete()` on model with package-aware flow).
3. Start moving feature checks from role-based to permission-based where needed (least privilege).
4. Keep `roles_name` read-only for compatibility, then deprecate and remove in a later migration.

## Phase 3 — Route and policy hardening

1. Group private routes under `auth` consistently.
2. Add route-level middleware per module (`role`/`permission`) as the first gate.
3. Move business rules to Policies/Gates and keep Blade/controller checks thin.

---

## 5) Suggested target model

- **Roles** represent job functions (coarse grouping).
- **Permissions** represent actions (view/create/update/delete/assign/export...).
- Controllers/routes authorize primarily by permissions; roles aggregate permissions.
- Exceptions are role/permission-based (never user-id based).

---

## 6) Safe “first change set” recommendation

To improve professionalism without breaking behavior, first implement only:

1. **Naming normalization map** for known variants (`Korso_ma` vs `korso_ma`) while supporting both temporarily.
2. **Central constants file** for all role names currently in use.
3. **Refactor only the role-management flow** (`RoleController`, `UserController`) to use package-native APIs instead of direct DB deletes.
4. **Add an authorization matrix document** per module (ticket, korso, handwerk, inventory, settings).

This provides immediate clarity and reduces conflict risk before any aggressive permission redesign.

---

## 7) Acceptance criteria before any role/permission removals

Only remove or rename roles/permissions after:

1. Every usage is located (routes/controllers/views/jobs/notifications).
2. Replacement mapping is applied and tested.
3. At least one end-to-end test per critical role passes.
4. Admin UI still creates/edits roles and assigns users correctly.

---

## 8) Concrete next step options

- **Option A (recommended):** Start with a non-breaking refactor PR (constants + alias handling + safer role APIs).
- **Option B:** Build a full permission matrix first, then refactor by module.
- **Option C:** Keep current behavior and only add monitoring/audit logs (lowest risk, lowest improvement).


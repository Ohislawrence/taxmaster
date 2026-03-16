# Accountant UI Copy & Wireframes

## Purpose
Short UI copy and lightweight wireframes for the accountant experience: create business form and business switcher.

## Create Business — Page / Modal

- Title: "Create client business"
- Subtitle: "Add a new client business you will manage"

Fields (labels / placeholders / validation):
- Business name: "Business name" / "Acme Pty Ltd" / required
- Registration number: "Registration number" / "ABN or VAT number" / optional
- Primary contact name: "Primary contact" / "Jane Doe" / required
- Primary contact email: "Contact email" / "client@example.com" / required, valid email
- Phone: "Phone" / "+61 4 1234 5678" / optional
- Address: "Address" / "Street, City, State" / optional
- Notes: "Internal notes" / "Client needs BAS assistance" / optional

Buttons / copy:
- Primary: "Create and manage"
- Secondary: "Cancel"
- After success: toast "Business created — you're now managing this client" and redirect to business dashboard

Server behaviour (brief):
- POST to `accountant.businesses.store` (controller creates Business with `owner_id = null`, sets `created_by_accountant_id`, `billing_managed_by_platform = false`, attaches pivot to current user)

Validation errors copy examples:
- "Business name is required."
- "Contact email must be a valid email address."

Edge guidance for UI teams:
- Do not show any billing or subscription UI on the success flow.
- Show a small grey pill near the business name: "Billing managed externally" with tooltip: "This business is billed outside the platform. Contact admin to enable on-platform billing."

## Business Switcher — Header component

Behavior:
- Label: show `current_business.name` or "Select business"
- When clicked, show a searchable dropdown of businesses the user `manages` (owned + pivot). Each item shows: business name, small muted subtitle ("You manage" or "Owner: <name>").
- Action when selected: POST to `business.switch` with `business_id`. Server authorizes via `managesBusiness()` and sets session `business_id`.

Copy and microcopy:
- Placeholder: "Search businesses..."
- Empty state: "No businesses to switch. Create a business or ask an admin to assign one."

Visual affordances:
- For businesses with `billing_managed_by_platform = false` show a small warning icon in the dropdown item and text: "External billing".
- Disable or hide billing actions in the header/context menu when switching to such a business.

## Disabled Billing Notice (component)

Use a small inline component to display on billing/subscription pages when the active business has `billing_managed_by_platform === false`:

- Title: "Billing managed externally"
- Body: "This client is billed outside the platform. To enable on-platform billing, the client must claim ownership or an admin must enable billing. Contact support or your account admin for help."
- Actions: "Contact admin" (link), optional "Request claim" (opens invite flow)

## Accessibility & UX notes

- Ensure the switcher is keyboard-accessible and searchable.
- Error states: show inline validation for each field. For server-side blocking (e.g., subscription creation attempted) show an alert with the same message as the guard: "This business is not eligible for on-platform subscriptions. Contact admin."

## Implementation hints for devs

- `HandleInertiaRequests` already exposes `current_business` and `businesses`. Use `current_business.billing_managed_by_platform` to hide/disable billing UI.
- Add a small reusable `BillingGuardNotice.vue` component and include it on billing entry pages.

---
Generated: 2026-03-12

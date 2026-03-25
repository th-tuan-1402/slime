---
title: "Ke hoach Migration: Laravel Headless API + Nuxt 3 SSR"
linearDocumentId: "15863769-6fb6-4730-80cd-fe1aed6c811a"
linearUrl: "https://linear.app/bienhoa-hive/document/ke-hoach-migration-laravel-headless-api-nuxt-3-ssr-d89b5e0bf758"
---

```yaml
name: Migration Headless API Plan
overview: Migration tu Zend Framework monolith sang Laravel Headless API (backend repo) + Nuxt 3 SSR (client repo). Business layer to chuc theo module, abstract controller wrap Laravel, custom request class.
todos:
  - id: b0-setup
    content: "Phase B0: Setup Laravel project (backend repo), Docker, AbstractApiController, BaseFormRequest, CORS + CSP + security headers, Eloquent models, multi-tenant middleware, OpenAPI spec v1 (Scramble), CI pipeline (PHPStan + Pest)"
    status: pending
  - id: f0-setup
    content: "Phase F0: Setup Nuxt 3 project (client repo), Tailwind + Nuxt UI, API client gen (orval), Pinia stores, Storybook"
    status: pending
  - id: b1-core
    content: "Phase B1: Core API - Module Auth (Sanctum), Module Tenant, Module Menu, Module Schema + Field (CRUD)"
    status: pending
  - id: f1-uikit
    content: "Phase F1: UI Kit (base components) + Login page (SSR) + App layout + SideNav"
    status: pending
  - id: b2-record
    content: "Phase B2: Module Record - CRUD, Search, CSV import/export, File upload, Dynamic validation"
    status: pending
  - id: f2-record
    content: "Phase F2: Record pages - list (SSR), form (dynamic fields), view, search"
    status: pending
  - id: b3-business
    content: "Phase B3: Module User, Module Admin, Module Action, Module Approval, Module Import, Module Tabulation"
    status: pending
  - id: f3-admin
    content: "Phase F3: Admin pages - schema, field, user, role, settings"
    status: pending
  - id: b4-integration
    content: "Phase B4: Module Mail, Module CTI, Module Accounting, Module CloudSign, Batch jobs (Laravel Queue)"
    status: pending
  - id: f4-advanced
    content: "Phase F4: Advanced pages - workflow, approval, mail, report/chart, system linkage"
    status: pending
  - id: b5-polish
    content: "Phase B5: Backend polish - Performance, caching, rate limiting, API docs, security audit"
    status: pending
  - id: f5-polish
    content: "Phase F5: Client polish - SSR optimization, a11y, Playwright E2E, performance"
    status: pending
  - id: cutover
    content: "Parallel run + cutover: Nginx routing, shared DB, E2E tests, gradual tenant migration, retire Zend app"
    status: pending
isProject: false
```

Nội dung chi tiết rất dài (kèm nhiều sơ đồ mermaid + cấu trúc module/backend/client). Nếu cần bản đầy đủ 100% y nguyên từ Linear (bao gồm toàn bộ phần phía dưới), mình sẽ export tiếp theo dạng file riêng và đẩy lên GitHub.


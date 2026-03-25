---
title: "[PM] Project Status Dashboard"
linearDocumentId: "289b9675-3bfb-4684-9e74-45e31a903953"
linearUrl: "https://linear.app/bienhoa-hive/document/pm-project-status-dashboard-2cbeba99649a"
---

# Project Status Dashboard

**Updated**: 2026-03-25

## Overall Progress

* **Total issues**: 44
* **Done**: 12 (27%)
* **In Progress**: 2
* **Todo**: 3
* **Backlog**: 27

## Milestones (Plan vs Forecast)

| Milestone | Issues (Done/Total) | Planned due | Forecast due | Notes |
| -- | -- | -- | -- | -- |
| B0: Backend Setup | 8/8 | 2026-04-13 | **2026-03-17 (Done)** | Hoàn thành sớm hơn kế hoạch |
| F0: Client Setup | 3/3 | 2026-04-13 | **2026-03-24 (Done)** | Hoàn thành sớm hơn kế hoạch |
| B1: Core API | 1/16 | 2026-05-11 | **2026-05-11 (At risk)** | 54 pts (leaf) còn lại; cần \~1.15 pts/ngày đến deadline |
| F1: UI Kit + Auth | 0/3 | 2026-05-11 | 2026-05-11 (TBD) | Chưa có estimate (cần survey/estimate) |
| B2: Record API | 0/2 | 2026-06-08 | 2026-06-08 (TBD) | Chưa có estimate (cần survey/estimate) |
| F2: Record Pages | 0/2 | 2026-06-08 | 2026-06-08 (TBD) | Chưa có estimate (cần survey/estimate) |
| B3: Business API | 0/3 | 2026-07-06 | 2026-07-06 (TBD) | Nhiều scope lớn (BIE-28) có khả năng cần tách |
| F3: Admin Pages | 0/1 | 2026-07-06 | 2026-07-06 (TBD) | Issue đơn nhưng scope lớn (có thể cần tách) |
| B4: Integration API | 0/1 | 2026-08-03 | 2026-08-03 (TBD) | 4 integration trong 1 issue → gần như chắc cần tách |
| F4: Advanced Pages | 0/1 | 2026-08-03 | 2026-08-03 (TBD) | Gộp nhiều màn hình phức tạp → cần tách |
| B5: Backend Polish | 0/1 | 2026-08-31 | 2026-08-31 (TBD) | Phụ thuộc B1–B4 |
| F5: Client Polish | 0/1 | 2026-08-31 | 2026-08-31 (TBD) | Phụ thuộc F1–F4 |
| Cutover: Parallel Run + Migration | 0/2 | 2026-10-12 | 2026-10-12 (TBD) | Phụ thuộc B5 + F5 |

## Current Focus

### B1: Core API — status breakdown

* **Done**: 1 (blocker env)
* **In Progress**: 2 (`BIE-16`, `BIE-40`)
* **Todo**: 3 (`BIE-37`, `BIE-38`, `BIE-43`)
* **Backlog**: 10

**Leaf-point total**: 54 pts (0 pts done)

## Risks / Watchlist

* **B1 velocity chưa có baseline theo points** (hiện các issue Done chưa có estimate) → sau khi xong 2-3 leaf issues đầu tiên của B1, nên cập nhật forecast theo points.
* **Các issue lớn cần tách**: `BIE-28`, `BIE-31`, `BIE-34`, `BIE-30`.


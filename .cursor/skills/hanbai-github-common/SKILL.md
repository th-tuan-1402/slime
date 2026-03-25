---
name: hanbai-github-common
description: >-
  Common GitHub-first workflow conventions for Hanbai Migration subagents.
  Defines repo context, label/state conventions, and gh command snippets that
  other subagent skills reference.
---

# Hanbai GitHub Common (shared)

## Purpose

Chuẩn hoá cách các sub-agent làm việc **trực tiếp với GitHub** (không qua Linear):
- Repo context
- Quy ước labels / trạng thái
- Command mẫu với `gh`
- Quy tắc log bằng comment

## Project Context

- **Repo**: `th-tuan-1402/slime`
- **Issue key**: `BIE-XX` (phải xuất hiện trong title hoặc body để truy vết)
- **Primary tool**: GitHub CLI `gh` (đã login)

## Label conventions (single source of truth)

### Status labels (state machine)

- `status/backlog`
- `status/todo`
- `status/in_progress`
- `status/in_review`
- `status/done`

### Phase labels (ownership / lane)

- `phase:design`
- `phase:dev`
- `phase:review`
- `phase:qa`

### Meta labels

- `priority/urgent`, `priority/high`, `priority/medium`, `priority/low`, `priority/none`
- `sp/1`, `sp/2`, `sp/3`, `sp/5`, `sp/8`, `sp/13`, `sp/23`
- `linear/imported` (chỉ để trace dữ liệu migrated)

## GitHub milestones

Milestone trên GitHub tương ứng milestone kế hoạch (B0, F0, B1, ...).

## Canonical gh commands

### List issues by labels

```bash
gh issue list -R th-tuan-1402/slime -l "status/todo" -l "phase:dev" --limit 50
```

### View an issue (structured)

```bash
gh issue view -R th-tuan-1402/slime <issue-number> --json number,title,body,labels,milestone,assignees,url
```

### Transition status (always comment)

```bash
gh issue edit -R th-tuan-1402/slime <issue-number> --add-label "status/in_progress" --remove-label "status/todo"
gh issue comment -R th-tuan-1402/slime <issue-number> --body "<why/what changed>"
```

### Create a PR and link it to issue

```bash
gh pr create --base main --head <branch> --title "<title>" --body "<body>"
gh issue comment -R th-tuan-1402/slime <issue-number> --body "PR: <url>"
```

### Close issue when Done

```bash
gh issue edit -R th-tuan-1402/slime <issue-number> --add-label "status/done"
gh issue close -R th-tuan-1402/slime <issue-number> --comment "<QA evidence / completion summary>"
```

## Rules

- **Không dùng Linear MCP** (`plugin-linear-linear`) trong các workflow.
- Mọi thay đổi trạng thái phải có **comment** ghi lý do + link (PR/CI/log).
- Không tự ý đổi phase label sang `phase:qa` nếu bạn không phải Team Lead.
- Mọi PR phải **CI xanh** trước khi gắn `status/in_review`.


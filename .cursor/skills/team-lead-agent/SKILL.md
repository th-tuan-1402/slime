---
name: team-lead-agent
description: >-
  Team Lead agent that orchestrates GitHub issues/PRs for the migration project.
  Creates detail designs, assigns work via GitHub labels, reviews code, and
  manages status transitions. Use when the user asks to lead, plan, design,
  review, or orchestrate a GitHub issue or migration task.
---

# Team Lead Agent

Orchestrate development workflow on GitHub: create detail designs, assign work
to Dev and QA subagents via labels, review code, and manage issue lifecycle.

## Shared conventions

This skill follows the shared workflow and label conventions defined in:
- `.cursor/skills/hanbai-github-common/SKILL.md`

## Project Context

- **Team**: Bienhoa-hive
- **Project**: Hanbai Migration: Headless API + Nuxt 3
- **Issue prefix**: BIE-
- **GitHub repo**: `th-tuan-1402/slime`

## Workspace

Paths relative to the repository clone root:

- **Root**: `.`
- **Backend**: `backend/` (Laravel API)
- **Client**: `client/` (Nuxt 3)

Use these paths when creating detail designs or reviewing code.

## Available Statuses

| Status      | When to use                          |
|-------------|--------------------------------------|
| Backlog     | Not yet planned                      |
| Todo        | Ready for next phase (dev or qa)     |
| In Progress | Actively being worked on             |
| In Review   | Code complete, awaiting review       |
| Done        | Fully completed and verified         |

## Phase Labels

| Label          | Meaning                               |
|----------------|---------------------------------------|
| phase:design   | Team Lead creating detail design      |
| phase:dev      | Ready for Dev subagent                |
| phase:review   | Awaiting code review by Team Lead     |
| phase:qa       | Ready for QA subagent                 |

## Workflow

### Step 1: Pick up an issue

```bash
gh issue list -R th-tuan-1402/slime -l "status/backlog" --limit 50
```

Select the highest-priority issue to work on.

### Step 2: Create detail design

Move the issue to design-in-progress:

```bash
gh issue edit -R th-tuan-1402/slime <issue-number> --add-label "status/in_progress" --add-label "phase:design"
gh issue comment -R th-tuan-1402/slime <issue-number> --body "Detail design started."
```

Analyze the issue requirements. Then create a detail design document:

Preferred destination:
- Add a markdown file under `docs/design/DD-BIE-XX-<slug>.md` and open a PR, then link it from the issue, OR
- Put the DD directly into the GitHub issue body if it is small enough.

#### Detail Design Template

```markdown
# Detail Design: <issue title>

## Overview
Brief description of what this issue delivers.

## Scope
- List of files/modules to create or modify
- Out-of-scope items

## Technical Design

### Architecture
How this fits into the module structure (backend/app/Modules/ or client/).

### API Endpoints (if applicable)
| Method | Path | Description |
|--------|------|-------------|
| GET    | /api/v1/... | ... |

### Data Model (if applicable)
Table/column changes or new Eloquent models.

### Business Logic
Key algorithms, validation rules, edge cases.

## Acceptance Criteria
- [ ] Criterion 1
- [ ] Criterion 2

## Dependencies
Other issues that must be completed first.
```

### Step 3: Hand off to Dev

After design is complete, transition the issue:

```bash
gh issue edit -R th-tuan-1402/slime <issue-number> --add-label "status/todo" --remove-label "status/in_progress" --add-label "phase:dev" --remove-label "phase:design"
gh issue comment -R th-tuan-1402/slime <issue-number> --body "$(cat <<'EOF'
Detail design complete. Ready for implementation.

Key points:
- <summary point 1>
- <summary point 2>
EOF
)"
```

### Step 4: Review code (when Dev submits)

Find issues awaiting review:

```bash
gh issue list -R th-tuan-1402/slime -l "status/in_review" -l "phase:review" --limit 50
```

For each issue:

1. Read the Dev's implementation comment on the issue
2. Read the changed files in the codebase
3. Review code applying project rules when present in the workspace, e.g.:
   - `.cursor/rules/coding_conventions.mdc`
   - `.cursor/rules/coding_guideline.mdc`
   - `.cursor/rules/code_review.mdc`
4. Post review feedback as a comment

#### If review passes:

```bash
gh issue edit -R th-tuan-1402/slime <issue-number> --add-label "status/todo" --remove-label "status/in_review" --add-label "phase:qa" --remove-label "phase:review"
gh issue comment -R th-tuan-1402/slime <issue-number> --body "$(cat <<'EOF'
Code review passed. Ready for QA.

Notes:
- <any notes for QA>
EOF
)"
```

#### If review fails:

```bash
gh issue edit -R th-tuan-1402/slime <issue-number> --add-label "status/in_progress" --remove-label "status/in_review" --add-label "phase:dev" --remove-label "phase:review"
gh issue comment -R th-tuan-1402/slime <issue-number> --body "$(cat <<'EOF'
Code review: changes requested.

## Required fixes
- <fix 1>
- <fix 2>
EOF
)"
```

### Step 5: Final verification

After QA marks an issue Done, verify in the issue comments that all acceptance
criteria from the detail design have been met.

## Review Checklist

When reviewing code, check these areas:

- [ ] Follows module structure (flat module with suffix-based naming)
- [ ] Type declarations on all method params/returns
- [ ] PHPDoc on all classes and methods
- [ ] No `Service`/`Logic` suffix (use `Searcher`/`Editor`)
- [ ] Strict comparison (`===`) used
- [ ] No reference passing
- [ ] SQL uses parameter binding
- [ ] No `SELECT *`
- [ ] No debug code (`var_dump`, `die`)
- [ ] Tests follow AAA pattern
- [ ] camelCase for methods/properties, StudlyCaps for classes

## Rules

- Always create a detail design document before handing off to Dev
- Always post a comment when transitioning an issue between phases
- Never skip code review before handing off to QA
- If an issue has blockers, note them in a comment and move to Backlog

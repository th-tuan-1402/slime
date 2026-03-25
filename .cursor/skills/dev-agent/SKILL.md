---
name: dev-agent
description: >-
  Dev agent that picks up GitHub issues, implements code following project
  conventions, runs tests and static analysis, then submits a PR for Team Lead
  review. GitHub-first (no Linear). Uses shared conventions in
  `hanbai-github-common`.
---

# Dev Agent

Pick up development tasks from GitHub Issues, implement code following project
conventions, and submit a PR for Team Lead review.

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

Always use `working_directory` when running shell commands to target the correct path.

## Workflow

### Step 1: Pick up a task

Find issues ready for Dev (GitHub labels):

```bash
# Prefer issues that are:
# - label: status/todo
# - label: phase:dev
# - (optional) milestone: current milestone
gh issue list -R th-tuan-1402/slime -l "status/todo" -l "phase:dev" --limit 50
```

Pick the highest-priority issue and read its details:

```bash
gh issue view -R th-tuan-1402/slime <issue-number> --json number,title,body,labels,milestone,assignees,url
```

Read the linked detail design document (repo doc link or issue body) and any comments from Team Lead.

### Step 2: Start work

Move the issue to In Progress on GitHub:

```bash
# Add status + keep phase label, and assign yourself if desired
gh issue edit -R th-tuan-1402/slime <issue-number> --add-label "status/in_progress" --remove-label "status/todo"
gh issue comment -R th-tuan-1402/slime <issue-number> --body "Starting implementation."
```

### Step 3: Implement

Follow the detail design document. Apply these project rules:

#### Mandatory Conventions (from coding_conventions.mdc)

- camelCase for classes/methods/variables; UPPER_SNAKE for constants
- Class suffixes: `XxxSearcher` (search), `XxxEditor` (edit), `XxxController`, `XxxDao`, `XxxValidator`
- Never use `Service` or `Logic` as suffix
- Type declarations on all method params, return types, properties
- Strict comparison (`===`) only
- No reference passing (`=&`)
- SQL: always use parameter binding, never `SELECT *`
- No debug code (`var_dump`, `die`, `myDebug`)
- No dynamic properties, no reflection
- JS variables use `const` (or `let` when reassignment needed)

#### Architecture (from coding_guideline.mdc)

- **Controller**: bridge only, no business logic or DB access
- **Searcher/Editor**: business logic layer
- **Dao**: CRUD only via `AmReadDao`/`AmWriteDao`
- **Model/DTO**: immutable data objects with `readonly` + constructor promotion
- Use constructor injection for dependencies

#### Module Structure (migration target)

```
backend/app/Modules/<ModuleName>/
  <ModuleName>Controller.php
  <ModuleName>Searcher.php
  <ModuleName>Editor.php
  <ModuleName>Repository.php
  <ModuleName>Model.php
  Requests/
    Store<ModuleName>Request.php
    Update<ModuleName>Request.php
```

#### Test Standards

- PHPUnit with AAA pattern (Arrange / Act / Assert)
- Test class: `<ClassName>Test`
- Test method: `test<Method>_<Scenario>_<Expected>`
- Use `createStub()` / `createMock()` for test doubles
- Use `@dataProvider` for multiple input patterns
- Factory helpers with sensible defaults

### Step 4: Verify locally

Run tests and static analysis before submitting:

```bash
# Run unit tests
make test-no-coverage

# Run specific test file
make test-file-no-coverage FILE=tests/unit/<path>/<TestFile>.php

# Run static analysis
make phpstan

# Run static analysis on specific file
make phpstan-file FILE=application/<path>/<File>.php
```

Fix any failures before proceeding.

### Step 4.5: Ensure CI is green (fix CI errors before review)

Before moving the issue to review, make sure your PR checks are passing.

- If CI is failing, **treat it as higher priority than review**:
  - Pull latest `main` (or the target base branch) and rebase/merge as appropriate
  - Fix the root cause
  - Re-run local verification (repeat Step 4)
  - Commit + push fixes
  - Confirm the PR checks are green

### Step 5: Commit, push, and create PR

After verification passes, commit, push, and open a Pull Request for review.
Follow the **commit-code** skill (`.cursor/skills/commit-code/SKILL.md`).

1. Create a feature branch (if not already on one):

```bash
git checkout -b feature/BIE-XX-short-description
```

2. Stage relevant files:

```bash
git add <files>
```

3. Write commit message to `.git/COMMIT_MSG` (PowerShell-safe):

Use the Write tool to create `.git/COMMIT_MSG` with Conventional Commits
format in Japanese, then:

```bash
git commit -F .git/COMMIT_MSG
```

4. Push the branch:

```bash
git push -u origin feature/BIE-XX-short-description
```

5. Create Pull Request for code review:

```bash
gh pr create --base main --head feature/BIE-XX-short-description --title "BIE-XX: <short description>" --body "## BIE-XX: <issue title>

Implements GitHub issue: https://github.com/th-tuan-1402/slime/issues/<issue-number>

### Changes
- <file1>: <what changed>
- <file2>: <what changed>

### Tests
- <test results summary>

### Notes
- <anything reviewer should know>"
```

If GitHub CLI (`gh`) is not available, use the repository's web UI to create a PR manually, targeting `main` from the feature branch.

### Step 6: Submit for review

Move the GitHub issue to review state and post summary:

```bash
gh issue edit -R th-tuan-1402/slime <issue-number> --add-label "status/in_review" --remove-label "status/in_progress" --add-label "phase:review"
gh issue comment -R th-tuan-1402/slime <issue-number> --body "$(cat <<'EOF'
Implementation complete. Ready for review.

## PR
- <PR URL>

## Changes
- <file1>: <what changed>
- <file2>: <what changed>

## Branch
- `feature/BIE-XX-short-description`

## Tests
- <test results summary>

## Notes
- <anything reviewer should know>
EOF
)"
```

### Step 7: Handle review feedback

If Team Lead requests changes (issue moved back to In Progress with `phase:dev`):

1. Read the review comment for required fixes
2. Implement the fixes
3. Re-run tests and PHPStan
4. Commit and push fixes (repeat Step 5)
5. Submit for review again (repeat Step 6)

## Rules

- Always read the detail design document before starting implementation
- Always run tests and PHPStan before submitting for review
- Always fix CI errors (PR checks must be green) before moving the issue to In Review
- Always commit, push, and create a PR before moving the issue to In Review
- Always post an implementation summary comment (including PR URL and branch name) when submitting
- Commit messages must follow Conventional Commits format in Japanese (see `commit-code` skill)
- Never change issue labels to `phase:qa` directly (only Team Lead does that)
- If requirements are unclear, post a question as a GitHub issue comment and wait

---
name: commit-code
description: >-
  Commit and push code changes to git with Conventional Commits format in
  Japanese. Use when the user asks to commit, push, save changes to git,
  or after completing an implementation task.
---

# Commit Code

Stage, commit, and push code changes following Conventional Commits in Japanese.

## Repository

- **Remote**: `origin` → `https://github.com/th-tuan-1402/slime.git`
- **Default branch**: `main`
- **Workspace root**: repository clone root (directory containing `.git`)

## Commit Message Format

Follows Conventional Commits in Japanese:

```
<type>: <description in Japanese>

- <detail 1>
- <detail 2>
```

### Types

| Type | Usage |
|------|-------|
| `feat` | New feature |
| `fix` | Bug fix |
| `docs` | Documentation only |
| `style` | Formatting, no logic change |
| `refactor` | Code change that is not feat/fix |
| `perf` | Performance improvement |
| `test` | Adding or fixing tests |
| `chore` | Build, tooling, dependencies |

### Rules

- First line: `<type>: <short description in Japanese>`
- Blank line after first line
- Body: bullet list of changes in Japanese
- Each line max ~72 characters
- Only reference staged changes

### Examples

**Good:**
```
feat: ユーザー認証エンドポイントの実装

- JWTトークンの発行・検証ロジックを追加
- ログインコントローラーとリクエストバリデーションを作成
```

**Good:**
```
fix: テナント切替時のDB接続リーク修正

- DB::purge()をreconnect前に確実に呼ぶよう修正
- テスト追加
```

## Workflow

### Step 1: Check status

```bash
git status
git diff --stat
```

Review what changed. Exclude secrets (`.env`, credentials).

### Step 2: Stage files

```bash
git add <specific files or paths>
```

Stage only relevant files. Use `git add -A` when all changes belong to one logical commit.

### Step 3: Commit

Write the commit message to a temp file (PowerShell does not support heredoc):

```bash
# Write message to temp file
# Then commit:
git commit -F .git/COMMIT_MSG
```

Since the shell is PowerShell on Windows, use the Write tool to create
`.git/COMMIT_MSG` with the message content, then run `git commit -F .git/COMMIT_MSG`.

### Step 4: Push

```bash
git push origin <branch>
```

If the branch has no upstream yet:

```bash
git push -u origin <branch>
```

### Step 5: Verify

```bash
git status
git log --oneline -3
```

Confirm clean working tree and correct commit appears.

## Branch Strategy

- `main` — stable, production-ready
- Feature branches: `feature/<issue-id>-<short-desc>` (e.g. `feature/BIE-15-auth-api`)
- Fix branches: `fix/<issue-id>-<short-desc>`

When working on a specific GitHub issue (e.g. BIE-XX), create a feature branch from `main`:

```bash
git checkout main
git pull origin main
git checkout -b feature/BIE-XX-short-description
```

After push, the Team Lead or dev can create a PR via `gh pr create`.

## PowerShell Compatibility

This workspace uses PowerShell. Important notes:

- `&&` does not work — use `;` to chain commands
- Heredoc (`<<'EOF'`) does not work — write commit messages to a file first
- Use `Write` tool to create `.git/COMMIT_MSG`, then `git commit -F .git/COMMIT_MSG`

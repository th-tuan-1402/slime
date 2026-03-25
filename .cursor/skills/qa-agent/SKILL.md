---
name: qa-agent
description: >-
  QA agent that picks up GitHub issues ready for testing, writes test cases
  based on detail design, executes tests, and reports results. Use when the
  user asks to test, verify, write test cases, or QA a GitHub issue, or when
  working on phase:qa tasks.
---

# QA Agent

Pick up QA tasks from GitHub Issues, write test cases from the detail design,
execute tests, and report results.

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

Find issues ready for QA (GitHub labels):

```bash
gh issue list -R th-tuan-1402/slime -l "status/todo" -l "phase:qa" --limit 50
```

Pick the highest-priority issue and read its details:

```bash
gh issue view -R th-tuan-1402/slime <issue-number> --json number,title,body,labels,milestone,assignees,url
```

Read the linked detail design document and all comments (especially the
implementation summary from Dev and review notes from Team Lead).

### Step 2: Start work

Move the issue to In Progress:

```bash
gh issue edit -R th-tuan-1402/slime <issue-number> --add-label "status/in_progress" --remove-label "status/todo"
gh issue comment -R th-tuan-1402/slime <issue-number> --body "QA started."
```

### Step 3: Write test cases

Based on the detail design's acceptance criteria, write a test case document.

Preferred destination:
- Add a markdown file in the repo under `docs/qa/TC-BIE-XX-<slug>.md` and open a PR, OR
- Post test cases as a GitHub issue comment (when PR for docs is out-of-scope).

#### Test Case Template

```markdown
# Test Cases: <issue title>

## Ref
- Issue: BIE-XX
- Detail Design: [DD] BIE-XX

## Test Environment
- Docker (make test / make test-file)
- PHPUnit + PHPStan

## Test Cases

### TC-01: <descriptive name>
- **Precondition**: <setup required>
- **Input**: <test data>
- **Steps**:
  1. <step>
  2. <step>
- **Expected**: <expected result>
- **Result**: PENDING

### TC-02: <descriptive name>
...

## Edge Cases

### TC-E01: <edge case name>
- **Precondition**: <setup>
- **Input**: <boundary/invalid data>
- **Steps**: ...
- **Expected**: <expected error/behavior>
- **Result**: PENDING

## Coverage Checklist
- [ ] All acceptance criteria from detail design
- [ ] Happy path for each endpoint/function
- [ ] Validation error cases
- [ ] Authorization/permission cases
- [ ] Edge cases (null, empty, boundary values)
```

Post a comment linking the test cases:

```bash
gh issue comment -R th-tuan-1402/slime <issue-number> --body "Test cases created. Total: X test cases (Y normal + Z edge cases)."
```

### Step 4: Execute tests

Implement the test cases as PHPUnit tests following project conventions:

- Test class: `<ClassName>Test` in `tests/unit/` mirroring the source path
- Test method: `test<Method>_<Scenario>_<Expected>`
- AAA pattern (Arrange / Act / Assert)
- Use `createStub()` / `createMock()` for test doubles
- Use `@dataProvider` for multiple input patterns
- Factory helpers with sensible defaults

Run the tests:

```bash
# Run specific test file
make test-file-no-coverage FILE=tests/unit/<path>/<TestFile>.php

# Run all tests
make test-no-coverage

# Run static analysis
make phpstan
```

### Step 5: Report results

#### If all tests pass:

Update the test case document with results (change PENDING to PASS).

Mark the issue as Done (close) and post results:

```bash
gh issue edit -R th-tuan-1402/slime <issue-number> --add-label "status/done" --remove-label "status/in_progress"
gh issue close -R th-tuan-1402/slime <issue-number> --comment "$(cat <<'EOF'
QA complete. All tests passed.

## Results
- Total: X test cases
- Passed: X
- Failed: 0

## Evidence
- <commands run / logs / CI link>

## Test files
- <paths>
EOF
)"
```

#### If tests fail:

Send the issue back to Dev by relabeling + commenting (do not close):

```bash
gh issue edit -R th-tuan-1402/slime <issue-number> --add-label "status/todo" --remove-label "status/in_progress" --add-label "phase:dev" --remove-label "phase:qa"
gh issue comment -R th-tuan-1402/slime <issue-number> --body "$(cat <<'EOF'
QA found issues. Returning to Dev.

## Failed tests
### TC-XX: <test name>
- Expected: <what should happen>
- Actual: <what happened>
- Error:
```
<error output>
```

## Steps to reproduce
1. <step>
2. <step>
EOF
)"
```

Update the test case document (change PENDING to FAIL with details).

## Rules

- Always read the detail design before writing test cases
- Always create or link a test case document (repo `docs/qa/` or issue comment) before executing
- Always write test code as PHPUnit tests (not just manual verification)
- Always update the test case document with actual results
- Never mark Done without all test cases passing
- If implementation has not started yet, write test cases first and wait
- If a bug is found, provide clear reproduction steps in the comment

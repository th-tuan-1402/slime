# Agents and Skills Guide

This document explains how we use Cursor subagents and skills in this repository.

## Why this exists

- We manage migration work directly on GitHub issues and pull requests.
- We use subagents for multi-step role-based work.
- We use skills for repeatable single-purpose guidance.

## Definitions in this repository

| Kind | Location |
|------|----------|
| Subagents | `.cursor/agents/*.md` |
| Skills | `.cursor/skills/<skill-name>/SKILL.md` |

After cloning, Cursor can load agents from `.cursor/agents/`. Copy or symlink skills into your
user skills directory if you want them globally; otherwise reference paths under `.cursor/skills/`.

### Subagents (`.cursor/agents/`)

- `hanbai-github-common`: shared GitHub / label / `gh` conventions (readonly policy)
- `team-lead`: orchestrates issue flow and reviews PRs
- `dev`: implements issues and prepares PRs
- `qa`: validates implementation with evidence
- `pm`: reports progress, milestones, and risks (readonly by default)

### Skills (`.cursor/skills/`)

- `hanbai-github-common`: expanded command snippets and rules (Vietnamese/English)
- `team-lead-agent`, `dev-agent`, `qa-agent`, `pm-agent`: detailed workflows for each role
- `commit-code`: Conventional Commits in Japanese for this repo
## Subagents in this project

Subagents are defined in `.cursor/agents/`:

- `team-lead`: orchestrates issue flow and reviews PRs
- `dev`: implements issues and prepares PRs
- `qa`: validates implementation with evidence
- `pm`: reports progress, milestones, and risks
- `hanbai-github-common`: shared conventions used by all agents

## Skills in this project

Skills are reusable instructions in `.cursor/skills/` and user-level skills.
Use a skill when the task is focused and repeatable. Use a subagent when the
task is long, role-driven, or needs isolated context.

## Invocation examples

Use slash invocation:

```text
/team-lead Pick highest backlog issue and prepare design handoff.
/dev Implement issue #51 and open PR.
/qa Verify PR for issue #51 and report pass/fail with repro.
/pm Summarize milestone progress and blockers.
```

Natural language invocation also works:

```text
Use the dev subagent to implement issue #51 and submit a PR.
```

## GitHub workflow conventions

We use labels as workflow state:

- Status: `status/backlog`, `status/todo`, `status/in_progress`, `status/in_review`, `status/done`
- Phase: `phase:design`, `phase:dev`, `phase:review`, `phase:qa`

Shared rules:

1. Use GitHub CLI (`gh`) for issue/PR operations.
2. Every status transition requires an issue comment.
3. Do not use Linear MCP in this repository workflow.
4. Ensure CI is green before setting `status/in_review`.

## End-to-end handoff flow

1. Team Lead picks backlog issue and writes detail design.
2. Team Lead moves issue to `status/todo` + `phase:dev`.
3. Dev moves to `status/in_progress`, implements, opens PR.
4. Dev moves issue to `status/in_review` + `phase:review`.
5. Team Lead reviews:
   - pass -> `status/todo` + `phase:qa`
   - fail -> `status/in_progress` + `phase:dev`
6. QA validates:
   - pass -> `status/done` and close issue
   - fail -> return to `phase:dev` with reproduction details
7. PM reports progress by milestone and risk status.


# Nuxt Minimal Starter

Look at the [Nuxt documentation](https://nuxt.com/docs/getting-started/introduction) to learn more.

## API client (Orval)

- **Generate:** `npm run api:gen` — reads `OPENAPI_URL` when set; otherwise uses `openapi/openapi.snapshot.json`.
- **Output:** `src/api/generated/` (`endpoints.ts`, `model/`, `index.ts`). Do not edit generated files by hand.
- **HTTP wrapper:** `src/api/client.ts` (Orval mutator) + `src/plugins/api.ts` (Nuxt plugin injects `Authorization` and `X-Tenant-ID` from cookies/state).
- **Regenerate snapshot:** Point `OPENAPI_URL` at the live OpenAPI document (e.g. Laravel Scramble), run `npm run api:gen`, then commit updated `openapi/openapi.snapshot.json` and `src/api/generated/` if the contract changed.
- **Drift check (CI):** install deps, run `npm run api:gen:check` — fails if generated output or snapshot differs from git.
- **Config:** `orval.config.mjs` uses `tsconfig.orval.json` so generation does not require a prior `nuxt prepare`.

## Setup

Make sure to install dependencies:

```bash
# npm
npm install

# pnpm
pnpm install

# yarn
yarn install

# bun
bun install
```

## Development Server

Start the development server on `http://localhost:3000`:

```bash
# npm
npm run dev

# pnpm
pnpm dev

# yarn
yarn dev

# bun
bun run dev
```

## Production

Build the application for production:

```bash
# npm
npm run build

# pnpm
pnpm build

# yarn
yarn build

# bun
bun run build
```

Locally preview production build:

```bash
# npm
npm run preview

# pnpm
pnpm preview

# yarn
yarn preview

# bun
bun run preview
```

Check out the [deployment documentation](https://nuxt.com/docs/getting-started/deployment) for more information.

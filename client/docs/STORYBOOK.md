# Storybook và kiểm tra client (BIE-15)

## Yêu cầu

- Node.js LTS (khuyến nghị 20+)
- Đã chạy `npm ci` hoặc `npm install` trong thư mục `client/`

## Script npm (`client/package.json`)

| Lệnh | Mô tả |
|------|--------|
| `npm run storybook` | Dev server Storybook (mặc định port 6006). |
| `npm run build-storybook` | Build static vào `client/storybook-static/`. |
| `npm run lint` | ESLint (Flat config). |
| `npm run lint:fix` | ESLint với `--fix`. |
| `npm run typecheck` | `nuxt typecheck`. |

## Tự kiểm tra trước khi mở PR

Trong `client/`:

1. `npm run lint`
2. `npm run typecheck`
3. `npm run build-storybook`

## CI

Workflow `.github/workflows/ci.yml` chạy job `client` trên Ubuntu: cài dependencies, lint, typecheck, build Storybook.

## Ghi chú SSR / Pinia

- Store `auth`: `hydrateFromStorage()` chỉ đọc `localStorage` khi `import.meta.client`; plugin `auth-hydrate.client.ts` chạy phía client.
- Store `ui`: getter `isDarkMode` với theme `system` chỉ đọc `matchMedia` ở trình duyệt; trên server trả về `false`.

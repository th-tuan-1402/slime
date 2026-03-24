import { defineConfig } from 'orval';

const inputTarget =
  process.env.OPENAPI_URL !== undefined && process.env.OPENAPI_URL !== ''
    ? process.env.OPENAPI_URL
    : './openapi/openapi.snapshot.json';

export default defineConfig({
  slime: {
    input: inputTarget,
    output: {
      mode: 'single',
      target: './src/api/generated/endpoints.ts',
      schemas: './src/api/generated/model',
      client: 'fetch',
      clean: true,
      prettier: false,
      override: {
        mutator: {
          path: './src/api/client.ts',
          name: 'apiRequest',
        },
      },
    },
  },
});

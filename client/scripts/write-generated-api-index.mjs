import { writeFileSync } from 'node:fs';
import { dirname, join } from 'node:path';
import { fileURLToPath } from 'node:url';

const dir = dirname(fileURLToPath(import.meta.url));
const target = join(dir, '..', 'src', 'api', 'generated', 'index.ts');

writeFileSync(
  target,
  `/** Barrel for generated client — recreated by \`npm run api:gen\`. */\nexport * from './endpoints';\nexport * from './model';\n`,
);

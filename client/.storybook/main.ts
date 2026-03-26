import type { StorybookConfig } from '@storybook-vue/nuxt'
import { fileURLToPath } from 'node:url'

const storybookRouterComposablePath = fileURLToPath(
  new URL('../node_modules/@storybook-vue/nuxt/dist/runtime/composables/router.js', import.meta.url)
).replace(/\\/g, '/')

const toCompactPath = (value: string): string =>
  Array.from(value)
    .filter((char) => char.charCodeAt(0) >= 32)
    .join('')
    .replace(/\s+/g, '')
    .replace(/[\\/]/g, '')
    .toLowerCase()

const config: StorybookConfig = {
  stories: ['../src/**/*.mdx', '../src/**/*.stories.@(js|jsx|ts|tsx|mdx)'],
  addons: ['@storybook/addon-a11y', '@storybook/addon-docs'],
  framework: '@storybook-vue/nuxt',
  viteFinal: async (viteConfig) => {
    viteConfig.plugins = viteConfig.plugins ?? []
    viteConfig.plugins.push({
      name: 'fix-storybook-nuxt-router-import-on-windows',
      enforce: 'pre',
      resolveId(source) {
        const compactSource = toCompactPath(source)
        const isBrokenStorybookRouterImport =
          compactSource.startsWith('c:') &&
          compactSource.includes('@storybook-vue') &&
          compactSource.includes('composables') &&
          compactSource.endsWith('outer')

        if (!isBrokenStorybookRouterImport) {
          return null
        }

        return storybookRouterComposablePath
      },
    })

    return viteConfig
  },
}

export default config

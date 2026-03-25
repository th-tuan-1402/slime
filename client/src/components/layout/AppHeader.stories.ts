import type { Meta, StoryObj } from '@storybook/vue3'
import AppHeaderStoryHost from './AppHeaderStoryHost.vue'

const meta = {
  title: 'Layout/AppHeader',
  component: AppHeaderStoryHost,
  tags: ['autodocs'],
  argTypes: {
    initialTheme: {
      control: 'select',
      options: ['light', 'dark', 'system'],
    },
    initialSidebarCollapsed: { control: 'boolean' },
    initialToasts: { control: { type: 'number', min: 0, max: 99, step: 1 } },
  },
  args: {
    initialTheme: 'system',
    initialSidebarCollapsed: false,
    initialToasts: 0,
  },
} satisfies Meta<typeof AppHeaderStoryHost>

export default meta

type Story = StoryObj<typeof meta>

export const Default: Story = {}

export const SidebarCollapsed: Story = {
  args: { initialSidebarCollapsed: true },
}

export const DarkTheme: Story = {
  args: { initialTheme: 'dark' },
  parameters: {
    backgrounds: { default: 'dark' },
  },
}

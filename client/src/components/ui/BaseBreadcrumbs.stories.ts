import type { Meta, StoryObj } from '@storybook/vue3'
import BaseBreadcrumbs from './BaseBreadcrumbs.vue'

const meta = {
  title: 'UI/BaseBreadcrumbs',
  component: BaseBreadcrumbs,
  tags: ['autodocs'],
} satisfies Meta<typeof BaseBreadcrumbs>

export default meta
type Story = StoryObj<typeof meta>

export const Default: Story = {
  args: {
    items: [
      { label: 'Home', to: '/' },
      { label: 'Users', to: '/users' },
      { label: 'Detail' },
    ],
  },
}

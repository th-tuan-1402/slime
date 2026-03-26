import type { Meta, StoryObj } from '@storybook/vue3'
import BaseToastDemo from './BaseToastDemo.vue'

const meta = {
  title: 'UI/BaseToastDemo',
  component: BaseToastDemo,
  tags: ['autodocs'],
} satisfies Meta<typeof BaseToastDemo>

export default meta
type Story = StoryObj<typeof meta>

export const Default: Story = {}

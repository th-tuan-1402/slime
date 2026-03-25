import type { Meta, StoryObj } from '@storybook/vue3'
import BaseButton from './BaseButton.vue'

const meta = {
  title: 'UI/BaseButton',
  component: BaseButton,
  tags: ['autodocs'],
  argTypes: {
    label: { control: 'text' },
    color: {
      control: 'select',
      options: ['primary', 'gray', 'red', 'green'],
    },
    variant: {
      control: 'select',
      options: ['solid', 'outline', 'soft', 'ghost'],
    },
    loading: { control: 'boolean' },
    disabled: { control: 'boolean' },
  },
  args: {
    label: 'Submit',
    color: 'primary',
    variant: 'solid',
    loading: false,
    disabled: false,
  },
} satisfies Meta<typeof BaseButton>

export default meta

type Story = StoryObj<typeof meta>

export const Primary: Story = {}

export const Secondary: Story = {
  args: { color: 'gray', variant: 'outline', label: 'Secondary' },
}

export const Disabled: Story = {
  args: { disabled: true, label: 'Disabled' },
}

export const Loading: Story = {
  args: { loading: true, label: 'Please wait' },
}

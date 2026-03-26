import { ref } from 'vue'
import type { Meta, StoryObj } from '@storybook/vue3'
import BaseDatePicker from './BaseDatePicker.vue'

const meta = {
  title: 'UI/BaseDatePicker',
  component: BaseDatePicker,
  tags: ['autodocs'],
} satisfies Meta<typeof BaseDatePicker>

export default meta
type Story = StoryObj<typeof meta>

export const Date: Story = {
  render: () => ({
    components: { BaseDatePicker },
    setup() {
      const value = ref('2026-03-26')
      return { value }
    },
    template: `<BaseDatePicker v-model="value" label="Due date" mode="date" />`,
  }),
}

export const Month: Story = {
  args: { label: 'Month', modelValue: '2026-03', mode: 'month' },
}

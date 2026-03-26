import { ref } from 'vue'
import type { Meta, StoryObj } from '@storybook/vue3'
import BaseTabs from './BaseTabs.vue'

const meta = {
  title: 'UI/BaseTabs',
  component: BaseTabs,
  tags: ['autodocs'],
} satisfies Meta<typeof BaseTabs>

export default meta
type Story = StoryObj<typeof meta>

export const Default: Story = {
  args: {
    items: [],
    modelValue: '',
  },
  render: () => ({
    components: { BaseTabs },
    setup() {
      const current = ref('overview')
      return { current }
    },
    template: `<BaseTabs
      v-model="current"
      :items="[
        { key: 'overview', label: 'Overview' },
        { key: 'members', label: 'Members' },
        { key: 'logs', label: 'Logs' },
      ]"
    />`,
  }),
}

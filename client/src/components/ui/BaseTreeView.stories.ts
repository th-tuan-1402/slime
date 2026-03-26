import { ref } from 'vue'
import type { Meta, StoryObj } from '@storybook/vue3'
import BaseTreeView from './BaseTreeView.vue'

const meta = {
  title: 'UI/BaseTreeView',
  component: BaseTreeView,
  tags: ['autodocs'],
  parameters: {
    docs: {
      description: {
        component:
          'Event contract: `update:modelValue` emits selected node id, `select` emits full node payload.',
      },
    },
  },
} satisfies Meta<typeof BaseTreeView>

export default meta
type Story = StoryObj<typeof meta>

export const Default: Story = {
  args: {
    nodes: [],
  },
  render: () => ({
    components: { BaseTreeView },
    setup() {
      const selected = ref<string | number | null>(null)
      return { selected }
    },
    template: `<BaseTreeView
      v-model="selected"
      :nodes="[
        { id: 'root-1', label: 'Root A', children: [{ id: 'a-1', label: 'Leaf A-1' }] },
        { id: 'root-2', label: 'Root B', children: [{ id: 'b-1', label: 'Leaf B-1' }, { id: 'b-2', label: 'Leaf B-2' }] },
      ]"
    />`,
  }),
}

import { ref } from 'vue'
import type { Meta, StoryObj } from '@storybook/vue3'
import DynamicForm from './DynamicForm.vue'

const meta = {
  title: 'UI/DynamicForm',
  component: DynamicForm,
  tags: ['autodocs'],
  parameters: {
    docs: {
      description: {
        component:
          'Schema format: `{ key, label, type, required?, options? }`. Validation maps required fields to inline error messages.',
      },
    },
  },
} satisfies Meta<typeof DynamicForm>

export default meta
type Story = StoryObj<typeof meta>

export const Default: Story = {
  args: {
    schema: [],
    modelValue: {},
  },
  render: () => ({
    components: { DynamicForm },
    setup() {
      const value = ref<Record<string, unknown>>({
        name: '',
        role: 'viewer',
        active: true,
      })
      return { value }
    },
    template: `<DynamicForm
      v-model="value"
      :schema="[
        { key: 'name', label: 'Name', type: 'text', required: true, placeholder: 'Enter name' },
        { key: 'bio', label: 'Bio', type: 'textarea' },
        { key: 'role', label: 'Role', type: 'select', options: [{ label: 'Admin', value: 'admin' }, { label: 'Viewer', value: 'viewer' }] },
        { key: 'active', label: 'Active', type: 'toggle' },
        { key: 'joinedAt', label: 'Joined at', type: 'date' },
      ]"
    />`,
  }),
}

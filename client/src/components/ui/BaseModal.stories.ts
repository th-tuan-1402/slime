import { ref } from 'vue'
import type { Meta, StoryObj } from '@storybook/vue3'
import BaseModal from './BaseModal.vue'

const meta = {
  title: 'UI/BaseModal',
  component: BaseModal,
  tags: ['autodocs'],
} satisfies Meta<typeof BaseModal>

export default meta
type Story = StoryObj<typeof meta>

export const Default: Story = {
  args: {
    modelValue: false,
  },
  render: () => ({
    components: { BaseModal },
    setup() {
      const open = ref(false)
      return { open }
    },
    template: `
      <div>
        <UButton @click="open = true">Open modal</UButton>
        <BaseModal v-model="open" title="Confirm action" description="This cannot be undone.">
          <p class="text-sm">Modal body content</p>
        </BaseModal>
      </div>
    `,
  }),
}

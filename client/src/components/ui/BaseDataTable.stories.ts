import { ref } from 'vue'
import type { Meta, StoryObj } from '@storybook/vue3'
import BaseDataTable from './BaseDataTable.vue'

const meta = {
  title: 'UI/BaseDataTable',
  component: BaseDataTable,
  tags: ['autodocs'],
} satisfies Meta<typeof BaseDataTable>

export default meta
type Story = StoryObj<typeof meta>

export const Default: Story = {
  args: {
    columns: [],
    rows: [],
  },
  render: () => ({
    components: { BaseDataTable },
    setup() {
      const page = ref(1)
      const rows = ref([
        { id: 1, name: 'Alice', role: 'Admin' },
        { id: 2, name: 'Bob', role: 'Editor' },
        { id: 3, name: 'Charlie', role: 'Viewer' },
      ])

      return { page, rows }
    },
    template: `<BaseDataTable
      :columns="[
        { key: 'id', label: 'ID', sortable: true },
        { key: 'name', label: 'Name', sortable: true },
        { key: 'role', label: 'Role' },
      ]"
      :rows="rows"
      :current-page="page"
      :per-page="2"
      :total="6"
      @page-change="page = $event"
    />`,
  }),
}

export const Empty: Story = {
  args: {
    columns: [{ key: 'name', label: 'Name' }],
    rows: [],
  },
}

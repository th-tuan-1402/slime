export interface MenuItem {
  id: string
  label: string
  to?: string
  icon?: string
  children?: MenuItem[]
  disabled?: boolean
}

interface MenuState {
  tenantId: string | null
  items: MenuItem[]
  isLoading: boolean
  errorMessage: string | null
}

function buildTenantMenu(tenantId: string): MenuItem[] {
  if (tenantId === 'tenant-b') {
    return [
      { id: 'dashboard', label: 'Dashboard', to: '/' },
      {
        id: 'orders',
        label: 'Orders',
        children: [
          { id: 'orders-list', label: 'Order List', to: '/orders' },
          { id: 'orders-report', label: 'Report', to: '/reports' },
        ],
      },
    ]
  }

  return [
    { id: 'dashboard', label: 'Dashboard', to: '/' },
    {
      id: 'catalog',
      label: 'Catalog',
      children: [
        { id: 'catalog-products', label: 'Products', to: '/products' },
        { id: 'catalog-categories', label: 'Categories', to: '/categories' },
      ],
    },
    { id: 'settings', label: 'Settings', to: '/settings' },
  ]
}

export const useMenuStore = defineStore('menu', {
  state: (): MenuState => ({
    tenantId: null,
    items: [],
    isLoading: false,
    errorMessage: null,
  }),
  getters: {
    hasError: (state): boolean => state.errorMessage !== null,
  },
  actions: {
    reset(): void {
      this.items = []
      this.errorMessage = null
      this.isLoading = false
    },
    async loadForTenant(tenantId: string): Promise<void> {
      this.tenantId = tenantId
      this.isLoading = true
      this.errorMessage = null

      try {
        await new Promise((resolve) => setTimeout(resolve, 100))
        if (tenantId === 'tenant-error') {
          throw new Error('Menu API failed')
        }
        this.items = buildTenantMenu(tenantId)
      } catch {
        this.items = []
        this.errorMessage = 'Khong the tai menu. Vui long thu lai.'
      } finally {
        this.isLoading = false
      }
    },
  },
})

export type UiTheme = 'light' | 'dark' | 'system'

export const useUiStore = defineStore('ui', {
  state: () => ({
    isGlobalLoading: false,
    sidebarCollapsed: false,
    activeTheme: 'system' as UiTheme,
    pendingToasts: 0,
  }),

  getters: {
    isDarkMode(): boolean {
      if (this.activeTheme === 'dark') {
        return true
      }
      if (this.activeTheme === 'light') {
        return false
      }
      if (!import.meta.client || typeof window === 'undefined' || !window.matchMedia) {
        return false
      }
      return window.matchMedia('(prefers-color-scheme: dark)').matches
    },
  },

  actions: {
    setGlobalLoading(flag: boolean): void {
      this.isGlobalLoading = flag
    },

    toggleSidebar(): void {
      this.sidebarCollapsed = !this.sidebarCollapsed
    },

    setTheme(theme: UiTheme): void {
      this.activeTheme = theme
    },

    setPendingToasts(count: number): void {
      this.pendingToasts = Math.max(0, count)
    },
  },
})

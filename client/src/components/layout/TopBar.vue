<script setup lang="ts">
import type { TenantOption } from '~/composables/useTenant'

defineProps<{
  userName: string
  currentTenantId: string
  tenantOptions: TenantOption[]
  isSwitchingTenant: boolean
  tenantError: string | null
}>()

const emit = defineEmits<{
  changeTenant: [tenantId: string]
}>()

function onTenantChange(event: Event): void {
  const value = (event.target as HTMLSelectElement).value
  emit('changeTenant', value)
}
</script>

<template>
  <header
    class="flex min-h-16 items-center gap-3 border-b border-gray-200 bg-white px-4 dark:border-gray-800 dark:bg-gray-950"
  >
    <div class="flex flex-col">
      <span class="text-xs uppercase tracking-wide text-gray-500">Current user</span>
      <span class="text-sm font-semibold text-gray-900 dark:text-gray-100">{{ userName }}</span>
    </div>

    <div class="ml-auto flex items-center gap-2">
      <label class="text-xs text-gray-600 dark:text-gray-300" for="tenant-select">Tenant</label>
      <select
        id="tenant-select"
        class="rounded border border-gray-300 bg-white px-2 py-1 text-sm dark:border-gray-700 dark:bg-gray-900"
        :disabled="isSwitchingTenant"
        :value="currentTenantId"
        @change="onTenantChange"
      >
        <option v-for="option in tenantOptions" :key="option.id" :value="option.id">
          {{ option.name }}
        </option>
      </select>
      <UBadge v-if="isSwitchingTenant" color="primary" variant="soft">Switching...</UBadge>
    </div>

    <p v-if="tenantError" class="text-xs text-amber-600 dark:text-amber-400">
      {{ tenantError }}
    </p>
  </header>
</template>

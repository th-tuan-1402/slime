<script setup lang="ts">
import { computed, ref, watch } from 'vue'

type Props = {
  q?: string
  filtersJson?: string
}

const props = defineProps<Props>()
const emit = defineEmits<{
  submit: [payload: { q?: string; filtersJson?: string }]
  clear: []
}>()

const localQ = ref(props.q ?? '')
const localFiltersJson = ref(props.filtersJson ?? '')

watch(
  () => [props.q, props.filtersJson],
  ([nextQ, nextFilters]) => {
    localQ.value = nextQ ?? ''
    localFiltersJson.value = nextFilters ?? ''
  },
)

const hasFiltersError = computed(() => {
  const raw = localFiltersJson.value.trim()
  if (raw === '') return false
  try {
    const parsed = JSON.parse(raw) as unknown
    return !(parsed && typeof parsed === 'object' && !Array.isArray(parsed))
  } catch {
    return true
  }
})

function onSubmit() {
  if (hasFiltersError.value) return
  emit('submit', {
    q: localQ.value.trim() !== '' ? localQ.value : undefined,
    filtersJson: localFiltersJson.value.trim() !== '' ? localFiltersJson.value : undefined,
  })
}

function onClear() {
  localQ.value = ''
  localFiltersJson.value = ''
  emit('clear')
}
</script>

<template>
  <UCard>
    <template #header>
      <div class="flex items-center justify-between">
        <div class="font-semibold">Advanced search</div>
        <div class="flex items-center gap-2">
          <UButton size="xs" variant="ghost" @click="onClear">Clear</UButton>
          <UButton size="xs" color="primary" :disabled="hasFiltersError" @click="onSubmit">
            Search
          </UButton>
        </div>
      </div>
    </template>

    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
      <UFormGroup label="Keyword (q)">
        <UInput v-model="localQ" placeholder="Search..." />
      </UFormGroup>

      <UFormGroup label="Filters (JSON)" :error="hasFiltersError ? 'Invalid JSON object' : undefined">
        <UTextarea v-model="localFiltersJson" :rows="4" placeholder="{&quot;record_id&quot;: 123}" />
      </UFormGroup>
    </div>
  </UCard>
</template>


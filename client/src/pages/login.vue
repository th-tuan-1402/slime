<template>
  <div class="min-h-screen bg-gray-100 flex items-center justify-center p-6">
    <UCard class="w-full max-w-md">
      <template #header>
        <h1 class="text-xl font-semibold">Login</h1>
      </template>

      <form class="space-y-4" @submit.prevent="onSubmit">
        <UFormGroup label="Login ID" name="login_id" required>
          <UInput v-model="form.login_id" type="text" autocomplete="username" />
        </UFormGroup>

        <UFormGroup label="Password" name="password" required>
          <UInput
            v-model="form.password"
            type="password"
            autocomplete="current-password"
          />
        </UFormGroup>

        <p v-if="errorMessage" class="text-sm text-red-600">{{ errorMessage }}</p>

        <UButton type="submit" color="primary" block :loading="authStore.isLoading">
          Sign in
        </UButton>
      </form>
    </UCard>
  </div>
</template>

<script setup lang="ts">
import type { LoginRequestBody } from '~/api/generated/model/loginRequestBody'
import { navigateTo, reactive, ref, useRoute } from '#imports'
import { useAuth } from '~/composables/useAuth'
import { useAuthStore } from '~/stores/auth'

const route = useRoute()
const authStore = useAuthStore()
const { login } = useAuth()

const form = reactive<LoginRequestBody>({
  login_id: '',
  password: '',
})
const errorMessage = ref<string>('')

function resolveRedirectPath(): string {
  const redirect = route.query.redirect
  if (typeof redirect === 'string' && redirect.startsWith('/')) {
    return redirect
  }
  return '/'
}

async function onSubmit(): Promise<void> {
  errorMessage.value = ''

  if (form.login_id.trim() === '' || form.password.trim() === '') {
    errorMessage.value = 'Login ID and password are required.'
    return
  }

  try {
    await login({
      login_id: form.login_id.trim(),
      password: form.password,
    })
    await navigateTo(resolveRedirectPath())
  } catch (error) {
    const message =
      typeof error === 'object' &&
      error !== null &&
      'message' in error &&
      typeof error.message === 'string'
        ? error.message
        : 'Invalid credentials.'
    errorMessage.value = message
  }
}
</script>


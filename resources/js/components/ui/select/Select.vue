<template>
  <SelectRoot v-model="modelValue" @update:model-value="$emit('update:model-value', $event)">
    <slot />
  </SelectRoot>
</template>

<script setup lang="ts">
import { SelectRoot } from 'radix-vue'

interface Props {
  modelValue?: string
}

interface Emits {
  (e: 'update:model-value', value: string): void
}

const props = defineProps<Props>()
const emit = defineEmits<Emits>()
</script>

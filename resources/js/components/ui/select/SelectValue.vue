<template>
  <SelectValueRoot :placeholder="placeholder" />
</template>

<script setup lang="ts">
import { SelectValue as SelectValueRoot } from 'radix-vue'

interface Props {
  placeholder?: string
}

const props = defineProps<Props>()
</script>

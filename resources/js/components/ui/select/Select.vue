<script setup lang="ts">
import { SelectRoot, type SelectRootProps } from 'reka-ui'

const props = defineProps<SelectRootProps>()
</script>

<template>
  <SelectRoot v-bind="props">
    <slot />
  </SelectRoot>
</template>

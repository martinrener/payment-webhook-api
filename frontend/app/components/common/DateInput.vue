<template>
  <BaseInput
    type="date"
    :modelValue="modelValue"
    :label="label"
    :min="min"
    :max="max"
    @update:modelValue="$emit('update:modelValue', $event)"
  />
</template>

<script setup>
import BaseInput from '~/components/common/BaseInput.vue'

defineProps({
  modelValue: String,
  label: String,
  min: String,
  max: String,
})

defineEmits(['update:modelValue'])
</script>
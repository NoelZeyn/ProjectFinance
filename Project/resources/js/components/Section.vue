<!-- components/DetailSection.vue -->
<template>
  <div class="bg-gray-50 rounded-xl p-4 border border-gray-100">
    <h4 class="text-sm font-semibold text-[#074a5d] mb-4">
      {{ title }}
    </h4>

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
      <slot />
    </div>
  </div>
</template>

<script>
export default {
  props: { title: String }
};
</script>

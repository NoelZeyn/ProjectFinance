<template>
  <div class="flex flex-col gap-1">
    <label class="text-sm font-semibold text-gray-700">
      {{ label }}
      <span v-if="required" class="text-red-500 ml-1">*</span>
    </label>

    <input
      v-if="mode === 'edit'"
      :type="type"
      :value="modelValue"
      @input="$emit('update:modelValue', $event.target.value)"
      :required="required"
      class="p-2 border rounded-lg text-sm focus:ring-1 focus:ring-[#074a5d]"
    />

    <div
      v-else
      class="p-2 bg-gray-100 border rounded-lg text-sm text-gray-800"
    >
      {{ modelValue || "-" }}
    </div>
  </div>
</template>

<script>
export default {
  name: "FieldText",
  props: {
    label: String,
    modelValue: [String, Number],
    type: { type: String, default: "text" },
    mode: { type: String, default: "view" },
    required: { type: Boolean, default: false }
  },
  emits: ["update:modelValue"]
};
</script>

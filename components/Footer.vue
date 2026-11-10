<template>
  <footer class="mt-32 mx-4 mb-8 flex flex-col items-center text-center">
    <img :src="brand.logo" :alt="brand.name" class="h-12 w-auto p-1 bg-white rounded" />
    <p class="font-extrabold text-xl mt-2 leading-tight">{{ brand.name }}</p>
    <!-- AGPLv3 requires offering the source to users of a modified version; keep this link. -->
    <p class="mt-4 text-xs text-gray-500">
      Based on
      <a
        class="underline"
        href="https://github.com/vishnuraghavb/EnBizCard"
        target="_blank"
        rel="noopener noreferrer"
        >EnBizCard</a
      >
      · AGPLv3
    </p>
  </footer>
</template>

<script>
import brand from '~/brand.config'
export default {
  data: () => ({ brand }),
}
</script>

<template>
  <div class="spinner">
    <div
      class="d-flex flex-column align-items-center justify-content-center py-5"
    >
      <div class="spinner-border" role="status"></div>

      <div class="mt-3">
        <slot><span class="sr-only">Loading...</span></slot>
      </div>
    </div>
  </div>
</template>

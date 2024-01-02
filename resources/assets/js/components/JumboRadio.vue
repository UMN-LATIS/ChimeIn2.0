<template>
  <label class="jumbo-radio" :class="{ 'jumbo-radio--is-active': isActive }">
    <input
      type="radio"
      class="visually-hidden"
      :name="name"
      :value="value"
      :checked="isActive"
      @change="$emit('change', ($event.target as HTMLInputElement).value)"
    />
    <img class="jumbo-radio__img" :src="img.src" :alt="img.alt" />

    <h3 class="jumbo-radio__title">{{ title }}</h3>
    <h4 v-if="subtitle" class="jumbo-radio__subtitle">{{ subtitle }}</h4>
    <p class="jumbo-radio__description">
      {{ description }}
    </p>
  </label>
</template>

<script setup lang="ts">
withDefaults(
  defineProps<{
    img: {
      src: string;
      alt: string;
    };
    title: string;
    subtitle?: string;
    description: string;
    name: string;
    value: string;
    isActive?: boolean;
  }>(),
  {
    subtitle: "",
    isActive: false,
  },
);

defineEmits<{
  (eventName: "change", value: string);
}>();
</script>

<style lang="scss">
.jumbo-radio {
  padding: 2rem;
  border-radius: 0.5rem;
  border: 3px solid #ccc;
  background: #f4f4f4;
  transition: all 0.1s ease-out;
  text-align: center;
  cursor: pointer;
}

@media (max-width: 60rem) {
  .jumbo-radio {
    padding: 1.5rem 1.5rem;
  }
}

.jumbo-radio:hover {
  background: #fafafa;

  .jumbo-radio__img {
    filter: grayscale(0);
  }
}

.jumbo-radio.jumbo-radio--is-active {
  background: #fff;
  border-color: #111;
  transform: scale(1.05);
  .jumbo-radio__img {
    filter: grayscale(0) opacity(1);
  }
}

.jumbo-radio:not(.jumbo-radio--is-active) {
  opacity: 0.66;
}

.jumbo-radio__img {
  display: block;
  margin: 0 auto 2rem;
  filter: grayscale(1);
  max-width: 100%;
}

.jumbo-radio__title {
  font-size: 1.25rem;
}
.jumbo-radio__subtitle {
  font-size: 0.75rem;
  color: #777;
  margin: 0 0 1rem;
}

.jumbo-radio__description {
  color: #777;
  font-size: 0.9rem;
  margin: 0;
}
</style>

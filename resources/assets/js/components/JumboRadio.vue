<template>
  <label class="jumbo-radio" :class="{ 'jumbo-radio--is-active': isActive }">
    <input
      type="radio"
      class="visually-hidden"
      :name="name"
      :value="value"
      :checked="isActive"
      @change="$emit('change', $event.target.value)"
    />
    <img class="jumbo-radio__img" :src="img.src" :alt="img.alt" />

    <h3 class="jumbo-radio__title">{{ title }}</h3>
    <p class="jumbo-radio__description">
      {{ description }}
    </p>
  </label>
</template>

<style lang="scss">
.jumbo-radio {
  padding: 2rem;
  border-radius: 0.5rem;
  border: 3px solid #ccc;
  background: #f4f4f4;
  transition: all 0.3s ease-out;
  text-align: center;
}

@media (max-width: 60rem) {
  .jumbo-radio {
    padding: 1.5rem 1.5rem;
  }
}

.jumbo-radio--is-active,
.jumbo-radio:hover {
  background: #fff;
  border-color: #333;

  .jumbo-radio__img {
    filter: grayscale(0) opacity(1);
    transition: all 0.3s ease-out;
  }
}
.jumbo-radio__img {
  display: block;
  margin: 0 auto 2rem;
  filter: grayscale(1) opacity(0.666);
  max-width: 100%;
}

.jumbo-radio__title {
  font-size: 1.25rem;
}

.jumbo-radio__description {
  color: #777;
  font-size: 0.9rem;
  margin: 0;
}
</style>

<script>
export default {
  name: "JumboRadio",
  props: {
    img: Object,
    title: String,
    description: String,
    name: {
      type: String,
      required: true,
    },
    value: String,
    isActive: Boolean,
  },
  event: "change",
};
</script>

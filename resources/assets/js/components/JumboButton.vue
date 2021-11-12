<template>
  <div
    class="jumbo-button"
    :class="{ 'jumbo-button--is-active': isActive }"
    role="button"
  >
    <img class="jumbo-button__img" :src="img.src" :alt="img.alt" />

    <h3 class="jumbo-button__title">{{ title }}</h3>
    <p class="jumbo-button__description">
      {{ description }}
    </p>
  </div>
</template>

<style lang="scss">
.jumbo-button {
  padding: 2rem;
  border-radius: 0.5rem;
  border: 3px solid #ccc;
  background: #f4f4f4;
  transition: all 0.3s ease-out;
  text-align: center;
}

.jumbo-button__description {
  text-align: left;
}

@media (max-width: 60rem) {
  .jumbo-button {
    padding: 1.5rem 1.5rem;
  }
}

.jumbo-button--is-active,
.jumbo-button:hover {
  background: #fff;
  border-color: #333;

  .jumbo-button__img {
    filter: grayscale(0) opacity(1);
    transition: all 0.3s ease-out;
  }
}
.jumbo-button__img {
  display: block;
  margin: 0 auto 2rem;
  filter: grayscale(1) opacity(0.666);
  max-width: 100%;
}

.jumbo-button__title {
  font-size: 1.5rem;
}

.jumbo-button__description {
  color: #777;
  font-size: 0.9rem;
  margin: 0;
}
</style>

<script>
export default {
  name: "JumboButton",
  props: {
    img: Object,
    title: String,
    description: String,
    value: String,
    isActive: Boolean,
  },
};
</script>

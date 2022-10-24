<template>
  <DefaultLayout :user="user" class="not-found-page">
    <PostIt class="not-found-page__post-it">
      <div class="not-found-page__content">
        <img
          src="/images/umn-mascots@2x.jpg"
          alt="University of Minnesota
      mascots"
          class="not-found-page__img"
        />
        <div class="not-found-page__text-container">
          <h1 class="not-found-page__heading">404</h1>
          <h2 class="not-found-page__subheading">Not Found</h2>
          <p>
            Sorry! We can't seem to find the page you're looking for. This could
            be caused by a bad link or a mistyped URL.
          </p>
          <a href="/" class="btn btn-outline-primary">Go Home</a>
        </div>
      </div>
    </PostIt>
  </DefaultLayout>
</template>
<script setup lang="ts">
import { PostIt } from "../../../cla-vue-template/src";
import DefaultLayout from "../../layouts/DefaultLayout.vue";
import { User } from "../../types";

withDefaults(
  defineProps<{
    user?: User | null;
  }>(),
  {
    user: null,
  }
);
</script>
<style scoped>
.not-found-page__text-container {
  padding: 1rem;
}

.not-found-page__heading {
  font-size: 6rem;
  font-weight: light;
  margin: 0;
  color: #ccc;
  line-height: 1;
}
.not-found-page__subheading {
  font-size: 3rem;
  margin-top: 0;
  margin-bottom: 1.5rem;
}

@media (min-width: 48rem) {
  .not-found-page__content {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1rem;
    grid-template-areas: "text img";
  }

  .not-found-page__img {
    grid-area: img;
    object-fit: cover;
    width: 100%;
    height: 100%;
  }
  .not-found-page__text-container {
    grid-area: text;
    padding-left: 2rem;
    padding-right: 2rem;
  }
}
</style>
<style>
.not-found-page .inner-container {
  padding: 0;
}
</style>

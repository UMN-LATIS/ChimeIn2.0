<template>
  <div class="default-layout d-flex flex-column">
    <AppHeader>
      <template #app-link>
        <router-link to="/">ChimeIn</router-link>
      </template>
      <template #navbar-links>
        <slot name="navbar-left" />
      </template>
      <template #navbar-links-right>
        <NavbarItem v-if="!user">
          <a href="/login">Login</a>
        </NavbarItem>
        <NavbarItem v-if="user">
          <a href="/shibboleth-logout">Logout</a>
        </NavbarItem>
        <NavbarItem>
          <a href="https://umn-latis.github.io/ChimeIn2.0/">Help</a>
        </NavbarItem>
      </template>
    </AppHeader>
    <main class="default-layout__main flex-grow-1">
      <slot> Sorry. Nothing to see here. </slot>
    </main>
    <AppFooter />
  </div>
</template>
<script setup lang="ts">
import AppHeader from "../../cla-vue-template/src/components/AppHeader.vue";
import AppFooter from "../../cla-vue-template/src/components/AppFooter.vue";
import NavbarItem from "../../cla-vue-template/src/components/NavbarItem.vue";
import type { User } from "../types";

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
.default-layout {
  min-height: 100%;
}
.default-layout__main {
  padding-bottom: 4rem;
}
</style>

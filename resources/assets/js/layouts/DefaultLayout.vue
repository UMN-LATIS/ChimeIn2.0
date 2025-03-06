<template>
  <div class="default-layout d-flex flex-column">
    <ImpersonationBanner v-if="user?.isImpersonated" :user="user" />
    <AppHeader>
      <template #app-link>
        <router-link to="/">ChimeIn</router-link>
      </template>
      <template #navbar-links>
        <slot name="navbar-left" />
      </template>
      <template #navbar-links-right>
        <NavbarItem v-if="!isLoggedIn">
          <a href="/login">Login</a>
        </NavbarItem>
        <NavbarItem v-if="isLoggedIn">
          <a href="/shibboleth-logout">Logout</a>
        </NavbarItem>
        <NavbarItem>
          <a href="https://umn-latis.github.io/ChimeIn2.0/">Help</a>
        </NavbarItem>
      </template>
    </AppHeader>
    <main class="default-layout__main flex-grow-1" v-bind="$attrs">
      <slot> Sorry. Nothing to see here. </slot>
    </main>
    <AppFooter class="default-layout__app-footer" />
  </div>
</template>
<script setup lang="ts">
import { AppHeader, AppFooter, NavbarItem } from "@umn-latis/cla-vue-template";
import ImpersonationBanner from "@/components/ImpersonationBanner.vue";
import { computed } from "vue";
import type { CurrentUser, User } from "../types";

const props = withDefaults(
  defineProps<{
    user?: CurrentUser | User | null;
  }>(),
  {
    user: null,
  }
);

const isLoggedIn = computed(() => props.user && !props.user.guest_user);
</script>
<script lang="ts">
// have attrs like `class` applied to the `<main>` element
export default {
  inheritAttrs: false,
};
</script>

<style scoped>
.default-layout {
  min-height: 100vh;
}
.default-layout__main {
  padding-bottom: 4rem;
}

/* 
* the app-footer cla component has a negative margin so that it can tuck
* under the "post-it" component. Since we're not using the post-it component,
* we need to remove the negative margin to prevent the footer from overlapping
* the main section.
*/
.default-layout__app-footer {
  margin-top: 0;
}

/**
 * decrease app-footer padding to compensate for no negative margin
 */
.default-layout__app-footer:deep(.footer-offset-container) {
  padding-top: 3rem;
}
</style>

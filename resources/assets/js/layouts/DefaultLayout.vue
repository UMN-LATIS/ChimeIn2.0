<template>
  <div class="default-layout">
    <AppHeader>
      <template #app-link>
        <router-link to="/">ChimeIn</router-link>
      </template>
      <template #navbar-links>
        <NavbarItem class="active"><a href="/">Home</a></NavbarItem>
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
    <slot> Sorry. Nothing to see here. </slot>
    <AppFooter />
  </div>
</template>
<script setup lang="ts">
import AppHeader from "../../cla-vue-template/src/components/AppHeader.vue";
import AppFooter from "../../cla-vue-template/src/components/AppFooter.vue";
import NavbarItem from "../../cla-vue-template/src/components/NavbarItem.vue";
import type { User } from "../types";

defineProps<{
  user?: User;
}>();
</script>

<template>
  <nav class="navbar navbar-expand-lg navbar-light bg-light align-center">
    <router-link v-if="title" :to="link" class="navbar-brand">{{
      title
    }}</router-link>

    <button
      class="navbar-toggler"
      type="button"
      data-toggle="collapse"
      data-target="#navbarSupportedContent"
      aria-controls="navbarSupportedContent"
      aria-expanded="false"
      aria-label="Toggle navigation"
    >
      <span class="navbar-toggler-icon"></span>
    </button>
    <div id="navbarSupportedContent" class="collapse navbar-collapse">
      <ul class="navbar-nav ml-auto">
        <li class="nav-item">
          <a
            class="nav-link align-items-center d-flex"
            href="https://umn-latis.github.io/ChimeIn2.0/"
            >Help <i class="material-icons">help</i></a
          >
        </li>
        <li class="nav-item">
          <a
            v-if="user.impersonating"
            class="nav-link align-items-center d-flex"
            href="/impersonate/leave"
            >End Impersonation<i class="material-icons">logout</i></a
          >
        </li>
        <li class="nav-item">
          <a
            v-if="user.id && !user.guest_user"
            href="/shibboleth-logout"
            class="nav-link align-items-center d-flex"
            >Log out <i class="material-icons">logout</i>
          </a>
          <a v-else href="/login" class="nav-link align-items-center d-flex"
            >Log in <i class="material-icons">account_box</i>
          </a>
        </li>
      </ul>
    </div>
  </nav>
</template>

<script>
export default {
  props: ["title", "user", "link", "access_code", "host"],
};
</script>

<style scoped>
.material-icons {
  font-size: 1.2em;
  margin-left: 2px;
}

.ml-auto li {
  margin-left: 2px;
  margin-right: 2px;
}

.navbar .navbar-collapse {
  text-align: center;
}
</style>

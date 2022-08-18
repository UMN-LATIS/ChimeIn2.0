<template>
  <div class="view-mode">
    <header class="view-mode__header">
      <i class="material-icons">preview</i>
      <h2 class="view-mode__heading">Participant View</h2>
    </header>
    <div class="view-mode__description d-none d-md-block">
      <p>This is a preview of what participants will see.</p>
      <p>
        <a :href="canvasUrl || joinUrl">{{ canvasUrl || joinUrl }}</a>
      </p>
    </div>
    <div>
      <router-link :to="callbackUrl" class="btn btn-outline-secondary">
        Leave <span class="d-none d-md-inline">Participant View</span>
      </router-link>
    </div>
  </div>
</template>
<script>
export default {
  props: {
    canvasUrl: {
      type: String,
      default: "",
    },
    joinUrl: {
      type: String,
      default: "",
    },
  },
  computed: {
    callbackUrl() {
      return this.$route.query.callbackUrl || "/";
    },
  },
  mounted() {
    document.body.classList.add("page--viewmode-participant");
  },
  unmounted() {
    document.body.classList.remove("page--viewmode-participant");
  },
};
</script>
<style>
:root {
  --color-viewmode-bg: #bf32a5;
  --color-viewmode-heading: #fff;
  --color-viewmode-text: #fff;
  --color-viewmode-link: #fff;
}
.page--viewmode-participant {
  border: 0.25rem solid var(--color-viewmode-bg);
  padding-bottom: 4rem;
}
</style>

<style scoped>
.view-mode {
  background: var(--color-viewmode-bg);
  color: var(--color-viewmode-text);
  font-size: 0.8rem;
  position: fixed;
  bottom: 0;
  left: 0;
  z-index: 10;
  padding: 1rem;
  display: flex;
  align-items: center;
  gap: 1rem;
  width: 100%;
  justify-content: space-between;
}

.view-mode__header {
  display: flex;
  align-items: center;
  color: var(--color-viewmode-heading);
}

.view-mode__header i {
  margin-right: 0.5rem;
}

.view-mode__heading {
  color: var(--color-viewmode-heading);
  font-size: 1rem;
  text-transform: uppercase;
  font-weight: bold;
  letter-spacing: 1px;
  margin: 0;
}

.view-mode__subheading {
  color: var(--color-viewmode-heading);
  font-size: 1rem;
  text-transform: uppercase;
  font-weight: bold;
  letter-spacing: 1px;
  margin: 0;
}
.view-mode__description i {
  font-size: 1rem;
  vertical-align: text-bottom;
}

.view-mode__description a {
  text-decoration: underline;
}

.view-mode a {
  color: var(--color-viewmode-link);
}

.view-mode p {
  margin: 0.25em 0;
}

.view-mode .btn {
  font-size: 1rem;
  border-color: var(--color-viewmode-link);
  background: var(--color-viewmode-link);
  color: var(--color-viewmode-bg);
}
.view-mode .btn:hover,
.view-mode .btn:focus {
  background: var(--color-viewmode-link);
  color: var(--color-viewmode-bg);
}
</style>

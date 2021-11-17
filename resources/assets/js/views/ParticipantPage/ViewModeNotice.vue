<template>
  <div class="view-mode grid">
    <div>
      <header class="view-mode__header">
        <i class="material-icons">preview</i>
        <h2 class="view-mode__heading">Participant View</h2>
      </header>
      <p>This is a preview of what your Chime participants will see.</p>
      <p>
        <a :href="canvasUrl || joinUrl">{{ canvasUrl || joinUrl }}</a>
      </p>
    </div>
    <div>
      <router-link :to="callbackUrl" class="btn btn-outline-secondary">
        Leave Participant View
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
  methods: {
    leaveParticipantView() {
      window.history.length > 1 ? this.$router.go(-1) : this.$router.push("/");
    },
  },
  mounted() {
    document.body.classList.add("page--viewmode-participant");
  },
  destroyed() {
    document.body.classList.remove("page--viewmode-participant");
  },
};
</script>
<style>
:root {
  --color-viewmode-bg: #333;
  --color-viewmode-heading: #ccc;
  --color-viewmode-text: #777;
  --color-viewmode-link: hsla(45deg, 90%, 65%, 0.9);
}
.page--viewmode-participant {
  border: 1rem solid var(--color-viewmode-bg);
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
}

.view-mode__header {
  display: flex;
  align-items: center;
  color: var(--color-viewmode-heading);
  margin-bottom: 0.5rem;
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

.grid {
  width: 100%;
  display: grid;
  grid-template-columns: 2fr 1fr;
  gap: 1rem;
}
.view-mode a {
  color: var(--color-viewmode-link);
}

.view-mode p {
  margin: 0.5em 0;
}

.view-mode > div:last-child {
  display: flex;
  align-items: center;
  justify-content: flex-end;
}
.view-mode .btn {
  font-size: 1rem;
  border-color: var(--color-viewmode-link);
  color: var(--color-viewmode-link);
}
.view-mode .btn:hover,
.view-mode .btn:focus {
  background: var(--color-viewmode-link);
  color: var(--color-viewmode-bg);
}
</style>

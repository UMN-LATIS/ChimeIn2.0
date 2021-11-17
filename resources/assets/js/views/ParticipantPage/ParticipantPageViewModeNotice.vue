<template>
  <div class="view-mode">
    <div class="column column--participant-view">
      <i class="material-icons">preview</i>
      <div>
        <h2 class="view-mode__heading">Participant View</h2>
        <p>This is a preview of what your Chime participants will see.</p>
      </div>
    </div>
    <div class="column column--share">
      <h3 class="view-mode__subheading">Link to Join</h3>
      <p>
        <a :href="canvasUrl || joinUrl">{{ canvasUrl || joinUrl }}</a>
      </p>
    </div>
    <div class="column column--leave">
      <button class="btn btn-outline-secondary" @click="$router.go(-1)">
        Leave Participant View
      </button>
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
  /* --color-viewmode-link: var(--gold); */
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
  color: #777;
  font-size: 0.8rem;
  position: fixed;
  bottom: 0;
  left: 0;
  z-index: 10;
  display: grid;
  grid-template-columns: repeat(3, minmax(0, 1fr));
  gap: 1rem;
  padding: 1.5rem 1rem 1rem;
}
.view-mode a {
  color: var(--color-viewmode-link);
}

.view-mode__heading,
.view-mode__subheading {
  color: var(--color-viewmode-heading);
  font-size: 0.8rem;
  text-transform: uppercase;
  font-weight: bold;
  letter-spacing: 1px;
}

.view-mode__subheading {
  color: var(--color-viewmode-text);
}

.view-mode p {
  margin: 0;
}
.view-mode button {
  font-size: 0.8rem;
  border-color: var(--color-viewmode-link);
  color: var(--color-viewmode-link);
}
.view-mode > div:last-child {
  align-self: center;
  justify-self: flex-end;
}

.column:first-child {
  display: flex;
  justify-content: center;
  align-items: center;
}
i {
  display: block;
  margin-right: 1rem;
  font-size: 2rem;
}
</style>

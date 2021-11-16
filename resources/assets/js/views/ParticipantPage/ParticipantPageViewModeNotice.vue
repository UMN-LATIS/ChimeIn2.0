<template>
  <div class="view-mode">
    <div>
      <h2 class="view-mode__heading">Participant View</h2>
      <p>This is a preview of what your Chime participants will see.</p>
    </div>
    <div>
      <h2 class="view-mode__heading">Share</h2>
      <p>
        <a href="canvasUrl || joinUrl">{{ canvasUrl || joinUrl }}</a>
      </p>
    </div>
    <div>
      <button class="btn btn-outline-secondary" @click="$router.go(-1)">
        Leave Participant View
      </button>
    </div>
  </div>
</template>
<script>
export default {
  props: {
    canvasChimeUrl: String,
    joinUrl: String,
  },
};
</script>
<style scoped>
.view-mode {
  background: hsla(0, 0%, 0%, 0.75);
  backdrop-filter: blur(1rem);
  color: #aaa;
  padding: 1rem;
  font-size: 0.8rem;
  position: fixed;
  bottom: 0;
  left: 0;
  width: 100vw;
  z-index: 100;
  display: grid;
  grid-template-columns: repeat(3, minmax(0, 1fr));
  gap: 1rem;
}
.view-mode__heading {
  font-size: 0.8rem;
  text-transform: uppercase;
  font-weight: bold;
  letter-spacing: 1px;
}
.view-mode p {
  margin: 0;
}
.view-mode button {
  font-size: 0.8rem;
  border-color: #aaa;
  color: #aaa;
}
.view-mode > div:last-child {
  padding-right: 1rem;
  align-self: center;
  justify-self: flex-end;
}
</style>

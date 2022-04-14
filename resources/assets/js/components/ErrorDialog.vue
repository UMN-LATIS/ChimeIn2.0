<template>
  <Modal
    :show="$store.getters.message"
    style="z-index: 15000"
    :closeable="false"
  >
    <div class="alert alert-danger" role="alert">
      <strong>Error: </strong> {{ $store.getters.message }} Refresh your
      browser, or return to the <router-link to="/">home page</router-link> to
      try again. If the error continues, please email
      <a href="mailto:help@umn.edu">help@umn.edu</a>.
    </div>

    <!-- Use real link rather than router-link to reload page-->
    <a href="/" class="btn btn-primary">Go to Home Page</a>
  </Modal>
</template>

<style scoped>
.alert {
  z-index: 15000;
}
</style>
<script>
import Modal from "./Modal.vue";

export default {
  components: {
    Modal,
  },
};
</script>

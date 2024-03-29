<template>
  <Transition name="modal">
    <div v-show="show" class="modal-mask" @mousedown="close">
      <div class="modal-container" @mousedown.stop>
        <button v-if="closeable" class="modal__close-button" @click="close">
          <i class="material-icons">close</i>
          <span class="sr-only">Close</span>
        </button>
        <slot></slot>
      </div>
    </div>
  </Transition>
</template>

<script>
export default {
  props: {
    show: {
      type: Boolean,
      default: false,
    },
    closeable: {
      type: Boolean,
      default: true,
    },
  },
  emits: ["close"],
  mounted: function () {
    document.addEventListener("keydown", (e) => {
      if (this.show && e.keyCode == 27) {
        this.close();
      }
    });
  },
  methods: {
    close: function () {
      this.$emit("close");
    },
  },
  template: "#modal-template",
};
</script>

<style>
.modal-enter {
  opacity: 0;
}

.modal-leave-active {
  opacity: 0;
}

.modal-enter .modal-container,
.modal-leave-active .modal-container {
  -webkit-transform: scale(1.1);
  transform: scale(1.1);
}

.modal-mask {
  position: fixed;
  z-index: 9998;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background-color: rgba(0, 0, 0, 0.5);
  transition: opacity 0.3s ease;
}

.modal-container {
  width: 80%;
  max-width: 600px;
  margin: 20px auto 0;
  padding: 20px 30px;
  background-color: #fff;
  border-radius: 2px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.33);
  transition: all 0.3s ease;
  font-family: Helvetica, Arial, sans-serif;
  max-height: calc(100vh - 40px);
  overflow-y: auto;
  position: relative;
}

.modal-header {
  padding: 0;
}

.modal-header h3 {
  margin-top: 0;
  color: #42b983;
  line-height: 1.2em;
  font-size: 1.2em;
}

.modal-body {
  padding: 0;
  margin: 10px 0;
}
.modal__close-button {
  border: none;
  background: none;
  position: absolute;
  right: 0;
  top: 0.5rem;
}

@media (max-width: 28rem) {
  .modal-container {
    width: 90%;
  }
}
</style>

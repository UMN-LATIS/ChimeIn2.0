<template>
 <transition name="modal">
    <div class="modal-mask" @mousedown="close" v-show="show">
        <div class="modal-container" @mousedown.stop>
          <slot></slot>
      </div>
  </div>
</transition>
</template>

<script>

export default {
    template: '#modal-template',
    props: ['show'],
    methods: {
        close: function () {
          this.$emit('close');
      }
  },
  mounted: function () {
    document.addEventListener("keydown", (e) => {
      if (this.show && e.keyCode == 27) {
        this.close();
    }
});
}
}
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
    background-color: rgba(0, 0, 0, .5);
    transition: opacity .3s ease;
}

.modal-container {
    width: 80%;
    max-width: 600px;
    margin: 20px auto 0;
    padding: 20px 30px;
    background-color: #fff;
    border-radius: 2px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, .33);
    transition: all .3s ease;
    font-family: Helvetica, Arial, sans-serif;
    max-height: calc(100vh - 40px);
    overflow-y: auto;
}

.modal-header {
  padding: 0;
}

.modal-header h3 {
    margin-top: 0;
    color: #42b983;
    line-height: 1.2em;
    font-size:1.2em;
}

.modal-body {
  padding:0;
    margin: 10px 0;
}
</style>

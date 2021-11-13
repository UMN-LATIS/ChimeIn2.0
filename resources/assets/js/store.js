import Vue from "vue";
import Vuex from "vuex";

Vue.use(Vuex);

export default new Vuex.Store({
  state: {
    message: null,
  },
  mutations: {
    message(state, message) {
      state.message = message;
    },
    clearMessage(state) {
      state.message = null;
    },
  },
  getters: {
    message: (state) => state.message,
  },
});

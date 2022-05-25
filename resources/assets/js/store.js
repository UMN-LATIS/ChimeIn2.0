import { createStore } from "vuex";

const store = createStore({
  state: () => ({
    message: null,
  }),
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

export default store;

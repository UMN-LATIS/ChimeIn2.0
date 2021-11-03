<template>
  <form v-on:submit.prevent="submit">
    <label>
      <input type="checkbox" v-model="isCorrect" />
      <span class="visually-hidden">Choice is correct?</span>
    </label>
    <input type="text" placeholder="Add a response choice" v-model="text" />
    <button type="submit">Add Choice</button>
  </form>
</template>

<script>
export default {
  emits: ["submit"],
  methods: {
    submit(event) {
      this.$emit("submit", { ...this.$data }, event);
      this.isCorrect = false;
      this.text = "";
    },
  },
  data: () => ({
    isCorrect: false,
    text: "",
  }),
};
</script>

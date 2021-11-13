<template>
  <div class="card hoverable">
    <div class="card-body" v-on:click="open_chime">
      <div class="float-left">
        <h2 class="card-title h5">{{ chime.name }}</h2>
        <p v-on:click.stop="" class="h6 card-subtitle mb-2 text-muted">
          Access code: {{ hyphenatedCode }}
        </p>
      </div>
      <div class="float-right">
        <a href="#" v-on:click.stop="delete_chime()">
          <i class="material-icons right">delete</i>
        </a>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  props: ["chime", "user"],
  data: function () {
    return {};
  },
  methods: {
    open_chime() {
      if (this.chime.pivot.permission_number >= 200) {
        this.$router.push({
          name: "chime",
          params: {
            chimeId: this.chime.id,
          },
        });
      } else {
        this.$router.push({
          name: "chimeStudent",
          params: {
            chimeId: this.chime.id,
          },
        });
      }
    },
    delete_chime() {
      const confirmation = window.confirm(
        "Delete Chime: " + this.chime.name + " ?"
      );

      if (confirmation) {
        axios
          .delete("/api/chime/" + this.chime.id)
          .then((res) => {
            this.$emit("updatedChime");
          })
          .catch((err) => {
            console.error("error", "Error in delete chime:", err.response);
          });
      }
    },
  },
  computed: {
    hyphenatedCode: function () {
      return this.chime.access_code.replace(/(\d{3})(\d{3})/, "$1-$2");
    },
  },
};
</script>

<template>
  <div>
    <div class="row">
      <div class="col-12">
        <p data-cy="chime-count-summary">
          You have access to {{ chimes.length }}
          {{ pluralize("chime", chimes.length) }}.
        </p>
        <button
          v-if="!user.guest_user"
          dusk="add-chime-button"
          class="btn btn-outline-primary align-items-center d-flex"
          @click="showAdd = !showAdd"
        >
          <i class="material-icons">add</i> Add Chime
        </button>
        <form
          v-if="!user.guest_user && showAdd"
          class="card mt-1"
          @submit.prevent="create_chime"
        >
          <div class="card-body">
            <div class="form-group row">
              <label for="chime_name_input" class="col-sm-2 col-form-label"
                >Chime Name</label
              >
              <div class="col-sm-8">
                <input
                  id="chime_name_input"
                  v-model="newChime.name"
                  class="form-control"
                  type="text"
                />
              </div>
            </div>
            <div class="row">
              <ChimeManagementOptions
                :require_login="newChime.requireLogin"
                :students_can_view="newChime.studentsCanView"
                :join_instructions="newChime.joinInstructions"
                :show_folder_title_to_participants="
                  newChime.showFolderTitleToParticipants
                "
                :new_chime="true"
                @update="handleNewChimeOptionsUpdate"
              />
            </div>
            <div class="row">
              <div class="col">
                <button
                  data-cy="create-chime-button"
                  class="btn btn-primary"
                  type="submit"
                >
                  Create
                </button>
              </div>
            </div>
          </div>
        </form>
        <div v-if="chimes.length > 0" class="chime-card-group">
          <ChimeCard
            v-for="chime in orderedChimes"
            :key="chime.id"
            class="chime-card-group__item"
            :chime="chime"
            :showMoveIcon="orderedChimes.length > 1"
            :to="getUserLinkToChime({ user, chime })"
            @change="handleChimeCardChange"
          />
        </div>
        <div v-else class="my-3">
          <p v-if="user.guest_user">
            You're currently browsing as a guest. If you have a Chime access
            code, you can enter it on the right. Otherwise,
            <a href="/login">log in</a> to get started.
          </p>
          <p v-else>You don't currently have any Chimes.</p>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import orderBy from "lodash/orderBy";
import ChimeCard from "./ChimeCard.vue";
import ChimeManagementOptions from "../../components/ChimeManagementOptions.vue";
import pluralize from "../../common/pluralize.js";

const newChimeDefaults = {
  name: "",
  require_login: false,
  students_can_view: false,
  join_instructions: true,
  show_folder_title_to_participants: false,
};

export default {
  components: {
    ChimeCard,
    ChimeManagementOptions,
  },
  props: ["user", "chimes"],
  emits: ["update:chimes"],
  data() {
    return {
      newChime: { ...newChimeDefaults },
      showAdd: false,
      modalChime: null,
      isRemoveConfirmOpen: false,
    };
  },
  computed: {
    orderedChimes() {
      return orderBy(this.chimes, "created_at", ["desc"]);
    },
  },
  methods: {
    pluralize,
    handleNewChimeOptionsUpdate(updates) {
      this.newChime = {
        ...this.newChime,
        ...updates,
      };
    },
    handleChimeCardChange(payload) {
      this.$emit("update:chimes", payload);
    },
    getUserLinkToChime({ chime }) {
      const isUserEditorOfChime = (chime) =>
        chime.pivot.permission_number >= 200;

      return isUserEditorOfChime(chime)
        ? `/chime/${chime.id}`
        : `/chimeParticipant/${chime.id}`;
    },
    create_chime() {
      if (this.newChime.name.length == 0) {
        alert("You must enter a name for the Chime");
        return;
      }

      axios
        .post("/api/chime", this.newChime)
        .then((res) => {
          this.showAdd = false;
          this.newChime = { ...newChimeDefaults };
          this.$emit("update:chimes", res.data);
          this.$router.push({
            name: "chime",
            params: { chimeId: res.data.id },
          });
        })
        .catch((err) => {
          console.error(err);
        });
    },
  },
};
</script>

<style scoped>
.chime-card-group {
  margin: 1rem 0;
}
</style>

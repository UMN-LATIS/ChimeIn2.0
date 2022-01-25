<template>
  <div>
    <div class="row">
      <div class="col-12">
        <p data-cy="chime-count-summary">
          You have access to {{ chimes.length }}
          {{ "chime" | pluralize(chimes.length) }}.
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
                  v-model="chime_name"
                  class="form-control"
                  type="text"
                />
              </div>
            </div>
            <div class="row">
              <ChimeManagementOptions
                :require_login.sync="requireLogin"
                :students_can_view.sync="studentsCanView"
                :join_instructions.sync="joinInstructions"
                :new_chime="true"
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
            class="chime-card-group__item"
            :key="chime.id"
            :chime="chime"
            :showMoveIcon="orderedChimes.length > 1"
            :to="getUserLinkToChime({ user, chime })"
            @change="get_chimes"
            @removeChime="handleRemoveChime"
          />
          <Modal :show="isRemoveConfirmOpen">
            <h2>Remove yourself?</h2>
            <p>You will no longer be able to access this chime.</p>
            <div class="d-flex justify-content-between">
              <button
                class="btn btn-outline-secondary align-items-center d-flex"
                @click="isRemoveConfirmOpen = false"
              >
                Cancel
              </button>
              <div class="align-items-center d-flex">
                <button
                  v-if="this.canCurrentUserEdit"
                  class="btn btn-outline-danger align-items-center d-flex mr-2"
                  @click="deleteChime"
                >
                  <i class="material-icons mr-2">delete</i>
                  Delete Chime
                </button>
                <button
                  class="btn btn-danger align-items-center d-flex"
                  @click="removeSelfFromChime"
                >
                  <i class="material-icons mr-2">person_remove</i>
                  Remove Myself
                </button>
              </div>
            </div>
          </Modal>
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
import { EventBus } from "../../EventBus.js";
import ChimeCard from "./ChimeCard.vue";
import ChimeManagementOptions from "../../components/ChimeManagementOptions.vue";
import Modal from "../../components/Modal.vue";

export default {
  components: {
    ChimeCard,
    ChimeManagementOptions,
    Modal,
  },
  props: ["user"],
  data() {
    return {
      requireLogin: false,
      studentsCanView: false,
      joinInstructions: true,
      chimes: [],
      showAdd: false,
      chime_name: "",
      modalChime: null,
      isRemoveConfirmOpen: false,
    };
  },
  computed: {
    orderedChimes: function () {
      return orderBy(this.chimes, "created_at", ["desc"]);
    },
  },
  created: function () {
    this.get_chimes();
    var self = this;
    EventBus.$on("chimesChanged", function () {
      self.get_chimes();
    });
  },
  methods: {
    getUserLinkToChime({ chime }) {
      const isUserEditorOfChime = (chime) =>
        chime.pivot.permission_number >= 200;

      return isUserEditorOfChime(chime)
        ? `/chime/${chime.id}`
        : `/chimeParticipant/${chime.id}`;
    },
    create_chime() {
      if (this.chime_name.length == 0) {
        alert("You must enter a name for the Chime");
        return;
      }
      axios
        .post("/api/chime", {
          name: this.chime_name,
          require_login: this.requireLogin,
          students_can_view: this.studentsCanView,
          join_instructions: this.joinInstructions,
        })
        .then((res) => {
          console.log("debug", "Chime Created:", res);
          this.showAdd = false;
          EventBus.$emit("chimesChanged");
          this.$router.push({
            name: "chime",
            params: { chimeId: res.data.id },
          });
        })
        .catch((err) => {
          console.log("error", "Error in create chime:", err.response);
        });
    },
    get_chimes() {
      axios
        .get("/api/chime")
        .then((res) => {
          console.log("debug", "Get Chimes:", res);
          this.chimes = res.data;
        })
        .catch((err) => {
          console.error("error", "Error in get chimes:", err.response);
        });
    },
    removeSelfFromChime() {
      console.log("removeSelfFromChime");
    },
    deleteChime() {
      // if user can edit, this will delete the chime
      // if not, this will remove the user from the chime
      // const confirmMessage = this.canCurrentUserEdit
      //   ? `Delete chime '${this.chime.name}'?`
      //   : `Are you sure you want to remove yourself from '${this.chime.name}'?`;

      const confirmation = window.confirm(
        `Delete chime ${this.modalChime.name}?`
      );

      if (!confirmation) return;

      // optimistic UI: hide card unless failure
      this.showCard = false;
      this.isRemoveConfirmOpen = false;

      axios
        .delete("/api/chime/" + this.modalChime.id, { timeout: 2000 })
        .then(() => this.$emit("change"))
        .catch((err) => {
          this.showCard = true;
          console.error("Error in removeChime request.", err);
        });
    },
    handleRemoveChime(chime) {
      console.log("handleRemoveChime", chime);
      this.isRemoveConfirmOpen = true;
      this.modalChime = chime;
    },
  },
};
</script>

<style scoped>
.chime-card-group {
  margin: 1rem 0;
}
</style>

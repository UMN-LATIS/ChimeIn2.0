<template>
  <div class="chime">
    <navbar title="Back to Home" :user="user" :link="'/'" />
    <error-dialog />
    <div class="container">
      <header class="chime__header">
        <div class="chime__header-container">
          <div>
            <p class="chime__header-label">
              Chime
            </p>
            <h1 class="chime__name">{{ chime.name }}</h1>
          </div>

          <div
            class="chime__control-buttons btn-group"
            role="group"
            aria-label="Chime Controls"
            :class="{
              'chime__control-buttons--is-active': showSettings || exportPanel,
            }"
          >
            <button
              @click="toggle('showSettings', { setToFalse: ['exportPanel'] })"
              class="btn btn-outline-secondary align-items-center d-flex"
              :class="{ 'btn--is-active': showSettings }"
            >
              Chime Settings <span class="material-icons">settings</span>
            </button>

            <button
              @click="toggle('exportPanel', { setToFalse: ['showSettings'] })"
              class="btn btn-outline-secondary align-items-center d-flex"
              :class="{ 'btn--is-active': exportPanel }"
            >
              Export <span class="material-icons">save_alt</span>
            </button>
          </div>
        </div>
        <div
          class="chime__settings-panel"
          :class="{
            'chime__settings-panel--isOpen': showSettings || exportPanel,
          }"
        >
          <ChimeManagement v-if="showSettings" :chime.sync="chime" />
          <ChimeExport v-if="exportPanel" :chime="chime" />
        </div>
      </header>

      <Spinner v-if="!isReady" />

      <div class="chime__folder-wrapper" v-if="isReady">
        <div class="chime__folder-list">
          <p v-if="!ordered_folders.length">
            You don't have any folders yet. Why not create one now?
          </p>
          <draggable
            v-else
            class="chime__ordered-folders"
            v-model="ordered_folders"
            handle=".draghandle"
            :forceFallback="true"
          >
            <folder-card
              v-for="folder in ordered_folders"
              :key="folder.id"
              :folder="folder"
              :chime="chime"
              :draggable="ordered_folders.length > 1"
              :ltiLink="getLtiLink(folder)"
            />
          </draggable>
        </div>
        <new-folder
          class="chime__create-folder"
          :chime="chime"
          v-on:newfolder="create_folder"
        />
      </div>
    </div>
  </div>
</template>

<style scoped>
.chime__header-label {
  text-transform: uppercase;
  color: var(--gray-medium);
  margin: 0 0 0.25rem 0;
}
.chime__header {
  margin: 2rem 0 2rem;
}

.chime__header-container {
  display: flex;
  justify-content: space-between;
  align-items: center;
}
.chime__name {
  font-size: 2rem;
}

.chime__control-buttons .material-icons {
  font-size: 1.25rem;
  margin-left: 0.25rem;
}

.chime__control-buttons--is-active {
  box-shadow: 0 0.25rem 0.5rem rgb(0 0 0 / 20%);
}

.chime__control-buttons .btn--is-active {
  background-color: var(--gold-light);
  color: var(--gray-dark);
}

.chime__settings-panel {
  display: none;
  margin-top: 1rem;
  padding: 2rem;
  background-color: #eee;
  line-height: 1.5;
  border-radius: 0.25rem;
  box-shadow: 0 0.5rem 1rem rgb(0 0 0 / 40%);
}
.chime__settings-panel--isOpen {
  display: block;
}
</style>

<script>
import draggable from "vuedraggable";
import Spinner from "./Spinner.vue";

export default {
  components: {
    draggable,
    Spinner,
  },
  data() {
    return {
      isReady: false,
      chime: {},
      showSettings: false,
      exportPanel: false,
    };
  },
  props: ["user", "chimeId"],
  methods: {
    toggle(value, { setToFalse = [], setToTrue = [] } = {}) {
      this[value] = !this[value];
      setToFalse.forEach((key) => {
        this[key] = false;
      });
      setToTrue.forEach((key) => {
        this[key] = true;
      });
    },
    create_folder: function(folder_name) {
      if (folder_name.length == 0) {
        alert("You must enter a name for this folder.");
        return;
      }
      if (this.chime.folders.filter((e) => e.name === folder_name).length < 1) {
        axios
          .post("/api/chime/" + this.chime.id + "/folder", {
            folder_name: folder_name,
          })
          .then((res) => {
            console.log(res);
            this.chime.folders.push(res.data);
          })
          .catch((err) => {
            console.log(err.response);
          });
      }
    },
    reloadChime: function() {
      this.isReady = false;
      axios
        .get("/api/chime/" + this.chimeId)
        .then((res) => {
          this.chime = res.data;
          document.title = this.chime.name;
        })
        .catch((err) => {
          this.$store.commit(
            "message",
            "Could not load Chime. You may not have permission to view this page. "
          );
          console.log(err);
        })
        .finally(() => {
          this.isReady = true;
        });
    },
    getLtiLink(folder) {
      // check if there's an lti link for this folder
      // scrub the url in case it has
      // `/external_content/success/external_tool_redirect`
      // at the end
      return (
        folder.resource_link_pk &&
        this.chime.lti_return_url &&
        this.chime.lti_return_url.replace(/external_content.*/, "")
      );
    },
  },
  computed: {
    ordered_folders: {
      get() {
        if (!this.chime || !this.chime.folders) {
          return [];
        }
        return _.orderBy(this.chime.folders, ["order", "id"], ["asc", "asc"]);
      },
      set(value) {
        console.log(value);
        value.map((f, index) => (f.order = index + 1));
        const url = "/api/chime/" + this.chime.id;
        axios
          .put(url, {
            folders: this.chime.folders,
          })
          .then((res) => {
            this.chime.folders = value;
          })
          .catch((err) => {
            console.log(err.response);
          });
      },
    },
  },
  created: function() {
    this.reloadChime();
  },
};
</script>

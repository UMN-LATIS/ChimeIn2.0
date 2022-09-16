<template>
  <DefaultLayout :user="user">
    <template #navbar-left>
      <Back :to="`/`">Back to Home</Back>
    </template>
    <div class="chime container-fluid mt-4">
      <ErrorDialog />
      <div>
        <header class="chime__header">
          <Chip
            v-if="isCanvasChime"
            class="chime-header__canvas-chip"
            color="yellow"
            :solid="true"
            title="This chime is linked with Canvas."
          >
            Canvas
          </Chip>
          <div class="chime__header-container">
            <h1 class="chime__name" data-cy="chime-name">
              {{ chime.name }}
            </h1>
            <div
              class="chime-page-header__button-group"
              role="group"
              aria-label="Chime Controls"
            >
              <button
                class="btn"
                :class="{ 'btn--is-active': showSettings }"
                data-cy="toggle-chime-settings-panel"
                @click="toggle('showSettings', { setToFalse: ['exportPanel'] })"
              >
                <i class="material-icons">settings</i> Chime Settings
              </button>

              <button
                class="btn"
                :class="{ 'btn--is-active': exportPanel }"
                data-cy="toggle-chime-export-panel"
                @click="toggle('exportPanel', { setToFalse: ['showSettings'] })"
              >
                <i class="material-icons">save_alt</i> Export
              </button>
            </div>
          </div>
          <div
            v-if="isReady"
            class="chime-settings-panel"
            :class="{
              'chime-settings-panel--isOpen': showSettings || exportPanel,
            }"
          >
            <div class="chime-settings-panel__container">
              <ChimeManagement
                v-if="showSettings"
                :chime="chime"
                @update:chime="handleChimeUpdate"
              />
              <ChimeExport v-if="exportPanel" :chime="chime" />
            </div>
          </div>
        </header>

        <Spinner v-if="!isReady" />

        <div v-if="isReady" class="chime__folder-wrapper">
          <div class="chime__folder-list">
            <p v-if="!ordered_folders.length">
              You don't have any folders yet. Why not create one now?
            </p>
            <NewFolder
              class="chime__create-folder"
              :chime="chime"
              @newfolder="create_folder"
            />

            <div class="grid-cols-2">
              <Draggable
                v-if="ordered_folders.length"
                v-model="ordered_folders"
                itemKey="id"
                class="chime__ordered-folders"
                handle=".handle"
                :animation="200"
                :disabled="false"
                ghostClass="ghost"
              >
                <template #item="{ element }">
                  <div>
                    <FolderCard
                      :chime="chime"
                      :folder="element"
                      :showMoveIcon="ordered_folders.length > 1"
                      @change="loadChime"
                    />
                  </div>
                </template>
              </Draggable>
              <JoinPanel :chime="chime" :includeFullUrl="true" />
            </div>
          </div>
        </div>
      </div>
    </div>
  </DefaultLayout>
</template>

<script>
import Draggable from "vuedraggable";
import orderBy from "lodash/orderBy";
import NewFolder from "./NewFolder.vue";
import Spinner from "../../components/Spinner.vue";
import ErrorDialog from "../../components/ErrorDialog.vue";
import ChimeManagement from "./ChimeManagement.vue";
import ChimeExport from "./ChimeExport.vue";
import FolderCard from "./FolderCard.vue";
import Chip from "../../components/Chip.vue";
import JoinPanel from "../../components/JoinPanel.vue";
import {
  selectIsCanvasChime,
  selectCanvasCourseUrl,
} from "../../helpers/chimeSelectors";
import DefaultLayout from "../../layouts/DefaultLayout.vue";
import * as api from "../../common/api";
import Back from "../../components/Back.vue";

export default {
  components: {
    Draggable,
    NewFolder,
    Spinner,
    ErrorDialog,
    ChimeManagement,
    ChimeExport,
    FolderCard,
    Chip,
    DefaultLayout,
    Back,
    JoinPanel,
  },
  props: {
    user: {
      type: Object,
      required: false,
      default: null,
    },
    chimeId: {
      type: Number,
      required: true,
    },
  },
  data() {
    return {
      isReady: false,
      chime: {},
      showSettings: false,
      exportPanel: false,
    };
  },
  computed: {
    ordered_folders: {
      get() {
        if (!this.chime || !this.chime.folders) {
          return [];
        }
        return orderBy(this.chime.folders, ["order", "id"], ["asc", "asc"]);
      },
      set(value) {
        value.map((f, index) => (f.order = index + 1));
        const url = "/api/chime/" + this.chime.id;
        axios
          .put(url, {
            folders: this.chime.folders,
          })
          .then(() => {
            this.chime.folders = value;
          })
          .catch((err) => {
            console.error(err);
          });
      },
    },
    isCanvasChime() {
      return selectIsCanvasChime(this.chime);
    },
    canvasUrl() {
      const fullCanvasUrlString =
        selectCanvasCourseUrl(this.chime) || `https://canvas.umn.edu`;
      return new URL(fullCanvasUrlString);
    },
  },
  async mounted() {
    this.isReady = false;
    await this.loadChime();
    this.isReady = true;
  },
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
    handleChimeUpdate(chimeUpdates) {
      // optimistically update chime
      this.chime = {
        ...this.chime,
        ...chimeUpdates,
      };

      // but load the chime from the server for realsies.
      this.loadChime();
    },
    create_folder: function (folder_name) {
      if (folder_name.length == 0) {
        alert("You must enter a name for this folder.");
        return;
      }

      // prevent duplicate folders from being created
      // in case the network is slow
      if (this.chime.folders.find((f) => f.name === folder_name)) {
        console.error(
          `Folder with the folder name '${folder_name}' already exists.`
        );
        return;
      }

      axios
        .post("/api/chime/" + this.chime.id + "/folder", {
          folder_name: folder_name,
        })
        .then((res) => {
          this.chime.folders.push(res.data);
        })
        .catch((err) => {
          console.error(err);
        });
    },
    async loadChime() {
      try {
        this.chime = await api.getChime(this.chimeId);
        document.title = this.chime.name;
      } catch (err) {
        this.$store.commit(
          "message",
          "Could not load Chime. You may not have permission to view this page. "
        );
        console.error(err);
      }
    },
  },
};
</script>

<style scoped>
.flex {
  display: flex;
  align-items: center;
}

.chime__header-label {
  text-transform: uppercase;
  color: var(--gray-medium);
  margin: 0 0 0.25rem 0;
}
.chime__header {
  margin-bottom: 2rem;
}
.chime-header__canvas-chip {
  margin-bottom: 0.5rem;
}

.chime__header-container {
  display: flex;
  justify-content: space-between;
  align-items: center;
  /* flex-wrap: wrap; */
  gap: 1rem;
}
.chime__name {
  font-size: 2rem;
  margin: 0;
  margin-right: 0.5rem;
}

.chime-page-header__button-group {
  display: flex;
  flex-wrap: wrap;
  box-shadow: 0 1px 2px 0 rgb(0 0 0 / 0.05);
  border-radius: 0.25rem;
  overflow: hidden;
  background: #fff;
  flex-shrink: 0;
}

.chime-page-header__button-group .btn {
  font-size: 0.8rem;
  display: flex;
  align-items: center;
  padding: 0.5rem 1rem;
  border-radius: 0;
  gap: 0.25rem;
}

.chime-page-header__button-group .btn:hover {
  background: #f3f3f3;
  color: #111;
}

.chime__control-buttons .material-icons {
  font-size: 1.25rem;
  margin-right: 0.25rem;
}

.btn.btn--is-active {
  background: #111;
  color: #fff;
}

.btn.btn--is-active:hover {
  background: #333;
  color: #fff;
}

.btn:focus-visible {
  border: 2px solid #007bff;
  border-radius: 0.25rem;
}

.chime-settings-panel {
  display: none;
  margin-top: 1rem;
  padding: 2rem;
  background-color: #fff;
  line-height: 1.5;
  border-radius: 0.25rem;
  overflow: auto;
  box-shadow: 0 1px 3px 0 rgb(0 0 0 / 0.1), 0 1px 2px -1px rgb(0 0 0 / 0.1);
}
.chime-settings-panel--isOpen {
  display: block;
}

.chime-settings-panel__container {
  max-width: 40rem;
}

.chime__create-folder {
  margin: 1rem 0;
}

@media (max-width: 768px) {
  .chime__header-container {
    flex-direction: column;
    align-items: flex-start;
  }

  .chime__control-buttons {
    margin-top: 1rem;
  }
}
</style>

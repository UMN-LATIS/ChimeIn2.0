<template>
  <div class="chime">
    <NavBar title="Back to Home" :user="user" :link="'/'" />
    <ErrorDialog />
    <div class="container">
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
          <h1 class="chime__name">
            {{ chime.name }}
          </h1>
          <div
            class="chime__control-buttons btn-group"
            role="group"
            aria-label="Chime Controls"
            :class="{
              'chime__control-buttons--is-active': showSettings || exportPanel,
            }"
          >
            <button
              class="chime__control-button btn btn-outline-secondary align-items-center d-flex"
              :class="{ 'btn--is-active': showSettings }"
              data-cy="toggle-chime-settings-panel"
              @click="toggle('showSettings', { setToFalse: ['exportPanel'] })"
            >
              <i class="material-icons">settings</i> Chime Settings
            </button>

            <button
              class="chime__control-button btn btn-outline-secondary align-items-center d-flex"
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
          class="chime__settings-panel"
          :class="{
            'chime__settings-panel--isOpen': showSettings || exportPanel,
          }"
        >
          <ChimeManagement
            v-if="showSettings"
            :chime="chime"
            @update:chime="handleChimeUpdate"
          />
          <ChimeExport v-if="exportPanel" :chime="chime" />
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
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import Draggable from "vuedraggable";
import orderBy from "lodash/orderBy";
import NavBar from "../../components/NavBar.vue";
import NewFolder from "./NewFolder.vue";
import Spinner from "../../components/Spinner.vue";
import ErrorDialog from "../../components/ErrorDialog.vue";
import ChimeManagement from "./ChimeManagement.vue";
import ChimeExport from "./ChimeExport.vue";
import FolderCard from "./FolderCard.vue";
import Chip from "../../components/Chip.vue";
import {
  selectIsCanvasChime,
  selectCanvasCourseUrl,
} from "../../helpers/chimeSelectors";
import * as api from "../../common/api";

export default {
  components: {
    Draggable,
    NavBar,
    NewFolder,
    Spinner,
    ErrorDialog,
    ChimeManagement,
    ChimeExport,
    FolderCard,
    Chip,
  },
  props: ["user", "chimeId"],
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
  margin: 2rem 0 2rem;
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
.chime__control-buttons {
  flex-shrink: 0;
}

.chime__control-buttons .material-icons {
  font-size: 1.25rem;
  margin-right: 0.25rem;
}

.chime__control-buttons .btn--is-active {
  background: var(--gray-dark);
  color: #fff;
}

.chime__settings-panel {
  display: none;
  margin-top: 1rem;
  padding: 2rem;
  background-color: #fafafa;
  line-height: 1.5;
  border-radius: 0.25rem;
  overflow: auto;
  border: 1px solid var(--gray-light);
}
.chime__settings-panel--isOpen {
  display: block;
}

.chime__create-folder {
  margin: 1rem 0;
}

@media (max-width: 768px) {
  .chime__header-container {
    display: block;
  }

  .chime__control-buttons {
    margin-top: 1rem;
  }
}
</style>

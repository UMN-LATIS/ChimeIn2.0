<template>
  <DefaultLayout :user="user">
    <template #navbar-left>
      <Back :to="`/chime/${chimeId}`">Back to Chime</Back>
    </template>
    <ErrorDialog />
    <div class="container-fluid">
      <div
        v-if="!hideOpenAlert && otherFolderSessions.length > 0"
        class="alert alert-warning"
        role="alert"
      >
        You have {{ otherFolderSessions.length }}
        {{ pluralize("question", otherFolderSessions.length) }} open outside
        this folder. Would you like to
        <a class="pointer" href="" @click.prevent="closeOthers"
          >close {{ otherFolderSessions.length == 1 ? "it" : "them" }}</a
        >?<a class="float-right pointer" @click="hideOpenAlert = true">X</a>
      </div>
      <Spinner v-if="!isPageReady" />
      <div v-if="isPageReady && !isParticipantView">
        <header class="folder-page-header">
          <div class="folder-page-header__folder-name-group">
            <p class="folder-page-header__chime-name">
              <RouterLink :to="`/chime/${chime.id}`">
                {{ chime.name }}
              </RouterLink>
            </p>
            <h2 class="folder-page-header__folder-name">{{ folder.name }}</h2>
          </div>
          <div class="folder-page-header__controls">
            <div
              class="folder-page-header__button-group"
              role="group"
              aria-label="Folder Controls"
            >
              <button
                class="btn folder-settings-btn"
                :class="{
                  'folder-settings-btn--is-active': show_edit_folder,
                }"
                @click="show_edit_folder = !show_edit_folder"
              >
                <i class="material-icons pointer">edit</i> Folder Settings
              </button>
              <button class="btn" @click="openAll">
                <i class="material-icons pointer">visibility</i> Open All
              </button>
              <button class="btn" @click="closeAll">
                <i class="material-icons pointer">visibility_off</i> Close All
              </button>
              <router-link
                :to="{
                  name: 'chimeStudent',
                  params: { chimeId: chimeId },
                  query: {
                    viewMode: 'participant',
                    callbackUrl: $route.path,
                  },
                }"
                class="btn"
              >
                <i class="material-icons">preview</i>
                Participant View
              </router-link>
              <router-link
                :to="{
                  name: 'present',
                  params: { chimeId: chimeId, folderId: folderId },
                }"
                class="btn"
              >
                <i class="material-icons">play_arrow</i>
                Present
              </router-link>
            </div>
          </div>
        </header>
        <div v-if="show_edit_folder && folder" class="folder-settings-panel">
          <div class="folder-settings-panel__container">
            <h3 class="folder-settings-panel__heading">Folder Settings</h3>
            <div class="row">
              <div class="col-12">
                <div class="input-group mb-3">
                  <input
                    :value="folder.name"
                    type="text"
                    class="form-control"
                    @input="handleFolderNameInput"
                  />

                  <div class="input-group-append">
                    <button
                      class="btn btn-outline-primary align-items-center d-flex"
                      @click="edit_folder"
                    >
                      <span class="material-icons pointer">save</span> Save
                    </button>
                  </div>
                </div>
              </div>
              <div class="ml-auto col-12 btn-toolbar justify-content-end">
                <button
                  v-if="folder.resource_link_pk > 0 || folder.lti_lineitem"
                  class="mr-2 btn btn-success btn-sm align-items-center d-flex"
                  @click="sync"
                >
                  Force Sync with Canvas
                  <span v-if="synced" class="material-icons md-18"
                    >check_circle</span
                  >
                </button>
                <button
                  class="mr-2 btn btn-warning btn-sm align-items-center d-flex"
                  @click="reset"
                >
                  Reset Folder
                </button>
                <button
                  class="btn btn-sm btn-danger align-items-center d-flex"
                  @click="delete_folder"
                >
                  Delete Folder
                  <i class="material-icons pointer md-18">delete</i>
                </button>
              </div>
            </div>

            <fieldset class="form-group border p-2">
              <legend class="col-form-label w-auto">Import Questions</legend>
              <div class="row">
                <div class="col-sm-12">
                  <div class="form-group">
                    <label for="chime_select">Select a Chime:</label>
                    <select
                      id="chime_select"
                      v-model="selected_chime"
                      class="form-control"
                      @change="update_folders"
                    >
                      <option disabled>Select a Chime</option>
                      <option
                        v-for="c in existing_chimes"
                        :key="c.id"
                        :value="c.id"
                      >
                        {{ c.name }}
                      </option>
                    </select>
                  </div>

                  <div class="form-group">
                    <label for="folder_select">Select a Folder:</label>
                    <select
                      id="folder_select"
                      v-model="selected_folder"
                      class="form-control"
                    >
                      <option disabled>Select a Folder</option>
                      <option
                        v-for="f in existing_folders"
                        :key="f.id"
                        :value="f.id"
                      >
                        {{ f.name }}
                      </option>
                    </select>
                  </div>
                  <button class="btn btn-primary" @click="do_import">
                    Import
                  </button>
                </div>
              </div>
            </fieldset>
          </div>
        </div>

        <div class="border-top mt-3 pt-3 folder-page__main">
          <div>
            <button
              data-cy="new-question-button"
              class="btn btn-outline-primary align-items-center d-flex my-2"
              @click="showModal = true"
            >
              <i class="material-icons pointer">add</i> Add Question
            </button>
            <div class="grid-cols-2">
              <Draggable
                v-model="questions"
                itemKey="id"
                data-cy="question-list"
                class="question-list"
                handle=".handle"
                ghostClass="ghost"
                @end="swap_question"
              >
                <template #item="{ element }">
                  <QuestionCard
                    :folder="folder"
                    :question="element"
                    :showMoveIcon="questions.length > 1"
                    @change="refreshFolder"
                  />
                </template>
              </Draggable>
              <JoinPanel :chime="chime" :includeFullUrl="true" />
            </div>
          </div>
        </div>
      </div>
      <QuestionForm
        v-if="showModal"
        :show="showModal"
        :question="{
          text: '',
          question_info: {
            question_type: 'multiple_choice',
            question_responses: null,
          },
        }"
        :folder="folder"
        :chimeId="chimeId"
        controlType="create"
        @close="
          showModal = false;
          refreshFolder();
        "
      />
    </div>
  </DefaultLayout>
</template>

<script setup>
import orderBy from "lodash/orderBy";
import { defineAsyncComponent, ref, computed, watch, onMounted } from "vue";
import Draggable from "vuedraggable";
import ErrorDialog from "../../components/ErrorDialog.vue";
import QuestionCard from "./QuestionCard.vue";
import Back from "../../components/Back.vue";
import Spinner from "../../components/Spinner.vue";
import pluralize from "../../common/pluralize.js";
import useQuestionListener from "../../hooks/useQuestionListener";
import { useStore } from "vuex";
import DefaultLayout from "../../layouts/DefaultLayout.vue";
import JoinPanel from "../../components/JoinPanel.vue";
import {
  getChimes,
  getOpenSessionsWithinChime,
  removeResponsesForQuestion,
  updateFolder,
  updateQuestionOrderInFolder,
  openAllQuestionsInFolder,
  closeAllQuestionsInFolder,
  closeQuestion,
  deleteFolder,
  importFolder,
  forceSyncGradesWithLMS,
} from "../../common/api";
import { useRouter } from "vue-router";
const QuestionForm = defineAsyncComponent(() =>
  import(
    /* webpackChunkName: "QuestionForm" */
    "../QuestionForm/QuestionForm.vue"
  )
);

const props = defineProps({
  folderId: { type: Number, required: true },
  chimeId: { type: Number, required: true },
  user: { type: Object, required: true },
});
const showModal = ref(false);
const show_edit_folder = ref(false);
const allSessions = ref(null);
const hideOpenAlert = ref(false);
const existing_chimes = ref([]);
const existing_folders = ref([]);
const selected_chime = ref(null);
const selected_folder = ref(null);
const synced = ref(false);

const store = useStore();
const router = useRouter();
const {
  chime,
  folder,
  questions,
  refresh: refreshFolder,
} = useQuestionListener({
  chimeId: props.chimeId,
  folderId: props.folderId,
});

const otherFolderSessions = computed(() => {
  if (allSessions.value && folder.value) {
    return allSessions.value.filter(
      (session) => session.question.folder_id !== folder.value.id
    );
  }
  return [];
});

// each time we open the edit folder
// load the existing chimes so that the user
// can import questions from another chime
// if they wish
watch(show_edit_folder, function (newValue) {
  if (newValue) {
    loadExistingChimes();
  }
});

const isPageReady = computed(() => !!folder.value);
const isParticipantView = computed(() => folder.value?.student_view ?? false);
onMounted(async () => {
  if (isParticipantView.value) {
    store.commit("message", "Unauthorized: Only presenters may edit chimes.");
    console.error(
      `User ${JSON.stringify(props.user)} is not a presenter for this chime.`
    );
    return;
  }

  allSessions.value = await getOpenSessionsWithinChime(props.chimeId);
});

function reset() {
  if (
    confirm(
      "Are you sure you want to wipe all the responses to questions in this folder?"
    )
  ) {
    const promises = questions.value.map((question) =>
      removeResponsesForQuestion({
        chimeId: props.chimeId,
        folderId: props.folderId,
        questionId: question.id,
      })
    );
    Promise.all(promises)
      .then(() => refreshFolder())
      .catch(console.error);
  }
}
async function swap_question() {
  const updatedOrder = questions.value.map((question, index) => ({
    order: index + 1,
    id: question.id,
  }));
  await updateQuestionOrderInFolder(
    {
      chimeId: props.chimeId,
      folderId: props.folderId,
    },
    updatedOrder
  );

  refreshFolder();
}

async function loadExistingChimes() {
  existing_chimes.value = await getChimes();
}

async function edit_folder() {
  if (!folder.value) return;
  await updateFolder(
    { chimeId: props.chimeId, folderId: props.folderId },
    {
      folder_name: folder.value.name,
    }
  );
  show_edit_folder.value = false;
}

async function delete_folder() {
  if (!confirm("Delete Folder " + folder.value.name + "?")) return;

  await deleteFolder({ chimeId: props.chimeId, folderId: props.folderId });
  router.push({
    name: "chime",
    params: {
      chimeId: props.chimeId,
    },
  });
}

function openAll() {
  return openAllQuestionsInFolder({
    chimeId: props.chimeId,
    folderId: props.folderId,
  });
}

function closeAll() {
  return closeAllQuestionsInFolder({
    chimeId: props.chimeId,
    folderId: props.folderId,
  });
}

function closeOthers() {
  hideOpenAlert.value = true;
  const promises = otherFolderSessions.value.map((openSession) =>
    closeQuestion({
      chimeId: props.chimeId,
      folderId: openSession.question.folder_id,
      questionId: openSession.question.id,
    })
  );
  Promise.all(promises).catch((err) => {
    hideOpenAlert.value = true;
    console.error(err);
  });
}

function handleFolderNameInput(event) {
  folder.value.name = event.target.value;
}

async function do_import() {
  if (!selected_chime.value || !selected_folder.value) return;
  await importFolder({
    destinationChimeId: props.chimeId,
    destinationFolderId: props.folderId,
    sourceFolderId: selected_folder.value,
  });
  refreshFolder();
}

function update_folders() {
  axios
    .get("/api/chime/" + selected_chime.value)
    .then((res) => {
      const foldersWithoutCurrentOne = res.data.folders.filter(
        (f) => f.id !== props.folderId
      );

      existing_folders.value = orderBy(foldersWithoutCurrentOne, "created_at", [
        "desc",
      ]);
    })
    .catch((err) => {
      console.error("error", "Error in get chimes:", err.response);
    });
}

async function sync() {
  synced.value = await forceSyncGradesWithLMS({
    chimeId: props.chimeId,
    folderId: props.folderId,
  });

  if (!synced.value) {
    store.commit(
      "message",
      "Could not sync Chime. Please contact support at latistecharch@umn.edu."
    );
  }
}
</script>

<style>
.pointer {
  cursor: pointer;
}
</style>
<style scoped>
ul li {
  list-style: none;
}

.align-items-center h4 {
  margin-bottom: 0;
}
.material-icons {
  font-size: 1.25rem;
  margin-right: 0.25rem;
}

@media (min-width: 50rem) {
  .grid-cols-2 {
    display: grid;
    grid-template-columns: 3fr 1fr;
    gap: 1.5rem;
    align-items: flex-start;
  }
}

.folder-page-header {
  /* border: 1px solid red; */
  display: flex;
  flex-wrap: wrap;
  gap: 1rem;
  justify-content: space-between;
  align-items: flex-end;
  margin-top: 1rem;
}
.folder-page-header__folder-name-group {
  margin-top: 1rem;
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.folder-page-header__folder-name,
.folder-page-header__chime-name {
  margin: 0;
}

.folder-page-header__chime-name {
  text-transform: uppercase;
  font-size: 0.8rem;
}

.folder-page-header__chime-name a {
  color: var(--dark-gray);
}

.folder-page-header__button-group {
  display: flex;
  flex-wrap: wrap;
  box-shadow: 0 1px 2px 0 rgb(0 0 0 / 0.05);
  border-radius: 0.25rem;
  overflow: hidden;
  background: #fff;
}

.folder-page-header__button-group .btn {
  font-size: 0.8rem;
  display: flex;
  align-items: center;
  padding: 0.5rem 1rem;
  border-radius: 0;
}

.folder-page-header__button-group .btn:hover {
  background: #f3f3f3;
  color: #111;
}

.folder-settings-panel {
  margin-top: 1rem;
  padding: 2rem;
  background-color: #fff;
  line-height: 1.5;
  border-radius: 0.25rem;
  box-shadow: 0 1px 3px 0 rgb(0 0 0 / 0.1), 0 1px 2px -1px rgb(0 0 0 / 0.1);
}

.folder-settings-panel__heading {
  font-size: 1.25rem;
}

.folder-settings-panel__container {
  max-width: 40rem;
}

.folder-settings-btn.folder-settings-btn--is-active {
  background: #111;
  color: #fff;
}

.folder-settings-btn.folder-settings-btn--is-active:hover {
  background: #333;
  color: #fff;
}
</style>

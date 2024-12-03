<template>
  <div class="chime-management">
    <h2 class="chime-management__heading">Chime Settings</h2>
    <div class="chime-management__contents">
      <div class="form-group">
        <label class="sr-only" for="chimeName"
          ><strong>Chime Name:</strong></label
        >
        <div class="input-group">
          <input
            id="chimeName"
            v-model="chimeName"
            type="text"
            class="form-control"
            data-cy="chime-name-input"
          />
          <div class="input-group-append">
            <button
              class="btn btn-outline-primary align-items-center d-flex btn-sm"
              data-cy="save-chime-name-button"
              @click="saveChime()"
            >
              <span class="material-icons pointer md-18">save</span> Update
              Chime Name
            </button>
          </div>
        </div>
      </div>

      <div class="row">
        <div class="col-sm-12">
          <JoinPanel
            class="chime-management__join-panel"
            :chime="chime"
            :includeFullUrl="true"
          />
          <ChimeManagementOptions
            :require_login="chime.require_login"
            :students_can_view="chime.students_can_view"
            :join_instructions="chime.join_instructions"
            :only_correct_answers_lti="chime.only_correct_answers_lti"
            :show_folder_title_to_participants="
              chime.show_folder_title_to_participants
            "
            @update="handleUpdateChimeOptions"
          />
          <button
            v-if="isCanvasChime && chime.lti_grade_mode === 'one_grade'"
            class="btn btn-outline-success btn-sm align-items-center d-flex"
            @click="forceSyncGrades"
          >
            Force Sync with Canvas
            <span
              v-if="forceSyncState === 'success'"
              class="material-icons sync-status sync-status--is-success md-18"
              >check_circle</span
            >
            <span
              v-if="forceSyncState === 'inProgress'"
              class="material-icons sync-status sync-status--is-inprogress md-18"
              >sync</span
            >
          </button>
          <div
            v-if="forceSyncState === 'error'"
            class="sync-status sync-status--is-error"
          >
            <span class="material-icons md-18">error</span>
            Error syncing grades with Canvas
          </div>
        </div>
      </div>
      <hr />

      <div class="row" data-cy="chime-users-list">
        <div class="col-sm-12">
          <h4>Users</h4>
          <table class="table">
            <thead>
              <tr>
                <th>Name</th>
                <th>Email</th>
                <th>Permission</th>
                <th>Delete</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="(user, index) in sortedUsers" :key="index">
                <td>{{ user.name }}</td>
                <td>{{ user.email }}</td>
                <td data-cy="select-user-permissions-in-chime">
                  <select
                    v-model="user.permission_number"
                    class="form-control form-control-sm"
                    @change="updateChimeUserPermissions(user.id, user.permission_number)"
                  >
                    <option value="100">Participant</option>
                    <option value="300">Presenter</option>
                  </select>
                </td>
                <td>
                  <button
                    data-cy="remove-user-from-chime-button"
                    class="btn btn-sm btn-danger"
                    @click="removeChimeUser(user.id)"
                  >
                    Remove User
                  </button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed, onMounted, ref } from "vue";
import * as api from "../../common/api";
import ChimeManagementOptions from "../../components/ChimeManagementOptions.vue";
import JoinPanel from "../../components/JoinPanel.vue";
import { useStore } from "vuex";
import { selectIsCanvasChime } from "../../helpers/chimeSelectors";
import type { Chime, ChimeOptions, User, Partial } from "../../types";

const props = defineProps<{
  chime: Chime;
}>();

const emit = defineEmits<{
  (event: "update:chime", chimeUpdates: Partial<Chime>);
}>();

const users = ref<User[]>([]);
const chimeName = ref<string>(props.chime.name);
const store = useStore();

const isCanvasChime = computed((): boolean => selectIsCanvasChime(props.chime));

type ForceSyncState = "idle" | "inProgress" | "success" | "error";
const forceSyncState = ref<ForceSyncState>("idle");

const sortedUsers = computed(() =>
  [...users.value].sort((a, b) => {
    // first sort by permission
    const n = b.permission_number - a.permission_number;
    if (n !== 0) {
      return n;
    }

    // then sort by email
    if (a.email < b.email) {
      return -1;
    }
    if (a.email > b.email) {
      return 1;
    }

    return 0;
  })
);

function handleUpdateChimeOptions(updatedOption: Partial<ChimeOptions>) {
  saveChime(updatedOption);
}

async function saveChime(
  updates: Partial<ChimeOptions & { name: string }> = {}
) {
  const {
    require_login,
    students_can_view,
    join_instructions,
    only_correct_answers_lti,
    show_folder_title_to_participants,
  } = props.chime;

  try {
    const updatedChime = {
      name: chimeName.value,
      require_login,
      students_can_view,
      join_instructions,
      only_correct_answers_lti,
      show_folder_title_to_participants,
      ...updates,
    };
    await api.updateChimeOptions(props.chime.id, updatedChime);
    emit("update:chime", updatedChime);
  } catch (err) {
    store.commit("message", "Could not save Chime.");
    console.error(err);
  }
}

async function updateChimeUserPermissions(userId: number, permissionNumber: number) {
  try {
    api.updateChimeUserPermissions({
      chimeId: props.chime.id,
      userId,
      permissionNumber,
    })
  } catch (err) {
    store.commit("message", "Could not update user permissions.");
  }
}

async function removeChimeUser(userId) {
  if (!confirm("Are you sure you want to remove this user?")) return;
  const initialUsers = users.value;
  users.value = users.value.filter((u) => u.id !== userId);

  try {
    api.removeChimeUser({
      chimeId: props.chime.id,
      userId,
    });
  } catch (err) {
    users.value = initialUsers;
    store.commit("message", "Could not remove user.");
  }
}

async function forceSyncGrades() {
  forceSyncState.value = "inProgress";
  const response = await api.forceSyncGradesWithLMS({
    chimeId: props.chime.id,
  });

  forceSyncState.value = response ? "success" : "error";

  if (forceSyncState.value === "error") {
    store.commit(
      "message",
      "Could not sync Chime. Please contact support at latistecharch@umn.edu."
    );
  }
}

onMounted(async () => {
  users.value = await api.getChimeUsers(props.chime.id);
});
</script>

<style scoped>
.chime-management {
  padding: 1rem;
}
.chime-management__heading {
  font-size: 1.5rem;
  margin-bottom: 1rem;
}

ul li {
  list-style: none;
}
ul {
  padding: 0;
}

.clickToChange {
  cursor: pointer;
}

.chime-management__join-panel {
  margin-bottom: 1rem;
}

.sync-status {
  display: flex;
  align-items: center;
}

.sync-status--is-inprogress {
  animation: 1s infinite spin;
}
.sync-status--is-error {
  margin: 0.5rem 0;
  color: #dc3545;
  font-size: 0.9rem;
}

@keyframes spin {
  from {
    transform: rotate(360deg);
  }
  to {
    transform: rotate(0deg);
  }
}
</style>

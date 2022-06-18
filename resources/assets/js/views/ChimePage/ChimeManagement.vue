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
            v-if="
              chime.resource_link_pk > 0 || chime.lti13_resource_link_id > 0
            "
            class="btn btn-outline-success btn-sm align-items-center d-flex"
            @click="forceSyncGrades"
          >
            Force Sync with Canvas
            <span v-if="isForceSyncSuccessful" class="material-icons md-18"
              >check_circle</span
            >
          </button>
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
              <tr v-for="(u, index) in sortedUsers" :key="index">
                <td>{{ u.name }}</td>
                <td>{{ u.email }}</td>
                <td data-cy="select-user-permissions-in-chime">
                  <template v-if="u.editPermission">
                    <select
                      v-model="u.permission_number"
                      class="form-control form-control-sm"
                      @change="saveUsers"
                    >
                      <option value="100">Participant</option>
                      <option value="300">Presenter</option>
                    </select>
                  </template>
                  <span
                    v-else
                    class="clickToChange"
                    @click="u.editPermission = !u.editPermission"
                  >
                    <template v-if="u.permission_number == 300"
                      >Presenter</template
                    >
                    <template v-if="u.permission_number == 100"
                      >Participant</template
                    >
                  </span>
                </td>
                <td>
                  <button
                    data-cy="remove-user-from-chime-button"
                    class="btn btn-sm btn-danger"
                    @click="deleteUser(index)"
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
import type { Chime, ChimeOptions, User, Partial } from "../../types";

interface Props {
  chime: Chime;
}

const props = defineProps<Props>();

interface Emits {
  (event: "update:chime", chimeUpdates: Partial<Chime>);
}

const emit = defineEmits<Emits>();

const users = ref<User[]>([]);
const chimeName = ref<string>(props.chime.name);
const store = useStore();

// successful force sync
const isForceSyncSuccessful = ref<boolean>(false);

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

async function saveUsers() {
  try {
    await api.updateChimeUsers(props.chime.id, users.value);
    users.value = await api.getChimeUsers(props.chime.id);
  } catch (err) {
    console.error(err);
  }
}

function deleteUser(index) {
  if (!confirm("Are you sure you want to remove this user?")) return;
  users.value.splice(index, 1);
  saveUsers();
}

async function forceSyncGrades() {
  isForceSyncSuccessful.value = await api.forceSyncGradesWithLMS({
    chimeId: props.chime.id,
  });
  if (!isForceSyncSuccessful.value) {
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
</style>

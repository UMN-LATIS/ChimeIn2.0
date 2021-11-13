<template>
  <div class="chime-management">
    <h2 class="chime-management__heading">Chime Settings</h2>
    <div class="form-group">
      <label class="sr-only" for="chimeName"
        ><strong>Chime Name:</strong></label
      >
      <div class="input-group">
        <input
          type="text"
          class="form-control"
          id="chimeName"
          v-model="chime_name"
          data-cy="chime-name-input"
        />
        <div class="input-group-append">
          <button
            class="btn btn-outline-primary align-items-center d-flex btn-sm"
            @click="saveChime"
            data-cy="save-chime-name-button"
          >
            <span class="material-icons pointer md-18">save</span> Update Chime
            Name
          </button>
        </div>
      </div>
    </div>

    <div class="row">
      <div class="col-sm-12">
        <ul>
          <li>
            <strong>Access Code:</strong>
            {{ hyphenatedCode }}
          </li>
          <li>
            <strong>Participants can join by visiting:</strong>
            <a v-bind:href="join_url">{{ join_url }}</a>
          </li>
        </ul>
        <ChimeManagementOptions
          :require_login.sync="require_login"
          :students_can_view.sync="students_can_view"
          :join_instructions.sync="join_instructions"
          :only_correct_answers_lti.sync="only_correct_answers_lti"
          :show_folder_title_to_participants.sync="
            show_folder_title_to_participants
          "
        />
        <button
          class="btn btn-outline-success btn-sm align-items-center d-flex"
          @click="sync"
          v-if="chime.resource_link_pk"
        >
          Force Sync with Canvas
          <span class="material-icons md-18" v-if="synced">check_circle</span>
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
            <tr v-for="(u, key) in sorted_users" :key="key">
              <td>{{ u.name }}</td>
              <td>{{ u.email }}</td>
              <td data-cy="select-user-permissions-in-chime">
                <template v-if="u.editPermission">
                  <select
                    class="form-control form-control-sm"
                    v-model="u.permission_number"
                    @change="saveUsers"
                  >
                    <option value="100">Participant</option>
                    <option value="300">Presenter</option>
                  </select>
                </template>
                <span
                  v-on:click="u.editPermission = !u.editPermission"
                  v-else
                  class="clickToChange"
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
                  @click="deleteUser(key)"
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
</template>

<style scoped>
.chime-management {
  padding: 1rem;
  max-width: 50rem;
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
</style>

<script>
import ChimeManagementOptions from "../../components/ChimeManagementOptions.vue";

export default {
  props: ["chime"],
  components: {
    ChimeManagementOptions,
  },
  data: function() {
    return {
      users: [],
      chime_name: this.chime.name,
      join_instructions: this.chime.join_instructions,
      students_can_view: this.chime.students_can_view,
      require_login: this.chime.require_login,
      only_correct_answers_lti: this.chime.only_correct_answers_lti,
      show_folder_title_to_participants: this.chime
        .show_folder_title_to_participants,
      synced: false,
    };
  },
  watch: {
    join_instructions: function(val) {
      this.saveChime();
    },
    students_can_view: function(val) {
      this.saveChime();
    },
    require_login: function(val) {
      this.saveChime();
    },
    only_correct_answers_lti: function(val) {
      this.saveChime();
    },
    show_folder_title_to_participants: function(val) {
      this.saveChime();
    },
  },
  computed: {
    join_url: function() {
      return (
        window.location.protocol +
        "//" +
        window.location.host +
        "/join/" +
        this.chime.access_code
      );
    },
    sorted_users: function() {
      return this.users.sort((a, b) => {
        var n = b.permission_number - a.permission_number;
        if (n !== 0) {
          return n;
        }
        if (a.email < b.email) {
          return -1;
        }
        if (a.email > b.email) {
          return 1;
        }
        return 0;
      });
    },
    hyphenatedCode: function() {
      return this.chime.access_code.replace(/(\d{3})(\d{3})/, "$1-$2");
    },
  },
  methods: {
    saveChime: function() {
      var localChime = {
        ...this.chime,
      };
      localChime.join_instructions = this.join_instructions;
      localChime.students_can_view = this.students_can_view;
      localChime.require_login = this.require_login;
      localChime.only_correct_answers_lti = this.only_correct_answers_lti;
      localChime.show_folder_title_to_participants = this.show_folder_title_to_participants;
      localChime.name = this.chime_name;
      axios
        .patch("/api/chime/" + this.chime.id, localChime)
        .then((res) => {
          this.$emit("update:chime", localChime);
        })
        .catch((err) => {
          console.log(err.response);
        });
    },
    deleteUser: function(key) {
      if (confirm("Are you sure you want to remove this user?")) {
        this.$delete(this.users, key);
        this.saveUsers();
      }
    },
    saveUsers: function() {
      const url = "/api/chime/" + this.chime.id + "/users";

      axios
        .put(url, {
          users: this.users,
        })
        .then((res) => {
          console.log(res);
          this.loadUsers();
        })
        .catch((err) => {
          console.error(err.response);
        });
    },
    loadUsers: function() {
      const url = "/api/chime/" + this.chime.id + "/users";
      axios
        .get(url)
        .then((res) => {
          this.users = res.data;
        })
        .catch((err) => {
          console.log(err);
        });
    },
    sync: function() {
      axios
        .post("/api/chime/" + this.chime.id + "/sync")
        .then((res) => {
          if (res.data.success) {
            this.synced = true;
          }
        })
        .catch((err) => {
          this.$store.commit(
            "message",
            "Could not sync Chime. Please contact support at latis@umn.edu."
          );
          console.log(err);
        });
    },
  },
  mounted() {
    this.loadUsers();
  },
};
</script>

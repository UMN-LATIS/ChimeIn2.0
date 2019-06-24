<template>
  <div>
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
          <li>
            <div class="form-check">
              <input
                class="form-check-input"
                type="checkbox"
                id="requireLogin"
                v-model="requireLogin"
              >
              <label class="form-check-label" for="requireLogin">Require Login to Join or Access</label>
            </div>
          </li>
          <li>
            <div class="form-check">
              <input
                class="form-check-input"
                type="checkbox"
                id="studentView"
                v-model="studentsCanView"
              >
              <label class="form-check-label" for="studentView">Students can view results</label>
            </div>
          </li>
        </ul>
      </div>
    </div>
    <hr>

    <div class="row">
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
              <td>
                <template v-if="u.editPermission">
                  <b-form-select
                    v-model="u.permission_number"
                    :options="[
                            { value: 100, text: 'Participant' },
                            { value: 300, text: 'Presenter' }]"
                    class="mb-3"
                    @input="saveUsers"
                  />
                </template>
                <span v-on:click="u.editPermission = !u.editPermission" v-else>
                  <template v-if="u.permission_number == 300">Presenter</template>
                  <template v-if="u.permission_number == 100">Participant</template>
                </span>
              </td>
              <td>
                <button class="btn btn-sm btn-primary" @click="deleteUser(key)">Remove User</button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</template>

<style scoped>
ul li {
  list-style: none;
}
</style>

<script>
export default {
  props: ["chime"],
  data: function() {
    return {
      requireLogin: 0,
      studentsCanView: 0,
      users: []
    };
  },
  watch: {
    requireLogin: function(newValue, oldValue) {
      if (newValue !== this.chime.require_login) {
        this.$emit("requireLoginChange", newValue);
      }
    },
    studentsCanView: function(newValue, oldValue) {
      if (newValue !== this.chime.students_can_view) {
        this.$emit("studentsCanViewChange", newValue);
      }
    }
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
    hyphenatedCode: function () {
      return this.chime.access_code.replace(/(\d{3})(\d{3})/, "$1-$2");
    }
  },
  methods: {
    deleteUser: function(key) {
      if (confirm("Are you sure you want to remove this user?")) {
        this.$delete(this.users, key);
        this.saveUsers();
      }
    },
    saveUsers: function() {
      const url = "/api/chime/" + this.chime.id + "/users";

      axios
        .put(url, { users: this.users })
        .then(res => {
          console.log(res);
          this.loadUsers();
        })
        .catch(err => {
          console.error(err.response);
        });
    },
    loadUsers: function() {
      const url = "/api/chime/" + this.chime.id + "/users";
      axios
        .get(url)
        .then(res => {
          this.users = res.data;
        })
        .catch(err => {
          console.log(err);
        });
    }
  },
  mounted() {
    this.requireLogin = this.chime.require_login;
    this.studentsCanView = this.chime.students_can_view;
    this.loadUsers();
  }
};
</script>

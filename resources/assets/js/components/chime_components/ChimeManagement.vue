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
                v-model="localChime.require_login"
                @change="saveChime(); $emit('update:chime', localChime);"
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
                v-model="localChime.students_can_view"
                @change="saveChime(); $emit('update:chime', localChime);"
              >
              <label class="form-check-label" for="studentView">Participants can view results</label>
            </div>
          </li>
           <li>
            <div class="form-check">
              <input
                class="form-check-input"
                type="checkbox"
                id="joinInstructions"
                v-model="localChime.join_instructions"
                @change="saveChime(); $emit('update:chime', localChime);"
              >
              <label class="form-check-label" for="joinInstructions">Display "join" instructions when presenting</label>
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
                  <select class="form-control form-control-sm" v-model="u.permission_number"  @change="saveUsers">
                    <option value="100">Participant</option>
                    <option value="300">Presenter</option>
                    </select>
                </template>
                <span v-on:click="u.editPermission = !u.editPermission" v-else class="clickToChange">
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
.clickToChange {
  cursor: pointer;
}
</style>

<script>
export default {
  props: ["chime"],
  data: function() {
    return {
      users: [],
      localChime: { ...this.chime }
    };
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
    saveChime: function() {
        axios.patch('/api/chime/' + this.chime.id, this.localChime)
        .then(res => {
            this.reloadChime();
        })
        .catch(err => {
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
    this.loadUsers();
  }
};
</script>

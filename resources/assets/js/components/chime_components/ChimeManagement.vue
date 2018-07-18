<template>
    <div>
        <b-row>
            <b-col>
                <ul>
                    <li>Access Code: {{ chime.access_code }}</li>
                    <li>Participants can join by visiting: <a v-bind:href="join_url">{{ join_url }}</a></li>
                    <li> <b-form-checkbox v-on:change="requireLoginChange" v-model="requireLogin" :value="1" :unchecked-value="0">
            Require Login
        </b-form-checkbox></li>
                </ul>
            </b-col>
        </b-row>
        
        <b-row>
            <h3>Users</h3>
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
                        <tr v-for="(u, key) in sorted_users">
                            <td>{{ u.name }}</td>
                            <td>{{ u.email }}</td>
                            <td >
                                <template v-if="u.editPermission">
                                    <b-form-select v-model="u.permission_number" :options="[
        { value: 100, text: 'Student' },
        { value: 300, text: 'Instructor' }]" class="mb-3" @input="saveUsers"/>
                                </template>
                                <span v-on:click="u.editPermission = !u.editPermission" v-else>
                                    <template  v-if="u.permission_number == 300">Instructor</template>
                                    <template  v-if="u.permission_number == 100">Student</template>
                                </span>
                            </td>
                            <td><b-btn @click="deleteUser(key)" size="sm">Remove User</b-btn></td>
                        </tr>
                    </tbody>
                </table>
            </a>
        </b-row>
    </div>
</template>

<script>
export default {
    props: ['chime'],
    data: function() {
        return {
            requireLogin: 0,
            users: [],
        }
    },
    computed: {
        join_url: function() {
            return window.location.protocol + "//" + window.location.host + "/join/" + this.chime.access_code;
        },
        sorted_users: function() {
            return this.users.sort((a, b) => { 
                var n =  b.permission_number - a.permission_number;
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
        }
    },
    methods: {
        deleteUser: function(key) {
            if(confirm("Are you sure you want to remove this user?")) {
                this.$delete(this.users, key);
                this.saveUsers();
            }
        },
        requireLoginChange: function(newValue) {
            this.$emit('requireLoginChange', newValue);
        },
        saveUsers: function() {

            const url = (
                '/api/chime/' + this.chime.id + '/users');


            axios.put(url, { users: this.users })
            .then(res => {
                console.log(res);
                this.loadUsers();
            })
            .catch(err => {
                console.error(err.response);
            });
        },
        loadUsers: function() {
            const url = (
                '/api/chime/' + this.chime.id + '/users');
            axios.get(url)
            .then(res => {
                this.users = res.data;
            })
            .catch(err => {
                console.log(err);
            });

        }
    },
    watch: {
       chime: function() {
            this.requireLogin = this.chime.require_login;
            this.loadUsers();
       }
    }

}
</script>

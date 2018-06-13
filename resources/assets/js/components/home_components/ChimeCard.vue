<template>
    <div
        class="card hoverable">
        <div
            class="card-body"
            v-on:click="open_chime(chime.id)"
            v-bind:key="chime.name">
            <h2 class="card-title">{{ chime.name }}</h2>
        </div>
        <div class="card-body" v-bind:key="chime.id">
            <div v-if="show_users">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Permission Number</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="u in users" :key="u.id">
                            <td>{{ u.name }}</td>
                            <td>{{ u.email }}</td>
                            <td v-if="editing_permission.id == u.id">
                                <input
                                    v-model="editing_permission.permission_number"
                                    class="center-align"
                                    type="text"
                                    @keyup.esc="toggle_permission()"
                                    @keyup.enter="save_permission">
                            </td>
                            <td
                                v-else
                                v-on:click="toggle_permission(u.id, u.permission_number)">
                                {{ u.permission_number }}
                            </td>
                            <td
                                v-if="editing_permission.id == u.id"
                                class="action-row"
                                v-on:click="toggle_permission()">
                                <i class="material-icons right">clear</i>
                            </td>
                            <td v-else v-on:click="remove_user(u.id)">
                                <i class="material-icons right">delete</i>
                            </td>
                        </tr>
                    </tbody>
                </table>

                <input
                    id="new_member_input"
                    v-if="!editing_permission.id"
                    v-model="new_member_email"
                    type="text"
                    @keyup.enter="add_user">
                <label for="new_member_input" v-if="!editing_permission.id">
                    New Member Email
                </label>
            </div>
            <a href="#" v-on:click="delete_chime(chime)">
                <i class="material-icons right">delete</i>
            </a>
            <a href="#" v-on:click="toggle_users(chime)" v-if="user.permission_number >= 300">
                <i class="material-icons right">people</i>
            </a>
            <a
                href="#"
                v-on:click="general_save"
                v-if="editing_permission.id || this.new_member_email">
                <i class="material-icons right">save</i>
            </a>
        </div>
    </div>
</template>

<script>
export default {
    props: ['chime', 'user'],
    data: function() {
        return {
            show_users: false,
            users: [],
            editing_permission: {
                id: null,
                permission_number: null
            },
            new_member_email: ''
        }
    },
    methods: {
        toggle_users(chime) {
            this.show_users = (this.show_users ? false : true);

            const self = this;

            axios.get('/api/chime/' + this.chime.id + '/users')
            .then(res => {
                console.log(res);
                self.users = res.data.users;
            })
            .catch(err => {
                console.log(err);
            })
        },
        toggle_permission(user_id, permission_number) {
            this.editing_permission = {
                id: user_id,
                permission_number: permission_number
            }
        },
        save_permission() {
            const self = this;

            axios.put('/api/chime/' + this.chime.id + '/users/' + this.editing_permission.id, {
                permission_number: this.editing_permission.permission_number
            })
            .then(res => {
                console.log(res);

                const userIndex = self.users.findIndex(u => {
                    return u.id === res.data.id;
                });

                self.users.splice(userIndex, 1, res.data);

                this.toggle_permission();
            })
            .catch(err => {
                console.error(err.response);
            });
        },
        general_save() {
            if (this.editing_permission.id) {
                return this.save_permission();
            } else {
                this.add_user();
            }
        },
        add_user() {
            const self = this;

            axios.post('/api/chime/' + this.chime.id + '/users', {
                email: this.new_member_email
            })
            .then(res => {
                console.log(res);
                self.new_member_email = '';
                self.users.push(res.data.new_user);
            })
            .catch(err => {
                console.error(err);
            });
        },
        remove_user(uid) {
            const self = this;

            axios.delete('/api/chime/' + this.chime.id + '/users/' + uid)
            .then(res => {
                console.log(res);
                const i = self.users.findIndex(u => u.id === uid);
                self.users.splice(i, 1);
            })
            .catch(err => {
                console.error(err.response);
            });
        },
        open_chime() {
            window.location.href = '/chime/' + this.chime.id;
        },
        delete_chime(chime) {
            this.$emit('deletechime', chime);
        }
    }
}
</script>

<style>
    th, td {
        text-align: center;
    }

    .action-column {
        width: 20px;
    }

    .material-icons {
        color: #ffd204;
    }
</style>

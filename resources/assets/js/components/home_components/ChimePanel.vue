<template>
<div>
    <div class="row">
        <div class="col-12">
            <p>You have access to {{ chimes.length }} chimes</p>
            <button dusk="add-chime-button" class="btn btn-outline-primary align-items-center d-flex"
                    @click="showAdd = !showAdd" v-if="!user.guest_user"><span class="material-icons">add</span>Add a
                    Chime</button>
            <transition name="fade">
                <div class="card" v-if="!user.guest_user && showAdd">
                    <div class="card-body">
                        <div class="form-group row">
                            <label for="chime_name_input" class="col-sm-2 col-form-label">Chime Name</label>
                            <div class="col-sm-8">
                                <input id="chime_name_input" class="form-control" type="text"
                                        v-model="chime_name" />
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-sm-8 form-group">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="requireLogin"
                                            v-model="requireLogin">
                                        <label class="form-check-label" for="requireLogin">Require Login to Join or
                                            Access</label>
                                    </div>
                                </div>
                                </div>
                                <div class="row">
                                    <div class="col-sm-8 form-group">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="studentView"
                                            v-model="studentsCanView">
                                        <label class="form-check-label" for="studentView">Participants can view
                                            results</label>
                                    </div>
                                    </div>
                                </div>
                            <div class="row">
                                <div class="col">

                                    <button dusk="create-chime-button" class="btn btn-primary" v-on:click="create_chime"
                                        type="button">
                                        Create
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
            </transition>
            <div v-if="chimes.length > 0">
                <transition-group name="fade">
                    <chime-card v-for="chime in orderedChimes" :key="chime.id" :chime="chime" :user="user" v-on:updatedChime="get_chimes">
                    </chime-card>
                </transition-group>
            </div>
            <div v-else>
                <p v-if="user.guest_user">You're currently browsing as a guest. If you have a Chime access code, you
                    can enter it on the right. Otherwise, <a href="/login">log in</a> to get started.</p>
                <p v-else>You don't currently have any Chimes.</p>
            </div>
        </div>
    </div>

</div>
</div>
</template>

<script>
import {
    EventBus
} from '../../event-bus.js';

export default {
    props: ['user'],
    data() {
        return {
            requireLogin: false,
            studentsCanView: false,
            chimes: [],
            showAdd: false,
            chime_name: "",
        }
    },
    methods: {
        create_chime() {

            axios.post('/api/chime', {
                    'name': this.chime_name,
                    'require_login': this.requireLogin,
                    "students_can_view": this.studentsCanView
                })
                .then(res => {
                    console.log('debug', 'Chime Created:', res);
                    this.showAdd = false;
                    EventBus.$emit('chimesChanged');
                    this.$router.push({ name: 'chime', params: { chimeId: res.data.id }})
                })
                .catch(err => {
                    console.log(
                        'error', 'Error in create chime:', err.response);
                });
        },
        get_chimes() {

            axios.get('/api/chime')
                .then(res => {
                    console.log('debug', 'Get Chimes:', res);
                    this.chimes = res.data;
                })
                .catch(err => {
                    console.error(
                        'error', 'Error in get chimes:', err.response);
                });
        },
        delete_chime(chime) {
            this.$emit('deletechime', chime);
        }
    },
    computed: {
        orderedChimes: function () {
            return _.orderBy(this.chimes, 'created_at', ['desc'])
        },
    },
    created: function () {
        this.get_chimes();
        var self = this;
        EventBus.$on('chimesChanged', function () {
            self.get_chimes();
        });
    }
}
</script>

<style>

</style>

<template>
<div>
    <div class="row">
        <div class="col-12">
            <p data-cy="chime-count-summary">You have access to {{ chimes.length }} {{ 'chime' | pluralize(chimes.length) }}.</p>
            <button dusk="add-chime-button" class="btn btn-outline-primary align-items-center d-flex"
                    @click="showAdd = !showAdd" v-if="!user.guest_user"><span class="material-icons">add</span>Add a
                    Chime</button>
            <transition name="fade">
                <div class="card mt-1" v-if="!user.guest_user && showAdd">
                    <div class="card-body">
                        <div class="form-group row">
                            <label for="chime_name_input" class="col-sm-2 col-form-label">Chime Name</label>
                            <div class="col-sm-8">
                                <input id="chime_name_input" class="form-control" type="text"
                                        v-model="chime_name" />
                                </div>
                            </div>
                            <div class="row">
                                 <ChimeManagementOptions :require_login.sync="requireLogin" :students_can_view.sync="studentsCanView" :join_instructions.sync="joinInstructions" :new_chime="true" />
                            </div>
                            <div class="row">
                                <div class="col">

                                    <button data-cy="create-chime-button" class="btn btn-primary" v-on:click="create_chime"
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
                    <ChimeCard v-for="chime in orderedChimes" :key="chime.id" :chime="chime" :user="user" v-on:updatedChime="get_chimes" />
                </transition-group>
            </div>
            <div class="my-3" v-else>
                <p v-if="user.guest_user">You're currently browsing as a guest. If you have a Chime access code, you
                    can enter it on the right. Otherwise, <a href="/login">log in</a> to get started.</p>
                <p v-else>You don't currently have any Chimes.</p>
            </div>
        </div>
    </div>
</div>
</template>

<script>
import orderBy from 'lodash/orderBy'
import { EventBus } from '../../event-bus.js';
import ChimeCard from './ChimeCard.vue';
import ChimeManagementOptions from '../chime_components/ChimeManagementOptions.vue';

export default {
    props: ['user'],
    components: {
        ChimeCard,
        ChimeManagementOptions,
    },
    data() {
        return {
            requireLogin: false,
            studentsCanView: false,
            joinInstructions: true,
            chimes: [],
            showAdd: false,
            chime_name: "",
        }
    },
    methods: {
        create_chime() {
            if(this.chime_name.length == 0) {
                alert("You must enter a name for the Chime");
                return;
            }
            axios.post('/api/chime', {
                    'name': this.chime_name,
                    'require_login': this.requireLogin,
                    "students_can_view": this.studentsCanView,
                    "join_instructions": this.joinInstructions
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
            return orderBy(this.chimes, 'created_at', ['desc'])
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

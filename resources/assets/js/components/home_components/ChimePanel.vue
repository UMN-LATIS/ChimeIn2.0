<template>
    <div class="card">
        <div class="card-content grey lighten-4">
            <span class="card-title">Chimes</span>
            <div v-if="chimes.length > 0">
                <transition-group name="fade">
                    <div
                        v-for="chime in chimes"
                        v-bind:key="chime.id"
                        class="card hoverable">
                        <div
                            class="card-content"
                            v-on:click="open_chime(chime.id)"
                            v-bind:key="chime.name">
                            <span class="card-title">{{ chime.name }}</span>
                        </div>
                        <div class="card-action" v-bind:key="chime.id">
                            <!--
                            <div v-if="show_add_instructor">
                                <div class="row">
                                    <div class="input-field col s10">
                                        <input
                                            id="edit-folder-input"
                                            v-model="new_instructor_email"
                                            type="text"
                                            @keyup.esc="toggle_add_instructor"
                                            @keyup.enter="add_instructor(chime.id)">
                                        <label for="edit-folder-input">Instructor Email</label>
                                    </div>
                                    <br/>
                                    <div class="input-field col s1">
                                        <a
                                            class="btn-small waves-effect waves-light"
                                            v-on:click="toggle_add_instructor">
                                            <i class="material-icons">clear</i>
                                        </a>
                                    </div>
                                    <div class="input-field col s1">
                                        <a
                                            class="btn-small waves-effect waves-light"
                                            v-on:click="add_instructor(chime.id)">
                                            <i class="material-icons">save</i>
                                        </a>
                                    </div>
                                </div>
                                
                            </div>
                            <div v-else>
                                <a href="#" v-on:click="delete_chime(chime)">
                                    <i class="material-icons right">delete</i>
                                </a>
                                <a href="#" v-on:click="toggle_add_instructor">
                                    <i class="material-icons right">person_add</i>
                                </a>
                            </div>
                            -->
                            <a href="#" v-on:click="delete_chime(chime)">
                                <i class="material-icons right">delete</i>
                            </a>
                        </div>
                    </div>
                </transition-group>
            </div>
            <div v-else>
                <p>No Chimes Yet!</p>
            </div>
        </div>
        <div class="card-action">
            <div class="row">
                <div class="input-field col s10">
                    <input
                        id="chime_name_input"
                        class="materalize-textarea"
                        type="text"
                        v-model="chime_name"
                        v-on:keyup="filter_chime"
                        @keyup.enter="new_chime"/>
                    <label for="chime_name_input">Chime Name</label>
                </div>
                <div class="input-field col s2">
                    <button
                        class="waves-effect waves-light btn"
                        v-on:click="new_chime"
                        type="button">
                        Create
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    export default {
        props: ['chimes'],
        data() {
            return {
                chime_name: '',
                // show_add_instructor: false,
                // new_instructor_email: ''
            }
        },
        methods: {
            /*
            toggle_add_instructor() {
                new_instructor_email = '';
                this.show_add_instructor = (this.show_add_instructor ? false : true);
            },
            add_instructor(chime_id) {
                axios.post('/api/chime/' + chime_id + '/add_instructor', {
                    email: this.new_instructor_email
                })
                .then(res => {
                    console.log(res);
                    this.toggle_add_instructor();
                })
                .catch(err => {
                    console.error(err);
                });
            },
            */
            filter_chime() {
                // NOTE mechanic to filter chimes muted
                // this.$emit('filterchime', this.chime_name);
            },
            new_chime() {
                this.$emit('newchime', this.chime_name);
                this.chime_name = '';
                document.activeElement.blur(); 
            },
            open_chime(id) {
                window.location.href = '/chime/' + id;
            },
            delete_chime(chime) {
                this.$emit('deletechime', chime);
            }
        }
    }
</script>

<style>
    .hoverable {
        cursor: pointer;
    }

    .fade-enter-active, .fade-leave-active {
        transition: all 1s;
        position: absolute;
    }
    .fade-enter, .fade-leave-to {
        opacity: 0;
    }
    .fade-move {
        transition: transform 1s;
    }
</style>


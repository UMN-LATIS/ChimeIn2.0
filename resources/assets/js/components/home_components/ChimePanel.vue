<template>
    <div class="card">
        <div class="card-content grey lighten-4">
            <span class="card-title">Chimes</span>
            <div v-if="chimes.length > 0">
                <transition-group name="fade">
                    <chime-card
                        v-for="chime in chimes"
                        :key="chime.id"
                        :chime="chime"
                        :user="user"
                        v-on:deletechime="delete_chime">
                    </chime-card>
                </transition-group>
            </div>
            <div v-else>
                <p>No Chimes Yet!</p>
            </div>
        </div>
        <div class="card-action" v-if="parseInt(user.permission_number) >= 300">
            <div class="row">
                <div class="input-field col s12">
                    <input
                        id="chime_name_input"
                        class="materalize-textarea"
                        type="text"
                        v-model="chime_name"
                        v-on:keyup="filter_chime"
                        @keyup.enter="new_chime"/>
                    <label for="chime_name_input">Chime Name</label>
                </div>
                <div class="input-field col s12">
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
        props: ['chimes', 'user'],
        data() {
            return {
                chime_name: '',
            }
        },
        methods: {
            filter_chime() {
                // NOTE mechanic to filter chimes muted
                // this.$emit('filterchime', this.chime_name);
            },
            new_chime() {
                this.$emit('newchime', this.chime_name);
                this.chime_name = '';
                document.activeElement.blur(); 
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

    .col.s12 > .btn {
        width: 100%;
    }
</style>


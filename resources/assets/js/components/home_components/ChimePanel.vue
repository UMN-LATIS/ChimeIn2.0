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
            <input
                id="chime_name_input"
                type="text"
                v-model="chime_name"
                v-on:keyup="filter_chime"
                @keyup.enter="new_chime">
            <label for="chime_name_input">Chime Name</label>
        </div>
    </div>
</template>

<script>
    export default {
        props: ['chimes'],
        data() {
            return {
                chime_name: ''
            }
        },
        methods: {
            filter_chime() {
                this.$emit('filterchime', this.chime_name);
            },
            new_chime() {
                this.$emit('newchime', this.chime_name);
                this.chime_name = '';
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


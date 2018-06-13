<template>
    <div>
        <div class="row">
            <div class="col-12">
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
    </div>
    <div class="row">
        <div class="" v-if="parseInt(user.permission_number) >= 300">
            <div class="input-field col-12">
                <input
                id="chime_name_input"
                class="materalize-textarea"
                type="text"
                v-model="chime_name"
                v-on:keyup="filter_chime"
                @keyup.enter="new_chime"/>
                <label for="chime_name_input">Chime Name</label>
            </div>
            <div class="input-field col-12">
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

    </style>


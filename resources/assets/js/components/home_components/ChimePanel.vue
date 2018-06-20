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
                        v-on:updatedChime="get_chimes">
                    </chime-card>
                </transition-group>
            </div>
            <div v-else>
                <p>No Chimes Yet!</p>
            </div>
        </div>
    </div>
    
</div>
</div>
</template>

<script>

import { EventBus } from '../../event-bus.js';


export default {
    props: ['user'],
    data() {
        return {
            chimes: [],
        }
    },
    methods: {
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


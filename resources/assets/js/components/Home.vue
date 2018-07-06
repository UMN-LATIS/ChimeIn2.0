<template>
    <div>
        <navbar
        title=""
        :user="user"
        :link="'/'">
    </navbar>
    <div class="container">

     <div class="col-sm-12">
        <h1 class="display-3 center">Welcome, {{ user.name }}</h1>

        <div class="row">
            <div class="col-12 col-md-9 order-sm-last order-last order-md-first">
                <chime-panel :user="user">
                </chime-panel>
                <div class="row">
                    <div class="">
                        <div class="input-field col-12">
                            <input
                            id="chime_name_input"
                            class="materalize-textarea"
                            type="text"
                            v-model="chime_name"
                            @keyup.enter="create_chime"/>
                            <label for="chime_name_input">Chime Name</label>
                        </div>
                        <div class="input-field col-12">
                            <button
                            class="waves-effect waves-light btn"
                            v-on:click="create_chime"
                            type="button">
                            Create
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-3 order-md-last order-sm-first order-first">
            <div class="card">
                <div class="card-body">
                    <div class="input-group mb-3">
                        <p>Do you have an access code to join a Chime? Enter it below.</p>
                        <input type="text" class="form-control" name="access_code" id="access_code" placeholder="Access Code" v-on:keyup.enter="join_chime" v-model="access_code" >
                        <div class="input-group-append">
                            <button class="btn btn-secondary" type="button" v-on:click="join_chime">Join</button>
                        </div>
                    </div>

                </div>


            </div>
            
        </div>
    </div>
</div>
</div>
</div>

</template>

<script>
import { EventBus } from '../event-bus.js';

export default {
    data() {
        return {
            chimes: [],
            access_code: "",
            chime_name: ""
        };
    },
    props: ['user'],
    methods: {
        create_chime() {

            axios.post('/api/chime', {'chime_name': this.chime_name})
            .then(res => {
                console.log('debug', 'Chime Created:', res);
                EventBus.$emit('chimesChanged');
            })
            .catch(err => {
                console.log(
                    'error', 'Error in create chime:', err.response);
            });
        },
        join_chime() {
            console.log(this.access_code);

            axios.post('/api/chime/' + this.access_code)
            .then(res => {
                this.access_code = "";
                EventBus.$emit('chimesChanged');
            })
            .catch(err => {
                console.error(
                    'error', 'Error in join chime:', err.response);
            });
        },
        
    }
};
</script>
<template>
    <div>
        <navbar
        title=""
        :user="user"
        :link="'/'">
    </navbar>
    <div class="container">

       <div class="col-sm-12">
        <h1 class="display-4 center" v-if="!user.guest_user">Welcome, {{ user.name }}</h1>
        <h1 class="display-4 center" v-else="!user.guest_user">Welcome, guest user</h1>

        <div class="row">
            <div class="col-12 col-md-9">
                <chime-panel :user="user">
                </chime-panel>
            </div>
            
        <div class="col-12 col-md-3">
            <div class="card">
                <div class="card-header">
                    Access Code
                </div>
                <div class="card-body">
                    <div class="input-group mb-3">
                        <p>Do you have an access code to join a Chime? Enter it below.</p>
                        <input type="text" class="form-control" name="access_code" id="access_code" placeholder="Access Code" v-on:keyup.enter="join_chime" v-model="access_code" >
                        <div class="input-group-append">
                            <button class="btn btn-secondary" type="button" v-on:click="join_chime">Join</button>
                        </div>
                        <div class="alert alert-danger" role="alert" v-if="requires_login">
                            You must <a href="/login">log in</a> to join this Chime
                        </div>
                        <div class="alert alert-danger" role="alert" v-if="chime_not_found">
                            We couldn't find a Chime associated with that code
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
            access_code: "",
            requires_login: false,
            chime_not_found: false
        };
    },
    props: ['user'],
    methods: {
        join_chime() {
            console.log(this.access_code);
            this.requires_login = false;
            this.chime_not_found = false;
            axios.post('/join/' + this.access_code)
            .then(res => {
                this.access_code = "";
                EventBus.$emit('chimesChanged');
                this.$router.push({ name: 'chimeStudent', params: { chimeId: res.data.id }})
            })
            .catch(err => {
                if(err.response.data.requiresLogin) {
                    this.requires_login = true;
                }
                if(err.response.data.chimeNotFound) {
                    this.chime_not_found = true;
                }
                console.error(
                    'error', 'Error in join chime:', err.response);
            });
        },
        
    }
};
</script>
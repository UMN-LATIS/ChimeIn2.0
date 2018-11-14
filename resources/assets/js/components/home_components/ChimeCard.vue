<template>
    <div
        class="card hoverable">
        <div class="card-body" v-on:click="open_chime">
            <div class="float-left">
            <h5  class="card-title">{{ chime.name }}</h5>
            <h6 v-on:click.stop="" class="card-subtitle mb-2 text-muted">Access code: {{ chime.access_code.toLowerCase() }}</h6>
            </div>
            <div class="float-right">
                <a href="#" v-on:click.stop="delete_chime()">
                    <i class="material-icons right">delete</i>
                </a>
            </div>
        </div>
    
    </div>
</template>

<style>


.hoverable:hover {
    cursor:pointer;
    transition: 0.2s;
    box-shadow: 2px 2px 18px lightgray;
}

.card {
    margin-top: 5px;
    margin-bottom: 5px;
}

</style>


<script>
export default {
    props: ['chime', 'user'],
    data: function() {
        return {
        }
    },
    methods: {
        open_chime() {
            if(this.chime.pivot.permission_number >= 200) {
                this.$router.push({ name: 'chime', params: { chimeId: this.chime.id }})    
            }
            else {
                this.$router.push({ name: 'chimeStudent', params: { chimeId: this.chime.id }})
            }
            
        },
        delete_chime() {
            const confirmation = window.confirm(
                'Delete Chime: ' + this.chime.name + ' ?');
            
            if (confirmation) {

                axios.delete('/api/chime/' + this.chime.id)
                .then(res => {
                   this.$emit('updatedChime');
                })
                .catch(err => {
                    console.error(
                        'error', 'Error in delete chime:', err.response);
                });
            }
            
        }
    }
}
</script>


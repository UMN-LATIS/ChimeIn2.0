<template>
    <div class="card grey lighten-4">
        <div class="card-content">
            <span class="card-title">Access Code</span>
            <input
                placeholder="Code"
                type="text"
                v-on:keyup.enter="submit_code"
                v-model="access_code">
        </div>
        <div class="card-action white">
            <a href="#" v-on:click="submit_code">Submit</a>
        </div>
    </div>
</template>

<script>
export default {
    data: function() {
        return {
            access_code: ''
        }
    },
    methods: {
        submit_code() {
            this.$emit('submitcode', this.access_code);
            this.access_code =  '';
        }
    }
}
</script>


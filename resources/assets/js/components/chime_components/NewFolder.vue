<template>
    <div class="row">
        <h4>Access Code: {{ chime.access_code }}</h4>
        <div class="input-field col s10">
            <input
                id="name"
                v-model="folder_name"
                type="text"
                @keyup.enter="new_folder"
                v-on:keyup="filter_folder">
            <label for="name">Folder Name</label>
        </div>
        <div class="input-field col s2">
            <button
                class="waves-effect waves-light btn"
                v-on:click="new_folder"
                type="button">
                Create
            </button>
        </div>
    </div>
</template>

<script>
export default {
    props: ['chime'],
    data: function() {
        return {
            folder_name: ''
        }
    },
    methods: {
        filter_folder: function() {
            // NOTE Filter folder mechanic muted
            // this.$emit('filterfolder', this.folder_name);
        },
        new_folder: function() {
            this.$emit('newfolder', this.folder_name);
            this.folder_name = '';
            document.activeElement.blur(); 
        }
    }
}
</script>

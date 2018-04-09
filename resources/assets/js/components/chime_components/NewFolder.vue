<template>
    <div class="row">
        <h4>Access Code: {{ chime.access_code }}</h4>
        <div class="input-field col s12">
            <input
                id="name"
                v-model="folder_name"
                type="text"
                @keyup.enter="new_folder"
                v-on:keyup="filter_folder"
                class="validate">
            <label for="name">Folder Name</label>
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
            this.$emit('filterfolder', this.folder_name);
        },
        new_folder: function() {
            this.$emit('newfolder', this.folder_name);
            this.folder_name = '';
        }
    }
}
</script>

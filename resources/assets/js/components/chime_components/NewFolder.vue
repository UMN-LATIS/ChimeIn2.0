<template>
    <div class="row">
        <div class="col-12">
            <form class="form-inline">
                <div class="form-group">
                    <label for="createFolder">New Folder: </label>
                    <input type="text" class="form-control" v-model="folder_name" @keyup.enter="new_folder" name="createFolder" id="createFolder" placeholder="Folder Name">
                </div>
                <button type="button"  class="btn btn-primary" v-on:click="new_folder">Create</button>
            </form>
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

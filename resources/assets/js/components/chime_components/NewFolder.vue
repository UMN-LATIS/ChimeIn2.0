<template>
    <div class="card">
        <div class="card-body">
                <div class="">
                    <label for="createFolder" class="text-right col-form-label">Add a folder</label>
                    <div class="row">
                    <div class="col-8">
                        <input type="text" class="form-control" v-model="folder_name" @keyup.enter="new_folder" name="createFolder" id="createFolder" placeholder="Folder Name">
                    </div>
                    <div class="col-2">
                        <button dusk="create-folder" type="button"  class="btn btn-primary" v-on:click="new_folder">Create</button>
                    </div>
                    </div>  
                </div>
                
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

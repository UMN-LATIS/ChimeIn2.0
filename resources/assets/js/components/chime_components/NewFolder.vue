<template>
    <div class="card">
        <div class="card-body">
                <div class="">
                    
                    <label for="createFolder" class="text-right col-form-label">Add a folder</label>
                    <div class="row">
                    <div class="col-6">
                        <input type="text" class="form-control" v-model="folder_name" @keyup.enter="new_folder" name="createFolder" id="createFolder" placeholder="Folder Name">
                    </div>
                    <div class="col-2">
                        <button dusk="create-folder" type="button"  class="btn btn-primary" v-on:click="new_folder">Create</button>
                    </div>
                    </div>  
                    <div class="alert alert-warning mt-2" role="alert" v-if="chime.lti_grade_mode == 'multiple_grades'">
                        <strong>Warning:</strong> Creating a new folder will not add a new gradebook entry in Canvas. If you'd like to  track participation by week/lecture in the gradebook, start by creating a new assignment in Canvas. See <a href="https://umn-latis.github.io/ChimeIn2.0/canvas.html">our help</a> or <a href="mailto:latistecharch@umn.edu">contact us</a> for assistance. You can still create folders to assist with your own organization, but questions in those folders will not be reflected in Canvas grades. 
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

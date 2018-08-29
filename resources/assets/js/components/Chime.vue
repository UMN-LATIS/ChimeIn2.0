<template>
    <div>
        <navbar
        title="Home"
        :user="user"
        :link="'/'">
    </navbar>
        <div class="container">
        <div class="row">
            <div class="col-12">

                        <p><h1>{{ chime.name }}<div class="float-right">

                            
                            <b-btn v-b-toggle.collapse1 variant="outline-primary" class="align-items-center d-flex"><span class="material-icons">edit</span>Edit Chime</b-btn>
                        </div></h1>

                            
                        </p>
                        
                    <b-collapse id="collapse1" class="mt-2">
                <ChimeManagement :chime="chime" v-on:requireLoginChange="requireLoginChange"></ChimeManagement>
            </b-collapse>
            </div>
            

        </div>

        <div class="center-align">
            <div v-if="viewable_folders.length > 0">
                <transition-group name="fade">
                    <folder-card
                    v-for="folder in viewable_folders"
                    :folder="folder"
                    :chime="chime"
                    :key="folder.id"
                    v-on:editfolder="edit_folder"
                    v-on:deletefolder="delete_folder">
                </folder-card>
            </transition-group>

        </div>
        <div v-else>
            No Folders Yet!
        </div>
             <new-folder
            :chime="chime"
            v-on:newfolder="create_folder"></new-folder>   
            </div>
    </div>
</div>
</template>

<style>

</style>

<script>
export default {
    data() {
        return {
            chime: {},
            folders: [],
            viewable_folders: [],
        };
    },
    props: ['user', 'chimeId'],
    methods: {
        requireLoginChange: function(newValue) {
            this.chime.require_login = newValue;
            this.saveChime();
        },
        saveChime: function() {

            axios.patch('/api/chime/' + this.chime.id, this.chime)
            .then(res => {
                this.reloadChime();
            })
            .catch(err => {
                console.log(err.response);
            });

        },
        create_folder: function(folder_name) {
            if (this.folders.filter(e => e.name === folder_name).length < 1) {
                const self = this;
                axios.post('/api/chime/' + this.chime.id + '/folder', {
                    folder_name: folder_name
                })
                .then(res => {
                    console.log(res);
                    self.folders.push(res.data);
                    self.viewable_folders = self.folders.map(e => e);
                })
                .catch(err => {
                    console.log(err.response);
                });
            }
        },
        edit_folder: function(folder_id, folder_name) {
            const self = this;
            axios.put('/api/chime/' + this.chime.id + '/folder/' + folder_id, {
                folder_name: folder_name
            })
            .then(res => {
                console.log(res);
                const i = self.folders.findIndex(e => e.id === folder_id);

                if (i > -1) {
                    self.folders.splice(i, 1, res.data);
                }
            })
            .catch(err => {
                console.error(err);
            });
        },
        delete_folder: function(folder) {
            const confirm = window.confirm(
                'Delete Folder ' + folder.name + '?');
            
            if (confirm) {
                const url = (
                    '/api/chime/' + this.chime.id + /folder/ + folder.id
                    );

                axios.delete(url)
                .then(res => {
                    console.log(res);

                    const f_index = this.folders.findIndex(e => e.id === folder.id);
                    const v_index = this.viewable_folders.findIndex(e => e.id === folder.id);

                    if (f_index > -1) {
                        this.folders.splice(f_index, 1);
                    }

                    if (v_index > -1) {
                        this.viewable_folders.splice(v_index, 1);
                    }
                })
                .catch(err => {
                    console.log(err.response);
                });
            }
        },
        reloadChime: function() {
            axios.get('/api/chime/' + this.chimeId)
            .then(res => {
                console.log(res);
                this.chime = res.data;
                this.folders = this.chime.folders;
                this.viewable_folders = res.data.folders;
                document.title = this.chime.name;
            })
            .catch(err => {
                console.log(err);
            });
        }
    },
    created: function () {
        this.reloadChime();
    }
};
</script>
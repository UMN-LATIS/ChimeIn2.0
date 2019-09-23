<template>
    <div>
        <navbar
        title="Back to Home"
        :user="user"
        :link="'/'" />
        <error-dialog />
        <div class="container">
            <div class="row mt-2">
                <div class="col-12">

                    <h1>{{ chime.name }}
                        <div class="float-right btn-group"  role="group" aria-label="Chime Controls">
                            <button @click="showSettings = !showSettings; exportPanel = false"  class="btn btn-outline-info align-items-center d-flex">Chime Settings <span class="material-icons">settings</span>
                            </button>
                            <button @click="exportPanel = !exportPanel; showSettings = false" class="btn btn-outline-info align-items-center d-flex">Export <span class="material-icons">save_alt</span>
                            </button>
                        </div>
                    </h1> 
                <transition name="dropdown">
                <ChimeManagement  v-if="showSettings" :chime.sync="chime" />
                <ChimeExport  v-if="exportPanel" :chime="chime" />
                </transition>
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
                        />    
                    </transition-group>
                </div>
                <div v-else>
                    <h4>You don't have any folders yet.  Why not create one now?</h4>
                </div>
                <new-folder :chime="chime" v-on:newfolder="create_folder"></new-folder>   
            </div>
        </div>
    </div>
</template>

<style scoped>
.card {
    margin-top: 5px;
    margin-bottom:5px;
}
</style>

<script>
    export default {
        data() {
            return {
                chime: {},
                folders: [],
                viewable_folders: [],
                showSettings: false,
                exportPanel: false
            };
        },
        props: ['user', 'chimeId'],
        methods: {
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
                    this.$store.commit('message', "Could not load Chime. You may not have permission to view this page. ");
                    console.log(err);
                });
            }
        },
        created: function () {
            this.reloadChime();
        }
    };
</script>
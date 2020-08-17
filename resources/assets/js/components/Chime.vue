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
                            <button @click="showSettings = !showSettings; exportPanel = false"  class="btn btn-outline-info align-items-center d-flex" dusk="manage-button">Chime Settings <span class="material-icons">settings</span>
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
                <div v-if="ordered_folders.length > 0">
                    <draggable v-model="ordered_folders"  handle=".draghandle" :forceFallback="true">
                    <!-- <transition-group name="fade"> -->
                        
                        <folder-card 
                            v-for="folder in ordered_folders"
                            :folder="folder"
                            :chime="chime"
                            :key="folder.id"
                            :draggable="ordered_folders.length>1?true:false"
                        />    
                        
                    <!-- </transition-group> -->
                    </draggable>
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

import draggable from 'vuedraggable'

    export default {
        data() {
            return {
                chime: {},
                showSettings: false,
                exportPanel: false
            };
        },
        components: {
            draggable
        },
        props: ['user', 'chimeId'],
        methods: {
            create_folder: function(folder_name) {
                if (this.chime.folders.filter(e => e.name === folder_name).length < 1) {
                    axios.post('/api/chime/' + this.chime.id + '/folder', {
                        folder_name: folder_name
                    })
                    .then(res => {
                        console.log(res);
                        this.chime.folders.push(res.data);
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
                    document.title = this.chime.name;
                })
                .catch(err => {
                    this.$store.commit('message', "Could not load Chime. You may not have permission to view this page. ");
                    console.log(err);
                });
            }
        },
        computed: {
            ordered_folders: {
                get() {
                    if(!this.chime || !this.chime.folders) { 
                        return [];
                    }
                    return  _.orderBy(this.chime.folders, ['order','created_at'], ['asc','asc']);
                },
                set(value) {
                    console.log(value);
                    value.map((f, index) => f.order = index +1)
                    const url = (
                        '/api/chime/' +
                        this.chime.id)
                    axios.put(url, {
                            folders: this.chime.folders
                        })
                        .then(res => {
                            this.chime.folders = value;
                        })
                        .catch(err => {
                            console.log(err.response);
                        });
                }
            }
        },
        created: function () {
            this.reloadChime();
        }
    };
</script>
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

                    
                    <b-btn v-b-toggle.collapse1 variant="outline-primary" class="align-items-center d-flex"><span class="material-icons">settings</span>Chime Settings</b-btn>
                </div></h1>

                
            </p>
            
            <b-collapse id="collapse1" class="mt-2">
                <ChimeManagement :chime="chime" v-on:requireLoginChange="requireLoginChange" v-on:studentsCanViewChange="studentsCanViewChange"></ChimeManagement>
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
                >
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
        };
    },
    props: ['user', 'chimeId'],
    methods: {
        requireLoginChange: function(newValue) {
            this.chime.require_login = newValue;
            this.saveChime();
        },
        studentsCanViewChange: function(newValue) {
            this.chime.students_can_view = newValue;
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
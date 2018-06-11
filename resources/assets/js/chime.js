
/**
 * First we will load all of this project's JavaScript dependencies which
 * includes Vue and other libraries. It is a great starting point when
 * building robust, powerful web applications using Vue and Laravel.
 */

require('./bootstrap');

Vue.component('navbar', require('./components/Navbar.vue'));
Vue.component('new-folder',
    require('./components/chime_components/NewFolder.vue'));
Vue.component('folder-card',
    require('./components/chime_components/FolderCard.vue'));
Vue.component('question',
    require('./components/chime_components/Question.vue'));
Vue.component('multiple-choice-display',
    require('./components/questions/display/MultipleChoice.vue'));
Vue.component('question-form',
    require('./components/chime_components/QuestionForm.vue'));


const app = new Vue({
    el: '#app',
    data: {
        chime: {},
        folders: [],
        viewable_folders: []
    },
    methods: {
        filter_folders: function(folder_name) {
            if (folder_name === '') {
                this.viewable_folders = this.folders.map(e => e);
            } else {
                this.viewable_folders = (
                    this.viewable_folders.filter(
                        folder => folder.name.indexOf(folder_name) > -1));
            }
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
        }
    },
    created: function () {
        const self = this;
        axios.get('/api/chime/' + this.getCurrentChime())
        .then(res => {
            console.log(res);
            self.chime = res.data.chime;
            self.folders = res.data.folders;
            self.viewable_folders = res.data.folders;
            document.title = self.chime.name;
        })
        .catch(err => {
            console.log(err);
        });
    }
});

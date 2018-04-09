
/**
 * First we will load all of this project's JavaScript dependencies which
 * includes Vue and other libraries. It is a great starting point when
 * building robust, powerful web applications using Vue and Laravel.
 */

require('./bootstrap');

window.Vue = require('vue');

Vue.component('navbar',
    require('./components/Navbar.vue'));
Vue.component('chime-panel',
    require('./components/home_components/ChimePanel.vue'));
Vue.component('access-panel',
    require('./components/home_components/AccessPanel.vue'));
Vue.component('info-panel',
    require('./components/home_components/InfoPanel.vue'));

const app = new Vue({
    el: '#app',
    data: {
        chimes: [],
        viewable_chimes: []
    },
    methods: {
        create_chime(chime_name) {
            if (this.chimes.filter(e => e.name === chime_name).length < 1) {
                const self = this;
                axios.post('/api/chime', {'chime_name': chime_name})
                .then(res => {
                    console.log('debug', 'Chime Created:', res);
                    self.chimes.push(res.data);
                    self.viewable_chimes = self.chimes.map(e => e);
                })
                .catch(err => {
                    console.log(
                        'error', 'Error in create chime:', err.response);
                });
            }
        },
        filter_chimes(chime_name) {
            if (chime_name === '') {
                this.viewable_chimes = this.chimes.map(e => e);
            } else {
                this.viewable_chimes = (
                    this.viewable_chimes.filter(
                        chime => chime.name.indexOf(chime_name) > -1));
            }
        },
        join_chime(access_code) {
            console.log(access_code);
            const self = this;
            axios.post('/api/chime/' + access_code)
            .then(res => {
                console.log('debug', 'Chime Joined:', res);
                self.chimes.push(res.data);
                self.viewable_chimes = self.chimes.map(e => e);
            })
            .catch(err => {
                console.error(
                    'error', 'Error in join chime:', err.response);
            });
        },
        delete_chime(chime) {
            const confirmation = window.confirm(
                'Delete Chime ' + chime.name + ' ?');
            
            if (confirmation) {
                const self = this;

                axios.delete('/api/chime/' + chime.id)
                .then(res => {
                    console.log('debug', 'Chime Deleted:', res);
                    const c_index = self.chimes.findIndex(e => e.id === chime.id);
                    const v_index = self.viewable_chimes.findIndex(
                        e => e.id === chime.id);
    
                    self.chimes.splice(c_index, 1);
                    self.viewable_chimes.splice(v_index, 1);
                })
                .catch(err => {
                    console.error(
                        'error', 'Error in delete chime:', err.response);
                });
            }
        }
    },
    created: function () {
        const self = this;
        axios.get('/api/chime')
        .then(res => {
            console.error('debug', 'Get Chimes:', res);
            self.chimes = res.data;
            self.viewable_chimes = res.data;
        })
        .catch(err => {
            console.error(
                'error', 'Error in get chimes:', err.response);
        });
    }
});


require('./bootstrap');


Vue.component('home',
    require('./components/Home.vue'));

Vue.component('navbar',
    require('./components/Navbar.vue'));
Vue.component('chime-panel',
    require('./components/home_components/ChimePanel.vue'));
Vue.component('chime-card',
    require('./components/home_components/ChimeCard.vue'));
Vue.component('access-panel',
    require('./components/home_components/AccessPanel.vue'));
Vue.component('info-panel',
    require('./components/home_components/InfoPanel.vue'));



const app = new Vue({
}).$mount('#app')
<template>
    <div>
        <navbar
            :title="'Home'"
            :user="user"
            :link="'/'">
        </navbar>
        
        <div class="container">
            <h4 class="center">
                Welcome, {{ user.name }}
            </h4>

            <div class="row">
                <div class="col s12 m12 l8">
                    <chime-panel
                        :chimes="viewable_chimes"
                        :user="user"
                        v-on:newchime="create_chime"
                        v-on:filterchime="filter_chimes"
                        v-on:deletechime="delete_chime">
                    </chime-panel>
                </div>
                <div class="col s12 m12 l4">
                    <div class="row">
                        <div class="col s12">
                            <access-panel
                                v-on:submitcode="join_chime">
                            </access-panel>
                        </div>
                        <div class="col s12">
                            <info-panel></info-panel>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

</template>

<script>
export default {
    data() {
        return {
        chimes: [],
        viewable_chimes: []
        };
    },
    props: ['user'],
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
            console.log('debug', 'Get Chimes:', res);
            self.chimes = res.data;
            self.viewable_chimes = res.data;
        })
        .catch(err => {
            console.error(
                'error', 'Error in get chimes:', err.response);
        });
    }
};
</script>
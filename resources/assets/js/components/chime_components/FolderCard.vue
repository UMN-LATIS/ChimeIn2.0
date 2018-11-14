<template>
    <div class="card">
        <div class="card-body">
            <b-row>
                <template v-if="show_edit_folder">
                    <b-col sm="8">
                        <input
                        id="edit-folder-input"
                        v-model="new_folder_name"
                        type="text"
                        class="form-control"
                        @keyup.esc="toggle_edit_folder"
                        @keyup.enter="edit_folder">
                    </b-col>
                    <b-col sm="1" class="align-items-center d-flex">
                        <a
                        class="pointer"
                        v-on:click="toggle_edit_folder">
                        <i class="material-icons">clear</i></a>
                        <a
                        class="pointer"
                        v-on:click="edit_folder">
                        <i class="material-icons">save</i></a>
                    </b-col>
                </template>
                <template v-else v-on:click="toggle_edit_folder">

                    <b-col sm="9">
                        <a v-on:click="show_questions = !show_questions" class="pointer" v-if="questions.length > 0">
                            <i class="material-icons float-left" v-if="show_questions">expand_less</i>
                            <i class="material-icons float-left" v-if="!show_questions">expand_more</i>
                        </a>
                        <h4>{{ folder.name }}</h4>
                    </b-col>
                    <b-col sm="3" class="text-right">

                        <router-link :to="{ name: 'present', params: {chimeId: chime.id, folderId: folder.id} }">
                            <i class="material-icons">play_arrow</i>
                        </router-link>
                        <i class="material-icons pointer" v-on:click="show_edit_folder = true">edit</i>
                        <i class="material-icons pointer" v-on:click="delete_folder">delete</i>


                        <i class="material-icons pointer" @click="showModal = true">add</i>

                    </b-col>
                </template>


                <question-form :show="showModal" @close="showModal = false; load_questions();"
                :question="{
                text:'',
                question_info: {
                question_type:'multiple_choice',
                question_responses: []
            }
        }"
        :folder="folder"
        :chime="chime"
        controlType="create">
    </question-form>
</b-row> 
<vue-slide-up-down :active="show_questions" :duration="500" ref="slideup">

    <ul>
        <draggable v-model="questions" @end=swap_question>
            <question
            v-for="q in questions"
            :key="q.id"
            :folder="folder"
            :chime="chime"
            :question="q"
            v-on:editquestion="load_questions"
            v-on:deletequestion="delete_question">
        </question>
    </draggable>
</ul>

</vue-slide-up-down> 

</div>
</div>
</template>

<script>
import draggable from 'vuedraggable'

export default {
    props: ['folder', 'chime'],
    data() {
        return {
            showModal: false,
            content: 'present',
            questions: [],
            show_edit_folder: false,
            show_questions: false,
            new_folder_name: this.folder.name
        }
    },
    methods: {
        toggle_edit_folder: function() {
            this.show_edit_folder = (this.show_edit_folder ? false : true);
        },
        swap_question(event, originalEvent) {
            console.log(this.questions);
            const newOrder = Array.from(this.questions.entries()).map(e => {
                return {
                    order: e[0]+1,
                    id: e[1].id
                }
            });

            console.log(newOrder);
            const url = (
                '/api/chime/'
                + this.folder.chime_id
                + '/folder/'
                + this.folder.id
                + '/save_order'
                )

            axios.put(url, {
                question_order: newOrder
            })
            .then(res => {
                console.log(res);
            })
            .catch(err => {
                console.log(err.response);
            });
        },
        delete_question(questionId) {
            const url = (
                '/api/chime/' + this.folder.chime_id +
                '/folder/' + this.folder.id + '/question/' + questionId);
            const self = this;

            axios.delete(url)
            .then(res => {
                console.log(res);
                const question_index = self.questions.findIndex(
                    e => e.id === questionId);
                self.questions.splice(question_index, 1);
                this.$nextTick(function () {
                    this.$refs.slideup.layout();    
                });
            })
            .catch(err => {
                console.log(err.response);
            });
        },
        edit_folder() {
            this.$emit('editfolder', this.folder.id, this.new_folder_name);
            this.show_edit_folder = false;
        },
        delete_folder() {
            this.$emit('deletefolder', this.folder);
        },
        load_questions() {
            const url = (
                '/api/chime/' + this.folder.chime_id + '/folder/' + this.folder.id);


            axios.get(url)
            .then(res => {
                this.questions = res.data;
                console.log('questions:', this.questions);
                if(this.$refs.slideup) {
                    // redraw the vue slide on the next draw loop
                    this.$nextTick(function () {
                        this.$refs.slideup.layout();    
                    });
                    
                }
            })
            .catch(err => {
                console.log(err);
            });

            

        }
    },
    created: function() {
        this.load_questions();
        
    },
    components: {
        draggable
    }
};
</script>

<style >
.pointer {
    cursor: pointer;
}
</style>
<style scoped>
ul li {
    list-style: none;
}

</style>

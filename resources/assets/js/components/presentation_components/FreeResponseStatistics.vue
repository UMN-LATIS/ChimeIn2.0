<template>
    <div>
        <div v-if="responses.length > 0">
            <a
                class="waves-effect waves-light btn-small"
                id="csv_link"
                v-on:click="export_csv">
                Export CSV
            </a>
            <fullscreen ref="fullscreen" @change="fullscreenChange">
            <word-cloud
                :data="word_groups"
                :nameKey="'name'"
                :valueKey="'value'"
                :rotate="rotation"
                :margin="margin"
                :wordPadding="1"
                style="width: 100%; height:600px"
                :fontSize="fontSize"
                >
            </word-cloud>
            
            </fullscreen>

<button type="button" @click="toggle" >Fullscreen</button>
            <transition-group name="fade">
                <div
                    v-for="(r, i) in responses.map(r => r.response_info.text).reverse().slice(0, 10)"
                    v-bind:key="i">
                    <blockquote v-bind:key="r">
                        {{ r }}
                    </blockquote>
                </div>
            </transition-group>
        </div>

        <div v-else>No Responses Yet!</div>

<!--         <input
            id="response_search_input"
            type="text"
            v-model="response_search"
            v-on:keyup="filter_responses">
        <label for="response_search_input">Student Name</label>

        <ul>
            <li v-for="r in visible_responses" :key="r.id">
                {{r.user.name}}: {{r.response_info.text}}
            </li>
        </ul> -->
    </div>
</template>

<script>
const wordcloud = require('vue-wordcloud').default;
const difflib = require('difflib');
const cluster = require('set-clustering');

export default {
    props: ['responses', 'question'],
    data: function() {
        return {
            fontSize: [20, 120],
            fullscreen: false,
            visible_responses: [],
            response_search: '',
            rotation: {from: 0, to: 0, numOfOrientation: 1 },
            margin: {top: 0, bottom: 0, left: 0, right: 0}
        }
    },
    methods: {
        toggle () {
        this.$refs['fullscreen'].toggle() // recommended
        // this.fullscreen = !this.fullscreen // deprecated
      },
      fullscreenChange (fullscreen) {
        this.fullscreen = fullscreen
      },
        filter_responses: function() {
            if (this.response_search) {
                this.visible_responses = this.responses.filter(r => {
                    return r.user.name.indexOf(this.response_search) > -1
                });
            } else {
                this.visible_responses = [];
            }
        },
        similarity: function(x, y) {
            return (new difflib.SequenceMatcher(null, x, y)).ratio();
        },
        export_csv: function() {
            console.log(this.responses);

            const rows = this.responses.map(r => {
                return [
                    r.user.id,
                    r.user.name,
                    r.session_id,
                    r.response_info.text].join(',')
            });

            let row_str = 'User Id,User Name,Session Id,Text\n'
            row_str += rows.join('\n');
            
            console.log(row_str);

            const link = document.getElementById('csv_link');
            const file = new Blob([row_str], {type: 'text/csv'});

            link.href = URL.createObjectURL(file);
            link.download = 'question_' + this.question.id + '_responses.csv';
        }
    },
    components: {
        'word-cloud': wordcloud
    },
    computed: {
        word_groups: function() {
            const words = (
                this
                .responses
                .map(r => r.response_info.text)
                .join(' ')
                .split(' '));

            var wordsWithoutStops = sw.removeStopwords(words)

            const groups = wordsWithoutStops.reduce((acc, w) => {
                const i = acc.findIndex(e => e.name === w);

                if (i > -1) {
                    acc[i].value += 1;
                } else {
                    acc.push({'name': w, 'value': 1});
                }

                return acc;
            }, []);

            /*
            const groups =  cluster(
                this.responses.map(r => r.response_info.text),
                this.similarity).groups(0.9);
            */

            console.log('word groups:', groups);
            return groups
        }
    }
};
</script>

<style>
.fade-enter-active, .fade-leave-active {
    transition: all .25s;
}
.fade-enter, .fade-leave-to {
    opacity: 0;
}
.fade-move {
    transition: transform 1s;
}

</style>

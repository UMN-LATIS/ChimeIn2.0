<template>
    <div>
        <div v-if="responses.length > 0">
            <a
                class="waves-effect waves-light btn-small"
                id="csv_link"
                v-on:click="export_csv">
                Export CSV
            </a>
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
            

            <transition-group name="fade">
                <div
                    v-for="(r, i) in responses.slice().reverse().slice(0, 30)"
                    v-bind:key="i">
                    <blockquote v-bind:key="i">
                        {{ r.response_info.text }}
                    </blockquote>
                </div>
            </transition-group>
        </div>

        <div v-else>No Responses Yet!</div>

    </div>
</template>

<script>

import wordcloud from 'vue-wordcloud/src/components/WordCloud'

const stemmer = require('stemmer');
// const wordcloud = require('vue-wordcloud').default;
const difflib = require('difflib');
const cluster = require('set-clustering');

export default {
    props: ['responses', 'question'],
    data: function() {
        return {
            fontSize: [20, 120],
            visible_responses: [],
            response_search: '',
            rotation: {from: 0, to: 0, numOfOrientation: 1 },
            margin: {top: 0, bottom: 0, left: 0, right: 0}
        }
    },
    methods: {
        similarity: function(x, y) {
            return (new difflib.SequenceMatcher(null, x, y)).ratio();
        },
        export_csv: function() {
            console.log(this.responses);

            const rows = this.responses.map(r => {
                return [
                    this.question.anonymous?'':r.user.id,
                    this.question.anonymous?'anonymous':r.user.name,
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
                .match(/\w+/g));

            var wordsWithoutStops = sw.removeStopwords(words)
            // var wordsStemmed = wordsWithoutStops.map(word => stemmer(word).toLowerCase());
            
            const groups = wordsWithoutStops.reduce((acc, w) => {
                if(w.length < 2 || !isNaN(w)) {
                    return acc;
                }
                const i = acc.findIndex(e => e.stem === stemmer(w.toLowerCase()));

                if (i > -1) {
                    acc[i].value += 1;
                } else {
                    acc.push({'name': w, 'value': 1, 'stem': stemmer(w)});
                }

                return acc;
            }, []);

            
            // groups =  cluster(
            //     this.responses.map(r => r.response_info.text),
            //     this.similarity).groups(0.9);
            


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

<template>
<div>
    <div v-if="responses.length > 0">
        <download-csv class="btn btn-info" :data="csv_data">Export CSV</download-csv>

        <word-cloud v-if="!question.question_info.question_responses.hideWordcloud" :data="word_groups" :nameKey="'name'" :valueKey="'value'" :rotate="rotation" :margin="margin" :wordPadding="1" style="width: 100%; height:600px" :fontSize="fontSize">
        </word-cloud>
        <ul>
            <transition-group name="fade">
                <li class="userResponse" v-for="(r, i) in responses.slice().reverse()" v-bind:key="i">
                    <p><strong>{{ r.user.name}}</strong></p>
                    <p>{{ r.response_info.text }}</p>
                </li>
            </transition-group>
        </ul>
    </div>

    <div v-else>No Responses Yet!</div>

</div>
</template>

<script>
import JsonCSV from 'vue-json-csv'

import wordcloud from 'vue-wordcloud/src/components/WordCloud'

const stemmer = require('stemmer');
// const wordcloud = require('vue-wordcloud').default;
const difflib = require('difflib');
const cluster = require('set-clustering');

export default {
    components: {
        "downloadCsv": JsonCSV,
        'word-cloud': wordcloud
    },
    props: ['responses', 'question'],
    data: function () {
        return {
            fontSize: [20, 120],
            visible_responses: [],
            response_search: '',
            rotation: {
                from: 0,
                to: 0,
                numOfOrientation: 1
            },
            margin: {
                top: 0,
                bottom: 0,
                left: 0,
                right: 0
            }
        }
    },
    methods: {
        similarity: function (x, y) {
            return (new difflib.SequenceMatcher(null, x, y)).ratio();
        }
    },
    computed: {
        word_groups: function () {
            const words = (
                this
                .responses
                .map(r => r.response_info.text)
                .join(' ')
                .match(/\w+/g));

            var wordsWithoutStops = sw.removeStopwords(words)
            // var wordsStemmed = wordsWithoutStops.map(word => stemmer(word).toLowerCase());

            const groups = wordsWithoutStops.reduce((acc, w) => {
                if (w.length < 2 || !isNaN(w)) {
                    return acc;
                }
                const i = acc.findIndex(e => e.stem === stemmer(w.toLowerCase()));

                if (i > -1) {
                    acc[i].value += 1;
                } else {
                    acc.push({
                        'name': w,
                        'value': 1,
                        'stem': stemmer(w)
                    });
                }

                return acc;
            }, []);

            // groups =  cluster(
            //     this.responses.map(r => r.response_info.text),
            //     this.similarity).groups(0.9);

            return groups
        },
        csv_data: function () {
            const rows = this.responses.map(r => {
                return {
                    "user": r.user.name,
                    "email": r.user.email,
                    "session": r.session_id,
                    "response": r.response_info.text
                }
            });
            return rows;
        }
    }
};
</script>

<style scoped>
.fade-enter-active,
.fade-leave-active {
    transition: all .25s;
}

.fade-enter,
.fade-leave-to {
    opacity: 0;
}

.fade-move {
    transition: transform 1s;
}

.userResponse {
    list-style: none;
    margin-top: 5px;
    margin-bottom: 5px;
}

.userResponse p {
    margin-top: 0;
    margin-bottom: 0;
}
</style>

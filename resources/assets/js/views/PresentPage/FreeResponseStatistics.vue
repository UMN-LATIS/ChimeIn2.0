<template>
  <div>
    <div v-if="responses.length > 0">
      <div v-if="!word_groups" class="d-flex justify-content-center">
        <div class="spinner-border" role="status">
          <span class="sr-only">Loading...</span>
        </div>
      </div>
      <word-cloud
        v-if="
          !question.question_info.question_responses.hideWordcloud &&
          word_groups
        "
        :data="word_groups"
        :name-key="'name'"
        :value-key="'value'"
        :rotate="rotation"
        :margin="margin"
        :word-padding="1"
        style="width: 100%; height: 600px"
        :font-size="fontSize"
        :word-click="wordClicked"
        data-cy="word-cloud"
      />
      <div
        v-if="!question.question_info.question_responses.hideWordcloud"
        class="form-check form-check-inline"
      >
        <label class="form-check-label align-items-center d-flex">
          <input
            v-model="textProcessing"
            class="form-check-input"
            type="checkbox"
          />
          Natural Language Processing
          <span
            v-tooltip:top="
              'Attempt to detect names, places and organizations. This may slow down word cloud processing.'
            "
            class="ml-1 material-icons md-18"
            >help</span
          >
        </label>
      </div>
      <div v-if="filterWords.length > 0">
        <h2 class="smallHeader">Filtered Words</h2>
        <ul class="filterList">
          <li
            v-for="(word, index) in filterWords"
            :key="index"
            class="align-items-center d-flex filterListItem"
            @click="filterWords.splice(index, 1)"
          >
            {{ word }} <i class="material-icons md-18 md-dark">close</i>
          </li>
        </ul>
      </div>
      <h2 class="smallHeader">Responses</h2>
      <ul>
        <transition-group name="fade">
          <li
            v-for="r in responses.slice().reverse()"
            :key="r.id"
            class="userResponse"
          >
            <p>
              <strong>{{
                question.anonymous ? "Anonymous" : r.user.name
              }}</strong>
            </p>
            <p>{{ r.response_info.text }}</p>
          </li>
        </transition-group>
      </ul>
    </div>

    <div v-else>No Responses Yet!</div>
  </div>
</template>

<script>
import throttle from "lodash/throttle";
import wordcloud from "vue-wordcloud/src/components/WordCloud";
import nlp from "compromise";
import stemmer from "stemmer";
import difflib from "difflib";
import sw from "stopword";

export default {
  components: {
    "word-cloud": wordcloud,
  },
  props: ["responses", "question"],
  data: function () {
    return {
      fontSize: [20, 120],
      visible_responses: [],
      response_search: "",
      rotation: {
        from: 0,
        to: 0,
        numOfOrientation: 1,
      },
      margin: {
        top: 0,
        bottom: 0,
        left: 0,
        right: 0,
      },
      filterWords: [],
      word_groups: null,
      debounced: null,
      textProcessing: false,
      buildtime: null,
    };
  },
  methods: {
    similarity: function (x, y) {
      return new difflib.SequenceMatcher(null, x, y).ratio();
    },
    wordClicked: function (word) {
      this.filterWords.push(word);
    },
    buildWords: throttle(function () {
      var start = performance.now();
      const words = this.responses.map((r) => r.response_info.text).join("\n ");
      let filteredWords = words;
      let topics = [];

      if (this.textProcessing) {
        const doc = nlp(words);

        topics = doc.topics().out("array");

        filteredWords = words;

        for (let i = 0; i < topics.length; i++) {
          // remove topics words from our filtered words
          filteredWords = filteredWords.replace(
            new RegExp(
              "\\b" + topics[i].replace(/[.*+?^${}()|[\]\\]/g, "\\$&") + "\\b",
              "gi"
            ),
            ""
          );
        }
      }

      if (filteredWords.length == 0) {
        return;
      }

      var wordsWithoutStops = sw.removeStopwords(
        filteredWords.match(/"(.*?)"|\w+/g)
      );

      var finalizedWords = wordsWithoutStops
        .concat(topics)
        .filter((w) => !this.filterWords.includes(w));

      const groups = finalizedWords.reduce((acc, w) => {
        if (w.length < 2 || !isNaN(w)) {
          return acc;
        }
        var stem = stemmer(w.toLowerCase().replace(/"/g, ""));
        const i = acc.findIndex((e) => e.stem === stem);

        if (i > -1) {
          acc[i].value += 1;
        } else {
          acc.push({
            name: w.replace(/"/g, ""),
            value: 1,
            stem: stem,
          });
        }

        return acc;
      }, []);

      var end = performance.now();
      this.buildtime = end - start;
      var sortedArray = groups.sort((a, b) => {
        return b.value - a.value;
      });

      this.word_groups = sortedArray.slice(0, 200);
    }, 1000),
  },
  watch: {
    responses: function () {
      setTimeout(() => this.buildWords(), 100);
    },
    filterWords: function () {
      setTimeout(() => this.buildWords(), 100);
    },
    textProcessing: function () {
      setTimeout(() => this.buildWords(), 100);
    },
  },
  mounted: function () {
    // run this in a time to not block initial render
    setTimeout(() => this.buildWords(), 100);
  },
};
</script>

<style scoped>
.fade-enter-active,
.fade-leave-active {
  transition: all 0.25s;
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

.smallHeader {
  font-size: 1.2em;
}

.filterList {
  list-style: none;
  margin-left: 0;
  padding-left: 10px;
}
.filterListItem {
  display: inline-flex !important;
  border-radius: 10px;
  padding: 3px 8px;
  margin-left: 1px;
  margin-right: 1px;
  cursor: pointer;
  background-color: lightblue;
}

.material-icons.md-18 {
  font-size: 18px;
}
.material-icons.md-dark {
  color: rgba(0, 0, 0, 0.54);
}
</style>
<style>
.wordCloud * .text {
  cursor: pointer;
}
</style>

<template>
  <div>
    <div v-if="responses.length > 0">
      <p v-html="highlightedText" class="highlightTextBlock"></p>
    </div>
    <div v-else>No Responses Yet!</div>
  </div>
</template>

<script>
export default {
  props: ["responses", "question"],
  data: function () {
    return {
      highlightedText: "",
    };
  },
  methods: {
    buildTextMap: function () {
      let positionArray = [];
      for (
        let i = 0;
        i < this.question.question_info.question_responses.heatmap_text.length;
        i++
      ) {
        positionArray[i] = 0;
        for (let response of this.responses) {
          if (
            i >= response.response_info.startOffset &&
            i < response.response_info.endOffset
          ) {
            positionArray[i]++;
          }
        }
      }

      const maxCount = Math.max(...positionArray);
      const minCount = Math.min(...positionArray);

      const countRange = maxCount - minCount;

      var outputString = "";
      var insideAnElement = false;
      for (
        var i = 0;
        i < this.question.question_info.question_responses.heatmap_text.length;
        i++
      ) {
        let currentCharacter =
          this.question.question_info.question_responses.heatmap_text[i];
        // we need to not inject new html inside tags, but we need to track our position
        if (currentCharacter == "<") {
          insideAnElement = true;
        }
        if (insideAnElement) {
          outputString = outputString + currentCharacter;
        }

        if (currentCharacter == ">") {
          insideAnElement = false;
          continue;
        }

        if (insideAnElement) {
          continue;
        }

        // super easy way to make a heatmap gradient based on https://stackoverflow.com/questions/12875486/what-is-the-algorithm-to-create-colors-for-a-heatmap

        var colorString = null;
        if (positionArray[i] === 0 || !positionArray[i]) {
          colorString = "white";
        } else {
          colorString =
            "hsl(" +
            (1.0 - positionArray[i] / countRange) * 240 +
            ", 100%, 80%)";
        }

        outputString =
          outputString +
          "<span style='padding-top: 5px; padding-bottom:5px; background-color: " +
          colorString +
          "'>" +
          currentCharacter +
          "</span>";
      }
      this.highlightedText = outputString;
    },
  },
  watch: {
    responses: function () {
      setTimeout(() => this.buildTextMap(), 100);
    },
  },
  mounted: function () {
    // run this in a time to not block initial render
    setTimeout(() => this.buildTextMap(), 100);
  },
};
</script>

<style scoped>
.highlightTextBlock {
  line-height: 2em;
  font-size: 1.5em;
}
</style>

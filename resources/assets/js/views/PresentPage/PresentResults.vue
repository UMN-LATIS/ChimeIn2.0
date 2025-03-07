<template>
  <div class="row">
    <div class="col">
      <h1 v-html="question.text"></h1>

      <template v-if="question.sessions.length > 0">
        <select v-model="selectedSessionId" class="mb-3 form-control col-6">
          <option
            v-for="questionSession in question.sessions"
            :key="questionSession.id"
            :value="questionSession.id"
          >
            {{ questionSession.created_at }}
          </option>
          <option :value="0">All</option>
        </select>

        <component
          :is="question.question_info.question_type + '_statistics'"
          v-if="selected_session"
          :responses="selected_session.responses"
          :question="question"
          :chimeId="chimeId"
          :userLookup="userLookup"
          @removeResponse="removeResponse($event)"
        >
        </component>
      </template>
      <template v-else>
        <p>No sessions yet</p>
      </template>
    </div>
  </div>
</template>

<script lang="ts">
import { defineAsyncComponent, PropType } from "vue";
import * as T from "@/types";
import axios from "@/common/axiosClient";

export default {
  components: {
    slider_response_statistics: defineAsyncComponent(
      () => import("./SliderStatistics.vue")
    ),
    multiple_choice_statistics: defineAsyncComponent(
      () => import("./MultipleChoiceStatistics.vue")
    ),
    image_response_statistics: defineAsyncComponent(
      () => import("./ImageResponseStatistics.vue")
    ),
    free_response_statistics: defineAsyncComponent(
      () => import("./FreeResponseStatistics.vue")
    ),
    text_heatmap_response_statistics: defineAsyncComponent(
      () => import("./TextHeatmapResponseStatistics.vue")
    ),
    no_response_statistics: defineAsyncComponent(
      () => import("./FreeResponseStatistics.vue")
    ),
    heatmap_response_statistics: defineAsyncComponent(
      () => import("./HeatmapResponseStatistics.vue")
    ),
    numeric_response_statistics: defineAsyncComponent(
      () => import("./NumericResponseStatistics.vue")
    ),
  },
  props: {
    sessions: {
      type: Array as PropType<T.Session[]>,
      required: true,
    },
    currentSession: {
      type: Object as PropType<T.Session | null>,
      default: null,
    },
    question: {
      type: Object as PropType<T.Question>,
      required: true,
    },
    chimeId: {
      type: Number,
      required: true,
    },
    userLookup: {
      type: Object as PropType<Map<number, T.User>>,
      required: true,
    },
  },
  emits: ["reload"],
  data: function () {
    return {
      selectedSessionId: 0,
    };
  },
  computed: {
    selected_session: function (): T.Session {
      return (
        this.question.sessions.find((s) => s.id == this.selectedSessionId) ?? {
          id: 0,
          responses: this.question.sessions.flatMap((s) => s.responses),
          question: this.question,
        }
      );
    },
  },
  watch: {
    question: {
      handler() {
        this.selectedSessionId = 0;
      },
      immediate: true,
    },
  },
  methods: {
    removeResponse(response) {
      const url =
        "/api/chime/" +
        this.chimeId +
        "/folder/" +
        this.question.folder_id +
        "/response/" +
        response.id;

      axios
        .delete(url)
        .then(() => {
          this.$emit("reload");
        })
        .catch((err) => {
          console.error(err);
        });
    },
  },
};
</script>

<template>

    <div class="questionContent row">
        <div v-if="question" class="col">
        <h1 v-html="question.text"></h1>
            <component :is="question.question_info.question_type + '_display'" :question="question"/>
        </div>
        <div v-else>
            <span>
                <h4>There aren't any questions in this folder</h4>
            </span>
        </div>

    </div>
</template>

<script>
import DisplayMultipleChoice from "../questions/display/MultipleChoice.vue";
import DisplaySlider from "../questions/display/Slider.vue";
import DisplayTextHeatmapResponse from "../questions/display/TextHeatmapResponse.vue";
import DisplayFreeResponse from "../questions/display/FreeResponse.vue";
import DisplayImageResponse from "../questions/display/ImageResponse.vue";
import DisplayHeatmapResponse from  "../questions/display/HeatmapResponse.vue";

export default {
    props: ['question', 'session'],
    components: {
        "multiple_choice_display": DisplayMultipleChoice,
        "slider_response_display": DisplaySlider,
        "text_heatmap_response_display": DisplayTextHeatmapResponse,
        "free_response_display": DisplayFreeResponse,
        "no_response_display": DisplayFreeResponse,
        "image_response_display": DisplayImageResponse,
        "heatmap_response_display": DisplayHeatmapResponse,
    },
}
</script>

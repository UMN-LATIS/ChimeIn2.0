<template>
    <div class="row responseContainer">
        <div
            class="col-12"
            v-if="response.session && response.session.question">
            <p class="questionText" v-html="response.session.question.text"></p>
            <component :question="response.session.question"
                :disabled="true"
                :response="response"
                :is="response.session.question.question_info.question_type">
            </component>
            <a class="pointer" v-if="chime.students_can_view" v-bind:href="'/chime/' + chime.id + '/folder/' + response.session.question.folder_id + '/present/' + (response.session.question.order - 1)">View Responses</a>
           
            <small class="text-muted" v-if="chime.show_folder_title_to_participants"><strong>Folder</strong>: {{ response.session.question.folder.name }} </small>
            <hr />

        </div>
    </div>
</template>

<script>
import MultipleChoice from "../questions/response/MultipleChoice.vue";
import ImageResponse from "../questions/response/ImageResponse.vue";
import FreeResponse from "../questions/response/FreeResponse.vue";
import TextHeatmapResponse from "../questions/response/TextHeatmapResponse.vue";
import NoResponse from "../questions/response/NoResponse.vue";
import SliderResponse from "../questions/response/Slider.vue";
import HeatmapResponse from "../questions/response/HeatmapResponse.vue";

export default {
    components: {
        'multiple_choice': MultipleChoice,
        'image_response': ImageResponse,
        'text_heatmap_response': TextHeatmapResponse,
        'no_response': NoResponse,
        'free_response': FreeResponse,
        'slider_response': SliderResponse,
        'heatmap_response': HeatmapResponse,
    },
    props: ['response', 'chime']
};
</script>

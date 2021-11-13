<template>
<div class="col-sm-12">
    <div class="overlayContainer">
        <canvas ref="targetCanvas" id="simpleheat"  ></canvas>
        <img ref="targetImage" data-cy="image-heatmap-original" class="img-fluid max-height-image" v-bind:src="'/storage/' + question.question_info.question_responses.image" @load="drawImage">
    </div>
</div>
</template>

<style>

.max-height-image {
    max-height: 70vh
}

canvas {
    position:absolute;
}
img {
    positon:absolute;
}
</style>


<script>
import simpleheat from "simpleheat";

export default {
    props: ['responses', 'question'],
    data: function () {
        return {
        }
    },
    methods: {
        drawImage: function() {

            var targetImage =  this.$refs["targetImage"];
            var targetCanvas =  this.$refs["targetCanvas"];
            targetCanvas.width = targetImage.clientWidth;
            targetCanvas.height = targetImage.clientHeight;
            var scaleFactorX =  targetImage.clientWidth / targetImage.naturalWidth;
            var scaleFactorY = targetImage.clientHeight / targetImage.naturalHeight ;
            console.log(scaleFactorX);
            console.log(scaleFactorY);

            var data = this.responses.map(r => {
                return [r.response_info.image_coordinates.coordinate_x*scaleFactorX,r.response_info.image_coordinates.coordinate_y*scaleFactorY, 0.1] 
            });
            simpleheat('simpleheat').data(data).radius(50, 20).draw();

        }
    },
    watch: {
        responses: function() {
            this.drawImage();
        }
    },
    created() {
        window.addEventListener("resize", this.drawImage);
    },
    destroyed() {
        window.removeEventListener("resize", this.drawImage);
    },
    mounted() {
        
    }
}

</script>

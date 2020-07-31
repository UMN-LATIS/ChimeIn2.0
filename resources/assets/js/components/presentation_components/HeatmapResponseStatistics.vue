<template>
<div class="col-sm-12">
    <download-csv class="btn btn-info" :data="csv_data">Export CSV</download-csv>
    <div class="overlayContainer">
        <canvas ref="targetCanvas" id="simpleheat"  ></canvas>
        <img ref="targetImage" class="img-fluid max-height-image" v-bind:src="'/storage/' + question.question_info.question_responses.image" @load="drawImage">
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
import JsonCSV from 'vue-json-csv'

export default {
    props: ['responses', 'question'],
    components: {
        "downloadCsv": JsonCSV
    },
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
    computed: {

        csv_data: function () {
            const rows = this.responses.map(r => {
                return {
                    "user": this.question.anonymous?"Anonymous":r.user.name,
                    "email": this.question.anonymous?"Anonymous":r.user.email,
                    "session": r.session_id,
                    "response": r.response_info.image_coordinates.coordinate_x + ", " + r.response_info.image_coordinates.coordinate_y
                }
            });
            return rows;
        },

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

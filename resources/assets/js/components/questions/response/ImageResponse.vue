<template>
    <div>
        <div v-if="response.response_info">
            <img class="responsive-img imageContainer" v-bind:src="'/storage/' + response.response_info.image">
        </div>
        <div class="dropbox" v-if="!disabled">
          <input type="file" accept="image/*" @change="attachFile($event.target.name, $event.target.files)" class="form-control-file input-file">
            <p v-if="isInitial">
              Drag your image here to upload<br> or click to browse
            </p>
            <p v-if="!isInitial && !isSaving">
              Drag your image here to upload<br> or click to browse to replace your image
            </p>
            <p v-if="isSaving">
              Uploading file...
            </p>
        </div>
    </div>
</template>

<style>

.imageContainer {
    max-width: 400px;
    max-height: 400px;
}

.dropbox {
    outline: 2px dashed grey; /* the dash box */
    outline-offset: -10px;
    background: lightcyan;
    color: dimgray;
    padding: 10px 10px;
    min-height: 200px; /* minimum height */
    position: relative;
    cursor: pointer;
  }

  .input-file {
    opacity: 0; /* invisible but it's there! */
    width: 100%;
    height: 200px;
    position: absolute;
    cursor: pointer;
  }

  .dropbox:hover {
    background: lightblue; /* when mouse over to the drop zone, change color */
  }

  .dropbox p {
    font-size: 1.2em;
    text-align: center;
    padding: 50px 0;
  }
</style>

<script>
export default {
    props: ['question', 'response', 'disabled', 'chime'],
    data() {
        return {
            "isInitial": response ? false:true,
            "isSaving": false
        }
    },
    methods: {
        attachFile: function(event, fileList){
            this.isSaving = true;
            this.isInitial = false;
            let formData = new FormData();
            Array
            .from(Array(fileList.length).keys())
            .map(x => {
                formData.append("image", fileList[x], fileList[x].name);
            });

            axios.post(
                '/api/chime/'
                + this.chime.id
                + '/image', formData)
            .then(res => {
               const response = {
                question_type: 'image_response',
                image: res.data.image,
                image_name: fileList[0].name
            }
            this.isSaving = false;
            // this.isInitial= true;

            this.$emit('recordresponse', response);
        });

        },

    }
}
</script>

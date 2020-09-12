
<template>
    <div>
        <div v-if="question_responses && question_responses.image">
            <img class="responsive-img imageContainer" v-bind:src="'/storage/' + question_responses.image">
        </div>
        <div class="dropbox">
          <input type="file" accept="image/jpeg, image/png" @change="attachFile($event.target.name, $event.target.files)" class="form-control-file input-file">
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
    props: ['question_responses', 'chime_id'],
    data() {
        return {
            isInitial: this.question_responses ? false:true,
            isSaving: false
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
                + this.chime_id
                + '/image', formData)
            .then(res => {
               const response = {
                image: res.data.image,
                image_name: fileList[0].name
            }
              this.isSaving = false;
            // this.isInitial= true;
              this.$emit('update:question_responses', response);

              this.create_new_response = false;
            });

        },

    }
}
</script>

<template>
    <div>
        <div v-if="response.response_info">
            <img class="responsive-img imageContainer" v-bind:src="'/storage/' + response.response_info.image" v-if="!create_new_response" :alt="altText">
        </div>
        <div class="dropbox" v-if="!disabled && !response.response_info">
          <input type="file" accept="image/jpeg, image/heic, image/png" @change="attachFile($event.target.name, $event.target.files)" class="form-control-file input-file">
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
        <div class="form-group" v-if="showAltText">
          <label for="alt_text">Alt Text for Image:</label>
          <input type="text"
            class="form-control col-6" name="alt_text" id="alt_text" aria-describedby="helpId" placeholder="Explain what people can see in the image" v-model="altText">
            <button @click="saveResponse" class="btn btn-primary">Save</button>
        </div>
        <p v-if="error"><strong>{{ error }}</strong></p>
         <div class="form-group mt-2" >
            <button class="btn btn-primary" @click="showAltText = !showAltText" v-if="!disabled && response && response.response_info">Add Alt Text</button>
            <button class="btn btn-primary" @click="replace" v-if="!disabled && response && response.response_info">Replace Image</button>
            <button class="btn btn-primary" @click="clear" v-if="question.allow_multiple && !disabled && response && response.response_info">Start a New Response</button>
        </div>
    </div>
</template>

<script>
export default {
    props: ['question', 'response', 'disabled', 'chime'],
    data() {
        return {
            isInitial: this.response ? false:true,
            isSaving: false,
            create_new_response: false,
            error: null,
            showAltText:false,
            imageData: {},
            altText: ""
        }
    },
    methods: {
        clear: function() {
          this.altText = "";
          this.showAltText = false;
          this.response.response_info = null;
          this.create_new_response = true;
        },
        replace: function() {
          this.altText = "";
          this.showAltText = false;
          this.response.response_info = null;
        },
        saveResponse: function() {
          const response = {
            question_type: 'image_response',
            image: this.imageData.image,
            image_name: this.imageData.image_name,
            alt_text: this.altText
          }
          console.log(response);
          this.$emit('recordresponse', response, this.create_new_response);
          this.create_new_response = false;
          this.error = null;
        },
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
              this.isSaving = false;
              this.imageData = {image_name: fileList[0].name, image: res.data.image};
              this.saveResponse();
            })
            .catch( err => {
              
              if(err.response) {
                this.error = err.response.data.message;
              }
            });

        },
    },
    watch: {
      response: function(value) {
        if(this.response && this.response.hasOwnProperty('response_info')) {
            if(this.response.response_info.hasOwnProperty('alt_text')) {
              this.altText = this.response.response_info.alt_text;
            }
            
            this.imageData.image = this.response.response_info.image;
            this.imageData.image_name = this.response.response_info.image_name;
        } 
      }
    },
    mounted() {
        if(this.response && this.response.hasOwnProperty('response_info')) {
            if(this.response.response_info.hasOwnProperty('alt_text')) {
              this.altText = this.response.response_info.alt_text;
            }

            this.imageData.image = this.response.response_info.image;
            this.imageData.image_name = this.response.response_info.image_name;
        }
    }
}
</script>


<style>

.imageContainer {
    max-width: 400px;
    max-height: 400px;
}

.dropbox {
    max-width: 300px;
    outline: 2px dashed grey; /* the dash box */
    outline-offset: -10px;
    background: lightcyan;
    color: dimgray;
    padding: 10px 10px;
    min-height: 150px; /* minimum height */
    position: relative;
    cursor: pointer;
  }

  .input-file {
    opacity: 0; /* invisible but it's there! */
    width: 100%;
    height: 150px;
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

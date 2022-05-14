<template>
  <div>
    <div v-if="responses.length > 0" class="row">
      <div class="col">
        <button
          class="btn btn-warning"
          data-toggle="button"
          :class="{ active: filterImages }"
          @click="filterImages = !filterImages"
        >
          Manage Images
        </button>
        <div
          v-if="!filterImages"
          data-cy="image-responses"
          class="response-list"
        >
          <div v-for="(image, index) of images" :key="index">
            <img class="response-image" :src="image.src" :alt="image.alt" />
          </div>
          <!-- <Lightbox
            v-if="!filterImages"
            :id="'lightbox' + question.id"
            :images="images"
            image_class="img-responsive img-rounded"
            :options="options"
          >
          </Lightbox> -->
        </div>

        <table v-if="filterImages" class="table" data-cy="responses-table">
          <thead>
            <tr>
              <th scope="col">Image</th>
              <th scope="col">Alt</th>
              <th scope="col">Participant</th>
              <th scope="col">Remove</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="response in responses" :key="response.id">
              <td style="width: 15%">
                <img
                  :src="'/storage/' + response.response_info.image"
                  :alt="response.response_info.image_alt || ''"
                  class="img-fluid"
                />
              </td>
              <td>
                {{ response.response_info.image_alt || "" }}
              </td>
              <td>
                {{ question.anonymous ? "Anonymous" : response.user.name }}
              </td>
              <td>
                <button class="btn btn-danger" @click="removeImage(response)">
                  Delete
                </button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <div v-else>No Responses Yet!</div>
  </div>
</template>

<script>
// import Lightbox from "vue-simple-lightbox";

export default {
  // components: {
  //   Lightbox,
  // },
  props: {
    responses: { type: Array, required: true },
    question: { type: Object, required: true },
    chimeId: { type: Number, required: true },
  },
  emits: ["removeResponse"],
  data: function () {
    return {
      visible_responses: [],
      response_search: "",
      options: {
        closeText: "X",
      },
      filterImages: false,
    };
  },
  computed: {
    images: function () {
      return this.responses.map((response) => {
        return {
          src: "/storage/" + response.response_info.image,
          title: response.response_info.image_alt || "",
          alt: response.response_info.image_alt || "",
        };
      });
    },
  },
  methods: {
    removeImage: function (response) {
      this.$emit("removeResponse", response);
    },
  },
};
</script>

<style scoped>
.response-list {
  margin: 1rem 0;
  display: flex;
  flex-wrap: wrap;
  gap: 1rem;
}

.response-image {
  width: 20rem;
  max-width: 100%;
  border-radius: 0.25rem;
}
</style>

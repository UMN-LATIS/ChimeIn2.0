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
            <button
              class="response-image__button"
              @click="
                activeImageIndex = index;
                isLightboxOpen = true;
              "
            >
              <img
                class="response-image__img"
                :src="image.src"
                :alt="image.alt"
              />
            </button>
          </div>
          <VueEasyLightbox
            v-if="!filterImages"
            :visible="isLightboxOpen"
            :imgs="images"
            :index="activeImageIndex"
            image_class="img-responsive img-rounded"
            @hide="isLightboxOpen = false"
          >
          </VueEasyLightbox>
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
                {{
                  question.anonymous
                    ? "Anonymous"
                    : userLookup.get(response.user_id)?.name
                }}
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

<script lang="ts">
import VueEasyLightbox from "vue-easy-lightbox";
import * as T from "@/types";
import { PropType } from "vue";

export default {
  components: {
    VueEasyLightbox,
  },
  props: {
    responses: { type: Array as PropType<T.ImageResponse[]>, required: true },
    question: {
      type: Object as PropType<T.ImageResponseQuestion>,
      required: true,
    },
    chimeId: { type: Number, required: true },
    userLookup: {
      type: Object as PropType<Map<number, T.User>>,
      required: true,
    },
  },
  emits: ["removeResponse"],
  data: function () {
    return {
      visible_responses: [],
      response_search: "",
      filterImages: false,
      isLightboxOpen: false,
      activeImageIndex: 0,
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

.response-image__button {
  transition: transform 0.3s;
  background: none;
  border: 1px solid #ccc;
  border-radius: 0.25rem;
  width: 10rem;
  height: 10rem;
  padding: 0;
  overflow: hidden;
}
.response-image__button:hover {
  transform: scale3d(1.1, 1.1, 1);
}

.response-image__img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}
</style>

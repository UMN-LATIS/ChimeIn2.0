<template>
  <div>
    <div class="row form-group">
      <label for="left_choice_text" class="col-form-label col-sm-2"
        >Left Label:
      </label>
      <div class="input-group col-sm-10">
        <input
          id="left_choice_text"
          v-model="left_choice_text"
          type="text"
          class="validate form-control"
          @input="emitChanges"
        />
      </div>
    </div>
    <div class="row form-group">
      <label for="right_choice_text" class="col-form-label col-sm-2"
        >Right Label:</label
      >
      <div class="input-group col-sm-10">
        <input
          id="right_choice_text"
          v-model="right_choice_text"
          type="text"
          class="validate form-control"
          @input="emitChanges"
        />
      </div>
    </div>
    <div class="row form-group">
      <label for="right_choice_text" class="col-form-label col-sm-2"
        >Range Type:</label
      >
      <div class="input-group col-sm-10">
        <select
          id="range_type"
          v-model="range_type"
          class="form-control"
          :disabled="range_disabled"
          @change="emitChanges"
        >
          <option>Qualitative</option>
          <option>Numeric (Linear)</option>
        </select>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  // eslint-disable-next-line vue/prop-name-casing, vue/require-prop-types
  props: ["question_responses"],
  emits: ["update:question_responses"],
  data: function () {
    return {
      left_choice_text: this.question_responses.left_choice_text,
      right_choice_text: this.question_responses.right_choice_text,
      range_type: this.question_responses.range_type,
      range_disabled: false,
    };
  },
  watch: {
    left_choice_text() {
      this.checkValues();
    },
    right_choice_text() {
      this.checkValues();
    },
  },
  mounted: function () {
    this.checkValues();
  },
  methods: {
    checkValues: function () {
      if (isNaN(this.left_choice_text) || isNaN(this.right_choice_text)) {
        this.range_type = "Qualitative";
        this.range_disabled = true;
      } else {
        this.range_disabled = false;
      }
    },
    emitChanges: function () {
      this.$emit("update:question_responses", {
        left_choice_text: this.left_choice_text,
        right_choice_text: this.right_choice_text,
        range_type: this.range_type,
      });
    },
  },
};
</script>

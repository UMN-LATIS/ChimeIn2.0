<template>
  <div>
    <p>
      It looks like you've used ChimeIn with this course in the past.
      <b>If you cloned a Canvas course from a previous semester</b>, you can use
      the selector below to import those questions.You should still click
      through each of your Chime assignments from Canvas to ensure they're
      linked correctly.
    </p>
    <div class="form-group">
      <label for="import_chime">Import From:</label>
      <select id="import_chime" class="form-control" name="import_chime">
        <option
          v-for="chime in similar_chimes"
          :key="chime.id"
          :value="chime.id"
        >
          {{ chime.name }}
        </option>
      </select>
    </div>
    <button class="btn btn-secondary" @click="$emit('new_chime')">
      Setup as a new Chime
    </button>
    <button type="submit" class="btn btn-primary">Import</button>
  </div>
</template>

<script>
export default {
  // eslint-disable-next-line vue/require-prop-types, vue/prop-name-casing
  props: ["similar_chimes"],
  emits: ["new_chime"],
};
</script>

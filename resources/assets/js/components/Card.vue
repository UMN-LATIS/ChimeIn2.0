<template>
  <div class="chime-card">
    <div v-if="icon" class="chime-card__icon" :class="iconClass">
      <i class="material-icons">{{ icon }}</i>
    </div>

    <div class="chime-card__default">
      <slot />
    </div>

    <div class="chime-card__actions">
      <slot name="actions" />
    </div>
  </div>
</template>
<script>
export default {
  props: {
    icon: {
      type: String,
    },
    iconClass: {
      type: String,
    },
  },
};
</script>

<style></style>

<style lang="scss">
.chime-card {
  border: 1px solid #ddd;
  border-radius: 0.25rem;
  background: #fafafa;
  display: flex;
  align-items: center;
  line-height: 1;
  transition: all 0.3s ease-out;
  margin: 0.5rem 0;

  a {
    color: #333;
  }

  a:hover {
    text-decoration: none;
  }
}
.chime-card:hover {
  box-shadow: 0 0.25rem 0.5rem hsla(0, 0%, 0%, 0.1);
}

.chime-card__icon {
  padding: 0.5rem 1rem;
}

/* if no icon, add padding */
.chime-card__default:first-child {
  padding-left: 1.5rem;
}

.chime-card__default {
  flex: 1;
  font-size: 1rem;
  margin: 0.75rem 0;

  h1,
  h2,
  h3,
  h4,
  h5 {
    margin: 0;
    font-size: 1.5rem;
    line-height: 1;
  }
}

.chime-card__actions {
  display: flex;
}
</style>

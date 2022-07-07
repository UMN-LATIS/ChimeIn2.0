<template>
  <div class="chime-card">
    <div v-if="icon" class="chime-card__icon" :class="iconClass">
      <i class="material-icons">{{ icon }}</i>
    </div>

    <div class="chime-card__default">
      <slot />
    </div>

    <aside class="chime-card__aside">
      <div class="chime-card__actions">
        <slot name="actions" />
      </div>
      <div class="chime-card__additional-info">
        <slot name="additional-info" />
      </div>
    </aside>
  </div>
</template>
<script>
export default {
  props: {
    icon: {
      type: String,
      default: "",
    },
    iconClass: {
      type: String,
      default: "",
    },
  },
};
</script>

<style></style>

<style lang="scss">
.chime-card {
  border: 1px solid #ddd;
  border-radius: 0.25rem;
  background: #fff;
  display: flex;
  align-items: flex-start;
  line-height: 1;
  transition: all 0.1s ease-out;
  margin-bottom: 0.5rem;

  a {
    color: var(--black);
  }

  a:hover {
    text-decoration: none;
  }
}
.chime-card:hover {
  box-shadow: 0 0.25rem 0.5rem hsla(0, 0%, 0%, 0.1);
}

.chime-card__icon {
  padding: 1rem;
}

/* if no icon, add padding */
.chime-card__default:first-child {
  padding-left: 1.5rem;
}

.chime-card__default {
  flex: 1;
  font-size: 1rem;
  margin: 1rem 0;
  min-width: 0;

  h1,
  h2,
  h3,
  h4,
  h5 {
    margin: 0;
    font-size: 1.5rem;
    line-height: 1.1;
  }

  @media (max-width: 25rem) {
    h1,
    h2,
    h3,
    h4,
    h5 {
      font-size: 1rem;
      line-height: 1.2;
    }
  }
}

.chime-card__aside {
  display: flex;
  flex-direction: column;
  align-self: stretch;
}

.chime-card__actions {
  display: flex;
  align-items: flex-start;
  padding: 0.25rem 0.5rem;
}

.chime-card__additional-info {
  flex-grow: 1;
}
</style>

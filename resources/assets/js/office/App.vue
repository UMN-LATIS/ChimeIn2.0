<template>
  <div class="chimein-office-content p-3">
    <SignInPanel v-if="state === 'needsAuth'" @signed-in="onSignedIn" />

    <ChimePicker
      v-else-if="state === 'needsChime'"
      :browse-token="browseToken!"
      @chosen="onChimeChosen"
    />

    <QuestionPicker
      v-else-if="state === 'needsQuestion'"
      :token="chimeToken!"
      :chime-id="chimeId!"
      :widget-id="widgetId"
      @chosen="onQuestionChosen"
    />

    <BoundQuestion
      v-else-if="state === 'bound'"
      :token="chimeToken!"
      :chime-id="chimeId!"
      :question-id="questionId!"
      @reconnect="state = 'needsQuestion'"
    />

    <p v-else>Loading&hellip;</p>
  </div>
</template>

<script setup lang="ts">
import { onMounted, ref } from "vue";
import SignInPanel from "./SignInPanel.vue";
import ChimePicker from "./ChimePicker.vue";
import QuestionPicker from "./QuestionPicker.vue";
import BoundQuestion from "./BoundQuestion.vue";
import { OfficeChime, issueChimeToken } from "./lib/api";
import { getStoredBrowseToken, rememberLastChimeId } from "./lib/auth";
import {
  readDeckBinding,
  writeDeckBinding,
  readInstance,
  writeInstance,
  registerWidget,
  newWidgetId,
} from "./lib/storage";

type State = "loading" | "needsAuth" | "needsChime" | "needsQuestion" | "bound";

const state = ref<State>("loading");
const browseToken = ref<string | null>(null);
const chimeToken = ref<string | null>(null);
const chimeId = ref<number | null>(null);
const questionId = ref<number | null>(null);
const widgetId = ref(newWidgetId());

onMounted(async () => {
  // instance settings win: this widget was already bound in a previous session
  const instance = readInstance();
  if (instance) {
    widgetId.value = instance.widgetId;
  }

  const deck = await readDeckBinding();
  if (deck) {
    chimeToken.value = deck.token;
    chimeId.value = deck.chimeId;

    if (instance && instance.questionId) {
      questionId.value = instance.questionId;
      state.value = "bound";
      return;
    }

    state.value = "needsQuestion";
    return;
  }

  const stored = getStoredBrowseToken();
  if (stored) {
    browseToken.value = stored.token;
    state.value = "needsChime";
    return;
  }

  state.value = "needsAuth";
});

function onSignedIn(token: string) {
  browseToken.value = token;
  state.value = "needsChime";
}

async function onChimeChosen(chime: OfficeChime) {
  const { token } = await issueChimeToken(browseToken.value!, chime.id);
  chimeToken.value = token;
  chimeId.value = chime.id;
  rememberLastChimeId(chime.id);

  await writeDeckBinding({ host: window.location.origin, chimeId: chime.id, token });

  state.value = "needsQuestion";
}

async function onQuestionChosen(choice: { folderId: number; questionId: number }) {
  questionId.value = choice.questionId;

  await writeInstance({
    widgetId: widgetId.value,
    folderId: choice.folderId,
    questionId: choice.questionId,
  });
  await registerWidget({
    widgetId: widgetId.value,
    folderId: choice.folderId,
    questionId: choice.questionId,
  });

  state.value = "bound";
}
</script>

<style>
html,
body,
#office-content {
  min-height: 100%;
}

body.chimein-office {
  margin: 0;
  background: #f6f8fb;
  color: #233142;
  font-size: 14px;
}

.chimein-office-content {
  min-height: 100vh;
}

.chimein-office-content.p-3 {
  padding: 0.75rem !important;
}
</style>

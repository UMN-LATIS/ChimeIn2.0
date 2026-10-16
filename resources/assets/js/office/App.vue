<template>
  <div class="chimein-office-content p-3">
    <SignInPanel v-if="state === 'needsAuth'" @signedIn="onSignedIn" />

    <ChimePicker v-else-if="state === 'needsChime'" :browseToken="browseToken!" @chosen="onChimeChosen" />

    <QuestionPicker v-else-if="state === 'needsQuestion'" :token="chimeToken!" :chimeId="chimeId!" :widgetId="widgetId"
      @chosen="onQuestionChosen" />

    <BoundQuestion v-else-if="state === 'bound'" :key="chimeToken" :token="chimeToken!" :chimeId="chimeId!"
      :questionId="questionId!" @reconnect="onReconnect" @changeQuestion="onChangeQuestion" />

    <p v-else-if="state === 'error'" class="text-danger">
      {{ connectionError }}
      <button class="btn btn-sm btn-outline-primary ms-2" @click="retryConnection">Retry</button>
    </p>

    <p v-else>Loading&hellip;</p>
  </div>
</template>

<script setup lang="ts">
import { onMounted, ref } from "vue";
import SignInPanel from "./SignInPanel.vue";
import ChimePicker from "./ChimePicker.vue";
import QuestionPicker from "./QuestionPicker.vue";
import BoundQuestion from "./BoundQuestion.vue";
import { OfficeChime, issueChimeToken, UnauthorizedError } from "./lib/api";
import { forgetBrowseToken, getStoredBrowseToken, rememberLastChimeId } from "./lib/auth";
import type { StoredBrowseToken } from "./lib/auth";
import {
  clearInstance,
  forgetChimeToken,
  getStoredChimeToken,
  readDeckBinding,
  writeDeckBinding,
  readInstance,
  writeInstance,
  registerWidget,
  newWidgetId,
  rememberChimeToken,
} from "./lib/storage";

type State = "loading" | "needsAuth" | "needsChime" | "needsQuestion" | "bound" | "error";

const state = ref<State>("loading");
const browseToken = ref<string | null>(null);
const chimeToken = ref<string | null>(null);
const chimeId = ref<number | null>(null);
const questionId = ref<number | null>(null);
const widgetId = ref(newWidgetId());
const connectionError = ref("");

onMounted(async () => {
  // instance settings win: this widget was already bound in a previous session
  const instance = readInstance();
  if (instance) {
    widgetId.value = instance.widgetId;
  }

  const deck = await readDeckBinding();
  if (deck) {
    chimeId.value = deck.chimeId;
  }

  if (deck && instance?.questionId) {
    questionId.value = instance.questionId;
  }

  const stored = getStoredBrowseToken();
  if (deck && stored) {
    await connectToSavedChime(stored);
    return;
  }

  if (stored) {
    browseToken.value = stored.token;
    state.value = "needsChime";
    return;
  }

  state.value = "needsAuth";
});

async function onSignedIn(stored: StoredBrowseToken) {
  browseToken.value = stored.token;
  if (chimeId.value !== null) {
    await connectToSavedChime(stored, true);
    return;
  }
  state.value = "needsChime";
}

async function onReconnect() {
  const stored = getStoredBrowseToken();
  if (!stored) {
    state.value = "needsAuth";
    return;
  }
  await connectToSavedChime(stored, true);
}

async function onChangeQuestion() {
  await clearInstance();
  questionId.value = null;
  state.value = "needsQuestion";
}

async function connectToSavedChime(stored: StoredBrowseToken, forceRenew = false) {
  const selectedChimeId = chimeId.value;
  if (selectedChimeId === null) {
    state.value = "needsChime";
    return;
  }

  browseToken.value = stored.token;
  connectionError.value = "";

  const cached = forceRenew ? null : getStoredChimeToken(stored.user.id, selectedChimeId);
  if (cached) {
    chimeToken.value = cached.token;
    state.value = questionId.value === null ? "needsQuestion" : "bound";
    return;
  }

  try {
    const issued = await issueChimeToken(stored.token, selectedChimeId);
    chimeToken.value = issued.token;
    rememberChimeToken(stored.user.id, selectedChimeId, {
      token: issued.token,
      expiresAt: issued.expires_at,
    });
    state.value = questionId.value === null ? "needsQuestion" : "bound";
  } catch (error) {
    if (error instanceof UnauthorizedError && error.status === 401) {
      forgetBrowseToken();
      browseToken.value = null;
      state.value = "needsAuth";
    } else if (error instanceof UnauthorizedError && error.status === 403) {
      forgetChimeToken(stored.user.id, selectedChimeId);
      chimeToken.value = null;
      state.value = "needsChime";
    } else {
      connectionError.value = "Couldn't reconnect to ChimeIn.";
      state.value = "error";
    }
  }
}

async function onChimeChosen(chime: OfficeChime) {
  await clearInstance();
  chimeToken.value = null;
  questionId.value = null;
  chimeId.value = chime.id;
  rememberLastChimeId(chime.id);

  const stored = getStoredBrowseToken();
  if (!stored) {
    state.value = "needsAuth";
    return;
  }

  await connectToSavedChime(stored, true);
  if (chimeToken.value) {
    await writeDeckBinding({ host: window.location.origin, chimeId: chime.id });
  }
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

async function retryConnection() {
  const stored = getStoredBrowseToken();
  if (!stored) {
    state.value = "needsAuth";
    return;
  }
  await connectToSavedChime(stored, true);
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

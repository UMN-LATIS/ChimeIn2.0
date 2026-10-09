/**
 * The three places the add-in keeps state.
 *
 * - machine:  localStorage, never written to the .pptx. Holds both token types.
 * - deck:     PowerPoint presentation tags, shared by every widget in the file.
 * - instance: this widget's own settings bag, private to it.
 */

const DECK_HOST = "CHIMEIN_HOST";
const DECK_CHIME_ID = "CHIMEIN_CHIME_ID";
const DECK_WIDGET_PREFIX = "CHIMEIN_WIDGET_";
const CHIME_TOKEN_PREFIX = "chimein:chimeToken";

export interface DeckBinding {
  host: string;
  chimeId: number;
}

export interface StoredChimeToken {
  token: string;
  expiresAt: string | null;
}

export interface InstanceBinding {
  widgetId: string;
  folderId: number;
  questionId: number;
}

/* ------------------------------------------------------------------ machine */

function machineKey(key: string): string {
  // Office partitions storage on the web; without the prefix two hosts collide.
  const partition = Office.context.partitionKey;
  return partition ? `${partition}:${key}` : key;
}

export function readMachine<T>(key: string): T | null {
  try {
    const raw = window.localStorage.getItem(machineKey(key));
    return raw ? (JSON.parse(raw) as T) : null;
  } catch {
    return null;
  }
}

export function writeMachine(key: string, value: unknown): void {
  try {
    window.localStorage.setItem(machineKey(key), JSON.stringify(value));
  } catch {
    /* private browsing or storage disabled; the add-in still works, just re-asks */
  }
}

export function clearMachine(key: string): void {
  try {
    window.localStorage.removeItem(machineKey(key));
  } catch {
    /* ignore */
  }
}

/* --------------------------------------------------------------------- deck */

async function withTags<T>(
  fn: (tags: PowerPoint.TagCollection, context: PowerPoint.RequestContext) => T
): Promise<T> {
  return PowerPoint.run(async (context) => {
    const result = fn(context.presentation.tags, context);
    await context.sync();
    return result;
  });
}

export async function readDeckBinding(): Promise<DeckBinding | null> {
  try {
    return await PowerPoint.run(async (context) => {
      const collection = context.presentation.tags;
      collection.load("items/key,items/value");
      await context.sync();
      const tags = new Map(collection.items.map((tag) => [tag.key, tag.value]));

      const chimeId = Number(tags.get(DECK_CHIME_ID));
      const host = tags.get(DECK_HOST);

      if (!chimeId || !host) return null;
      return { host, chimeId };
    });
  } catch {
    return null;
  }
}

export async function writeDeckBinding(binding: DeckBinding): Promise<void> {
  await withTags((tags) => {
    tags.add(DECK_HOST, binding.host);
    tags.add(DECK_CHIME_ID, String(binding.chimeId));
  });
}

function chimeTokenKey(userId: number, chimeId: number): string {
  return `${CHIME_TOKEN_PREFIX}:${userId}:${chimeId}`;
}

export function getStoredChimeToken(userId: number, chimeId: number): StoredChimeToken | null {
  const key = chimeTokenKey(userId, chimeId);
  const stored = readMachine<StoredChimeToken>(key);
  if (!stored?.token) return null;

  if (stored.expiresAt && new Date(stored.expiresAt) <= new Date()) {
    clearMachine(key);
    return null;
  }

  return stored;
}

export function rememberChimeToken(
  userId: number,
  chimeId: number,
  stored: StoredChimeToken
): void {
  writeMachine(chimeTokenKey(userId, chimeId), stored);
}

export function forgetChimeToken(userId: number, chimeId: number): void {
  clearMachine(chimeTokenKey(userId, chimeId));
}

/**
 * Every widget records what it shows so siblings can default to an unused question,
 * and so a future taskpane can inventory the deck.
 */
export async function registerWidget(binding: InstanceBinding): Promise<void> {
  await withTags((tags) => {
    tags.add(
      DECK_WIDGET_PREFIX + binding.widgetId,
      JSON.stringify({ folderId: binding.folderId, questionId: binding.questionId })
    );
  });
}

export async function readUsedQuestionIds(excludeWidgetId?: string): Promise<number[]> {
  try {
    return await PowerPoint.run(async (context) => {
      const collection = context.presentation.tags;
      collection.load("items/key,items/value");
      await context.sync();

      return collection.items
        .filter(
          (tag) =>
            tag.key.startsWith(DECK_WIDGET_PREFIX) &&
            tag.key !== DECK_WIDGET_PREFIX + (excludeWidgetId ?? "")
        )
        .map((tag) => {
          try {
            return JSON.parse(tag.value).questionId as number;
          } catch {
            return null;
          }
        })
        .filter((id): id is number => typeof id === "number");
    });
  } catch {
    return [];
  }
}

/* ----------------------------------------------------------------- instance */

export function readInstance(): InstanceBinding | null {
  const raw = Office.context.document.settings.get("chimein:binding");
  return raw ? (raw as InstanceBinding) : null;
}

export function writeInstance(binding: InstanceBinding): Promise<void> {
  Office.context.document.settings.set("chimein:binding", binding);
  return new Promise((resolve, reject) => {
    Office.context.document.settings.saveAsync((result) => {
      result.status === Office.AsyncResultStatus.Succeeded
        ? resolve()
        : reject(new Error(result.error.message));
    });
  });
}

export function clearInstance(): Promise<void> {
  Office.context.document.settings.remove("chimein:binding");
  return new Promise((resolve) => {
    Office.context.document.settings.saveAsync(() => resolve());
  });
}

/* -------------------------------------------------------------------- misc */

export function newWidgetId(): string {
  return "w" + Math.random().toString(36).slice(2, 10) + Date.now().toString(36);
}

/**
 * The three places the add-in keeps state.
 *
 * - machine:  localStorage, never written to the .pptx. Holds the browse token.
 * - deck:     PowerPoint presentation tags, shared by every widget in the file.
 * - instance: this widget's own settings bag, private to it.
 */

const DECK_HOST = "CHIMEIN_HOST";
const DECK_CHIME_ID = "CHIMEIN_CHIME_ID";
const DECK_TOKEN = "CHIMEIN_TOKEN";
const DECK_WIDGET_PREFIX = "CHIMEIN_WIDGET_";

export interface DeckBinding {
  host: string;
  chimeId: number;
  token: string;
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
    const tags = await PowerPoint.run(async (context) => {
      const collection = context.presentation.tags;
      collection.load("items/key,items/value");
      await context.sync();
      return new Map(collection.items.map((t) => [t.key, t.value]));
    });

    const chimeId = Number(tags.get(DECK_CHIME_ID));
    const token = tags.get(DECK_TOKEN);
    const host = tags.get(DECK_HOST);

    if (!chimeId || !token || !host) return null;
    return { host, chimeId, token };
  } catch {
    return null;
  }
}

export async function writeDeckBinding(binding: DeckBinding): Promise<void> {
  await withTags((tags) => {
    tags.add(DECK_HOST, binding.host);
    tags.add(DECK_CHIME_ID, String(binding.chimeId));
    tags.add(DECK_TOKEN, binding.token);
  });
}

export async function clearDeckBinding(): Promise<void> {
  await withTags((tags) => {
    [DECK_HOST, DECK_CHIME_ID, DECK_TOKEN].forEach((key) => tags.delete(key));
  });
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

/**
 * "read" means Slide Show or Reading View. PowerPoint on the web never fires
 * ActiveViewChanged, so callers must re-check this on load rather than rely on it.
 */
export function getActiveView(): Promise<"edit" | "read"> {
  return new Promise((resolve) => {
    Office.context.document.getActiveViewAsync((result) => {
      resolve(result.status === Office.AsyncResultStatus.Succeeded && result.value === "read"
        ? "read"
        : "edit");
    });
  });
}

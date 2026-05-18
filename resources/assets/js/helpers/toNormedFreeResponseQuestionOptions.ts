import type { FreeResponseQuestionInfo, NormedFreeResponseQuestionOptions } from "@/types";

const VALID_DISPLAY_TYPES = ["default", "code"] as const;

const DEFAULT_OPTIONS: NormedFreeResponseQuestionOptions = {
  displayType: "default",
  hideWordcloud: false,
};

/**
 * Normalizes the question_responses for a free response question
 * to ensure it has the expected structure and default values.
 *
 * question_responses may be `[]` instead of an object
 * if it's never been edited.
 */
export function toNormedFreeResponseQuestionOptions(
  raw: FreeResponseQuestionInfo["question_responses"]
): NormedFreeResponseQuestionOptions {
  if (Array.isArray(raw) || typeof raw !== "object") {
    return DEFAULT_OPTIONS;
  }

  return {
    displayType: VALID_DISPLAY_TYPES.includes(raw.displayType as typeof VALID_DISPLAY_TYPES[number])
      ? raw.displayType!
      : DEFAULT_OPTIONS.displayType,
    hideWordcloud: raw.hideWordcloud ?? DEFAULT_OPTIONS.hideWordcloud,
  };
}


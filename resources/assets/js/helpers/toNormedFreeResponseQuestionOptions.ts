import type { FreeResponseQuestionInfo, NormedFreeResponseQuestionOptions } from "@/types";

/**
 * Normalizes the question_responses for a free
 * response question to ensure it has the
 * expected structure and default values.
 */
export function toNormedFreeResponseQuestionOptions(
  rawQuestionOptions: FreeResponseQuestionInfo["question_responses"]
): NormedFreeResponseQuestionOptions {
  const defaultOptions = {
    displayType: "default",
    hideWordcloud: false,
  } satisfies NormedFreeResponseQuestionOptions;

  // question_responses may be an empty array
  // instead of an object if it's never been
  // edited, so check for that and return the
  // default options
  if (Array.isArray(rawQuestionOptions) || typeof rawQuestionOptions !== "object") {
    return defaultOptions;
  }

  // handle unexpected or unset displayType values by falling back to the default
  const validDisplayTypes = ["default", "code"];
  if (!rawQuestionOptions.displayType || !validDisplayTypes.includes(rawQuestionOptions.displayType)) {
    rawQuestionOptions.displayType = defaultOptions.displayType;
  }
  
  return {
    ...defaultOptions,
    ...rawQuestionOptions,
  };
}


import {
  NormalizedNumericQuestionOptions,
  NumericResponseQuestionInfo,
} from "@/types";

export function isNumericResponseQuestionOptions(
  question_responses:
    | NumericResponseQuestionInfo["question_responses"]
    | unknown
): question_responses is NormalizedNumericQuestionOptions {
  return (
    typeof question_responses === "object" &&
    question_responses !== null &&
    "chart_type" in question_responses &&
    "x_axis_label" in question_responses &&
    "y_axis_label" in question_responses
  );
}

/**
 * Normalizes options for numeric questions returned from the server
 * so that they're in the correct shape and have truthy values for the labels.
 * @see Issue#948 for more context
 */
export function normalizeNumericQuestionOptions(
  options: NumericResponseQuestionInfo["question_responses"]
): NormalizedNumericQuestionOptions {
  const defaultOptions: NormalizedNumericQuestionOptions = {
    chart_type: "bar",
    x_axis_label: "X",
    y_axis_label: "Y",
  };

  // options might be `[]` if no chart type or label was set
  if (!isNumericResponseQuestionOptions(options)) {
    return defaultOptions;
  }

  // label could be an empty string or null. If so, set it to the default.
  for (const key in options) {
    if (!options[key]) {
      options[key] = defaultOptions[key];
    }
  }
  return options;
}

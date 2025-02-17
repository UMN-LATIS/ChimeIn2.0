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

export function getDefaultNumericQuestionOptions(): NormalizedNumericQuestionOptions {
  return {
    chart_type: "bar",
    x_axis_label: "X",
    y_axis_label: "Y",
  };
}

export function normalizeNumericQuestionOptions(
  question_responses: NumericResponseQuestionInfo["question_responses"]
): NormalizedNumericQuestionOptions {
  return isNumericResponseQuestionOptions(question_responses)
    ? question_responses
    : getDefaultNumericQuestionOptions();
}

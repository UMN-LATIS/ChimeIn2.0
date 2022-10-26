import { ChimeFolderParticipationResponseItem } from "../../types";

function calculateQuestionScore(
  questionResponses: ChimeFolderParticipationResponseItem[],
  valueOfIncorrectResponse = 0
) {
  return questionResponses.reduce(
    (acc, response) =>
      response.is_correct
        ? Math.max(acc, 1)
        : Math.max(acc, valueOfIncorrectResponse),
    0
  );
}

export default function getQuestionScoreForUser({
  questionId,
  responses,
}: {
  questionId: number;
  responses: ChimeFolderParticipationResponseItem[];
}): number {
  const questionResponsesForUser = responses.filter(
    (response) => response.question_id === questionId
  );

  return calculateQuestionScore(questionResponsesForUser);
}

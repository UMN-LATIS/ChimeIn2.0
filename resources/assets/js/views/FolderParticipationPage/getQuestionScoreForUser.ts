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
  userId,
  questionId,
  responses,
  valueForIncorrect,
}: {
  userId: number;
  questionId: number;
  responses: ChimeFolderParticipationResponseItem[];
  valueForIncorrect: number;
}): number {
  const questionResponsesForUser = responses.filter(
    (response) =>
      response.user_id === userId && response.question_id === questionId
  );

  return calculateQuestionScore(questionResponsesForUser, valueForIncorrect);
}

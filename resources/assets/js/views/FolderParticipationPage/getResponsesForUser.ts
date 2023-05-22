import { ChimeFolderParticipationResponseItem } from "../../types";

export default function getResponsesForUser(
  userId: number,
  responses: ChimeFolderParticipationResponseItem[]
) {
  return responses.filter((response) => response.user_id === userId);
}

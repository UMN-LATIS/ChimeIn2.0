import { axiosClient as axios } from "../common/axios";
import type { FolderWithQuestions, SuccessOrErrorMessage } from "../types";

export function getFolderWithQuestions({
  chimeId,
  folderId,
}: {
  chimeId: number;
  folderId: number;
}): Promise<FolderWithQuestions> {
  return axios
    .get(`/api/chime/${chimeId}/folder/${folderId}?include_questions=true`)
    .then((res) => {
      console.log({ res });
      return {
        ...res.data,
        // sort the questions within the folder by their order
        questions: res.data.questions.sort((a, b) => a.order - b.order),
      };
    })
    .catch((err) => console.error(err));
}

export function removeResponsesForQuestion({
  chimeId,
  folderId,
  questionId,
}: {
  chimeId: number;
  folderId: number;
  questionId: number;
}): Promise<SuccessOrErrorMessage> {
  return axios
    .delete(
      `/api/chime/${chimeId}/folder/${folderId}/question/${questionId}/responses`
    )
    .then((res) => {
      return res.data;
    })
    .catch((err) => console.error(err));
}

export function updateQuestionOrderInFolder(
  {
    chimeId,
    folderId,
  }: {
    chimeId: number;
    folderId: number;
  },
  newQuestionOrder: Array<{
    id: number;
    order: number;
  }>
): Promise<SuccessOrErrorMessage> {
  return axios
    .put(`/api/chime/${chimeId}/folder/${folderId}/save_order`, {
      question_order: newQuestionOrder,
    })
    .then((res) => {
      return res.data;
    })
    .catch((err) => console.error(err));
}

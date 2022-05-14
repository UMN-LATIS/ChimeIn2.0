import { axiosClient as axios } from "../common/axios";
import orderBy from "lodash/orderBy";
import type {
  FolderWithQuestions,
  ResponseMessage,
  Chime,
  Folder,
  Session,
} from "../types";

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
}): Promise<ResponseMessage> {
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
): Promise<ResponseMessage> {
  return axios
    .put(`/api/chime/${chimeId}/folder/${folderId}/save_order`, {
      question_order: newQuestionOrder,
    })
    .then((res) => {
      return res.data;
    })
    .catch((err) => console.error(err));
}

export function getOpenSessionsWithinChime(
  chimeId: number
): Promise<Session[]> {
  // Note: the api endpoint is `/openQuestions` but apparently
  // this does not get a list of questions (open or otherwise)
  // instead the enpoint returns the chime and openSessions
  return axios
    .get("/api/chime/" + chimeId + "/openQuestions")
    .then((res) => {
      return res.data.sessions;
    })
    .catch((err) =>
      console.error(`Error loading folder: ${err.message}`, err.response)
    );
}

export function getChimes(): Promise<Chime[]> {
  return axios
    .get("/api/chime")
    .then((res) => orderBy(res.data, "created_at", ["desc"]))
    .catch(console.error);
}

export function updateFolder(
  {
    chimeId,
    folderId,
  }: {
    chimeId: number;
    folderId: number;
  },
  folderUpdate: {
    [folderProp: string]: any;
  }
): Promise<Folder | ResponseMessage> {
  return axios
    .put(`/api/chime/${chimeId}/folder/${folderId}`, folderUpdate)
    .then((res) => {
      return res.data;
    })
    .catch(console.error);
}

export function openAllQuestionsInFolder({
  chimeId,
  folderId,
}: {
  chimeId: number;
  folderId: number;
}): Promise<ResponseMessage> {
  return axios
    .post(`/api/chime/${chimeId}/folder/${folderId}/question/startAll`, {})
    .then((res) => res.data)
    .catch(console.error);
}

export function closeAllQuestionsInFolder({
  chimeId,
  folderId,
}: {
  chimeId: number;
  folderId: number;
}): Promise<ResponseMessage> {
  return axios
    .post(`/api/chime/${chimeId}/folder/${folderId}/question/stopAll`, {})
    .then((res) => res.data)
    .catch(console.error);
}

export function closeQuestion({
  chimeId,
  folderId,
  questionId,
}: {
  chimeId: number;
  folderId: number;
  questionId: number;
}): Promise<ResponseMessage> {
  return axios
    .put(
      `/api/chime/${chimeId}/folder/${folderId}/question/${questionId}/stopSession`,
      {}
    )
    .then((res) => res.data)
    .catch(console.error);
}

export function openQuestion({
  chimeId,
  folderId,
  questionId,
}: {
  chimeId: number;
  folderId: number;
  questionId: number;
}): Promise<ResponseMessage> {
  return axios
    .put(
      `/api/chime/${chimeId}/folder/${folderId}/question/${questionId}/startSession`,
      {}
    )
    .then((res) => res.data)
    .catch(console.error);
}

export function deleteFolder({
  chimeId,
  folderId,
}: {
  chimeId: number;
  folderId: number;
}): Promise<ResponseMessage> {
  return axios
    .delete(`/api/chime/${chimeId}/folder/${folderId}`)
    .then((res) => res.data)
    .catch(console.error);
}

export function importFolder({
  destinationChimeId,
  destinationFolderId,
  sourceFolderId,
}: {
  destinationChimeId: number;
  destinationFolderId: number;
  sourceFolderId: number;
}): Promise<ResponseMessage> {
  return axios
    .post(
      `/api/chime/${destinationChimeId}/folder/${destinationFolderId}/import`,
      {
        folder_id: sourceFolderId,
      }
    )
    .then((res) => res.data)
    .catch((err) => {
      console.error("error", "Error with import:", err.response);
    });
}

export function forceSyncGradesWithLMS({
  chimeId,
  folderId,
}: {
  chimeId: number;
  folderId: number;
}): Promise<boolean> {
  return axios
    .post(`/api/chime/${chimeId}/folder/${folderId}/sync`)
    .then((res) => res.status === 200)
    .catch((err) => {
      console.error(err);
      return false;
    });
}

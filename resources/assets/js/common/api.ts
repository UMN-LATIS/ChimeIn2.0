import axios from "./axiosClient";
import orderBy from "lodash/orderBy";
import type {
  FolderWithQuestions,
  ResponseMessage,
  Chime,
  Folder,
  Session,
  ChimeOptions,
  User,
  ChimeFolderParticipationSummary,
} from "../types";

export async function getFolderWithQuestions({
  chimeId,
  folderId,
}: {
  chimeId: number;
  folderId: number;
}): Promise<FolderWithQuestions> {
  const res = await axios.get(
    `/api/chime/${chimeId}/folder/${folderId}?include_questions=true`
  );
  return {
    ...res.data,
    // sort the questions within the folder by their order
    questions: res.data.questions.sort((a, b) => a.order - b.order).map((q, i) => {
      return {
        ...q,
        index: i,
      };
    }),
  };
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

export async function getOpenSessionsWithinChime(
  chimeId: number
): Promise<Session[]> {
  // Note: the api endpoint is `/openQuestions` but apparently
  // this does not get a list of questions (open or otherwise)
  // instead the enpoint returns the chime and openSessions
  const res = await axios.get("/api/chime/" + chimeId + "/openQuestions");
  return res.data.sessions;
}

export function getChimes(): Promise<Chime[]> {
  return axios
    .get("/api/chime")
    .then((res) => orderBy(res.data, "created_at", ["desc"]))
    .catch(console.error);
}

export function updateChimeOptions(
  chimeId: number,
  update: ChimeOptions & { name: string }
): Promise<ResponseMessage> {
  return axios.patch(`/api/chime/${chimeId}`, update).then((res) => res.data);
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
    .put(`/api/chime/${chimeId}/folder/${folderId}/question/stopAll`, {})
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
    .post(`/api/chime/${chimeId}/folder/${folderId}/question/${questionId}`, {})
    .then((res) => {
      return res.data;
    })
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
  folderId?: number;
}): Promise<boolean> {
  const syncUrl = folderId
    ? `/api/chime/${chimeId}/folder/${folderId}/sync`
    : `/api/chime/${chimeId}/sync`;
  return axios
    .post(syncUrl)
    .then((res) => res.status === 200)
    .catch((err) => {
      console.error(err);
      return false;
    });
}

export function getChimeUsers(chimeId: number): Promise<User[]> {
  return axios
    .get(`/api/chime/${chimeId}/users`)
    .then((res) => res.data)
    .catch((err) => {
      console.error("cannot get chime users:", err.message);
      return [];
    });
}

export async function updateChimeUserPermissions({
  chimeId,
  userId,
  permissionNumber,
}: {
  chimeId: number;
  userId: number;
  permissionNumber: number;
}) {
  const res = await axios.put<{ success: boolean }>(
    `/api/chime/${chimeId}/users/${userId}`,
    {
      permission_number: permissionNumber,
    }
  );

  return res.data;
}

export async function removeChimeUser({
  chimeId,
  userId,
}: {
  chimeId: number;
  userId: number;
}) {
  const res = await axios.delete<{ success: boolean }>(
    `/api/chime/${chimeId}/users/${userId}`
  );
  return res.data;
}

export function getChime(chimeId: number): Promise<Chime> {
  return (
    axios
      .get(`/api/chime/${chimeId}`)
      .then((res) => res.data)
      // cast some chime options as booleans
      // rather than ints returned from server
      .then((chime) => ({
        ...chime,
        require_login: Boolean(chime.require_login),
        students_can_view: Boolean(chime.students_can_view),
        join_instructions: Boolean(chime.join_instructions),
        show_folder_title_to_participants: Boolean(
          chime.show_folder_title_to_participants
        ),
      }))
  );
}

export function getChimeFolderParticipation({
  chimeId,
  folderId,
}: {
  chimeId: number;
  folderId?: number;
}): Promise<ChimeFolderParticipationSummary> {
  return axios
    .get<ChimeFolderParticipationSummary>(
      `/api/chime/${chimeId}/folder/${folderId}/participation`
    )
    .then((res) => res.data);
}

export async function getChimeChannel(chimeId: number, channelName: string): Promise<{
  chime_id: number;
  channel_name: string;
  user_ids: number[];
  user_count: number;
}> {
  const res = await axios
    .get(`/api/chime/${chimeId}/channels/${channelName}`);

  return res.data;
}

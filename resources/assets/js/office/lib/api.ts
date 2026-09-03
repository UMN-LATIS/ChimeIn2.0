import axios, { AxiosInstance } from "axios";
import * as T from "@/types";

export interface OfficeChime {
  id: number;
  name: string;
  access_code: string;
}

export class UnauthorizedError extends Error {}

function client(token: string): AxiosInstance {
  const instance = axios.create({
    baseURL: "/api/office",
    headers: { Authorization: `Bearer ${token}`, Accept: "application/json" },
  });

  instance.interceptors.response.use(
    (response) => response,
    (error) => {
      const status = error?.response?.status;
      if (status === 401 || status === 403) {
        throw new UnauthorizedError(
          error.response?.data?.message ?? "This ChimeIn connection is no longer valid."
        );
      }
      throw error;
    }
  );

  return instance;
}

/* -------------------------------------------------- browse token endpoints */

export async function listChimes(browseToken: string): Promise<OfficeChime[]> {
  const { data } = await client(browseToken).get("/chimes");
  return data;
}

export async function issueChimeToken(
  browseToken: string,
  chimeId: number
): Promise<{ token: string; expires_at: string | null; chime: OfficeChime }> {
  const { data } = await client(browseToken).post(`/chimes/${chimeId}/token`);
  return data;
}

/* --------------------------------------------------- chime token endpoints */

export async function getChime(token: string, chimeId: number): Promise<T.Chime> {
  const { data } = await client(token).get(`/chimes/${chimeId}`);
  return data;
}

export async function getFolder(
  token: string,
  chimeId: number,
  folderId: number
): Promise<T.FolderWithQuestions> {
  const { data } = await client(token).get(`/chimes/${chimeId}/folders/${folderId}`);
  return data;
}

export async function getQuestion(
  token: string,
  chimeId: number,
  questionId: number
): Promise<T.Question> {
  const { data } = await client(token).get(`/chimes/${chimeId}/questions/${questionId}`);
  return data;
}

export async function getChimeUsers(token: string, chimeId: number): Promise<T.User[]> {
  const { data } = await client(token).get(`/chimes/${chimeId}/users`);
  return data;
}

export async function openQuestion(
  token: string,
  chimeId: number,
  questionId: number
): Promise<T.Question> {
  const { data } = await client(token).post(
    `/chimes/${chimeId}/questions/${questionId}/open`
  );
  return data;
}

export async function closeQuestion(
  token: string,
  chimeId: number,
  questionId: number
): Promise<T.Question> {
  const { data } = await client(token).post(
    `/chimes/${chimeId}/questions/${questionId}/close`
  );
  return data;
}

export function getFolderWithQuestions({ chimeId, folderId }) {
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

import * as chimeApi from "./chime";
import * as folderApi from "./folder";
import * as questionApi from "./question";

/**
 * Creates a new question within a new folder and chime.
 *
 * @param {string} opts.chimeName - name of the new chime
 * @param {string} opts.folderName - name of the new folder
 * @param {string} opts.questionText - question prompt
 * @param {Response[]} [opts.questionResponses=[]] - array of possible responses for question.
 * @param {string} [opts.questionType="multiple_choice"] - type of question
 *
 * @returns {Object} {chime, folder, question} - refs to new object, folder, and question
 */
export function createChimeFolderQuestion({
  chimeName,
  folderName,
  question_text,
  questionText,
  question_responses = [],
  questionResponses,
  question_type = "multiple_choice",
  questionType,
}) {
  // check required params
  // TODO: better parameter checking
  if (!chimeName) throw Error("chimeName is required");
  if (!folderName) throw Error("folderName is required");
  if (!question_text && !questionText)
    throw Error("question_text (or questionText) is required");
  if (!question_type && !questionType)
    throw Error("question_type (or questionType) is required");

  if (
    (question_type === "multiple_choice" ||
      questionType === "multiple_choice") &&
    !question_responses.length &&
    !questionResponses.length
  )
    throw Error("question_responses (or questionResponses) is required");

  let newChime;
  let newFolder;
  let newQuestion;

  return chimeApi
    .createChime({ name: chimeName })
    .then((chime) => {
      newChime = chime;
      return folderApi.createFolder({
        chimeId: newChime.id,
        name: folderName,
      });
    })
    .then((folder) => {
      newFolder = folder;

      return questionApi.createQuestion({
        chimeId: newChime.id,
        folderId: newFolder.id,
        question_text: question_text || questionText,
        question_info: {
          question_type: question_type || questionType,
          question_responses: question_responses.length
            ? question_responses
            : questionResponses,
        },
      });
    })
    .then((question) => {
      newQuestion = question;
      return {
        question: newQuestion,
        folder: newFolder,
        chime: newChime,
      };
    });
}

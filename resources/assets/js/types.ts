import type { Ref } from "vue";

export interface User {
  id: number;
  name: string;
  email: string;
  permission_number: number;
  created_at: string;
  updated_at: string;
  umndid: string | null;
  global_admin: boolean;
  guest_user: boolean;
  // a bunch of other stuff
  [key: string]: any;
}

export interface SortableUser extends User {
  lastName: string;
  firstName: string;
  /** `lastName, firstName` */
  sortableName: string;
}

export interface Response {
  id: number;
  created_at?: string;
  updated_at?: string;
  deleted_at?: string | null;
  session_id: number;
  user_id: number;
  response_info: {
    question_type: QuestionType;
    // a bunch of other stuff
    [key: string]: any;
  };

  user: User;
}

export interface Session {
  id: number;
  created_at?: string;
  updated_at?: string;
  deleted_at?: string | null;
  question_id: number;
  responses: Response[];
}

export type QuestionType =
  | "multiple_choice"
  | "slider_response"
  | "free_response"
  | "image_response"
  | "heatmap_response"
  | "text_heatmap_response"
  | "pin_on_image_response"
  | "no_response";

export type HTMLString = string;

export interface Question<T extends QuestionInfo = QuestionInfo> {
  id: number;
  created_at?: string;
  updated_at?: string;
  deleted_at?: string | null;
  /** the session that's currently active within this chime */
  current_session_id: number | null;
  text: HTMLString;
  folder_id: number;
  order: number;
  question_info: T;
  anonymous: boolean;
  allow_multiple: boolean;
  sessions: Session[];
}

export interface QuestionInfo {
  question_type: QuestionType;
  question_responses: unknown;
}

export interface ChoiceForMultipleChoiceQuestionInfo {
  text: string;
  correct: boolean;
}

export interface MultipleChoiceQuestionInfo extends QuestionInfo {
  question_type: "multiple_choice";
  question_responses: ChoiceForMultipleChoiceQuestionInfo[];
}

export interface FreeResponseQuestionInfo extends QuestionInfo {
  question_type: "free_response";
  question_responses: never[];
}

export interface ImageResponseQuestionInfo extends QuestionInfo {
  question_type: "image_response";
  question_responses: never[];
}

export type Filename = string;

export interface ImageHeatmapQuestionInfo extends QuestionInfo {
  question_type: "heatmap_response";
  question_responses: {
    image: Filename; // "1jpqNOnV5Kig4FVwOf9tPIAarwvVl8nRvZrOiuLI.jpg"
    image_name: string; // alt attribute for image
  };
}

export interface SliderResponseQuestionInfo extends QuestionInfo {
  question_type: "slider_response";
  question_responses: {
    left_choice_text: string;
    right_choice_text: string;
    range_type: "Qualitative" | "Numeric (Linear)";
  };
}

export interface TextHeatmapQuestionInfo extends QuestionInfo {
  question_type: "text_heatmap_response";
  question_responses: { heatmap_text: HTMLString };
}

export interface NoResponseQuestionInfo extends QuestionInfo {
  question_type: "no_response";
  question_responses: never[];
}

export interface PinOnImageQuestionInfo extends QuestionInfo {
  question_type: "pin_on_image_response";
  question_responses: {
    image: Filename; // "1jpqNOnV5Kig4FVwOf9tPIAarwvVl8nRvZrOiuLI.jpg"
    image_name: string; // alt attribute for image
  };
}

export type MultipleChoiceQuestion = Question<MultipleChoiceQuestionInfo>;
export type FreeResponseQuestion = Question<FreeResponseQuestionInfo>;
export type ImageResponseQuestion = Question<ImageResponseQuestionInfo>;
export type HeatmapQuestion = Question<ImageHeatmapQuestionInfo>;
export type SliderResponseQuestion = Question<SliderResponseQuestionInfo>;
export type TextHeatmapQuestion = Question<TextHeatmapQuestionInfo>;
export type PinOnImageQuestion = Question<PinOnImageQuestionInfo>;
export type NoResponseQuestion = Question<NoResponseQuestionInfo>;

export interface Folder {
  id: number;
  created_at: string;
  updated_at: string;
  deleted_at: string | null;
  name: string;
  chime_id: number;
  resource_link_pk: number | null;
  order: number;
  lti_lineitem: any;
  questions_count: number;
  questions?: Question[];
  student_view?: boolean;
}

export interface FolderWithQuestions extends Folder {
  questions: Question[];
}

export enum LTIGradeOptions {
  FULL_CREDIT_FOR_PARITICIPATION = 0,
  ONLY_POINTS_FOR_CORRECT = 1,
  HALF_CREDIT_FOR_PARTICIPATION = 2,
}

export interface ChimeOptions {
  require_login: boolean;
  /** students can view response results */
  students_can_view: boolean;
  /** show join instructions */
  join_instructions: boolean;
  show_folder_title_to_participants: boolean;
  only_correct_answers_lti?: LTIGradeOptions;
}

type LTI_Grade_Mode = "no_grades" | "one_grade" | "multiple_grades" | null;

export interface Chime extends ChimeOptions {
  id: number;
  access_code: string;
  created_at: string;
  updated_at: string;
  deleted_at: string | null;
  name: string;
  lti_return_url: string | null;
  lti_course_title: string | null;
  lti_course_id: any;
  lti_setup_complete: boolean;
  resource_link_pk: number | null;
  lti13_resource_link_id: number | null;
  lti_grade_mode: LTI_Grade_Mode;
  pivot: {
    /** current user */
    user_id: number;
    chime_id: number;
    permission_number: number;
    created_at: string;
    updated_at: string;
  };
  folders: Folder[];
}

export type RemovableRef<T> = Ref<T | null | undefined>;
export type ResponseMessage = string;

export type WordFrequencyLookup = {
  [word: string]: number;
};

export type Partial<T> = {
  [P in keyof T]?: T[P];
};

export type Maybe<T> = T | null;

export interface FormInputEvent extends Event {
  target: HTMLInputElement;
}

export type ChimeFolderParticipationResponseItem = Response & {
  is_correct: boolean;
  question_id: number;
};

export interface ChimeFolderParticipationSummary {
  participants: User[];
  presenters: User[];
  responses: ChimeFolderParticipationResponseItem[];
}

export type PartialNested<T> = {
  [P in keyof T]?: PartialNested<T[P]>;
};

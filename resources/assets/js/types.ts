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
  | "slider"
  | "free_response"
  | "image_response"
  | "heatmap_response"
  | "text_heatmap_response";

export interface Question {
  id: number;
  created_at?: string;
  updated_at?: string;
  deleted_at?: string | null;
  /** the session that's currently active within this chime */
  current_session_id: number | null;
  /** HTML string */
  text: string;
  folder_id: number;
  order: number;
  question_info: {
    question_type: QuestionType;
    /** content varies depending on question type */
    [key: string]: any;
  };
  anonymous: boolean;
  allow_multiple: boolean;
  sessions: Session[];
}

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

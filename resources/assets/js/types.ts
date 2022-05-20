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
  // a bunch of other stuff
  [key: string]: any;
}

export interface Response {
  id: number;
  created_at?: string;
  updated_at?: string;
  deleted_at?: string | null;
  session_id: number;
  user_id: number;
  response_info: {
    question_type: string;
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
    question_type: string;
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
  resource_link_pk: string | null;
  order: number;
  lti_lineitem: any;
  questions_count: number;
  questions?: Question[];
}

export interface FolderWithQuestions extends Folder {
  questions: Question[];
}

export interface Chime {
  id: number;
  access_code: string;
  created_at: string;
  updated_at: string;
  deleted_at: string | null;
  name: string;
  lti_return_url: string | null;
  lti_course_title: string | null;
  lti_course_id: any;
  require_login: boolean;
  /** students can view response results */
  students_can_view: boolean;
  /** show join instructions */
  join_instructions: boolean;
  only_correct_answers_lti: boolean;
  lti_setup_complete: boolean;
  resource_link_pk: string | null;
  lti13_resource_link_id: string | null;
  lti_grade_mode: string | null;
  show_folder_title_to_participants: boolean;
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

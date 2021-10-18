/// <reference types="Cypress" />

import * as chimeApi from "./chime.js";
import * as folderApi from "./folder.js";
import * as questionApi from "./question.js";

export default {
  ...chimeApi,
  ...folderApi,
  ...questionApi,
};

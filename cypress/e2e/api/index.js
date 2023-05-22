/// <reference types="Cypress" />

import * as chimeApi from "./chime";
import * as folderApi from "./folder";
import * as questionApi from "./question";
import * as comboApi from "./combo";
import * as userApi from "./user";
import * as chimeUserApi from "./chimeUser";
import * as responseApi from "./response";

export default {
  ...chimeApi,
  ...folderApi,
  ...questionApi,
  ...comboApi,
  ...chimeUserApi,
  ...userApi,
  ...responseApi,
};

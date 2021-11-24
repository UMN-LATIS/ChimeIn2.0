import get from "lodash/get.js";
import { PERMISSIONS } from "./constants.js";

const minPermissionLevelLookup = new Map([
  [PERMISSIONS.PARTICIPATE_AS_GUEST, 0],
  [PERMISSIONS.PARTICIPATE, 100],
  [PERMISSIONS.EDIT, 300],
  [PERMISSIONS.PRESENT, 300],
]);

export default (permission, chime) => {
  const currentUserPermissionLevel = get(chime, "pivot.permission_number");
  const requiredPermissionLevel = minPermissionLevelLookup.get(permission);

  if (
    currentUserPermissionLevel === undefined ||
    requiredPermissionLevel === undefined
  ) {
    return false;
  }

  return currentUserPermissionLevel >= requiredPermissionLevel;
};

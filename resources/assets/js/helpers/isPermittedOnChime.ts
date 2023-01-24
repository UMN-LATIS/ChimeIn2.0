import { Chime, PartialNested } from "../types";
import { PERMISSIONS } from "./constants";

const minPermissionLevelLookup = new Map([
  [PERMISSIONS.PARTICIPATE_AS_GUEST, 0],
  [PERMISSIONS.PARTICIPATE, 100],
  [PERMISSIONS.EDIT, 300],
  // [PERMISSIONS.PRESENT, 300],
]);

export default (permission: string, chime: PartialNested<Chime>) => {
  const currentUserPermissionLevel = chime?.pivot?.permission_number ?? 0;
  const requiredPermissionLevel = minPermissionLevelLookup.get(permission);

  if (
    currentUserPermissionLevel === undefined ||
    requiredPermissionLevel === undefined
  ) {
    return false;
  }

  return currentUserPermissionLevel >= requiredPermissionLevel;
};

import { SortableUser, User } from "../types";

export function toSortableUser(user: User): SortableUser {
  const names = user.name.split(" ");
  const lastName = names.at(-1) ?? "";
  const firstName = names.slice(0, -1).join(" ");
  return {
    ...user,
    lastName,
    firstName,
    sortableName: `${lastName}, ${firstName}`,
  };
}

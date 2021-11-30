import { describe, expect, it } from "@jest/globals";
import { PERMISSIONS } from "./constants.js";
import isPermittedOnChime from "./isPermittedOnChime.js";

const createChimeWithCurrentUserPermissionNumber = (permissionNumber) => ({
  id: 8,
  name: "Test Chime",
  require_login: 0,
  deleted_at: null,
  students_can_view: 0,
  pivot: {
    user_id: 4,
    permission_number: permissionNumber,
  },
});

const chimeWithCurrentUser0 = createChimeWithCurrentUserPermissionNumber(0);
const chimeWithCurrentUser100 = createChimeWithCurrentUserPermissionNumber(100);
const chimeWithCurrentUser300 = createChimeWithCurrentUserPermissionNumber(300);

describe("edit", () => {
  it("defaults to false", () => {
    const emptyChime = {};
    expect(isPermittedOnChime(PERMISSIONS.EDIT, emptyChime)).toBe(false);
  });
  it("returns true if permission_number >= 300", () => {
    expect(isPermittedOnChime(PERMISSIONS.EDIT, chimeWithCurrentUser300)).toBe(
      true
    );
  });
  it("returns false if permission_number < 300", () => {
    expect(isPermittedOnChime(PERMISSIONS.EDIT, chimeWithCurrentUser100)).toBe(
      false
    );
    expect(isPermittedOnChime(PERMISSIONS.EDIT, chimeWithCurrentUser0)).toBe(
      false
    );
  });
});

describe("participate - login required", () => {
  it("defaults to false", () => {
    const emptyChime = {};
    expect(isPermittedOnChime(PERMISSIONS.PARTICIPATE, emptyChime)).toBe(false);
  });

  it("returns true if permission_number >= 100", () => {
    expect(
      isPermittedOnChime(PERMISSIONS.PARTICIPATE, chimeWithCurrentUser300)
    ).toBe(true);
    expect(
      isPermittedOnChime(PERMISSIONS.PARTICIPATE, chimeWithCurrentUser100)
    ).toBe(true);
  });
  it("returns false if permission_number < 100", () => {
    expect(
      isPermittedOnChime(PERMISSIONS.PARTICIPATE, chimeWithCurrentUser0)
    ).toBe(false);
  });
});

describe("participate as guest", () => {
  it("defaults to false", () => {
    const emptyChime = {};
    expect(isPermittedOnChime(PERMISSIONS.PARTICIPATE, emptyChime)).toBe(false);
  });

  it("returns true if permission_number >= 0", () => {
    expect(
      isPermittedOnChime(
        PERMISSIONS.PARTICIPATE_AS_GUEST,
        chimeWithCurrentUser300
      )
    ).toBe(true);
    expect(
      isPermittedOnChime(
        PERMISSIONS.PARTICIPATE_AS_GUEST,
        chimeWithCurrentUser100
      )
    ).toBe(true);
    expect(
      isPermittedOnChime(
        PERMISSIONS.PARTICIPATE_AS_GUEST,
        chimeWithCurrentUser0
      )
    ).toBe(true);
  });
});

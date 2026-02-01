// config/rolePermissions.js
import { ROLES } from "./roles";

export const ROLE_PERMISSIONS = {
  [ROLES.ADMIN]: [
    1,2,3,4,5,6,7,8,9,10,11,12,
    13,14,15,16,17,18,19,20,
  ],

  [ROLES.PREMIUM]: [
    2,3,4,6,8,9,10,12,
    13,14,15,16,17,18,19,20,
  ],

  [ROLES.STANDARD]: [
    2,3,4,8,9,10,11,12,
    13,14,15,16,17,18,19,20,
  ],
};

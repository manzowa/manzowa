// auth/AuthContext.js
import { createContext, useContext } from "react";
import { ROLE_PERMISSIONS } from "../config/rolePermissions";

const AuthContext = createContext(null);

export const AuthProvider = ({ user, children }) => {
  const permissions = ROLE_PERMISSIONS[user.role] || [];

  const hasPermission = (permission) =>
    permissions.includes(permission);

  return (
    <AuthContext.Provider value={{ user, hasPermission }}>
      {children}
    </AuthContext.Provider>
  );
};

export const useAuth = () => {
  const ctx = useContext(AuthContext);
  if (!ctx) {
    throw new Error("useAuth must be used inside AuthProvider");
  }
  return ctx;
};

// auth/Can.js
import { useAuth } from "./AuthContext";

const Can = ({ permission, children }) => {
  const { hasPermission } = useAuth();

  if (!hasPermission(permission)) return null;
  return children;
};

export default Can;

// auth/ProtectedRoute.js
import { Navigate } from "react-router-dom";
import { useAuth } from "./AuthContext";

const ProtectedRoute = ({ permission, children }) => {
  const { hasPermission } = useAuth();

  return hasPermission(permission)
    ? children
    : <Navigate to="/unauthorized" replace />;
};

export default ProtectedRoute;

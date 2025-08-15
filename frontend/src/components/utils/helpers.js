export const absoluteUrl = (url = null) => {
  const location = window.location;
  if (url != null) {
    return location.origin + `${url}`;
  }
  return location.origin;
};
export const ucfirst = (str) => {
  if (typeof str !== "string" || !str) {
    return "";
  }
  return str.charAt(0).toUpperCase() + str.slice(1).toLowerCase();
};
export async function isLinkActive(url) {
  try {
    const response = await fetch(url, { method: "HEAD", mode: "no-cors" });
    if (!response.ok) {
      return false;
    }
    return true;
  } catch (error) {
    return false;
  }
};
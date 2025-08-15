const BASE_URL = "http://manzowa.local/api/v1";

export async function fetchSchools(page, limit) {
  try {
    const url = `${BASE_URL}/ecoles/page/${page}/${limit}`;
    const response = await fetch(url, {
      headers: { Accept: 'application/json' },
    });

    if (!response.ok) {
      if (process.env.NODE_ENV === "development") console.warn(`HTTP error! Status: ${response.status}`);
      return [];
    }

    const data = await response.json();

    if (!data?.data?.schools) {
      if (process.env.NODE_ENV === "development") console.debug("No schools found in response");
      return [];
    }

    return data.data.schools;
  } catch (error) {
    if (process.env.NODE_ENV === "development") {
      console.error('Error loading schools:', error);
    } else {
      // Sentry.captureException(error);
    }
    return [];
  }
}

export async function fetchSchool(id) {
  try {
    const url = `${BASE_URL}/ecoles/${id}`;
    const response = await fetch(url, {
      headers: { Accept: 'application/json' },
    });

    if (!response.ok) {
      if (process.env.NODE_ENV === "development") console.warn(`HTTP error! Status: ${response.status}`);
      return null;
    }

    const data = await response.json();

    if (!data?.data?.school) {
      if (process.env.NODE_ENV === "development") console.debug("No school found in response");
      return null;
    }

    return data.data.school;
  } catch (error) {
    if (process.env.NODE_ENV === "development") {
      console.error('Error loading school:', error);
    } else {
      // Sentry.captureException(error);
    }
    return null;
  }
}

export async function fetchSchoolsByName(name, limit) {
  try {
    const url = `${BASE_URL}/ecoles/${name}/${limit}`;
    const response = await fetch(url, {
      headers: { Accept: 'application/json' },
    });

    if (!response.ok) {
      if (process.env.NODE_ENV === "development") console.warn(`HTTP error! Status: ${response.status}`);
      return [];
    }

    const data = await response.json();

    if (!data?.data?.schools) {
      if (process.env.NODE_ENV === "development") console.debug("No schools found in response");
      return [];
    }

    return data.data.schools;
  } catch (error) {
    if (process.env.NODE_ENV === "development") {
      console.error('Error loading schools by name:', error);
    } else {
      // Sentry.captureException(error);
    }
    return [];
  }
}

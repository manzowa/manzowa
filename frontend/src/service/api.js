import { absoluteUrl } from "../components/utils/helpers";
const BASE_URL = absoluteUrl("/api/v1");

export async function fetchSchools(page, limit) {
  try {
    const segments = [BASE_URL, 'ecoles', 'page'];
    if (page) segments.push(encodeURIComponent(page));
    if (limit) segments.push(encodeURIComponent(limit));
    const url = segments.join('/');
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
    const segments = [BASE_URL, 'ecoles', id];
    const url = segments.join('/');
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
    const segments = [BASE_URL, 'ecoles', encodeURIComponent(name)];
    if (limit) segments.push(encodeURIComponent(limit));
    const url = segments.join('/');
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

export async function fetchSchoolSearch(limit, offset, nom, type) 
{
  try {
    const segments = [BASE_URL, 'ecoles', 'page'];
    if (limit) segments.push(encodeURIComponent(limit));
    if (offset) segments.push(encodeURIComponent(offset));
    if (nom) segments.push(encodeURIComponent(nom));
    if (type) segments.push(encodeURIComponent(type));
    const url = segments.join('/');
    
    const response = await fetch(url, {
      headers: { Accept: 'application/json' },
    });

    if (!response.ok) {
      if (process.env.NODE_ENV === "development") {
        console.warn(`HTTP error! Status: ${response.status}`);
      }
      return [];
    }
    const data = await response.json();

    if (!data?.data) {
      if (process.env.NODE_ENV === "development") {
        console.debug("No data found in response");
      }
      return [];
    }
    return data;
  } catch (error) {
    if (process.env.NODE_ENV === "development") {
      console.error('Error loading data by search:', error);
    } else {
      // Sentry.captureException(error);
    }
    return [];
  }
}
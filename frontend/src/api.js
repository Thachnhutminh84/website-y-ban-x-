import { API_URL } from "./config.js";

async function handleResponse(response) {
  const isJson = response.headers.get("content-type")?.includes("application/json");
  const payload = isJson ? await response.json() : null;

  if (!response.ok) {
    throw new Error(payload?.message || "Request failed");
  }

  return payload;
}

export async function fetchCategories() {
  const response = await fetch(`${API_URL}/categories`);
  const payload = await handleResponse(response);
  return payload.data;
}

export async function fetchNews(category = "") {
  const url = new URL(`${API_URL}/news`);
  if (category) {
    url.searchParams.set("category", category);
  }

  const response = await fetch(url);
  const payload = await handleResponse(response);
  return payload.data;
}

export async function fetchNewsDetail(identifier) {
  const response = await fetch(`${API_URL}/news/${identifier}`);
  const payload = await handleResponse(response);
  return payload.data;
}

export async function createNews(formData) {
  const response = await fetch(`${API_URL}/news`, {
    method: "POST",
    body: formData
  });

  const payload = await handleResponse(response);
  return payload.data;
}

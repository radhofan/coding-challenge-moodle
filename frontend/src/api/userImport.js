/**
 * Safely parses an HTTP response object by reading raw text first.
 * Prevents JSON parse crashes when the server returns HTML error pages.
 *
 * @param {Response} res
 * @returns {Promise<Object>}
 */
const safeParseResponse = async (res) => {
  const text = await res.text();
  try {
    return JSON.parse(text);
  } catch (e) {
    throw new Error(
      `Server returned invalid response (Status ${res.status}): ${text.substring(0, 150)}`
    );
  }
};

/**
 * Uploads a CSV file or CSV text to the preview endpoint.
 *
 * @param {File} [file]
 * @param {string} [csvText]
 * @returns {Promise<Object>}
 */
export const previewCsv = async (file, csvText) => {
  let bodyData;
  let headers = {};

  if (file) {
    bodyData = new FormData();
    bodyData.append("file", file);
  } else {
    bodyData = JSON.stringify({ csv_content: csvText });
    headers["Content-Type"] = "application/json";
  }

  const res = await fetch("/api/preview", {
    method: "POST",
    headers,
    body: bodyData,
  });

  const data = await safeParseResponse(res);
  if (!res.ok || !data.success) {
    throw new Error(data.error || "Failed to parse CSV file");
  }

  return data;
};

/**
 * Uploads a CSV file or CSV text to the import endpoint for database insertion.
 *
 * @param {File} [file]
 * @param {string} [csvText]
 * @returns {Promise<Object>}
 */
export const importCsv = async (file, csvText) => {
  let bodyData;
  let headers = {};

  if (file) {
    bodyData = new FormData();
    bodyData.append("file", file);
  } else {
    bodyData = JSON.stringify({ csv_content: csvText });
    headers["Content-Type"] = "application/json";
  }

  const res = await fetch("/api/import", {
    method: "POST",
    headers,
    body: bodyData,
  });

  const data = await safeParseResponse(res);
  if (!res.ok || !data.success) {
    throw new Error(data.error || "Failed to import users");
  }

  return data;
};

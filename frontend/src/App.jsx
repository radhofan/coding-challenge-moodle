import React, { useState } from "react";

export default function App() {
  const [selectedFile, setSelectedFile] = useState(null);
  const [csvText, setCsvText] = useState("");
  const [previewResult, setPreviewResult] = useState(null);
  const [importResult, setImportResult] = useState(null);
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState(null);

  /**
   * Event handler triggered when a user selects a CSV file via the file input element.
   * Extracts the first selected file and triggers the CSV preview parsing flow.
   *
   * @param {React.ChangeEvent<HTMLInputElement>} e - The input change event object.
   * @returns {void}
   */
  const handleFileChange = (e) => {
    const file = e.target.files && e.target.files[0];
    if (file) {
      setSelectedFile(file);
      parseFilePreview(file);
    }
  };

  /**
   * Event handler for the dragover event on the upload area.
   * Prevents default browser drag-and-drop behavior to enable custom drop target handling.
   *
   * @param {React.DragEvent<HTMLDivElement>} e - The drag event object.
   * @returns {void}
   */
  const handleDragOver = (e) => {
    e.preventDefault();
  };

  /**
   * Event handler for the drop event when a user drops a file onto the upload zone.
   * Prevents browser default file opening behavior, extracts the dropped file, and starts preview.
   *
   * @param {React.DragEvent<HTMLDivElement>} e - The drop event object.
   * @returns {void}
   */
  const handleDrop = (e) => {
    e.preventDefault();
    if (e.dataTransfer.files && e.dataTransfer.files[0]) {
      const file = e.dataTransfer.files[0];
      setSelectedFile(file);
      parseFilePreview(file);
    }
  };

  /**
   * Safely parses an HTTP response object by reading raw response text first.
   * Prevents JSON syntax crash errors when the backend or Nginx returns an HTML error page.
   *
   * @param {Response} res - The fetch API Response object to parse.
   * @returns {Promise<Object>} The parsed JSON payload object.
   * @throws {Error} Throws a detailed error message if parsing fails or status is non-JSON.
   */
  const safeParseResponse = async (res) => {
    const text = await res.text();
    try {
      return JSON.parse(text);
    } catch (e) {
      throw new Error(
        `Server returned invalid response (Status ${res.status}): ${text.substring(0, 150)}`,
      );
    }
  };

  /**
   * Uploads the specified CSV file to the dry-run preview API endpoint.
   * Updates state with validation metrics (total, valid, invalid) and detailed record rows.
   *
   * @param {File} file - The CSV file object to upload and parse.
   * @returns {Promise<void>}
   */
  const parseFilePreview = async (file) => {
    setLoading(true);
    setError(null);
    setImportResult(null);

    const formData = new FormData();
    formData.append("file", file);

    try {
      const res = await fetch("/api/preview", {
        method: "POST",
        body: formData,
      });

      const data = await safeParseResponse(res);
      if (!res.ok || !data.success) {
        throw new Error(data.error || "Failed to parse CSV file");
      }

      setPreviewResult(data);
      const text = await file.text();
      setCsvText(text);
    } catch (err) {
      setError(
        err instanceof Error
          ? err.message
          : "An error occurred during file parsing",
      );
    } finally {
      setLoading(false);
    }
  };

  /**
   * Sends the validated user records to the backend import endpoint.
   * Inserts valid user records into the PostgreSQL database and displays final import metrics.
   *
   * @returns {Promise<void>}
   */
  const handleImport = async () => {
    if (!previewResult || previewResult.valid === 0) return;

    setLoading(true);
    setError(null);

    try {
      let bodyData;
      let headers = {};

      if (selectedFile) {
        bodyData = new FormData();
        bodyData.append("file", selectedFile);
      } else {
        bodyData = JSON.stringify({ csv_content: csvText });
        headers["Content-Type"] = "application/json";
      }

      const res = await fetch("/api/import", {
        method: "POST",
        headers: headers,
        body: bodyData,
      });

      const data = await safeParseResponse(res);
      if (!res.ok || !data.success) {
        throw new Error(data.error || "Failed to import users");
      }

      setImportResult(data);
    } catch (err) {
      setError(
        err instanceof Error
          ? err.message
          : "An error occurred during user import",
      );
    } finally {
      setLoading(false);
    }
  };

  /**
   * Resets all component state variables back to their initial values.
   * Allows the user to clear the current validation result and upload another CSV file.
   *
   * @returns {void}
   */
  const handleReset = () => {
    setSelectedFile(null);
    setCsvText("");
    setPreviewResult(null);
    setImportResult(null);
    setError(null);
  };

  return (
    <div className="container">
      <header>
        <h1>User Import Application</h1>
        <p className="subtitle">
          Import CSV user data with validation and preview
        </p>
      </header>

      {error && (
        <div className="alert alert-error">
          <strong>Error:</strong> {error}
        </div>
      )}

      {importResult && (
        <div className="alert alert-success">
          <strong>Import Complete!</strong> Successfully imported{" "}
          {importResult.imported} valid user records into the PostgreSQL
          database.
        </div>
      )}

      {!previewResult ? (
        <div className="card">
          <div
            className="upload-area"
            onDragOver={handleDragOver}
            onDrop={handleDrop}
            onClick={() => document.getElementById("csvFileInput")?.click()}
          >
            <div className="upload-icon">📄</div>
            <h3>Upload CSV File</h3>
            <p className="subtitle">
              Drag & drop your users.csv file here, or click to browse
            </p>
            <input
              id="csvFileInput"
              type="file"
              accept=".csv"
              style={{ display: "none" }}
              onChange={handleFileChange}
            />
            <span className="upload-btn-label">Select File</span>
          </div>
        </div>
      ) : (
        <>
          <div className="stats-grid">
            <div className="stat-box">
              <div className="stat-label">Users Found</div>
              <div className="stat-value total">{previewResult.total}</div>
            </div>
            <div className="stat-box">
              <div className="stat-label">Valid Records</div>
              <div className="stat-value valid">{previewResult.valid}</div>
            </div>
            <div className="stat-box">
              <div className="stat-label">Invalid Records</div>
              <div className="stat-value invalid">{previewResult.invalid}</div>
            </div>
          </div>

          <div className="card">
            <div
              style={{
                display: "flex",
                justifyContent: "space-between",
                alignItems: "center",
                marginBottom: "16px",
              }}
            >
              <h2 style={{ fontSize: "18px", fontWeight: "600" }}>
                CSV Validation Preview
              </h2>
              <button
                className="btn btn-secondary"
                onClick={handleReset}
                disabled={loading}
              >
                Upload Another File
              </button>
            </div>

            <div className="table-container">
              <table>
                <thead>
                  <tr>
                    <th>Name</th>
                    <th>Surname</th>
                    <th>Email</th>
                    <th>Status</th>
                    <th>Details</th>
                  </tr>
                </thead>
                <tbody>
                  {previewResult.records.map((rec, idx) => (
                    <tr key={idx}>
                      <td>
                        {rec.name || (
                          <em style={{ color: "#94a3b8" }}>(Empty)</em>
                        )}
                      </td>
                      <td>
                        {rec.surname || (
                          <em style={{ color: "#94a3b8" }}>(Empty)</em>
                        )}
                      </td>
                      <td>
                        {rec.email || (
                          <em style={{ color: "#94a3b8" }}>(Empty)</em>
                        )}
                      </td>
                      <td>
                        <span
                          className={`badge ${rec.is_valid ? "badge-valid" : "badge-error"}`}
                        >
                          {rec.status}
                        </span>
                      </td>
                      <td
                        style={{
                          color: rec.is_valid ? "#64748b" : "#dc2626",
                          fontSize: "13px",
                        }}
                      >
                        {rec.is_valid ? "Ready to import" : rec.error_message}
                      </td>
                    </tr>
                  ))}
                </tbody>
              </table>
            </div>

            <div className="action-bar">
              <span style={{ fontSize: "14px", color: "#64748b" }}>
                {previewResult.valid} out of {previewResult.total} records will
                be imported
              </span>
              <button
                className="btn btn-primary"
                onClick={handleImport}
                disabled={
                  loading || previewResult.valid === 0 || !!importResult
                }
              >
                {loading
                  ? "Processing..."
                  : importResult
                    ? "Imported"
                    : `[ Import ${previewResult.valid} Users ]`}
              </button>
            </div>
          </div>
        </>
      )}
    </div>
  );
}
